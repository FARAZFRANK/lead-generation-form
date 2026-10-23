<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

// Page Assets
wp_enqueue_style( 'wlgf-admin-ui-css' );
wp_enqueue_style( 'wlgf-fontawesome-css', plugins_url( 'assets/fontawesome-free-6.4.2-web/css/all.min.css', __FILE__ ), array(), '6.4.2', 'all' );

wp_enqueue_style( 'wlgf-import-export-css', plugin_dir_url( __FILE__ ) . 'assets/css/import-export.css', array( 'wlgf-admin-ui-css' ), WLGF_PLUGIN_VERSION, 'all' );
wp_enqueue_script( 'wlgf-import-export-js', plugin_dir_url( __FILE__ ) . 'assets/js/import-export.js', array( 'jquery' ), WLGF_PLUGIN_VERSION, true );

wp_add_inline_script(
	'wlgf-import-export-js',
	'const ImportExport = ' . wp_json_encode(
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wlgf-import-export' ),
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
				<i class="fa-solid fa-file-import"></i>
			</div>
			<div class="wlgf-header__titles">
				<h1 class="wlgf-header__title"><?php esc_html_e( 'Import, Export & Combine Forms', 'lead-generation-form' ); ?></h1>
				<p class="wlgf-header__subtitle"><?php esc_html_e( 'Transfer form schemas across websites, backup templates, or combine two existing forms into one composite structure.', 'lead-generation-form' ); ?></p>
			</div>
		</div>
		<div class="wlgf-header__actions">
			<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--secondary" style="background: #fffbeb; border-color: #fde68a; color: #92400e; font-weight: 700; text-decoration: none;">
					<i class="fa-solid fa-crown" style="color: #f59e0b;"></i> <?php esc_html_e( 'Free vs Pro', 'lead-generation-form' ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-manage-forms' ) ); ?>" class="wlgf-btn wlgf-btn--outline">
				<i class="fa-solid fa-list-check"></i> <?php esc_html_e( 'Manage Forms', 'lead-generation-form' ); ?>
			</a>
		</div>
	</header>

	<!-- Grid of Actions -->
	<div class="wlgf-grid wlgf-grid--3">
		<!-- Export Form Card -->
		<div class="wlgf-card">
			<div class="wlgf-card__header">
				<h2 class="wlgf-card__title">
					<i class="fa-solid fa-file-export"></i> <?php esc_html_e( 'Export Form Schema', 'lead-generation-form' ); ?>
				</h2>
			</div>
			<div class="wlgf-card__body">
				<div class="wlgf-form-group">
					<label for="wlgf-form-list" class="wlgf-label"><?php esc_html_e( 'Select Form to Export', 'lead-generation-form' ); ?></label>
					<select class="wlgf-select wlgf-w-full" id="wlgf-form-list">
						<option value="0"><?php esc_html_e( '-- Choose a Form --', 'lead-generation-form' ); ?></option>
						<?php
						if ( is_array( $wlgf_all_forms ) ) {
							foreach ( $wlgf_all_forms as $wlgf_form ) {
								$wlgf_form_data = get_option( $wlgf_form->option_name );
								if ( ! is_array( $wlgf_form_data ) || empty( $wlgf_form_data['form_id'] ) ) {
									continue;
								}
								$f_id   = sanitize_text_field( $wlgf_form_data['form_id'] );
								$f_name = ! empty( $wlgf_form_data['form_name'] ) ? sanitize_text_field( $wlgf_form_data['form_name'] ) : ( 'Form #' . $f_id );
								?>
								<option value="<?php echo esc_attr( $f_id ); ?>">
									<?php echo esc_html( $f_name . ' (#' . $f_id . ')' ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<button type="button" id="wlgf-export-btn" class="wlgf-btn wlgf-btn--primary wlgf-w-full">
					<i class="fa-solid fa-code"></i> <?php esc_html_e( 'Generate Export Payload', 'lead-generation-form' ); ?>
				</button>

				<div id="wlgf-export-output-wrap" class="wlgf-mt-3 d-none">
					<label class="wlgf-label"><?php esc_html_e( 'Schema JSON Payload:', 'lead-generation-form' ); ?></label>
					<div id="wlgf-form-export" class="wlgf-mb-2"></div>
					<button type="button" id="wlgf-copycode" class="wlgf-btn wlgf-btn--outline wlgf-w-full">
						<i class="fa-regular fa-copy"></i> <?php esc_html_e( 'Copy Payload to Clipboard', 'lead-generation-form' ); ?>
					</button>
					<div id="wlgf-copy-success" class="wlgf-alert wlgf-alert--success wlgf-mt-2 d-none">
						<i class="fa-solid fa-circle-check"></i> <span><?php esc_html_e( 'Copied schema payload to clipboard!', 'lead-generation-form' ); ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Import Form Card -->
		<div class="wlgf-card">
			<div class="wlgf-card__header">
				<h2 class="wlgf-card__title">
					<i class="fa-solid fa-file-import"></i> <?php esc_html_e( 'Import Form Schema', 'lead-generation-form' ); ?>
				</h2>
			</div>
			<div class="wlgf-card__body">
				<div class="wlgf-form-group">
					<label for="wlgf-import-name" class="wlgf-label">
						<?php esc_html_e( 'New Form Name', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span>
					</label>
					<input type="text" class="wlgf-input" id="wlgf-import-name" placeholder="e.g. Imported Customer Inquiry">
				</div>

				<div class="wlgf-form-group">
					<label for="wlgf-import-data" class="wlgf-label">
						<?php esc_html_e( 'Paste JSON Schema Payload', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span>
					</label>
					<textarea class="wlgf-code-box" id="wlgf-import-data" rows="7" placeholder="[{ &quot;type&quot;: &quot;text&quot;, &quot;label&quot;: &quot;Full Name&quot; }]"></textarea>
				</div>

				<button type="button" id="wlgf-import-btn" class="wlgf-btn wlgf-btn--primary wlgf-w-full">
					<i class="fa-solid fa-arrow-down-to-bracket"></i> <?php esc_html_e( 'Import & Create Form', 'lead-generation-form' ); ?>
				</button>
				<div id="wlgf-import-result" class="wlgf-mt-2"></div>
			</div>
		</div>

		<!-- Combine Forms Card -->
		<div class="wlgf-card">
			<div class="wlgf-card__header">
				<h2 class="wlgf-card__title">
					<i class="fa-solid fa-object-group"></i> <?php esc_html_e( 'Combine Two Forms', 'lead-generation-form' ); ?>
				</h2>
			</div>
			<div class="wlgf-card__body">
				<div class="wlgf-form-group">
					<label for="wlgf-form-one" class="wlgf-label"><?php esc_html_e( 'First Form (Top Fields)', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span></label>
					<select class="wlgf-select wlgf-w-full" id="wlgf-form-one">
						<option value="0"><?php esc_html_e( '-- Choose First Form --', 'lead-generation-form' ); ?></option>
						<?php
						if ( is_array( $wlgf_all_forms ) ) {
							foreach ( $wlgf_all_forms as $wlgf_form ) {
								$wlgf_form_data = get_option( $wlgf_form->option_name );
								if ( ! is_array( $wlgf_form_data ) || empty( $wlgf_form_data['form_id'] ) ) {
									continue;
								}
								$f_id   = sanitize_text_field( $wlgf_form_data['form_id'] );
								$f_name = ! empty( $wlgf_form_data['form_name'] ) ? sanitize_text_field( $wlgf_form_data['form_name'] ) : ( 'Form #' . $f_id );
								?>
								<option value="<?php echo esc_attr( $f_id ); ?>">
									<?php echo esc_html( $f_name ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
				</div>

				<div class="wlgf-form-group">
					<label for="wlgf-form-two" class="wlgf-label"><?php esc_html_e( 'Second Form (Bottom Fields)', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span></label>
					<select class="wlgf-select wlgf-w-full" id="wlgf-form-two">
						<option value="0"><?php esc_html_e( '-- Choose Second Form --', 'lead-generation-form' ); ?></option>
						<?php
						if ( is_array( $wlgf_all_forms ) ) {
							foreach ( $wlgf_all_forms as $wlgf_form ) {
								$wlgf_form_data = get_option( $wlgf_form->option_name );
								if ( ! is_array( $wlgf_form_data ) || empty( $wlgf_form_data['form_id'] ) ) {
									continue;
								}
								$f_id   = sanitize_text_field( $wlgf_form_data['form_id'] );
								$f_name = ! empty( $wlgf_form_data['form_name'] ) ? sanitize_text_field( $wlgf_form_data['form_name'] ) : ( 'Form #' . $f_id );
								?>
								<option value="<?php echo esc_attr( $f_id ); ?>">
									<?php echo esc_html( $f_name ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
				</div>

				<button type="button" id="wlgf-combine-btn" class="wlgf-btn wlgf-btn--primary wlgf-w-full">
					<i class="fa-solid fa-plus"></i> <?php esc_html_e( 'Merge Forms into New Form', 'lead-generation-form' ); ?>
				</button>
				<div id="wlgf-combine-result" class="wlgf-mt-2"></div>
			</div>
		</div>
	</div>

	<!-- FAQ Card -->
	<div class="wlgf-card wlgf-mt-4">
		<div class="wlgf-card__header">
			<h2 class="wlgf-card__title">
				<i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'Import/Export & Merge Guide', 'lead-generation-form' ); ?>
			</h2>
		</div>
		<div class="wlgf-card__body">
			<div class="wlgf-steps">
				<div class="wlgf-step">
					<span class="wlgf-step__num"><i class="fa-solid fa-file-export"></i></span>
					<div class="wlgf-step__content">
						<strong class="wlgf-step__title"><?php esc_html_e( 'How does Export work?', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-step__desc"><?php esc_html_e( 'Exporting extracts the complete field configuration, labels, validation parameters, and subtypes into portable JSON without including any private lead submissions.', 'lead-generation-form' ); ?></span>
					</div>
				</div>
				<div class="wlgf-step">
					<span class="wlgf-step__num"><i class="fa-solid fa-file-import"></i></span>
					<div class="wlgf-step__content">
						<strong class="wlgf-step__title"><?php esc_html_e( 'How does Import work?', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-step__desc"><?php esc_html_e( 'Paste the JSON schema and provide a new form name. The plugin verifies the syntax and creates a brand-new form with a unique ID.', 'lead-generation-form' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>