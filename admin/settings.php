<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

// Page Assets
wp_enqueue_media();
wp_enqueue_style( 'wlgf-admin-ui-css' );
wp_enqueue_style( 'wlgf-fontawesome-css', plugins_url( 'assets/fontawesome-free-6.4.2-web/css/all.min.css', __FILE__ ), array(), '6.4.2', 'all' );

$wlgf_settings_css_ver = file_exists( plugin_dir_path( __FILE__ ) . 'assets/css/settings.css' ) ? WLGF_PLUGIN_VERSION . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/settings.css' ) : WLGF_PLUGIN_VERSION;
$wlgf_settings_js_ver  = file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/settings.js' ) ? WLGF_PLUGIN_VERSION . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/settings.js' ) : WLGF_PLUGIN_VERSION;

wp_deregister_style( 'wlgf-settings-css' );
wp_register_style( 'wlgf-settings-css', plugin_dir_url( __FILE__ ) . 'assets/css/settings.css', array( 'wlgf-admin-ui-css' ), $wlgf_settings_css_ver, 'all' );
wp_enqueue_style( 'wlgf-settings-css' );

wp_deregister_script( 'wlgf-settings-js' );
wp_register_script( 'wlgf-settings-js', plugin_dir_url( __FILE__ ) . 'assets/js/settings.js', array( 'jquery', 'wlgf-dialog-js' ), $wlgf_settings_js_ver, true );
wp_enqueue_script( 'wlgf-settings-js' );

wp_add_inline_script(
	'wlgf-settings-js',
	'const Settings = ' . wp_json_encode(
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'wlgf-save-settings' ),
			'resetNonce' => wp_create_nonce( 'wlgf-factory-reset' ),
			'exportUrl'  => admin_url( 'admin.php?page=wlgf-import-export' ),
		)
	),
	'before'
);

// Load saved settings
$wlgf_settings        = get_option( 'wlgf_settings', array() );
$wlgf_recaptcha       = isset( $wlgf_settings['recaptcha'] ) ? sanitize_text_field( $wlgf_settings['recaptcha'] ) : '2';
$wlgf_captcha_provider = isset( $wlgf_settings['captcha_provider'] ) ? sanitize_text_field( $wlgf_settings['captcha_provider'] ) : ( ( '1' === (string) $wlgf_recaptcha ) ? 'recaptcha' : ( ( '3' === (string) $wlgf_recaptcha ) ? 'turnstile' : 'none' ) );
$wlgf_sitekey         = isset( $wlgf_settings['sitekey'] ) ? sanitize_text_field( $wlgf_settings['sitekey'] ) : '';
$wlgf_secretkey       = isset( $wlgf_settings['secretkey'] ) ? sanitize_text_field( $wlgf_settings['secretkey'] ) : '';
$wlgf_turnstile_sitekey   = isset( $wlgf_settings['turnstile_sitekey'] ) ? sanitize_text_field( $wlgf_settings['turnstile_sitekey'] ) : '';
$wlgf_turnstile_secretkey = isset( $wlgf_settings['turnstile_secretkey'] ) ? sanitize_text_field( $wlgf_settings['turnstile_secretkey'] ) : '';
$wlgf_submission_rate_limit = isset( $wlgf_settings['submission_rate_limit'] ) ? sanitize_text_field( $wlgf_settings['submission_rate_limit'] ) : '1';
$wlgf_honeypot_status       = isset( $wlgf_settings['honeypot_status'] ) ? sanitize_text_field( $wlgf_settings['honeypot_status'] ) : '1';
$wlgf_scanner_shield_status = isset( $wlgf_settings['scanner_shield_status'] ) ? sanitize_text_field( $wlgf_settings['scanner_shield_status'] ) : '1';

$wlgf_notify_admin    = isset( $wlgf_settings['notify_admin'] ) ? sanitize_text_field( $wlgf_settings['notify_admin'] ) : '2';
$wlgf_email_engine    = isset( $wlgf_settings['email_engine'] ) ? sanitize_text_field( $wlgf_settings['email_engine'] ) : '1';
$wlgf_smtp_host       = isset( $wlgf_settings['smtp_host'] ) ? sanitize_text_field( $wlgf_settings['smtp_host'] ) : '';
$wlgf_smtp_username   = isset( $wlgf_settings['smtp_username'] ) ? sanitize_text_field( $wlgf_settings['smtp_username'] ) : '';
$wlgf_smtp_password   = isset( $wlgf_settings['smtp_password'] ) ? sanitize_text_field( $wlgf_settings['smtp_password'] ) : '';
$wlgf_smtp_encryption = isset( $wlgf_settings['smtp_encryption'] ) ? sanitize_text_field( $wlgf_settings['smtp_encryption'] ) : '1';
$wlgf_smtp_port       = isset( $wlgf_settings['smtp_port'] ) ? sanitize_text_field( $wlgf_settings['smtp_port'] ) : '587';
$wlgf_cc_admin_emails = isset( $wlgf_settings['cc_admin_emails'] ) ? sanitize_textarea_field( $wlgf_settings['cc_admin_emails'] ) : '';

// Email & Domain Blacklist Settings
$wlgf_blacklist_status  = isset( $wlgf_settings['blacklist_status'] ) ? sanitize_text_field( $wlgf_settings['blacklist_status'] ) : '2';
$wlgf_blacklist_emails  = isset( $wlgf_settings['blacklist_emails'] ) ? sanitize_textarea_field( $wlgf_settings['blacklist_emails'] ) : '';
$wlgf_blacklist_domains = isset( $wlgf_settings['blacklist_domains'] ) ? sanitize_textarea_field( $wlgf_settings['blacklist_domains'] ) : '';
$wlgf_blacklist_action  = isset( $wlgf_settings['blacklist_action'] ) ? sanitize_text_field( $wlgf_settings['blacklist_action'] ) : 'reject_error';
$wlgf_blacklist_message = isset( $wlgf_settings['blacklist_message'] ) && '' !== $wlgf_settings['blacklist_message'] ? sanitize_text_field( $wlgf_settings['blacklist_message'] ) : __( 'Sorry! Your email address or domain is not permitted to submit this form.', 'lead-generation-form' );

// Email Notification Template Settings
$wlgf_email_logo_url          = isset( $wlgf_settings['email_logo_url'] ) ? esc_url_raw( $wlgf_settings['email_logo_url'] ) : '';
$wlgf_email_logo_width        = isset( $wlgf_settings['email_logo_width'] ) ? intval( $wlgf_settings['email_logo_width'] ) : 180;
$wlgf_email_header_title      = isset( $wlgf_settings['email_header_title'] ) && '' !== $wlgf_settings['email_header_title'] ? sanitize_text_field( $wlgf_settings['email_header_title'] ) : '{form_name} - New Lead Submission';
$wlgf_email_header_bg         = isset( $wlgf_settings['email_header_bg'] ) && '' !== $wlgf_settings['email_header_bg'] ? sanitize_hex_color( $wlgf_settings['email_header_bg'] ) : '#1e293b';
$wlgf_email_header_color      = isset( $wlgf_settings['email_header_color'] ) && '' !== $wlgf_settings['email_header_color'] ? sanitize_hex_color( $wlgf_settings['email_header_color'] ) : '#ffffff';
$wlgf_email_body_greeting     = isset( $wlgf_settings['email_body_greeting'] ) && '' !== $wlgf_settings['email_body_greeting'] ? sanitize_textarea_field( $wlgf_settings['email_body_greeting'] ) : __( 'Hello Admin, a new inquiry has just been submitted on your website. Below are the complete submission details:', 'lead-generation-form' );
$wlgf_email_body_bg           = isset( $wlgf_settings['email_body_bg'] ) && '' !== $wlgf_settings['email_body_bg'] ? sanitize_hex_color( $wlgf_settings['email_body_bg'] ) : '#f1f5f9';
$wlgf_email_card_bg           = isset( $wlgf_settings['email_card_bg'] ) && '' !== $wlgf_settings['email_card_bg'] ? sanitize_hex_color( $wlgf_settings['email_card_bg'] ) : '#ffffff';
$wlgf_email_text_color        = isset( $wlgf_settings['email_text_color'] ) && '' !== $wlgf_settings['email_text_color'] ? sanitize_hex_color( $wlgf_settings['email_text_color'] ) : '#334155';
$wlgf_email_footer_text       = isset( $wlgf_settings['email_footer_text'] ) && '' !== $wlgf_settings['email_footer_text'] ? sanitize_textarea_field( $wlgf_settings['email_footer_text'] ) : sprintf( ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ? /* translators: %s: Site title */ __( 'This notification was automatically sent by Lead Generation Form Pro on %s.', 'lead-generation-form' ) : /* translators: %s: Site title */ __( 'This notification was automatically sent by Lead Generation Form on %s.', 'lead-generation-form' ) ), '{site_title}' );
$wlgf_email_footer_bg         = isset( $wlgf_settings['email_footer_bg'] ) && '' !== $wlgf_settings['email_footer_bg'] ? sanitize_hex_color( $wlgf_settings['email_footer_bg'] ) : '#f8fafc';
$wlgf_email_footer_color      = isset( $wlgf_settings['email_footer_color'] ) && '' !== $wlgf_settings['email_footer_color'] ? sanitize_hex_color( $wlgf_settings['email_footer_color'] ) : '#64748b';
$wlgf_email_show_dashboard    = isset( $wlgf_settings['email_show_dashboard_btn'] ) ? sanitize_text_field( $wlgf_settings['email_show_dashboard_btn'] ) : '1';

$wlgf_user_message    = isset( $wlgf_settings['user_message'] ) ? $wlgf_settings['user_message'] : __( 'Thank you! Your query has been successfully submitted.', 'lead-generation-form' );

// Confirmation message appearance variables (PRO)
$wlgf_confirmation_heading       = isset( $wlgf_settings['confirmation_heading'] ) && '' !== $wlgf_settings['confirmation_heading'] ? sanitize_text_field( $wlgf_settings['confirmation_heading'] ) : __( 'Thank you! Submission Received', 'lead-generation-form' );
$wlgf_confirmation_style         = isset( $wlgf_settings['confirmation_style'] ) ? sanitize_key( $wlgf_settings['confirmation_style'] ) : 'modern-card';
$wlgf_confirmation_icon          = isset( $wlgf_settings['confirmation_icon'] ) ? sanitize_text_field( $wlgf_settings['confirmation_icon'] ) : 'circle-check';
$wlgf_confirmation_animated      = isset( $wlgf_settings['confirmation_animated'] ) ? sanitize_text_field( $wlgf_settings['confirmation_animated'] ) : '1';
$wlgf_confirmation_fadeout       = isset( $wlgf_settings['confirmation_fadeout'] ) ? max( 5, min( 50, intval( $wlgf_settings['confirmation_fadeout'] ) ) ) : 10;
$wlgf_confirmation_bg            = isset( $wlgf_settings['confirmation_bg'] ) && '' !== $wlgf_settings['confirmation_bg'] ? sanitize_hex_color( $wlgf_settings['confirmation_bg'] ) : '#f0fdf4';
$wlgf_confirmation_border_color  = isset( $wlgf_settings['confirmation_border_color'] ) && '' !== $wlgf_settings['confirmation_border_color'] ? sanitize_hex_color( $wlgf_settings['confirmation_border_color'] ) : '#86efac';
$wlgf_confirmation_heading_color = isset( $wlgf_settings['confirmation_heading_color'] ) && '' !== $wlgf_settings['confirmation_heading_color'] ? sanitize_hex_color( $wlgf_settings['confirmation_heading_color'] ) : '#14532d';
$wlgf_confirmation_text_color    = isset( $wlgf_settings['confirmation_text_color'] ) && '' !== $wlgf_settings['confirmation_text_color'] ? sanitize_hex_color( $wlgf_settings['confirmation_text_color'] ) : '#166534';
$wlgf_confirmation_icon_color    = isset( $wlgf_settings['confirmation_icon_color'] ) && '' !== $wlgf_settings['confirmation_icon_color'] ? sanitize_hex_color( $wlgf_settings['confirmation_icon_color'] ) : '#16a34a';
$wlgf_confirmation_icon_bg       = isset( $wlgf_settings['confirmation_icon_bg'] ) && '' !== $wlgf_settings['confirmation_icon_bg'] ? sanitize_hex_color( $wlgf_settings['confirmation_icon_bg'] ) : '#dcfce7';

$wlgf_admin_email     = get_option( 'admin_email' );

// Newsletter & Integrations Settings (PRO-008)
$wlgf_newsletter_provider    = isset( $wlgf_settings['newsletter_provider'] ) ? sanitize_text_field( $wlgf_settings['newsletter_provider'] ) : 'local';
$wlgf_mailchimp_api_key      = isset( $wlgf_settings['mailchimp_api_key'] ) ? sanitize_text_field( $wlgf_settings['mailchimp_api_key'] ) : '';
$wlgf_mailchimp_list_id      = isset( $wlgf_settings['mailchimp_list_id'] ) ? sanitize_text_field( $wlgf_settings['mailchimp_list_id'] ) : '';
$wlgf_mailchimp_optin        = isset( $wlgf_settings['mailchimp_double_optin'] ) ? sanitize_text_field( $wlgf_settings['mailchimp_double_optin'] ) : '0';
$wlgf_brevo_api_key          = isset( $wlgf_settings['brevo_api_key'] ) ? sanitize_text_field( $wlgf_settings['brevo_api_key'] ) : '';
$wlgf_brevo_list_id          = isset( $wlgf_settings['brevo_list_id'] ) ? sanitize_text_field( $wlgf_settings['brevo_list_id'] ) : '';
$wlgf_mailerlite_api_key     = isset( $wlgf_settings['mailerlite_api_key'] ) ? sanitize_text_field( $wlgf_settings['mailerlite_api_key'] ) : '';
$wlgf_mailerlite_group_id    = isset( $wlgf_settings['mailerlite_group_id'] ) ? sanitize_text_field( $wlgf_settings['mailerlite_group_id'] ) : '';
$wlgf_sendgrid_api_key       = isset( $wlgf_settings['sendgrid_api_key'] ) ? sanitize_text_field( $wlgf_settings['sendgrid_api_key'] ) : '';
$wlgf_sendgrid_list_id       = isset( $wlgf_settings['sendgrid_list_id'] ) ? sanitize_text_field( $wlgf_settings['sendgrid_list_id'] ) : '';
$wlgf_activecampaign_api_url = isset( $wlgf_settings['activecampaign_api_url'] ) ? sanitize_text_field( $wlgf_settings['activecampaign_api_url'] ) : '';
$wlgf_activecampaign_api_key = isset( $wlgf_settings['activecampaign_api_key'] ) ? sanitize_text_field( $wlgf_settings['activecampaign_api_key'] ) : '';
$wlgf_activecampaign_list_id = isset( $wlgf_settings['activecampaign_list_id'] ) ? sanitize_text_field( $wlgf_settings['activecampaign_list_id'] ) : '';

// Payment Settings (PRO-016)
$wlgf_enable_payments        = isset( $wlgf_settings['enable_payments'] ) ? sanitize_text_field( $wlgf_settings['enable_payments'] ) : '0';
$wlgf_payment_currency       = isset( $wlgf_settings['payment_currency'] ) ? sanitize_text_field( $wlgf_settings['payment_currency'] ) : 'USD';
$wlgf_stripe_enabled         = isset( $wlgf_settings['stripe_enabled'] ) ? sanitize_text_field( $wlgf_settings['stripe_enabled'] ) : '0';
$wlgf_stripe_test_mode       = isset( $wlgf_settings['stripe_test_mode'] ) ? sanitize_text_field( $wlgf_settings['stripe_test_mode'] ) : '1';
$wlgf_stripe_test_pub_key    = isset( $wlgf_settings['stripe_test_pub_key'] ) ? sanitize_text_field( $wlgf_settings['stripe_test_pub_key'] ) : '';
$wlgf_stripe_test_sec_key    = isset( $wlgf_settings['stripe_test_sec_key'] ) ? sanitize_text_field( $wlgf_settings['stripe_test_sec_key'] ) : '';
$wlgf_stripe_live_pub_key    = isset( $wlgf_settings['stripe_live_pub_key'] ) ? sanitize_text_field( $wlgf_settings['stripe_live_pub_key'] ) : '';
$wlgf_stripe_live_sec_key    = isset( $wlgf_settings['stripe_live_sec_key'] ) ? sanitize_text_field( $wlgf_settings['stripe_live_sec_key'] ) : '';
$wlgf_paypal_enabled         = isset( $wlgf_settings['paypal_enabled'] ) ? sanitize_text_field( $wlgf_settings['paypal_enabled'] ) : '0';
$wlgf_paypal_mode            = isset( $wlgf_settings['paypal_mode'] ) ? sanitize_text_field( $wlgf_settings['paypal_mode'] ) : 'sandbox';
$wlgf_paypal_sandbox_client  = isset( $wlgf_settings['paypal_sandbox_client_id'] ) ? sanitize_text_field( $wlgf_settings['paypal_sandbox_client_id'] ) : '';
$wlgf_paypal_sandbox_secret  = isset( $wlgf_settings['paypal_sandbox_secret'] ) ? sanitize_text_field( $wlgf_settings['paypal_sandbox_secret'] ) : '';
$wlgf_paypal_live_client     = isset( $wlgf_settings['paypal_live_client_id'] ) ? sanitize_text_field( $wlgf_settings['paypal_live_client_id'] ) : '';
$wlgf_paypal_live_secret     = isset( $wlgf_settings['paypal_live_secret'] ) ? sanitize_text_field( $wlgf_settings['paypal_live_secret'] ) : '';

// Floating Multi-Channel Widget Settings (SiteLeads Challenger)
$wlgf_floating_widget_enabled = isset( $wlgf_settings['floating_widget_enabled'] ) ? sanitize_text_field( $wlgf_settings['floating_widget_enabled'] ) : '0';
$wlgf_floating_position       = isset( $wlgf_settings['floating_position'] ) ? sanitize_text_field( $wlgf_settings['floating_position'] ) : 'bottom-right';
$wlgf_floating_color          = isset( $wlgf_settings['floating_color'] ) ? sanitize_hex_color( $wlgf_settings['floating_color'] ) : '#2563eb';
$wlgf_floating_greeting       = isset( $wlgf_settings['floating_greeting'] ) ? sanitize_text_field( $wlgf_settings['floating_greeting'] ) : '👋 Need help? Chat with us';
$wlgf_floating_title          = isset( $wlgf_settings['floating_title'] ) ? sanitize_text_field( $wlgf_settings['floating_title'] ) : 'How can we help you?';
$wlgf_floating_subtitle       = isset( $wlgf_settings['floating_subtitle'] ) ? sanitize_text_field( $wlgf_settings['floating_subtitle'] ) : 'Usually replies within minutes';
$wlgf_floating_whatsapp       = isset( $wlgf_settings['floating_whatsapp'] ) ? sanitize_text_field( $wlgf_settings['floating_whatsapp'] ) : '';
$wlgf_floating_whatsapp_msg   = isset( $wlgf_settings['floating_whatsapp_msg'] ) ? sanitize_text_field( $wlgf_settings['floating_whatsapp_msg'] ) : 'Hello! I have an inquiry.';
$wlgf_floating_phone          = isset( $wlgf_settings['floating_phone'] ) ? sanitize_text_field( $wlgf_settings['floating_phone'] ) : '';
$wlgf_floating_telegram       = isset( $wlgf_settings['floating_telegram'] ) ? sanitize_text_field( $wlgf_settings['floating_telegram'] ) : '';
$wlgf_floating_sms            = isset( $wlgf_settings['floating_sms'] ) ? sanitize_text_field( $wlgf_settings['floating_sms'] ) : '';
$wlgf_floating_email          = isset( $wlgf_settings['floating_email'] ) ? sanitize_email( $wlgf_settings['floating_email'] ) : '';
$wlgf_floating_form_id        = isset( $wlgf_settings['floating_form_id'] ) ? sanitize_text_field( $wlgf_settings['floating_form_id'] ) : '';

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wlgf_forms_dropdown = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT option_name FROM {$wpdb->options} WHERE `option_name` LIKE %s AND `option_name` NOT LIKE %s ORDER BY option_id ASC",
		$wpdb->esc_like( 'wlgf_form_' ) . '%',
		$wpdb->esc_like( 'wlgf_form_views_' ) . '%'
	)
);

$wlgf_changelog_url = defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ? plugins_url( 'change-log.txt', dirname( __FILE__ ) ) : 'https://wordpress.org/plugins/lead-generation-form/#developers';
$wlgf_support_url   = defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ? 'https://wpfrank.com/contact/' : 'https://wordpress.org/support/plugin/lead-generation-form/';
$wlgf_docs_url      = 'https://wpfrank.com/wordpress-plugins/lead-generation-form-pro/';
$wlgf_max_upload_size = size_format( wp_max_upload_size() );
$wlgf_max_post_size   = ini_get( 'post_max_size' ) ? ini_get( 'post_max_size' ) : 'N/A';
$wlgf_memory_limit    = ini_get( 'memory_limit' ) ? ini_get( 'memory_limit' ) : 'N/A';
$wlgf_wp_version_str  = get_bloginfo( 'version' );
?>

<div class="wlgf-admin-wrap">
	<!-- Page Header -->
	<header class="wlgf-header wlgf-header--sticky">
		<div class="wlgf-header__main">
			<div class="wlgf-header__icon">
				<i class="fa-solid fa-gear"></i>
			</div>
			<div class="wlgf-header__titles">
				<h1 class="wlgf-header__title"><?php esc_html_e( 'Global Settings', 'lead-generation-form' ); ?></h1>
				<p class="wlgf-header__subtitle"><?php esc_html_e( 'Configure user messages, email dispatch engine, SMTP credentials, anti-spam verification, and payments.', 'lead-generation-form' ); ?></p>
			</div>
		</div>
		<div class="wlgf-header__actions">
			<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--secondary" style="background: #fffbeb; border-color: #fde68a; color: #92400e; font-weight: 700; text-decoration: none;">
					<i class="fa-solid fa-crown" style="color: #f59e0b;"></i> <?php esc_html_e( 'Free vs Pro', 'lead-generation-form' ); ?>
				</a>
			<?php endif; ?>
			<button type="button" id="wlgf-header-save-btn" class="wlgf-btn wlgf-btn--primary">
				<i class="fa-solid fa-floppy-disk"></i> <?php esc_html_e( 'Save Settings', 'lead-generation-form' ); ?>
			</button>
		</div>
	</header>

	<div id="wlgf-save-result" class="wlgf-mb-3"></div>

	<!-- Tabs Navigation -->
	<nav class="wlgf-tabs-nav">
		<button type="button" class="wlgf-tab-btn wlgf-tab-btn--active" data-target="#wlgf-tab-general">
			<i class="fa-solid fa-message"></i> <?php esc_html_e( 'General & Confirmation', 'lead-generation-form' ); ?>
		</button>
		<button type="button" class="wlgf-tab-btn" data-target="#wlgf-tab-smtp">
			<i class="fa-solid fa-envelope"></i> <?php esc_html_e( 'Email & SMTP', 'lead-generation-form' ); ?>
		</button>
		<button type="button" class="wlgf-tab-btn" data-target="#wlgf-tab-security">
			<i class="fa-solid fa-shield-halved"></i> <?php esc_html_e( 'Anti-Spam & Security', 'lead-generation-form' ); ?>
		</button>
		<button type="button" class="wlgf-tab-btn" data-target="#wlgf-tab-integrations">
			<i class="fa-solid fa-paper-plane"></i> <?php esc_html_e( 'Newsletter & Integrations', 'lead-generation-form' ); ?>
		</button>
		<button type="button" class="wlgf-tab-btn" data-target="#wlgf-tab-payments">
			<i class="fa-solid fa-credit-card"></i> <?php esc_html_e( 'Payments & Checkout', 'lead-generation-form' ); ?>
			<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
				<span class="wlgf-badge wlgf-badge--warning wlgf-ml-1"><?php esc_html_e( 'PRO', 'lead-generation-form' ); ?></span>
			<?php endif; ?>
		</button>
		<button type="button" class="wlgf-tab-btn" data-target="#wlgf-tab-rest-api">
			<i class="fa-solid fa-code"></i> <?php esc_html_e( 'Headless REST API', 'lead-generation-form' ); ?>
			<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
				<span class="wlgf-badge wlgf-badge--warning wlgf-ml-1"><?php esc_html_e( 'PRO', 'lead-generation-form' ); ?></span>
			<?php endif; ?>
		</button>
		<button type="button" class="wlgf-tab-btn" data-target="#wlgf-tab-floating-widget">
			<i class="fa-solid fa-comment-dots"></i> <?php esc_html_e( 'Floating Contact Widget', 'lead-generation-form' ); ?>
			<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
				<span class="wlgf-badge wlgf-badge--warning wlgf-ml-1"><?php esc_html_e( 'PRO', 'lead-generation-form' ); ?></span>
			<?php endif; ?>
		</button>
		<button type="button" class="wlgf-tab-btn" data-target="#wlgf-tab-help">
			<i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'Help & Documentation', 'lead-generation-form' ); ?>
		</button>
		<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
		<button type="button" class="wlgf-tab-btn" data-target="#wlgf-tab-upgrade">
			<i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Upgrade to Pro', 'lead-generation-form' ); ?>
			<span class="wlgf-badge wlgf-badge--warning wlgf-ml-1"><?php esc_html_e( 'PRO', 'lead-generation-form' ); ?></span>
		</button>
		<?php endif; ?>
	</nav>

	<!-- Settings Form Container -->
	<form id="wlgf-settings-form">
		<!-- TAB 1: General & Confirmation -->
		<div id="wlgf-tab-general" class="wlgf-tab-content wlgf-tab-content--active">
			<div class="wlgf-grid wlgf-grid--2 wlgf-gap-4">
				<!-- Left Card: Confirmation Message Designer & Settings -->
				<div class="wlgf-card">
					<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center">
						<h2 class="wlgf-card__title">
							<i class="fa-solid fa-wand-magic-sparkles text-primary"></i> <?php esc_html_e( 'Confirmation Message Designer', 'lead-generation-form' ); ?>
						</h2>
						<span class="wlgf-badge wlgf-badge--primary"><?php esc_html_e( 'Interactive', 'lead-generation-form' ); ?></span>
					</div>
					<div class="wlgf-card__body">
						<p class="wlgf-help-text wlgf-mb-4">
							<?php esc_html_e( 'Customize the design, layout style, animated badge, headline, message, and color scheme for the Thank You confirmation card displayed upon successful form submission.', 'lead-generation-form' ); ?>
						</p>

						<!-- Confirmation Heading -->
						<div class="wlgf-form-group">
							<label for="confirmation_heading" class="wlgf-label">
								<?php esc_html_e( 'Confirmation Heading / Title', 'lead-generation-form' ); ?>
							</label>
							<input type="text" class="wlgf-input" id="confirmation_heading" name="confirmation_heading" value="<?php echo esc_attr( $wlgf_confirmation_heading ); ?>" placeholder="<?php esc_attr_e( 'Thank you! Submission Received', 'lead-generation-form' ); ?>">
							<p class="wlgf-help-text"><?php esc_html_e( 'Bold primary headline displayed inside the confirmation card.', 'lead-generation-form' ); ?></p>
						</div>

						<!-- User Message / Body -->
						<div class="wlgf-form-group">
							<label for="user_message" class="wlgf-label">
								<?php esc_html_e( 'Success Message Body', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span>
							</label>
							<textarea class="wlgf-textarea" rows="3" id="user_message" name="user_message"><?php echo esc_textarea( $wlgf_user_message ); ?></textarea>
							<p class="wlgf-help-text">
								<?php esc_html_e( 'The supporting message body text. Allowed HTML: <p>, <strong>, <i>, <br>, <a>, <hr>, <span>.', 'lead-generation-form' ); ?>
							</p>
						</div>

						<!-- Layout Preset & Icon Selector in 2 Columns -->
						<div class="wlgf-grid wlgf-grid--2 wlgf-gap-3">
							<div class="wlgf-form-group">
								<label for="confirmation_style" class="wlgf-label"><?php esc_html_e( 'Visual Layout Preset', 'lead-generation-form' ); ?></label>
								<select class="wlgf-select wlgf-w-full" id="confirmation_style" name="confirmation_style">
									<option value="modern-card" <?php selected( $wlgf_confirmation_style, 'modern-card' ); ?>><?php esc_html_e( 'Modern Centered Card (Recommended)', 'lead-generation-form' ); ?></option>
									<option value="banner" <?php selected( $wlgf_confirmation_style, 'banner' ); ?>><?php esc_html_e( 'Accent Left Banner', 'lead-generation-form' ); ?></option>
									<option value="minimal-pill" <?php selected( $wlgf_confirmation_style, 'minimal-pill' ); ?>><?php esc_html_e( 'Minimal Inline Pill', 'lead-generation-form' ); ?></option>
									<option value="glassmorphism" <?php selected( $wlgf_confirmation_style, 'glassmorphism' ); ?>><?php esc_html_e( 'Glassmorphism Blur Card', 'lead-generation-form' ); ?></option>
								</select>
							</div>

							<div class="wlgf-form-group">
								<label for="confirmation_icon" class="wlgf-label"><?php esc_html_e( 'Badge Icon', 'lead-generation-form' ); ?></label>
								<select class="wlgf-select wlgf-w-full" id="confirmation_icon" name="confirmation_icon">
									<option value="circle-check" <?php selected( $wlgf_confirmation_icon, 'circle-check' ); ?>>✓ Checkmark Circle (Default)</option>
									<option value="paper-plane" <?php selected( $wlgf_confirmation_icon, 'paper-plane' ); ?>>✈ Paper Plane (Sent)</option>
									<option value="sparkles" <?php selected( $wlgf_confirmation_icon, 'sparkles' ); ?>>✨ Magic Sparkles</option>
									<option value="thumbs-up" <?php selected( $wlgf_confirmation_icon, 'thumbs-up' ); ?>>👍 Thumbs Up</option>
									<option value="envelope-circle-check" <?php selected( $wlgf_confirmation_icon, 'envelope-circle-check' ); ?>>✉ Envelope Delivered</option>
									<option value="heart" <?php selected( $wlgf_confirmation_icon, 'heart' ); ?>>♥ Heart Appreciation</option>
									<option value="bell" <?php selected( $wlgf_confirmation_icon, 'bell' ); ?>>🔔 Bell Notification</option>
									<option value="none" <?php selected( $wlgf_confirmation_icon, 'none' ); ?>>— No Icon</option>
								</select>
							</div>
						</div>

						<!-- Animation Option Toggle -->
						<div class="wlgf-form-group">
							<label class="wlgf-label wlgf-flex wlgf-items-center wlgf-gap-2" style="cursor: pointer;">
								<input type="checkbox" id="confirmation_animated" name="confirmation_animated" value="1" <?php checked( $wlgf_confirmation_animated, '1' ); ?>>
								<span><?php esc_html_e( 'Enable Pop & Pulse Entry Animation on Badge', 'lead-generation-form' ); ?></span>
							</label>
							<p class="wlgf-help-text"><?php esc_html_e( 'Creates an interactive spring pop-in effect when the confirmation message appears.', 'lead-generation-form' ); ?></p>
						</div>

						<!-- Confirmation Message Fadeout Duration -->
						<div class="wlgf-form-group">
							<label for="confirmation_fadeout" class="wlgf-label wlgf-flex wlgf-items-center wlgf-justify-between">
								<span><i class="fa-solid fa-clock-rotate-left text-primary"></i> <?php esc_html_e( 'Confirmation Message Fadeout Duration', 'lead-generation-form' ); ?></span>
								<span id="confirmation_fadeout_badge" class="wlgf-badge wlgf-badge--primary" style="font-size: 11.5px;"><?php echo esc_html( $wlgf_confirmation_fadeout ); ?>s</span>
							</label>
							<div class="wlgf-flex wlgf-items-center wlgf-gap-3">
								<input type="range" class="wlgf-range" id="confirmation_fadeout_range" min="5" max="50" step="1" value="<?php echo esc_attr( $wlgf_confirmation_fadeout ); ?>" style="flex: 1; cursor: pointer;">
								<input type="number" class="wlgf-input" id="confirmation_fadeout" name="confirmation_fadeout" min="5" max="50" step="1" value="<?php echo esc_attr( $wlgf_confirmation_fadeout ); ?>" style="width: 76px; text-align: center; font-weight: 600;">
								<span style="font-size: 12px; color: #64748b; font-weight: 600;"><?php esc_html_e( 'sec', 'lead-generation-form' ); ?></span>
							</div>
							<p class="wlgf-help-text">
								<?php esc_html_e( 'Adjust how long the confirmation message stays visible before automatically fading out (5 to 50 seconds, default: 10s).', 'lead-generation-form' ); ?>
							</p>
						</div>

						<hr class="wlgf-divider wlgf-my-3">

						<!-- Color Theme Palette -->
						<h3 class="wlgf-section-subtitle" style="font-size: 13.5px; font-weight: 700; margin-bottom: 12px; color: #1e293b;">
							<i class="fa-solid fa-palette text-primary"></i> <?php esc_html_e( 'Color Palette & Styling', 'lead-generation-form' ); ?>
						</h3>

						<div class="wlgf-grid wlgf-grid--3 wlgf-gap-3">
							<!-- Card BG -->
							<div class="wlgf-form-group">
								<label for="confirmation_bg" class="wlgf-label" style="font-size: 12px;"><?php esc_html_e( 'Card Background', 'lead-generation-form' ); ?></label>
								<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
									<input type="color" class="wlgf-color-picker" id="confirmation_bg_picker" value="<?php echo esc_attr( $wlgf_confirmation_bg ); ?>">
									<input type="text" class="wlgf-input wlgf-color-code" id="confirmation_bg" name="confirmation_bg" value="<?php echo esc_attr( $wlgf_confirmation_bg ); ?>" maxlength="7" style="font-size: 12px; padding: 6px 8px;">
								</div>
							</div>

							<!-- Border Color -->
							<div class="wlgf-form-group">
								<label for="confirmation_border_color" class="wlgf-label" style="font-size: 12px;"><?php esc_html_e( 'Border Color', 'lead-generation-form' ); ?></label>
								<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
									<input type="color" class="wlgf-color-picker" id="confirmation_border_color_picker" value="<?php echo esc_attr( $wlgf_confirmation_border_color ); ?>">
									<input type="text" class="wlgf-input wlgf-color-code" id="confirmation_border_color" name="confirmation_border_color" value="<?php echo esc_attr( $wlgf_confirmation_border_color ); ?>" maxlength="7" style="font-size: 12px; padding: 6px 8px;">
								</div>
							</div>

							<!-- Accent / Icon Color -->
							<div class="wlgf-form-group">
								<label for="confirmation_icon_color" class="wlgf-label" style="font-size: 12px;"><?php esc_html_e( 'Icon Accent Color', 'lead-generation-form' ); ?></label>
								<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
									<input type="color" class="wlgf-color-picker" id="confirmation_icon_color_picker" value="<?php echo esc_attr( $wlgf_confirmation_icon_color ); ?>">
									<input type="text" class="wlgf-input wlgf-color-code" id="confirmation_icon_color" name="confirmation_icon_color" value="<?php echo esc_attr( $wlgf_confirmation_icon_color ); ?>" maxlength="7" style="font-size: 12px; padding: 6px 8px;">
								</div>
							</div>

							<!-- Icon Badge BG -->
							<div class="wlgf-form-group">
								<label for="confirmation_icon_bg" class="wlgf-label" style="font-size: 12px;"><?php esc_html_e( 'Icon Badge BG', 'lead-generation-form' ); ?></label>
								<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
									<input type="color" class="wlgf-color-picker" id="confirmation_icon_bg_picker" value="<?php echo esc_attr( $wlgf_confirmation_icon_bg ); ?>">
									<input type="text" class="wlgf-input wlgf-color-code" id="confirmation_icon_bg" name="confirmation_icon_bg" value="<?php echo esc_attr( $wlgf_confirmation_icon_bg ); ?>" maxlength="7" style="font-size: 12px; padding: 6px 8px;">
								</div>
							</div>

							<!-- Heading Color -->
							<div class="wlgf-form-group">
								<label for="confirmation_heading_color" class="wlgf-label" style="font-size: 12px;"><?php esc_html_e( 'Heading Color', 'lead-generation-form' ); ?></label>
								<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
									<input type="color" class="wlgf-color-picker" id="confirmation_heading_color_picker" value="<?php echo esc_attr( $wlgf_confirmation_heading_color ); ?>">
									<input type="text" class="wlgf-input wlgf-color-code" id="confirmation_heading_color" name="confirmation_heading_color" value="<?php echo esc_attr( $wlgf_confirmation_heading_color ); ?>" maxlength="7" style="font-size: 12px; padding: 6px 8px;">
								</div>
							</div>

							<!-- Body Text Color -->
							<div class="wlgf-form-group">
								<label for="confirmation_text_color" class="wlgf-label" style="font-size: 12px;"><?php esc_html_e( 'Body Text Color', 'lead-generation-form' ); ?></label>
								<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
									<input type="color" class="wlgf-color-picker" id="confirmation_text_color_picker" value="<?php echo esc_attr( $wlgf_confirmation_text_color ); ?>">
									<input type="text" class="wlgf-input wlgf-color-code" id="confirmation_text_color" name="confirmation_text_color" value="<?php echo esc_attr( $wlgf_confirmation_text_color ); ?>" maxlength="7" style="font-size: 12px; padding: 6px 8px;">
								</div>
							</div>
						</div>

						<div class="wlgf-flex wlgf-gap-2 wlgf-mt-3">
							<button type="button" id="wlgf-reset-conf-defaults-btn" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm">
								<i class="fa-solid fa-arrow-rotate-left"></i> <?php esc_html_e( 'Reset to Modern Defaults', 'lead-generation-form' ); ?>
							</button>
						</div>
					</div>
				</div>

				<!-- Right Card: Real-Time Live Preview -->
				<div class="wlgf-card">
					<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center">
						<h2 class="wlgf-card__title">
							<i class="fa-solid fa-eye text-primary"></i> <?php esc_html_e( 'Live Frontend Preview', 'lead-generation-form' ); ?>
						</h2>
						<button type="button" id="wlgf-replay-conf-animation-btn" class="wlgf-btn wlgf-btn--secondary wlgf-btn--sm" title="<?php esc_attr_e( 'Test the pop animation', 'lead-generation-form' ); ?>">
							<i class="fa-solid fa-play"></i> <?php esc_html_e( 'Replay Animation', 'lead-generation-form' ); ?>
						</button>
					</div>
					<div class="wlgf-card__body wlgf-conf-preview-container">
						<div class="wlgf-conf-preview-wrapper">
							<div id="wlgf-conf-live-preview">
								<?php echo wlgf_get_confirmation_html( '', $wlgf_settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>
						<p class="wlgf-conf-preview-note">
							<i class="fa-solid fa-circle-info text-primary"></i> <span><?php esc_html_e( 'This interactive preview reflects changes in real-time and matches what users see after form submission.', 'lead-generation-form' ); ?></span>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- TAB 2: Email & SMTP -->
		<div id="wlgf-tab-smtp" class="wlgf-tab-content">
			<div class="wlgf-grid wlgf-grid--2">
				<div class="wlgf-card">
					<div class="wlgf-card__header">
						<h2 class="wlgf-card__title">
							<i class="fa-solid fa-envelope-circle-check"></i> <?php esc_html_e( 'Admin Email Notifications', 'lead-generation-form' ); ?>
						</h2>
					</div>
					<div class="wlgf-card__body">
						<div class="wlgf-form-group">
							<label for="notify_admin" class="wlgf-label"><?php esc_html_e( 'Send Lead Notifications to Admin', 'lead-generation-form' ); ?></label>
							<select class="wlgf-select wlgf-w-full" id="notify_admin" name="notify_admin">
								<option value="1" <?php selected( $wlgf_notify_admin, '1' ); ?>><?php esc_html_e( 'ON - Send instant email per lead submission', 'lead-generation-form' ); ?></option>
								<option value="2" <?php selected( $wlgf_notify_admin, '2' ); ?>><?php esc_html_e( 'OFF - Store leads silently in dashboard only', 'lead-generation-form' ); ?></option>
							</select>
							<p class="wlgf-help-text">
								<?php
								/* translators: %s: admin email */
								printf( esc_html__( 'When enabled, emails are dispatched to %s with full submission data.', 'lead-generation-form' ), '<code>' . esc_html( $wlgf_admin_email ) . '</code>' );
								?>
							</p>
						</div>

						<div class="wlgf-form-group wlgf-email-engine-group <?php echo ( '2' === (string) $wlgf_notify_admin ) ? 'd-none' : ''; ?>">
							<label for="email_engine" class="wlgf-label"><?php esc_html_e( 'Dispatch Method', 'lead-generation-form' ); ?></label>
							<select class="wlgf-select wlgf-w-full" id="email_engine" name="email_engine">
								<option value="1" <?php selected( $wlgf_email_engine, '1' ); ?>><?php esc_html_e( 'Default WordPress Mail (wp_mail)', 'lead-generation-form' ); ?></option>
								<option value="2" <?php selected( $wlgf_email_engine, '2' ); ?>><?php esc_html_e( 'Custom SMTP Server', 'lead-generation-form' ); ?></option>
							</select>
							<p class="wlgf-help-text wlgf-wpmail-note <?php echo ( '2' === (string) $wlgf_email_engine ) ? 'd-none' : ''; ?>">
								<i class="fa-solid fa-circle-info text-primary"></i> <?php esc_html_e( 'wp_mail relies on your web hosting mail service. If your server blocks PHP mail() or emails land in spam, switch to Custom SMTP Server for authenticated, 100% reliable deliverability.', 'lead-generation-form' ); ?>
							</p>
						</div>

						<!-- CC Additional Admin Email Notifications -->
						<div class="wlgf-form-group wlgf-cc-admin-group <?php echo ( '2' === (string) $wlgf_notify_admin ) ? 'd-none' : ''; ?>">
							<label for="cc_admin_emails" class="wlgf-label" style="display: flex; align-items: center; justify-content: space-between;">
								<span><i class="fa-solid fa-copy text-primary"></i> <?php esc_html_e( 'CC Additional Admin Email(s)', 'lead-generation-form' ); ?></span>
								<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
									<span class="wlgf-badge wlgf-badge--warning" style="font-size: 10px; font-weight: 700;"><?php esc_html_e( 'PRO', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-badge wlgf-badge--muted" style="font-size: 11px;"><?php esc_html_e( 'Multi-Recipient', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</label>
							<textarea class="wlgf-textarea" rows="3" id="cc_admin_emails" name="cc_admin_emails" placeholder="sales@yourdomain.com&#10;manager@yourdomain.com" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>><?php echo esc_textarea( $wlgf_cc_admin_emails ); ?></textarea>
							<p class="wlgf-help-text">
								<?php esc_html_e( 'Send a carbon copy (Cc:) of each new lead notification to additional team members. Enter valid email addresses separated by commas or line breaks.', 'lead-generation-form' ); ?>
								<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
									<br><span class="wlgf-text-warning"><i class="fa-solid fa-lock"></i> <?php esc_html_e( 'Multi-Admin CC Routing is available in Lead Generation Form Pro.', 'lead-generation-form' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-link-external"><?php esc_html_e( 'Upgrade to Pro', 'lead-generation-form' ); ?></a></span>
								<?php endif; ?>
							</p>
						</div>

						<!-- Quick Test Email Area -->
						<div class="wlgf-form-group wlgf-quick-test-group wlgf-mt-4 <?php echo ( '2' === (string) $wlgf_notify_admin ) ? 'd-none' : ''; ?>" style="padding-top: 16px; border-top: 1px dashed var(--wlgf-border);">
							<label for="wlgf_quick_test_email" class="wlgf-label" style="display: flex; align-items: center; justify-content: space-between;">
								<span><i class="fa-solid fa-paper-plane text-primary"></i> <?php esc_html_e( 'Send Test Email', 'lead-generation-form' ); ?></span>
								<span class="wlgf-text-muted" style="font-size: 11px; font-weight: normal;"><?php esc_html_e( 'Verify email dispatch & deliverability', 'lead-generation-form' ); ?></span>
							</label>
							<div class="wlgf-flex wlgf-gap-2">
								<input type="email" class="wlgf-input" id="wlgf_quick_test_email" value="<?php echo esc_attr( $wlgf_admin_email ); ?>" placeholder="test-recipient@domain.com">
								<button type="button" id="wlgf-quick-test-email-btn" class="wlgf-btn wlgf-btn--outline" style="white-space: nowrap;">
									<i class="fa-solid fa-paper-plane"></i> <?php esc_html_e( 'Send Test Email', 'lead-generation-form' ); ?>
								</button>
							</div>
							<div id="wlgf-quick-test-email-result" class="wlgf-mt-2"></div>
						</div>
					</div>
				</div>

				<!-- Custom SMTP Settings -->
				<div class="wlgf-card wlgf-smtp-card <?php echo ( '2' === (string) $wlgf_notify_admin || '2' !== (string) $wlgf_email_engine ) ? 'd-none' : ''; ?>">
					<div class="wlgf-card__header">
						<h2 class="wlgf-card__title">
							<i class="fa-solid fa-server"></i> <?php esc_html_e( 'SMTP Server Credentials', 'lead-generation-form' ); ?>
						</h2>
					</div>
					<div class="wlgf-card__body">
						<div class="wlgf-form-group">
							<label for="smtp_host" class="wlgf-label"><?php esc_html_e( 'SMTP Host', 'lead-generation-form' ); ?></label>
							<input type="text" class="wlgf-input" id="smtp_host" name="smtp_host" value="<?php echo esc_attr( $wlgf_smtp_host ); ?>" placeholder="e.g. smtp.gmail.com">
						</div>

						<div class="wlgf-form-group">
							<label for="smtp_username" class="wlgf-label"><?php esc_html_e( 'SMTP Username', 'lead-generation-form' ); ?></label>
							<input type="text" class="wlgf-input" id="smtp_username" name="smtp_username" value="<?php echo esc_attr( $wlgf_smtp_username ); ?>" placeholder="e.g. you@gmail.com">
						</div>

						<div class="wlgf-form-group">
							<label for="smtp_password" class="wlgf-label"><?php esc_html_e( 'SMTP Password / App Password', 'lead-generation-form' ); ?></label>
							<div style="position: relative; display: flex; align-items: center;">
								<input type="password" class="wlgf-input" id="smtp_password" name="smtp_password" value="" autocomplete="new-password" placeholder="<?php echo ! empty( $wlgf_smtp_password ) ? esc_attr__( '•••••••••••• (Leave blank to keep existing)', 'lead-generation-form' ) : esc_attr__( 'Enter SMTP App Password', 'lead-generation-form' ); ?>" style="padding-right: 42px;">
								<button type="button" id="wlgf-toggle-smtp-pw" class="wlgf-btn" style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: none; background: transparent; padding: 4px 8px; cursor: pointer; color: #64748b;" title="<?php esc_attr_e( 'Show/Hide Password', 'lead-generation-form' ); ?>">
									<i class="fa-solid fa-eye" id="wlgf-toggle-smtp-pw-icon"></i>
								</button>
							</div>
							<p class="wlgf-help-text">
								<?php esc_html_e( 'For Gmail, standard account passwords will not work. Generate a 16-character App Password at myaccount.google.com/apppasswords with 2-Step Verification enabled.', 'lead-generation-form' ); ?>
							</p>
						</div>

						<div class="wlgf-grid wlgf-grid--2">
							<div class="wlgf-form-group">
								<label for="smtp_encryption" class="wlgf-label"><?php esc_html_e( 'Encryption', 'lead-generation-form' ); ?></label>
								<select class="wlgf-select wlgf-w-full" id="smtp_encryption" name="smtp_encryption">
									<option value="1" <?php selected( $wlgf_smtp_encryption, '1' ); ?>>TLS (Recommended)</option>
									<option value="2" <?php selected( $wlgf_smtp_encryption, '2' ); ?>>SSL</option>
								</select>
							</div>

							<div class="wlgf-form-group">
								<label for="smtp_port" class="wlgf-label"><?php esc_html_e( 'SMTP Port', 'lead-generation-form' ); ?></label>
								<input type="number" class="wlgf-input" id="smtp_port" name="smtp_port" value="<?php echo esc_attr( $wlgf_smtp_port ); ?>" placeholder="587">
							</div>
						</div>

						<hr class="wlgf-divider">

						<!-- Live SMTP Test Area -->
						<div class="wlgf-form-group">
							<label for="wlgf_test_email" class="wlgf-label"><?php esc_html_e( 'Test SMTP Connection', 'lead-generation-form' ); ?></label>
							<div class="wlgf-flex wlgf-gap-2">
								<input type="email" class="wlgf-input" id="wlgf_test_email" value="<?php echo esc_attr( $wlgf_admin_email ); ?>" placeholder="test-recipient@domain.com">
								<button type="button" id="wlgf-test-smtp-btn" class="wlgf-btn wlgf-btn--outline">
									<i class="fa-solid fa-paper-plane"></i> <?php esc_html_e( 'Send Test Email', 'lead-generation-form' ); ?>
								</button>
							</div>
							<div id="wlgf-smtp-test-result" class="wlgf-mt-2"></div>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION: Email Notification Template Designer & Branding -->
			<div class="wlgf-card wlgf-mt-4">
				<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center wlgf-flex-wrap wlgf-gap-2">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-palette text-primary"></i> <?php esc_html_e( 'Email Notification Template & Branding', 'lead-generation-form' ); ?>
					</h2>
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<button type="button" id="wlgf-open-test-modal-btn" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm">
							<i class="fa-solid fa-paper-plane"></i> <?php esc_html_e( 'Send Test Email', 'lead-generation-form' ); ?>
						</button>
						<button type="button" id="wlgf-preview-template-btn" class="wlgf-btn wlgf-btn--secondary wlgf-btn--sm">
							<i class="fa-solid fa-eye"></i> <?php esc_html_e( 'Live Template Preview', 'lead-generation-form' ); ?>
						</button>
					</div>
				</div>
				<div class="wlgf-card__body">
					<p class="wlgf-help-text wlgf-mb-4">
						<?php esc_html_e( 'Customize the visual layout, branding colors, custom logo, header, body greeting, and footer disclaimer for incoming lead notification emails.', 'lead-generation-form' ); ?>
					</p>

					<div class="wlgf-grid wlgf-grid--2 wlgf-gap-4">
						<!-- Left Column: Branding & Header -->
						<div>
							<h3 class="wlgf-section-subtitle" style="font-size: 14.5px; font-weight: 700; margin-bottom: 14px; color: #1e293b;">
								<i class="fa-solid fa-image text-primary"></i> <?php esc_html_e( 'Brand Logo & Header Design', 'lead-generation-form' ); ?>
							</h3>

							<!-- Custom Logo Upload -->
							<div class="wlgf-form-group">
								<label class="wlgf-label"><?php esc_html_e( 'Custom Brand Logo', 'lead-generation-form' ); ?></label>
								<div class="wlgf-flex wlgf-items-center wlgf-gap-3 wlgf-flex-wrap">
									<div id="wlgf-logo-preview-wrap" class="wlgf-logo-preview-box <?php echo empty( $wlgf_email_logo_url ) ? 'd-none' : ''; ?>">
										<img id="wlgf-logo-preview-img" src="<?php echo esc_url( $wlgf_email_logo_url ); ?>" alt="Logo Preview" style="max-height: 48px; max-width: 140px; display: block;">
									</div>
									<div class="wlgf-flex wlgf-gap-2">
										<button type="button" id="wlgf-upload-logo-btn" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm">
											<i class="fa-solid fa-upload"></i> <?php esc_html_e( 'Select / Upload Logo', 'lead-generation-form' ); ?>
										</button>
										<button type="button" id="wlgf-remove-logo-btn" class="wlgf-btn wlgf-btn--danger-outline wlgf-btn--sm <?php echo empty( $wlgf_email_logo_url ) ? 'd-none' : ''; ?>">
											<i class="fa-solid fa-trash-can"></i> <?php esc_html_e( 'Remove', 'lead-generation-form' ); ?>
										</button>
									</div>
								</div>
								<input type="hidden" name="email_logo_url" id="email_logo_url" value="<?php echo esc_attr( $wlgf_email_logo_url ); ?>">
								<p class="wlgf-help-text"><?php esc_html_e( 'Displayed centered in the email header. Supports PNG, SVG, or JPG.', 'lead-generation-form' ); ?></p>
							</div>

							<!-- Logo Width -->
							<div class="wlgf-form-group">
								<label for="email_logo_width" class="wlgf-label"><?php esc_html_e( 'Logo Display Max Width (px)', 'lead-generation-form' ); ?></label>
								<input type="number" name="email_logo_width" id="email_logo_width" class="wlgf-input" value="<?php echo esc_attr( $wlgf_email_logo_width ); ?>" min="60" max="320" step="10" placeholder="180">
							</div>

							<!-- Header Title -->
							<div class="wlgf-form-group">
								<label for="email_header_title" class="wlgf-label"><?php esc_html_e( 'Header Title / Subject', 'lead-generation-form' ); ?></label>
								<input type="text" name="email_header_title" id="email_header_title" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_email_header_title ); ?>" placeholder="{form_name} - New Lead Submission">
								<p class="wlgf-help-text"><?php esc_html_e( 'Smart tags: {form_name}, {site_title}, {form_id}', 'lead-generation-form' ); ?></p>
							</div>

							<!-- Header Colors -->
							<div class="wlgf-grid wlgf-grid--2">
								<div class="wlgf-form-group">
									<label for="email_header_bg" class="wlgf-label"><?php esc_html_e( 'Header Background', 'lead-generation-form' ); ?></label>
									<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
										<input type="color" name="email_header_bg" id="email_header_bg" value="<?php echo esc_attr( $wlgf_email_header_bg ); ?>" class="wlgf-color-picker">
										<input type="text" id="email_header_bg_text" class="wlgf-input wlgf-input--sm wlgf-color-code" value="<?php echo esc_attr( $wlgf_email_header_bg ); ?>" maxlength="7" style="width: 85px;">
									</div>
								</div>
								<div class="wlgf-form-group">
									<label for="email_header_color" class="wlgf-label"><?php esc_html_e( 'Header Text Color', 'lead-generation-form' ); ?></label>
									<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
										<input type="color" name="email_header_color" id="email_header_color" value="<?php echo esc_attr( $wlgf_email_header_color ); ?>" class="wlgf-color-picker">
										<input type="text" id="email_header_color_text" class="wlgf-input wlgf-input--sm wlgf-color-code" value="<?php echo esc_attr( $wlgf_email_header_color ); ?>" maxlength="7" style="width: 85px;">
									</div>
								</div>
							</div>
						</div>

						<!-- Right Column: Body, Greeting & Footer -->
						<div>
							<h3 class="wlgf-section-subtitle" style="font-size: 14.5px; font-weight: 700; margin-bottom: 14px; color: #1e293b;">
								<i class="fa-solid fa-align-left text-primary"></i> <?php esc_html_e( 'Body Content & Footer Styling', 'lead-generation-form' ); ?>
							</h3>

							<!-- Greeting Message -->
							<div class="wlgf-form-group">
								<label for="email_body_greeting" class="wlgf-label"><?php esc_html_e( 'Email Body Greeting Text', 'lead-generation-form' ); ?></label>
								<textarea name="email_body_greeting" id="email_body_greeting" class="wlgf-textarea wlgf-w-full" rows="3"><?php echo esc_textarea( $wlgf_email_body_greeting ); ?></textarea>
								<p class="wlgf-help-text"><?php esc_html_e( 'Appears above the lead fields table in notification emails.', 'lead-generation-form' ); ?></p>
							</div>

							<!-- Body & Card Colors -->
							<div class="wlgf-grid wlgf-grid--3">
								<div class="wlgf-form-group">
									<label for="email_body_bg" class="wlgf-label"><?php esc_html_e( 'Email Outer BG', 'lead-generation-form' ); ?></label>
									<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
										<input type="color" name="email_body_bg" id="email_body_bg" value="<?php echo esc_attr( $wlgf_email_body_bg ); ?>" class="wlgf-color-picker">
										<input type="text" id="email_body_bg_text" class="wlgf-input wlgf-input--sm wlgf-color-code" value="<?php echo esc_attr( $wlgf_email_body_bg ); ?>" maxlength="7" style="width: 85px;">
									</div>
								</div>
								<div class="wlgf-form-group">
									<label for="email_card_bg" class="wlgf-label"><?php esc_html_e( 'Content Card BG', 'lead-generation-form' ); ?></label>
									<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
										<input type="color" name="email_card_bg" id="email_card_bg" value="<?php echo esc_attr( $wlgf_email_card_bg ); ?>" class="wlgf-color-picker">
										<input type="text" id="email_card_bg_text" class="wlgf-input wlgf-input--sm wlgf-color-code" value="<?php echo esc_attr( $wlgf_email_card_bg ); ?>" maxlength="7" style="width: 85px;">
									</div>
								</div>
								<div class="wlgf-form-group">
									<label for="email_text_color" class="wlgf-label"><?php esc_html_e( 'Body Text Color', 'lead-generation-form' ); ?></label>
									<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
										<input type="color" name="email_text_color" id="email_text_color" value="<?php echo esc_attr( $wlgf_email_text_color ); ?>" class="wlgf-color-picker">
										<input type="text" id="email_text_color_text" class="wlgf-input wlgf-input--sm wlgf-color-code" value="<?php echo esc_attr( $wlgf_email_text_color ); ?>" maxlength="7" style="width: 85px;">
									</div>
								</div>
							</div>

							<!-- Footer Text -->
							<div class="wlgf-form-group">
								<label for="email_footer_text" class="wlgf-label"><?php esc_html_e( 'Footer Disclaimer & Copyright Note', 'lead-generation-form' ); ?></label>
								<textarea name="email_footer_text" id="email_footer_text" class="wlgf-textarea wlgf-w-full" rows="2"><?php echo esc_textarea( $wlgf_email_footer_text ); ?></textarea>
							</div>

							<!-- Footer Colors -->
							<div class="wlgf-grid wlgf-grid--2">
								<div class="wlgf-form-group">
									<label for="email_footer_bg" class="wlgf-label"><?php esc_html_e( 'Footer BG', 'lead-generation-form' ); ?></label>
									<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
										<input type="color" name="email_footer_bg" id="email_footer_bg" value="<?php echo esc_attr( $wlgf_email_footer_bg ); ?>" class="wlgf-color-picker">
										<input type="text" id="email_footer_bg_text" class="wlgf-input wlgf-input--sm wlgf-color-code" value="<?php echo esc_attr( $wlgf_email_footer_bg ); ?>" maxlength="7" style="width: 85px;">
									</div>
								</div>
								<div class="wlgf-form-group">
									<label for="email_footer_color" class="wlgf-label"><?php esc_html_e( 'Footer Text Color', 'lead-generation-form' ); ?></label>
									<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
										<input type="color" name="email_footer_color" id="email_footer_color" value="<?php echo esc_attr( $wlgf_email_footer_color ); ?>" class="wlgf-color-picker">
										<input type="text" id="email_footer_color_text" class="wlgf-input wlgf-input--sm wlgf-color-code" value="<?php echo esc_attr( $wlgf_email_footer_color ); ?>" maxlength="7" style="width: 85px;">
									</div>
								</div>
							</div>

							<!-- Quick Dashboard Link Toggle -->
							<div class="wlgf-form-group wlgf-mt-2">
								<label class="wlgf-checkbox-label">
									<input type="checkbox" name="email_show_dashboard_btn" id="email_show_dashboard_btn" value="1" <?php checked( '1', $wlgf_email_show_dashboard ); ?>>
									<span><?php esc_html_e( 'Include "View Lead in Dashboard" button in notification emails', 'lead-generation-form' ); ?></span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- TAB 3: Anti-Spam Security -->
		<div id="wlgf-tab-security" class="wlgf-tab-content">
			<div class="wlgf-card">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-shield-halved"></i> <?php esc_html_e( 'CAPTCHA & Bot Protection', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<div class="wlgf-form-group">
						<label for="captcha_provider" class="wlgf-label"><?php esc_html_e( 'Spam & CAPTCHA Provider', 'lead-generation-form' ); ?></label>
						<select class="wlgf-select wlgf-w-full" id="captcha_provider" name="captcha_provider">
							<option value="none" <?php selected( $wlgf_captcha_provider, 'none' ); ?>><?php esc_html_e( 'None / Disabled', 'lead-generation-form' ); ?></option>
							<option value="recaptcha" <?php selected( $wlgf_captcha_provider, 'recaptcha' ); ?>><?php esc_html_e( 'Google reCAPTCHA v3 (Invisible)', 'lead-generation-form' ); ?></option>
							<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
								<option value="turnstile" <?php selected( $wlgf_captcha_provider, 'turnstile' ); ?>><?php esc_html_e( 'Cloudflare Turnstile (Recommended - Privacy-First, 100% Free)', 'lead-generation-form' ); ?></option>
							<?php else : ?>
								<option value="turnstile" disabled <?php selected( $wlgf_captcha_provider, 'turnstile' ); ?>><?php esc_html_e( 'Cloudflare Turnstile [PRO - Upgrade to Unlock]', 'lead-generation-form' ); ?></option>
							<?php endif; ?>
						</select>
						<input type="hidden" id="recaptcha" name="recaptcha" value="<?php echo esc_attr( $wlgf_recaptcha ); ?>">
					</div>

					<!-- Cloudflare Turnstile Settings -->
					<div id="wlgf-turnstile-keys" class="<?php echo ( 'turnstile' !== $wlgf_captcha_provider ) ? 'd-none' : ''; ?>">
						<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
							<div class="wlgf-alert wlgf-alert--info wlgf-mb-3">
								<i class="fa-solid fa-crown text-warning"></i>
								<div>
									<strong><?php esc_html_e( 'Cloudflare Turnstile is a Pro Feature', 'lead-generation-form' ); ?></strong>
									<p class="wlgf-m-0"><?php esc_html_e( 'Frictionless, privacy-first bot detection with zero user puzzles. Upgrade to Pro to activate Cloudflare Turnstile verification.', 'lead-generation-form' ); ?></p>
									<div class="wlgf-mt-2">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary">
											<i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Upgrade to Pro to Unlock', 'lead-generation-form' ); ?>
										</a>
									</div>
								</div>
							</div>
						<?php else : ?>
							<div class="wlgf-alert wlgf-alert--info wlgf-mb-3">
								<i class="fa-brands fa-cloudflare"></i>
								<?php // phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- External link to Cloudflare dashboard. ?>
								<span><?php esc_html_e( 'Cloudflare Turnstile provides smart, non-interactive verification that stops spam bots without annoying user challenges.', 'lead-generation-form' ); ?> <a href="<?php echo esc_url( 'https://dash.cloudflare.com/?to=/:account/turnstile' ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-link-external"><?php esc_html_e( 'Get Free Turnstile Keys', 'lead-generation-form' ); ?> <i class="fa-solid fa-arrow-up-right-from-square"></i></a></span>
							</div>
						<?php endif; ?>
						<fieldset <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?> style="border:none;padding:0;margin:0;">
						<div class="wlgf-form-group">
							<label for="turnstile_sitekey" class="wlgf-label"><?php esc_html_e( 'Turnstile Site Key', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span></label>
							<input type="text" class="wlgf-input" id="turnstile_sitekey" name="turnstile_sitekey" value="<?php echo esc_attr( $wlgf_turnstile_sitekey ); ?>" placeholder="0x4AAAAAA...">
						</div>

						<div class="wlgf-form-group">
							<label for="turnstile_secretkey" class="wlgf-label"><?php esc_html_e( 'Turnstile Secret Key', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span></label>
							<input type="password" class="wlgf-input" id="turnstile_secretkey" name="turnstile_secretkey" value="<?php echo ! empty( $wlgf_turnstile_secretkey ) ? '••••••••••••' : ''; ?>" placeholder="0x4AAAAAA...">
						</div>
						</fieldset>
					</div>

					<!-- Google reCAPTCHA v3 Settings -->
					<div id="wlgf-recaptcha-keys" class="<?php echo ( 'recaptcha' !== $wlgf_captcha_provider ) ? 'd-none' : ''; ?>">
						<div class="wlgf-form-group">
							<label for="sitekey" class="wlgf-label"><?php esc_html_e( 'Google reCAPTCHA v3 Site Key', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span></label>
							<input type="text" class="wlgf-input" id="sitekey" name="sitekey" value="<?php echo esc_attr( $wlgf_sitekey ); ?>" placeholder="Enter Google reCAPTCHA v3 Site Key">
						</div>

						<div class="wlgf-form-group">
							<label for="secretkey" class="wlgf-label"><?php esc_html_e( 'Google reCAPTCHA v3 Secret Key', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span></label>
							<input type="password" class="wlgf-input" id="secretkey" name="secretkey" value="<?php echo ! empty( $wlgf_secretkey ) ? '••••••••••••' : ''; ?>" placeholder="Enter Google reCAPTCHA v3 Secret Key">
							<p class="wlgf-help-text">
								<?php esc_html_e( 'Register your domain at Google reCAPTCHA Admin console to obtain your v3 keys.', 'lead-generation-form' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Card: Automated Abuse & Bot Shields -->
			<div class="wlgf-card wlgf-mt-4">
				<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-user-shield text-primary"></i> <?php esc_html_e( 'Automated Abuse & Flood Shields', 'lead-generation-form' ); ?>
					</h2>
					<span class="wlgf-badge wlgf-badge--success">
						<i class="fa-solid fa-lock"></i> <?php esc_html_e( 'Core Defense Active', 'lead-generation-form' ); ?>
					</span>
				</div>
				<div class="wlgf-card__body">
					<p class="wlgf-help-text wlgf-mb-4">
						<?php esc_html_e( 'Protect your mail server and database from scripted email flooding, automated pentest fuzzers, and headless browser bots.', 'lead-generation-form' ); ?>
					</p>

					<div class="wlgf-grid wlgf-grid--3 wlgf-gap-4">
						<div class="wlgf-form-group">
							<label for="submission_rate_limit" class="wlgf-label">
								<i class="fa-solid fa-stopwatch text-primary"></i> <?php esc_html_e( 'IP Rate Limiting (Anti-Flood)', 'lead-generation-form' ); ?>
							</label>
							<select class="wlgf-select wlgf-w-full" id="submission_rate_limit" name="submission_rate_limit">
								<option value="1" <?php selected( $wlgf_submission_rate_limit, '1' ); ?>><?php esc_html_e( 'ON - Enabled (Default)', 'lead-generation-form' ); ?></option>
								<option value="2" <?php selected( $wlgf_submission_rate_limit, '2' ); ?>><?php esc_html_e( 'OFF - Disabled', 'lead-generation-form' ); ?></option>
							</select>
							<p class="wlgf-help-text">
								<?php esc_html_e( 'Limits rapid submissions to max 3 per 2 mins per IP. Protects inbox and mail server from script loops.', 'lead-generation-form' ); ?>
							</p>
						</div>

						<div class="wlgf-form-group">
							<label for="honeypot_status" class="wlgf-label">
								<i class="fa-solid fa-spider text-primary"></i> <?php esc_html_e( 'Smart CSS Decoy Honeypot Trap', 'lead-generation-form' ); ?>
							</label>
							<select class="wlgf-select wlgf-w-full" id="honeypot_status" name="honeypot_status">
								<option value="1" <?php selected( $wlgf_honeypot_status, '1' ); ?>><?php esc_html_e( 'ON - Enabled (Default)', 'lead-generation-form' ); ?></option>
								<option value="2" <?php selected( $wlgf_honeypot_status, '2' ); ?>><?php esc_html_e( 'OFF - Disabled', 'lead-generation-form' ); ?></option>
							</select>
							<p class="wlgf-help-text">
								<?php esc_html_e( 'Injects an invisible off-screen decoy trap. Bots autofill it and are silently dropped without sending emails.', 'lead-generation-form' ); ?>
							</p>
						</div>

						<div class="wlgf-form-group">
							<label for="scanner_shield_status" class="wlgf-label">
								<i class="fa-solid fa-bug-slash text-primary"></i> <?php esc_html_e( 'Vulnerability Scanner & SQLi Shield', 'lead-generation-form' ); ?>
							</label>
							<select class="wlgf-select wlgf-w-full" id="scanner_shield_status" name="scanner_shield_status">
								<option value="1" <?php selected( $wlgf_scanner_shield_status, '1' ); ?>><?php esc_html_e( 'ON - Enabled (Default)', 'lead-generation-form' ); ?></option>
								<option value="2" <?php selected( $wlgf_scanner_shield_status, '2' ); ?>><?php esc_html_e( 'OFF - Disabled', 'lead-generation-form' ); ?></option>
							</select>
							<p class="wlgf-help-text">
								<?php esc_html_e( 'Auto-detects and drops automated pentest probes testing sleep(), PG_SLEEP, waitfor delay, or UNION SELECT.', 'lead-generation-form' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Card: Email & Domain Blacklist Protection -->
			<div class="wlgf-card wlgf-mt-4">
				<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-ban text-danger"></i> <?php esc_html_e( 'Email & Domain Blacklist Protection', 'lead-generation-form' ); ?>
					</h2>
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<span class="wlgf-badge wlgf-badge--warning"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO Feature', 'lead-generation-form' ); ?></span>
					<?php else : ?>
						<span id="wlgf-blacklist-badge" class="wlgf-badge <?php echo ( '1' === (string) $wlgf_blacklist_status ) ? 'wlgf-badge--success' : 'wlgf-badge--secondary'; ?>">
							<i class="fa-solid <?php echo ( '1' === (string) $wlgf_blacklist_status ) ? 'fa-shield-halved' : 'fa-pause'; ?>" id="wlgf-blacklist-badge-icon"></i>
							<span id="wlgf-blacklist-badge-text"><?php echo ( '1' === (string) $wlgf_blacklist_status ) ? esc_html__( 'Active & Blocking', 'lead-generation-form' ) : esc_html__( 'Enforcement Paused', 'lead-generation-form' ); ?></span>
						</span>
					<?php endif; ?>
				</div>
				<div class="wlgf-card__body">
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<div class="wlgf-alert wlgf-alert--info wlgf-mb-4">
							<i class="fa-solid fa-circle-info"></i>
							<div>
								<strong><?php esc_html_e( 'Email & Domain Blacklist Protection is a Pro Feature', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-m-0"><?php esc_html_e( 'Prevent spam bots, disposable temporary mailboxes, phishing entries, and competitor emails from submitting your forms by filtering specific email addresses or entire domain names.', 'lead-generation-form' ); ?></p>
								<div class="wlgf-mt-2">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary">
										<i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Upgrade to Pro to Unlock', 'lead-generation-form' ); ?>
									</a>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<p class="wlgf-help-text wlgf-mb-4">
						<?php esc_html_e( 'Prevent spam bots, disposable temporary mailboxes, phishing entries, and competitor emails from submitting your forms by filtering specific email addresses or entire domain names.', 'lead-generation-form' ); ?>
					</p>

					<fieldset <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?> style="border:none;padding:0;margin:0;">
					<div class="wlgf-form-group">
						<label for="blacklist_status" class="wlgf-label"><?php esc_html_e( 'Blacklist Enforcement Status', 'lead-generation-form' ); ?></label>
						<select class="wlgf-select wlgf-w-full" id="blacklist_status" name="blacklist_status">
							<option value="1" <?php selected( $wlgf_blacklist_status, '1' ); ?>><?php esc_html_e( 'ON - Actively block blacklisted emails and domains', 'lead-generation-form' ); ?></option>
							<option value="2" <?php selected( $wlgf_blacklist_status, '2' ); ?>><?php esc_html_e( 'OFF - Disabled / Paused (Keep list saved, allow all submissions)', 'lead-generation-form' ); ?></option>
						</select>
					</div>

					<div id="wlgf-blacklist-paused-notice" class="wlgf-alert wlgf-alert--info wlgf-mb-3 <?php echo ( '1' === (string) $wlgf_blacklist_status ) ? 'd-none' : ''; ?>">
						<i class="fa-solid fa-circle-info"></i>
						<span><?php esc_html_e( 'Enforcement is currently Paused. You can add and manage blocked emails/domains below; they will be enforced once status is set to ON and saved.', 'lead-generation-form' ); ?></span>
					</div>

					<!-- Blocklist Fields Container: Always visible so admin can edit anytime -->
					<div id="wlgf-blacklist-controls">
						<div class="wlgf-grid wlgf-grid--2 wlgf-gap-4">
							<div class="wlgf-form-group">
								<label for="blacklist_emails" class="wlgf-label">
									<i class="fa-solid fa-at text-primary"></i> <?php esc_html_e( 'Blocked Email Addresses', 'lead-generation-form' ); ?>
								</label>
								<textarea class="wlgf-textarea" rows="6" id="blacklist_emails" name="blacklist_emails" placeholder="spammer@example.com&#10;fake-user@scam.org&#10;abusive@competitor.com"><?php echo esc_textarea( $wlgf_blacklist_emails ); ?></textarea>
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Exact email addresses to prohibit from submitting. Enter one email per line or separate with commas.', 'lead-generation-form' ); ?>
								</p>
							</div>

							<div class="wlgf-form-group">
								<label for="blacklist_domains" class="wlgf-label">
									<i class="fa-solid fa-globe text-primary"></i> <?php esc_html_e( 'Blocked Domains & TLDs', 'lead-generation-form' ); ?>
								</label>
								<textarea class="wlgf-textarea" rows="6" id="blacklist_domains" name="blacklist_domains" placeholder="mailinator.com&#10;tempmail.com&#10;guerrillamail.com&#10;*.xyz&#10;.ru"><?php echo esc_textarea( $wlgf_blacklist_domains ); ?></textarea>
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Block temporary/disposable mail domains, competitor domains, or high-risk TLDs (e.g. mailinator.com, tempmail.com, .xyz, *.ru). One per line or comma-separated.', 'lead-generation-form' ); ?>
								</p>
								<div class="wlgf-chip-container">
									<span class="wlgf-chip-label"><i class="fa-solid fa-bolt"></i> <?php esc_html_e( 'Quick Add:', 'lead-generation-form' ); ?></span>
									<button type="button" class="wlgf-chip-btn wlgf-quick-add-domain" data-domain="mailinator.com" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>+ mailinator.com</button>
									<button type="button" class="wlgf-chip-btn wlgf-quick-add-domain" data-domain="tempmail.com" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>+ tempmail.com</button>
									<button type="button" class="wlgf-chip-btn wlgf-quick-add-domain" data-domain="10minutemail.com" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>+ 10minutemail.com</button>
									<button type="button" class="wlgf-chip-btn wlgf-quick-add-domain" data-domain="guerrillamail.com" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>+ guerrillamail.com</button>
									<button type="button" class="wlgf-chip-btn wlgf-quick-add-domain" data-domain="*.xyz" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>+ *.xyz</button>
									<button type="button" class="wlgf-chip-btn wlgf-quick-add-domain" data-domain="*.top" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>+ *.top</button>
									<button type="button" class="wlgf-chip-btn wlgf-quick-add-domain" data-domain=".ru" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>+ .ru</button>
								</div>
							</div>
						</div>

						<div class="wlgf-grid wlgf-grid--2 wlgf-gap-4 wlgf-mt-3">
							<div class="wlgf-form-group">
								<label for="blacklist_action" class="wlgf-label"><?php esc_html_e( 'Action When Blocked', 'lead-generation-form' ); ?></label>
								<select class="wlgf-select wlgf-w-full" id="blacklist_action" name="blacklist_action">
									<option value="reject_error" <?php selected( $wlgf_blacklist_action, 'reject_error' ); ?>><?php esc_html_e( 'Show Error Notice (Rejects submission with alert)', 'lead-generation-form' ); ?></option>
									<option value="silent_drop" <?php selected( $wlgf_blacklist_action, 'silent_drop' ); ?>><?php esc_html_e( 'Silent Drop / Honeypot (Pretend success, but silently discard data & send no emails)', 'lead-generation-form' ); ?></option>
								</select>
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Silent Drop prevents spam bots and phishers from knowing they have been filtered.', 'lead-generation-form' ); ?>
								</p>
							</div>

							<div class="wlgf-form-group <?php echo ( 'silent_drop' === (string) $wlgf_blacklist_action ) ? 'd-none' : ''; ?>" id="wlgf-blacklist-msg-wrap">
								<label for="blacklist_message" class="wlgf-label"><?php esc_html_e( 'Custom Rejection Notice', 'lead-generation-form' ); ?></label>
								<input type="text" class="wlgf-input" id="blacklist_message" name="blacklist_message" value="<?php echo esc_attr( $wlgf_blacklist_message ); ?>" placeholder="<?php esc_attr_e( 'Sorry! Your email address or domain is not permitted to submit this form.', 'lead-generation-form' ); ?>">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Message displayed to frontend visitor when "Show Error Notice" is active.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>
					</div>
					</fieldset>
				</div>
			</div>
		</div>

		<!-- TAB 4: Newsletter & Integrations (PRO-008) -->
		<div id="wlgf-tab-integrations" class="wlgf-tab-content">
			<div class="wlgf-card">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-paper-plane"></i> <?php esc_html_e( 'Email Marketing & Newsletter Integration', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<div class="wlgf-form-group">
						<label for="newsletter_provider" class="wlgf-label">
							<?php esc_html_e( 'Primary Newsletter Provider', 'lead-generation-form' ); ?>
						</label>
						<select class="wlgf-select wlgf-w-full" id="newsletter_provider" name="newsletter_provider">
							<option value="local" <?php selected( $wlgf_newsletter_provider, 'local' ); ?>><?php esc_html_e( 'Local WordPress CRM (No External API Required)', 'lead-generation-form' ); ?></option>
							<option value="mailchimp" <?php selected( $wlgf_newsletter_provider, 'mailchimp' ); ?>><?php esc_html_e( 'Mailchimp', 'lead-generation-form' ); ?></option>
							<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
								<option value="brevo" <?php selected( $wlgf_newsletter_provider, 'brevo' ); ?>><?php esc_html_e( 'Brevo (formerly Sendinblue)', 'lead-generation-form' ); ?></option>
								<option value="mailerlite" <?php selected( $wlgf_newsletter_provider, 'mailerlite' ); ?>><?php esc_html_e( 'MailerLite', 'lead-generation-form' ); ?></option>
								<option value="sendgrid" <?php selected( $wlgf_newsletter_provider, 'sendgrid' ); ?>><?php esc_html_e( 'SendGrid Marketing', 'lead-generation-form' ); ?></option>
								<option value="activecampaign" <?php selected( $wlgf_newsletter_provider, 'activecampaign' ); ?>><?php esc_html_e( 'ActiveCampaign', 'lead-generation-form' ); ?></option>
							<?php else : ?>
								<option value="brevo_pro" disabled><?php esc_html_e( 'Brevo (Sendinblue) [Pro]', 'lead-generation-form' ); ?></option>
								<option value="mailerlite_pro" disabled><?php esc_html_e( 'MailerLite [Pro]', 'lead-generation-form' ); ?></option>
								<option value="sendgrid_pro" disabled><?php esc_html_e( 'SendGrid Marketing [Pro]', 'lead-generation-form' ); ?></option>
								<option value="activecampaign_pro" disabled><?php esc_html_e( 'ActiveCampaign [Pro]', 'lead-generation-form' ); ?></option>
							<?php endif; ?>
						</select>
						<p class="wlgf-help-text">
							<?php esc_html_e( 'Select the service where newsletter subscribers will be pushed when they opt-in upon form submission.', 'lead-generation-form' ); ?>
						</p>
					</div>

					<!-- Local CRM Card Info -->
					<div id="wlgf-card-provider-local" class="wlgf-alert wlgf-alert--primary wlgf-mt-3 <?php echo ( 'local' === $wlgf_newsletter_provider || empty( $wlgf_newsletter_provider ) ) ? '' : 'd-none'; ?>">
						<i class="fa-solid fa-circle-info"></i>
						<div>
							<strong><?php esc_html_e( 'Built-in Local Subscriber Hub Active', 'lead-generation-form' ); ?></strong>
							<p class="wlgf-mt-1 wlgf-mb-0"><?php esc_html_e( 'Opted-in contacts are tagged with an "Opted-in" badge in your Captured Leads table. You can filter and export subscribers anytime in 1-click.', 'lead-generation-form' ); ?></p>
						</div>
					</div>

					<!-- Mailchimp Configuration Card -->
					<div id="wlgf-card-provider-mailchimp" class="wlgf-card wlgf-card--bordered wlgf-mt-3 <?php echo ( 'mailchimp' === $wlgf_newsletter_provider ) ? '' : 'd-none'; ?>">
						<div class="wlgf-card__header">
							<h3 class="wlgf-card__title"><i class="fa-brands fa-mailchimp"></i> <?php esc_html_e( 'Mailchimp Configuration', 'lead-generation-form' ); ?></h3>
						</div>
						<div class="wlgf-card__body">
							<div class="wlgf-grid wlgf-grid--2">
								<div class="wlgf-form-group">
									<label for="mailchimp_api_key" class="wlgf-label"><?php esc_html_e( 'Mailchimp API Key', 'lead-generation-form' ); ?></label>
									<input type="password" class="wlgf-input" id="mailchimp_api_key" name="mailchimp_api_key" placeholder="<?php echo ! empty( $wlgf_mailchimp_api_key ) ? '••••••••••••••••••••••••••••••••' : esc_attr__( 'e.g. abcd1234efgh5678-us1', 'lead-generation-form' ); ?>" autocomplete="new-password">
									<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to preserve your currently saved key.', 'lead-generation-form' ); ?></p>
								</div>
								<div class="wlgf-form-group">
									<label for="mailchimp_list_id" class="wlgf-label"><?php esc_html_e( 'Audience / List ID', 'lead-generation-form' ); ?></label>
									<input type="text" class="wlgf-input" id="mailchimp_list_id" name="mailchimp_list_id" value="<?php echo esc_attr( $wlgf_mailchimp_list_id ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1a2b3c4d5e', 'lead-generation-form' ); ?>">
									<p class="wlgf-help-text"><?php esc_html_e( 'Found under Audience > Settings > Audience name and defaults in Mailchimp.', 'lead-generation-form' ); ?></p>
								</div>
							</div>
							<div class="wlgf-form-group wlgf-mt-2">
								<label class="wlgf-checkbox-label">
									<input type="checkbox" id="mailchimp_double_optin" name="mailchimp_double_optin" value="1" <?php checked( $wlgf_mailchimp_optin, '1' ); ?>>
									<span class="wlgf-checkbox-text"><strong><?php esc_html_e( 'Enable Double Opt-In', 'lead-generation-form' ); ?></strong> — <?php esc_html_e( 'Mailchimp will send a confirmation link email before adding the contact to your audience.', 'lead-generation-form' ); ?></span>
								</label>
							</div>
							<div class="wlgf-mt-3">
								<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline wlgf-test-connection-btn" data-provider="mailchimp">
									<i class="fa-solid fa-plug"></i> <?php esc_html_e( 'Test Mailchimp Connection', 'lead-generation-form' ); ?>
								</button>
								<span class="wlgf-test-connection-feedback wlgf-ml-2"></span>
							</div>
						</div>
					</div>

					<!-- Brevo Configuration Card (Pro) -->
					<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
						<div id="wlgf-card-provider-brevo" class="wlgf-card wlgf-card--bordered wlgf-mt-3 <?php echo ( 'brevo' === $wlgf_newsletter_provider ) ? '' : 'd-none'; ?>">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title"><i class="fa-solid fa-envelope"></i> <?php esc_html_e( 'Brevo (Sendinblue) Configuration', 'lead-generation-form' ); ?></h3>
							</div>
							<div class="wlgf-card__body">
								<div class="wlgf-grid wlgf-grid--2">
									<div class="wlgf-form-group">
										<label for="brevo_api_key" class="wlgf-label"><?php esc_html_e( 'Brevo v3 API Key', 'lead-generation-form' ); ?></label>
										<input type="password" class="wlgf-input" id="brevo_api_key" name="brevo_api_key" placeholder="<?php echo ! empty( $wlgf_brevo_api_key ) ? '••••••••••••••••••••••••••••••••' : esc_attr__( 'e.g. xkeysib-...', 'lead-generation-form' ); ?>" autocomplete="new-password">
										<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to preserve currently saved key.', 'lead-generation-form' ); ?></p>
									</div>
									<div class="wlgf-form-group">
										<label for="brevo_list_id" class="wlgf-label"><?php esc_html_e( 'Target List ID (Numeric)', 'lead-generation-form' ); ?></label>
										<input type="number" class="wlgf-input" id="brevo_list_id" name="brevo_list_id" value="<?php echo esc_attr( $wlgf_brevo_list_id ); ?>" placeholder="e.g. 2">
									</div>
								</div>
								<div class="wlgf-mt-3">
									<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline wlgf-test-connection-btn" data-provider="brevo">
										<i class="fa-solid fa-plug"></i> <?php esc_html_e( 'Test Brevo Connection', 'lead-generation-form' ); ?>
									</button>
									<span class="wlgf-test-connection-feedback wlgf-ml-2"></span>
								</div>
							</div>
						</div>

						<!-- MailerLite Configuration Card (Pro) -->
						<div id="wlgf-card-provider-mailerlite" class="wlgf-card wlgf-card--bordered wlgf-mt-3 <?php echo ( 'mailerlite' === $wlgf_newsletter_provider ) ? '' : 'd-none'; ?>">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title"><i class="fa-solid fa-paper-plane"></i> <?php esc_html_e( 'MailerLite Configuration', 'lead-generation-form' ); ?></h3>
							</div>
							<div class="wlgf-card__body">
								<div class="wlgf-grid wlgf-grid--2">
									<div class="wlgf-form-group">
										<label for="mailerlite_api_key" class="wlgf-label"><?php esc_html_e( 'MailerLite API Token', 'lead-generation-form' ); ?></label>
										<input type="password" class="wlgf-input" id="mailerlite_api_key" name="mailerlite_api_key" placeholder="<?php echo ! empty( $wlgf_mailerlite_api_key ) ? '••••••••••••••••••••••••••••••••' : esc_attr__( 'Enter API token...', 'lead-generation-form' ); ?>" autocomplete="new-password">
										<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to preserve currently saved key.', 'lead-generation-form' ); ?></p>
									</div>
									<div class="wlgf-form-group">
										<label for="mailerlite_group_id" class="wlgf-label"><?php esc_html_e( 'Target Group ID', 'lead-generation-form' ); ?></label>
										<input type="text" class="wlgf-input" id="mailerlite_group_id" name="mailerlite_group_id" value="<?php echo esc_attr( $wlgf_mailerlite_group_id ); ?>" placeholder="e.g. 123456789">
									</div>
								</div>
								<div class="wlgf-mt-3">
									<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline wlgf-test-connection-btn" data-provider="mailerlite">
										<i class="fa-solid fa-plug"></i> <?php esc_html_e( 'Test MailerLite Connection', 'lead-generation-form' ); ?>
									</button>
									<span class="wlgf-test-connection-feedback wlgf-ml-2"></span>
								</div>
							</div>
						</div>

						<!-- SendGrid Marketing Configuration Card (Pro) -->
						<div id="wlgf-card-provider-sendgrid" class="wlgf-card wlgf-card--bordered wlgf-mt-3 <?php echo ( 'sendgrid' === $wlgf_newsletter_provider ) ? '' : 'd-none'; ?>">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title"><i class="fa-solid fa-envelope-open-text"></i> <?php esc_html_e( 'SendGrid Marketing Configuration', 'lead-generation-form' ); ?></h3>
							</div>
							<div class="wlgf-card__body">
								<div class="wlgf-grid wlgf-grid--2">
									<div class="wlgf-form-group">
										<label for="sendgrid_api_key" class="wlgf-label"><?php esc_html_e( 'SendGrid API Key', 'lead-generation-form' ); ?></label>
										<input type="password" class="wlgf-input" id="sendgrid_api_key" name="sendgrid_api_key" placeholder="<?php echo ! empty( $wlgf_sendgrid_api_key ) ? '••••••••••••••••••••••••••••••••' : esc_attr__( 'e.g. SG.xxxx...', 'lead-generation-form' ); ?>" autocomplete="new-password">
										<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to preserve currently saved key.', 'lead-generation-form' ); ?></p>
									</div>
									<div class="wlgf-form-group">
										<label for="sendgrid_list_id" class="wlgf-label"><?php esc_html_e( 'Target List ID (UUID)', 'lead-generation-form' ); ?></label>
										<input type="text" class="wlgf-input" id="sendgrid_list_id" name="sendgrid_list_id" value="<?php echo esc_attr( $wlgf_sendgrid_list_id ); ?>" placeholder="e.g. 11111111-2222-3333-4444-555555555555">
									</div>
								</div>
								<div class="wlgf-mt-3">
									<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline wlgf-test-connection-btn" data-provider="sendgrid">
										<i class="fa-solid fa-plug"></i> <?php esc_html_e( 'Test SendGrid Connection', 'lead-generation-form' ); ?>
									</button>
									<span class="wlgf-test-connection-feedback wlgf-ml-2"></span>
								</div>
							</div>
						</div>

						<!-- ActiveCampaign Configuration Card (Pro) -->
						<div id="wlgf-card-provider-activecampaign" class="wlgf-card wlgf-card--bordered wlgf-mt-3 <?php echo ( 'activecampaign' === $wlgf_newsletter_provider ) ? '' : 'd-none'; ?>">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title"><i class="fa-solid fa-bullhorn"></i> <?php esc_html_e( 'ActiveCampaign Configuration', 'lead-generation-form' ); ?></h3>
							</div>
							<div class="wlgf-card__body">
								<div class="wlgf-grid wlgf-grid--3">
									<div class="wlgf-form-group">
										<label for="activecampaign_api_url" class="wlgf-label"><?php esc_html_e( 'API URL', 'lead-generation-form' ); ?></label>
										<input type="url" class="wlgf-input" id="activecampaign_api_url" name="activecampaign_api_url" value="<?php echo esc_url( $wlgf_activecampaign_api_url ); ?>" placeholder="https://youraccount.api-us1.com">
									</div>
									<div class="wlgf-form-group">
										<label for="activecampaign_api_key" class="wlgf-label"><?php esc_html_e( 'API Key / Token', 'lead-generation-form' ); ?></label>
										<input type="password" class="wlgf-input" id="activecampaign_api_key" name="activecampaign_api_key" placeholder="<?php echo ! empty( $wlgf_activecampaign_api_key ) ? '••••••••••••••••••••••••••••••••' : esc_attr__( 'API token...', 'lead-generation-form' ); ?>" autocomplete="new-password">
									</div>
									<div class="wlgf-form-group">
										<label for="activecampaign_list_id" class="wlgf-label"><?php esc_html_e( 'Target List ID (Numeric)', 'lead-generation-form' ); ?></label>
										<input type="number" class="wlgf-input" id="activecampaign_list_id" name="activecampaign_list_id" value="<?php echo esc_attr( $wlgf_activecampaign_list_id ); ?>" placeholder="e.g. 1">
									</div>
								</div>
								<div class="wlgf-mt-3">
									<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline wlgf-test-connection-btn" data-provider="activecampaign">
										<i class="fa-solid fa-plug"></i> <?php esc_html_e( 'Test ActiveCampaign Connection', 'lead-generation-form' ); ?>
									</button>
									<span class="wlgf-test-connection-feedback wlgf-ml-2"></span>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- TAB 5: Payments & Checkout (PRO-016) -->
		<div id="wlgf-tab-payments" class="wlgf-tab-content">
			<div class="wlgf-card">
				<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center wlgf-flex-wrap wlgf-gap-2">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-credit-card"></i> <?php esc_html_e( 'Payment Gateway Integrations', 'lead-generation-form' ); ?>
					</h2>
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<span class="wlgf-badge wlgf-badge--warning"><i class="fa-solid fa-lock"></i> <?php esc_html_e( 'PRO Feature', 'lead-generation-form' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wlgf-card__body">
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<div class="wlgf-alert wlgf-alert--info wlgf-mb-4">
							<i class="fa-solid fa-circle-info"></i>
							<div>
								<strong><?php esc_html_e( 'Unlock Online Payments in Pro', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-m-0"><?php esc_html_e( 'Collect credit cards via Stripe Elements and PayPal Smart Checkout directly on your forms. Upgrade to Pro to activate payment processing.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
					<?php endif; ?>

					<!-- General Payment Settings -->
					<div class="wlgf-grid wlgf-grid--2">
						<div class="wlgf-form-group">
							<label for="enable_payments" class="wlgf-label"><?php esc_html_e( 'Enable Global Payments', 'lead-generation-form' ); ?></label>
							<select class="wlgf-input" id="enable_payments" name="enable_payments" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
								<option value="0" <?php selected( $wlgf_enable_payments, '0' ); ?>><?php esc_html_e( 'Disabled (Standard Lead Forms)', 'lead-generation-form' ); ?></option>
								<option value="1" <?php selected( $wlgf_enable_payments, '1' ); ?>><?php esc_html_e( 'Enabled (Allow Payments on Forms)', 'lead-generation-form' ); ?></option>
							</select>
							<p class="wlgf-help-text"><?php esc_html_e( 'Master toggle to activate checkout features across published forms.', 'lead-generation-form' ); ?></p>
						</div>

						<div class="wlgf-form-group">
							<label for="payment_currency" class="wlgf-label"><?php esc_html_e( 'Default Currency', 'lead-generation-form' ); ?></label>
							<select class="wlgf-input" id="payment_currency" name="payment_currency" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
								<option value="USD" <?php selected( $wlgf_payment_currency, 'USD' ); ?>>USD ($ - US Dollar)</option>
								<option value="EUR" <?php selected( $wlgf_payment_currency, 'EUR' ); ?>>EUR (€ - Euro)</option>
								<option value="GBP" <?php selected( $wlgf_payment_currency, 'GBP' ); ?>>GBP (£ - British Pound)</option>
								<option value="CAD" <?php selected( $wlgf_payment_currency, 'CAD' ); ?>>CAD ($ - Canadian Dollar)</option>
								<option value="AUD" <?php selected( $wlgf_payment_currency, 'AUD' ); ?>>AUD ($ - Australian Dollar)</option>
								<option value="INR" <?php selected( $wlgf_payment_currency, 'INR' ); ?>>INR (₹ - Indian Rupee)</option>
								<option value="JPY" <?php selected( $wlgf_payment_currency, 'JPY' ); ?>>JPY (¥ - Japanese Yen)</option>
								<option value="BRL" <?php selected( $wlgf_payment_currency, 'BRL' ); ?>>BRL (R$ - Brazilian Real)</option>
								<option value="CHF" <?php selected( $wlgf_payment_currency, 'CHF' ); ?>>CHF (CHF - Swiss Franc)</option>
								<option value="SEK" <?php selected( $wlgf_payment_currency, 'SEK' ); ?>>SEK (kr - Swedish Krona)</option>
								<option value="SGD" <?php selected( $wlgf_payment_currency, 'SGD' ); ?>>SGD ($ - Singapore Dollar)</option>
								<option value="NZD" <?php selected( $wlgf_payment_currency, 'NZD' ); ?>>NZD ($ - New Zealand Dollar)</option>
							</select>
							<p class="wlgf-help-text"><?php esc_html_e( 'Currency code used when initializing payment transactions.', 'lead-generation-form' ); ?></p>
						</div>
					</div>

					<!-- Stripe Gateway Card -->
					<div class="wlgf-card wlgf-card--bordered wlgf-mt-4">
						<div class="wlgf-card__header wlgf-payment-card-header">
							<h3 class="wlgf-card__title"><i class="fa-brands fa-stripe"></i> <?php esc_html_e( 'Stripe Elements Integration', 'lead-generation-form' ); ?></h3>
							<div class="wlgf-toggle-wrap">
								<label class="wlgf-switch">
									<input type="checkbox" id="stripe_enabled" name="stripe_enabled" value="1" <?php checked( $wlgf_stripe_enabled, '1' ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<span class="wlgf-slider wlgf-round"></span>
								</label>
								<span class="wlgf-toggle-label"><?php esc_html_e( 'Enable Stripe', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<div class="wlgf-card__body">
							<div class="wlgf-form-group">
								<label for="stripe_test_mode" class="wlgf-label"><?php esc_html_e( 'Stripe Environment Mode', 'lead-generation-form' ); ?></label>
								<select class="wlgf-input" id="stripe_test_mode" name="stripe_test_mode" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<option value="1" <?php selected( $wlgf_stripe_test_mode, '1' ); ?>><?php esc_html_e( 'Test Mode (Sandbox)', 'lead-generation-form' ); ?></option>
									<option value="0" <?php selected( $wlgf_stripe_test_mode, '0' ); ?>><?php esc_html_e( 'Live Mode (Production)', 'lead-generation-form' ); ?></option>
								</select>
							</div>

							<div class="wlgf-grid wlgf-grid--2 wlgf-mt-3">
								<div class="wlgf-form-group">
									<label for="stripe_test_pub_key" class="wlgf-label"><?php esc_html_e( 'Test Publishable Key', 'lead-generation-form' ); ?></label>
									<input type="text" class="wlgf-input" id="stripe_test_pub_key" name="stripe_test_pub_key" value="<?php echo esc_attr( $wlgf_stripe_test_pub_key ); ?>" placeholder="pk_test_..." <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
								</div>
								<div class="wlgf-form-group">
									<label for="stripe_test_sec_key" class="wlgf-label"><?php esc_html_e( 'Test Secret Key', 'lead-generation-form' ); ?></label>
									<input type="password" class="wlgf-input" id="stripe_test_sec_key" name="stripe_test_sec_key" placeholder="<?php echo ! empty( $wlgf_stripe_test_sec_key ) ? '••••••••••••••••••••••••••••••••' : 'sk_test_...'; ?>" autocomplete="new-password" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to keep saved key.', 'lead-generation-form' ); ?></p>
								</div>
							</div>

							<div class="wlgf-grid wlgf-grid--2 wlgf-mt-3">
								<div class="wlgf-form-group">
									<label for="stripe_live_pub_key" class="wlgf-label"><?php esc_html_e( 'Live Publishable Key', 'lead-generation-form' ); ?></label>
									<input type="text" class="wlgf-input" id="stripe_live_pub_key" name="stripe_live_pub_key" value="<?php echo esc_attr( $wlgf_stripe_live_pub_key ); ?>" placeholder="pk_live_..." <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
								</div>
								<div class="wlgf-form-group">
									<label for="stripe_live_sec_key" class="wlgf-label"><?php esc_html_e( 'Live Secret Key', 'lead-generation-form' ); ?></label>
									<input type="password" class="wlgf-input" id="stripe_live_sec_key" name="stripe_live_sec_key" placeholder="<?php echo ! empty( $wlgf_stripe_live_sec_key ) ? '••••••••••••••••••••••••••••••••' : 'sk_live_...'; ?>" autocomplete="new-password" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to keep saved key.', 'lead-generation-form' ); ?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- PayPal Smart Checkout Card -->
					<div class="wlgf-card wlgf-card--bordered wlgf-mt-4">
						<div class="wlgf-card__header wlgf-payment-card-header">
							<h3 class="wlgf-card__title"><i class="fa-brands fa-paypal"></i> <?php esc_html_e( 'PayPal Smart Checkout Integration', 'lead-generation-form' ); ?></h3>
							<div class="wlgf-toggle-wrap">
								<label class="wlgf-switch">
									<input type="checkbox" id="paypal_enabled" name="paypal_enabled" value="1" <?php checked( $wlgf_paypal_enabled, '1' ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<span class="wlgf-slider wlgf-round"></span>
								</label>
								<span class="wlgf-toggle-label"><?php esc_html_e( 'Enable PayPal', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<div class="wlgf-card__body">
							<div class="wlgf-form-group">
								<label for="paypal_mode" class="wlgf-label"><?php esc_html_e( 'PayPal Environment Mode', 'lead-generation-form' ); ?></label>
								<select class="wlgf-input" id="paypal_mode" name="paypal_mode" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<option value="sandbox" <?php selected( $wlgf_paypal_mode, 'sandbox' ); ?>><?php esc_html_e( 'Sandbox (Testing)', 'lead-generation-form' ); ?></option>
									<option value="live" <?php selected( $wlgf_paypal_mode, 'live' ); ?>><?php esc_html_e( 'Live (Production)', 'lead-generation-form' ); ?></option>
								</select>
							</div>

							<div class="wlgf-grid wlgf-grid--2 wlgf-mt-3">
								<div class="wlgf-form-group">
									<label for="paypal_sandbox_client_id" class="wlgf-label"><?php esc_html_e( 'Sandbox Client ID', 'lead-generation-form' ); ?></label>
									<input type="text" class="wlgf-input" id="paypal_sandbox_client_id" name="paypal_sandbox_client_id" value="<?php echo esc_attr( $wlgf_paypal_sandbox_client ); ?>" placeholder="<?php esc_attr_e( 'Enter Sandbox Client ID...', 'lead-generation-form' ); ?>" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
								</div>
								<div class="wlgf-form-group">
									<label for="paypal_sandbox_secret" class="wlgf-label"><?php esc_html_e( 'Sandbox Secret', 'lead-generation-form' ); ?></label>
									<input type="password" class="wlgf-input" id="paypal_sandbox_secret" name="paypal_sandbox_secret" placeholder="<?php echo ! empty( $wlgf_paypal_sandbox_secret ) ? '••••••••••••••••••••••••••••••••' : esc_attr__( 'Enter Sandbox Secret...', 'lead-generation-form' ); ?>" autocomplete="new-password" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to keep saved secret.', 'lead-generation-form' ); ?></p>
								</div>
							</div>

							<div class="wlgf-grid wlgf-grid--2 wlgf-mt-3">
								<div class="wlgf-form-group">
									<label for="paypal_live_client_id" class="wlgf-label"><?php esc_html_e( 'Live Client ID', 'lead-generation-form' ); ?></label>
									<input type="text" class="wlgf-input" id="paypal_live_client_id" name="paypal_live_client_id" value="<?php echo esc_attr( $wlgf_paypal_live_client ); ?>" placeholder="<?php esc_attr_e( 'Enter Live Client ID...', 'lead-generation-form' ); ?>" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
								</div>
								<div class="wlgf-form-group">
									<label for="paypal_live_secret" class="wlgf-label"><?php esc_html_e( 'Live Secret', 'lead-generation-form' ); ?></label>
									<input type="password" class="wlgf-input" id="paypal_live_secret" name="paypal_live_secret" placeholder="<?php echo ! empty( $wlgf_paypal_live_secret ) ? '••••••••••••••••••••••••••••••••' : esc_attr__( 'Enter Live Secret...', 'lead-generation-form' ); ?>" autocomplete="new-password" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to keep saved secret.', 'lead-generation-form' ); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- TAB: Headless REST API (PRO-019) -->
		<div id="wlgf-tab-rest-api" class="wlgf-tab-content">
			<div class="wlgf-card">
				<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center wlgf-flex-wrap wlgf-gap-2">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-code"></i> <?php esc_html_e( 'Headless & External Forms REST API', 'lead-generation-form' ); ?>
					</h2>
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<span class="wlgf-badge wlgf-badge--warning"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO Feature', 'lead-generation-form' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wlgf-card__body">
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<div class="wlgf-alert wlgf-alert--info wlgf-mb-4">
							<i class="fa-solid fa-circle-info"></i>
							<div>
								<strong><?php esc_html_e( 'Headless REST API & Webhooks is a Pro Feature', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-m-0"><?php esc_html_e( 'Upgrade to Lead Generation Form Pro to unlock authenticated REST endpoints, CORS support, and real-time webhook payload dispatch to Zapier, Make, and external CRMs.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
					<?php endif; ?>
					<p class="wlgf-help-text wlgf-mb-3">
						<?php esc_html_e( 'Connect custom headless applications (React, Next.js, Vue, mobile apps, or external websites) to submit leads and fetch dynamic form structures using the WordPress REST API.', 'lead-generation-form' ); ?>
					</p>

					<div class="wlgf-grid wlgf-grid--2 wlgf-gap-3">
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">GET</span> <?php esc_html_e( '1. Fetch Form Schema', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text"><?php esc_html_e( 'Returns form fields, labels, options, theme tokens, and multi-step configurations.', 'lead-generation-form' ); ?></p>
								<div class="wlgf-code-pill wlgf-w-full wlgf-mt-2">
									<code class="wlgf-code-pill__text"><?php echo esc_url( rest_url( 'wlgf/v1/form/{form_id}' ) ); ?></code>
								</div>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--success">POST</span> <?php esc_html_e( '2. Submit Form Data', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text"><?php esc_html_e( 'Submit lead data via JSON or multipart/form-data with full spam protection and CRM storage.', 'lead-generation-form' ); ?></p>
								<div class="wlgf-code-pill wlgf-w-full wlgf-mt-2">
									<code class="wlgf-code-pill__text"><?php echo esc_url( rest_url( 'wlgf/v1/submit' ) ); ?></code>
								</div>
							</div>
						</div>
					</div>

					<div class="wlgf-form-group wlgf-mt-4">
						<label class="wlgf-label"><?php esc_html_e( 'Example JavaScript Fetch Call', 'lead-generation-form' ); ?></label>
						<textarea class="wlgf-textarea wlgf-code-box" rows="7" readonly>fetch('<?php echo esc_url( rest_url( 'wlgf/v1/submit' ) ); ?>', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    form_id: '1',
    Name: 'Jane Doe',
    Email: 'jane@example.com',
    Message: 'Hello from React app!'
  })
})
.then(res => res.json())
.then(data => console.log('Lead submitted:', data));</textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- TAB: Floating Multi-Channel Contact Widget (SiteLeads Challenger) -->
		<div id="wlgf-tab-floating-widget" class="wlgf-tab-content">
			<div class="wlgf-card">
				<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center wlgf-flex-wrap wlgf-gap-2">
					<div>
						<h2 class="wlgf-card__title">
							<i class="fa-solid fa-comment-dots"></i> <?php esc_html_e( 'Floating Multi-Channel Contact Launcher', 'lead-generation-form' ); ?>
						</h2>
						<p class="wlgf-card__text">
							<?php esc_html_e( 'Display a modern floating contact bubble on your website giving visitors 1-click access to WhatsApp, Phone Call, Telegram, SMS, Email, and your Lead Generation Form in a modal.', 'lead-generation-form' ); ?>
						</p>
					</div>
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<span class="wlgf-badge wlgf-badge--warning"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO Feature', 'lead-generation-form' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wlgf-card__body">
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<div class="wlgf-alert wlgf-alert--info wlgf-mb-4">
							<i class="fa-solid fa-circle-info"></i>
							<div>
								<strong><?php esc_html_e( 'Floating Contact Launcher is a Pro Feature', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-m-0"><?php esc_html_e( 'Give mobile visitors 1-tap WhatsApp chat and phone calling buttons. Replaces standalone SaaS widgets like SiteLeads and Boei. Upgrade to Pro to activate.', 'lead-generation-form' ); ?></p>
								<div class="wlgf-mt-2">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary">
										<i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Upgrade to Pro to Unlock', 'lead-generation-form' ); ?>
									</a>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<fieldset <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?> style="border:none;padding:0;margin:0;">
					<!-- Enable Widget Toggle -->
					<div class="wlgf-form-group wlgf-mb-4">
						<label class="wlgf-switch-label">
							<input type="checkbox" name="floating_widget_enabled" id="floating_widget_enabled" value="1" <?php checked( '1', $wlgf_floating_widget_enabled ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
							<span class="wlgf-switch-slider"></span>
							<strong class="wlgf-ml-2"><?php esc_html_e( 'Enable Floating Contact Launcher on Website', 'lead-generation-form' ); ?></strong>
						</label>
						<p class="wlgf-help-text wlgf-mt-1">
							<?php esc_html_e( 'When enabled, the floating contact launcher button will appear on all frontend pages.', 'lead-generation-form' ); ?>
						</p>
					</div>

					<div class="wlgf-grid wlgf-grid--2 wlgf-gap-4">
						<!-- Position -->
						<div class="wlgf-form-group">
							<label for="floating_position" class="wlgf-label"><?php esc_html_e( 'Screen Position', 'lead-generation-form' ); ?></label>
							<select name="floating_position" id="floating_position" class="wlgf-select wlgf-w-full">
								<option value="bottom-right" <?php selected( 'bottom-right', $wlgf_floating_position ); ?>><?php esc_html_e( 'Bottom Right (Recommended)', 'lead-generation-form' ); ?></option>
								<option value="bottom-left" <?php selected( 'bottom-left', $wlgf_floating_position ); ?>><?php esc_html_e( 'Bottom Left', 'lead-generation-form' ); ?></option>
								<option value="center-right" <?php selected( 'center-right', $wlgf_floating_position ); ?>><?php esc_html_e( 'Middle Right (Side Tab)', 'lead-generation-form' ); ?></option>
								<option value="center-left" <?php selected( 'center-left', $wlgf_floating_position ); ?>><?php esc_html_e( 'Middle Left (Side Tab)', 'lead-generation-form' ); ?></option>
								<option value="bottom-center" <?php selected( 'bottom-center', $wlgf_floating_position ); ?>><?php esc_html_e( 'Bottom Center', 'lead-generation-form' ); ?></option>
								<option value="top-right" <?php selected( 'top-right', $wlgf_floating_position ); ?>><?php esc_html_e( 'Top Right', 'lead-generation-form' ); ?></option>
								<option value="top-left" <?php selected( 'top-left', $wlgf_floating_position ); ?>><?php esc_html_e( 'Top Left', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<!-- Primary Color -->
						<div class="wlgf-form-group">
							<label for="floating_color" class="wlgf-label"><?php esc_html_e( 'Widget Accent Color', 'lead-generation-form' ); ?></label>
							<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
								<input type="color" name="floating_color" id="floating_color" value="<?php echo esc_attr( $wlgf_floating_color ); ?>" class="wlgf-color-picker">
								<span class="wlgf-text-muted"><?php echo esc_html( $wlgf_floating_color ); ?></span>
							</div>
						</div>
					</div>

					<!-- Greeting Text -->
					<div class="wlgf-form-group wlgf-mt-3">
						<label for="floating_greeting" class="wlgf-label"><?php esc_html_e( 'Speech Bubble Greeting Message', 'lead-generation-form' ); ?></label>
						<input type="text" name="floating_greeting" id="floating_greeting" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_greeting ); ?>" placeholder="👋 Need help? Chat with us">
						<p class="wlgf-help-text"><?php esc_html_e( 'Visitors see this proactive welcome message next to the button. Leave blank to disable.', 'lead-generation-form' ); ?></p>
					</div>

					<!-- Card Titles -->
					<div class="wlgf-grid wlgf-grid--2 wlgf-gap-4 wlgf-mt-3">
						<div class="wlgf-form-group">
							<label for="floating_title" class="wlgf-label"><?php esc_html_e( 'Menu Header Title', 'lead-generation-form' ); ?></label>
							<input type="text" name="floating_title" id="floating_title" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_title ); ?>" placeholder="How can we help you?">
						</div>
						<div class="wlgf-form-group">
							<label for="floating_subtitle" class="wlgf-label"><?php esc_html_e( 'Menu Header Subtitle / Availability', 'lead-generation-form' ); ?></label>
							<input type="text" name="floating_subtitle" id="floating_subtitle" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_subtitle ); ?>" placeholder="Usually replies within minutes">
						</div>
					</div>

					<!-- Channel Details Header -->
					<h3 class="wlgf-mt-4 wlgf-mb-2" style="font-size: 16px; font-weight: 700; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
						<i class="fa-solid fa-share-nodes text-primary"></i> <?php esc_html_e( 'Communication Channels', 'lead-generation-form' ); ?>
					</h3>

					<div class="wlgf-grid wlgf-grid--2 wlgf-gap-4">
						<!-- WhatsApp Number -->
						<div class="wlgf-form-group">
							<label for="floating_whatsapp" class="wlgf-label">
								<i class="fa-brands fa-whatsapp text-success"></i> <?php esc_html_e( 'WhatsApp Number (with Country Code)', 'lead-generation-form' ); ?>
							</label>
							<input type="text" name="floating_whatsapp" id="floating_whatsapp" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_whatsapp ); ?>" placeholder="e.g. +14155552671">
						</div>

						<!-- WhatsApp Pre-filled Text -->
						<div class="wlgf-form-group">
							<label for="floating_whatsapp_msg" class="wlgf-label"><?php esc_html_e( 'WhatsApp Pre-filled Message', 'lead-generation-form' ); ?></label>
							<input type="text" name="floating_whatsapp_msg" id="floating_whatsapp_msg" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_whatsapp_msg ); ?>" placeholder="Hello! I have a question.">
						</div>

						<!-- Direct Phone Call -->
						<div class="wlgf-form-group">
							<label for="floating_phone" class="wlgf-label">
								<i class="fa-solid fa-phone text-info"></i> <?php esc_html_e( 'Direct Phone Number (Click to Call)', 'lead-generation-form' ); ?>
							</label>
							<input type="text" name="floating_phone" id="floating_phone" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_phone ); ?>" placeholder="e.g. +14155552671">
						</div>

						<!-- Telegram -->
						<div class="wlgf-form-group">
							<label for="floating_telegram" class="wlgf-label">
								<i class="fa-brands fa-telegram" style="color: #229ed9;"></i> <?php esc_html_e( 'Telegram Username', 'lead-generation-form' ); ?>
							</label>
							<input type="text" name="floating_telegram" id="floating_telegram" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_telegram ); ?>" placeholder="e.g. mycompany">
						</div>

						<!-- SMS -->
						<div class="wlgf-form-group">
							<label for="floating_sms" class="wlgf-label">
								<i class="fa-solid fa-comment-sms" style="color: #8b5cf6;"></i> <?php esc_html_e( 'SMS Number', 'lead-generation-form' ); ?>
							</label>
							<input type="text" name="floating_sms" id="floating_sms" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_sms ); ?>" placeholder="e.g. +14155552671">
						</div>

						<!-- Email Address -->
						<div class="wlgf-form-group">
							<label for="floating_email" class="wlgf-label">
								<i class="fa-solid fa-envelope" style="color: #ea580c;"></i> <?php esc_html_e( 'Support Email Address', 'lead-generation-form' ); ?>
							</label>
							<input type="email" name="floating_email" id="floating_email" class="wlgf-input wlgf-w-full" value="<?php echo esc_attr( $wlgf_floating_email ); ?>" placeholder="support@example.com">
						</div>
					</div>

					<!-- Embedded Lead Form Modal Trigger -->
					<div class="wlgf-form-group wlgf-mt-4" style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
						<label for="floating_form_id" class="wlgf-label" style="font-size: 14px; font-weight: 700;">
							<i class="fa-solid fa-file-signature text-primary"></i> <?php esc_html_e( 'Connect a Lead Generation Form (Opens in Modal Lightbox)', 'lead-generation-form' ); ?>
						</label>
						<select name="floating_form_id" id="floating_form_id" class="wlgf-select wlgf-w-full wlgf-mt-1">
							<option value=""><?php esc_html_e( '-- None (Communication Channels Only) --', 'lead-generation-form' ); ?></option>
							<?php
							if ( is_array( $wlgf_forms_dropdown ) ) {
								foreach ( $wlgf_forms_dropdown as $wlgf_f_row ) {
									$wlgf_f_data = get_option( $wlgf_f_row->option_name );
									if ( ! is_array( $wlgf_f_data ) || empty( $wlgf_f_data['form_id'] ) ) {
										continue;
									}
									$wlgf_fid  = sanitize_text_field( $wlgf_f_data['form_id'] );
									$wlgf_name = ! empty( $wlgf_f_data['form_name'] ) ? sanitize_text_field( $wlgf_f_data['form_name'] ) : ( 'Form #' . $wlgf_fid );
									?>
									<option value="<?php echo esc_attr( $wlgf_fid ); ?>" <?php selected( $wlgf_fid, $wlgf_floating_form_id ); ?>>
										<?php echo esc_html( $wlgf_name . ' (ID: #' . $wlgf_fid . ')' ); ?>
									</option>
									<?php
								}
							}
							?>
						</select>
						<p class="wlgf-help-text wlgf-mt-1">
							<?php esc_html_e( 'When selected, an "Send Inquiry Form" option is added to the floating menu. Clicking it opens the full lead form in an instant floating lightbox modal!', 'lead-generation-form' ); ?>
						</p>
					</div>
					</fieldset>
				</div>
			</div>
		</div>

		<!-- TAB 6: Help & Documentation -->
		<div id="wlgf-tab-help" class="wlgf-tab-content">
			<!-- Documentation Header & Quick Action Buttons -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header wlgf-flex wlgf-flex--between wlgf-items-center wlgf-flex-wrap wlgf-gap-2">
					<div>
						<h2 class="wlgf-card__title">
							<i class="fa-solid fa-book-bookmark"></i> <?php esc_html_e( 'Comprehensive Documentation & Knowledge Base', 'lead-generation-form' ); ?>
						</h2>
						<p class="wlgf-card__text wlgf-mt-1">
							<?php esc_html_e( 'Complete architectural reference guide: understand what each feature does, how it operates under the hood, step-by-step setup instructions, and default configuration values.', 'lead-generation-form' ); ?>
						</p>
					</div>
					<div class="wlgf-btn-group">
						<a href="<?php echo esc_url( $wlgf_docs_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary">
							<i class="fa-solid fa-book"></i> <?php esc_html_e( 'Online Docs', 'lead-generation-form' ); ?>
						</a>
						<a href="<?php echo esc_url( $wlgf_support_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline">
							<i class="fa-solid fa-comments"></i> <?php esc_html_e( 'Get Support', 'lead-generation-form' ); ?>
						</a>
						<a href="https://www.youtube.com/@WPFrankOfficial/videos" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--sm wlgf-btn--danger">
							<i class="fa-brands fa-youtube"></i> <?php esc_html_e( 'Video Tutorials', 'lead-generation-form' ); ?>
						</a>
						<a href="<?php echo esc_url( $wlgf_changelog_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline">
							<i class="fa-solid fa-list-ul"></i> <?php esc_html_e( 'Changelog', 'lead-generation-form' ); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- SECTION 1: Feature & Module Breakdown -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-layer-group"></i> <?php esc_html_e( '1. Feature & Module Breakdown', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<div class="wlgf-grid wlgf-grid--3 wlgf-gap-3">
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-wand-magic-sparkles text-primary"></i> <?php esc_html_e( 'Form Builder', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Visual drag-and-drop builder canvas. Supports 18+ custom fields (Text, Email, Phone, Dropzone, Calculations, Star Rating, NPS Matrix, Multi-Step Breaks, Terms, Hidden). Lets you configure labels, placeholders, column widths, validation, and auto-reply emails.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-list-check text-primary"></i> <?php esc_html_e( 'Manage Forms', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Central form directory. Displays live impression views, total lead submissions, real-time conversion rates (%), 1-click shortcode copying pill [WLFG id="X"], 1-click form cloning, and form deletion.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-users-viewfinder text-primary"></i> <?php esc_html_e( 'Captured Leads (Mini-CRM)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Built-in lead management hub. Filter entries per form, update lead statuses (New, In Review, Contacted, Qualified, Won, Lost), append internal sticky notes with timestamps, stream complete CSV exports, and generate 1-click PDF receipts.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-calculator text-primary"></i> <?php esc_html_e( 'Formula Price Estimator', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Evaluates dynamic arithmetic expressions like {base_price} + ({quantity} * 25). Automatically recalculates live on input changes and connects directly to Stripe & PayPal checkouts.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-credit-card text-primary"></i> <?php esc_html_e( 'Payments & Checkout', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Stripe Elements (Credit/Debit Card, Apple Pay, Google Pay) and PayPal Smart Buttons. Collect fixed or formula-calculated payments directly upon form submission with server-side validation.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-code text-primary"></i> <?php esc_html_e( 'Headless REST API', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Dedicated /wp-json/wlgf/v1/ endpoints. Enables external Jamstack sites, React, Next.js, mobile apps, and third-party automations (Zapier, Make) to fetch form schemas and submit leads directly with CORS support.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-table-columns text-primary"></i> <?php esc_html_e( 'Multi-Column Responsive Grid', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( '1-Click Quick Column Switcher bar (100%, 50%, 33%, 25%) directly on each canvas field card. Fields automatically render side-by-side in real-time on the builder canvas and gracefully stack into a single column on mobile devices (max-width: 640px).', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-shapes text-primary"></i> <?php esc_html_e( 'Instant Template Library & Viewport Switcher', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( '1-Click pre-built starter templates categorized by industry (Lead Gen, Contact, Survey, Booking, Feedback). Includes a Live Responsive Device Viewport Switcher (Desktop, Tablet, Mobile) allowing you to test form responsiveness without leaving the builder.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-comment-dots text-primary"></i> <?php esc_html_e( 'Floating WhatsApp & Contact Launcher', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'SiteLeads challenger widget: Sleek floating corner bubble with radar pulse animation. Supports direct WhatsApp chat, one-touch phone calling, Telegram, SMS, or triggering your lead capture form in an instant popup modal.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-ban text-danger"></i> <?php esc_html_e( 'Email & Domain Blacklist Protection', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Enterprise Anti-Spam & Phishing barrier. Restricts spam bots, disposable temporary mailboxes (e.g. Mailinator, TempMail), competitors, and phishers from submitting forms. Supports exact email addresses, entire root domains, subdomains, and wildcard TLDs (*.xyz, *.top, .ru). Features 1-click Quick Add domain chips and a stealth "Silent Drop / Honeypot" mode.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-shield-halved text-success"></i> <?php esc_html_e( 'Automated Abuse Shields & Cloudflare Turnstile (v3.4.8)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Comprehensive automated defense suite against email flooding, scripted bot fuzzers, and scanner probes. Features Cloudflare Turnstile integration (frictionless, privacy-friendly CAPTCHA), Server-Side IP Rate Limiting (throttles rapid script loops to max 3 submissions per 2 mins per IP), Smart CSS Decoy Honeypot Trap (silently drops automated bots autofilling off-screen decoy fields), and Vulnerability Scanner Shield (auto-detects and neutralizes SQLi and pentest fuzzing payloads like sleep, PG_SLEEP, waitfor delay, UNION SELECT without sending emails).', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-copy text-primary"></i> <?php esc_html_e( 'Multi-Admin CC Lead Notifications', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Multi-recipient email routing engine. Automatically carbon copies (Cc:) branded HTML lead notification emails to multiple team members, department inboxes (e.g. sales, support, management), or external stakeholders. Includes automatic syntax validation, duplicate cleanup, and primary admin deduplication across wp_mail, SMTP, and REST API submissions.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-reply-all text-primary"></i> <?php esc_html_e( 'Submitter Auto-Responder', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Dispatches automated, high-converting HTML confirmation emails directly to users when they submit an inquiry. Supports personalized dynamic merge tags ({Name}, {Email}, {Site_Name}, {Form_Name}, {Submission_Date}), automatic logo embedding, header accents, and responsive layout styling.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-palette text-primary"></i> <?php esc_html_e( 'Email Notification Template & Branding', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Central email branding suite. Configure your company logo (with max-width control), custom header background & text colors, header title with smart tags ({form_name}, {site_title}), body greeting, card styling, and custom footer disclaimer with live in-browser preview and test dispatches.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-check-double text-success"></i> <?php esc_html_e( 'Confirmation Message & Thank You Designer', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( '2-Column interactive submission designer. Features 4 visual layout presets (Modern Card, Sleek Banner, Minimal Pill, Glassmorphism), 8 animated FontAwesome badge icons, dual color pickers, and configurable 5-50 second auto-fadeout durations with live instant preview.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-network-wired text-primary"></i> <?php esc_html_e( 'Real-Time Outgoing Webhooks (PRO-007)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Asynchronously dispatches structured JSON payloads upon every form submission to external automation platforms (Zapier, Make.com, n8n, Pabbly, or custom REST webhooks) with non-blocking execution and server timeout protection.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-calendar-xmark text-warning"></i> <?php esc_html_e( 'Form Scheduling & Submission Quotas', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Automate form availability by configuring exact start and end dates/times (ideal for limited-time offers, events, and job postings) or capping maximum lead capacity (e.g. first 100 entries). Automatically closes the form and displays a customized friendly notice.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-clone text-info"></i> <?php esc_html_e( 'Form Cloning & Import/Export Hub (v3.4.4)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( '1-Click form duplication with instant shortcode copying, row highlight animation, and real-time KPI counter sync. Includes complete JSON form schema exporting, CSV lead downloading, and single-click form importing across different WordPress installations.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-shoe-prints text-primary"></i> <?php esc_html_e( 'Multi-Step Form Wizard & Progress Bar (PRO-002)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Split long, overwhelming forms into clean, interactive multi-step funnels using Step Break elements. Includes real-time percentage progress indicators, step indicator badges, customized "Next / Back / Submit" buttons, and per-step client-side validation.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-signature text-purple" style="color: #8b5cf6;"></i> <?php esc_html_e( 'HTML5 Digital E-Signature Pad (PRO-018 / v3.4.5)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Collect legally binding digital signatures directly on mobile touchscreens and desktop mice. Features High-DPI canvas scaling, automatic clearing upon form submission, inline validation, and automated PDF certification stamping.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<i class="fa-solid fa-cloud-arrow-up text-primary"></i> <?php esc_html_e( 'Drag & Drop Multi-File Upload Dropzone (PRO-003)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Modern file uploader supporting drag-and-drop file queues, custom allowed MIME/extension whitelists, max file size restrictions (MB), and multi-file count caps. Includes secure directory execution denial (.htaccess) and admin lead attachment lightbox previews.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION 2: How It Works Under the Hood (Architecture) -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-gears"></i> <?php esc_html_e( '2. How It Works Under the Hood', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<div class="wlgf-steps">
						<div class="wlgf-step">
							<span class="wlgf-step__num">1</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'End-to-End Submission Pipeline:', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'When a user submits a form, it executes a strict multi-stage security pipeline:', 'lead-generation-form' ); ?><br>
									<strong>a) Anti-Spam Velocity & Honeypot:</strong> <?php esc_html_e( 'Checks hidden honeypot field (must be blank) and verifies form load timestamp is >= 2 seconds (rejects rapid automated spam bots).', 'lead-generation-form' ); ?><br>
									<strong>b) Nonce & CSRF Check:</strong> <?php esc_html_e( 'Validates wp_create_nonce("wlgf-form-post") to guarantee request authenticity.', 'lead-generation-form' ); ?><br>
									<strong>c) Scheduling & Quotas:</strong> <?php esc_html_e( 'Verifies current timestamp is within schedule_start / schedule_end and lead count has not exceeded max_submissions limit.', 'lead-generation-form' ); ?><br>
									<strong>d) Blacklist Filter:</strong> <?php esc_html_e( 'Checks submitter email against form-specific or global blacklisted emails/domains.', 'lead-generation-form' ); ?><br>
									<strong>e) Payment Verification:</strong> <?php esc_html_e( 'If enabled, validates Stripe Payment Intent (pi_...) or PayPal Order ID server-side before allowing lead creation.', 'lead-generation-form' ); ?><br>
									<strong>f) File Upload Security:</strong> <?php esc_html_e( 'Validates extensions against strict whitelist, sanitizes filenames, and places files inside protected directories with .htaccess execution denial.', 'lead-generation-form' ); ?><br>
									<strong>g) Database Persistence:</strong> <?php esc_html_e( 'Writes sanitized lead data to wlgf_saved_form_data_{form_id}, increments view/submission KPIs, and dispatches admin/auto-reply emails and webhooks.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">2</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Safe Math Calculation Engine (Zero eval):', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Formula evaluation uses an AST-based recursive-descent math tokenizer in PHP without eval() or create_function(). It replaces bracketed tokens like {quantity} with sanitized numbers, strips non-math characters, and safely evaluates arithmetic (+, -, *, /, parentheses). Tampering with client numbers is impossible because the server independently computes the final price.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">3</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Visual Conditional Logic Engine:', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Conditional rules attach real-time event listeners to form input fields. When a trigger field changes, the engine evaluates conditions using AND/OR logic and toggles the visibility and "required" state of dependent fields dynamically on the client canvas.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">4</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Pure-PHP Vector PDF Generator Engine:', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Generates vector-sharp PDF summaries and invoice receipts using raw PDF stream primitives (Type1 fonts, re/f/S coordinate paths). Runs 100% in pure PHP with zero heavy binary dependencies (no DOMPDF, no TCPDF, no WKHTMLTOPDF), executing in under 25ms with minimal memory consumption.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>
						<div class="wlgf-step">
							<span class="wlgf-step__num">5</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Email & Domain Blacklist Matching Engine (Silent Drop vs. Reject):', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Inspects submitted email fields and evaluates them against blacklisted patterns: exact email matches (case-insensitive), root domain matches (e.g. mailinator.com), subdomain hierarchies (e.g. temp.mailinator.com), and wildcard TLDs (e.g. *.xyz, *.top, .ru). If "Show Error Notice" is active, submissions are rejected with a customized alert. If "Silent Drop / Honeypot" is active, the engine returns a fake 200 HTTP success message to trick automated bots, while silently discarding data without saving leads or dispatching notification emails.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">6</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Multi-Recipient CC Lead Notification Dispatcher:', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Parses comma and newline separated emails configured in "CC Additional Admin Email(s)". Cleans each email address, validates syntax via is_email(), deduplicates redundant entries, and excludes the primary site administrator to prevent duplicate inboxes. Appends standard RFC-compliant "Cc: <email>" headers to outgoing wp_mail(), custom authenticated PHPMailer SMTP sessions, and headless REST API notifications.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">7</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Submitter Auto-Responder & Dynamic Merge Tag Resolver:', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Inspects incoming lead data to capture the submitter\'s name and email address. Merges personalized tags ({Name}, {Email}, {Site_Name}, {Form_Name}, {Submission_Date}) across subject and body templates. Embeds your global Brand Logo using RFC Content-ID (cid:wlgf_email_logo) inline MIME attachments for pristine webmail/Gmail rendering, wraps output in responsive HTML cards, and dispatches via authenticated PHPMailer or wp_mail without blocking the visitor experience.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">8</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Real-Time Outgoing Webhook Dispatch Engine (PRO-007):', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Executes non-blocking, asynchronous HTTP POST requests via wp_remote_post() to your configured webhook URL (Zapier, Make, n8n, Slack, or CRM). Packages sanitized lead fields, unique submission IDs, form metadata, timestamps, and payment transaction details in standard application/json headers with a strict 15-second timeout safeguard to eliminate frontend latency.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">9</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Form Scheduling & Quota Enforcement Engine:', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Before rendering frontend forms or accepting AJAX dispatches, evaluates current server timestamp against schedule_start and schedule_end windows. Simultaneously queries total saved submissions in wlgf_saved_form_data_{id} against max_submissions caps. If expired or limit reached, gracefully swaps form inputs for a customizable closed banner alert and rejects incoming submissions.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">10</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Multi-Step Client-Side State & Per-Step Validation Engine (PRO-002):', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Maintains active step index in client memory. When a visitor clicks "Next", validates only the visible required inputs belonging to that active step partition. If all inputs are valid, triggers smooth hardware-accelerated CSS sliding transitions, recalculates completion percentage, and scrolls the next card into view while maintaining partial draft auto-capture.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">11</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'High-DPI E-Signature Pad & Pure-PHP Vector PDF Engine (PRO-018 / v3.4.5):', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Initializes HTML5 Canvas calibrated with window.devicePixelRatio (2x-3x retina scaling) to prevent pixelation on iPads, iPhones, and Android touchscreens. Records high-fidelity vector Bezier curves, serializes coordinates into compressed PNG Base64 data, and stamps verified digital signatures directly onto raw PDF receipt streams without external binary libraries.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>

						<div class="wlgf-step">
							<span class="wlgf-step__num">12</span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Automated Multi-Layer Anti-Abuse & Exploit Shield Pipeline (v3.4.8):', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc">
									<?php esc_html_e( 'Executes early defensive inspection on frontend and REST API submissions before leads or emails are processed: a) Smart CSS Decoy Honeypot: Injects an invisible, off-screen decoy trap; bots autofilling it are silently dropped with a mock success response. b) Vulnerability Scanner Filter: Analyzes all POST fields for automated blind SQLi or time-based fuzzing patterns (sleep, PG_SLEEP, waitfor delay, UNION SELECT) and immediately drops the payload. c) Server-Side IP Rate Limiter: Tracks submission velocity via WordPress transients (max 3 submissions per 120s per IP) to prevent scripted email flooding. d) Cloudflare Turnstile: Asynchronously validates response tokens against Cloudflare API.', 'lead-generation-form' ); ?>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION 3: Step-by-Step Setup Guides -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-list-ol"></i> <?php esc_html_e( '3. Step-by-Step Setup Guides', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<div class="wlgf-grid wlgf-grid--2 wlgf-gap-3">
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 1</span> <?php esc_html_e( 'Setting Up Email Notifications & SMTP', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Navigate to Settings > Email & SMTP tab.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Set "Send Lead Notifications to Admin" to ON.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Under "Email Delivery Engine", select "Custom Authenticated SMTP".', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Enter your SMTP Host (e.g. smtp.gmail.com), Username, and App Password.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Select Encryption: TLS (Port 587) or SSL (Port 465).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click "Save Settings", then use "Send Test Email" to verify delivery in real time.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 2</span> <?php esc_html_e( 'Configuring Cloudflare Turnstile & Anti-Spam Shields', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Log in to Cloudflare Dashboard and navigate to Turnstile > Add Site (or use Google reCAPTCHA v3 console).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Enter your website domain and choose Widget Mode ("Managed" recommended) to generate your Site Key and Secret Key.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'In WP Admin, navigate to Lead Gen Form > Global Settings > Anti-Spam & Security tab.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Under CAPTCHA Provider, choose "Cloudflare Turnstile" and paste your Site Key and Secret Key.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Ensure IP Rate Limiting, Smart Decoy Honeypot, and Scanner Exploit Shield remain ON (Enabled by default).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click "Save Settings". Turnstile provides a frictionless, privacy-first CAPTCHA with zero user puzzles while automated shields block spam floods.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 3</span> <?php esc_html_e( 'Setting Up Stripe & PayPal Checkouts', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Go to Settings > Payments & Checkout tab and toggle "Enable Online Payments" ON.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Enter your Stripe Publishable & Secret Keys (Test mode for development, Live mode for production).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Enter your PayPal Client ID & Secret Key (Sandbox or Live).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Open Form Builder on your form, scroll to "Online Payments & Checkout", toggle "Enable Payment", and configure Fixed Amount or select a Calculation Field.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 4</span> <?php esc_html_e( 'Connecting Email Marketing (ESP Sync)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Go to Settings > Newsletter & Integrations tab.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Choose your provider: Mailchimp, Brevo, MailerLite, SendGrid, or ActiveCampaign.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Enter your API Key and List / Audience ID.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click "Verify API Connection" to ensure green status confirmation.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'In Form Builder, enable Newsletter Sync and select Explicit (opt-in checkbox) or Automatic sync mode.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 5</span> <?php esc_html_e( 'Setting Up Email & Domain Blacklist Protection', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Navigate to Settings > Anti-Spam Security tab.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Scroll down to the "Email & Domain Blacklist Protection" card.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Set "Blacklist Enforcement Status" to ON (badge updates to "Active & Blocking").', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Enter forbidden email addresses in "Blocked Email Addresses" (one per line or comma-separated).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Enter forbidden domains in "Blocked Domains & TLDs" or click 1-click Quick Add chips (+ mailinator.com, + tempmail.com, + *.xyz, etc.).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Select Action: "Show Error Notice" (shows alert) or "Silent Drop / Honeypot" (returns fake success to bots, discards lead).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click "Save Settings" at the top-right to activate real-time protection.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 6</span> <?php esc_html_e( 'CC Lead Notifications to Additional Admins', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Navigate to Settings > Email & SMTP tab.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Ensure "Send Lead Notifications to Admin" is set to ON.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Under "CC Additional Admin Email(s)", enter email addresses for team members (comma or newline separated, e.g. sales@domain.com, manager@domain.com).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click "Save Settings". All newly submitted leads will automatically include these recipients in the Cc: header.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 7</span> <?php esc_html_e( 'Configuring Submitter Auto-Responder & Dynamic Merge Tags', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Open your form in the Form Builder, click the Settings icon tab.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Toggle "Send Instant Confirmation Email to Submitter (Auto-Responder)" to ON.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Enter your Email Subject Line (default pre-filled for high conversions).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'In Email Message Body, personalize your greeting using dynamic merge tags: {Name}, {Email}, {Site_Name}, {Form_Name}, and {Submission_Date}.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Ensure you have configured your Brand Logo under Settings > Email & SMTP for automated branded header insertion.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click "Save Form". Every valid submission automatically dispatches this personalized confirmation email.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 8</span> <?php esc_html_e( 'Designing Confirmation Messages & Thank You Cards', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Navigate to Settings > General & Confirmation tab.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Select a visual preset: Modern Card (default), Sleek Banner, Minimal Pill, or Glassmorphism.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Pick a badge icon (Checkmark, Trophy, Star, Shield, Thumbs Up) and toggle the spring pop animation.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Customize Card Background, Border, Icon Accent, Heading, and Body Text colors using live dual pickers.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Configure Message Fadeout Duration (0 to keep permanently, or 5-50 seconds to automatically dismiss).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'To redirect to an external thank-you page instead, set Confirmation Action to "Redirect to URL" in Form Builder.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 9</span> <?php esc_html_e( 'Connecting Webhooks to Zapier, Make.com, or n8n', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'In your automation platform (Zapier / Make / n8n / Pabbly), create a new scenario with a "Webhooks" trigger.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Copy the generated Catch Hook URL (e.g. https://hooks.zapier.com/hooks/catch/...).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Edit your form in Form Builder, open the Settings tab, and find "Real-Time Outgoing Webhook (Zapier / Make / n8n)".', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Paste your webhook URL and save the form.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Submit a test entry on your live page. Your automation platform will instantly detect the test payload with all form fields.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 10</span> <?php esc_html_e( 'Building a Multi-Step Wizard Form with Progress Bar', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'In the Form Builder canvas, drag fields for your first step (e.g. Name, Email, Phone).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Drag a "Step Break" element from the sidebar palette between your field sets.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click the Step Break to edit its Step Title (e.g. "Step 2: Service Details") and optional description.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Add additional fields for subsequent steps, repeating Step Breaks as needed.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'On the frontend, visitors experience an interactive card wizard with real-time percentage progress bar and validation per step.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 11</span> <?php esc_html_e( 'Setting Up Form Scheduling & Submission Quotas', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'Edit your form in Form Builder and scroll to the "Scheduling & Limits" section in the form settings drawer.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'To restrict by date: set "Schedule Start Date" and "Schedule End Date" with exact time windows.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'To restrict by quota: enter "Maximum Submissions" (e.g. 50 or 100 for limited-seat registrations).', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Customize the "Form Closed Notice" message to inform visitors when registration has concluded.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Save the form. When expired or full, the form automatically disables itself and displays your notice.', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>

						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__header">
								<h3 class="wlgf-card__title" style="font-size: 15px;">
									<span class="wlgf-badge wlgf-badge--primary">Guide 12</span> <?php esc_html_e( '1-Click Form Cloning, Backups & Migration (Import & Export)', 'lead-generation-form' ); ?>
								</h3>
							</div>
							<div class="wlgf-card__body">
								<ol class="wlgf-help-text" style="padding-left: 20px; line-height: 1.8;">
									<li><?php esc_html_e( 'To duplicate a form on the same site: navigate to "Manage Forms" and click "Clone" next to any form. A confirmation modal appears with a 1-click shortcode copy button.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'To backup your data: navigate to "Lead Generation Form > Import & Export".', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click "Export All Forms (JSON)" to download complete form structures, styling, and conditional rules.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'Click "Export All Leads (CSV)" to download all captured inquiries with notes and submission timestamps.', 'lead-generation-form' ); ?></li>
									<li><?php esc_html_e( 'To migrate to a new website: open Import & Export on the destination site, choose your backup JSON file, and click "Import Forms".', 'lead-generation-form' ); ?></li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION 4: Master Default Settings Reference Table -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-table-list"></i> <?php esc_html_e( '4. Master Default Settings Reference', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<div class="wlgf-table-wrap">
						<table class="wlgf-table wlgf-w-full">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Setting Name', 'lead-generation-form' ); ?></th>
									<th><?php esc_html_e( 'Default Value', 'lead-generation-form' ); ?></th>
									<th><?php esc_html_e( 'Available Options', 'lead-generation-form' ); ?></th>
									<th><?php esc_html_e( 'Operational Purpose & Behavior', 'lead-generation-form' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><strong>user_message</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted">"Thank you! Your query has been successfully submitted."</span></td>
									<td>Custom text or HTML (strong, em, br, a)</td>
									<td>Displayed on-screen immediately after the form is successfully submitted.</td>
								</tr>
								<tr>
									<td><strong>notify_admin</strong></td>
									<td><span class="wlgf-badge wlgf-badge--warning">2 (OFF)</span></td>
									<td>1 (ON), 2 (OFF)</td>
									<td>When ON (1), dispatches instant HTML email containing all submitted lead fields to the site admin.</td>
								</tr>
								<tr>
									<td><strong>email_engine</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">1 (WordPress Mail)</span></td>
									<td>1 (wp_mail), 2 (Custom SMTP)</td>
									<td>Engine 1 uses server sendmail; Engine 2 authenticates directly via your dedicated SMTP host.</td>
								</tr>
								<tr>
									<td><strong>smtp_port & encryption</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted">587 / TLS (1)</span></td>
									<td>TLS (Port 587), SSL (Port 465), None (25)</td>
									<td>Accurate port and encryption protocol mapping passed to PHPMailer.</td>
								</tr>
								<tr>
									<td><strong>captcha_provider</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted">none</span></td>
									<td>none, turnstile, recaptcha</td>
									<td>Active CAPTCHA challenge engine. Choose Cloudflare Turnstile or Google reCAPTCHA v3.</td>
								</tr>
								<tr>
									<td><strong>submission_rate_limit</strong></td>
									<td><span class="wlgf-badge wlgf-badge--success">1 (ON - Default)</span></td>
									<td>1 (Enabled), 2 (Disabled)</td>
									<td>IP rate limiter (max 3 submissions per 2 mins per IP). Prevents email flood loops.</td>
								</tr>
								<tr>
									<td><strong>honeypot_status</strong></td>
									<td><span class="wlgf-badge wlgf-badge--success">1 (ON - Default)</span></td>
									<td>1 (Enabled), 2 (Disabled)</td>
									<td>Smart CSS decoy trap. Injects invisible field that traps automated bots.</td>
								</tr>
								<tr>
									<td><strong>scanner_shield_status</strong></td>
									<td><span class="wlgf-badge wlgf-badge--success">1 (ON - Default)</span></td>
									<td>1 (Enabled), 2 (Disabled)</td>
									<td>Vulnerability scanner & SQLi heuristic filter (sleep, PG_SLEEP, waitfor, UNION).</td>
								</tr>
								<tr>
									<td><strong>recaptcha</strong></td>
									<td><span class="wlgf-badge wlgf-badge--warning">2 (Disabled)</span></td>
									<td>1 (reCAPTCHA v3), 2 (None), 3 (Turnstile)</td>
									<td>Legacy sync key ensuring backward compatibility with older v3.x installations.</td>
								</tr>
								<tr>
									<td><strong>newsletter_provider</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">local</span></td>
									<td>local, mailchimp, brevo, mailerlite, sendgrid, activecampaign</td>
									<td>Where subscriber leads are dispatched. "local" preserves entries inside the built-in mini-CRM.</td>
								</tr>
								<tr>
									<td><strong>enable_payments</strong></td>
									<td><span class="wlgf-badge wlgf-badge--warning">0 (Disabled)</span></td>
									<td>1 (Enabled), 0 (Disabled)</td>
									<td>Global master switch enabling Stripe Elements and PayPal Smart Buttons checkouts.</td>
								</tr>
								<tr>
									<td><strong>stripe_test_mode</strong></td>
									<td><span class="wlgf-badge wlgf-badge--success">1 (Test Sandbox ON)</span></td>
									<td>1 (Test Mode), 0 (Live Production)</td>
									<td>Ensures safe payment testing with Stripe test card numbers before switching live.</td>
								</tr>
								<tr>
									<td><strong>paypal_mode</strong></td>
									<td><span class="wlgf-badge wlgf-badge--success">sandbox</span></td>
									<td>sandbox, live</td>
									<td>Directs PayPal Smart Buttons SDK to sandbox environment for test payments.</td>
								</tr>
								<tr>
									<td><strong>theme_primary</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">#2563eb</span></td>
									<td>Any valid HEX color string</td>
									<td>Primary brand accent color applied to submit buttons, step progress bars, and focus outlines.</td>
								</tr>
								<tr>
									<td><strong>theme_radius</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted">6px</span></td>
									<td>0px, 4px, 6px, 8px, 12px, 16px</td>
									<td>Corner rounding applied to form inputs, containers, dropdowns, and action buttons.</td>
								</tr>
								<tr>
									<td><strong>File Upload Limits</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">50 MB (Pro)</span></td>
									<td>Pro: 50 MB / Free: 5 MB</td>
									<td>Maximum file size allowed per attachment. Whitelist: jpg, png, webp, pdf, docx, xlsx, zip.</td>
								</tr>
								<tr>
									<td><strong>Anti-Spam Time Gate</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">2.0 Seconds</span></td>
									<td>Hard-coded security constant</td>
									<td>Rejects submissions if time elapsed between form load and submit is less than 2 seconds.</td>
								</tr>
								<tr>
									<td><strong>cc_admin_emails</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( '"" (Empty)', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Comma or newline separated valid email addresses', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Dispatches carbon copy (Cc:) lead notification emails to additional team members and departments.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>blacklist_status</strong></td>
									<td><span class="wlgf-badge wlgf-badge--warning">2 (OFF / Paused)</span></td>
									<td>1 (ON - Active), 2 (OFF - Paused)</td>
									<td><?php esc_html_e( 'Master enforcement switch for email and domain blacklist filtering across all forms.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>blacklist_emails</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( '"" (Empty)', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Textarea list of exact emails (one per line or comma-separated)', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Exact email addresses prohibited from submitting any form on the site.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>blacklist_domains</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( '"" (Empty)', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Textarea list of domains and wildcard TLDs (*.xyz, .ru)', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Prohibits temporary/disposable mailboxes, competitors, or entire high-risk TLDs.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>blacklist_action</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">reject_error</span></td>
									<td>reject_error, silent_drop</td>
									<td><?php esc_html_e( '"reject_error" displays rejection alert notice; "silent_drop" (honeypot) pretends success to bots while discarding data.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>blacklist_message</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( '"Sorry! Your email address or domain is not permitted to submit this form."', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Custom text notice string', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Notice displayed to visitors on the frontend when their submission is rejected by the blacklist.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>auto_reply_enabled</strong></td>
									<td><span class="wlgf-badge wlgf-badge--warning">0 (Disabled)</span></td>
									<td>1 (Enabled), 0 (Disabled)</td>
									<td><?php esc_html_e( 'Form-level switch dispatches instant HTML confirmation email to the submitter upon valid form submission.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>auto_reply_subject</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( '"Thank you for your inquiry! We received your message."', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Custom text with merge tags', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Email subject line delivered to submitters. Supports dynamic merge tags.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>auto_reply_body</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( 'Pre-filled greeting & closing', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Rich text with {Name}, {Email}, {Site_Name}, {Form_Name}, {Submission_Date}', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Confirmation email message body delivered to the inquiry submitter with automated brand logo embedding.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>email_logo_url</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( '"" (Empty)', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Valid image URL from Media Library', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Brand logo embedded in all admin notifications and submitter auto-responders via RFC inline CID attachments.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>email_logo_width</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">180px</span></td>
									<td>60px to 320px</td>
									<td><?php esc_html_e( 'Maximum display width in pixels for brand logo across desktop and mobile email clients.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>email_header_title</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted">"{form_name} - New Lead Submission"</span></td>
									<td><?php esc_html_e( 'Custom text with {form_name}, {site_title}, {form_id}', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'H1 banner heading rendered at the top of admin lead notification emails.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>email_header_bg & color</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">#1e293b / #ffffff</span></td>
									<td><?php esc_html_e( 'Any valid HEX color string', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Background accent and text color for email header banner and cards.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>email_show_dashboard_btn</strong></td>
									<td><span class="wlgf-badge wlgf-badge--success">1 (ON)</span></td>
									<td>1 (Enabled), 0 (Disabled)</td>
									<td><?php esc_html_e( 'Includes a 1-click "View Lead in Dashboard" button inside admin notification emails.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>confirmation_preset</strong></td>
									<td><span class="wlgf-badge wlgf-badge--primary">modern-card</span></td>
									<td>modern-card, banner, minimal-pill, glassmorphism</td>
									<td><?php esc_html_e( 'Visual presentation template for frontend submission success alerts.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>confirmation_icon & animation</strong></td>
									<td><span class="wlgf-badge wlgf-badge--success">fa-check / Pulse ON (1)</span></td>
									<td>8 FontAwesome icons / 1 (ON), 0 (OFF)</td>
									<td><?php esc_html_e( 'Central animated glowing badge icon celebrating successful form submission.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>confirmation_fadeout_duration</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted">0 (No Fadeout)</span></td>
									<td>0 (Permanent), 5 to 50 seconds</td>
									<td><?php esc_html_e( 'Time elapsed before thank you message smoothly fades out and resets form view.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>webhook_url</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( '"" (Empty)', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Valid HTTPS Webhook Endpoint URL', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Form-level destination for real-time JSON submission payloads (Zapier, Make, n8n).', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>schedule_start & schedule_end</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted"><?php esc_html_e( '"" (No Window)', 'lead-generation-form' ); ?></span></td>
									<td><?php esc_html_e( 'Valid YYYY-MM-DD HH:MM timestamps', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Automated time window during which the form actively accepts visitor inquiries.', 'lead-generation-form' ); ?></td>
								</tr>
								<tr>
									<td><strong>max_submissions</strong></td>
									<td><span class="wlgf-badge wlgf-badge--muted">0 (Unlimited)</span></td>
									<td><?php esc_html_e( 'Positive integer (e.g. 50, 100, 500)', 'lead-generation-form' ); ?></td>
									<td><?php esc_html_e( 'Maximum total submissions allowed before form automatically locks and shows closed notice.', 'lead-generation-form' ); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- SECTION 5: Page Builders & Shortcodes -->
			<div class="wlgf-grid wlgf-grid--2 wlgf-mb-4">
				<div class="wlgf-card">
					<div class="wlgf-card__header">
						<h2 class="wlgf-card__title">
							<i class="fa-solid fa-cubes"></i> <?php esc_html_e( '5. Page Builders & Shortcode Embedding Guide', 'lead-generation-form' ); ?>
						</h2>
					</div>
					<div class="wlgf-card__body">
						<div class="wlgf-embed-guide">
							<div class="wlgf-embed-guide__item">
								<h3 class="wlgf-embed-guide__title"><i class="fa-solid fa-cube text-primary"></i> <?php esc_html_e( 'Gutenberg Block Editor', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-embed-guide__desc"><?php esc_html_e( 'Type /lead-form or search for "Lead Generation Form" in the block inserter, then select your form from the inspector dropdown with live preview.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-embed-guide__item wlgf-mt-3">
								<h3 class="wlgf-embed-guide__title"><i class="fa-solid fa-shapes text-primary"></i> <?php esc_html_e( 'Elementor Page Builder', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-embed-guide__desc"><?php esc_html_e( 'Search for the "Lead Generation Form" widget in the Elementor panel and drag it directly into any section or container with full typography controls.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-embed-guide__item wlgf-mt-3">
								<h3 class="wlgf-embed-guide__title"><i class="fa-solid fa-code text-primary"></i> <?php esc_html_e( 'Standard Shortcode Syntax', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-embed-guide__desc"><?php esc_html_e( 'Embed anywhere in classic editor, Divi, Beaver Builder, or widget areas:', 'lead-generation-form' ); ?></p>
								<div class="wlgf-code-pill wlgf-mb-2">
									<code class="wlgf-code-pill__text">[WLFG id="FORM_ID"]</code>
								</div>
								<p class="wlgf-help-text">
									<?php esc_html_e( 'Pro routing parameter: [WLFG id="1" carbon_copy="sales@company.com,team@company.com"] for department notification routing.', 'lead-generation-form' ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>

				<!-- SECTION 6: Comprehensive Troubleshooting & FAQ -->
				<div class="wlgf-card">
					<div class="wlgf-card__header">
						<h2 class="wlgf-card__title">
							<i class="fa-solid fa-circle-question"></i> <?php esc_html_e( '6. Comprehensive Troubleshooting & Knowledge Base FAQ', 'lead-generation-form' ); ?>
						</h2>
					</div>
					<div class="wlgf-card__body">
						<div class="wlgf-faq">
							<div class="wlgf-faq-item">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-envelope-circle-check text-primary"></i> <?php esc_html_e( 'Not receiving lead notification emails?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( 'Default PHP mail() is frequently flagged as spam or dropped by hosts. Configure Custom SMTP under the "Email & SMTP" tab using an authenticated provider (e.g. Google Workspace, SendGrid, Amazon SES, Postmark) and send a test email.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-faq-item wlgf-mt-3">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-reply-all text-primary"></i> <?php esc_html_e( 'How does the Submitter Auto-Responder work and which merge tags are supported?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( 'When enabled in Form Builder > Settings, the auto-responder dispatches a personalized HTML confirmation email directly to the submitter. It automatically embeds your Brand Logo and replaces dynamic tags: {Name} (submitter name), {Email} (submitter email), {Site_Name} (website title), {Form_Name} (current form title), and {Submission_Date} (timestamp).', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-faq-item wlgf-mt-3">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-network-wired text-primary"></i> <?php esc_html_e( 'How do I connect form submissions to Zapier, Make, or n8n?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( 'In Form Builder > Settings tab, paste your webhook URL into "Real-Time Outgoing Webhook". The plugin dispatches non-blocking JSON payloads containing all submitted fields, submission ID, form metadata, and payment transaction details via background HTTP POST.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-faq-item wlgf-mt-3">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-database text-primary"></i> <?php esc_html_e( 'Are leads saved if email delivery fails?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( 'Yes! All lead submissions are immediately written to your database under "Lead Generation Form > Captured Leads" before any email dispatch occurs.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-faq-item wlgf-mt-3">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-shield-halved text-primary"></i> <?php esc_html_e( 'Does page caching break form submissions?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( 'Our forms feature dynamic timing and REST/AJAX handlers. If using aggressive full-page caching (WP Rocket, LiteSpeed, Cloudflare APO), exclude the form page URL from caching to ensure fresh CSRF nonces.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-faq-item wlgf-mt-3">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-cloud-arrow-up text-primary"></i> <?php esc_html_e( 'Where are uploaded files stored and are they protected?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( 'Attachments are sanitized and placed inside a dedicated uploads folder (wp-content/uploads/wlgf_uploads/). Direct execution of PHP scripts is strictly blocked via an automatic .htaccess configuration to prevent malicious exploits. Admins can preview files directly from the Leads table with the interactive lightbox.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-faq-item wlgf-mt-3">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-signature text-primary"></i> <?php esc_html_e( 'Does the Digital Signature Pad work on touchscreens and mobile?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( 'Yes! The signature pad utilizes high-DPI canvas scaling to match your smartphone or tablet retina screen resolution. It captures smooth finger strokes on iPads, iPhones, and Android devices, and automatically stamps the signature onto downloadable vector PDF receipts.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-faq-item wlgf-mt-3">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-user-secret text-primary"></i> <?php esc_html_e( 'How does Blacklist "Silent Drop / Honeypot" work?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( '"Silent Drop" is a stealth defense mechanism. Automated spam bots and phishers receive a deceptive success confirmation on their screen, leading them to believe their submission succeeded. However, the plugin silently discards the payload—no lead is saved in the database, and no emails or webhooks are triggered. This prevents bot operators from detecting blocks and rotating email addresses.', 'lead-generation-form' ); ?></p>
							</div>
							<div class="wlgf-faq-item wlgf-mt-3">
								<h3 class="wlgf-faq-item__q"><i class="fa-solid fa-users text-primary"></i> <?php esc_html_e( 'Can multiple admin team members receive lead alerts?', 'lead-generation-form' ); ?></h3>
								<p class="wlgf-faq-item__a"><?php esc_html_e( 'Yes! Under "Email & SMTP" settings, add comma-separated or newline-separated emails into "CC Additional Admin Email(s)". Every valid lead submission will automatically dispatch formatted HTML notifications with these addresses in the RFC-compliant Cc: header across wp_mail, authenticated SMTP, and REST API submissions.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION 7: Server Environment & Diagnostics -->
			<div class="wlgf-card wlgf-mb-3">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-server"></i> <?php esc_html_e( '7. Server Environment & Runtime Diagnostics', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<p class="wlgf-card__text wlgf-mb-3">
						<?php esc_html_e( 'Current server hosting parameters and WordPress runtime environment:', 'lead-generation-form' ); ?>
					</p>
					<div class="wlgf-flex wlgf-gap-3 wlgf-flex-wrap">
						<div class="wlgf-badge wlgf-badge--primary">
							<?php esc_html_e( 'WordPress:', 'lead-generation-form' ); ?> v<?php echo esc_html( $wlgf_wp_version_str ); ?>
						</div>
						<div class="wlgf-badge wlgf-badge--primary">
							<?php esc_html_e( 'PHP Version:', 'lead-generation-form' ); ?> v<?php echo esc_html( PHP_VERSION ); ?>
						</div>
						<div class="wlgf-badge wlgf-badge--secondary">
							<?php esc_html_e( 'Plugin Version:', 'lead-generation-form' ); ?> v<?php echo esc_html( WLGF_PLUGIN_VERSION ); ?>
						</div>
						<div class="wlgf-badge wlgf-badge--secondary">
							<?php esc_html_e( 'Memory Limit:', 'lead-generation-form' ); ?> <?php echo esc_html( $wlgf_memory_limit ); ?>
						</div>
						<div class="wlgf-badge wlgf-badge--secondary">
							<?php esc_html_e( 'Max Upload Size:', 'lead-generation-form' ); ?> <?php echo esc_html( $wlgf_max_upload_size ); ?>
						</div>
						<div class="wlgf-badge wlgf-badge--secondary">
							<?php esc_html_e( 'Max POST Size:', 'lead-generation-form' ); ?> <?php echo esc_html( $wlgf_max_post_size ); ?>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION 8: Floating Multi-Channel Launcher Guide (SiteLeads Challenger) -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-comment-dots text-primary"></i> <?php esc_html_e( '8. Floating Multi-Channel Contact Launcher Guide', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<p class="wlgf-card__text wlgf-mb-3">
						<?php esc_html_e( 'The Floating Contact Launcher is designed to maximize mobile and desktop conversions by providing immediate click-to-connect access to the communication channels visitors use most.', 'lead-generation-form' ); ?>
					</p>
					<div class="wlgf-grid wlgf-grid--3 wlgf-gap-3">
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__body">
								<strong class="wlgf-card__title" style="font-size: 14px;"><i class="fa-brands fa-whatsapp text-success"></i> <?php esc_html_e( 'WhatsApp Direct Chat', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-help-text wlgf-mt-1"><?php esc_html_e( 'Enter your international phone number (e.g. +14155552671) and an optional pre-filled greeting. Tapping opens WhatsApp Web on desktop or WhatsApp App on mobile instantly.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__body">
								<strong class="wlgf-card__title" style="font-size: 14px;"><i class="fa-solid fa-phone text-info"></i> <?php esc_html_e( '1-Tap Click-to-Call', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-help-text wlgf-mt-1"><?php esc_html_e( 'Connects your sales or support phone number via tel: protocol. On mobile smartphones, one tap immediately dials your business without copy-pasting numbers.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__body">
								<strong class="wlgf-card__title" style="font-size: 14px;"><i class="fa-solid fa-file-signature text-primary"></i> <?php esc_html_e( 'Modal Form Launcher', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-help-text wlgf-mt-1"><?php esc_html_e( 'Select any Lead Generation Form in the "Floating Contact Widget" settings tab. Visitors can click "Send Inquiry Form" to open the form in an instant floating lightbox modal without leaving their current page.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION 9: Popups, Slide-ins & Exit-Intent Triggers Guide (OptinMonster & Hustle Challenger) -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-bolt text-warning"></i> <?php esc_html_e( '9. Popups, Slide-ins & Smart Behavioral Triggers Guide', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<p class="wlgf-card__text wlgf-mb-3">
						<?php esc_html_e( 'Transform any static lead form into a high-converting lightbox modal, corner slide-in, or top notification bar with smart behavioral triggers.', 'lead-generation-form' ); ?>
					</p>
					<div class="wlgf-grid wlgf-grid--4 wlgf-gap-3">
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__body">
								<strong class="wlgf-card__title" style="font-size: 14px;"><i class="fa-solid fa-arrow-up-from-bracket text-danger"></i> <?php esc_html_e( 'Exit-Intent Trigger', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-help-text wlgf-mt-1"><?php esc_html_e( 'Monitors mouse departure towards the browser tab bar on desktop and fast upward scrolls on mobile to catch visitors right before they bounce.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__body">
								<strong class="wlgf-card__title" style="font-size: 14px;"><i class="fa-solid fa-scroll text-primary"></i> <?php esc_html_e( 'Scroll Depth %', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-help-text wlgf-mt-1"><?php esc_html_e( 'Triggers the form popup after a visitor scrolls 25%, 50%, or 75% down the article or landing page.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__body">
								<strong class="wlgf-card__title" style="font-size: 14px;"><i class="fa-solid fa-stopwatch text-info"></i> <?php esc_html_e( 'Time Delay', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-help-text wlgf-mt-1"><?php esc_html_e( 'Displays the popup automatically after X seconds of user engagement on your website.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
						<div class="wlgf-card wlgf-card--bordered">
							<div class="wlgf-card__body">
								<strong class="wlgf-card__title" style="font-size: 14px;"><i class="fa-solid fa-hand-pointer text-success"></i> <?php esc_html_e( 'Click (MonsterLink)', 'lead-generation-form' ); ?></strong>
								<p class="wlgf-help-text wlgf-mt-1"><?php esc_html_e( 'Any link or button on your website with class="wlgf-trigger-popup" data-form-id="1" or href="#wlgf-popup-1" opens the modal immediately on click.', 'lead-generation-form' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION 10: HTML5 E-Signature & PDF Integration Guide (Forminator Challenger) -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-signature text-purple" style="color: #8b5cf6;"></i> <?php esc_html_e( '10. Digital E-Signature Pad & Vector PDF Receipts', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<p class="wlgf-card__text wlgf-mb-2">
						<?php esc_html_e( 'Collect legally compliant digital signatures directly on your forms with touch & mouse stroke recording, and stamp them onto pure-PHP vector PDF invoices.', 'lead-generation-form' ); ?>
					</p>
					<ul class="wlgf-help-list">
						<li><strong><?php esc_html_e( 'How to Add:', 'lead-generation-form' ); ?></strong> <?php esc_html_e( 'In the Form Builder canvas, drag a "Signature Pad" control or use the Gym / Fitness Membership 1-click template.', 'lead-generation-form' ); ?></li>
						<li><strong><?php esc_html_e( 'Smooth Touch Strokes:', 'lead-generation-form' ); ?></strong> <?php esc_html_e( 'Works seamlessly on iPhones, iPads, Android devices, and desktop trackpads/mice with built-in "Clear Signature" action.', 'lead-generation-form' ); ?></li>
						<li><strong><?php esc_html_e( 'PDF Verification:', 'lead-generation-form' ); ?></strong> <?php esc_html_e( 'When viewing leads in All Captured Leads, click the PDF icon to download a branded receipt with the signed verification block.', 'lead-generation-form' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- SECTION 11: Abandoned Lead Recovery & Kanban CRM Pipeline Guide -->
			<div class="wlgf-card wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title">
						<i class="fa-solid fa-chart-gantt text-emerald" style="color: #10b981;"></i> <?php esc_html_e( '11. Abandoned Lead Recovery & Mini-CRM Kanban Pipeline', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<div class="wlgf-grid wlgf-grid--2 wlgf-gap-3">
						<div>
							<h3 style="font-size: 15px; font-weight: 700; margin-bottom: 6px;"><i class="fa-solid fa-user-clock text-danger"></i> <?php esc_html_e( 'Partial / Abandoned Lead Auto-Capture', 'lead-generation-form' ); ?></h3>
							<p class="wlgf-help-text"><?php esc_html_e( '70% of website visitors abandon forms midway. With Abandoned Lead Capture enabled, as soon as a visitor fills their email or phone number and leaves without completing the form, a draft lead is automatically stored in your CRM under "Abandoned / Draft" for immediate WhatsApp or phone follow-up.', 'lead-generation-form' ); ?></p>
						</div>
						<div>
							<h3 style="font-size: 15px; font-weight: 700; margin-bottom: 6px;"><i class="fa-solid fa-table-columns text-primary"></i> <?php esc_html_e( 'Visual Kanban Pipeline Management', 'lead-generation-form' ); ?></h3>
							<p class="wlgf-help-text"><?php esc_html_e( 'Switch between Table View and Kanban Pipeline on the All Captured Leads page. Drag lead cards between stages (New -> Contacted -> Follow-Up -> Qualified -> Won). Each card features 1-click WhatsApp chat, direct phone dial, and PDF receipt downloads.', 'lead-generation-form' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- SECTION 12: Complete Factory Reset (Danger Zone) -->
			<div class="wlgf-card wlgf-card--danger-zone wlgf-mb-4">
				<div class="wlgf-card__header">
					<h2 class="wlgf-card__title text-danger" style="color: #ef4444;">
						<i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i> <?php esc_html_e( '12. Danger Zone: Complete Factory Reset', 'lead-generation-form' ); ?>
					</h2>
				</div>
				<div class="wlgf-card__body">
					<!-- Backup Recommendation Alert -->
					<div class="wlgf-reset-backup-notice">
						<i class="fa-solid fa-triangle-exclamation"></i>
						<div class="wlgf-reset-backup-notice__text">
							<strong><?php esc_html_e( 'Important: Take a Data Backup First!', 'lead-generation-form' ); ?></strong><br>
							<?php esc_html_e( 'Performing a factory reset will permanently erase all custom forms, captured lead records, customer notes, tracking analytics, and custom configuration settings. This action cannot be reversed.', 'lead-generation-form' ); ?><br>
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: Link to Import & Export page */
									__( 'We strongly advise you to download a backup of your forms and leads before proceeding. Visit the %s to export your data.', 'lead-generation-form' ),
									'<a href="' . esc_url( admin_url( 'admin.php?page=wlgf-import-export' ) ) . '" target="_blank" class="wlgf-reset-backup-notice__link">' . esc_html__( 'Import & Export Page', 'lead-generation-form' ) . ' <i class="fa-solid fa-arrow-up-right-from-square"></i></a>'
								),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
										'class'  => array(),
									),
									'i' => array(
										'class'  => array(),
									),
								)
							);
							?>
						</div>
					</div>

					<div class="wlgf-grid wlgf-grid--2 wlgf-gap-3" style="align-items: center;">
						<div>
							<h3 style="font-size: 15px; font-weight: 700; margin-bottom: 6px; color: #1f2937;">
								<i class="fa-solid fa-rotate-left" style="color: #ef4444;"></i> <?php esc_html_e( 'Reset Plugin to Initial Factory State', 'lead-generation-form' ); ?>
							</h3>
							<p class="wlgf-help-text" style="margin-bottom: 0;">
								<?php esc_html_e( 'This will purge all custom options, leads, analytics, and restore the clean default starter forms (Contact Form & Sample Lead Form) and default settings, giving you a fresh start.', 'lead-generation-form' ); ?>
							</p>
						</div>
						<div style="text-align: right;">
							<button type="button" id="wlgf-factory-reset-btn" class="wlgf-btn wlgf-btn--danger" style="padding: 10px 20px; font-weight: 600;">
								<i class="fa-solid fa-rotate-left"></i> <?php esc_html_e( 'Reset Plugin to Factory Defaults', 'lead-generation-form' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- TAB 7: Upgrade to Pro (Comparison Table) -->
		<div id="wlgf-tab-upgrade" class="wlgf-tab-content">
			<?php
			$wlgf_is_tab_embed = true;
			require plugin_dir_path( __FILE__ ) . 'free-vs-pro.php';
			?>
		</div>
	</form>

	<!-- Email Template Live Preview Modal -->
	<div id="wlgf-email-preview-modal" class="wlgf-modal-overlay d-none" role="dialog" aria-modal="true">
		<div class="wlgf-modal wlgf-email-preview-modal-dialog" style="max-width: 780px; width: 95%; max-height: 90vh; display: flex; flex-direction: column;">
			<div class="wlgf-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid var(--wlgf-border);">
				<div class="wlgf-flex wlgf-items-center wlgf-gap-3">
					<h3 style="margin: 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
						<i class="fa-solid fa-envelope-open-text text-primary"></i> <?php esc_html_e( 'Email Template Live Preview', 'lead-generation-form' ); ?>
					</h3>
					<!-- Viewport Toggle -->
					<div class="wlgf-preview-viewport-toggle" style="display: inline-flex; border: 1px solid var(--wlgf-border); border-radius: 6px; overflow: hidden; background: #f8fafc;">
						<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-preview-size-btn wlgf-preview-size-btn--active" data-width="100%" style="padding: 4px 10px; font-size: 12px; border: none; border-radius: 0;">
							<i class="fa-solid fa-desktop"></i> <?php esc_html_e( 'Desktop', 'lead-generation-form' ); ?>
						</button>
						<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-preview-size-btn" data-width="400px" style="padding: 4px 10px; font-size: 12px; border: none; border-radius: 0;">
							<i class="fa-solid fa-mobile-screen"></i> <?php esc_html_e( 'Mobile', 'lead-generation-form' ); ?>
						</button>
					</div>
				</div>
				<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
					<button type="button" id="wlgf-preview-modal-test-btn" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline" style="padding: 4px 12px; font-size: 12px;">
						<i class="fa-solid fa-paper-plane"></i> <?php esc_html_e( 'Send Test Email', 'lead-generation-form' ); ?>
					</button>
					<button type="button" id="wlgf-close-email-preview-btn" class="wlgf-btn wlgf-btn--sm wlgf-btn--secondary" aria-label="Close" style="padding: 4px 10px;">
						<i class="fa-solid fa-xmark"></i>
					</button>
				</div>
			</div>
			<div class="wlgf-modal-body" style="padding: 20px; background: #cbd5e1; flex: 1; overflow-y: auto;">
				<!-- Simulated Email Client Container -->
				<div id="wlgf-email-preview-viewport" style="max-width: 100%; margin: 0 auto; transition: max-width 0.25s ease; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); border-radius: 10px; overflow: hidden; background: #ffffff;">
					<div id="wlgf-email-preview-content">
						<div style="text-align: center; padding: 40px;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 24px;"></i></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Send Test Email Modal -->
	<div id="wlgf-test-email-modal" class="wlgf-modal-overlay d-none" role="dialog" aria-modal="true">
		<div class="wlgf-modal" style="max-width: 520px; width: 95%;">
			<div class="wlgf-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--wlgf-border);">
				<h3 style="margin: 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
					<i class="fa-solid fa-paper-plane text-primary"></i> <?php esc_html_e( 'Send Test Notification Email', 'lead-generation-form' ); ?>
				</h3>
				<button type="button" id="wlgf-close-test-email-modal-btn" class="wlgf-btn wlgf-btn--sm wlgf-btn--secondary" aria-label="Close" style="padding: 4px 10px;">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<div class="wlgf-modal-body" style="padding: 20px;">
				<p class="wlgf-help-text wlgf-mb-3" style="font-size: 13px; line-height: 1.5; color: #475569;">
					<?php esc_html_e( 'Send a test email with your latest customized template styling, brand logo, and sample lead submission data to test deliverability directly in your inbox.', 'lead-generation-form' ); ?>
				</p>
				<div class="wlgf-form-group wlgf-mb-3">
					<label for="wlgf_modal_test_email" class="wlgf-label"><?php esc_html_e( 'Recipient Email Address', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span></label>
					<input type="email" class="wlgf-input wlgf-w-full" id="wlgf_modal_test_email" value="<?php echo esc_attr( $wlgf_admin_email ); ?>" placeholder="your-email@domain.com">
				</div>
				<div class="wlgf-card-hint" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #64748b; margin-bottom: 16px;">
					<i class="fa-solid fa-circle-info text-primary" style="margin-right: 6px;"></i>
					<span id="wlgf-active-engine-hint"><?php echo ( '2' === (string) $wlgf_email_engine ) ? esc_html__( 'Active Dispatch: Custom SMTP Server', 'lead-generation-form' ) : esc_html__( 'Active Dispatch: Default WordPress Mail (wp_mail)', 'lead-generation-form' ); ?></span>
				</div>
				<div id="wlgf-modal-test-email-result" class="wlgf-mb-2"></div>
			</div>
			<div class="wlgf-modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--wlgf-border); display: flex; justify-content: flex-end; gap: 10px; background: #fafafa;">
				<button type="button" id="wlgf-cancel-test-email-btn" class="wlgf-btn wlgf-btn--secondary wlgf-btn--sm">
					<?php esc_html_e( 'Cancel', 'lead-generation-form' ); ?>
				</button>
				<button type="button" id="wlgf-confirm-send-test-btn" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm">
					<i class="fa-solid fa-paper-plane"></i> <?php esc_html_e( 'Send Test Email Now', 'lead-generation-form' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
<script id="wlgf-blacklist-inline-js">
(function() {
    function initBlacklistUI() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initBlacklistUI, 50);
            return;
        }
        var $ = jQuery;
        $(document).off('change.wlgf_bl_status').on('change.wlgf_bl_status', '#blacklist_status', function() {
            var val = $(this).val();
            if (val === '1') {
                $('#wlgf-blacklist-badge').removeClass('wlgf-badge--secondary').addClass('wlgf-badge--success');
                $('#wlgf-blacklist-badge-icon').removeClass('fa-pause').addClass('fa-shield-halved');
                $('#wlgf-blacklist-badge-text').text('Active & Blocking');
                $('#wlgf-blacklist-paused-notice').addClass('d-none');
            } else {
                $('#wlgf-blacklist-badge').removeClass('wlgf-badge--success').addClass('wlgf-badge--secondary');
                $('#wlgf-blacklist-badge-icon').removeClass('fa-shield-halved').addClass('fa-pause');
                $('#wlgf-blacklist-badge-text').text('Enforcement Paused');
                $('#wlgf-blacklist-paused-notice').removeClass('d-none');
            }
        });
        $(document).off('change.wlgf_bl_action').on('change.wlgf_bl_action', '#blacklist_action', function() {
            if ($(this).val() === 'reject_error') {
                $('#wlgf-blacklist-msg-wrap').removeClass('d-none');
            } else {
                $('#wlgf-blacklist-msg-wrap').addClass('d-none');
            }
        });
        $(document).off('click.wlgf_bl_chip').on('click.wlgf_bl_chip', '.wlgf-quick-add-domain', function(e) {
            e.preventDefault();
            var domain = $(this).data('domain');
            var $textarea = $('#blacklist_domains');
            var currentVal = ($textarea.val() || '').trim();
            var lines = currentVal ? currentVal.split(/[\r\n,]+/).map(function(s) { return s.trim().toLowerCase(); }) : [];
            if (lines.indexOf(domain.toLowerCase()) === -1) {
                if (currentVal.length > 0) {
                    $textarea.val(currentVal + '\n' + domain);
                } else {
                    $textarea.val(domain);
                }
                $(this).css({'background': '#dcfce7', 'color': '#166534', 'border-color': '#86efac'});
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBlacklistUI);
    } else {
        initBlacklistUI();
    }
})();
</script>
