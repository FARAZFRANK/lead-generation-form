<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

// Page Assets
wp_enqueue_style( 'wlgf-admin-ui-css' );
wp_enqueue_style( 'wlgf-fontawesome-css', plugins_url( 'assets/fontawesome-free-6.4.2-web/css/all.min.css', __FILE__ ), array(), '6.4.2', 'all' );
wp_enqueue_style( 'wlgf-datatables-min-css', plugin_dir_url( __FILE__ ) . 'assets/datatables-2.0.8/datatables.min.css', array(), '2.0.8', 'all' );
wp_enqueue_script( 'wlgf-datatables-min-js', plugin_dir_url( __FILE__ ) . 'assets/datatables-2.0.8/datatables.min.js', array( 'jquery' ), '2.0.8', true );

wp_enqueue_style( 'wlgf-leads-css', plugin_dir_url( __FILE__ ) . 'assets/css/leads.css', array( 'wlgf-admin-ui-css' ), WLGF_PLUGIN_VERSION, 'all' );
wp_enqueue_style( 'wlgf-leads-kanban-css', plugin_dir_url( __FILE__ ) . 'assets/css/leads-kanban.css', array( 'wlgf-leads-css' ), WLGF_PLUGIN_VERSION, 'all' );
$wlgf_leads_js_ver = file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/leads.js' ) ? WLGF_PLUGIN_VERSION . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/leads.js' ) : WLGF_PLUGIN_VERSION;
wp_enqueue_script( 'wlgf-leads-js', plugin_dir_url( __FILE__ ) . 'assets/js/leads.js', array( 'jquery', 'wlgf-datatables-min-js', 'wlgf-dialog-js' ), $wlgf_leads_js_ver, true );
if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
	wp_enqueue_script( 'wlgf-leads-kanban-js', plugin_dir_url( __FILE__ ) . 'assets/js/leads-kanban.js', array( 'jquery', 'wlgf-leads-js' ), WLGF_PLUGIN_VERSION, true );
}

wp_add_inline_script(
	'wlgf-leads-js',
	'const Leads = ' . wp_json_encode(
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wlgf-leads' ),
		)
	),
	'before'
);

// Fetch all forms
global $wpdb;
$wlgf_options_table_name = "{$wpdb->prefix}options";

$wlgf_all_forms = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT option_name FROM `$wlgf_options_table_name` WHERE `option_name` LIKE %s AND `option_name` NOT LIKE %s ORDER BY option_id ASC",
		$wpdb->esc_like( 'wlgf_form_' ) . '%',
		$wpdb->esc_like( 'wlgf_form_views_' ) . '%'
	)
);
?>

<div class="wlgf-admin-wrap">
	<!-- Page Header -->
	<header class="wlgf-header">
		<div class="wlgf-header__main">
			<div class="wlgf-header__icon">
				<i class="fa-solid fa-users-viewfinder"></i>
			</div>
			<div class="wlgf-header__titles">
				<h1 class="wlgf-header__title"><?php esc_html_e( 'Captured Leads', 'lead-generation-form' ); ?></h1>
				<p class="wlgf-header__subtitle"><?php esc_html_e( 'Filter, manage, inspect entries, and export captured lead submissions.', 'lead-generation-form' ); ?></p>
			</div>
		</div>
		<div class="wlgf-header__actions">
			<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--secondary" style="background: #fffbeb; border-color: #fde68a; color: #92400e; font-weight: 700; text-decoration: none;">
					<i class="fa-solid fa-crown" style="color: #f59e0b;"></i> <?php esc_html_e( 'Free vs Pro', 'lead-generation-form' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</header>

	<!-- Form Selector Card -->
	<div class="wlgf-card" style="overflow: visible;">
		<div class="wlgf-card__header">
			<h2 class="wlgf-card__title">
				<i class="fa-solid fa-filter"></i> <?php esc_html_e( 'Select Form to View Leads', 'lead-generation-form' ); ?>
			</h2>
		</div>
		<div class="wlgf-card__body">
			<?php
			$forms_list_for_dropdown = array();
			$preselected_form_id     = isset( $_GET['form_id'] ) ? sanitize_text_field( wp_unslash( $_GET['form_id'] ) ) : '';
			$selected_form_label     = __( '-- Choose a Form to Access Leads --', 'lead-generation-form' );

			if ( is_array( $wlgf_all_forms ) ) {
				foreach ( $wlgf_all_forms as $wlgf_form ) {
					$wlgf_form_key  = $wlgf_form->option_name;
					$wlgf_form_data = get_option( $wlgf_form_key );
					if ( ! is_array( $wlgf_form_data ) || empty( $wlgf_form_data['form_id'] ) ) {
						continue;
					}
					$f_id   = sanitize_text_field( $wlgf_form_data['form_id'] );
					$f_name = ! empty( $wlgf_form_data['form_name'] ) ? sanitize_text_field( $wlgf_form_data['form_name'] ) : ( 'Form #' . $f_id );
					$forms_list_for_dropdown[] = array(
						'id'   => $f_id,
						'name' => $f_name,
					);
					if ( (string) $preselected_form_id === (string) $f_id ) {
						$selected_form_label = $f_name . ' (ID: #' . $f_id . ')';
					}
				}
			}
			?>
			<div class="wlgf-form-group wlgf-mb-0">
				<div class="wlgf-flex wlgf-flex--between wlgf-items-center wlgf-mb-1">
					<label for="wlgf-form-list" class="wlgf-label wlgf-m-0">
						<i class="fa-solid fa-list-check wlgf-text-primary"></i> <?php esc_html_e( 'Choose Form:', 'lead-generation-form' ); ?>
					</label>
					<span class="wlgf-text-xs wlgf-text-muted" id="wlgf-form-count-badge">
						<i class="fa-solid fa-layer-group"></i> <?php echo esc_html( count( $forms_list_for_dropdown ) ); ?> <?php esc_html_e( 'Forms', 'lead-generation-form' ); ?>
					</span>
				</div>

				<!-- Hidden Native Select (Kept for full backward compatibility & automated hooks) -->
				<select class="wlgf-select wlgf-w-full" id="wlgf-form-list" name="wlgf-form-list" onchange="return WLGF(this.value, 'load', '');" style="display: none !important;">
					<option value="0"><?php esc_html_e( '-- Choose a Form to Access Leads --', 'lead-generation-form' ); ?></option>
					<?php foreach ( $forms_list_for_dropdown as $f_item ) : ?>
						<option value="<?php echo esc_attr( $f_item['id'] ); ?>" <?php selected( $preselected_form_id, $f_item['id'] ); ?>>
							<?php echo esc_html( $f_item['name'] . ' (ID: #' . $f_item['id'] . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<!-- Custom Searchable Combobox Component -->
				<div class="wlgf-searchable-select" id="wlgf-form-searchable-select">
					<!-- Trigger Button -->
					<div class="wlgf-searchable-select__trigger" id="wlgf-form-search-trigger" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
						<div class="wlgf-searchable-select__trigger-text">
							<i class="fa-solid fa-magnifying-glass wlgf-searchable-select__icon"></i>
							<span class="wlgf-searchable-select__current-label" id="wlgf-form-search-current-label"><?php echo esc_html( $selected_form_label ); ?></span>
						</div>
						<div class="wlgf-searchable-select__trigger-arrow">
							<i class="fa-solid fa-chevron-down"></i>
						</div>
					</div>

					<!-- Dropdown Floating Panel -->
					<div class="wlgf-searchable-select__dropdown" id="wlgf-form-search-dropdown" style="display: none;">
						<!-- Search Input Bar -->
						<div class="wlgf-searchable-select__search-bar">
							<i class="fa-solid fa-magnifying-glass wlgf-searchable-select__search-icon"></i>
							<input type="text" class="wlgf-searchable-select__search-input" id="wlgf-form-search-input" placeholder="<?php esc_attr_e( 'Search form by name or ID (e.g. Real Estate, #3)...', 'lead-generation-form' ); ?>" autocomplete="off" spellcheck="false">
							<button type="button" class="wlgf-searchable-select__search-clear" id="wlgf-form-search-clear" title="<?php esc_attr_e( 'Clear search', 'lead-generation-form' ); ?>" style="display: none;">
								<i class="fa-solid fa-xmark"></i>
							</button>
						</div>

						<!-- Options List -->
						<ul class="wlgf-searchable-select__options" id="wlgf-form-search-options" role="listbox">
							<li class="wlgf-searchable-select__option wlgf-searchable-select__option--default <?php echo ( empty( $preselected_form_id ) || '0' === $preselected_form_id ) ? 'is-selected' : ''; ?>" data-value="0" data-label="<?php esc_attr_e( '-- Choose a Form to Access Leads --', 'lead-generation-form' ); ?>" data-search="" role="option">
								<span class="wlgf-searchable-select__option-name">
									<i class="fa-regular fa-circle-dot wlgf-text-muted"></i> <?php esc_html_e( '-- Choose a Form to Access Leads --', 'lead-generation-form' ); ?>
								</span>
							</li>
							<?php foreach ( $forms_list_for_dropdown as $f_item ) : ?>
								<?php
								$is_sel       = ( (string) $preselected_form_id === (string) $f_item['id'] );
								$option_label = $f_item['name'] . ' (ID: #' . $f_item['id'] . ')';
								?>
								<li class="wlgf-searchable-select__option <?php echo $is_sel ? 'is-selected' : ''; ?>" data-value="<?php echo esc_attr( $f_item['id'] ); ?>" data-label="<?php echo esc_attr( $option_label ); ?>" data-search="<?php echo esc_attr( strtolower( $f_item['name'] . ' ' . $f_item['id'] . ' #' . $f_item['id'] ) ); ?>" role="option">
									<span class="wlgf-searchable-select__option-name">
										<i class="fa-solid fa-file-lines wlgf-searchable-select__option-icon"></i>
										<?php echo esc_html( $f_item['name'] ); ?>
									</span>
									<span class="wlgf-searchable-select__option-badge">
										ID: #<?php echo esc_html( $f_item['id'] ); ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>

						<!-- Empty Search State -->
						<div class="wlgf-searchable-select__empty" id="wlgf-form-search-empty" style="display: none;">
							<div class="wlgf-searchable-select__empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
							<div class="wlgf-searchable-select__empty-text"><?php esc_html_e( 'No matching forms found', 'lead-generation-form' ); ?></div>
							<div class="wlgf-searchable-select__empty-sub"><?php esc_html_e( 'Try typing a different name or form ID number', 'lead-generation-form' ); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Dynamic View Switcher (Table vs Kanban) -->
	<div class="wlgf-flex wlgf-flex--between wlgf-items-center wlgf-mt-3">
		<div class="wlgf-view-switcher">
			<button type="button" class="wlgf-view-btn active" data-view="table">
				<i class="fa-solid fa-table-list"></i> <?php esc_html_e( 'Table View', 'lead-generation-form' ); ?>
			</button>
			<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
				<button type="button" class="wlgf-view-btn" data-view="kanban">
					<i class="fa-solid fa-chart-gantt"></i> <?php esc_html_e( 'Kanban Pipeline', 'lead-generation-form' ); ?>
				</button>
			<?php else : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-view-btn wlgf-view-btn--pro" title="<?php esc_attr_e( 'Kanban Pipeline is available in Pro Edition', 'lead-generation-form' ); ?>">
					<i class="fa-solid fa-chart-gantt"></i> <?php esc_html_e( 'Kanban Pipeline', 'lead-generation-form' ); ?> <span class="wlgf-badge wlgf-badge--warning"><?php esc_html_e( 'PRO', 'lead-generation-form' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
		<div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--sm" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-weight: 700; text-decoration: none;">
				<i class="fa-solid fa-crown" style="color: #f59e0b;"></i> <?php esc_html_e( 'Unlock Kanban & E-Signatures with Pro', 'lead-generation-form' ); ?> &rarr;
			</a>
		</div>
	</div>

	<!-- Modern Loader State -->
	<div id="wlgf-loader-process" class="wlgf-loader-center wlgf-leads-loader-card d-none">
		<div class="wlgf-leads-loader-card__inner">
			<div class="wlgf-leads-loader-card__spinner-wrap">
				<div class="wlgf-leads-loader-card__spinner-ring"></div>
				<i class="fa-solid fa-users-viewfinder wlgf-leads-loader-card__icon"></i>
			</div>
			<div class="wlgf-leads-loader-card__text-wrap">
				<h4 class="wlgf-leads-loader-card__title"><?php esc_html_e( 'Loading Captured Leads...', 'lead-generation-form' ); ?></h4>
				<p class="wlgf-leads-loader-card__desc"><?php esc_html_e( 'Retrieving inquiries, form submission data, and contact records', 'lead-generation-form' ); ?></p>
			</div>
			<div class="wlgf-leads-loader-card__bar-wrap">
				<div class="wlgf-leads-loader-card__bar"></div>
			</div>
			<!-- Shimmer Skeleton Rows -->
			<div class="wlgf-leads-loader-card__skeleton">
				<div class="wlgf-leads-skeleton-row wlgf-leads-skeleton-row--head">
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--sm"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--md"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--lg"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--md"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--sm"></span>
				</div>
				<div class="wlgf-leads-skeleton-row">
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--sm"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--md"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--lg"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--md"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--sm"></span>
				</div>
				<div class="wlgf-leads-skeleton-row">
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--sm"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--md"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--lg"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--md"></span>
					<span class="wlgf-skeleton-cell wlgf-skeleton-cell--sm"></span>
				</div>
			</div>
		</div>
	</div>

	<!-- Dynamic Table Container -->
	<div id="wlgf-table-container" class="wlgf-mt-2">
		<div class="wlgf-empty-state">
			<div class="wlgf-empty-state__icon">
				<i class="fa-solid fa-users-viewfinder"></i>
			</div>
			<h3 class="wlgf-empty-state__title"><?php esc_html_e( 'No Form Selected', 'lead-generation-form' ); ?></h3>
			<p class="wlgf-empty-state__desc">
				<?php esc_html_e( 'Select a form from the dropdown selector above to inspect, search, and export its captured lead inquiries.', 'lead-generation-form' ); ?>
			</p>
		</div>
	</div>

	<!-- Dynamic Kanban Board Container -->
	<div id="wlgf-kanban-container" class="wlgf-mt-2" style="display: none;">
		<div id="wlgf-kanban-empty" class="wlgf-empty-state">
			<div class="wlgf-empty-state__icon">
				<i class="fa-solid fa-chart-gantt"></i>
			</div>
			<h3 class="wlgf-empty-state__title"><?php esc_html_e( 'Select a form above to populate the Kanban pipeline', 'lead-generation-form' ); ?></h3>
			<p class="wlgf-empty-state__desc">
				<?php esc_html_e( 'Choose a form from the dropdown selector above to visualize and manage captured leads across sales pipeline stages.', 'lead-generation-form' ); ?>
			</p>
		</div>

		<div id="wlgf-kanban-board-wrap" class="wlgf-kanban-board" style="display: none;">
			<!-- Column: New Lead -->
			<div class="wlgf-kanban-column wlgf-col-new" data-status="new">
				<div class="wlgf-kanban-col-header">
					<h4 class="wlgf-kanban-col-title"><i class="fa-solid fa-inbox text-primary"></i> <?php esc_html_e( 'New Lead', 'lead-generation-form' ); ?></h4>
					<span class="wlgf-kanban-col-badge" id="wlgf-count-new">0</span>
				</div>
				<div class="wlgf-kanban-cards" id="wlgf-cards-new"></div>
			</div>

			<!-- Column: Contacted -->
			<div class="wlgf-kanban-column wlgf-col-contacted" data-status="contacted">
				<div class="wlgf-kanban-col-header">
					<h4 class="wlgf-kanban-col-title"><i class="fa-solid fa-phone-volume text-info"></i> <?php esc_html_e( 'Contacted', 'lead-generation-form' ); ?></h4>
					<span class="wlgf-kanban-col-badge" id="wlgf-count-contacted">0</span>
				</div>
				<div class="wlgf-kanban-cards" id="wlgf-cards-contacted"></div>
			</div>

			<!-- Column: Follow-up -->
			<div class="wlgf-kanban-column wlgf-col-followup" data-status="followup">
				<div class="wlgf-kanban-col-header">
					<h4 class="wlgf-kanban-col-title"><i class="fa-solid fa-clock text-warning"></i> <?php esc_html_e( 'Follow-Up', 'lead-generation-form' ); ?></h4>
					<span class="wlgf-kanban-col-badge" id="wlgf-count-followup">0</span>
				</div>
				<div class="wlgf-kanban-cards" id="wlgf-cards-followup"></div>
			</div>

			<!-- Column: Qualified -->
			<div class="wlgf-kanban-column wlgf-col-qualified" data-status="qualified">
				<div class="wlgf-kanban-col-header">
					<h4 class="wlgf-kanban-col-title"><i class="fa-solid fa-star text-purple" style="color: #8b5cf6;"></i> <?php esc_html_e( 'Qualified', 'lead-generation-form' ); ?></h4>
					<span class="wlgf-kanban-col-badge" id="wlgf-count-qualified">0</span>
				</div>
				<div class="wlgf-kanban-cards" id="wlgf-cards-qualified"></div>
			</div>

			<!-- Column: Converted -->
			<div class="wlgf-kanban-column wlgf-col-converted" data-status="converted">
				<div class="wlgf-kanban-col-header">
					<h4 class="wlgf-kanban-col-title"><i class="fa-solid fa-trophy text-success"></i> <?php esc_html_e( 'Won / Converted', 'lead-generation-form' ); ?></h4>
					<span class="wlgf-kanban-col-badge" id="wlgf-count-converted">0</span>
				</div>
				<div class="wlgf-kanban-cards" id="wlgf-cards-converted"></div>
			</div>

			<!-- Column: Abandoned -->
			<div class="wlgf-kanban-column wlgf-col-abandoned" data-status="abandoned">
				<div class="wlgf-kanban-col-header">
					<h4 class="wlgf-kanban-col-title"><i class="fa-solid fa-triangle-exclamation text-danger"></i> <?php esc_html_e( 'Abandoned / Draft', 'lead-generation-form' ); ?></h4>
					<span class="wlgf-kanban-col-badge" id="wlgf-count-abandoned">0</span>
				</div>
				<div class="wlgf-kanban-cards" id="wlgf-cards-abandoned"></div>
			</div>
		</div>
	</div>

	<!-- FAQ Card -->
	<div class="wlgf-card wlgf-mt-4">
		<div class="wlgf-card__header">
			<h2 class="wlgf-card__title">
				<i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'Leads Management Guide & Tips', 'lead-generation-form' ); ?>
			</h2>
		</div>
		<div class="wlgf-card__body">
			<div class="wlgf-steps">
				<div class="wlgf-step">
					<span class="wlgf-step__num"><i class="fa-solid fa-chart-gantt"></i></span>
					<div class="wlgf-step__content">
						<strong class="wlgf-step__title"><?php esc_html_e( 'Visual Kanban Pipeline (Mini-CRM)', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-step__desc"><?php esc_html_e( 'Switch from Table View to "Kanban Pipeline" to visualize your sales funnel. Each captured inquiry becomes an interactive card with lead details and quick actions (1-click WhatsApp Chat, Direct Phone Call, PDF Summary Download). Easily drag-and-drop cards across the 6 checkpoint stages (New Lead, Contacted, Follow-Up, Qualified, Won / Converted, Abandoned / Draft)—all status updates save automatically in real time without refreshing.', 'lead-generation-form' ); ?></span>
					</div>
				</div>
				<div class="wlgf-step">
					<span class="wlgf-step__num"><i class="fa-solid fa-magnifying-glass"></i></span>
					<div class="wlgf-step__content">
						<strong class="wlgf-step__title"><?php esc_html_e( 'Searching & Sorting Leads', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-step__desc"><?php esc_html_e( 'Use the real-time search box above the table to search any field (name, email, phone). Click any column header to sort in ascending or descending order.', 'lead-generation-form' ); ?></span>
					</div>
				</div>
				<div class="wlgf-step">
					<span class="wlgf-step__num"><i class="fa-solid fa-file-csv"></i></span>
					<div class="wlgf-step__content">
						<strong class="wlgf-step__title"><?php esc_html_e( 'Instant CSV Export', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-step__desc"><?php esc_html_e( 'Click "Download All Leads (CSV)" to stream a fully formatted, RFC-4180 compliant CSV spreadsheet ready for Microsoft Excel, Google Sheets, or CRM import.', 'lead-generation-form' ); ?></span>
					</div>
				</div>
				<div class="wlgf-step">
					<span class="wlgf-step__num"><i class="fa-solid fa-trash-can"></i></span>
					<div class="wlgf-step__content">
						<strong class="wlgf-step__title"><?php esc_html_e( 'Bulk Deletion', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-step__desc"><?php esc_html_e( 'Use the checkboxes to select multiple entries and click the red trash icon in the table header to delete multiple leads simultaneously.', 'lead-generation-form' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Delete Confirmation Modal Component -->
	<div id="wlgf-lead-delete-modal" class="wlgf-modal-overlay wlgf-d-none">
		<div class="wlgf-modal">
			<div class="wlgf-modal__header">
				<h3 class="wlgf-modal__title">
					<i class="fa-solid fa-triangle-exclamation wlgf-text-danger"></i> <?php esc_html_e( 'Confirm Lead Deletion', 'lead-generation-form' ); ?>
				</h3>
				<button type="button" class="wlgf-modal__close wlgf-modal__close-btn" id="wlgf-modal-close-btn" title="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>" aria-label="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<div class="wlgf-modal__body">
				<p id="wlgf-modal-message">
					<?php esc_html_e( 'Are you sure you want to permanently delete this lead record? This action cannot be undone.', 'lead-generation-form' ); ?>
				</p>
			</div>
			<div class="wlgf-modal__footer">
				<button type="button" id="wlgf-modal-cancel-btn" class="wlgf-btn wlgf-btn--secondary">
					<?php esc_html_e( 'Cancel', 'lead-generation-form' ); ?>
				</button>
				<button type="button" id="wlgf-modal-confirm-delete" class="wlgf-btn wlgf-btn--danger">
					<i class="fa-solid fa-trash-can"></i> <?php esc_html_e( 'Yes, Delete Permanently', 'lead-generation-form' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Internal Notes Modal (PRO-011) -->
	<div id="wlgf-lead-notes-modal" class="wlgf-modal-overlay wlgf-d-none">
		<div class="wlgf-modal">
			<div class="wlgf-modal__header">
				<h3 class="wlgf-modal__title">
					<i class="fa-regular fa-note-sticky wlgf-text-primary"></i> <?php esc_html_e( 'Lead Internal Notes & Follow-up', 'lead-generation-form' ); ?>
				</h3>
				<button type="button" class="wlgf-modal__close wlgf-modal__close-btn" id="wlgf-notes-modal-close-btn" title="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>" aria-label="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<div class="wlgf-modal__body">
				<div id="wlgf-lead-notes-list" class="wlgf-notes-list">
					<!-- Dynamically injected note items -->
				</div>
				<div class="wlgf-form-group">
					<label for="wlgf-new-note-text" class="wlgf-label"><?php esc_html_e( 'Add Internal Note / Follow-up Comment:', 'lead-generation-form' ); ?></label>
					<textarea id="wlgf-new-note-text" class="wlgf-textarea" rows="3" placeholder="<?php esc_attr_e( 'Spoke with client on phone, interested in enterprise package...', 'lead-generation-form' ); ?>"></textarea>
				</div>
			</div>
			<div class="wlgf-modal__footer">
				<button type="button" id="wlgf-notes-modal-cancel-btn" class="wlgf-btn wlgf-btn--secondary">
					<?php esc_html_e( 'Close', 'lead-generation-form' ); ?>
				</button>
				<button type="button" id="wlgf-add-note-btn" class="wlgf-btn wlgf-btn--primary">
					<i class="fa-solid fa-plus"></i> <?php esc_html_e( 'Save Note', 'lead-generation-form' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Digital Signature Preview Lightbox Modal -->
	<div id="wlgf-signature-preview-modal" class="wlgf-modal-overlay wlgf-d-none" role="dialog" aria-modal="true">
		<div class="wlgf-modal" style="max-width: 560px; width: 95%;">
			<div class="wlgf-modal__header" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--wlgf-border);">
				<h3 class="wlgf-modal__title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--wlgf-text-main); display: flex; align-items: center; gap: 8px;">
					<i class="fa-solid fa-file-signature wlgf-text-primary"></i> <?php esc_html_e( 'Verified Digital Signature', 'lead-generation-form' ); ?>
				</h3>
				<button type="button" class="wlgf-modal__close wlgf-modal__close-btn" id="wlgf-signature-modal-close-btn" title="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>" aria-label="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<div class="wlgf-modal__body" style="padding: 24px; text-align: center; background: #f8fafc;">
				<div class="wlgf-signature-preview-card" style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); margin-bottom: 16px;">
					<img id="wlgf-signature-preview-img" src="" alt="<?php esc_attr_e( 'Digital Signature', 'lead-generation-form' ); ?>" style="max-width: 100%; max-height: 220px; height: auto; display: block; margin: 0 auto; filter: contrast(1.1);">
				</div>
				<div style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; color: #059669; font-weight: 600; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 20px; padding: 6px 16px;">
					<i class="fa-solid fa-circle-check"></i> <?php esc_html_e( 'Digitally Signed & Verified via Touch/Mouse Canvas', 'lead-generation-form' ); ?>
				</div>
			</div>
			<div class="wlgf-modal__footer" style="padding: 12px 20px; border-top: 1px solid var(--wlgf-border); display: flex; justify-content: flex-end; gap: 10px; background: #ffffff;">
				<a id="wlgf-signature-download-btn" href="" download="digital-signature.png" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm">
					<i class="fa-solid fa-download"></i> <?php esc_html_e( 'Download PNG', 'lead-generation-form' ); ?>
				</a>
				<button type="button" id="wlgf-signature-modal-close-action" class="wlgf-btn wlgf-btn--secondary wlgf-btn--sm">
					<?php esc_html_e( 'Close', 'lead-generation-form' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Media / File Upload Preview Lightbox Modal -->
	<div id="wlgf-media-lightbox-modal" class="wlgf-modal-overlay wlgf-d-none" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Media Preview', 'lead-generation-form' ); ?>">
		<div class="wlgf-modal wlgf-lightbox-modal">
			<div class="wlgf-modal__header wlgf-lightbox-header">
				<div class="wlgf-lightbox-title-wrap">
					<div class="wlgf-lightbox-icon-wrap" id="wlgf-lightbox-icon-wrap">
						<i class="fa-solid fa-image" id="wlgf-lightbox-file-icon"></i>
					</div>
					<div>
						<h3 class="wlgf-modal__title wlgf-lightbox-title" id="wlgf-lightbox-title"><?php esc_html_e( 'Attachment Preview', 'lead-generation-form' ); ?></h3>
						<span class="wlgf-lightbox-filename" id="wlgf-lightbox-filename"></span>
					</div>
				</div>
				<div class="wlgf-lightbox-header-actions">
					<a id="wlgf-lightbox-external-btn" href="#" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm" title="<?php esc_attr_e( 'Open in new tab', 'lead-generation-form' ); ?>">
						<i class="fa-solid fa-arrow-up-right-from-square"></i>
					</a>
					<button type="button" class="wlgf-modal__close wlgf-modal__close-btn" id="wlgf-lightbox-modal-close-btn" title="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>" aria-label="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>">
						<i class="fa-solid fa-xmark"></i>
					</button>
				</div>
			</div>
			<div class="wlgf-modal__body wlgf-lightbox-body" id="wlgf-lightbox-body">
				<!-- Dynamically injected preview element -->
			</div>
			<div class="wlgf-modal__footer wlgf-lightbox-footer">
				<div class="wlgf-lightbox-footer-info" id="wlgf-lightbox-footer-info">
					<span class="wlgf-badge wlgf-badge--muted" id="wlgf-lightbox-ext-badge"></span>
				</div>
				<div class="wlgf-flex wlgf-gap-2">
					<a id="wlgf-lightbox-download-btn" href="#" download class="wlgf-btn wlgf-btn--primary wlgf-btn--sm">
						<i class="fa-solid fa-download"></i> <?php esc_html_e( 'Download File', 'lead-generation-form' ); ?>
					</a>
					<button type="button" id="wlgf-lightbox-modal-close-action" class="wlgf-btn wlgf-btn--secondary wlgf-btn--sm">
						<?php esc_html_e( 'Close', 'lead-generation-form' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Single Lead Details Preview Lightbox Modal -->
	<div id="wlgf-lead-details-modal" class="wlgf-modal-overlay wlgf-d-none" role="dialog" aria-modal="true">
		<div class="wlgf-modal" style="max-width: 800px; width: 95%; max-height: 92vh; display: flex; flex-direction: column;">
			<div class="wlgf-modal__header" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 22px; border-bottom: 1px solid var(--wlgf-border); flex-shrink: 0;">
				<h3 class="wlgf-modal__title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--wlgf-text-main); display: flex; align-items: center; gap: 8px;">
					<i class="fa-solid fa-address-card wlgf-text-primary"></i> <span id="wlgf-lead-details-modal-title"><?php esc_html_e( 'Lead Submission Details', 'lead-generation-form' ); ?></span>
				</h3>
				<button type="button" class="wlgf-modal__close wlgf-modal__close-btn" id="wlgf-lead-details-modal-close-btn" title="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>" aria-label="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<div class="wlgf-modal__body" id="wlgf-lead-details-modal-body" style="padding: 22px; overflow-y: auto; flex: 1; background: #f8fafc;">
				<!-- Dynamic content loaded via AJAX -->
			</div>
			<div class="wlgf-modal__footer" style="padding: 14px 22px; border-top: 1px solid var(--wlgf-border); display: flex; justify-content: space-between; align-items: center; gap: 10px; background: #ffffff; flex-shrink: 0;">
				<div class="wlgf-flex wlgf-gap-2">
					<button type="button" id="wlgf-print-lead-btn" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm">
						<i class="fa-solid fa-print"></i> <?php esc_html_e( 'Print Lead', 'lead-generation-form' ); ?>
					</button>
					<a id="wlgf-details-pdf-btn" href="#" target="_blank" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm" title="<?php esc_attr_e( 'Download PDF Summary [Pro Feature]', 'lead-generation-form' ); ?>">
						<i class="fa-solid fa-file-pdf wlgf-text-danger"></i> <?php esc_html_e( 'Download PDF', 'lead-generation-form' ); ?><?php if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) : ?> <span class="wlgf-badge wlgf-badge--warning" style="font-size: 9px; padding: 2px 5px; margin-left: 4px;">PRO</span><?php endif; ?>
					</a>
				</div>
				<div class="wlgf-flex wlgf-gap-2" style="align-items: center;">
					<button type="button" id="wlgf-lead-prev-btn" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm" title="<?php esc_attr_e( 'Previous Lead', 'lead-generation-form' ); ?>" disabled>
						<i class="fa-solid fa-chevron-left"></i> <?php esc_html_e( 'Prev Lead', 'lead-generation-form' ); ?>
					</button>
					<button type="button" id="wlgf-lead-next-btn" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm" title="<?php esc_attr_e( 'Next Lead', 'lead-generation-form' ); ?>" disabled>
						<?php esc_html_e( 'Next Lead', 'lead-generation-form' ); ?> <i class="fa-solid fa-chevron-right"></i>
					</button>
					<button type="button" id="wlgf-lead-details-modal-close-action" class="wlgf-btn wlgf-btn--secondary wlgf-btn--sm">
						<?php esc_html_e( 'Close', 'lead-generation-form' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>