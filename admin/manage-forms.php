<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

// Page Assets
wp_enqueue_style( 'wlgf-admin-ui-css' );
wp_enqueue_style( 'wlgf-fontawesome-css', plugins_url( 'assets/fontawesome-free-6.4.2-web/css/all.min.css', __FILE__ ), array(), '6.4.2', 'all' );
wp_enqueue_style( 'wlgf-datatables-min-css', plugin_dir_url( __FILE__ ) . 'assets/datatables-2.0.8/datatables.min.css', array(), '2.0.8', 'all' );
wp_enqueue_script( 'wlgf-datatables-min-js', plugin_dir_url( __FILE__ ) . 'assets/datatables-2.0.8/datatables.min.js', array( 'jquery' ), '2.0.8', true );

wp_enqueue_style( 'wlgf-manage-forms-css', plugin_dir_url( __FILE__ ) . 'assets/css/manage-forms.css', array( 'wlgf-admin-ui-css' ), WLGF_PLUGIN_VERSION, 'all' );
wp_enqueue_script( 'wlgf-manage-forms-js', plugin_dir_url( __FILE__ ) . 'assets/js/manage-forms.js', array( 'jquery', 'wlgf-datatables-min-js' ), WLGF_PLUGIN_VERSION, true );

wp_add_inline_script(
	'wlgf-manage-forms-js',
	'const ManageForms = ' . wp_json_encode(
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wlgf-manage-forms' ),
		)
	),
	'before'
);

// Fetch all forms
global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wlgf_all_forms = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT option_name FROM {$wpdb->options} WHERE `option_name` LIKE %s AND `option_name` NOT LIKE %s ORDER BY option_id ASC",
		$wpdb->esc_like( 'wlgf_form_' ) . '%',
		$wpdb->esc_like( 'wlgf_form_views_' ) . '%'
	)
);

// Sort forms numerically by form_id
if ( is_array( $wlgf_all_forms ) ) {
	usort( $wlgf_all_forms, function( $a, $b ) {
		$id_a = intval( str_replace( 'wlgf_form_', '', $a->option_name ) );
		$id_b = intval( str_replace( 'wlgf_form_', '', $b->option_name ) );
		return $id_a <=> $id_b;
	} );
}

// Calculate overall KPI metrics
$wlgf_total_forms = 0;
$wlgf_total_views = 0;
$wlgf_total_leads = 0;

if ( is_array( $wlgf_all_forms ) ) {
	foreach ( $wlgf_all_forms as $wlgf_f_obj ) {
		$wlgf_f_raw = get_option( $wlgf_f_obj->option_name );
		if ( ! is_array( $wlgf_f_raw ) || empty( $wlgf_f_raw['form_id'] ) ) {
			continue;
		}
		$wlgf_total_forms++;
		$wlgf_f_id        = sanitize_text_field( $wlgf_f_raw['form_id'] );
		$wlgf_total_views += intval( get_option( 'wlgf_form_views_' . $wlgf_f_id, 0 ) );
		$wlgf_saved_leads = get_option( 'wlgf_saved_form_data_' . $wlgf_f_id, array() );
		$wlgf_total_leads += is_array( $wlgf_saved_leads ) ? count( $wlgf_saved_leads ) : 0;
	}
}
$wlgf_avg_conv_rate = $wlgf_total_views > 0 ? round( ( $wlgf_total_leads / $wlgf_total_views ) * 100, 1 ) : ( $wlgf_total_leads > 0 ? 100 : 0 );
?>

<div class="wlgf-admin-wrap">
	<!-- Page Header -->
	<header class="wlgf-header">
		<div class="wlgf-header__main">
			<div class="wlgf-header__icon">
				<i class="fa-solid fa-list-check"></i>
			</div>
			<div class="wlgf-header__titles">
				<h1 class="wlgf-header__title"><?php esc_html_e( 'Manage Forms', 'lead-generation-form' ); ?></h1>
				<p class="wlgf-header__subtitle"><?php esc_html_e( 'View, clone, edit, inspect conversion rates, or copy shortcodes for your published forms.', 'lead-generation-form' ); ?></p>
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
		</div>
	</header>

	<!-- KPI Metric Cards Row -->
	<section class="wlgf-grid wlgf-grid--4 wlgf-mb-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
		<div class="wlgf-stat-card">
			<div class="wlgf-stat-card__icon wlgf-stat-card__icon--primary">
				<i class="fa-solid fa-file-lines"></i>
			</div>
			<div class="wlgf-stat-card__body">
				<h3 class="wlgf-stat-card__value" id="wlgf-total-forms-stat"><?php echo esc_html( (string) $wlgf_total_forms ); ?></h3>
				<p class="wlgf-stat-card__label"><?php esc_html_e( 'Total Forms', 'lead-generation-form' ); ?></p>
			</div>
		</div>

		<div class="wlgf-stat-card">
			<div class="wlgf-stat-card__icon wlgf-stat-card__icon--info">
				<i class="fa-regular fa-eye"></i>
			</div>
			<div class="wlgf-stat-card__body">
				<h3 class="wlgf-stat-card__value"><?php echo esc_html( number_format_i18n( $wlgf_total_views ) ); ?></h3>
				<p class="wlgf-stat-card__label"><?php esc_html_e( 'Total Impressions', 'lead-generation-form' ); ?></p>
			</div>
		</div>

		<div class="wlgf-stat-card">
			<div class="wlgf-stat-card__icon wlgf-stat-card__icon--success">
				<i class="fa-solid fa-users"></i>
			</div>
			<div class="wlgf-stat-card__body">
				<h3 class="wlgf-stat-card__value"><?php echo esc_html( number_format_i18n( $wlgf_total_leads ) ); ?></h3>
				<p class="wlgf-stat-card__label"><?php esc_html_e( 'Captured Leads', 'lead-generation-form' ); ?></p>
			</div>
		</div>

		<div class="wlgf-stat-card">
			<div class="wlgf-stat-card__icon wlgf-stat-card__icon--warning">
				<i class="fa-solid fa-chart-line"></i>
			</div>
			<div class="wlgf-stat-card__body">
				<h3 class="wlgf-stat-card__value"><?php echo esc_html( $wlgf_avg_conv_rate . '%' ); ?></h3>
				<p class="wlgf-stat-card__label"><?php esc_html_e( 'Avg. Conversion Rate', 'lead-generation-form' ); ?></p>
			</div>
		</div>
	</section>

	<!-- Forms Table Container -->
	<div class="wlgf-table-wrap">
		<table id="forms-tables" class="wlgf-table wlgf-w-full">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'lead-generation-form' ); ?></th>
					<th><?php esc_html_e( 'Form Name & Display Mode', 'lead-generation-form' ); ?></th>
					<th><?php esc_html_e( 'Views', 'lead-generation-form' ); ?></th>
					<th><?php esc_html_e( 'Submissions', 'lead-generation-form' ); ?></th>
					<th><?php esc_html_e( 'Conversion Rate', 'lead-generation-form' ); ?></th>
					<th><?php esc_html_e( 'Created', 'lead-generation-form' ); ?></th>
					<th><?php esc_html_e( 'Last Updated', 'lead-generation-form' ); ?></th>
					<th><?php esc_html_e( 'Shortcode', 'lead-generation-form' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'lead-generation-form' ); ?></th>
				</tr>
			</thead>
			<tbody id="wlgf-tbody">
				<?php
				if ( is_array( $wlgf_all_forms ) ) {
					foreach ( $wlgf_all_forms as $wlgf_form ) {
						$wlgf_form_key       = $wlgf_form->option_name;
						$wlgf_form_data      = get_option( $wlgf_form_key );
						if ( ! is_array( $wlgf_form_data ) || empty( $wlgf_form_data['form_id'] ) ) {
							continue;
						}
						$wlgf_form_id        = sanitize_text_field( $wlgf_form_data['form_id'] );
						$wlgf_form_name      = ! empty( $wlgf_form_data['form_name'] ) ? sanitize_text_field( $wlgf_form_data['form_name'] ) : ( 'Form #' . $wlgf_form_id );
						$wlgf_form_shortcode = "[WLFG id='{$wlgf_form_id}']";

						$wlgf_views       = intval( get_option( 'wlgf_form_views_' . $wlgf_form_id, 0 ) );
						$wlgf_leads_array = get_option( 'wlgf_saved_form_data_' . $wlgf_form_id, array() );
						$wlgf_submissions = is_array( $wlgf_leads_array ) ? count( $wlgf_leads_array ) : 0;
						$wlgf_conv_rate   = $wlgf_views > 0 ? round( ( $wlgf_submissions / $wlgf_views ) * 100, 1 ) : ( $wlgf_submissions > 0 ? 100 : 0 );
						$wlgf_badge_class = $wlgf_conv_rate >= 10 ? 'wlgf-badge--success' : ( $wlgf_conv_rate > 0 ? 'wlgf-badge--primary' : 'wlgf-badge--muted' );
						$wlgf_dates       = wlgf_get_form_dates( $wlgf_form_id, $wlgf_form_data );

						// Display Mode metadata
						$mode = ! empty( $wlgf_form_data['display_mode'] ) ? $wlgf_form_data['display_mode'] : 'inline';
						$wlgf_mode_meta = array(
							'inline'          => array( 'label' => __( 'Inline Embed', 'lead-generation-form' ), 'badge' => 'wlgf-badge--muted', 'icon' => 'fa-code' ),
							'popup'           => array( 'label' => __( 'Lightbox Popup', 'lead-generation-form' ), 'badge' => 'wlgf-badge--primary', 'icon' => 'fa-window-restore' ),
							'slidein'         => array( 'label' => __( 'Slide-in', 'lead-generation-form' ), 'badge' => 'wlgf-badge--info', 'icon' => 'fa-square-caret-up' ),
							'sticky_bar'      => array( 'label' => __( 'Sticky Bar', 'lead-generation-form' ), 'badge' => 'wlgf-badge--warning', 'icon' => 'fa-window-maximize' ),
							'floating_widget' => array( 'label' => __( 'Floating Widget', 'lead-generation-form' ), 'badge' => 'wlgf-badge--success', 'icon' => 'fa-comment-dots' ),
						);
						$wlgf_curr_m = isset( $wlgf_mode_meta[ $mode ] ) ? $wlgf_mode_meta[ $mode ] : $wlgf_mode_meta['inline'];
						?>
						<tr id="<?php echo esc_attr( $wlgf_form_id ); ?>">
							<td data-order="<?php echo esc_attr( intval( $wlgf_form_id ) ); ?>">
								<span class="wlgf-badge wlgf-badge--muted">#<?php echo esc_html( $wlgf_form_id ); ?></span>
							</td>
							<td>
								<div style="display: flex; flex-direction: column; gap: 4px;">
									<strong style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $wlgf_form_name ); ?></strong>
									<div>
										<span class="wlgf-badge <?php echo esc_attr( $wlgf_curr_m['badge'] ); ?>" style="font-size: 11px; padding: 2px 7px;">
											<i class="fa-solid <?php echo esc_attr( $wlgf_curr_m['icon'] ); ?>"></i> <?php echo esc_html( $wlgf_curr_m['label'] ); ?>
										</span>
									</div>
								</div>
							</td>
							<td data-order="<?php echo esc_attr( intval( $wlgf_views ) ); ?>">
								<span class="wlgf-badge wlgf-badge--muted"><i class="fa-regular fa-eye"></i> <?php echo esc_html( number_format_i18n( $wlgf_views ) ); ?></span>
							</td>
							<td data-order="<?php echo esc_attr( intval( $wlgf_submissions ) ); ?>">
								<span class="wlgf-badge wlgf-badge--muted"><i class="fa-solid fa-users"></i> <?php echo esc_html( number_format_i18n( $wlgf_submissions ) ); ?></span>
							</td>
							<td data-order="<?php echo esc_attr( floatval( $wlgf_conv_rate ) ); ?>">
								<span class="wlgf-badge <?php echo esc_attr( $wlgf_badge_class ); ?>"><i class="fa-solid fa-chart-line"></i> <?php echo esc_html( $wlgf_conv_rate . '%' ); ?></span>
							</td>
							<td data-order="<?php echo esc_attr( $wlgf_dates['created_time'] ); ?>">
								<div class="wlgf-date-cell">
									<span class="wlgf-date-main"><i class="fa-regular fa-calendar-plus"></i> <?php echo esc_html( $wlgf_dates['created_formatted'] ); ?></span>
									<span class="wlgf-date-sub"><?php echo esc_html( $wlgf_dates['created_time_fmt'] ); ?></span>
								</div>
							</td>
							<td data-order="<?php echo esc_attr( $wlgf_dates['updated_time'] ); ?>">
								<div class="wlgf-date-cell">
									<span class="wlgf-date-main"><i class="fa-solid fa-clock-rotate-left"></i> <?php echo esc_html( $wlgf_dates['updated_formatted'] ); ?></span>
									<span class="wlgf-date-sub"><?php echo esc_html( $wlgf_dates['updated_human'] ); ?></span>
								</div>
							</td>
							<td>
								<div class="wlgf-code-pill">
									<code class="wlgf-code-pill__text">[WLFG id="<?php echo esc_attr( $wlgf_form_id ); ?>"]</code>
									<button type="button" class="wlgf-code-pill__copy-btn" onclick="return WLGF('<?php echo esc_attr( $wlgf_form_id ); ?>', 'copy');" title="<?php esc_attr_e( 'Copy Shortcode', 'lead-generation-form' ); ?>">
										<i class="fa-regular fa-copy"></i>
									</button>
									<span id="wlgf-copied-<?php echo esc_attr( $wlgf_form_id ); ?>" class="wlgf-code-pill__copied-tooltip"><?php esc_html_e( 'Copied!', 'lead-generation-form' ); ?></span>
								</div>
							</td>
							<td>
								<div class="wlgf-btn-group">
									<button type="button" onclick="return WLGF('<?php echo esc_attr( $wlgf_form_id ); ?>', 'clone', this);" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline" title="<?php esc_attr_e( 'Clone Form', 'lead-generation-form' ); ?>">
										<i class="fa-regular fa-clone"></i> <?php esc_html_e( 'Clone', 'lead-generation-form' ); ?>
									</button>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-form-generator&form-id=' . $wlgf_form_id . '&modify-nonce=' . wp_create_nonce( 'wlgf-modify-form' ) ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary" title="<?php esc_attr_e( 'Edit Form', 'lead-generation-form' ); ?>">
										<i class="fa-solid fa-pen-to-square"></i> <?php esc_html_e( 'Edit', 'lead-generation-form' ); ?>
									</a>
									<button type="button" onclick="return WLGF('<?php echo esc_attr( $wlgf_form_id ); ?>', 'delete', this);" class="wlgf-btn wlgf-btn--sm wlgf-btn--danger" title="<?php esc_attr_e( 'Delete Form', 'lead-generation-form' ); ?>">
										<i class="fa-regular fa-trash-can"></i> <?php esc_html_e( 'Delete', 'lead-generation-form' ); ?>
									</button>
								</div>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</div>

	<!-- FAQ & Tips Card -->
	<div class="wlgf-card">
		<div class="wlgf-card__header">
			<h2 class="wlgf-card__title">
				<i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'Frequently Asked Questions', 'lead-generation-form' ); ?>
			</h2>
		</div>
		<div class="wlgf-card__body">
			<div class="wlgf-steps">
				<div class="wlgf-step">
					<span class="wlgf-step__num"><i class="fa-solid fa-clone"></i></span>
					<div class="wlgf-step__content">
						<strong class="wlgf-step__title"><?php esc_html_e( 'How does cloning a form work?', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-step__desc"><?php esc_html_e( 'Cloning creates an exact duplicate of the form fields, labels, and settings with a new unique Form ID so you can tweak it without affecting the original.', 'lead-generation-form' ); ?></span>
					</div>
				</div>
				<div class="wlgf-step">
					<span class="wlgf-step__num"><i class="fa-solid fa-triangle-exclamation"></i></span>
					<div class="wlgf-step__content">
						<strong class="wlgf-step__title"><?php esc_html_e( 'What happens when I delete a form?', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-step__desc"><?php esc_html_e( 'Deleting a form permanently removes the form configuration and all captured leads associated with that Form ID.', 'lead-generation-form' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Delete Confirmation Modal -->
	<div id="wlgf-delete-modal" class="wlgf-modal-overlay">
		<div class="wlgf-modal">
			<div class="wlgf-modal__header">
				<h3 class="wlgf-modal__title">
					<i class="fa-solid fa-triangle-exclamation wlgf-text-danger wlgf-mr-2"></i>
					<?php esc_html_e( 'Confirm Form Deletion', 'lead-generation-form' ); ?>
				</h3>
			</div>
			<div class="wlgf-modal__body">
				<p id="wlgf-delete-modal-msg"></p>
				<div class="wlgf-alert wlgf-alert--danger">
					<i class="fa-solid fa-circle-exclamation"></i>
					<span><?php esc_html_e( 'All captured leads and submission records for this form will be permanently deleted.', 'lead-generation-form' ); ?></span>
				</div>
			</div>
			<div class="wlgf-modal__footer">
				<button type="button" id="wlgf-cancel-delete-btn" class="wlgf-btn wlgf-btn--secondary">
					<?php esc_html_e( 'Cancel', 'lead-generation-form' ); ?>
				</button>
				<button type="button" id="wlgf-confirm-delete-btn" class="wlgf-btn wlgf-btn--danger">
					<i class="fa-regular fa-trash-can"></i> <?php esc_html_e( 'Delete Form', 'lead-generation-form' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Clone Success Confirmation Modal -->
	<div id="wlgf-clone-modal" class="wlgf-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="wlgf-clone-modal-title">
		<div class="wlgf-modal wlgf-clone-modal-card">
			<div class="wlgf-modal__header wlgf-clone-modal__header">
				<div class="wlgf-clone-modal__header-left">
					<div class="wlgf-clone-modal__icon-wrap">
						<i class="fa-solid fa-clone"></i>
					</div>
					<div>
						<h3 id="wlgf-clone-modal-title" class="wlgf-modal__title">
							<?php esc_html_e( 'Form Cloned Successfully!', 'lead-generation-form' ); ?>
						</h3>
						<span class="wlgf-clone-modal__subtitle" id="wlgf-clone-modal-source"></span>
					</div>
				</div>
				<button type="button" class="wlgf-clone-modal-close-btn" aria-label="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<div class="wlgf-modal__body wlgf-clone-modal__body">
				<div class="wlgf-clone-info-card">
					<div class="wlgf-clone-info-row">
						<span class="wlgf-clone-info-label">
							<i class="fa-regular fa-file-lines"></i> <?php esc_html_e( 'Cloned Form Name', 'lead-generation-form' ); ?>
						</span>
						<div class="wlgf-clone-name-display">
							<span id="wlgf-clone-modal-id-badge" class="wlgf-badge wlgf-badge--primary"></span>
							<strong id="wlgf-clone-modal-name"></strong>
						</div>
					</div>

					<div class="wlgf-clone-info-row">
						<span class="wlgf-clone-info-label">
							<i class="fa-solid fa-code"></i> <?php esc_html_e( 'Form Shortcode', 'lead-generation-form' ); ?>
						</span>
						<div class="wlgf-clone-shortcode-box">
							<code id="wlgf-clone-modal-shortcode"></code>
							<button type="button" id="wlgf-clone-modal-copy-btn" class="wlgf-btn wlgf-btn--sm wlgf-btn--secondary" title="<?php esc_attr_e( 'Copy Shortcode', 'lead-generation-form' ); ?>">
								<i class="fa-regular fa-copy"></i>
								<span id="wlgf-clone-modal-copy-text"><?php esc_html_e( 'Copy Shortcode', 'lead-generation-form' ); ?></span>
							</button>
						</div>
					</div>
				</div>

				<div class="wlgf-alert wlgf-alert--success wlgf-clone-modal__tip">
					<i class="fa-solid fa-circle-check"></i>
					<span><?php esc_html_e( 'The cloned form has been added to your table. You can embed this shortcode anywhere or start customizing its fields immediately.', 'lead-generation-form' ); ?></span>
				</div>
			</div>
			<div class="wlgf-modal__footer wlgf-clone-modal__footer">
				<button type="button" class="wlgf-btn wlgf-btn--secondary wlgf-clone-modal-close-btn">
					<?php esc_html_e( 'Done', 'lead-generation-form' ); ?>
				</button>
				<a id="wlgf-clone-modal-edit-link" href="#" class="wlgf-btn wlgf-btn--primary">
					<i class="fa-solid fa-pen-to-square"></i> <?php esc_html_e( 'Edit Cloned Form', 'lead-generation-form' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>