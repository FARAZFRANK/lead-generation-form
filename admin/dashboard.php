<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

// Get plugin version with authoritative constant prioritization and auto DB sync
$wlgf_display_version = defined( 'WLGF_PLUGIN_VERSION' ) ? WLGF_PLUGIN_VERSION : get_option( 'wlgf_current_version', '1.3.3' );
if ( defined( 'WLGF_PLUGIN_VERSION' ) && get_option( 'wlgf_current_version' ) !== WLGF_PLUGIN_VERSION ) {
	update_option( 'wlgf_current_version', WLGF_PLUGIN_VERSION );
}

// Enqueue modern design system, icons, chartjs and dashboard controller
wp_enqueue_style( 'wlgf-admin-ui-css' );
wp_enqueue_style( 'wlgf-fontawesome-css' );
$wlgf_dashboard_js_ver = file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/dashboard.js' ) ? ( defined( 'WLGF_PLUGIN_VERSION' ) ? WLGF_PLUGIN_VERSION : '1.3.3' ) . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/dashboard.js' ) : '1.3.3';
wp_enqueue_script( 'wlgf-dashboard-js', plugin_dir_url( __FILE__ ) . 'assets/js/dashboard.js', array( 'jquery', 'wlgf-chartjs' ), $wlgf_dashboard_js_ver, true );

// Calculate forms, leads counts, and rich analytics safely
$wlgf_form_count = 0;
$wlgf_lead_count = 0;

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wlgf_all_forms = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT option_name FROM {$wpdb->options} WHERE `option_name` LIKE %s AND `option_name` NOT LIKE %s ORDER BY option_id ASC",
		$wpdb->esc_like( 'wlgf_form_' ) . '%',
		$wpdb->esc_like( 'wlgf_form_views_' ) . '%'
	)
);

$wlgf_lead_count          = 0;
$wlgf_total_views         = 0;
$wlgf_forms_list          = array();
$wlgf_dashboard_analytics = array();

if ( is_array( $wlgf_all_forms ) ) {
	foreach ( $wlgf_all_forms as $wlgf_form ) {
		$wlgf_key        = $wlgf_form->option_name;
		$wlgf_form_data  = get_option( $wlgf_key );
		if ( ! is_array( $wlgf_form_data ) || empty( $wlgf_form_data['form_id'] ) ) {
			continue;
		}
		$wlgf_form_count++;
		$wlgf_form_id     = sanitize_text_field( $wlgf_form_data['form_id'] );
			$wlgf_form_title  = ! empty( $wlgf_form_data['form_name'] ) ? $wlgf_form_data['form_name'] : ( 'Form #' . $wlgf_form_id );
			$wlgf_saved_data  = get_option( 'wlgf_saved_form_data_' . $wlgf_form_id );
			$wlgf_f_views     = intval( get_option( 'wlgf_form_views_' . $wlgf_form_id, 0 ) );
			$wlgf_total_views += $wlgf_f_views;
			$wlgf_f_leads     = is_array( $wlgf_saved_data ) ? count( $wlgf_saved_data ) : 0;
			$wlgf_lead_count  += $wlgf_f_leads;

			$wlgf_forms_list[ $wlgf_form_id ] = $wlgf_form_title;

			// Extract analytics record
			$wlgf_f_analytics = get_option( 'wlgf_analytics_' . $wlgf_form_id, array(
				'total_views'       => $wlgf_f_views,
				'total_submissions' => $wlgf_f_leads,
				'daily'             => array(),
				'step_views'        => array(),
				'device_breakdown'  => array(),
			) );

			if ( empty( $wlgf_f_analytics['total_views'] ) ) {
				$wlgf_f_analytics['total_views'] = $wlgf_f_views;
			}
			if ( empty( $wlgf_f_analytics['total_submissions'] ) ) {
				$wlgf_f_analytics['total_submissions'] = $wlgf_f_leads;
			}

			// Seed fallback daily analytics for clean visual charts if legacy form has no daily events yet
			if ( empty( $wlgf_f_analytics['daily'] ) && ( $wlgf_f_views > 0 || $wlgf_f_leads > 0 ) ) {
				$wlgf_today = gmdate( 'Y-m-d' );
				$wlgf_f_analytics['daily'][ $wlgf_today ] = array( 'views' => $wlgf_f_views, 'submissions' => $wlgf_f_leads );
			}

			$wlgf_dashboard_analytics[ $wlgf_form_id ] = $wlgf_f_analytics;
	}
}
$wlgf_overall_conversion = $wlgf_total_views > 0 ? round( ( $wlgf_lead_count / $wlgf_total_views ) * 100, 1 ) : ( $wlgf_lead_count > 0 ? 100 : 0 );

// Pass analytics store to dashboard controller
wp_add_inline_script( 'wlgf-dashboard-js', 'const WLGFDashboardData = ' . wp_json_encode( array(
	'forms' => $wlgf_dashboard_analytics,
) ), 'before' );

// Load global settings for status checks
$wlgf_settings      = get_option( 'wlgf_settings', array() );
$wlgf_recaptcha_on  = ( isset( $wlgf_settings['recaptcha'] ) && '1' === (string) $wlgf_settings['recaptcha'] );
$wlgf_smtp_on       = ( isset( $wlgf_settings['email_engine'] ) && '2' === (string) $wlgf_settings['email_engine'] );
$wlgf_notify_on     = ( isset( $wlgf_settings['notify_admin'] ) && '1' === (string) $wlgf_settings['notify_admin'] );
$wlgf_changelog_url = plugin_dir_url( __FILE__ ) . '../change-log.txt';
?>

<div class="wlgf-admin-wrap">
	<!-- Hero Header -->
	<header class="wlgf-header">
		<div class="wlgf-header__main">
			<div class="wlgf-header__icon">
				<i class="fa-solid fa-layer-group"></i>
			</div>
			<div class="wlgf-header__titles">
				<div class="wlgf-header__title-row">
					<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
						<h1 class="wlgf-header__title"><?php esc_html_e( 'Lead Generation Form Pro', 'lead-generation-form' ); ?></h1>
						<span class="wlgf-badge wlgf-badge--pro">v<?php echo esc_html( $wlgf_display_version ); ?> Pro</span>
					<?php else : ?>
						<h1 class="wlgf-header__title"><?php esc_html_e( 'Lead Generation Form', 'lead-generation-form' ); ?></h1>
						<span class="wlgf-badge wlgf-badge--primary">v<?php echo esc_html( $wlgf_display_version ); ?></span>
					<?php endif; ?>
				</div>
				<p class="wlgf-header__subtitle"><?php esc_html_e( 'Create dynamic lead capture forms, manage incoming inquiries, and export data seamlessly.', 'lead-generation-form' ); ?></p>
			</div>
		</div>
		<div class="wlgf-header__actions">
			<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--secondary" style="background: #fffbeb; border-color: #fde68a; color: #92400e; font-weight: 700; text-decoration: none;">
					<i class="fa-solid fa-crown" style="color: #f59e0b;"></i> <?php esc_html_e( 'Free vs Pro', 'lead-generation-form' ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-form-generator' ) ); ?>" class="wlgf-btn wlgf-btn--primary">
				<i class="fa-solid fa-plus"></i> <?php esc_html_e( 'Create New Form', 'lead-generation-form' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-manage-forms' ) ); ?>" class="wlgf-btn wlgf-btn--secondary">
				<i class="fa-solid fa-table-list"></i> <?php esc_html_e( 'Manage Forms', 'lead-generation-form' ); ?>
			</a>
		</div>
	</header>

	<!-- Quick-Start Onboarding & Feature Navigator Guide -->
	<section class="wlgf-card wlgf-mb-4" id="wlgf-quickstart-guide" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05); overflow: hidden;">
		<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center wlgf-flex-wrap wlgf-gap-2" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; padding: 16px 22px;">
			<div class="wlgf-flex wlgf-items-center wlgf-gap-3">
				<div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(37, 99, 235, 0.25); border: 1px solid rgba(59, 130, 246, 0.5); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #60a5fa;">
					<i class="fa-solid fa-compass"></i>
				</div>
				<div>
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<h2 style="font-size: 17px; font-weight: 700; margin: 0; color: #fff;">
							<?php esc_html_e( 'Quick-Start & Feature Navigator Guide', 'lead-generation-form' ); ?>
						</h2>
						<span class="wlgf-badge wlgf-badge--success" style="font-size: 10px; padding: 2px 7px; text-transform: uppercase; font-weight: 700;">
							<?php esc_html_e( '4 Easy Steps', 'lead-generation-form' ); ?>
						</span>
					</div>
					<p style="margin: 3px 0 0 0; font-size: 12.5px; color: #94a3b8;">
						<?php esc_html_e( 'Master your lead generation suite: Create high-converting forms, customize multi-column layouts, embed anywhere, and close deals in your visual CRM.', 'lead-generation-form' ); ?>
					</p>
				</div>
			</div>
			<div class="wlgf-btn-group">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-settings#wlgf-tab-help' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary">
					<i class="fa-solid fa-book-open"></i> <?php esc_html_e( 'Full Knowledge Base', 'lead-generation-form' ); ?>
				</a>
				<button type="button" id="wlgf-toggle-quickstart-btn" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline" style="color: #cbd5e1; border-color: rgba(255,255,255,0.25);" title="<?php esc_attr_e( 'Toggle guide view', 'lead-generation-form' ); ?>">
					<i class="fa-solid fa-chevron-up"></i>
				</button>
			</div>
		</div>
		<div class="wlgf-card__body" id="wlgf-quickstart-body" style="padding: 20px; background: #fff;">
			<div class="wlgf-grid wlgf-grid--4 wlgf-gap-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px;">
				
				<!-- Step 1 -->
				<div class="wlgf-card wlgf-card--bordered" style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; background: #fafafa; display: flex; flex-direction: column;">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2 wlgf-mb-2">
						<span style="width: 28px; height: 28px; border-radius: 6px; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">1</span>
						<h3 style="font-size: 14px; font-weight: 700; margin: 0; color: #0f172a;">
							<i class="fa-solid fa-wand-magic-sparkles text-primary wlgf-mr-1"></i> <?php esc_html_e( 'Create Your Form', 'lead-generation-form' ); ?>
						</h3>
					</div>
					<p style="font-size: 12.5px; color: #475569; line-height: 1.5; margin-bottom: 14px; flex-grow: 1;">
						<?php esc_html_e( 'Pick a pre-built industry template (Real Estate, Gym, Valuation, Booking) or drag fields onto the canvas in Form Builder.', 'lead-generation-form' ); ?>
					</p>
					<div class="wlgf-flex wlgf-items-center wlgf-justify-between wlgf-mt-2">
						<span class="wlgf-badge wlgf-badge--muted" style="font-size: 11px;">18+ Fields & Templates</span>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-form-generator' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary">
							<?php esc_html_e( 'Build Form', 'lead-generation-form' ); ?> &rarr;
						</a>
					</div>
				</div>

				<!-- Step 2 -->
				<div class="wlgf-card wlgf-card--bordered" style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; background: #fafafa; display: flex; flex-direction: column;">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2 wlgf-mb-2">
						<span style="width: 28px; height: 28px; border-radius: 6px; background: #f0fdf4; color: #16a34a; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">2</span>
						<h3 style="font-size: 14px; font-weight: 700; margin: 0; color: #0f172a;">
							<i class="fa-solid fa-table-columns text-success wlgf-mr-1"></i> <?php esc_html_e( 'Columns & Logic', 'lead-generation-form' ); ?>
						</h3>
					</div>
					<p style="font-size: 12.5px; color: #475569; line-height: 1.5; margin-bottom: 14px; flex-grow: 1;">
						<?php esc_html_e( 'Click 50% or 33% on canvas cards for side-by-side fields. Add step-breaks for multi-step pacing, formulas, or conditional logic.', 'lead-generation-form' ); ?>
					</p>
					<div class="wlgf-flex wlgf-items-center wlgf-justify-between wlgf-mt-2">
						<span class="wlgf-badge wlgf-badge--success" style="font-size: 11px;">1-Click 50% & 33%</span>
						<span class="wlgf-text-xs wlgf-text-muted"><i class="fa-solid fa-mobile-screen"></i> Responsive</span>
					</div>
				</div>

				<!-- Step 3 -->
				<div class="wlgf-card wlgf-card--bordered" style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; background: #fafafa; display: flex; flex-direction: column;">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2 wlgf-mb-2">
						<span style="width: 28px; height: 28px; border-radius: 6px; background: #fefce8; color: #ca8a04; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">3</span>
						<h3 style="font-size: 14px; font-weight: 700; margin: 0; color: #0f172a;">
							<i class="fa-solid fa-comment-dots text-warning wlgf-mr-1"></i> <?php esc_html_e( 'Publish & Display', 'lead-generation-form' ); ?>
						</h3>
					</div>
					<p style="font-size: 12.5px; color: #475569; line-height: 1.5; margin-bottom: 14px; flex-grow: 1;">
						<?php esc_html_e( 'Embed via shortcode [WLFG id="X"], trigger exit-intent popups, slide-ins, or enable the bottom Floating WhatsApp widget.', 'lead-generation-form' ); ?>
					</p>
					<div class="wlgf-flex wlgf-items-center wlgf-justify-between wlgf-mt-2">
						<span class="wlgf-badge wlgf-badge--warning" style="font-size: 11px;">Shortcodes & Popups</span>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-manage-forms' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline">
							<?php esc_html_e( 'Forms List', 'lead-generation-form' ); ?> &rarr;
						</a>
					</div>
				</div>

				<!-- Step 4 -->
				<div class="wlgf-card wlgf-card--bordered" style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; background: #fafafa; display: flex; flex-direction: column;">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2 wlgf-mb-2">
						<span style="width: 28px; height: 28px; border-radius: 6px; background: #f3e8ff; color: #9333ea; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">4</span>
						<h3 style="font-size: 14px; font-weight: 700; margin: 0; color: #0f172a;">
							<i class="fa-solid fa-users-viewfinder text-purple wlgf-mr-1" style="color: #9333ea;"></i> <?php esc_html_e( 'Close Leads in CRM', 'lead-generation-form' ); ?>
						</h3>
					</div>
					<p style="font-size: 12.5px; color: #475569; line-height: 1.5; margin-bottom: 14px; flex-grow: 1;">
						<?php esc_html_e( 'Track incoming leads on the Kanban board (New ➔ Contacted ➔ Qualified ➔ Won), write notes, call/WhatsApp clients, and export CSV/PDFs.', 'lead-generation-form' ); ?>
					</p>
					<div class="wlgf-flex wlgf-items-center wlgf-justify-between wlgf-mt-2">
						<span class="wlgf-badge wlgf-badge--primary" style="font-size: 11px;">Mini-CRM Pipeline</span>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-leads' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary">
							<?php esc_html_e( 'Open CRM', 'lead-generation-form' ); ?> &rarr;
						</a>
					</div>
				</div>

			</div>

			<!-- Quick Feature Navigator Shortcuts Bar -->
			<div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
				<div style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #64748b;">
					<i class="fa-solid fa-bolt text-warning"></i>
					<strong><?php esc_html_e( 'Jump Directly to Feature:', 'lead-generation-form' ); ?></strong>
				</div>
				<div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-form-generator' ) ); ?>" class="wlgf-badge wlgf-badge--muted" style="text-decoration: none; padding: 5px 10px; font-size: 11.5px; cursor: pointer;">
						<i class="fa-solid fa-wand-magic-sparkles text-primary"></i> <?php esc_html_e( 'Form Builder & Templates', 'lead-generation-form' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-manage-forms' ) ); ?>" class="wlgf-badge wlgf-badge--muted" style="text-decoration: none; padding: 5px 10px; font-size: 11.5px; cursor: pointer;">
						<i class="fa-solid fa-table-list text-info"></i> <?php esc_html_e( 'Manage Forms & Shortcodes', 'lead-generation-form' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-leads' ) ); ?>" class="wlgf-badge wlgf-badge--muted" style="text-decoration: none; padding: 5px 10px; font-size: 11.5px; cursor: pointer;">
						<i class="fa-solid fa-chart-gantt text-success"></i> <?php esc_html_e( 'Kanban Pipeline CRM', 'lead-generation-form' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-settings' ) ); ?>" class="wlgf-badge wlgf-badge--muted" style="text-decoration: none; padding: 5px 10px; font-size: 11.5px; cursor: pointer;">
						<i class="fa-solid fa-sliders text-warning"></i> <?php esc_html_e( 'SMTP & Integrations', 'lead-generation-form' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-settings#wlgf-tab-help' ) ); ?>" class="wlgf-badge wlgf-badge--primary" style="text-decoration: none; padding: 5px 10px; font-size: 11.5px; cursor: pointer;">
						<i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'Help & Documentation', 'lead-generation-form' ); ?>
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Stat Metric Cards (PRO-010 / PRO-017) -->
	<section class="wlgf-grid wlgf-grid--3">
		<!-- Metric: Total Forms -->
		<div class="wlgf-stat-card">
			<div class="wlgf-stat-card__icon wlgf-stat-card__icon--primary">
				<i class="fa-solid fa-file-lines"></i>
			</div>
			<div class="wlgf-stat-card__body">
				<h3 class="wlgf-stat-card__value"><?php echo esc_html( (string) $wlgf_form_count ); ?></h3>
				<p class="wlgf-stat-card__label"><?php esc_html_e( 'Forms Active', 'lead-generation-form' ); ?></p>
				<p class="wlgf-stat-card__subtext">
					<i class="fa-solid fa-circle-check text-success"></i> <?php
					/* translators: %d: count of published forms */
					echo esc_html( $wlgf_form_count > 0 ? sprintf( __( '%d published & ready', 'lead-generation-form' ), $wlgf_form_count ) : __( 'No forms created yet', 'lead-generation-form' ) );
					?>
				</p>
			</div>
		</div>

		<!-- Metric: Total Leads -->
		<div class="wlgf-stat-card">
			<div class="wlgf-stat-card__icon wlgf-stat-card__icon--success">
				<i class="fa-solid fa-users"></i>
			</div>
			<div class="wlgf-stat-card__body">
				<h3 class="wlgf-stat-card__value"><?php echo esc_html( (string) $wlgf_lead_count ); ?></h3>
				<p class="wlgf-stat-card__label"><?php esc_html_e( 'Total Submissions', 'lead-generation-form' ); ?></p>
				<p class="wlgf-stat-card__subtext">
					<i class="fa-solid fa-inbox text-primary"></i> <?php
					/* translators: %d: count of captured leads */
					echo esc_html( $wlgf_lead_count > 0 ? sprintf( __( '%d leads captured across forms', 'lead-generation-form' ), $wlgf_lead_count ) : __( 'Awaiting first lead inquiry', 'lead-generation-form' ) );
					?>
				</p>
			</div>
		</div>

		<!-- Metric: Conversion Rate -->
		<div class="wlgf-stat-card">
			<div class="wlgf-stat-card__icon wlgf-stat-card__icon--primary">
				<i class="fa-solid fa-chart-line"></i>
			</div>
			<div class="wlgf-stat-card__body">
				<h3 class="wlgf-stat-card__value"><?php echo esc_html( $wlgf_overall_conversion . '%' ); ?></h3>
				<p class="wlgf-stat-card__label"><?php esc_html_e( 'Avg. Conversion Rate', 'lead-generation-form' ); ?></p>
				<p class="wlgf-stat-card__subtext">
					<i class="fa-solid fa-eye text-muted"></i> <?php
					/* translators: %s: formatted total impressions count */
					echo esc_html( sprintf( __( '%s total impressions tracked', 'lead-generation-form' ), number_format_i18n( $wlgf_total_views ) ) );
					?>
				</p>
			</div>
		</div>
	</section>

	<!-- System Health & Security Status Strip -->
	<div class="wlgf-health-bar">
		<div class="wlgf-health-bar__items">
			<div class="wlgf-health-bar__item">
				<span class="wlgf-status-dot wlgf-status-dot--success" title="<?php esc_attr_e( 'Honeypot and submission time traps are active.', 'lead-generation-form' ); ?>"></span>
				<span><strong><?php esc_html_e( 'Spam Trap:', 'lead-generation-form' ); ?></strong> <?php esc_html_e( 'Active', 'lead-generation-form' ); ?></span>
			</div>
			<div class="wlgf-health-bar__item">
				<?php if ( $wlgf_recaptcha_on ) : ?>
					<span class="wlgf-status-dot wlgf-status-dot--primary"></span>
					<span><strong><?php esc_html_e( 'reCAPTCHA:', 'lead-generation-form' ); ?></strong> <?php esc_html_e( 'v3 Active', 'lead-generation-form' ); ?></span>
				<?php else : ?>
					<span class="wlgf-status-dot wlgf-status-dot--muted"></span>
					<span><strong><?php esc_html_e( 'reCAPTCHA:', 'lead-generation-form' ); ?></strong> <?php esc_html_e( 'Disabled', 'lead-generation-form' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="wlgf-health-bar__item">
				<span class="wlgf-status-dot <?php echo $wlgf_smtp_on ? 'wlgf-status-dot--success' : 'wlgf-status-dot--primary'; ?>"></span>
				<span><strong><?php esc_html_e( 'Email Engine:', 'lead-generation-form' ); ?></strong> <?php echo $wlgf_smtp_on ? esc_html__( 'Custom SMTP', 'lead-generation-form' ) : esc_html__( 'PHP Mailer', 'lead-generation-form' ); ?></span>
			</div>
		</div>
		<div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-settings' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline">
				<i class="fa-solid fa-gear"></i> <?php esc_html_e( 'Configure System Settings', 'lead-generation-form' ); ?>
			</a>
		</div>
	</div>

	<!-- Visual Analytics & Drop-off Funnel Charts (PRO-017) -->
	<section class="wlgf-card wlgf-mb-4">
		<div class="wlgf-card__header wlgf-analytics-toolbar">
			<div class="wlgf-analytics-toolbar__left">
				<h2 class="wlgf-analytics-toolbar__title">
					<i class="fa-solid fa-chart-line wlgf-text-primary"></i> <?php esc_html_e( 'Visual Analytics & Drop-off Funnel', 'lead-generation-form' ); ?>
				</h2>
				<div class="wlgf-analytics-toolbar__filter">
					<select id="wlgf-analytics-form-filter" class="wlgf-analytics-select" <?php echo empty( $wlgf_forms_list ) ? 'disabled style="opacity: 0.6;"' : ''; ?>>
						<option value="all"><?php echo empty( $wlgf_forms_list ) ? esc_html__( 'No Forms Available', 'lead-generation-form' ) : esc_html__( 'All Forms (Aggregate)', 'lead-generation-form' ); ?></option>
						<?php foreach ( $wlgf_forms_list as $wlgf_fid => $wlgf_fname ) : ?>
							<option value="<?php echo esc_attr( $wlgf_fid ); ?>"><?php echo esc_html( $wlgf_fname ); ?> (#<?php echo esc_html( $wlgf_fid ); ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="wlgf-btn-group" id="wlgf-time-range-group">
				<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary" data-range="7"><?php esc_html_e( '7 Days', 'lead-generation-form' ); ?></button>
				<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline" data-range="30"><?php esc_html_e( '30 Days', 'lead-generation-form' ); ?></button>
				<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline" data-range="90"><?php esc_html_e( '90 Days', 'lead-generation-form' ); ?></button>
			</div>
		</div>
		<div class="wlgf-card__body">
			<!-- Onboarding / Empty State when zero impressions or no forms created -->
			<?php $wlgf_has_forms = ! empty( $wlgf_forms_list ); ?>
			<div id="wlgf-analytics-empty-state" class="wlgf-analytics-empty <?php echo ( $wlgf_total_views > 0 || $wlgf_lead_count > 0 ) ? 'wlgf-d-none' : ''; ?>">
				<div class="wlgf-analytics-empty__icon">
					<i class="fa-solid fa-chart-simple"></i>
				</div>
				<h3 id="wlgf-analytics-empty-title" class="wlgf-analytics-empty__title">
					<?php echo $wlgf_has_forms ? esc_html__( 'Start Tracking Form Conversions', 'lead-generation-form' ) : esc_html__( 'No Forms Created Yet', 'lead-generation-form' ); ?>
				</h3>
				<p id="wlgf-analytics-empty-desc" class="wlgf-analytics-empty__desc">
					<?php
					if ( $wlgf_has_forms ) {
						esc_html_e( 'Embed your form shortcode on any page or post to automatically track visitor views, submission velocity, and step drop-offs.', 'lead-generation-form' );
					} else {
						esc_html_e( 'You have not created any forms yet. Create your first lead generation form using our drag & drop builder or pre-built templates to start capturing leads and tracking conversions.', 'lead-generation-form' );
					}
					?>
				</p>
				<div id="wlgf-analytics-empty-embed" class="wlgf-analytics-empty__embed" style="<?php echo ! $wlgf_has_forms ? 'display: none;' : ''; ?>">
					<span><?php esc_html_e( 'Active Shortcode:', 'lead-generation-form' ); ?></span>
					<code id="wlgf-empty-shortcode-preview" class="wlgf-analytics-empty__code"><?php echo $wlgf_has_forms ? '[WLFG id="' . esc_attr( key( $wlgf_forms_list ) ) . '"]' : ''; ?></code>
				</div>
				<div class="wlgf-btn-group">
					<a id="wlgf-empty-manage-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-manage-forms' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary" style="<?php echo ! $wlgf_has_forms ? 'display: none;' : ''; ?>">
						<i class="fa-solid fa-table-list"></i> <?php esc_html_e( 'Manage Forms & Shortcodes', 'lead-generation-form' ); ?>
					</a>
					<a id="wlgf-empty-create-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-form-generator' ) ); ?>" class="wlgf-btn wlgf-btn--sm <?php echo $wlgf_has_forms ? 'wlgf-btn--outline' : 'wlgf-btn--primary'; ?>">
						<i class="fa-solid fa-plus"></i> <span id="wlgf-empty-create-btn-text"><?php echo $wlgf_has_forms ? esc_html__( 'Create Another Form', 'lead-generation-form' ) : esc_html__( 'Create Your First Form', 'lead-generation-form' ); ?></span>
					</a>
				</div>
			</div>

			<!-- Analytics Charts Content Area -->
			<div id="wlgf-analytics-content-area">
				<div class="wlgf-analytics-grid">
					<div>
						<h3 class="wlgf-card__title wlgf-mb-2"><i class="fa-solid fa-arrow-trend-up wlgf-text-primary"></i> <?php esc_html_e( 'Impression & Lead Conversion Velocity', 'lead-generation-form' ); ?></h3>
						<div class="wlgf-chart-box">
							<canvas id="wlgf-analytics-chart"></canvas>
						</div>
					</div>
					<div>
						<h3 class="wlgf-card__title wlgf-mb-2"><i class="fa-solid fa-mobile-screen wlgf-text-primary"></i> <?php esc_html_e( 'Device Distribution', 'lead-generation-form' ); ?></h3>
						<div class="wlgf-chart-box">
							<canvas id="wlgf-device-chart"></canvas>
						</div>
					</div>
				</div>

				<div class="wlgf-divider wlgf-my-4"></div>

				<div>
					<div class="wlgf-d-flex wlgf-justify-between wlgf-align-center wlgf-mb-3 wlgf-flex-wrap wlgf-gap-2">
						<h3 class="wlgf-card__title wlgf-m-0"><i class="fa-solid fa-filter wlgf-text-primary"></i> <?php esc_html_e( 'Step Progression & Drop-off Funnel', 'lead-generation-form' ); ?></h3>
						<span class="wlgf-text-muted wlgf-text-sm"><?php esc_html_e( 'Identifies bottleneck steps where users drop off before completing the form.', 'lead-generation-form' ); ?></span>
					</div>
					<div id="wlgf-funnel-stages" class="wlgf-funnel-wrap">
						<!-- Dynamically populated by dashboard.js -->
					</div>
				</div>
			</div>
		</div>
	</section>
</div>