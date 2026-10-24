<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

// Enqueue Core & jQuery UI dependencies for FormBuilder
wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-accordion' );
wp_enqueue_script( 'jquery-ui-autocomplete' );
wp_enqueue_script( 'jquery-ui-button' );
wp_enqueue_script( 'jquery-ui-datepicker' );
wp_enqueue_script( 'jquery-ui-dialog' );
wp_enqueue_script( 'jquery-ui-draggable' );
wp_enqueue_script( 'jquery-ui-droppable' );
wp_enqueue_script( 'jquery-ui-menu' );
wp_enqueue_script( 'jquery-ui-mouse' );
wp_enqueue_script( 'jquery-ui-progressbar' );
wp_enqueue_script( 'jquery-ui-selectable' );
wp_enqueue_script( 'jquery-ui-resizable' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_script( 'jquery-ui-slider' );
wp_enqueue_script( 'jquery-ui-spinner' );
wp_enqueue_script( 'jquery-ui-tooltip' );
wp_enqueue_script( 'jquery-ui-tabs' );
wp_enqueue_script( 'jquery-effects-core' );
wp_enqueue_script( 'jquery-effects-blind' );
wp_enqueue_script( 'jquery-effects-shake' );

wp_enqueue_script( 'wlgf-formbuilder-js', plugin_dir_url( __FILE__ ) . 'assets/formbuilder-3.19.12/js/form-builder.min.js', array( 'jquery' ), '3.19.12', true );
wp_enqueue_style( 'wlgf-flatpickr-css', plugin_dir_url( __FILE__ ) . 'assets/css/flatpickr.min.css', array(), '4.6.13', 'all' );
wp_enqueue_script( 'wlgf-flatpickr-js', plugin_dir_url( __FILE__ ) . 'assets/js/flatpickr.min.js', array( 'jquery' ), '4.6.13', true );
wp_enqueue_style( 'wlgf-admin-ui-css' );
wp_enqueue_style( 'wlgf-fontawesome-css', plugins_url( 'assets/fontawesome-free-6.4.2-web/css/all.min.css', __FILE__ ), array(), '6.4.2', 'all' );

$wlgf_FormIdSet           = 'notset';
$wlgf_form_id             = '';
$wlgf_form_name           = '';
$wlgf_form_data           = '';
$wlgf_confirmation_type   = 'message';
$wlgf_redirect_url         = '';
$wlgf_custom_message       = '';
$wlgf_default_auto_reply_subject = __( 'Thank you for your inquiry! We received your message.', 'lead-generation-form' );
$wlgf_default_auto_reply_body    = "Hi {Name},\n\nThank you for reaching out to us. We have received your inquiry regarding \"{Form_Name}\" and will get back to you shortly.\n\nBest regards,\n{Site_Name}";

$wlgf_auto_reply_enabled   = 0;
$wlgf_auto_reply_subject   = $wlgf_default_auto_reply_subject;
$wlgf_auto_reply_body      = $wlgf_default_auto_reply_body;
$wlgf_theme_primary        = '#2563eb';
$wlgf_theme_radius         = '6px';
$wlgf_theme_bg             = 'card';
$wlgf_theme_font           = 'inherit';
$wlgf_theme_btn_width      = 'auto';
$wlgf_theme_field_size     = 'default';
$wlgf_theme_mode           = 'light';
$wlgf_theme_input_style    = 'bordered';
$wlgf_theme_max_width      = '640px';
$wlgf_theme_btn_shadow     = 'default';
$wlgf_theme_label_style    = 'default';
$wlgf_webhook_url          = '';
$wlgf_webhook_auth         = '';
$wlgf_schedule_start       = '';
$wlgf_schedule_end         = '';
$wlgf_max_submissions      = 0;
$wlgf_closed_message       = '';
$wlgf_newsletter_enabled         = 0;
$wlgf_newsletter_mode            = 'explicit';
$wlgf_newsletter_label           = __( 'Subscribe to our newsletter for exclusive updates and offers', 'lead-generation-form' );
$wlgf_newsletter_default_checked = 0;
$wlgf_newsletter_tag             = '';
$wlgf_wizard_style               = 'circles';
$wlgf_wizard_step_1_title         = '';
$wlgf_wizard_prev_text           = __( 'Previous', 'lead-generation-form' );
$wlgf_wizard_next_text           = __( 'Next Step', 'lead-generation-form' );
$wlgf_conditions                 = array();
$wlgf_payment_enabled            = 0;
$wlgf_payment_type               = 'fixed';
$wlgf_payment_amount             = '0.00';
$wlgf_payment_calc_field         = '';
$wlgf_payment_item_name          = '';
$wlgf_display_mode               = 'inline';
$wlgf_display_position           = 'right';
$wlgf_trigger_type               = 'time_delay';
$wlgf_trigger_delay              = 5;
$wlgf_trigger_scroll             = 50;
$wlgf_trigger_frequency          = 7;
$wlgf_capture_abandoned          = 1;


// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET['form-id'] ) && current_user_can( 'manage_options' ) ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$wlgf_form_id             = sanitize_text_field( wp_unslash( $_GET['form-id'] ) );
	$wlgf_saved_record = get_option( 'wlgf_form_' . $wlgf_form_id );
	if ( ! empty( $wlgf_saved_record ) ) {
		// Normalize $wlgf_saved_record if it was stored as a JSON string or object in legacy versions
		if ( is_string( $wlgf_saved_record ) ) {
			$wlgf_maybe_decoded = json_decode( $wlgf_saved_record, true );
			if ( ! is_array( $wlgf_maybe_decoded ) ) {
				$wlgf_maybe_decoded = json_decode( stripslashes( $wlgf_saved_record ), true );
			}
			if ( is_array( $wlgf_maybe_decoded ) ) {
				$wlgf_saved_record = $wlgf_maybe_decoded;
			}
		} elseif ( is_object( $wlgf_saved_record ) ) {
			$wlgf_saved_record = (array) $wlgf_saved_record;
		}

		if ( is_array( $wlgf_saved_record ) ) {
			$wlgf_form_name = isset( $wlgf_saved_record['form_name'] ) ? sanitize_text_field( $wlgf_saved_record['form_name'] ) : ( isset( $wlgf_saved_record['name'] ) ? sanitize_text_field( $wlgf_saved_record['name'] ) : '' );

			// Multi-tier decoding for form canvas data (handles objects, arrays, and legacy single/double-encoded JSON strings)
			$wlgf_raw_form_content = isset( $wlgf_saved_record['form'] ) ? $wlgf_saved_record['form'] : ( isset( $wlgf_saved_record['fields'] ) ? $wlgf_saved_record['fields'] : '' );

			if ( is_array( $wlgf_raw_form_content ) ) {
				$wlgf_form_data = $wlgf_raw_form_content;
			} elseif ( is_object( $wlgf_raw_form_content ) ) {
				$wlgf_form_data = (array) $wlgf_raw_form_content;
			} else {
				$wlgf_raw_str = (string) $wlgf_raw_form_content;
				$wlgf_decoded = json_decode( $wlgf_raw_str, true );
				if ( ! is_array( $wlgf_decoded ) ) {
					$wlgf_decoded = json_decode( stripslashes( $wlgf_raw_str ), true );
				}
				if ( is_string( $wlgf_decoded ) ) {
					$wlgf_second = json_decode( $wlgf_decoded, true );
					if ( is_array( $wlgf_second ) ) {
						$wlgf_decoded = $wlgf_second;
					}
				}
				$wlgf_form_data = is_array( $wlgf_decoded ) ? $wlgf_decoded : array();
			}

			$wlgf_confirmation_type          = isset( $wlgf_saved_record['confirmation_type'] ) ? sanitize_text_field( $wlgf_saved_record['confirmation_type'] ) : 'message';
			$wlgf_redirect_url                = isset( $wlgf_saved_record['redirect_url'] ) ? esc_url_raw( $wlgf_saved_record['redirect_url'] ) : '';
			$wlgf_custom_message              = isset( $wlgf_saved_record['custom_message'] ) ? sanitize_text_field( $wlgf_saved_record['custom_message'] ) : '';
			$wlgf_auto_reply_enabled          = isset( $wlgf_saved_record['auto_reply_enabled'] ) ? intval( $wlgf_saved_record['auto_reply_enabled'] ) : 0;
			$wlgf_auto_reply_subject          = ( isset( $wlgf_saved_record['auto_reply_subject'] ) && '' !== trim( $wlgf_saved_record['auto_reply_subject'] ) ) ? sanitize_text_field( $wlgf_saved_record['auto_reply_subject'] ) : $wlgf_default_auto_reply_subject;
			$wlgf_auto_reply_body             = ( isset( $wlgf_saved_record['auto_reply_body'] ) && '' !== trim( $wlgf_saved_record['auto_reply_body'] ) ) ? wp_kses_post( $wlgf_saved_record['auto_reply_body'] ) : $wlgf_default_auto_reply_body;
			$wlgf_theme_primary               = ( isset( $wlgf_saved_record['theme_primary'] ) && ! empty( $wlgf_saved_record['theme_primary'] ) ) ? sanitize_hex_color( $wlgf_saved_record['theme_primary'] ) : '#2563eb';
			$wlgf_theme_radius                = isset( $wlgf_saved_record['theme_radius'] ) ? sanitize_text_field( $wlgf_saved_record['theme_radius'] ) : '6px';
			$wlgf_theme_bg                    = isset( $wlgf_saved_record['theme_bg'] ) ? sanitize_text_field( $wlgf_saved_record['theme_bg'] ) : 'card';
			$wlgf_theme_font                  = isset( $wlgf_saved_record['theme_font'] ) ? sanitize_text_field( $wlgf_saved_record['theme_font'] ) : 'inherit';
			$wlgf_theme_btn_width             = isset( $wlgf_saved_record['theme_btn_width'] ) ? sanitize_text_field( $wlgf_saved_record['theme_btn_width'] ) : 'auto';
			$wlgf_theme_field_size            = isset( $wlgf_saved_record['theme_field_size'] ) ? sanitize_text_field( $wlgf_saved_record['theme_field_size'] ) : 'default';
			$wlgf_theme_mode                  = isset( $wlgf_saved_record['theme_mode'] ) ? sanitize_text_field( $wlgf_saved_record['theme_mode'] ) : 'light';
			$wlgf_theme_input_style           = isset( $wlgf_saved_record['theme_input_style'] ) ? sanitize_text_field( $wlgf_saved_record['theme_input_style'] ) : 'bordered';
			$wlgf_theme_max_width             = ( isset( $wlgf_saved_record['theme_max_width'] ) && ! empty( $wlgf_saved_record['theme_max_width'] ) ) ? sanitize_text_field( $wlgf_saved_record['theme_max_width'] ) : '640px';
			$wlgf_theme_btn_shadow            = isset( $wlgf_saved_record['theme_btn_shadow'] ) ? sanitize_text_field( $wlgf_saved_record['theme_btn_shadow'] ) : 'default';
			$wlgf_theme_label_style           = isset( $wlgf_saved_record['theme_label_style'] ) ? sanitize_text_field( $wlgf_saved_record['theme_label_style'] ) : 'default';
			$wlgf_webhook_url                 = isset( $wlgf_saved_record['webhook_url'] ) ? esc_url_raw( $wlgf_saved_record['webhook_url'] ) : '';
			$wlgf_webhook_auth                = isset( $wlgf_saved_record['webhook_auth'] ) ? sanitize_text_field( $wlgf_saved_record['webhook_auth'] ) : '';
			$wlgf_schedule_start              = isset( $wlgf_saved_record['schedule_start'] ) ? sanitize_text_field( $wlgf_saved_record['schedule_start'] ) : '';
			$wlgf_schedule_end                = isset( $wlgf_saved_record['schedule_end'] ) ? sanitize_text_field( $wlgf_saved_record['schedule_end'] ) : '';
			$wlgf_max_submissions             = isset( $wlgf_saved_record['max_submissions'] ) ? intval( $wlgf_saved_record['max_submissions'] ) : 0;
			$wlgf_closed_message              = isset( $wlgf_saved_record['closed_message'] ) ? sanitize_text_field( $wlgf_saved_record['closed_message'] ) : '';
			$wlgf_newsletter_enabled          = isset( $wlgf_saved_record['newsletter_enabled'] ) ? intval( $wlgf_saved_record['newsletter_enabled'] ) : 0;
			$wlgf_newsletter_mode             = isset( $wlgf_saved_record['newsletter_mode'] ) ? sanitize_text_field( $wlgf_saved_record['newsletter_mode'] ) : 'explicit';
			$wlgf_newsletter_label            = isset( $wlgf_saved_record['newsletter_label'] ) ? sanitize_text_field( $wlgf_saved_record['newsletter_label'] ) : __( 'Subscribe to our newsletter for exclusive updates and offers', 'lead-generation-form' );
			$wlgf_newsletter_default_checked  = isset( $wlgf_saved_record['newsletter_default_checked'] ) ? intval( $wlgf_saved_record['newsletter_default_checked'] ) : 0;
			$wlgf_newsletter_tag              = isset( $wlgf_saved_record['newsletter_tag'] ) ? sanitize_text_field( $wlgf_saved_record['newsletter_tag'] ) : '';
			$wlgf_wizard_style                = isset( $wlgf_saved_record['wizard_style'] ) ? sanitize_text_field( $wlgf_saved_record['wizard_style'] ) : 'circles';
			$wlgf_wizard_step_1_title         = isset( $wlgf_saved_record['wizard_step_1_title'] ) ? sanitize_text_field( $wlgf_saved_record['wizard_step_1_title'] ) : '';
			$wlgf_wizard_prev_text            = isset( $wlgf_saved_record['wizard_prev_text'] ) ? sanitize_text_field( $wlgf_saved_record['wizard_prev_text'] ) : __( 'Previous', 'lead-generation-form' );
			$wlgf_wizard_next_text            = isset( $wlgf_saved_record['wizard_next_text'] ) ? sanitize_text_field( $wlgf_saved_record['wizard_next_text'] ) : __( 'Next Step', 'lead-generation-form' );
			$wlgf_conditions                  = isset( $wlgf_saved_record['conditions'] ) && is_array( $wlgf_saved_record['conditions'] ) ? $wlgf_saved_record['conditions'] : array();
			$wlgf_payment_enabled             = isset( $wlgf_saved_record['payment_enabled'] ) ? intval( $wlgf_saved_record['payment_enabled'] ) : 0;
			$wlgf_payment_type                = isset( $wlgf_saved_record['payment_type'] ) ? sanitize_text_field( $wlgf_saved_record['payment_type'] ) : 'fixed';
			$wlgf_payment_amount              = isset( $wlgf_saved_record['payment_amount'] ) ? sanitize_text_field( $wlgf_saved_record['payment_amount'] ) : '0.00';
			$wlgf_payment_calc_field          = isset( $wlgf_saved_record['payment_calc_field'] ) ? sanitize_text_field( $wlgf_saved_record['payment_calc_field'] ) : '';
			$wlgf_payment_item_name           = isset( $wlgf_saved_record['payment_item_name'] ) ? sanitize_text_field( $wlgf_saved_record['payment_item_name'] ) : '';
			$wlgf_display_mode                = isset( $wlgf_saved_record['display_mode'] ) ? sanitize_text_field( $wlgf_saved_record['display_mode'] ) : 'inline';
			$wlgf_display_position            = isset( $wlgf_saved_record['display_position'] ) ? sanitize_text_field( $wlgf_saved_record['display_position'] ) : 'right';
			$wlgf_trigger_type                = isset( $wlgf_saved_record['trigger_type'] ) ? sanitize_text_field( $wlgf_saved_record['trigger_type'] ) : 'time_delay';
			$wlgf_trigger_delay               = isset( $wlgf_saved_record['trigger_delay'] ) ? intval( $wlgf_saved_record['trigger_delay'] ) : 5;
			$wlgf_trigger_scroll              = isset( $wlgf_saved_record['trigger_scroll'] ) ? intval( $wlgf_saved_record['trigger_scroll'] ) : 50;
			$wlgf_trigger_frequency           = isset( $wlgf_saved_record['trigger_frequency'] ) ? intval( $wlgf_saved_record['trigger_frequency'] ) : 7;
			$wlgf_capture_abandoned           = isset( $wlgf_saved_record['capture_abandoned'] ) ? intval( $wlgf_saved_record['capture_abandoned'] ) : 1;
			$wlgf_FormIdSet                   = 'set';
		}
	}
	}

$wlgf_fg_css_ver = file_exists( plugin_dir_path( __FILE__ ) . 'assets/css/form-generator.css' ) ? WLGF_PLUGIN_VERSION . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/form-generator.css' ) : WLGF_PLUGIN_VERSION;
$wlgf_fg_js_ver  = file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/form-generator.js' ) ? WLGF_PLUGIN_VERSION . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/form-generator.js' ) : WLGF_PLUGIN_VERSION;
wp_enqueue_style( 'wlgf-form-generator-css', plugin_dir_url( __FILE__ ) . 'assets/css/form-generator.css', array( 'wlgf-admin-ui-css' ), $wlgf_fg_css_ver, 'all' );
wp_enqueue_script( 'wlgf-form-generator-js', plugin_dir_url( __FILE__ ) . 'assets/js/form-generator.js', array( 'jquery', 'wlgf-formbuilder-js' ), $wlgf_fg_js_ver, true );

wp_add_inline_script(
	'wlgf-form-generator-js',
	'const FormGenerator = ' . wp_json_encode(
		array(
			'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
			'pluginUrl'         => plugin_dir_url( dirname( __FILE__ ) ),
			'FormIdSet'         => $wlgf_FormIdSet,
			'FormId'            => $wlgf_form_id,
			'FormName'          => $wlgf_form_name,
			'FormData'          => $wlgf_form_data,
			'ConfirmationType'  => $wlgf_confirmation_type,
			'RedirectUrl'       => $wlgf_redirect_url,
			'AutoReplyEnabled'  => $wlgf_auto_reply_enabled,
			'AutoReplySubject'  => $wlgf_auto_reply_subject,
			'AutoReplyBody'     => $wlgf_auto_reply_body,
			'SiteName'          => esc_html( get_bloginfo( 'name' ) ),
			'DefaultReplySubj'  => $wlgf_default_auto_reply_subject,
			'DefaultReplyBody'  => $wlgf_default_auto_reply_body,
			'SaveNonce'         => wp_create_nonce( 'wlgf-save-form' ),
			'ModifyNonce'       => wp_create_nonce( 'wlgf-modify-form' ),
			'WizardStyle'       => $wlgf_wizard_style,
			'WizardStep1Title'  => $wlgf_wizard_step_1_title,
			'WizardPrevText'    => $wlgf_wizard_prev_text,
			'WizardNextText'    => $wlgf_wizard_next_text,
			'Conditions'        => $wlgf_conditions,
			'PaymentEnabled'    => $wlgf_payment_enabled,
			'PaymentType'       => $wlgf_payment_type,
			'PaymentAmount'     => $wlgf_payment_amount,
			'PaymentCalcField'  => $wlgf_payment_calc_field,
			'PaymentItemName'   => $wlgf_payment_item_name,
			'DisplayMode'       => $wlgf_display_mode,
			'DisplayPosition'   => $wlgf_display_position,
			'TriggerType'       => $wlgf_trigger_type,
			'TriggerDelay'      => $wlgf_trigger_delay,
			'TriggerScroll'     => $wlgf_trigger_scroll,
			'TriggerFrequency'  => $wlgf_trigger_frequency,
			'CaptureAbandoned'  => $wlgf_capture_abandoned,
			'IsPro'             => defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO,
		)
	),
	'before'
);

$wlgf_max_upload_size = ini_get( 'upload_max_filesize' );
$wlgf_max_post_size   = ini_get( 'post_max_size' );
?>

<div class="wlgf-admin-wrap">
	<style>
	/* Sticky Header for Form Generator */
	.wlgf-admin-wrap .wlgf-header.wlgf-header--sticky,
	.wlgf-header--sticky {
		position: sticky;
		top: 32px;
		z-index: 900;
		background: var(--wlgf-surface, #ffffff);
		transition: border-radius 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
	}
	body.admin-bar .wlgf-admin-wrap .wlgf-header.wlgf-header--sticky,
	body.admin-bar .wlgf-header--sticky {
		top: 32px;
	}
	body:not(.admin-bar) .wlgf-admin-wrap .wlgf-header.wlgf-header--sticky,
	body:not(.admin-bar) .wlgf-header--sticky {
		top: 0;
	}
	@media screen and (max-width: 782px) {
		body.admin-bar .wlgf-admin-wrap .wlgf-header.wlgf-header--sticky,
		body.admin-bar .wlgf-header--sticky {
			top: 46px;
		}
	}
	@media screen and (max-width: 600px) {
		body.admin-bar .wlgf-admin-wrap .wlgf-header.wlgf-header--sticky,
		body.admin-bar .wlgf-header--sticky {
			top: 0;
		}
	}
	.wlgf-admin-wrap .wlgf-header.wlgf-header--sticky.wlgf-header--is-stuck,
	.wlgf-header--sticky.wlgf-header--is-stuck {
		border-top-left-radius: 0 !important;
		border-top-right-radius: 0 !important;
		border-top-color: transparent !important;
		box-shadow: 0 10px 25px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -2px rgba(15, 23, 42, 0.04) !important;
	}

	@keyframes wlgfTemplatePulse {
		0% {
			box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.65), 0 4px 14px rgba(99, 102, 241, 0.35);
		}
		70% {
			box-shadow: 0 0 0 10px rgba(99, 102, 241, 0), 0 4px 14px rgba(99, 102, 241, 0.35);
		}
		100% {
			box-shadow: 0 0 0 0 rgba(99, 102, 241, 0), 0 4px 14px rgba(99, 102, 241, 0.35);
		}
	}
	@keyframes wlgfSparkleTwinkle {
		0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.9; }
		50% { transform: scale(1.3) rotate(15deg); opacity: 1; filter: drop-shadow(0 0 4px #ffd700); }
	}
	.wlgf-admin-wrap .wlgf-btn.wlgf-btn--templates-highlight,
	#wlgf-open-templates-btn {
		background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
		color: #ffffff !important;
		border: 1px solid #4338ca !important;
		font-weight: 600 !important;
		padding: 0 16px !important;
		height: 38px !important;
		line-height: 36px !important;
		border-radius: 8px !important;
		display: inline-flex !important;
		align-items: center !important;
		gap: 8px !important;
		animation: wlgfTemplatePulse 2.5s infinite !important;
		transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
		cursor: pointer !important;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15) !important;
	}
	.wlgf-admin-wrap .wlgf-btn.wlgf-btn--templates-highlight:hover,
	#wlgf-open-templates-btn:hover {
		background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%) !important;
		transform: translateY(-2px) scale(1.02) !important;
		box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5) !important;
		color: #ffffff !important;
	}
	.wlgf-btn--templates-highlight .wlgf-btn-sparkle {
		display: inline-flex;
		color: #fef08a;
		animation: wlgfSparkleTwinkle 2s ease-in-out infinite;
	}
	.wlgf-btn--templates-highlight .wlgf-pill-badge {
		background: rgba(255, 255, 255, 0.25);
		color: #ffffff;
		font-size: 11px;
		font-weight: 700;
		padding: 2px 8px;
		border-radius: 9999px;
		letter-spacing: 0.02em;
		border: 1px solid rgba(255, 255, 255, 0.3);
		line-height: 1.2;
	}
	.wlgf-templates-callout-banner {
		display: flex !important;
		align-items: center !important;
		justify-content: space-between !important;
		gap: 16px !important;
		background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%) !important;
		border: 1px solid #ddd6fe !important;
		border-left: 4px solid #7c3aed !important;
		border-radius: 10px !important;
		padding: 12px 18px !important;
		margin-bottom: 22px !important;
		box-shadow: 0 2px 8px rgba(124, 58, 237, 0.06) !important;
		flex-wrap: wrap !important;
	}
	.wlgf-templates-callout-banner__left {
		display: flex !important;
		align-items: center !important;
		gap: 12px !important;
		font-size: 13.5px !important;
		color: #4c1d95 !important;
		font-weight: 500 !important;
		flex: 1 !important;
		min-width: 260px !important;
	}
	.wlgf-templates-callout-badge {
		display: inline-flex !important;
		align-items: center !important;
		gap: 5px !important;
		background: #7c3aed !important;
		color: #ffffff !important;
		font-size: 11px !important;
		font-weight: 700 !important;
		text-transform: uppercase !important;
		letter-spacing: 0.05em !important;
		padding: 3px 9px !important;
		border-radius: 6px !important;
		white-space: nowrap !important;
		box-shadow: 0 2px 4px rgba(124, 58, 237, 0.25) !important;
	}
	.wlgf-open-templates-shortcut-btn {
		background: #7c3aed !important;
		border-color: #6d28d9 !important;
		color: #ffffff !important;
		font-weight: 600 !important;
		box-shadow: 0 2px 6px rgba(124, 58, 237, 0.25) !important;
		white-space: nowrap !important;
		transition: all 0.2s ease !important;
	}
	.wlgf-open-templates-shortcut-btn:hover {
		background: #6d28d9 !important;
		transform: translateY(-1px) !important;
		box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35) !important;
	}

	/* Keep Canvas Label and Asterisk (*) on the exact same line */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .field-label {
		display: inline-block !important;
		width: auto !important;
		max-width: calc(100% - 30px) !important;
		margin-right: 4px !important;
		vertical-align: middle !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .required-asterisk,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .formbuilder-required {
		color: #ef4444 !important;
		font-weight: 700 !important;
		font-size: 15px !important;
		line-height: 1 !important;
		vertical-align: middle !important;
		margin-left: 2px !important;
	}
	/* Only display required asterisk when field is actually configured as required */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .required-asterisk {
		display: none;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .required-asterisk[style*="display: inline"],
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .required-asterisk[style*="display:inline"],
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .required-asterisk[style*="display: block"],
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .required-asterisk[style*="display:block"] {
		display: inline-block !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .required-asterisk[style*="display: none"],
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .required-asterisk[style*="display:none"] {
		display: none !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field .formbuilder-required {
		display: inline-block !important;
	}

	/* Display and action fields on canvas: hide redundant top label, required asterisk, and tooltip icon */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field > .field-label,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field > .field-label,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field > .field-label,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.divider-field > .field-label,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.step-break-field > .field-label,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field .required-asterisk,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field .required-asterisk,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field .required-asterisk,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.divider-field .required-asterisk,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.step-break-field .required-asterisk,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field .formbuilder-required,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field .formbuilder-required,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field .formbuilder-required,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.divider-field .formbuilder-required,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.step-break-field .formbuilder-required,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.divider-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.step-break-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field .tooltip,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field .tooltip,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field .tooltip,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field [tooltip],
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field [tooltip],
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field [tooltip],
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field .form-elements .description-wrap,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field .form-elements .description-wrap,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field .form-elements .description-wrap,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field .form-elements .required-wrap,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field .form-elements .required-wrap,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field .form-elements .required-wrap {
		display: none !important;
	}

	/* Keep Radio circle & Checkbox square aligned horizontally with text on the same line */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder .formbuilder-radio,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder .formbuilder-checkbox {
		display: flex !important;
		align-items: center !important;
		gap: 8px !important;
		margin-bottom: 8px !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder .formbuilder-radio input[type="radio"],
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder .formbuilder-checkbox input[type="checkbox"] {
		margin: 0 !important;
		flex-shrink: 0 !important;
		width: 16px !important;
		height: 16px !important;
		cursor: pointer !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder .formbuilder-radio label,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder .formbuilder-checkbox label {
		display: inline-block !important;
		width: auto !important;
		margin: 0 !important;
		padding: 0 !important;
		cursor: pointer !important;
		font-weight: 400 !important;
		color: var(--wlgf-text-main, #0f172a) !important;
		line-height: 1.4 !important;
	}

	/* Prevent column switcher bar from rendering on canvas */
	.wlgf-field-col-bar {
		display: none !important;
	}

	/* Ensure all form fields on the builder canvas take 100% full width (overriding WP core .form-field select max-width: 95%) */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field select,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder select,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder .form-control,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb select,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb textarea,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]) {
		width: 100% !important;
		max-width: 100% !important;
		min-width: 0 !important;
		box-sizing: border-box !important;
	}

	/* Prevent select option text from clipping / vertically cutting off in builder preview */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field select,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .prev-holder select,
	.wlgf-admin-wrap .wlgf-builder-canvas select,
	.wlgf-admin-wrap .form-wrap.form-builder select {
		height: 38px !important;
		min-height: 38px !important;
		max-height: 38px !important;
		line-height: normal !important;
		padding: 0 34px 0 12px !important;
		vertical-align: middle !important;
	}

	/* Field Drawer Container Padding & Spacing */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements {
		background: var(--wlgf-surface-subtle, #f8fafc) !important;
		border-top: 1px solid var(--wlgf-border, #e2e8f0) !important;
		margin: 16px -20px -16px -20px !important;
		padding: 20px 22px 26px 22px !important;
		border-radius: 0 0 var(--wlgf-radius-lg, 12px) var(--wlgf-radius-lg, 12px) !important;
		position: relative !important;
	}

	/* Modern Pill Button Styles for Field Style Group & Precise Left Alignment */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap {
		display: flex !important;
		align-items: center !important;
		clear: both !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap label {
		float: none !important;
		width: 16.6667% !important;
		min-width: 93.3px !important;
		max-width: 93.3px !important;
		text-align: right !important;
		margin: 0 !important;
		padding: 0 !important;
		box-sizing: border-box !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group {
		flex: 1 !important;
		margin: 0 0 0 11.2px !important;
		display: flex !important;
		align-items: center !important;
		gap: 6px !important;
		flex-wrap: wrap !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn {
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		border-radius: 20px !important;
		padding: 4px 11px !important;
		font-size: 11.5px !important;
		font-weight: 600 !important;
		cursor: pointer !important;
		transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
		line-height: 1.4 !important;
		outline: none !important;
		margin: 0 !important;
		border: 1px solid transparent !important;
		text-shadow: none !important;
		box-shadow: none !important;
		height: auto !important;
		min-height: 26px !important;
	}

	/* Style Pill: Default */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-default {
		background: #f1f5f9 !important;
		color: #475569 !important;
		border-color: #cbd5e1 !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-default:hover,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-default.selected {
		background: #334155 !important;
		color: #ffffff !important;
		border-color: #334155 !important;
		box-shadow: 0 2px 6px rgba(51, 65, 85, 0.25) !important;
	}

	/* Style Pill: Danger */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-danger {
		background: #fef2f2 !important;
		color: #dc2626 !important;
		border-color: #fecaca !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-danger:hover,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-danger.selected {
		background: #dc2626 !important;
		color: #ffffff !important;
		border-color: #dc2626 !important;
		box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25) !important;
	}

	/* Style Pill: Info */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-info {
		background: #f0f9ff !important;
		color: #0284c7 !important;
		border-color: #bae6fd !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-info:hover,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-info.selected {
		background: #0284c7 !important;
		color: #ffffff !important;
		border-color: #0284c7 !important;
		box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25) !important;
	}

	/* Style Pill: Primary */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-primary {
		background: #eff6ff !important;
		color: #2563eb !important;
		border-color: #bfdbfe !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-primary:hover,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-primary.selected {
		background: #2563eb !important;
		color: #ffffff !important;
		border-color: #2563eb !important;
		box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3) !important;
	}

	/* Style Pill: Success */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-success {
		background: #f0fdf4 !important;
		color: #16a34a !important;
		border-color: #bbf7d0 !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-success:hover,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-success.selected {
		background: #16a34a !important;
		color: #ffffff !important;
		border-color: #16a34a !important;
		box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25) !important;
	}

	/* Style Pill: Warning */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-warning {
		background: #fffbeb !important;
		color: #d97706 !important;
		border-color: #fde68a !important;
	}
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-warning:hover,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.btn-warning.selected {
		background: #d97706 !important;
		color: #ffffff !important;
		border-color: #d97706 !important;
		box-shadow: 0 2px 6px rgba(217, 119, 6, 0.25) !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements .style-wrap .btn-group .btn.selected {
		transform: translateY(-1px) !important;
	}

	/* Modern Floating & Centered Close Button with proper breathing spacing */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements a.close-field {
		position: relative !important;
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		left: 50% !important;
		transform: translateX(-50%) !important;
		bottom: auto !important;
		margin-top: 18px !important;
		margin-bottom: 2px !important;
		padding: 6px 20px !important;
		background: #ffffff !important;
		color: #475569 !important;
		font-size: 12px !important;
		font-weight: 600 !important;
		border: 1px solid #cbd5e1 !important;
		border-radius: 6px !important;
		text-decoration: none !important;
		cursor: pointer !important;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
		transition: all 0.15s ease !important;
		line-height: 1.4 !important;
		height: auto !important;
		width: auto !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder .frmb .form-elements a.close-field:hover {
		background: #f1f5f9 !important;
		color: #0f172a !important;
		border-color: #94a3b8 !important;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
		transform: translateX(-50%) translateY(-1px) !important;
	}

	/* Ultra-Modern Flatpickr Calendar Theme (Replaces Retro OS Datepicker) */
	.flatpickr-calendar {
		background: #ffffff !important;
		border-radius: 14px !important;
		box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.06) !important;
		border: none !important;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
		padding: 12px 14px !important;
		width: 336px !important;
		box-sizing: border-box !important;
		z-index: 999999 !important;
		animation: wlgfFadeDown 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
	}
	.flatpickr-calendar.arrowTop:before, .flatpickr-calendar.arrowTop:after,
	.flatpickr-calendar.arrowBottom:before, .flatpickr-calendar.arrowBottom:after {
		border: none !important;
	}
	.flatpickr-months {
		display: flex !important;
		align-items: center !important;
		justify-content: space-between !important;
		position: relative !important;
		padding-bottom: 8px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		margin-bottom: 8px !important;
	}
	.flatpickr-months .flatpickr-month {
		height: 36px !important;
		min-height: 36px !important;
		overflow: visible !important;
		position: relative !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		flex: 1 1 auto !important;
		color: #0f172a !important;
	}
	.flatpickr-current-month {
		position: static !important;
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		gap: 6px !important;
		width: auto !important;
		height: 36px !important;
		line-height: 36px !important;
		padding: 0 !important;
		font-size: 14.5px !important;
		font-weight: 700 !important;
		color: #0f172a !important;
		transform: none !important;
		left: auto !important;
	}
	.flatpickr-current-month .flatpickr-monthDropdown-months {
		display: inline-block !important;
		width: auto !important;
		max-width: none !important;
		height: 28px !important;
		line-height: 28px !important;
		padding: 0 6px 0 4px !important;
		margin: 0 !important;
		font-size: 14.5px !important;
		font-weight: 700 !important;
		color: #0f172a !important;
		border: none !important;
		background: transparent !important;
		box-shadow: none !important;
		outline: none !important;
		cursor: pointer !important;
		vertical-align: middle !important;
		border-radius: 6px !important;
		transition: background-color 0.15s ease !important;
	}
	.flatpickr-current-month .flatpickr-monthDropdown-months:hover {
		background-color: #f1f5f9 !important;
	}
	.flatpickr-current-month .numInputWrapper {
		display: inline-flex !important;
		align-items: center !important;
		width: 64px !important;
		height: 28px !important;
		line-height: 28px !important;
		font-weight: 700 !important;
		vertical-align: middle !important;
		margin: 0 !important;
		padding: 0 !important;
		border-radius: 6px !important;
		transition: background-color 0.15s ease !important;
	}
	.flatpickr-current-month .numInputWrapper:hover {
		background-color: #f1f5f9 !important;
	}
	.flatpickr-current-month input.cur-year {
		height: 28px !important;
		line-height: 28px !important;
		padding: 0 4px !important;
		margin: 0 !important;
		font-size: 14.5px !important;
		font-weight: 700 !important;
		color: #0f172a !important;
		border: none !important;
		background: transparent !important;
		box-shadow: none !important;
		outline: none !important;
		display: inline-block !important;
		width: 100% !important;
		vertical-align: middle !important;
		box-sizing: border-box !important;
	}
	.flatpickr-months .flatpickr-prev-month,
	.flatpickr-months .flatpickr-next-month {
		padding: 6px !important;
		border-radius: 8px !important;
		color: #64748b !important;
		transition: all 0.15s ease !important;
		height: 32px !important;
		width: 32px !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
	}
	.flatpickr-months .flatpickr-prev-month:hover,
	.flatpickr-months .flatpickr-next-month:hover {
		background: #f1f5f9 !important;
		color: #0f172a !important;
	}
	.flatpickr-rContainer,
	.flatpickr-days,
	.dayContainer {
		width: 308px !important;
		max-width: 308px !important;
		min-width: 308px !important;
		margin: 0 auto !important;
		box-sizing: border-box !important;
	}
	.flatpickr-weekdays {
		width: 308px !important;
		margin: 0 auto !important;
	}
	.flatpickr-weekdaycontainer {
		width: 100% !important;
		display: flex !important;
		justify-content: space-between !important;
	}
	span.flatpickr-weekday {
		color: #94a3b8 !important;
		font-size: 11px !important;
		font-weight: 700 !important;
		text-transform: uppercase !important;
		letter-spacing: 0.5px !important;
		white-space: nowrap !important;
		display: inline-block !important;
		width: 14.28% !important;
		text-align: center !important;
		overflow: visible !important;
	}
	.flatpickr-day {
		border-radius: 8px !important;
		font-size: 13px !important;
		font-weight: 500 !important;
		color: #1e293b !important;
		height: 36px !important;
		line-height: 36px !important;
		max-width: 36px !important;
		margin: 2px !important;
		transition: all 0.15s ease !important;
		border: 1px solid transparent !important;
		box-sizing: border-box !important;
	}
	.flatpickr-day:hover {
		background: #f1f5f9 !important;
		color: #0f172a !important;
		border-color: #e2e8f0 !important;
	}
	.flatpickr-day.today {
		border-color: #6366f1 !important;
		color: #4f46e5 !important;
		font-weight: 700 !important;
	}
	.flatpickr-day.selected,
	.flatpickr-day.selected:hover {
		background: #4f46e5 !important;
		color: #ffffff !important;
		border-color: #4f46e5 !important;
		font-weight: 700 !important;
		box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35) !important;
	}
	.flatpickr-day.prevMonthDay,
	.flatpickr-day.nextMonthDay {
		color: #cbd5e1 !important;
	}
	.flatpickr-day.flatpickr-disabled,
	.flatpickr-day.flatpickr-disabled:hover {
		color: #cbd5e1 !important;
		background: transparent !important;
		border-color: transparent !important;
		cursor: not-allowed !important;
		opacity: 0.4 !important;
	}
	.flatpickr-months .flatpickr-prev-month.flatpickr-disabled,
	.flatpickr-months .flatpickr-next-month.flatpickr-disabled {
		opacity: 0.25 !important;
		cursor: not-allowed !important;
		pointer-events: none !important;
	}

	/* Canvas Tooltip Un-clipping & Modern Tooltip Design */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field {
		overflow: visible !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 17px;
		height: 17px;
		line-height: 1;
		border-radius: 50%;
		background: #f1f5f9;
		color: #64748b;
		border: 1px solid #cbd5e1;
		font-size: 11px;
		font-weight: 700;
		margin-left: 6px;
		vertical-align: middle;
		cursor: help;
		transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
		box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
		position: relative;
	}

	/* Completely hide tooltip icon when Help Text (description) is empty or hidden */
	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element:not([tooltip]),
	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element[tooltip=""],
	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element[tooltip=" "],
	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element[tooltip="null"],
	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element[tooltip="undefined"],
	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element.wlgf-d-none,
	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element[style*="display: none"],
	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element[style*="display:none"] {
		display: none !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder .tooltip-element:hover {
		background: #4f46e5 !important;
		color: #ffffff !important;
		border-color: #4f46e5 !important;
		box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3) !important;
		transform: scale(1.1) !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder *[tooltip]:hover::after {
		background: #0f172a !important;
		color: #f8fafc !important;
		border-radius: 8px !important;
		padding: 8px 12px !important;
		font-size: 12px !important;
		line-height: 1.45 !important;
		font-weight: 500 !important;
		width: max-content !important;
		max-width: 260px !important;
		box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1) !important;
		bottom: calc(100% + 8px) !important;
		left: 50% !important;
		transform: translateX(-50%) !important;
		z-index: 999999 !important;
		letter-spacing: normal !important;
		text-align: left !important;
		white-space: normal !important;
		word-break: break-word !important;
		pointer-events: none !important;
	}

	.wlgf-admin-wrap .form-wrap.form-builder *[tooltip]:hover::before {
		border-color: #0f172a transparent transparent transparent !important;
		border-style: solid !important;
		border-width: 6px 6px 0 6px !important;
		bottom: calc(100% + 2px) !important;
		left: 50% !important;
		transform: translateX(-50%) !important;
		z-index: 999999 !important;
		pointer-events: none !important;
	}

	/* Canvas Display & Action Fields Tooltip Override (Ensures no stray tooltips on header, paragraph, button, divider) */
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.button-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.header-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.paragraph-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.divider-field .tooltip-element,
	.wlgf-admin-wrap .form-wrap.form-builder .frmb li.form-field.step-break-field .tooltip-element {
		display: none !important;
	}

	/* Live Form Preview Modal Grid Columns & Elements */
	.wlgf-form-preview-grid .wlgf-col-12,
	.wlgf-preview-body .wlgf-col-12 {
		flex: 0 0 100% !important;
		max-width: 100% !important;
		width: 100% !important;
		box-sizing: border-box !important;
	}
	.wlgf-form-preview-grid .wlgf-col-6,
	.wlgf-preview-body .wlgf-col-6 {
		flex: 0 0 50% !important;
		max-width: 50% !important;
		width: 50% !important;
		box-sizing: border-box !important;
	}
	.wlgf-form-preview-grid .wlgf-col-4,
	.wlgf-preview-body .wlgf-col-4 {
		flex: 0 0 33.333333% !important;
		max-width: 33.333333% !important;
		width: 33.333333% !important;
		box-sizing: border-box !important;
	}
	.wlgf-form-preview-grid .wlgf-col-3,
	.wlgf-preview-body .wlgf-col-3 {
		flex: 0 0 25% !important;
		max-width: 25% !important;
		width: 25% !important;
		box-sizing: border-box !important;
	}

	@media (max-width: 640px) {
		.wlgf-form-preview-grid .wlgf-col-6,
		.wlgf-form-preview-grid .wlgf-col-4,
		.wlgf-form-preview-grid .wlgf-col-3,
		.wlgf-preview-body .wlgf-col-6,
		.wlgf-preview-body .wlgf-col-4,
		.wlgf-preview-body .wlgf-col-3 {
			flex: 0 0 100% !important;
			max-width: 100% !important;
			width: 100% !important;
		}
	}

	/* Preview Modal Tooltips (Identical to Frontend) */
	.wlgf-modal--preview .wlgf-tooltip-icon,
	.wlgf-preview-field .wlgf-tooltip-icon {
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		width: 17px !important;
		height: 17px !important;
		line-height: 1 !important;
		border-radius: 50% !important;
		background: #f1f5f9 !important;
		color: #64748b !important;
		border: 1px solid #cbd5e1 !important;
		font-size: 11px !important;
		font-weight: 700 !important;
		margin-left: 6px !important;
		vertical-align: middle !important;
		cursor: help !important;
		position: relative !important;
		transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
		box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
		text-decoration: none !important;
	}
	.wlgf-modal--preview .wlgf-tooltip-icon:hover,
	.wlgf-preview-field .wlgf-tooltip-icon:hover {
		background: #4f46e5 !important;
		color: #ffffff !important;
		border-color: #4f46e5 !important;
		box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3) !important;
		transform: scale(1.1) !important;
	}
	.wlgf-modal--preview .wlgf-tooltip-icon:hover::after,
	.wlgf-preview-field .wlgf-tooltip-icon:hover::after,
	.wlgf-modal--preview [data-wlgf-tooltip]:hover::after {
		content: attr(data-wlgf-tooltip) !important;
		position: absolute !important;
		bottom: calc(100% + 8px) !important;
		left: 50% !important;
		transform: translateX(-50%) !important;
		background: #0f172a !important;
		color: #f8fafc !important;
		font-size: 12px !important;
		font-weight: 500 !important;
		line-height: 1.45 !important;
		padding: 8px 12px !important;
		border-radius: 8px !important;
		width: max-content !important;
		max-width: 260px !important;
		white-space: normal !important;
		word-break: break-word !important;
		text-align: left !important;
		box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1) !important;
		z-index: 9999999 !important;
		pointer-events: none !important;
	}
	.wlgf-modal--preview .wlgf-tooltip-icon:hover::before,
	.wlgf-preview-field .wlgf-tooltip-icon:hover::before,
	.wlgf-modal--preview [data-wlgf-tooltip]:hover::before {
		content: "" !important;
		position: absolute !important;
		bottom: calc(100% + 2px) !important;
		left: 50% !important;
		transform: translateX(-50%) !important;
		border-color: #0f172a transparent transparent transparent !important;
		border-style: solid !important;
		border-width: 6px 6px 0 6px !important;
		z-index: 9999999 !important;
		pointer-events: none !important;
	}

	/* Dialog Modal Overrides for Form Generator */
	.wlgf-dialog-overlay {
		z-index: 1000005 !important;
		background: rgba(15, 23, 42, 0.65) !important;
		backdrop-filter: blur(4px) !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		padding: 20px !important;
	}
	.wlgf-dialog-modal {
		background: #ffffff !important;
		border-radius: 14px !important;
		box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
		max-width: 480px !important;
		width: 100% !important;
		overflow: hidden !important;
		border: 1px solid rgba(226, 232, 240, 0.9) !important;
		transform: scale(0.92) translateY(10px) !important;
		opacity: 0 !important;
		transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
	}
	.wlgf-modal-overlay--active .wlgf-dialog-modal {
		transform: scale(1) translateY(0) !important;
		opacity: 1 !important;
	}
	.wlgf-dialog-header {
		display: flex !important;
		align-items: center !important;
		gap: 14px !important;
		padding: 20px 22px 14px !important;
		border-bottom: 1px solid #f1f5f9 !important;
		position: relative !important;
	}
	.wlgf-dialog-header__text {
		flex: 1 !important;
		min-width: 0 !important;
	}
	.wlgf-dialog__title {
		font-size: 17px !important;
		font-weight: 700 !important;
		color: #0f172a !important;
		margin: 0 !important;
		line-height: 1.3 !important;
	}
	.wlgf-dialog__icon {
		width: 44px !important;
		height: 44px !important;
		border-radius: 12px !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		font-size: 20px !important;
		flex-shrink: 0 !important;
	}
	.wlgf-dialog__icon--warning {
		background: #fef3c7 !important;
		color: #d97706 !important;
		border: 1px solid #fde68a !important;
	}
	.wlgf-dialog__icon--danger {
		background: #fee2e2 !important;
		color: #dc2626 !important;
		border: 1px solid #fecaca !important;
	}
	.wlgf-dialog__icon--success {
		background: #dcfce7 !important;
		color: #16a34a !important;
		border: 1px solid #bbf7d0 !important;
	}
	.wlgf-dialog__icon--info {
		background: #ede9fe !important;
		color: #6366f1 !important;
		border: 1px solid #ddd6fe !important;
	}
	.wlgf-dialog-close-btn {
		background: none !important;
		border: none !important;
		font-size: 24px !important;
		color: #94a3b8 !important;
		cursor: pointer !important;
		padding: 2px 8px !important;
		line-height: 1 !important;
		border-radius: 6px !important;
		transition: all 0.15s ease !important;
	}
	.wlgf-dialog-close-btn:hover {
		color: #0f172a !important;
		background: #f1f5f9 !important;
	}
	.wlgf-dialog__body {
		padding: 16px 22px 20px !important;
		font-size: 14px !important;
		color: #475569 !important;
		line-height: 1.6 !important;
	}
	.wlgf-dialog__body strong {
		color: #0f172a !important;
	}
	.wlgf-dialog__footer {
		padding: 14px 22px !important;
		background: #f8fafc !important;
		border-top: 1px solid #f1f5f9 !important;
		display: flex !important;
		justify-content: flex-end !important;
		align-items: center !important;
		gap: 10px !important;
	}
	</style>
	<!-- Page Header -->
	<header class="wlgf-header wlgf-header--sticky">
		<div class="wlgf-header__main">
			<div class="wlgf-header__icon">
				<i class="fa-solid fa-wand-magic-sparkles"></i>
			</div>
			<div class="wlgf-header__titles">
				<div class="wlgf-header__title-row">
					<h1 class="wlgf-header__title">
						<?php
						if ( 'set' === $wlgf_FormIdSet ) {
							/* translators: %s: form name */
							printf( esc_html__( 'Edit Form: %s', 'lead-generation-form' ), esc_html( $wlgf_form_name ) );
						} else {
							esc_html_e( 'Form Builder', 'lead-generation-form' );
						}
						?>
					</h1>
					<?php if ( 'set' === $wlgf_FormIdSet ) : ?>
						<span class="wlgf-badge wlgf-badge--primary">#<?php echo esc_html( $wlgf_form_id ); ?></span>
					<?php else : ?>
						<span class="wlgf-badge wlgf-badge--success"><?php esc_html_e( 'New Form', 'lead-generation-form' ); ?></span>
					<?php endif; ?>
				</div>
				<p class="wlgf-header__subtitle"><?php esc_html_e( 'Drag and drop fields onto the canvas to customize your form structure and validation rules.', 'lead-generation-form' ); ?></p>
			</div>
		</div>
		<div class="wlgf-header__actions">
			<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-free-vs-pro' ) ); ?>" class="wlgf-btn wlgf-btn--secondary" style="background: #fffbeb; border-color: #fde68a; color: #92400e; font-weight: 700; text-decoration: none;">
					<i class="fa-solid fa-crown" style="color: #f59e0b;"></i> <?php esc_html_e( 'Free vs Pro', 'lead-generation-form' ); ?>
				</a>
			<?php endif; ?>
			<button type="button" id="wlgf-open-preview-btn" class="wlgf-btn wlgf-btn--secondary">
				<i class="fa-solid fa-eye"></i> <?php esc_html_e( 'Live Preview', 'lead-generation-form' ); ?>
			</button>
			<button type="button" id="wlgf-header-save-btn" class="wlgf-btn wlgf-btn--primary">
				<?php if ( 'set' === $wlgf_FormIdSet ) : ?>
					<i class="fa-solid fa-file-pen"></i> <?php esc_html_e( 'Update Form', 'lead-generation-form' ); ?>
				<?php else : ?>
					<i class="fa-solid fa-floppy-disk"></i> <?php esc_html_e( 'Save Form', 'lead-generation-form' ); ?>
				<?php endif; ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlgf-manage-forms' ) ); ?>" class="wlgf-btn wlgf-btn--secondary">
				<i class="fa-solid fa-arrow-left"></i> <?php esc_html_e( 'Back to Forms', 'lead-generation-form' ); ?>
			</a>
		</div>
	</header>

	<!-- Global Feedback Notice Area -->
	<div id="wlgf-global-feedback"></div>

	<!-- Main Visual Canvas Card -->
	<div class="wlgf-card">
		<div class="wlgf-card__header">
			<h2 class="wlgf-card__title">
				<i class="fa-solid fa-sliders"></i> <?php esc_html_e( 'Form Configuration & Canvas', 'lead-generation-form' ); ?>
			</h2>
		</div>
		<div class="wlgf-card__body">
			<!-- Template Shortcut Callout Banner -->
			<div class="wlgf-templates-callout-banner">
				<div class="wlgf-templates-callout-banner__left">
					<span class="wlgf-templates-callout-badge"><i class="fa-solid fa-wand-magic-sparkles"></i> <?php esc_html_e( 'Pro Tip', 'lead-generation-form' ); ?></span>
					<span><?php esc_html_e( 'Save time! Start with a pre-built industry template (Lead Gen, Real Estate, Dental, Fitness, Cost Estimator) instead of building from scratch.', 'lead-generation-form' ); ?></span>
				</div>
				<button type="button" id="wlgf-open-templates-btn" class="wlgf-btn wlgf-btn--templates-highlight" title="<?php esc_attr_e( 'Browse 20+ ready-to-use high-converting form templates', 'lead-generation-form' ); ?>">
					<span class="wlgf-btn-sparkle"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
					<strong><?php esc_html_e( 'Template Library', 'lead-generation-form' ); ?></strong>
					<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
						<span class="wlgf-pill-badge"><?php esc_html_e( '5 Starter + Pro', 'lead-generation-form' ); ?></span>
					<?php else : ?>
						<span class="wlgf-pill-badge"><?php esc_html_e( '25+ Ready', 'lead-generation-form' ); ?></span>
					<?php endif; ?>
				</button>
			</div>

			<!-- Form Name Input -->
			<div class="wlgf-form-group">
				<label for="wlgf-form-name" class="wlgf-label">
					<?php esc_html_e( 'Form Name', 'lead-generation-form' ); ?> <span class="wlgf-label__required">*</span>
				</label>
				<input class="wlgf-input" id="wlgf-form-name" name="wlgf-form-name" type="text" value="<?php echo esc_attr( $wlgf_form_name ); ?>" placeholder="<?php esc_attr_e( 'e.g. Contact Us Form / Lead Inquiry', 'lead-generation-form' ); ?>">
				<p class="wlgf-help-text"><?php esc_html_e( 'Give your form a recognizable title to identify it in submissions and shortcodes.', 'lead-generation-form' ); ?></p>
			</div>

			<!-- Visual Builder Mount -->
			<div class="wlgf-builder-canvas">
				<div id="build-wrap"></div>
			</div>

			<!-- Collapsible Form Actions & Auto-Responder Settings -->
			<div class="wlgf-collapsible" id="wlgf-form-settings-accordion">
				<div class="wlgf-collapsible__header">
					<h3 class="wlgf-collapsible__title">
						<i class="fa-solid fa-gear wlgf-text-primary"></i> <?php esc_html_e( 'Submission Actions, Redirect & Submitter Auto-Responder', 'lead-generation-form' ); ?>
					</h3>
					<span class="wlgf-collapsible__chevron"><i class="fa-solid fa-chevron-down"></i></span>
				</div>
				<div class="wlgf-collapsible__body">
					<!-- Confirmation / Redirect Row -->
					<div class="wlgf-grid wlgf-grid--2 wlgf-mb-4">
						<div class="wlgf-form-group">
							<label for="wlgf-confirmation-type" class="wlgf-label"><?php esc_html_e( 'Submission Confirmation Action', 'lead-generation-form' ); ?></label>
							<select id="wlgf-confirmation-type" class="wlgf-select">
								<option value="message" <?php selected( $wlgf_confirmation_type, 'message' ); ?>><?php esc_html_e( 'Display Inline Thank You Message', 'lead-generation-form' ); ?></option>
								<option value="redirect" <?php selected( $wlgf_confirmation_type, 'redirect' ); ?>><?php esc_html_e( 'Redirect to Custom URL (e.g. Booking / Thank You Page)', 'lead-generation-form' ); ?></option>
							</select>
						</div>
						<div class="wlgf-form-group <?php echo ( 'redirect' === $wlgf_confirmation_type ) ? '' : 'wlgf-d-none'; ?>" id="wlgf-redirect-url-wrap">
							<label for="wlgf-redirect-url" class="wlgf-label"><?php esc_html_e( 'Custom Redirect URL', 'lead-generation-form' ); ?></label>
							<input type="url" id="wlgf-redirect-url" class="wlgf-input" value="<?php echo esc_attr( $wlgf_redirect_url ); ?>" placeholder="https://example.com/thank-you">
						</div>
						<div class="wlgf-form-group <?php echo ( 'message' === $wlgf_confirmation_type ) ? '' : 'wlgf-d-none'; ?>" id="wlgf-custom-message-wrap">
							<label for="wlgf-custom-message" class="wlgf-label"><?php esc_html_e( 'Custom Thank You Message', 'lead-generation-form' ); ?></label>
							<input type="text" id="wlgf-custom-message" class="wlgf-input" value="<?php echo esc_attr( $wlgf_custom_message ); ?>" placeholder="<?php esc_attr_e( 'Thank you! Your submission has been received.', 'lead-generation-form' ); ?>">
							<p class="wlgf-help-text"><?php esc_html_e( 'Leave blank to use the default message from Settings. Supports {Name}, {Email}, {Site_Name}, {Form_Name}, {Submission_Date}.', 'lead-generation-form' ); ?></p>
						</div>
					</div>

					<!-- Submitter Auto-Responder Section -->
					<div class="wlgf-toggle-card <?php echo ( 1 === (int) $wlgf_auto_reply_enabled ) ? 'wlgf-toggle-card--active' : ''; ?>">
						<div class="wlgf-toggle-card__header">
							<div class="wlgf-toggle-card__content">
								<h4 class="wlgf-toggle-card__title">
									<i class="fa-solid fa-envelope-circle-check wlgf-text-primary"></i>
									<?php esc_html_e( 'Send Instant Confirmation Email to Submitter (Auto-Responder)', 'lead-generation-form' ); ?>
								</h4>
								<p class="wlgf-toggle-card__desc"><?php esc_html_e( 'Automatically dispatch a personalized confirmation email to the user when they submit an inquiry.', 'lead-generation-form' ); ?></p>
							</div>
							<label class="wlgf-switch" for="wlgf-auto-reply-enabled">
								<input type="checkbox" id="wlgf-auto-reply-enabled" value="1" <?php checked( $wlgf_auto_reply_enabled, 1 ); ?>>
								<span class="wlgf-switch__slider"></span>
							</label>
						</div>

						<div id="wlgf-auto-reply-fields" class="wlgf-toggle-card__body <?php echo ( 1 === (int) $wlgf_auto_reply_enabled ) ? '' : 'wlgf-d-none'; ?>">
							<div class="wlgf-form-group wlgf-mb-3">
								<label for="wlgf-auto-reply-subject" class="wlgf-label"><?php esc_html_e( 'Email Subject Line', 'lead-generation-form' ); ?></label>
								<input type="text" id="wlgf-auto-reply-subject" class="wlgf-input" value="<?php echo esc_attr( $wlgf_auto_reply_subject ); ?>" placeholder="<?php esc_attr_e( 'Thank you for your inquiry! We received your message.', 'lead-generation-form' ); ?>">
							</div>

							<div class="wlgf-form-group">
								<label for="wlgf-auto-reply-body" class="wlgf-label"><?php esc_html_e( 'Email Message Body', 'lead-generation-form' ); ?></label>
								<textarea id="wlgf-auto-reply-body" class="wlgf-textarea" rows="4" placeholder="<?php esc_attr_e( 'Hi {Name}, thank you for reaching out to us...', 'lead-generation-form' ); ?>"><?php echo esc_textarea( $wlgf_auto_reply_body ); ?></textarea>
								<div class="wlgf-tag-pills">
									<span class="wlgf-help-text wlgf-mr-2"><?php esc_html_e( 'Insert dynamic merge tags:', 'lead-generation-form' ); ?></span>
									<button type="button" class="wlgf-tag-pill" data-tag="{Name}" title="<?php esc_attr_e( 'Insert Submitter Name', 'lead-generation-form' ); ?>">{Name}</button>
									<button type="button" class="wlgf-tag-pill" data-tag="{Email}" title="<?php esc_attr_e( 'Insert Submitter Email', 'lead-generation-form' ); ?>">{Email}</button>
									<button type="button" class="wlgf-tag-pill" data-tag="{Site_Name}" title="<?php esc_attr_e( 'Insert Website / Brand Name', 'lead-generation-form' ); ?>">{Site_Name}</button>
									<button type="button" class="wlgf-tag-pill" data-tag="{Form_Name}" title="<?php esc_attr_e( 'Insert Form Name', 'lead-generation-form' ); ?>">{Form_Name}</button>
									<button type="button" class="wlgf-tag-pill" data-tag="{Submission_Date}" title="<?php esc_attr_e( 'Insert Submission Date & Time', 'lead-generation-form' ); ?>">{Submission_Date}</button>
								</div>

								<!-- Merge Tags Information Guide -->
								<div class="wlgf-tag-guide-box">
									<div class="wlgf-tag-guide-title">
										<i class="fa-solid fa-circle-info"></i> <?php esc_html_e( 'Merge Tags Information (Click any tag above to insert):', 'lead-generation-form' ); ?>
									</div>
									<div class="wlgf-tag-guide-grid">
										<div class="wlgf-tag-guide-item">
											<span class="wlgf-tag-badge">{Name}</span>
											<span class="wlgf-tag-desc"><?php esc_html_e( 'Submitter\'s Name (captured from Name field)', 'lead-generation-form' ); ?></span>
										</div>
										<div class="wlgf-tag-guide-item">
											<span class="wlgf-tag-badge">{Email}</span>
											<span class="wlgf-tag-desc"><?php esc_html_e( 'Submitter\'s Email address', 'lead-generation-form' ); ?></span>
										</div>
										<div class="wlgf-tag-guide-item">
											<span class="wlgf-tag-badge">{Site_Name}</span>
											<span class="wlgf-tag-desc"><?php esc_html_e( 'Website / Business Name (e.g. WP Frank)', 'lead-generation-form' ); ?></span>
										</div>
										<div class="wlgf-tag-guide-item">
											<span class="wlgf-tag-badge">{Form_Name}</span>
											<span class="wlgf-tag-desc"><?php esc_html_e( 'Current Form Name (e.g. Classic Contact Form)', 'lead-generation-form' ); ?></span>
										</div>
										<div class="wlgf-tag-guide-item">
											<span class="wlgf-tag-badge">{Submission_Date}</span>
											<span class="wlgf-tag-desc"><?php esc_html_e( 'Form Submission Date & Time', 'lead-generation-form' ); ?></span>
										</div>
									</div>
								</div>

								<div class="wlgf-mt-2" style="font-size: 11.5px; color: #64748b; line-height: 1.5;">
									<i class="fa-solid fa-circle-info" style="color: #2563eb;"></i> <?php esc_html_e( 'Auto-responder emails automatically use your global Brand Logo, colors, and responsive email template configured under Settings → Email Notification Template & Branding.', 'lead-generation-form' ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Visual Theme & Styling Customizer -->
			<div class="wlgf-collapsible wlgf-mb-4" id="wlgf-theme-styler-accordion">
				<div class="wlgf-collapsible__header">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<i class="fa-solid fa-palette wlgf-text-primary"></i>
						<div>
							<strong class="wlgf-text-main"><?php esc_html_e( 'Live Form Theme & Design Styler', 'lead-generation-form' ); ?></strong>
						</div>
					</div>
					<i class="fa-solid fa-chevron-down wlgf-collapsible__icon"></i>
				</div>
				<div class="wlgf-collapsible__body">
					<p class="wlgf-text-xs wlgf-text-muted wlgf-mb-3"><?php esc_html_e( 'Customize the button colors, corner radius, background, and typography for this form when published on your live website.', 'lead-generation-form' ); ?></p>
					<div class="wlgf-grid wlgf-grid--2col wlgf-gap-4">
						<div class="wlgf-form-group">
							<label for="wlgf-theme-primary" class="wlgf-label"><?php esc_html_e( 'Primary Accent Color', 'lead-generation-form' ); ?></label>
							<div class="wlgf-color-picker-wrap">
								<input type="color" id="wlgf-theme-primary-picker" value="<?php echo esc_attr( $wlgf_theme_primary ); ?>">
								<input type="text" id="wlgf-theme-primary" class="wlgf-input" value="<?php echo esc_attr( $wlgf_theme_primary ); ?>" placeholder="#2563eb">
							</div>
							<div class="wlgf-theme-swatches">
								<span class="wlgf-theme-swatch wlgf-theme-swatch--blue" data-color="#2563eb" title="<?php esc_attr_e( 'Royal Blue', 'lead-generation-form' ); ?>"></span>
								<span class="wlgf-theme-swatch wlgf-theme-swatch--teal" data-color="#0d9488" title="<?php esc_attr_e( 'Teal', 'lead-generation-form' ); ?>"></span>
								<span class="wlgf-theme-swatch wlgf-theme-swatch--purple" data-color="#7c3aed" title="<?php esc_attr_e( 'Purple', 'lead-generation-form' ); ?>"></span>
								<span class="wlgf-theme-swatch wlgf-theme-swatch--orange" data-color="#ea580c" title="<?php esc_attr_e( 'Orange', 'lead-generation-form' ); ?>"></span>
								<span class="wlgf-theme-swatch wlgf-theme-swatch--emerald" data-color="#059669" title="<?php esc_attr_e( 'Emerald', 'lead-generation-form' ); ?>"></span>
								<span class="wlgf-theme-swatch wlgf-theme-swatch--rose" data-color="#e11d48" title="<?php esc_attr_e( 'Rose', 'lead-generation-form' ); ?>"></span>
								<span class="wlgf-theme-swatch wlgf-theme-swatch--slate" data-color="#0f172a" title="<?php esc_attr_e( 'Slate Dark', 'lead-generation-form' ); ?>"></span>
							</div>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-radius" class="wlgf-label"><?php esc_html_e( 'Corner Border Radius', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-radius" class="wlgf-select">
								<option value="0px" <?php selected( $wlgf_theme_radius, '0px' ); ?>><?php esc_html_e( 'Square (0px)', 'lead-generation-form' ); ?></option>
								<option value="4px" <?php selected( $wlgf_theme_radius, '4px' ); ?>><?php esc_html_e( 'Subtle Rounded (4px)', 'lead-generation-form' ); ?></option>
								<option value="6px" <?php selected( $wlgf_theme_radius, '6px' ); ?>><?php esc_html_e( 'Default Modern (6px)', 'lead-generation-form' ); ?></option>
								<option value="12px" <?php selected( $wlgf_theme_radius, '12px' ); ?>><?php esc_html_e( 'Soft Rounded (12px)', 'lead-generation-form' ); ?></option>
								<option value="24px" <?php selected( $wlgf_theme_radius, '24px' ); ?>><?php esc_html_e( 'Pill / Ultra Rounded (24px)', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-bg" class="wlgf-label"><?php esc_html_e( 'Form Container Background', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-bg" class="wlgf-select">
								<option value="card" <?php selected( $wlgf_theme_bg, 'card' ); ?>><?php esc_html_e( 'White Card with Subtle Shadow', 'lead-generation-form' ); ?></option>
								<option value="default" <?php selected( $wlgf_theme_bg, 'default' ); ?>><?php esc_html_e( 'Transparent / Theme Default', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-font" class="wlgf-label"><?php esc_html_e( 'Font Family', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-font" class="wlgf-select">
								<option value="inherit" <?php selected( $wlgf_theme_font, 'inherit' ); ?>><?php esc_html_e( 'Inherit from Active WordPress Theme', 'lead-generation-form' ); ?></option>
								<option value="Inter, sans-serif" <?php selected( $wlgf_theme_font, 'Inter, sans-serif' ); ?>>Inter</option>
								<option value="Roboto, sans-serif" <?php selected( $wlgf_theme_font, 'Roboto, sans-serif' ); ?>>Roboto</option>
								<option value="'Open Sans', sans-serif" <?php selected( $wlgf_theme_font, "'Open Sans', sans-serif" ); ?>>Open Sans</option>
								<option value="Outfit, sans-serif" <?php selected( $wlgf_theme_font, 'Outfit, sans-serif' ); ?>>Outfit</option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-btn-width" class="wlgf-label"><?php esc_html_e( 'Button Width & Alignment', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-btn-width" class="wlgf-select">
								<option value="auto" <?php selected( $wlgf_theme_btn_width, 'auto' ); ?>><?php esc_html_e( 'Auto / Inline (Default)', 'lead-generation-form' ); ?></option>
								<option value="full" <?php selected( $wlgf_theme_btn_width, 'full' ); ?>><?php esc_html_e( 'Full Width (100% Block)', 'lead-generation-form' ); ?></option>
								<option value="center" <?php selected( $wlgf_theme_btn_width, 'center' ); ?>><?php esc_html_e( 'Center Aligned', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-field-size" class="wlgf-label"><?php esc_html_e( 'Field Size & Density', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-field-size" class="wlgf-select">
								<option value="default" <?php selected( $wlgf_theme_field_size, 'default' ); ?>><?php esc_html_e( 'Default Modern (44px)', 'lead-generation-form' ); ?></option>
								<option value="compact" <?php selected( $wlgf_theme_field_size, 'compact' ); ?>><?php esc_html_e( 'Compact (36px)', 'lead-generation-form' ); ?></option>
								<option value="large" <?php selected( $wlgf_theme_field_size, 'large' ); ?>><?php esc_html_e( 'Comfortable / Large (52px)', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-mode" class="wlgf-label"><?php esc_html_e( 'Color Scheme / Dark Mode', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-mode" class="wlgf-select">
								<option value="light" <?php selected( $wlgf_theme_mode, 'light' ); ?>><?php esc_html_e( 'Clean Light (Default)', 'lead-generation-form' ); ?></option>
								<option value="dark" <?php selected( $wlgf_theme_mode, 'dark' ); ?>><?php esc_html_e( 'Sleek Dark Mode', 'lead-generation-form' ); ?></option>
								<option value="auto" <?php selected( $wlgf_theme_mode, 'auto' ); ?>><?php esc_html_e( 'Auto (System Preference)', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-input-style" class="wlgf-label"><?php esc_html_e( 'Input Field Style', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-input-style" class="wlgf-select">
								<option value="bordered" <?php selected( $wlgf_theme_input_style, 'bordered' ); ?>><?php esc_html_e( 'Classic Bordered (Default)', 'lead-generation-form' ); ?></option>
								<option value="filled" <?php selected( $wlgf_theme_input_style, 'filled' ); ?>><?php esc_html_e( 'Soft Tint / Filled', 'lead-generation-form' ); ?></option>
								<option value="underline" <?php selected( $wlgf_theme_input_style, 'underline' ); ?>><?php esc_html_e( 'Underline / Material', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-max-width" class="wlgf-label"><?php esc_html_e( 'Form Maximum Width', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-max-width" class="wlgf-select">
								<option value="640px" <?php selected( $wlgf_theme_max_width, '640px' ); ?>><?php esc_html_e( 'Standard Form (640px) (Default)', 'lead-generation-form' ); ?></option>
								<option value="480px" <?php selected( $wlgf_theme_max_width, '480px' ); ?>><?php esc_html_e( 'Compact Card (480px)', 'lead-generation-form' ); ?></option>
								<option value="800px" <?php selected( $wlgf_theme_max_width, '800px' ); ?>><?php esc_html_e( 'Wide Hero (800px)', 'lead-generation-form' ); ?></option>
								<option value="100%" <?php selected( $wlgf_theme_max_width, '100%' ); ?>><?php esc_html_e( '100% Full Width', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-btn-shadow" class="wlgf-label"><?php esc_html_e( 'Button Shadow & Hover Effect', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-btn-shadow" class="wlgf-select">
								<option value="default" <?php selected( $wlgf_theme_btn_shadow, 'default' ); ?>><?php esc_html_e( 'Subtle Elevation (Default)', 'lead-generation-form' ); ?></option>
								<option value="glow" <?php selected( $wlgf_theme_btn_shadow, 'glow' ); ?>><?php esc_html_e( 'Accent Glow (Matches Accent Color)', 'lead-generation-form' ); ?></option>
								<option value="flat" <?php selected( $wlgf_theme_btn_shadow, 'flat' ); ?>><?php esc_html_e( 'Flat / Minimalist (No Shadow)', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-theme-label-style" class="wlgf-label"><?php esc_html_e( 'Label Typography Style', 'lead-generation-form' ); ?></label>
							<select id="wlgf-theme-label-style" class="wlgf-select">
								<option value="default" <?php selected( $wlgf_theme_label_style, 'default' ); ?>><?php esc_html_e( 'Standard (Default)', 'lead-generation-form' ); ?></option>
								<option value="bold" <?php selected( $wlgf_theme_label_style, 'bold' ); ?>><?php esc_html_e( 'Bold & Semibold', 'lead-generation-form' ); ?></option>
								<option value="uppercase" <?php selected( $wlgf_theme_label_style, 'uppercase' ); ?>><?php esc_html_e( 'Uppercase Micro-Text', 'lead-generation-form' ); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<!-- Webhooks & Third-Party Integrations -->
			<div class="wlgf-collapsible wlgf-mb-4" id="wlgf-webhook-accordion">
				<div class="wlgf-collapsible__header">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<i class="fa-solid fa-bolt wlgf-text-primary"></i>
						<strong class="wlgf-text-main"><?php esc_html_e( 'Webhooks & Automation (Zapier, Make, Pabbly)', 'lead-generation-form' ); ?></strong>
					</div>
					<i class="fa-solid fa-chevron-down wlgf-collapsible__icon"></i>
				</div>
				<div class="wlgf-collapsible__body">
					<p class="wlgf-help-text wlgf-mb-3"><?php esc_html_e( 'Stream submitted lead data asynchronously to external webhook endpoints, Zapier, Make, or CRM systems upon submission.', 'lead-generation-form' ); ?></p>
					<div class="wlgf-grid wlgf-grid--2col wlgf-gap-4">
						<div class="wlgf-form-group">
							<label for="wlgf-webhook-url" class="wlgf-label"><?php esc_html_e( 'Webhook Endpoint URL', 'lead-generation-form' ); ?></label>
							<input type="url" id="wlgf-webhook-url" class="wlgf-input" value="<?php echo esc_url( $wlgf_webhook_url ); ?>" placeholder="https://hooks.zapier.com/hooks/catch/...">
						</div>
						<div class="wlgf-form-group">
							<label for="wlgf-webhook-auth" class="wlgf-label"><?php esc_html_e( 'Authorization Header / Secret Key (Optional)', 'lead-generation-form' ); ?></label>
							<input type="password" id="wlgf-webhook-auth" class="wlgf-input" value="<?php echo esc_attr( $wlgf_webhook_auth ); ?>" placeholder="Bearer token or custom key">
						</div>
					</div>
				</div>
			</div>

			<!-- Form Scheduling & Submission Quotas -->
			<div class="wlgf-collapsible wlgf-mb-4" id="wlgf-scheduling-accordion">
				<div class="wlgf-collapsible__header">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<i class="fa-solid fa-clock wlgf-text-primary"></i>
						<strong class="wlgf-text-main"><?php esc_html_e( 'Form Scheduling & Submission Quotas', 'lead-generation-form' ); ?></strong>
					</div>
					<i class="fa-solid fa-chevron-down wlgf-collapsible__icon"></i>
				</div>
				<div class="wlgf-collapsible__body">
					<div class="wlgf-grid wlgf-grid--2col wlgf-gap-4">
						<div class="wlgf-form-group">
							<label for="wlgf-schedule-start" class="wlgf-label"><?php esc_html_e( 'Open Form Date (Start)', 'lead-generation-form' ); ?></label>
							<input type="datetime-local" id="wlgf-schedule-start" class="wlgf-input" value="<?php echo esc_attr( $wlgf_schedule_start ); ?>">
						</div>
						<div class="wlgf-form-group">
							<label for="wlgf-schedule-end" class="wlgf-label"><?php esc_html_e( 'Close Form Date (Expiration)', 'lead-generation-form' ); ?></label>
							<input type="datetime-local" id="wlgf-schedule-end" class="wlgf-input" value="<?php echo esc_attr( $wlgf_schedule_end ); ?>">
						</div>
						<div class="wlgf-form-group">
							<label for="wlgf-max-submissions" class="wlgf-label"><?php esc_html_e( 'Maximum Submission Quota (0 = Unlimited)', 'lead-generation-form' ); ?></label>
							<input type="number" id="wlgf-max-submissions" class="wlgf-input" min="0" value="<?php echo esc_attr( $wlgf_max_submissions ); ?>" placeholder="0">
						</div>
						<div class="wlgf-form-group">
							<label for="wlgf-closed-message" class="wlgf-label"><?php esc_html_e( 'Custom Form Closed / Inactive Message', 'lead-generation-form' ); ?></label>
							<input type="text" id="wlgf-closed-message" class="wlgf-input" value="<?php echo esc_attr( $wlgf_closed_message ); ?>" placeholder="<?php esc_attr_e( 'This form is currently closed for new submissions.', 'lead-generation-form' ); ?>">
						</div>
					</div>
				</div>
			</div>

			<!-- Collapsible Newsletter & Audience Sync (PRO-008) -->
			<div class="wlgf-collapsible" id="wlgf-form-newsletter-accordion">
				<div class="wlgf-collapsible__header">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<i class="fa-solid fa-paper-plane wlgf-text-primary"></i>
						<strong class="wlgf-text-main"><?php esc_html_e( 'Newsletter & Audience Sync (Mailchimp / ESP)', 'lead-generation-form' ); ?></strong>
					</div>
					<i class="fa-solid fa-chevron-down wlgf-collapsible__icon"></i>
				</div>
				<div class="wlgf-collapsible__body">
					<div class="wlgf-form-group">
						<label class="wlgf-checkbox-label">
							<input type="checkbox" id="wlgf-newsletter-enabled" value="1" <?php checked( $wlgf_newsletter_enabled, 1 ); ?>>
							<span class="wlgf-checkbox-text"><strong><?php esc_html_e( 'Enable Newsletter & Audience Sync for this form', 'lead-generation-form' ); ?></strong></span>
						</label>
					</div>

					<div id="wlgf-newsletter-settings-drawer" class="<?php echo empty( $wlgf_newsletter_enabled ) ? 'd-none' : ''; ?>">
						<div class="wlgf-grid wlgf-grid--2col wlgf-gap-4 wlgf-mt-3">
							<div class="wlgf-form-group">
								<label for="wlgf-newsletter-mode" class="wlgf-label"><?php esc_html_e( 'Opt-In Mode', 'lead-generation-form' ); ?></label>
								<select id="wlgf-newsletter-mode" class="wlgf-select wlgf-w-full">
									<option value="explicit" <?php selected( $wlgf_newsletter_mode, 'explicit' ); ?>><?php esc_html_e( 'Explicit (Add Opt-in Checkbox to form - Recommended for GDPR)', 'lead-generation-form' ); ?></option>
									<option value="automatic" <?php selected( $wlgf_newsletter_mode, 'automatic' ); ?>><?php esc_html_e( 'Automatic (Silently subscribe all valid submitters)', 'lead-generation-form' ); ?></option>
								</select>
								<p id="wlgf-gdpr-notice" class="wlgf-help-text wlgf-text-warning <?php echo ( 'automatic' === $wlgf_newsletter_mode ) ? '' : 'd-none'; ?>">
									<i class="fa-solid fa-triangle-exclamation"></i> <?php esc_html_e( 'Ensure your website privacy policy and applicable local laws (GDPR, CAN-SPAM, CCPA) permit automatic subscription.', 'lead-generation-form' ); ?>
								</p>
							</div>
							<div class="wlgf-form-group">
								<label for="wlgf-newsletter-tag" class="wlgf-label"><?php esc_html_e( 'Audience Subscriber Tag / Group', 'lead-generation-form' ); ?></label>
								<input type="text" id="wlgf-newsletter-tag" class="wlgf-input" value="<?php echo esc_attr( $wlgf_newsletter_tag ); ?>" placeholder="<?php esc_attr_e( 'e.g. contact-form, newsletter-lead', 'lead-generation-form' ); ?>">
								<p class="wlgf-help-text"><?php esc_html_e( 'Tag applied to subscriber in your selected newsletter provider.', 'lead-generation-form' ); ?></p>
							</div>
						</div>

						<div id="wlgf-explicit-checkbox-options" class="wlgf-grid wlgf-grid--2col wlgf-gap-4 wlgf-mt-3 <?php echo ( 'explicit' === $wlgf_newsletter_mode ) ? '' : 'd-none'; ?>">
							<div class="wlgf-form-group">
								<label for="wlgf-newsletter-label" class="wlgf-label"><?php esc_html_e( 'Checkbox Label Text', 'lead-generation-form' ); ?></label>
								<input type="text" id="wlgf-newsletter-label" class="wlgf-input" value="<?php echo esc_attr( $wlgf_newsletter_label ); ?>">
							</div>
							<div class="wlgf-form-group wlgf-flex wlgf-items-center wlgf-pt-3">
								<label class="wlgf-checkbox-label">
									<input type="checkbox" id="wlgf-newsletter-default-checked" value="1" <?php checked( $wlgf_newsletter_default_checked, 1 ); ?>>
									<span class="wlgf-checkbox-text"><?php esc_html_e( 'Pre-checked by default', 'lead-generation-form' ); ?></span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Multi-Step Wizard & Progress Settings (PRO-002) -->
			<div class="wlgf-collapsible wlgf-mb-4 <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'wlgf-pro-locked-section' : ''; ?>" id="wlgf-wizard-accordion">
				<div class="wlgf-collapsible__header">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<i class="fa-solid fa-layer-group wlgf-text-primary"></i>
						<strong class="wlgf-text-main"><?php esc_html_e( 'Multi-Step Wizard & Progress Navigation', 'lead-generation-form' ); ?></strong>
						<?php if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) : ?>
							<span class="wlgf-badge wlgf-badge--warning"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO Feature', 'lead-generation-form' ); ?></span>
						<?php endif; ?>
					</div>
					<i class="fa-solid fa-chevron-down wlgf-collapsible__icon"></i>
				</div>
				<div class="wlgf-collapsible__body">
					<?php if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) : ?>
						<div class="wlgf-alert wlgf-alert--info wlgf-mb-3" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-left: 4px solid #f59e0b;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<i class="fa-solid fa-crown" style="font-size: 20px; color: #f59e0b;"></i>
								<div>
									<strong><?php esc_html_e( 'Multi-Step Form Wizard is a Pro Feature', 'lead-generation-form' ); ?></strong>
									<p class="wlgf-m-0 text-muted" style="font-size: 13px;"><?php esc_html_e( 'Divide your forms into interactive multi-step wizard cards with progress indicators and per-step validation. Upgrade to unlock.', 'lead-generation-form' ); ?></p>
								</div>
							</div>
							<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary wlgf-open-pro-modal-btn">
								<i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Upgrade to Pro', 'lead-generation-form' ); ?>
							</button>
						</div>
					<?php endif; ?>
					<p class="wlgf-help-text wlgf-mb-3">
						<?php esc_html_e( 'Add "Step Break (Page Break)" fields onto the canvas to divide your form into multi-step wizard cards. In Free, step breaks render as clean section dividers.', 'lead-generation-form' ); ?>
					</p>
					<fieldset <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?> style="border:none;padding:0;margin:0;">
						<div class="wlgf-grid wlgf-grid--4 wlgf-gap-4">
							<div class="wlgf-form-group">
								<label for="wlgf-wizard-style" class="wlgf-label"><?php esc_html_e( 'Progress Indicator Style', 'lead-generation-form' ); ?></label>
								<select id="wlgf-wizard-style" class="wlgf-select wlgf-w-full" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
									<option value="circles" <?php selected( $wlgf_wizard_style, 'circles' ); ?>><?php esc_html_e( 'Numbered Step Circles', 'lead-generation-form' ); ?></option>
									<option value="bar" <?php selected( $wlgf_wizard_style, 'bar' ); ?>><?php esc_html_e( 'Progress Percentage Bar', 'lead-generation-form' ); ?></option>
									<option value="breadcrumbs" <?php selected( $wlgf_wizard_style, 'breadcrumbs' ); ?>><?php esc_html_e( 'Step Breadcrumb Tabs', 'lead-generation-form' ); ?></option>
								</select>
							</div>
							<div class="wlgf-form-group">
								<label for="wlgf-wizard-step1-title" class="wlgf-label"><?php esc_html_e( 'Initial Step 1 Title', 'lead-generation-form' ); ?></label>
								<input type="text" id="wlgf-wizard-step1-title" class="wlgf-input" value="<?php echo esc_attr( $wlgf_wizard_step_1_title ); ?>" placeholder="<?php esc_attr_e( 'Step 1 (or custom name)', 'lead-generation-form' ); ?>" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
							</div>
							<div class="wlgf-form-group">
								<label for="wlgf-wizard-prev-text" class="wlgf-label"><?php esc_html_e( 'Previous Button Label', 'lead-generation-form' ); ?></label>
								<input type="text" id="wlgf-wizard-prev-text" class="wlgf-input" value="<?php echo esc_attr( $wlgf_wizard_prev_text ); ?>" placeholder="<?php esc_attr_e( 'Previous', 'lead-generation-form' ); ?>" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
							</div>
							<div class="wlgf-form-group">
								<label for="wlgf-wizard-next-text" class="wlgf-label"><?php esc_html_e( 'Next Button Label', 'lead-generation-form' ); ?></label>
								<input type="text" id="wlgf-wizard-next-text" class="wlgf-input" value="<?php echo esc_attr( $wlgf_wizard_next_text ); ?>" placeholder="<?php esc_attr_e( 'Next Step', 'lead-generation-form' ); ?>" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
							</div>
						</div>
					</fieldset>
				</div>
			</div>

			<!-- Visual Conditional Logic Manager (PRO-001) -->
			<div class="wlgf-collapsible wlgf-mb-4 <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'wlgf-pro-locked-section' : ''; ?>" id="wlgf-conditions-accordion">
				<div class="wlgf-collapsible__header">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<i class="fa-solid fa-code-branch wlgf-text-primary"></i>
						<strong class="wlgf-text-main"><?php esc_html_e( 'Visual Conditional Logic Rules', 'lead-generation-form' ); ?></strong>
						<?php if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) : ?>
							<span class="wlgf-badge wlgf-badge--warning"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO Feature', 'lead-generation-form' ); ?></span>
						<?php endif; ?>
					</div>
					<i class="fa-solid fa-chevron-down wlgf-collapsible__icon"></i>
				</div>
				<div class="wlgf-collapsible__body">
					<?php if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) : ?>
						<div class="wlgf-alert wlgf-alert--info wlgf-mb-3" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-left: 4px solid #f59e0b;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<i class="fa-solid fa-crown" style="font-size: 20px; color: #f59e0b;"></i>
								<div>
									<strong><?php esc_html_e( 'Visual Conditional Logic is a Pro Feature', 'lead-generation-form' ); ?></strong>
									<p class="wlgf-m-0 text-muted" style="font-size: 13px;"><?php esc_html_e( 'Dynamically show or hide fields based on user selections in previous fields. Upgrade to unlock.', 'lead-generation-form' ); ?></p>
								</div>
							</div>
							<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary wlgf-open-pro-modal-btn">
								<i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Upgrade to Pro', 'lead-generation-form' ); ?>
							</button>
						</div>
					<?php endif; ?>
					<p class="wlgf-help-text wlgf-mb-3">
						<?php esc_html_e( 'Dynamically show or hide fields based on user selections in previous fields.', 'lead-generation-form' ); ?>
					</p>
					<fieldset <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?> style="border:none;padding:0;margin:0;">
						<div id="wlgf-conditions-container">
							<!-- Condition Rule Cards Injected Here via JavaScript -->
						</div>
						<div class="wlgf-mt-3">
							<button type="button" id="wlgf-add-condition-btn" class="wlgf-btn wlgf-btn--secondary wlgf-btn--sm" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
								<i class="fa-solid fa-plus"></i> <?php esc_html_e( 'Add Field Condition Rule', 'lead-generation-form' ); ?>
							</button>
						</div>
					</fieldset>
				</div>
			</div>

			<!-- Payment Collection & Checkout (PRO-016) -->
			<div class="wlgf-collapsible wlgf-mb-4" id="wlgf-payment-accordion">
				<div class="wlgf-collapsible__header">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<i class="fa-solid fa-credit-card wlgf-text-primary"></i>
						<strong class="wlgf-text-main"><?php esc_html_e( 'Payment Collection & Online Checkout', 'lead-generation-form' ); ?></strong>
						<?php if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) : ?>
							<span class="wlgf-badge wlgf-badge--warning"><?php esc_html_e( 'Pro Feature', 'lead-generation-form' ); ?></span>
						<?php endif; ?>
					</div>
					<i class="fa-solid fa-chevron-down wlgf-collapsible__icon"></i>
				</div>
				<div class="wlgf-collapsible__body">
					<div class="wlgf-form-group">
						<label class="wlgf-checkbox-label">
							<input type="checkbox" id="wlgf-payment-enabled" value="1" <?php checked( $wlgf_payment_enabled, 1 ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
							<span class="wlgf-checkbox-text"><strong><?php esc_html_e( 'Enable Payment Collection on this form', 'lead-generation-form' ); ?></strong></span>
						</label>
					</div>

					<div id="wlgf-payment-settings-drawer" class="<?php echo empty( $wlgf_payment_enabled ) ? 'd-none' : ''; ?>">
						<div class="wlgf-grid wlgf-grid--2col wlgf-gap-4 wlgf-mt-3">
							<div class="wlgf-form-group">
								<label for="wlgf-payment-type" class="wlgf-label"><?php esc_html_e( 'Payment Amount Type', 'lead-generation-form' ); ?></label>
								<select id="wlgf-payment-type" class="wlgf-select wlgf-w-full">
									<option value="fixed" <?php selected( $wlgf_payment_type, 'fixed' ); ?>><?php esc_html_e( 'Fixed Amount', 'lead-generation-form' ); ?></option>
									<option value="calculation" <?php selected( $wlgf_payment_type, 'calculation' ); ?>><?php esc_html_e( 'Dynamic (Calculated from Formula Field)', 'lead-generation-form' ); ?></option>
								</select>
							</div>
							<div class="wlgf-form-group <?php echo ( 'calculation' === $wlgf_payment_type ) ? 'd-none' : ''; ?>" id="wlgf-payment-fixed-wrap">
								<label for="wlgf-payment-amount" class="wlgf-label"><?php esc_html_e( 'Fixed Amount', 'lead-generation-form' ); ?></label>
								<input type="number" step="0.01" min="0" id="wlgf-payment-amount" class="wlgf-input" value="<?php echo esc_attr( $wlgf_payment_amount ); ?>" placeholder="e.g. 49.00">
							</div>
							<div class="wlgf-form-group <?php echo ( 'calculation' === $wlgf_payment_type ) ? '' : 'd-none'; ?>" id="wlgf-payment-calc-wrap">
								<label for="wlgf-payment-calc-field" class="wlgf-label"><?php esc_html_e( 'Linked Calculation Field', 'lead-generation-form' ); ?></label>
								<select id="wlgf-payment-calc-field" class="wlgf-select wlgf-w-full" data-selected="<?php echo esc_attr( $wlgf_payment_calc_field ); ?>">
									<option value=""><?php esc_html_e( '-- Select Calculation Field --', 'lead-generation-form' ); ?></option>
								</select>
							</div>
							<div class="wlgf-form-group">
								<label for="wlgf-payment-item-name" class="wlgf-label"><?php esc_html_e( 'Item Description / Charge Label', 'lead-generation-form' ); ?></label>
								<input type="text" id="wlgf-payment-item-name" class="wlgf-input" value="<?php echo esc_attr( $wlgf_payment_item_name ); ?>" placeholder="<?php esc_attr_e( 'e.g. Booking Deposit / Consultation Fee', 'lead-generation-form' ); ?>">
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Collapsible Display Modes & Smart Conversion Triggers (OptinMonster & Hustle Challenger) -->
			<div class="wlgf-collapsible wlgf-mb-4" id="wlgf-display-triggers-accordion">
				<div class="wlgf-collapsible__header">
					<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
						<i class="fa-solid fa-bolt wlgf-text-primary"></i>
						<div>
							<strong class="wlgf-text-main"><?php esc_html_e( 'Display Modes & Smart Conversion Triggers', 'lead-generation-form' ); ?></strong>
							<span class="wlgf-badge wlgf-badge--primary wlgf-ml-2"><?php esc_html_e( 'Exit-Intent / Popups', 'lead-generation-form' ); ?></span>
						</div>
					</div>
					<i class="fa-solid fa-chevron-down wlgf-collapsible__icon"></i>
				</div>
				<div class="wlgf-collapsible__body">
					<p class="wlgf-text-xs wlgf-text-muted wlgf-mb-3"><?php esc_html_e( 'Choose how and when this form is displayed: standard static embed, exit-intent popup modal, slide-in box, sticky notification bar, or floating WhatsApp/contact bubble widget.', 'lead-generation-form' ); ?></p>
					
					<div class="wlgf-grid wlgf-grid--2col wlgf-gap-3">
						<div class="wlgf-form-group">
							<label for="wlgf-display-mode" class="wlgf-label"><?php esc_html_e( 'Form Display Mode', 'lead-generation-form' ); ?></label>
							<select id="wlgf-display-mode" class="wlgf-select wlgf-w-full">
								<option value="inline" <?php selected( $wlgf_display_mode, 'inline' ); ?>><?php esc_html_e( 'Standard Inline Embed (Shortcode / Elementor / Gutenberg)', 'lead-generation-form' ); ?></option>
								<option value="popup" <?php selected( $wlgf_display_mode, 'popup' ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>><?php esc_html_e( 'Lightbox Modal Popup ★ PRO', 'lead-generation-form' ); ?></option>
								<option value="slidein" <?php selected( $wlgf_display_mode, 'slidein' ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>><?php esc_html_e( 'Slide-in Corner Box ★ PRO', 'lead-generation-form' ); ?></option>
								<option value="sticky_bar" <?php selected( $wlgf_display_mode, 'sticky_bar' ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>><?php esc_html_e( 'Sticky Notification Bar ★ PRO', 'lead-generation-form' ); ?></option>
								<option value="floating_widget" <?php selected( $wlgf_display_mode, 'floating_widget' ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>><?php esc_html_e( 'Floating WhatsApp & Contact Launcher ★ PRO', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group" id="wlgf-position-wrap">
							<label for="wlgf-display-position" class="wlgf-label"><?php esc_html_e( 'Placement Position', 'lead-generation-form' ); ?></label>
							<select id="wlgf-display-position" class="wlgf-select wlgf-w-full">
								<option value="right" <?php selected( $wlgf_display_position, 'right' ); ?>><?php esc_html_e( 'Bottom Right / Top Bar', 'lead-generation-form' ); ?></option>
								<option value="left" <?php selected( $wlgf_display_position, 'left' ); ?>><?php esc_html_e( 'Bottom Left / Bottom Bar', 'lead-generation-form' ); ?></option>
							</select>
						</div>
					</div>

					<div class="wlgf-grid wlgf-grid--3 wlgf-gap-3 wlgf-mt-3" id="wlgf-triggers-row">
						<div class="wlgf-form-group">
							<label for="wlgf-trigger-type" class="wlgf-label"><?php esc_html_e( 'Behavioral Trigger', 'lead-generation-form' ); ?></label>
							<select id="wlgf-trigger-type" class="wlgf-select wlgf-w-full" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
								<option value="time_delay" <?php selected( $wlgf_trigger_type, 'time_delay' ); ?>><?php esc_html_e( 'Time Delay (Seconds on page)', 'lead-generation-form' ); ?></option>
								<option value="exit_intent" <?php selected( $wlgf_trigger_type, 'exit_intent' ); ?>><?php esc_html_e( 'Exit-Intent (Mouse departs top/close)', 'lead-generation-form' ); ?></option>
								<option value="scroll_depth" <?php selected( $wlgf_trigger_type, 'scroll_depth' ); ?>><?php esc_html_e( 'Scroll Depth %', 'lead-generation-form' ); ?></option>
								<option value="click_only" <?php selected( $wlgf_trigger_type, 'click_only' ); ?>><?php esc_html_e( 'Click Trigger Only (MonsterLink)', 'lead-generation-form' ); ?></option>
							</select>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-trigger-delay" class="wlgf-label"><?php esc_html_e( 'Delay / Inactivity (Seconds)', 'lead-generation-form' ); ?></label>
							<input type="number" id="wlgf-trigger-delay" class="wlgf-input" value="<?php echo esc_attr( $wlgf_trigger_delay ); ?>" min="1" max="120" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
						</div>

						<div class="wlgf-form-group">
							<label for="wlgf-trigger-scroll" class="wlgf-label"><?php esc_html_e( 'Scroll Percentage (%)', 'lead-generation-form' ); ?></label>
							<input type="number" id="wlgf-trigger-scroll" class="wlgf-input" value="<?php echo esc_attr( $wlgf_trigger_scroll ); ?>" min="10" max="100" step="5" <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
						</div>
					</div>

					<!-- Abandoned Lead Auto-Capture Toggle -->
					<div class="wlgf-form-group wlgf-mt-3" style="background: #f8fafc; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0;">
						<label class="wlgf-switch-label">
							<input type="checkbox" id="wlgf-capture-abandoned" value="1" <?php checked( 1, $wlgf_capture_abandoned ); ?> <?php echo ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) ? 'disabled' : ''; ?>>
							<span class="wlgf-switch-slider"></span>
							<strong class="wlgf-ml-2"><?php esc_html_e( 'Capture Partial / Abandoned Leads Automatically', 'lead-generation-form' ); ?></strong>
							<?php if ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) : ?>
								<span class="wlgf-badge wlgf-badge--warning wlgf-ml-2"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO', 'lead-generation-form' ); ?></span>
							<?php endif; ?>
						</label>
						<p class="wlgf-help-text wlgf-mt-1">
							<?php esc_html_e( 'When enabled, if a visitor starts filling their name/email and leaves without submitting, their partial entry is saved to the CRM marked as "Abandoned" so you can follow up.', 'lead-generation-form' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Action Buttons / Feedback Area -->
			<div id="wlgf-button-div" class="wlgf-mt-4">
				<?php if ( 'set' === $wlgf_FormIdSet ) : ?>
					<input type="hidden" id="wlgf-modify-form-id" value="<?php echo esc_attr( $wlgf_form_id ); ?>">
					<div class="wlgf-form-actions-row wlgf-flex wlgf-items-center wlgf-gap-3 wlgf-mb-3">
						<button type="button" id="wlgf-modify-form" value="wlgf_modify_form" class="wlgf-action-button wlgf-btn wlgf-btn--primary wlgf-btn--lg">
							<i class="fa-solid fa-file-pen"></i> <?php esc_html_e( 'Update Form', 'lead-generation-form' ); ?>
						</button>
					</div>

					<!-- Prominent Form Shortcode Embed Box (Displayed AFTER Save/Update Button) -->
					<div class="wlgf-shortcode-embed-box">
						<div class="wlgf-shortcode-embed-box__info">
							<div class="wlgf-shortcode-embed-box__icon">
								<i class="fa-solid fa-code"></i>
							</div>
							<div class="wlgf-shortcode-embed-box__meta">
								<span class="wlgf-shortcode-embed-box__title"><?php esc_html_e( 'Form Shortcode', 'lead-generation-form' ); ?></span>
								<span class="wlgf-shortcode-embed-box__desc"><?php esc_html_e( 'Copy & paste into any page, post, or block widget.', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<div class="wlgf-shortcode-embed-box__action">
							<code class="wlgf-shortcode-embed-box__code">[WLFG id="<?php echo esc_attr( $wlgf_form_id ); ?>"]</code>
							<button type="button" id="wlgf-shortcode-btn-<?php echo esc_attr( $wlgf_form_id ); ?>" class="wlgf-shortcode-embed-box__btn" onclick="return WLGF('<?php echo esc_attr( $wlgf_form_id ); ?>', 'copy', this);" title="<?php esc_attr_e( 'Copy Shortcode', 'lead-generation-form' ); ?>">
								<i class="fa-regular fa-copy"></i>
								<span><?php esc_html_e( 'Copy Shortcode', 'lead-generation-form' ); ?></span>
							</button>
							<span id="wlgf-copied-<?php echo esc_attr( $wlgf_form_id ); ?>" class="wlgf-code-pill__copied-tooltip"><?php esc_html_e( 'Copied!', 'lead-generation-form' ); ?></span>
						</div>
					</div>
				<?php else : ?>
					<button type="button" id="wlgf-save-form" value="wlgf_save_form" class="wlgf-action-button wlgf-btn wlgf-btn--primary wlgf-btn--lg">
						<i class="fa-solid fa-floppy-disk"></i> <?php esc_html_e( 'Save Form', 'lead-generation-form' ); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Instructions & Server Health Grid -->
	<div class="wlgf-grid wlgf-grid--2 wlgf-mt-4">
		<!-- Form Builder Tips & Field Guide Card -->
		<div class="wlgf-card">
			<div class="wlgf-card__header">
				<h2 class="wlgf-card__title">
					<i class="fa-solid fa-circle-info"></i> <?php esc_html_e( 'Form Builder Tips & Field Guide', 'lead-generation-form' ); ?>
				</h2>
				<span class="wlgf-badge wlgf-badge--primary"><?php esc_html_e( 'Pro Tips', 'lead-generation-form' ); ?></span>
			</div>
			<div class="wlgf-card__body">
				<!-- Core Onboarding Steps -->
				<div class="wlgf-steps">
					<div class="wlgf-step">
						<span class="wlgf-step__num"><i class="fa-solid fa-check"></i></span>
						<div class="wlgf-step__content">
							<strong class="wlgf-step__title"><?php esc_html_e( 'Adding & Customizing Fields', 'lead-generation-form' ); ?></strong>
							<span class="wlgf-step__desc"><?php esc_html_e( 'Drag elements from the right toolbar directly onto the form canvas. Click any field on the canvas to customize its label, placeholder, column width, and required status.', 'lead-generation-form' ); ?></span>
						</div>
					</div>
					<div class="wlgf-step">
						<span class="wlgf-step__num"><i class="fa-solid fa-paper-plane"></i></span>
						<div class="wlgf-step__content">
							<strong class="wlgf-step__title"><?php esc_html_e( 'Submit Button Requirement', 'lead-generation-form' ); ?></strong>
							<span class="wlgf-step__desc"><?php esc_html_e( 'Every form requires at least one Submit button to validate inputs and capture leads into your database and email dispatch.', 'lead-generation-form' ); ?></span>
						</div>
					</div>
				</div>

				<!-- Advanced Fields Guide Section -->
				<div class="wlgf-field-guide-section">
					<div class="wlgf-field-guide-header">
						<h3 class="wlgf-field-guide-title">
							<i class="fa-solid fa-wand-magic-sparkles"></i> <?php esc_html_e( 'Special & Advanced Fields Guide', 'lead-generation-form' ); ?>
						</h3>
						<div class="wlgf-field-guide-actions">
							<span class="wlgf-badge wlgf-badge--info"><?php esc_html_e( '5 Special Fields', 'lead-generation-form' ); ?></span>
							<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--secondary" id="wlgf-toggle-all-tips" title="<?php esc_attr_e( 'Expand or collapse all field guides', 'lead-generation-form' ); ?>">
								<i class="fa-solid fa-angles-down"></i> <?php esc_html_e( 'Expand All', 'lead-generation-form' ); ?>
							</button>
						</div>
					</div>
					<p class="wlgf-field-guide-subtitle">
						<?php esc_html_e( 'Click any field below to view what it is, how to configure it, and practical examples:', 'lead-generation-form' ); ?>
					</p>

					<div class="wlgf-field-tips-accordion">
						<!-- 1. Star Rating (1-5) -->
						<div class="wlgf-collapsible wlgf-field-tip-item">
							<div class="wlgf-collapsible__header">
								<div class="wlgf-field-tip-item__header-left">
									<span class="wlgf-field-tip-icon wlgf-field-tip-icon--star">
										<i class="fa-solid fa-star"></i>
									</span>
									<span class="wlgf-field-tip-name"><?php esc_html_e( 'Star Rating (1-5)', 'lead-generation-form' ); ?></span>
									<span class="wlgf-badge wlgf-badge--warning"><?php esc_html_e( 'Customer Feedback', 'lead-generation-form' ); ?></span>
								</div>
								<span class="wlgf-collapsible__chevron"><i class="fa-solid fa-chevron-down"></i></span>
							</div>
							<div class="wlgf-collapsible__body">
								<div class="wlgf-field-tip-body">
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'What is it?', 'lead-generation-form' ); ?></strong>
										<p><?php esc_html_e( 'An interactive 1-to-5 star rating widget with smooth hover states and instant click selection. It allows website visitors to rate customer satisfaction, review products, or rate service quality intuitively on desktop and mobile devices.', 'lead-generation-form' ); ?></p>
									</div>
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-sliders"></i> <?php esc_html_e( 'How to Use & Configure:', 'lead-generation-form' ); ?></strong>
										<ul>
											<li><?php esc_html_e( 'Drag "Star Rating (1-5)" from the right toolbar onto the form canvas.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Click the field to customize the Label (e.g. "Rate your experience with our team").', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Toggle "Required" if rating is mandatory before form submission.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Star colors and hover highlights automatically match your form primary color chosen in Theme Styler.', 'lead-generation-form' ); ?></li>
										</ul>
									</div>
									<div class="wlgf-field-tip-example">
										<div class="wlgf-field-tip-example__header">
											<i class="fa-solid fa-lightbulb"></i> <?php esc_html_e( 'Example Use Case:', 'lead-generation-form' ); ?>
										</div>
										<p><?php esc_html_e( 'Post-Service Feedback: "How would you rate the responsiveness of our support engineer?" (Submits numerical score 1 to 5 saved directly in your Leads database).', 'lead-generation-form' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<!-- 2. NPS Score Matrix (0-10) -->
						<div class="wlgf-collapsible wlgf-field-tip-item">
							<div class="wlgf-collapsible__header">
								<div class="wlgf-field-tip-item__header-left">
									<span class="wlgf-field-tip-icon wlgf-field-tip-icon--nps">
										<i class="fa-solid fa-gauge-high"></i>
									</span>
									<span class="wlgf-field-tip-name"><?php esc_html_e( 'NPS Score Matrix (0-10)', 'lead-generation-form' ); ?></span>
									<span class="wlgf-badge wlgf-badge--info"><?php esc_html_e( 'Loyalty Benchmark', 'lead-generation-form' ); ?></span>
								</div>
								<span class="wlgf-collapsible__chevron"><i class="fa-solid fa-chevron-down"></i></span>
							</div>
							<div class="wlgf-collapsible__body">
								<div class="wlgf-field-tip-body">
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'What is it?', 'lead-generation-form' ); ?></strong>
										<p><?php esc_html_e( 'Industry-standard 0 to 10 Net Promoter Score matrix. Renders responsive numbered chip buttons that measure customer loyalty and categorize respondents into Detractors (0-6), Passives (7-8), and Promoters (9-10).', 'lead-generation-form' ); ?></p>
									</div>
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-sliders"></i> <?php esc_html_e( 'How to Use & Configure:', 'lead-generation-form' ); ?></strong>
										<ul>
											<li><?php esc_html_e( 'Drag "NPS Score Matrix (0-10)" onto the form canvas.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Set the question label (e.g. "How likely are you to recommend us to a colleague?").', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Visitors see clickable pills numbered 0 to 10 with clear "Not at all likely" to "Extremely likely" guide indicators.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Pair with Conditional Logic: Trigger a testimonial request textarea if score is 9-10, or an issue resolution box if score is 0-6.', 'lead-generation-form' ); ?></li>
										</ul>
									</div>
									<div class="wlgf-field-tip-example">
										<div class="wlgf-field-tip-example__header">
											<i class="fa-solid fa-lightbulb"></i> <?php esc_html_e( 'Example Use Case:', 'lead-generation-form' ); ?>
										</div>
										<p><?php esc_html_e( 'SaaS or Client Exit Survey: "On a scale of 0 to 10, how likely are you to recommend our services?" (Benchmark score tracked per lead entry).', 'lead-generation-form' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<!-- 3. Calculation / Price Estimator -->
						<div class="wlgf-collapsible wlgf-field-tip-item">
							<div class="wlgf-collapsible__header">
								<div class="wlgf-field-tip-item__header-left">
									<span class="wlgf-field-tip-icon wlgf-field-tip-icon--calc">
										<i class="fa-solid fa-calculator"></i>
									</span>
									<span class="wlgf-field-tip-name"><?php esc_html_e( 'Calculation / Price Estimator', 'lead-generation-form' ); ?></span>
									<span class="wlgf-badge wlgf-badge--primary"><?php esc_html_e( 'Real-time Math', 'lead-generation-form' ); ?></span>
								</div>
								<span class="wlgf-collapsible__chevron"><i class="fa-solid fa-chevron-down"></i></span>
							</div>
							<div class="wlgf-collapsible__body">
								<div class="wlgf-field-tip-body">
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'What is it?', 'lead-generation-form' ); ?></strong>
										<p><?php esc_html_e( 'A dynamic real-time mathematical formula evaluator that calculates instant estimates, quotes, loan projections, or service totals live in the browser as visitors type or select options.', 'lead-generation-form' ); ?></p>
									</div>
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-sliders"></i> <?php esc_html_e( 'How to Use & Configure:', 'lead-generation-form' ); ?></strong>
										<ul>
											<li><?php esc_html_e( 'First add input fields (Number, Dropdown, or Checkboxes) and note their field names (e.g. "hours", "qty", or "rooms").', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Drag "Calculation / Price Estimator" onto the form canvas and click it to open settings.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Calculation Formula: Reference input field names inside square brackets, e.g. ([hours] * 50) + 100. Supports +, -, *, /, and parentheses.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Prefix: Enter your currency symbol (e.g. "$", "₹", "€").', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Postfix: Enter a duration or unit suffix (e.g. "/month", " total").', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Decimal Precision: Set 2 for standard decimals (e.g. $150.00) or 0 for rounded integers.', 'lead-generation-form' ); ?></li>
										</ul>
									</div>
									<div class="wlgf-field-tip-example">
										<div class="wlgf-field-tip-example__header">
											<i class="fa-solid fa-lightbulb"></i> <?php esc_html_e( 'Example Formula:', 'lead-generation-form' ); ?>
										</div>
										<p><code>([quantity] * 45) + 20</code></p>
										<p class="wlgf-text-muted small"><?php esc_html_e( 'When visitor enters quantity = 4, the field immediately displays "$200.00 total" live before submit.', 'lead-generation-form' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<!-- 4. GDPR / Terms Checkbox -->
						<div class="wlgf-collapsible wlgf-field-tip-item">
							<div class="wlgf-collapsible__header">
								<div class="wlgf-field-tip-item__header-left">
									<span class="wlgf-field-tip-icon wlgf-field-tip-icon--terms">
										<i class="fa-solid fa-shield-halved"></i>
									</span>
									<span class="wlgf-field-tip-name"><?php esc_html_e( 'GDPR / Terms Checkbox', 'lead-generation-form' ); ?></span>
									<span class="wlgf-badge wlgf-badge--success"><?php esc_html_e( 'Legal & Privacy', 'lead-generation-form' ); ?></span>
								</div>
								<span class="wlgf-collapsible__chevron"><i class="fa-solid fa-chevron-down"></i></span>
							</div>
							<div class="wlgf-collapsible__body">
								<div class="wlgf-field-tip-body">
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'What is it?', 'lead-generation-form' ); ?></strong>
										<p><?php esc_html_e( 'A mandatory legal compliance checkbox required by GDPR, CCPA, and data privacy regulations. Submissions are strictly blocked until the visitor explicitly checks the agreement box.', 'lead-generation-form' ); ?></p>
									</div>
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-sliders"></i> <?php esc_html_e( 'How to Use & Configure:', 'lead-generation-form' ); ?></strong>
										<ul>
											<li><?php esc_html_e( 'Drag "GDPR / Terms Checkbox" to the bottom of the form (typically right before the Submit button).', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Click the field to customize the agreement text. HTML anchor links are fully supported inside the label.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'The checkbox remains mandatory by default, protecting your website from non-compliant data collection.', 'lead-generation-form' ); ?></li>
										</ul>
									</div>
									<div class="wlgf-field-tip-example">
										<div class="wlgf-field-tip-example__header">
											<i class="fa-solid fa-lightbulb"></i> <?php esc_html_e( 'Example Label with HTML Links:', 'lead-generation-form' ); ?>
										</div>
										<p><code>I agree to the &lt;a href="/terms" target="_blank"&gt;Terms of Service&lt;/a&gt; and &lt;a href="/privacy" target="_blank"&gt;Privacy Policy&lt;/a&gt;.</code></p>
									</div>
								</div>
							</div>
						</div>

						<!-- 5. Digital Signature Pad -->
						<div class="wlgf-collapsible wlgf-field-tip-item">
							<div class="wlgf-collapsible__header">
								<div class="wlgf-field-tip-item__header-left">
									<span class="wlgf-field-tip-icon wlgf-field-tip-icon--sig">
										<i class="fa-solid fa-pen-nib"></i>
									</span>
									<span class="wlgf-field-tip-name"><?php esc_html_e( 'Digital Signature Pad', 'lead-generation-form' ); ?></span>
									<span class="wlgf-badge wlgf-badge--secondary"><?php esc_html_e( 'E-Signature Canvas', 'lead-generation-form' ); ?></span>
								</div>
								<span class="wlgf-collapsible__chevron"><i class="fa-solid fa-chevron-down"></i></span>
							</div>
							<div class="wlgf-collapsible__body">
								<div class="wlgf-field-tip-body">
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-circle-question"></i> <?php esc_html_e( 'What is it?', 'lead-generation-form' ); ?></strong>
										<p><?php esc_html_e( 'An interactive HTML5 canvas signature pad that lets respondents draw their e-signature using finger touch (mobile/tablet) or mouse (desktop). Features smooth vector line smoothing and an instant "Clear" reset button.', 'lead-generation-form' ); ?></p>
									</div>
									<div class="wlgf-field-tip-block">
										<strong><i class="fa-solid fa-sliders"></i> <?php esc_html_e( 'How to Use & Configure:', 'lead-generation-form' ); ?></strong>
										<ul>
											<li><?php esc_html_e( 'Drag "Digital Signature Pad" from the right toolbar onto your form canvas.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Set field label (e.g. "Authorized Representative Signature" or "Client Sign-off").', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Upon submission, the signature is converted into a high-resolution transparent PNG base64 string and stored securely with the lead record.', 'lead-generation-form' ); ?></li>
											<li><?php esc_html_e( 'Admins can view verified signatures directly in the Leads Table and include them in PDF download receipts.', 'lead-generation-form' ); ?></li>
										</ul>
									</div>
									<div class="wlgf-field-tip-example">
										<div class="wlgf-field-tip-example__header">
											<i class="fa-solid fa-lightbulb"></i> <?php esc_html_e( 'Example Use Case:', 'lead-generation-form' ); ?>
										</div>
										<p><?php esc_html_e( 'Service Contracts, NDA Agreements, Equipment Rentals, and Maintenance Authorisations requiring legal sign-off before dispatch.', 'lead-generation-form' ); ?></p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Server Diagnostics & Best Practices Card -->
		<div class="wlgf-card">
			<div class="wlgf-card__header">
				<h2 class="wlgf-card__title">
					<i class="fa-solid fa-server"></i> <?php esc_html_e( 'Server File Upload Limits & Best Practices', 'lead-generation-form' ); ?>
				</h2>
				<span class="wlgf-badge wlgf-badge--primary"><?php esc_html_e( 'Server Health', 'lead-generation-form' ); ?></span>
			</div>
			<div class="wlgf-card__body">
				<p class="wlgf-card__text">
					<?php esc_html_e( 'If you use File Upload fields on your form, submissions will be bound by your hosting server thresholds:', 'lead-generation-form' ); ?>
				</p>
				<div class="wlgf-flex wlgf-gap-3 wlgf-flex-wrap wlgf-mb-4">
					<div class="wlgf-badge wlgf-badge--primary">
						<?php esc_html_e( 'Max Upload Size:', 'lead-generation-form' ); ?> <?php echo esc_html( $wlgf_max_upload_size ); ?>
					</div>
					<div class="wlgf-badge wlgf-badge--primary">
						<?php esc_html_e( 'Max POST Size:', 'lead-generation-form' ); ?> <?php echo esc_html( $wlgf_max_post_size ); ?>
					</div>
				</div>

				<div class="wlgf-field-guide-section wlgf-mt-3">
					<div class="wlgf-field-guide-header">
						<h3 class="wlgf-field-guide-title">
							<i class="fa-solid fa-shield-halved"></i> <?php esc_html_e( 'Lead Form Best Practices', 'lead-generation-form' ); ?>
						</h3>
					</div>
					<div class="wlgf-steps wlgf-mt-3">
						<div class="wlgf-step">
							<span class="wlgf-step__num"><i class="fa-solid fa-table-columns"></i></span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Multi-Column Grid Layouts', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc"><?php esc_html_e( 'Use 50% width on First & Last Name or Email & Phone to create clean 2-column horizontal rows that increase form conversions.', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<div class="wlgf-step">
							<span class="wlgf-step__num"><i class="fa-solid fa-arrows-split-up-and-left"></i></span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Smart Conditional Logic', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc"><?php esc_html_e( 'Keep initial forms short. Use the Conditional Logic accordion above to reveal secondary questions only when relevant options are selected.', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<div class="wlgf-step">
							<span class="wlgf-step__num"><i class="fa-solid fa-layer-group"></i></span>
							<div class="wlgf-step__content">
								<strong class="wlgf-step__title"><?php esc_html_e( 'Multi-Step Wizard Breakers', 'lead-generation-form' ); ?></strong>
								<span class="wlgf-step__desc"><?php esc_html_e( 'For forms with 6+ fields, insert Step Break elements to split questions into bite-sized steps with animated progress bars.', 'lead-generation-form' ); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Templates Modal Dialog -->
	<div id="wlgf-templates-modal" class="wlgf-modal-overlay wlgf-d-none">
		<div class="wlgf-modal wlgf-modal--lg">
			<div class="wlgf-modal__header">
				<div class="wlgf-modal__icon">
					<i class="fa-solid fa-shapes"></i>
				</div>
				<h3 class="wlgf-modal__title"><?php esc_html_e( 'Form Template Library', 'lead-generation-form' ); ?></h3>
				<button type="button" class="wlgf-modal__close-btn" id="wlgf-close-templates-btn" title="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>" aria-label="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>"><i class="fa-solid fa-xmark"></i></button>
			</div>
			<div class="wlgf-modal__body">
				<p class="wlgf-template-modal-header-desc"><?php esc_html_e( 'Choose from 20+ conversion-optimized, pre-built templates or start with a clean slate to rapidly build your custom form structure.', 'lead-generation-form' ); ?></p>
				
				<div class="wlgf-template-toolbar">
					<div class="wlgf-template-search-box">
						<i class="fa-solid fa-magnifying-glass"></i>
						<input type="text" id="wlgf-template-search-input" class="wlgf-template-search-input" placeholder="<?php esc_attr_e( 'Search templates (e.g. quote, consultation, booking, demo, survey)...', 'lead-generation-form' ); ?>">
					</div>
					<div class="wlgf-template-filter-pills" id="wlgf-template-filter-pills">
						<button type="button" class="wlgf-template-filter-pill active" data-category="all"><i class="fa-solid fa-border-all"></i> <?php esc_html_e( 'All Templates', 'lead-generation-form' ); ?> (25)</button>
						<button type="button" class="wlgf-template-filter-pill" data-category="lead-gen"><i class="fa-solid fa-bullseye"></i> <?php esc_html_e( 'Lead Gen & Sales', 'lead-generation-form' ); ?> (10)</button>
						<button type="button" class="wlgf-template-filter-pill" data-category="surveys"><i class="fa-solid fa-chart-pie"></i> <?php esc_html_e( 'Surveys & Feedback', 'lead-generation-form' ); ?> (4)</button>
						<button type="button" class="wlgf-template-filter-pill" data-category="events"><i class="fa-solid fa-calendar-days"></i> <?php esc_html_e( 'Events & Bookings', 'lead-generation-form' ); ?> (5)</button>
						<button type="button" class="wlgf-template-filter-pill" data-category="hr"><i class="fa-solid fa-user-tie"></i> <?php esc_html_e( 'HR & Support', 'lead-generation-form' ); ?> (2)</button>
						<button type="button" class="wlgf-template-filter-pill" data-category="ecommerce"><i class="fa-solid fa-credit-card"></i> <?php esc_html_e( 'Orders & Payments', 'lead-generation-form' ); ?> (4)</button>
					</div>
				</div>

				<div class="wlgf-templates-grid" id="wlgf-templates-grid">
					<!-- Template 0: Blank Form -->
					<div class="wlgf-template-card wlgf-template-card--blank" data-template="blank" data-category="all" data-keywords="blank empty new start scratch custom clean 0 00 #0 #00">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-plus"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number wlgf-template-card__number--blank">#00</span>
								<span class="wlgf-template-card__tag"><?php esc_html_e( 'Blank Canvas', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">0. </span><?php esc_html_e( 'Start from Scratch', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Begin with a clean slate and build your own tailored form using the drag-and-drop field palette.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-cube"></i> <?php esc_html_e( '0 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-sparkles"></i> <?php esc_html_e( 'Clean Canvas', 'lead-generation-form' ); ?></span>
						</div>
						<button type="button" class="wlgf-btn wlgf-btn--secondary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Create Blank Form', 'lead-generation-form' ); ?></button>
					</div>

					<!-- 1: General Contact -->
					<div class="wlgf-template-card" data-template="contact" data-category="lead-gen" data-keywords="contact message general inquiry email phone name support get in touch 1 01 #1 #01">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-envelope-open-text"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#01</span>
								<span class="wlgf-template-card__tag wlgf-template-card__tag--popular"><?php esc_html_e( 'Popular', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">1. </span><?php esc_html_e( 'Classic Contact Form', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Essential inquiry layout with Full Name, Business Email, Subject line, and Message area.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '6 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-bullseye"></i> <?php esc_html_e( 'Lead Gen', 'lead-generation-form' ); ?></span>
						</div>
						<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
					</div>

					<!-- 2: Request Quote -->
					<div class="wlgf-template-card" data-template="quote" data-category="lead-gen" data-is-pro="1" data-keywords="quote price estimate calculation cost multi step scope budget 2 02 #2 #02">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-calculator"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#02</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Calculation', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Calc', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">2. </span><?php esc_html_e( 'Request a Quote & Cost Estimator', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Multi-step scope wizard with Service dropdown, Budget range, Target Date, and Dynamic Price Calculation.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-layer-group"></i> <?php esc_html_e( '3 Steps • 8 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-calculator"></i> <?php esc_html_e( 'Calculation', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 3: B2B Demo Booking -->
					<div class="wlgf-template-card" data-template="demo" data-category="lead-gen" data-keywords="demo b2b sales discovery call enterprise team software saas presentation 3 03 #3 #03">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-laptop-code"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#03</span>
								<span class="wlgf-template-card__tag wlgf-template-card__tag--popular"><?php esc_html_e( 'High Converting', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">3. </span><?php esc_html_e( 'B2B Demo Booking & Discovery', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Qualify prospective clients with Work Email, Company Name, Team Size, and Current Toolset.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '7 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-briefcase"></i> <?php esc_html_e( 'B2B Sales', 'lead-generation-form' ); ?></span>
						</div>
						<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
					</div>

					<!-- 4: Real Estate Property Inquiry -->
					<div class="wlgf-template-card" data-template="realestate" data-category="lead-gen" data-keywords="real estate property house apartment mortgage tour buyer seller broker agent 4 04 #4 #04">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-house-chimney"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#04</span>
								<span class="wlgf-template-card__tag"><?php esc_html_e( 'Real Estate', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">4. </span><?php esc_html_e( 'Real Estate Property Tour & Inquiry', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Capture prospective buyer interest with Property Type, Budget, Financing status, and Tour Date.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '7 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-house"></i> <?php esc_html_e( 'Property', 'lead-generation-form' ); ?></span>
						</div>
						<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
					</div>

					<!-- 5: Agency Project Scope -->
					<div class="wlgf-template-card" data-template="agency" data-category="lead-gen" data-is-pro="1" data-keywords="agency client intake marketing seo design branding project scope multi step 5 05 #5 #05">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#05</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Multi-Step', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Wizard', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">5. </span><?php esc_html_e( 'Agency Client Intake & Project Scope', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Organized 3-step intake covering Client Brand, Deliverable Selection, Budget, and Target Launch Date.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-layer-group"></i> <?php esc_html_e( '3 Steps • 9 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-palette"></i> <?php esc_html_e( 'Agency', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 6: Free Consultation Scheduling -->
					<div class="wlgf-template-card" data-template="consultation" data-category="lead-gen" data-keywords="consultation appointment booking schedule meeting expert lawyer doctor coach advisor 6 06 #6 #06">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-calendar-plus"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#06</span>
								<span class="wlgf-template-card__tag"><?php esc_html_e( 'Booking', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">6. </span><?php esc_html_e( 'Free Consultation Scheduling', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Appointment intake with Topic interest, Preferred Date, Morning/Afternoon slot, and Phone contact.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '6 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-calendar-check"></i> <?php esc_html_e( 'Consulting', 'lead-generation-form' ); ?></span>
						</div>
						<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
					</div>

					<!-- 7: Gated E-Book Download -->
					<div class="wlgf-template-card" data-template="ebook" data-category="lead-gen" data-keywords="ebook whitepaper lead magnet gated content download guide report 7 07 #7 #07">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-book-bookmark"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#07</span>
								<span class="wlgf-template-card__tag wlgf-template-card__tag--popular"><?php esc_html_e( 'Lead Magnet', 'lead-generation-form' ); ?></span>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">7. </span><?php esc_html_e( 'Gated E-Book & Whitepaper Download', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'High-conversion opt-in collecting Business Email, Job Title, Company, and Industry.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '5 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-download"></i> <?php esc_html_e( 'Opt-In', 'lead-generation-form' ); ?></span>
						</div>
						<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
					</div>

					<!-- 8: SaaS Waitlist -->
					<div class="wlgf-template-card" data-template="waitlist" data-category="lead-gen" data-is-pro="1" data-keywords="waitlist beta early access launch signup software saas app product 8 08 #8 #08">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-rocket"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#08</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Waitlist', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Waitlist', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">8. </span><?php esc_html_e( 'SaaS Early Access & Beta Waitlist', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Build pre-launch excitement with Email, Role, Primary Use Case, and Referral Source.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '5 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-users-viewfinder"></i> <?php esc_html_e( 'Pre-Launch', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 9: CSAT & NPS Survey -->
					<div class="wlgf-template-card" data-template="csat_nps" data-category="surveys" data-is-pro="1" data-keywords="csat nps survey net promoter score satisfaction rating feedback star matrix 9 09 #9 #09">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-gauge-high"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#09</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'NPS Survey', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • NPS', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">9. </span><?php esc_html_e( 'CSAT & Net Promoter Score (NPS)', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Measure client loyalty with 0-10 NPS Matrix, 5-Star Service Rating, and Open Feedback.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-chart-line"></i> <?php esc_html_e( '6 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-star"></i> <?php esc_html_e( 'NPS Matrix', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 10: Website UX & Bug Report -->
					<div class="wlgf-template-card" data-template="ux_bug" data-category="surveys" data-is-pro="1" data-keywords="bug report ux feedback website issue error glitch screenshot file 10 #10">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-bug"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#10</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'File Upload', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • File', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">10. </span><?php esc_html_e( 'Website UX Feedback & Bug Report', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Capture usability friction with Page URL, Issue Category, Severity, and Screenshot Upload.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-paperclip"></i> <?php esc_html_e( '6 Fields • Upload', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-code"></i> <?php esc_html_e( 'QA / UX', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 11: Employee Pulse Survey -->
					<div class="wlgf-template-card" data-template="employee_pulse" data-category="surveys" data-is-pro="1" data-keywords="employee pulse workplace hr internal team culture satisfaction survey 11 #11">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-users"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#11</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Team Pulse', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Pulse', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">11. </span><?php esc_html_e( 'Employee Pulse & Team Feedback', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Gather internal sentiment with Department selection, Work Environment Rating, and Wellness checks.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '6 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-user-group"></i> <?php esc_html_e( 'HR Pulse', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 12: Product Review & Testimonial -->
					<div class="wlgf-template-card" data-template="product_review" data-category="surveys" data-is-pro="1" data-keywords="product review testimonial rating star customer social proof feedback 12 #12">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-star-half-stroke"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#12</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( '5-Star Rating', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Rating', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">12. </span><?php esc_html_e( 'Product Review & Testimonial Collector', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Collect authentic social proof with Product Name, 5-Star Rating, Review Headline, and Permission toggle.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-star"></i> <?php esc_html_e( '5-Star Rating', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-comment-dots"></i> <?php esc_html_e( 'Testimonials', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 13: Conference & Webinar Registration -->
					<div class="wlgf-template-card" data-template="conference" data-category="events" data-is-pro="1" data-keywords="conference webinar registration event ticket attendee rsvp multi step 13 #13">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-ticket"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#13</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Multi-Step', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Wizard', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">13. </span><?php esc_html_e( 'Conference & Webinar Registration', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Step 1 Attendee Profile, Step 2 Session Track Selection, Step 3 Special Accessibility / Dietary notes.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-layer-group"></i> <?php esc_html_e( '3 Steps • 8 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-video"></i> <?php esc_html_e( 'Webinar', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 14: Workshop & Masterclass Enrollment -->
					<div class="wlgf-template-card" data-template="workshop" data-category="events" data-is-pro="1" data-keywords="workshop masterclass course training class education enrollment student 14 #14">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-graduation-cap"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#14</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Multi-Step', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Wizard', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">14. </span><?php esc_html_e( 'Workshop & Masterclass Enrollment', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Course registration with Participant Name, Experience Level, Workshop Slot, and Learning Goals.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '6 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-chalkboard-user"></i> <?php esc_html_e( 'Workshop', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 15: Restaurant Table & VIP Reservation -->
					<div class="wlgf-template-card" data-template="restaurant_vip" data-category="events" data-is-pro="1" data-keywords="restaurant table dining reservation vip booking party dinner food 15 #15">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-utensils"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#15</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'VIP Booking', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • VIP', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">15. </span><?php esc_html_e( 'Restaurant Table & VIP Reservation', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Table booking with Guest Count, Reservation Date/Time, Seating Area (Patio/Dining/VIP), and Requests.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '7 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-wine-glass"></i> <?php esc_html_e( 'Reservation', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 16: Community Volunteer Signup -->
					<div class="wlgf-template-card" data-template="volunteer" data-category="events" data-is-pro="1" data-keywords="volunteer community non profit charity event help signup team 16 #16">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#16</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Non-Profit', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Non-Profit', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">16. </span><?php esc_html_e( 'Community Volunteer Signup', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Onboard volunteers with Skill Checklist, Availability Slots (Weekdays/Weekends), and Emergency Contact.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '6 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-hands-holding-child"></i> <?php esc_html_e( 'Volunteer', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 17: Job Application & Resume -->
					<div class="wlgf-template-card" data-template="job_application" data-category="hr" data-is-pro="1" data-keywords="job application resume cv hiring recruitment hr career applicant file upload 17 #17">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-id-card-clip"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#17</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'File Upload', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • File Upload', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">17. </span><?php esc_html_e( 'Job Application & Resume Uploader', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Streamline recruiting with Applicant Info, Role Applied For, Portfolio URL, and Resume File Upload.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-paperclip"></i> <?php esc_html_e( '7 Fields • Upload', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-briefcase"></i> <?php esc_html_e( 'Careers', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 18: IT Support Ticket -->
					<div class="wlgf-template-card" data-template="support" data-category="hr" data-is-pro="1" data-keywords="support ticket bug helpdesk technical issue priority hardware software it 18 #18">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-headset"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#18</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Helpdesk', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Support', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">18. </span><?php esc_html_e( 'IT Helpdesk & Priority Support Ticket', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Technical ticketing with Severity Matrix, Affected System, Issue Description, and Error Logs.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-list-check"></i> <?php esc_html_e( '7 Fields • Upload', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-life-ring"></i> <?php esc_html_e( 'Support', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 19: Digital Product Order -->
					<div class="wlgf-template-card" data-template="digital_order" data-category="ecommerce" data-is-pro="1" data-keywords="order checkout ecommerce payment calculation digital download software license 19 #19">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-cart-shopping"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#19</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Instant Checkout', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Checkout', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">19. </span><?php esc_html_e( 'Digital Product Order & Instant Checkout', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Product package selection, License Tier, Dynamic Total Calculation, and Stripe / PayPal checkout.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-calculator"></i> <?php esc_html_e( 'Calc & Checkout', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-credit-card"></i> <?php esc_html_e( 'E-Commerce', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 20: Service Retainer Order -->
					<div class="wlgf-template-card" data-template="retainer" data-category="ecommerce" data-is-pro="1" data-keywords="retainer subscription recurring service monthly package billing contract order 20 #20">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#20</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Dynamic Pricing', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Calc', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">20. </span><?php esc_html_e( 'Monthly Service Retainer Agreement', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Client Retainer Plan, Monthly Hours slider, Calculation Badge, and Payment Method.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-calculator"></i> <?php esc_html_e( 'Dynamic Pricing', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-file-contract"></i> <?php esc_html_e( 'Retainer', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 21: Nonprofit Donation -->
					<div class="wlgf-template-card" data-template="donation" data-category="ecommerce" data-is-pro="1" data-keywords="donation charity non profit give fund contribution fundraise support 21 #21">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-heart-pulse"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#21</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Donations', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Checkout', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">21. </span><?php esc_html_e( 'Nonprofit Donation & Contribution', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Donor Details, Giving Tier radio presets ($25, $50, $100, Custom), and Dedication Message.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-hand-holding-dollar"></i> <?php esc_html_e( '6 Fields', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-heart"></i> <?php esc_html_e( 'Giving', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 22: Gym & Fitness Application (E-Signature) -->
					<div class="wlgf-template-card" data-template="gym_fitness" data-category="lead-gen" data-is-pro="1" data-keywords="gym fitness membership workout trainer signature waiver contract legal 22 #22">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-dumbbell"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#22</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--popular"><?php esc_html_e( 'E-Signature', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • E-Sign', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">22. </span><?php esc_html_e( 'Gym & Fitness Membership Application', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Membership tier selection, contact details, medical consent, and digital touch signature pad.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-pen-nib"></i> <?php esc_html_e( 'E-Signature', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-dumbbell"></i> <?php esc_html_e( 'Fitness', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 23: Dental & Medical Booking -->
					<div class="wlgf-template-card" data-template="dental_medical" data-category="events" data-is-pro="1" data-keywords="dental medical clinic doctor healthcare appointment patient teeth cleaning 23 #23">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-tooth"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#23</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Healthcare', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Medical', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">23. </span><?php esc_html_e( 'Dental & Medical Appointment Booking', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Patient appointment scheduling with treatment concern, date picker, and symptom notes.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-calendar-check"></i> <?php esc_html_e( 'Booking', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-user-doctor"></i> <?php esc_html_e( 'Medical', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 24: Website Cost Estimator (Math) -->
					<div class="wlgf-template-card" data-template="cost_estimator" data-category="ecommerce" data-is-pro="1" data-keywords="cost estimator calculator quote price math formula pages website software 24 #24">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-calculator"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#24</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Math Formula', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Calc', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">24. </span><?php esc_html_e( 'Website & Project Cost Estimator', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Interactive cost calculator with dynamic math formula, add-on checkboxes, and instant total quote.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-calculator"></i> <?php esc_html_e( 'Math Engine', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-coins"></i> <?php esc_html_e( 'Instant Quote', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>

					<!-- 25: Auto Repair & Service Quote -->
					<div class="wlgf-template-card" data-template="auto_repair" data-category="lead-gen" data-is-pro="1" data-keywords="auto car vehicle repair mechanic oil change brakes quote inspection photo 25 #25">
						<div class="wlgf-template-card__header">
							<div class="wlgf-template-card__icon"><i class="fa-solid fa-car-side"></i></div>
							<div class="wlgf-template-card__tags">
								<span class="wlgf-template-card__number">#25</span>
								<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
									<span class="wlgf-template-card__tag"><?php esc_html_e( 'Automotive', 'lead-generation-form' ); ?></span>
								<?php else : ?>
									<span class="wlgf-template-card__tag wlgf-template-card__tag--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'PRO • Upload', 'lead-generation-form' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<h4 class="wlgf-template-card__title"><span class="wlgf-template-card__title-num">25. </span><?php esc_html_e( 'Automotive Repair & Service Quote', 'lead-generation-form' ); ?></h4>
						<p class="wlgf-template-card__desc"><?php esc_html_e( 'Vehicle make/model, repair service dropdown, photo upload of issue, and instant quote dispatch.', 'lead-generation-form' ); ?></p>
						<div class="wlgf-template-card__meta">
							<span><i class="fa-solid fa-wrench"></i> <?php esc_html_e( 'Quote', 'lead-generation-form' ); ?></span>
							<span><i class="fa-solid fa-car"></i> <?php esc_html_e( 'Automotive', 'lead-generation-form' ); ?></span>
						</div>
						<?php if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) : ?>
							<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm wlgf-template-card__btn"><?php esc_html_e( 'Use Template', 'lead-generation-form' ); ?></button>
						<?php else : ?>
							<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-template-card__btn wlgf-template-card__btn--pro"><i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Unlock with Pro', 'lead-generation-form' ); ?></button>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Live Preview Modal Dialog -->
	<div id="wlgf-preview-modal" class="wlgf-modal-overlay wlgf-d-none">
		<div class="wlgf-modal wlgf-modal--preview">
			<div class="wlgf-modal__header">
				<div class="wlgf-modal__icon">
					<i class="fa-solid fa-eye"></i>
				</div>
				<h3 class="wlgf-modal__title"><?php esc_html_e( 'Live Form Preview', 'lead-generation-form' ); ?></h3>
				<div class="wlgf-preview-device-switcher">
					<button type="button" class="wlgf-device-btn active" data-device="desktop" title="<?php esc_attr_e( 'Desktop View (100%)', 'lead-generation-form' ); ?>"><i class="fa-solid fa-desktop"></i> <span><?php esc_html_e( 'Desktop', 'lead-generation-form' ); ?></span></button>
					<button type="button" class="wlgf-device-btn" data-device="tablet" title="<?php esc_attr_e( 'Tablet View (768px)', 'lead-generation-form' ); ?>"><i class="fa-solid fa-tablet-screen-button"></i> <span><?php esc_html_e( 'Tablet', 'lead-generation-form' ); ?></span></button>
					<button type="button" class="wlgf-device-btn" data-device="mobile" title="<?php esc_attr_e( 'Mobile View (375px)', 'lead-generation-form' ); ?>"><i class="fa-solid fa-mobile-screen-button"></i> <span><?php esc_html_e( 'Mobile', 'lead-generation-form' ); ?></span></button>
				</div>
				<button type="button" class="wlgf-modal__close-btn" id="wlgf-close-preview-btn" title="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>" aria-label="<?php esc_attr_e( 'Close', 'lead-generation-form' ); ?>"><i class="fa-solid fa-xmark"></i></button>
			</div>
			<div class="wlgf-preview-body">
				<div class="wlgf-preview-card" id="wlgf-preview-render-mount">
					<!-- Dynamic Form Elements Rendered Here -->
				</div>
			</div>
			<div class="wlgf-modal__footer">
				<button type="button" class="wlgf-btn wlgf-btn--secondary" id="wlgf-close-preview-footer-btn"><?php esc_html_e( 'Close Preview', 'lead-generation-form' ); ?></button>
			</div>
		</div>
	</div>

	<!-- Pro Feature Preview Modal Dialog -->
	<div id="wlgf-pro-feature-modal" class="wlgf-modal-overlay wlgf-d-none">
		<div class="wlgf-modal wlgf-pro-feature-modal-dialog">
			<div class="wlgf-pro-feature-modal__icon">
				<i class="fa-solid fa-crown"></i>
			</div>
			<h3 class="wlgf-pro-feature-modal__title" id="wlgf-pro-modal-title"><?php esc_html_e( 'Unlock Pro Template & Advanced Controls', 'lead-generation-form' ); ?></h3>
			<p class="wlgf-pro-feature-modal__desc">
				<?php esc_html_e( 'This high-converting funnel requires Lead Generation Form Pro to unlock interactive wizards, dynamic math calculations, live payments, and unlimited lead capture.', 'lead-generation-form' ); ?>
			</p>
			<ul class="wlgf-pro-feature-modal__list">
				<li><i class="fa-solid fa-circle-check"></i> <?php esc_html_e( 'Interactive Multi-Step Form Wizards & Progress Bars', 'lead-generation-form' ); ?></li>
				<li><i class="fa-solid fa-circle-check"></i> <?php esc_html_e( 'Real-Time Dynamic Math Calculations & Price Estimators', 'lead-generation-form' ); ?></li>
				<li><i class="fa-solid fa-circle-check"></i> <?php esc_html_e( 'Live Stripe & PayPal Payments with Instant Checkout', 'lead-generation-form' ); ?></li>
				<li><i class="fa-solid fa-circle-check"></i> <?php esc_html_e( 'Unlimited Lead Submissions & CSV Spreadsheet Exports', 'lead-generation-form' ); ?></li>
			</ul>
			<div class="wlgf-btn-group wlgf-justify-center">
				<a href="https://wpfrank.com/wordpress-plugins/lead-generation-form-pro/" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--primary wlgf-btn--lg">
					<i class="fa-solid fa-rocket"></i> <?php esc_html_e( 'Get Lead Generation Form Pro', 'lead-generation-form' ); ?>
				</a>
				<button type="button" class="wlgf-btn wlgf-btn--secondary" id="wlgf-close-pro-modal-btn">
					<?php esc_html_e( 'Close', 'lead-generation-form' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>