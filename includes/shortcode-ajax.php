<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

/**
 * Dispatch newsletter subscriber to configured ESP (Mailchimp / Brevo / MailerLite)
 * or local CRM with fail-safe rolling log. (PRO-008 / CRIT-3 / R1 / R7)
 */
function wlgf_dispatch_newsletter_sync( $wlgf_form_id, $wlgf_form_info, $sanitized_data, $lead_index = null ) {
	$settings = get_option( 'wlgf_settings', array() );
	$provider = isset( $settings['newsletter_provider'] ) ? sanitize_text_field( $settings['newsletter_provider'] ) : 'local';

	// Edition downgrade guard: If on Free and provider is not local or mailchimp, downgrade to local_only
	if ( ( ! defined( 'WLGF_IS_PRO' ) || ! WLGF_IS_PRO ) && ! in_array( $provider, array( 'local', 'mailchimp' ), true ) ) {
		$provider = 'local';
	}

	if ( 'local' === $provider || empty( $provider ) ) {
		return array( 'status' => 'local_only', 'message' => 'Stored in local CRM' );
	}

	// Auto-detect email and name from submission
	$email = '';
	$name  = '';
	foreach ( $sanitized_data as $key => $val ) {
		if ( is_string( $val ) ) {
			if ( empty( $email ) && ( strcasecmp( $key, 'email' ) === 0 || false !== strpos( strtolower( $key ), 'email' ) || is_email( $val ) ) ) {
				if ( is_email( $val ) ) {
					$email = sanitize_email( $val );
				}
			}
			if ( empty( $name ) && ( strcasecmp( $key, 'name' ) === 0 || false !== strpos( strtolower( $key ), 'name' ) ) ) {
				$name = sanitize_text_field( $val );
			}
		}
	}

	if ( empty( $email ) ) {
		return array( 'status' => 'failed', 'message' => 'No valid email field found' );
	}

	$name_parts = ! empty( $name ) ? explode( ' ', $name, 2 ) : array( '', '' );
	$first_name = $name_parts[0];
	$last_name  = isset( $name_parts[1] ) ? $name_parts[1] : '';
	$form_tag   = ! empty( $wlgf_form_info['newsletter_tag'] ) ? sanitize_text_field( $wlgf_form_info['newsletter_tag'] ) : '';

	$sync_status = 'pending';
	$resp_msg    = '';

	if ( 'mailchimp' === $provider ) {
		$api_key = isset( $settings['mailchimp_api_key'] ) ? $settings['mailchimp_api_key'] : '';
		$list_id = isset( $settings['mailchimp_list_id'] ) ? $settings['mailchimp_list_id'] : '';
		$double_optin = isset( $settings['mailchimp_double_optin'] ) && '1' === (string) $settings['mailchimp_double_optin'];

		if ( empty( $api_key ) || empty( $list_id ) ) {
			$sync_status = 'failed: missing api_key or list_id';
			$resp_msg    = 'Missing Mailchimp credentials';
		} else {
			$key_parts = explode( '-', $api_key );
			$dc        = ( count( $key_parts ) > 1 ) ? end( $key_parts ) : 'us1';
			$md5_email = md5( strtolower( trim( $email ) ) );
			$url       = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$md5_email}";

			$body = array(
				'email_address' => $email,
				'status_if_new' => $double_optin ? 'pending' : 'subscribed',
				'merge_fields'  => array(
					'FNAME' => $first_name,
					'LNAME' => $last_name,
				),
			);
			if ( ! empty( $form_tag ) ) {
				$body['tags'] = array( $form_tag );
			}

			$response = wp_remote_request(
				$url,
				array(
					'method'  => 'PUT',
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ),
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $body ),
					'timeout' => 3,
				)
			);

			if ( is_wp_error( $response ) ) {
				$sync_status = 'failed: ' . $response->get_error_message();
				$resp_msg    = $response->get_error_message();
			} else {
				$code = wp_remote_retrieve_response_code( $response );
				if ( in_array( $code, array( 200, 204 ), true ) ) {
					$sync_status = 'synced';
					$resp_msg    = 'Synced successfully to Mailchimp';
				} else {
					$res_body = json_decode( wp_remote_retrieve_body( $response ), true );
					$sync_status = 'failed: HTTP ' . $code;
					$resp_msg    = isset( $res_body['detail'] ) ? $res_body['detail'] : "HTTP {$code}";
				}
			}
		}
	} elseif ( 'brevo' === $provider && defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
		$api_key = isset( $settings['brevo_api_key'] ) ? $settings['brevo_api_key'] : '';
		$list_id = isset( $settings['brevo_list_id'] ) ? intval( $settings['brevo_list_id'] ) : 0;

		if ( empty( $api_key ) || empty( $list_id ) ) {
			$sync_status = 'failed: missing api_key or list_id';
			$resp_msg    = 'Missing Brevo credentials';
		} else {
			$url  = 'https://api.brevo.com/v3/contacts';
			$body = array(
				'email'         => $email,
				'attributes'    => array(
					'FIRSTNAME' => $first_name,
					'LASTNAME'  => $last_name,
				),
				'listIds'       => array( $list_id ),
				'updateEnabled' => true,
			);

			$response = wp_remote_post(
				$url,
				array(
					'headers' => array(
						'api-key'      => $api_key,
						'Content-Type' => 'application/json',
					),
					'body'    => wp_json_encode( $body ),
					'timeout' => 3,
				)
			);

			if ( is_wp_error( $response ) ) {
				$sync_status = 'failed: ' . $response->get_error_message();
				$resp_msg    = $response->get_error_message();
			} else {
				$code = wp_remote_retrieve_response_code( $response );
				if ( in_array( $code, array( 200, 201, 204 ), true ) ) {
					$sync_status = 'synced';
					$resp_msg    = 'Synced successfully to Brevo';
				} else {
					$res_body = json_decode( wp_remote_retrieve_body( $response ), true );
					$sync_status = 'failed: HTTP ' . $code;
					$resp_msg    = isset( $res_body['message'] ) ? $res_body['message'] : "HTTP {$code}";
				}
			}
		}
	} elseif ( 'mailerlite' === $provider && defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
		$api_key  = isset( $settings['mailerlite_api_key'] ) ? $settings['mailerlite_api_key'] : '';
		$group_id = isset( $settings['mailerlite_group_id'] ) ? $settings['mailerlite_group_id'] : '';

		if ( empty( $api_key ) ) {
			$sync_status = 'failed: missing api_token';
			$resp_msg    = 'Missing MailerLite token';
		} else {
			$url  = 'https://connect.mailerlite.com/api/subscribers';
			$body = array(
				'email'  => $email,
				'fields' => array(
					'name'      => $first_name,
					'last_name' => $last_name,
				),
			);
			if ( ! empty( $group_id ) ) {
				$body['groups'] = array( $group_id );
			}

			$response = wp_remote_post(
				$url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $body ),
					'timeout' => 3,
				)
			);

			if ( is_wp_error( $response ) ) {
				$sync_status = 'failed: ' . $response->get_error_message();
				$resp_msg    = $response->get_error_message();
			} else {
				$code = wp_remote_retrieve_response_code( $response );
				if ( in_array( $code, array( 200, 201 ), true ) ) {
					$sync_status = 'synced';
					$resp_msg    = 'Synced successfully to MailerLite';
				} else {
					$res_body = json_decode( wp_remote_retrieve_body( $response ), true );
					$sync_status = 'failed: HTTP ' . $code;
					$resp_msg    = isset( $res_body['message'] ) ? $res_body['message'] : "HTTP {$code}";
				}
			}
		}
	}

	// Rolling 50-entry log (CRIT-3 / R1)
	$log_entry = array(
		'timestamp' => current_time( 'mysql' ),
		'form_id'   => $wlgf_form_id,
		'email'     => $email,
		'provider'  => $provider,
		'status'    => $sync_status,
		'response'  => $resp_msg,
	);
	$log = get_option( 'wlgf_newsletter_dispatch_log', array() );
	if ( ! is_array( $log ) ) {
		$log = array();
	}
	array_unshift( $log, $log_entry );
	if ( count( $log ) > 50 ) {
		$log = array_slice( $log, 0, 50 );
	}
	update_option( 'wlgf_newsletter_dispatch_log', $log, false );

	// Update lead record with final sync status
	if ( null !== $lead_index ) {
		$stored_leads = get_option( 'wlgf_saved_form_data_' . $wlgf_form_id, array() );
		if ( is_array( $stored_leads ) && isset( $stored_leads[ $lead_index ] ) ) {
			$stored_leads[ $lead_index ]['_newsletter_status'] = $sync_status;
			update_option( 'wlgf_saved_form_data_' . $wlgf_form_id, $stored_leads );
		}
	}

	return array( 'status' => $sync_status, 'message' => $resp_msg );
}

/**
 * Validate that all required fields in the form definition have been submitted.
 * Returns array('valid' => true) or array('valid' => false, 'message' => string)
 */
function wlgf_validate_required_submission_fields( $form_fields, $post_data, $files_data = array() ) {
	if ( ! is_array( $form_fields ) ) {
		return array( 'valid' => true );
	}

	foreach ( $form_fields as $fld ) {
		if ( ! is_array( $fld ) ) {
			continue;
		}

		$type   = isset( $fld['type'] ) ? $fld['type'] : '';
		$name   = isset( $fld['name'] ) ? $fld['name'] : '';
		$label  = isset( $fld['label'] ) ? $fld['label'] : $name;
		$is_req = ! empty( $fld['required'] ) && ( true === $fld['required'] || 1 == $fld['required'] || 'true' === $fld['required'] || '1' === $fld['required'] );

		// Ignore display-only non-input elements
		if ( in_array( $type, array( 'header', 'paragraph', 'divider', 'step-break', 'button', 'hidden' ), true ) ) {
			continue;
		}

		if ( empty( $name ) ) {
			continue;
		}

		// 1. File input validation (Required check + allowed types + size check)
		if ( 'file' === $type ) {
			$has_file = false;
			if ( isset( $files_data[ $name ] ) ) {
				if ( is_array( $files_data[ $name ]['error'] ) ) {
					foreach ( $files_data[ $name ]['error'] as $err ) {
						if ( (int) $err === UPLOAD_ERR_OK ) {
							$has_file = true;
							break;
						}
					}
				} elseif ( (int) $files_data[ $name ]['error'] === UPLOAD_ERR_OK ) {
					$has_file = true;
				}
			}
			if ( $is_req && ! $has_file ) {
				return array(
					'valid'   => false,
					'message' => sprintf( __( 'Field "%s" is required.', 'lead-generation-form' ), wp_strip_all_tags( $label ) ),
				);
			}

			// Validate allowed file extensions and file size if file was provided
			if ( $has_file ) {
				$raw_allowed_types = ! empty( $fld['allowed_types'] ) ? (string) $fld['allowed_types'] : 'pdf, doc, docx, png, jpg';
				$allowed_exts = array_filter( array_map( 'trim', explode( ',', str_replace( array( '.', ' ' ), array( '', '' ), strtolower( $raw_allowed_types ) ) ) ) );
				if ( empty( $allowed_exts ) ) {
					$allowed_exts = array( 'pdf', 'doc', 'docx', 'png', 'jpg' );
				}

				$max_size_mb = ( isset( $fld['max_file_size'] ) && '' !== (string) $fld['max_file_size'] && floatval( $fld['max_file_size'] ) > 0 )
					? floatval( $fld['max_file_size'] )
					: ( ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ? 50 : 5 );
				$max_size_bytes = intval( $max_size_mb * 1024 * 1024 );

				$dangerous_exts = array( 'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'cgi', 'pl', 'py', 'exe', 'sh', 'bat', 'cmd', 'js', 'vbs' );

				$file_names  = is_array( $files_data[ $name ]['name'] ) ? $files_data[ $name ]['name'] : array( $files_data[ $name ]['name'] );
				$file_sizes  = is_array( $files_data[ $name ]['size'] ) ? $files_data[ $name ]['size'] : array( $files_data[ $name ]['size'] );
				$file_errors = is_array( $files_data[ $name ]['error'] ) ? $files_data[ $name ]['error'] : array( $files_data[ $name ]['error'] );

				$total_size = 0;
				for ( $i = 0; $i < count( $file_names ); $i++ ) {
					if ( (int) $file_errors[ $i ] !== UPLOAD_ERR_OK || empty( $file_names[ $i ] ) ) {
						continue;
					}
					$ext = strtolower( pathinfo( $file_names[ $i ], PATHINFO_EXTENSION ) );
					if ( in_array( $ext, $dangerous_exts, true ) || ! in_array( $ext, $allowed_exts, true ) ) {
						return array(
							'valid'   => false,
							'message' => sprintf( __( 'File type ".%1$s" is not allowed for "%2$s". Allowed formats: %3$s', 'lead-generation-form' ), esc_html( $ext ), wp_strip_all_tags( $label ), strtoupper( implode( ', ', $allowed_exts ) ) ),
						);
					}
					$total_size += (int) $file_sizes[ $i ];
				}

				if ( $total_size > $max_size_bytes ) {
					return array(
						'valid'   => false,
						'message' => sprintf( __( 'File size exceeds maximum allowed limit of %1$sMB for "%2$s".', 'lead-generation-form' ), esc_html( $max_size_mb ), wp_strip_all_tags( $label ) ),
					);
				}
			}
			continue;
		}

		if ( ! $is_req ) {
			continue;
		}

		// 2. Checkbox group validation
		if ( 'checkbox-group' === $type ) {
			$has_checked = false;
			if ( isset( $post_data[ $name ] ) && ! empty( $post_data[ $name ] ) ) {
				$has_checked = true;
			} else {
				foreach ( $post_data as $k => $v ) {
					if ( strpos( $k, $name . '[' ) === 0 && ! empty( $v ) ) {
						$has_checked = true;
						break;
					}
				}
			}
			if ( ! $has_checked ) {
				return array(
					'valid'   => false,
					'message' => sprintf( __( 'Field "%s" is required.', 'lead-generation-form' ), wp_strip_all_tags( $label ) ),
				);
			}
			continue;
		}

		// 3. All other fields: text, textarea, select, radio-group, star-rating, nps-matrix, terms, etc.
		$val = isset( $post_data[ $name ] ) ? $post_data[ $name ] : null;
		$is_empty = false;
		if ( null === $val ) {
			$is_empty = true;
		} elseif ( is_array( $val ) ) {
			$is_empty = empty( $val );
		} else {
			// Note: strlen check ensures score "0" in NPS matrix is treated as filled!
			$is_empty = ( strlen( trim( (string) $val ) ) === 0 );
		}

		if ( $is_empty ) {
			return array(
				'valid'   => false,
				'message' => sprintf( __( 'Field "%s" is required.', 'lead-generation-form' ), wp_strip_all_tags( $label ) ),
			);
		}
	}

	return array( 'valid' => true );
}

/**
 * --------------------------------------------------------------------------
 * Server-Side Payment Verification & Gateway Processing (PRO-016)
 * --------------------------------------------------------------------------
 */

function wlgf_create_stripe_payment_intent_callback() {
	wp_send_json_error( array( 'message' => __( 'Payment collection is available in Lead Generation Form Pro.', 'lead-generation-form' ) ) );
}
add_action( 'wp_ajax_wlgf_create_stripe_payment_intent', 'wlgf_create_stripe_payment_intent_callback' );
add_action( 'wp_ajax_nopriv_wlgf_create_stripe_payment_intent', 'wlgf_create_stripe_payment_intent_callback' );

function wlgf_verify_form_payment( $wlgf_form_id, $wlgf_form_info, $wlgf_settings, &$sanitized_data ) {
	unset( $sanitized_data['wlgf_payment_transaction_id'] );
	unset( $sanitized_data['wlgf_payment_status'] );
	unset( $sanitized_data['wlgf_payment_amount_paid'] );
	unset( $sanitized_data['wlgf_payment_gateway'] );
	return array( 'success' => true );
}

add_shortcode( 'WLFG', 'wlgf_shortcode_callback' );
add_shortcode( 'WLGF', 'wlgf_shortcode_callback' );
add_shortcode( 'wlgf_form', 'wlgf_shortcode_callback' );
function wlgf_shortcode_callback( $atts ) {
	if ( isset( $atts['id'] ) ) {
          ob_start();
          
          // get form id and load form structure
          $wlgf_form_id = sanitize_text_field($atts['id']);
		$wlgf_form_record = get_option('wlgf_form_'.$wlgf_form_id);
		if ( empty( $wlgf_form_record ) || ! is_array( $wlgf_form_record ) ) {
			ob_end_clean();
			if ( current_user_can( 'manage_options' ) ) {
				$manage_url = admin_url( 'admin.php?page=wlgf-manage-forms' );
				return '<div class="wlgf-container wlgf-my-3"><div class="wlgf-alert wlgf-alert--warning"><i class="fa-solid fa-triangle-exclamation"></i> <div><strong>' . esc_html__( 'Lead Generation Form Notice:', 'lead-generation-form' ) . '</strong> ' . sprintf( esc_html__( 'Form #%s does not exist or has been deleted. Please check your form shortcode in %sManage Forms%s.', 'lead-generation-form' ), esc_html( $wlgf_form_id ), '<a href="' . esc_url( $manage_url ) . '">', '</a>' ) . '</div></div></div>';
			}
			return '';
		}

		$wlgf_form_info = $wlgf_form_record;
		$raw_form       = isset( $wlgf_form_record['form'] ) ? $wlgf_form_record['form'] : '[]';
		if ( is_array( $raw_form ) ) {
			$wlgf_form_data = json_decode( wp_json_encode( $raw_form ), true );
		} elseif ( is_string( $raw_form ) ) {
			$wlgf_form_data = json_decode( $raw_form, true );
		} else {
			$wlgf_form_data = array();
		}
		if ( ! is_array( $wlgf_form_data ) ) {
			$wlgf_form_data = array();
		}
          
          // defaults
          $wlgf_blacklist_flag = 0;
          $wlgf_form_nonce = wp_create_nonce( 'wlgf-form-post' );
          $wlgf_checkbox_count = 0;
		$wlgf_checkbox_fields = array();
		$wlgf_cb_names = array();
		$wlgf_tooltip = array(
		    'span' => array(
			   'class' => array(),
			   'data-bs-toggle' => array(),
			   'data-bs-placement' => array(),
			   'data-wlgf-tooltip' => array(),
			   'title' => array()
		    )
		);
          $wlgf_loader = plugin_dir_url(__FILE__) . 'assets/img/wlgf-loader.gif';
          
          // custom parameters - add to reply, blacklist
		$wlgf_blacklist = (isset($atts['blacklist'])) ? sanitize_text_field($atts['blacklist']) : sanitize_text_field("not-match");
		$wlgf_AddReplyToFieldName = (isset($atts['reply_field_name'])) ? sanitize_text_field($atts['reply_field_name']) : "";
		$wlgf_AddReplyToFieldEmail = (isset($atts['reply_field_email'])) ? sanitize_text_field($atts['reply_field_email']) : "";
		$wlgf_carbon_copy = (isset($atts['carbon_copy']) && defined('WLGF_IS_PRO') && WLGF_IS_PRO) ? sanitize_text_field($atts['carbon_copy']) : "";
		$wlgf_settings = get_option('wlgf_settings'); //load settings
		$wlgf_recaptcha = (isset($wlgf_settings['recaptcha'])) ? sanitize_text_field($wlgf_settings['recaptcha']) : 2;
		$wlgf_captcha_provider = isset($wlgf_settings['captcha_provider']) ? sanitize_text_field($wlgf_settings['captcha_provider']) : (($wlgf_recaptcha == '1') ? 'recaptcha' : (($wlgf_recaptcha == '3') ? 'turnstile' : 'none'));
		$wlgf_sitekey = (isset($wlgf_settings['sitekey'])) ? sanitize_text_field($wlgf_settings['sitekey']) : '';
		$wlgf_secretkey = (isset($wlgf_settings['secretkey'])) ? sanitize_text_field($wlgf_settings['secretkey']) : '';
		$wlgf_turnstile_sitekey = isset($wlgf_settings['turnstile_sitekey']) ? sanitize_text_field($wlgf_settings['turnstile_sitekey']) : '';
		$wlgf_turnstile_secretkey = isset($wlgf_settings['turnstile_secretkey']) ? sanitize_text_field($wlgf_settings['turnstile_secretkey']) : '';
		$wlgf_user_message = (isset($wlgf_settings['user_message'])) ? sanitize_text_field($wlgf_settings['user_message']) : '';
		$wlgf_error_message = '';
          
          // load plugin styles and scripts
          wp_enqueue_style('wlgf-shortcode-form');
          wp_enqueue_style('wlgf-fontawesome-css');
		wp_enqueue_script('jquery');

		$is_stripe_enabled   = !empty($wlgf_settings['stripe_enabled']) && 1 === (int)$wlgf_settings['stripe_enabled'];
		$stripe_test_mode    = !empty($wlgf_settings['stripe_test_mode']) && 1 === (int)$wlgf_settings['stripe_test_mode'];
		$stripe_pub_key      = $stripe_test_mode ? (isset($wlgf_settings['stripe_test_pub_key']) ? $wlgf_settings['stripe_test_pub_key'] : '') : (isset($wlgf_settings['stripe_live_pub_key']) ? $wlgf_settings['stripe_live_pub_key'] : '');
		$is_paypal_enabled   = !empty($wlgf_settings['paypal_enabled']) && 1 === (int)$wlgf_settings['paypal_enabled'];
		$paypal_mode         = isset($wlgf_settings['paypal_mode']) ? $wlgf_settings['paypal_mode'] : 'sandbox';
		$paypal_client_id    = ('sandbox' === $paypal_mode) ? (isset($wlgf_settings['paypal_sandbox_client_id']) ? $wlgf_settings['paypal_sandbox_client_id'] : '') : (isset($wlgf_settings['paypal_live_client_id']) ? $wlgf_settings['paypal_live_client_id'] : '');
		$payment_currency    = !empty($wlgf_settings['payment_currency']) ? $wlgf_settings['payment_currency'] : 'USD';

		$is_payments_enabled = ( !empty($wlgf_settings['enable_payments']) && 1 === (int)$wlgf_settings['enable_payments'] )
			|| ( $is_stripe_enabled && !empty($stripe_pub_key) )
			|| ( $is_paypal_enabled && !empty($paypal_client_id) );
		$is_form_payment     = !empty($wlgf_form_info['payment_enabled']) && 1 === (int)$wlgf_form_info['payment_enabled'];

		wp_enqueue_script( 'wlgf-dialog-js' );
		wp_enqueue_style( 'wlgf-flatpickr-css' );
		wp_enqueue_script( 'wlgf-flatpickr-js' );
		$form_js_deps = array( 'jquery', 'wlgf-dialog-js', 'wlgf-flatpickr-js' );
		if ($is_payments_enabled && $is_form_payment && (defined('WLGF_IS_PRO') && WLGF_IS_PRO)) {
			if ($is_stripe_enabled && !empty($stripe_pub_key)) {
				wp_enqueue_script('wlgf-stripe-js', 'https://js.stripe.com/v3/', array(), null, true);
				$form_js_deps[] = 'wlgf-stripe-js';
			}
			if ($is_paypal_enabled && !empty($paypal_client_id)) {
				wp_enqueue_script('wlgf-paypal-sdk', 'https://www.paypal.com/sdk/js?client-id=' . esc_attr($paypal_client_id) . '&currency=' . esc_attr($payment_currency), array(), null, true);
				$form_js_deps[] = 'wlgf-paypal-sdk';
			}
		}

		$wlgf_form_js_ver = file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/wlgf-shortcode-form.js' ) ? ( defined('WLGF_PLUGIN_VERSION') ? WLGF_PLUGIN_VERSION : '1.3.1' ) . '.' . filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/wlgf-shortcode-form.js' ) : null;
		wp_enqueue_script('wlgf-shortcode-form-js', plugin_dir_url(__FILE__). 'assets/js/wlgf-shortcode-form.js', $form_js_deps, $wlgf_form_js_ver, true); // load in footer

		$wlgf_shortcode_config = array(
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'FormId'           => (string) $wlgf_form_id,
			'Recaptcha'        => (string) $wlgf_recaptcha,
			'SiteKey'          => (string) $wlgf_sitekey,
			'IsPro'            => defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO,
			'Conditions'       => ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO && isset( $wlgf_form_info['conditions'] ) && is_array( $wlgf_form_info['conditions'] ) ) ? $wlgf_form_info['conditions'] : array(),
			'PaymentEnabled'   => ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) && $is_payments_enabled && $is_form_payment,
			'PaymentType'      => isset( $wlgf_form_info['payment_type'] ) ? $wlgf_form_info['payment_type'] : 'fixed',
			'PaymentAmount'    => isset( $wlgf_form_info['payment_amount'] ) ? $wlgf_form_info['payment_amount'] : '0.00',
			'PaymentCalcField' => isset( $wlgf_form_info['payment_calc_field'] ) ? $wlgf_form_info['payment_calc_field'] : '',
			'PaymentCurrency'  => $payment_currency,
			'StripePubKey'     => ( $is_stripe_enabled ) ? $stripe_pub_key : '',
			'PayPalClientId'   => ( $is_paypal_enabled ) ? $paypal_client_id : '',
			'FadeoutSeconds'   => isset( $wlgf_settings['confirmation_fadeout'] ) ? max( 5, min( 50, intval( $wlgf_settings['confirmation_fadeout'] ) ) ) : 10,
		);

		$wlgf_ajax_config = array(
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'FormId'           => (string) $wlgf_form_id,
			'UserMsg'          => (string) $wlgf_user_message,
			'ConfirmationHTML' => function_exists( 'wlgf_get_confirmation_html' ) ? wlgf_get_confirmation_html( (string) $wlgf_user_message, $wlgf_settings ) : '',
			'FadeoutSeconds'   => isset( $wlgf_settings['confirmation_fadeout'] ) ? max( 5, min( 50, intval( $wlgf_settings['confirmation_fadeout'] ) ) ) : 10,
		);

		$wlgf_encoded_shortcode = wp_json_encode( $wlgf_shortcode_config );
		$wlgf_encoded_ajax      = wp_json_encode( $wlgf_ajax_config );
		$form_id_safe           = sanitize_key( $wlgf_form_id );

		wp_add_inline_script(
			'wlgf-shortcode-form-js',
			"window.WLGFShortcodeData = window.WLGFShortcodeData || {}; window.WLGFShortcodeData['{$form_id_safe}'] = {$wlgf_encoded_shortcode}; window.WLGFShortcode = window.WLGFShortcodeData['{$form_id_safe}'];",
			'before'
		);

		wp_enqueue_script( 'wlgf-shortcode-ajax-script' );
		wp_add_inline_script(
			'wlgf-shortcode-form-js',
			"window.WLGFAjaxData = window.WLGFAjaxData || {}; window.WLGFAjaxData['{$form_id_safe}'] = {$wlgf_encoded_ajax}; window.WLGFAjax = window.WLGFAjaxData['{$form_id_safe}'];",
			'before'
		);
          
		if ( 'turnstile' === $wlgf_captcha_provider && $wlgf_turnstile_sitekey && $wlgf_turnstile_secretkey ) {
			wp_enqueue_script( 'wlgf-cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );
			$wlgf_form_data[] = array( 'type' => 'turnstile', 'sitekey' => $wlgf_turnstile_sitekey );
		} elseif ( ( 'recaptcha' === $wlgf_captcha_provider || $wlgf_recaptcha == 1 ) && $wlgf_sitekey && $wlgf_secretkey ) {
			wp_enqueue_script( 'wlgf-google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . esc_attr($wlgf_sitekey), array(), null, true );
			$wlgf_form_data[] = array( 'type' => 'recaptcha', 'sitekey' => $wlgf_sitekey );
		}
		
          // Form Scheduling & Submission Quotas Check (PRO-009)
          $now_timestamp   = current_time( 'timestamp' );
          $schedule_start  = ! empty( $wlgf_form_info['schedule_start'] ) ? strtotime( $wlgf_form_info['schedule_start'] ) : false;
          $schedule_end    = ! empty( $wlgf_form_info['schedule_end'] ) ? strtotime( $wlgf_form_info['schedule_end'] ) : false;
          $max_submissions = ! empty( $wlgf_form_info['max_submissions'] ) ? intval( $wlgf_form_info['max_submissions'] ) : 0;
          $closed_msg      = ! empty( $wlgf_form_info['closed_message'] ) ? $wlgf_form_info['closed_message'] : __( 'This form is currently closed for new submissions.', 'lead-generation-form' );

          if ( $schedule_start && $now_timestamp < $schedule_start ) {
              $start_date_formatted = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $schedule_start );
              return '<div class="wlgf-container"><div class="wlgf-alert wlgf-alert--warning"><i class="fa-solid fa-clock"></i> ' . sprintf( esc_html__( 'This form will open for submissions on %s.', 'lead-generation-form' ), esc_html( $start_date_formatted ) ) . '</div></div>';
          }

          if ( $schedule_end && $now_timestamp > $schedule_end ) {
              return '<div class="wlgf-container"><div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $closed_msg ) . '</div></div>';
          }

          if ( $max_submissions > 0 ) {
              $existing_leads = get_option( 'wlgf_saved_form_data_' . $wlgf_form_id, array() );
              if ( is_array( $existing_leads ) && count( $existing_leads ) >= $max_submissions ) {
                  return '<div class="wlgf-container"><div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $closed_msg ) . '</div></div>';
              }
          }

          // Frontend Impression Tracking is handled via client-side beacon in wlgf-shortcode-form.js (PRO-010/PRO-017)

          // form structure output start
          $theme_primary     = ( isset( $wlgf_form_info['theme_primary'] ) && ! empty( $wlgf_form_info['theme_primary'] ) ) ? sanitize_hex_color( $wlgf_form_info['theme_primary'] ) : '#2563eb';
          $theme_radius      = isset( $wlgf_form_info['theme_radius'] ) ? sanitize_text_field( $wlgf_form_info['theme_radius'] ) : '6px';
          $theme_bg          = isset( $wlgf_form_info['theme_bg'] ) ? sanitize_text_field( $wlgf_form_info['theme_bg'] ) : 'card';
          $theme_font        = isset( $wlgf_form_info['theme_font'] ) ? sanitize_text_field( $wlgf_form_info['theme_font'] ) : 'inherit';
          $theme_btn_width   = isset( $wlgf_form_info['theme_btn_width'] ) ? sanitize_text_field( $wlgf_form_info['theme_btn_width'] ) : 'auto';
          $theme_field_size  = isset( $wlgf_form_info['theme_field_size'] ) ? sanitize_text_field( $wlgf_form_info['theme_field_size'] ) : 'default';
          $theme_mode        = isset( $wlgf_form_info['theme_mode'] ) ? sanitize_text_field( $wlgf_form_info['theme_mode'] ) : 'light';
          $theme_input_style = isset( $wlgf_form_info['theme_input_style'] ) ? sanitize_text_field( $wlgf_form_info['theme_input_style'] ) : 'bordered';
          $theme_max_width   = ( isset( $wlgf_form_info['theme_max_width'] ) && ! empty( $wlgf_form_info['theme_max_width'] ) ) ? sanitize_text_field( $wlgf_form_info['theme_max_width'] ) : '640px';
          $theme_btn_shadow  = isset( $wlgf_form_info['theme_btn_shadow'] ) ? sanitize_text_field( $wlgf_form_info['theme_btn_shadow'] ) : 'default';
          $theme_label_style = isset( $wlgf_form_info['theme_label_style'] ) ? sanitize_text_field( $wlgf_form_info['theme_label_style'] ) : 'default';

          // Multi-Step Wizard Configuration (PRO-002)
          $wlgf_step_breaks = 0;
          $step_titles      = array();
          if ( is_array( $wlgf_form_data ) ) {
              foreach ( $wlgf_form_data as $f ) {
                  if ( isset( $f['type'] ) && 'step-break' === $f['type'] ) {
                      $wlgf_step_breaks++;
                      $step_titles[] = ! empty( $f['label'] ) ? $f['label'] : sprintf( __( 'Step %d', 'lead-generation-form' ), $wlgf_step_breaks );
                  }
              }
          }
          $is_pro           = defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO;
          $has_multi_step   = ( $wlgf_step_breaks > 0 && $is_pro );
          $total_steps      = $wlgf_step_breaks + 1;
          $current_step     = 1;
          $wizard_style     = isset( $wlgf_form_info['wizard_style'] ) ? $wlgf_form_info['wizard_style'] : 'circles';
          $wizard_step_1_title = ! empty( $wlgf_form_info['wizard_step_1_title'] ) ? $wlgf_form_info['wizard_step_1_title'] : __( 'Step 1', 'lead-generation-form' );
          $wizard_prev_text    = ! empty( $wlgf_form_info['wizard_prev_text'] ) ? $wlgf_form_info['wizard_prev_text'] : __( 'Previous', 'lead-generation-form' );
          $wizard_next_text    = ! empty( $wlgf_form_info['wizard_next_text'] ) ? $wlgf_form_info['wizard_next_text'] : __( 'Next Step', 'lead-generation-form' );

          $container_style = sprintf( '--wlgf-primary: %s; --wlgf-radius: %s; --wlgf-max-width: %s; font-family: %s;', esc_attr( $theme_primary ? $theme_primary : '#2563eb' ), esc_attr( $theme_radius ), esc_attr( $theme_max_width ), esc_attr( $theme_font ) );

          echo "<div class='wlgf-container' data-theme-bg='" . esc_attr( $theme_bg ) . "' data-theme-mode='" . esc_attr( $theme_mode ) . "' data-theme-field-size='" . esc_attr( $theme_field_size ) . "' data-theme-btn-width='" . esc_attr( $theme_btn_width ) . "' data-theme-input-style='" . esc_attr( $theme_input_style ) . "' data-theme-btn-shadow='" . esc_attr( $theme_btn_shadow ) . "' data-theme-label-style='" . esc_attr( $theme_label_style ) . "' style='" . esc_attr( $container_style ) . "'>";
               echo "<form id='" . esc_attr("wlgf-form-$wlgf_form_id") . "' method='post' enctype='multipart/form-data' novalidate>";

               if ( $has_multi_step ) {
                   if ( 'bar' === $wizard_style ) {
                       $initial_pct = round( 100 / $total_steps );
                       echo "<div class='wlgf-step-progress mb-4'>";
                       echo "<div class='wlgf-step-progress__info mb-1 d-flex justify-content-between'>";
                       echo "<span class='wlgf-step-progress__text'>" . sprintf( esc_html__( 'Step 1 of %d', 'lead-generation-form' ), $total_steps ) . "</span> ";
                       echo "<span class='wlgf-step-progress__pct'>" . esc_html( $initial_pct ) . "%</span>";
                       echo "</div>";
                       echo "<div class='wlgf-step-progress__track'><div class='wlgf-step-progress__fill' style='width: " . esc_attr( $initial_pct ) . "%;'></div></div>";
                       echo "</div>";
                   } elseif ( 'breadcrumbs' === $wizard_style ) {
                       echo "<div class='wlgf-step-indicators wlgf-step-indicators--breadcrumbs mb-4'>";
                       for ( $s = 1; $s <= $total_steps; $s++ ) {
                           $active_cls = ( 1 === $s ) ? 'wlgf-step-indicator--active' : '';
                           $title_txt  = ( 1 === $s ) ? $wizard_step_1_title : ( isset( $step_titles[ $s - 2 ] ) ? $step_titles[ $s - 2 ] : sprintf( __( 'Step %d', 'lead-generation-form' ), $s ) );
                           echo "<div class='wlgf-step-indicator " . esc_attr( $active_cls ) . "' data-step='" . esc_attr( $s ) . "' role='tab' aria-label='" . esc_attr( $title_txt ) . "'>";
                           echo "<span class='wlgf-step-indicator__badge'>";
                           echo "<span class='wlgf-step-indicator__num'>" . esc_html( $s ) . "</span>";
                           echo "<i class='fa-solid fa-check wlgf-step-indicator__check'></i>";
                           echo "</span>";
                           echo "<span class='wlgf-step-indicator__title'>" . esc_html( $title_txt ) . "</span>";
                           echo "</div>";
                           if ( $s < $total_steps ) {
                               echo "<div class='wlgf-step-breadcrumb-sep' aria-hidden='true'><i class='fa-solid fa-chevron-right'></i></div>";
                           }
                       }
                       echo "</div>";
                   } else {
                       // Default: Numbered Step Circles
                       echo "<div class='wlgf-step-indicators wlgf-step-indicators--circles mb-4'>";
                       for ( $s = 1; $s <= $total_steps; $s++ ) {
                           $active_cls = ( 1 === $s ) ? 'wlgf-step-indicator--active' : '';
                           $title_txt  = ( 1 === $s ) ? $wizard_step_1_title : ( isset( $step_titles[ $s - 2 ] ) ? $step_titles[ $s - 2 ] : sprintf( __( 'Step %d', 'lead-generation-form' ), $s ) );
                           echo "<div class='wlgf-step-indicator " . esc_attr( $active_cls ) . "' data-step='" . esc_attr( $s ) . "'>";
                           echo "<span class='wlgf-step-indicator__badge'><span class='wlgf-step-indicator__num'>" . esc_html( $s ) . "</span><i class='fa-solid fa-check wlgf-step-indicator__check'></i></span>";
                           echo "<span class='wlgf-step-indicator__title'>" . esc_html( $title_txt ) . "</span>";
                           echo "</div>";
                       }
                       echo "</div>";
                   }

                   echo "<div class='wlgf-step-card wlgf-step-card--active' data-step='1'>";
               }
               
               foreach ($wlgf_form_data as $field) {
                    $is_field_required = ! empty( $field['required'] ) && ( true === $field['required'] || 1 == $field['required'] || 'true' === $field['required'] || '1' === $field['required'] );
                    $required = $is_field_required ? "required" : '';
                    $required_badge = $is_field_required ? '<span class="wlgf-required">*</span>' : '';
                    $data_required_attr = $is_field_required ? "data-required='1'" : "";
                    $col_class = 'wlgf-col-12';
                    if ( ! empty( $field['column_width'] ) ) {
                         $col_num = intval( $field['column_width'] );
                         if ( in_array( $col_num, array( 3, 4, 6, 12 ), true ) ) {
                              $col_class = 'wlgf-col-' . $col_num;
                         }
                    } elseif ( ! empty( $field['className'] ) && preg_match( '/\bwlgf-col-(12|6|4|3)\b/', $field['className'], $matches ) ) {
                         $col_class = 'wlgf-col-' . $matches[1];
                    }

                    // Auto-strip stray HTML tags or clipboard span styles (e.g. <span style="font-family:">) from field labels
                    if ( isset( $field['label'] ) && ! in_array( $field['type'], array( 'paragraph' ), true ) ) {
                         $clean_lbl = (string) $field['label'];
                         if ( false !== strpos( $clean_lbl, '&lt;' ) && false !== strpos( $clean_lbl, '&gt;' ) ) {
                              $clean_lbl = html_entity_decode( $clean_lbl, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                         }
                         $field['label'] = trim( wp_strip_all_tags( $clean_lbl ) );
                    }

                    // Auto-strip stray HTML tags from select / checkbox / radio option labels
                    if ( isset( $field['values'] ) && is_array( $field['values'] ) ) {
                         foreach ( $field['values'] as &$opt_item ) {
                              if ( is_array( $opt_item ) && isset( $opt_item['label'] ) ) {
                                   $opt_lbl = (string) $opt_item['label'];
                                   if ( false !== strpos( $opt_lbl, '&lt;' ) && false !== strpos( $opt_lbl, '&gt;' ) ) {
                                        $opt_lbl = html_entity_decode( $opt_lbl, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                                   }
                                   $opt_item['label'] = trim( wp_strip_all_tags( $opt_lbl ) );
                              }
                         }
                         unset( $opt_item );
                    }

                    switch ($field['type']) {
                         case "step-break":
                              if ( $has_multi_step ) {
                                   echo "<div class='wlgf-step-nav mt-4 d-flex justify-content-between gap-2'>";
                                   if ( $current_step > 1 ) {
                                        echo "<button type='button' class='wlgf-btn wlgf-btn--outline wlgf-btn--prev'>" . esc_html( $wizard_prev_text ) . "</button>";
                                   } else {
                                        echo "<span></span>";
                                   }
                                   echo "<button type='button' class='wlgf-btn wlgf-btn--primary wlgf-btn--next'>" . esc_html( $wizard_next_text ) . "</button>";
                                   echo "</div>";
                                   echo "</div>"; // close current .wlgf-step-card

                                   $current_step++;
                                   echo "<div class='wlgf-step-card' data-step='" . esc_attr( $current_step ) . "'>";
                              } else {
                                   $step_label = ! empty( $field['label'] ) ? esc_html( $field['label'] ) : '';
                                   echo "<div class='wlgf-section-divider-wrap my-4 " . esc_attr( $col_class ) . "'>";
                                   if ( ! empty( $step_label ) ) {
                                        echo "<h4 class='wlgf-section-title h5 mb-2'>" . esc_html( $step_label ) . "</h4>";
                                   }
                                   echo "<hr class='wlgf-section-divider'>";
                                   echo "</div>";
                              }
                              break;

                         case "divider":
                              $divider_label = ! empty( $field['label'] ) ? esc_html( $field['label'] ) : '';
                              echo "<div class='wlgf-form-divider-wrap my-3 " . esc_attr( $col_class ) . "' data-field-type='divider'>";
                              if ( ! empty( $divider_label ) && 'Section Divider' !== $divider_label ) {
                                   echo "<h4 class='wlgf-form-divider-title'>" . esc_html( $divider_label ) . "</h4>";
                              }
                              echo "<hr class='wlgf-form-divider'>";
                              echo "</div>";
                              break;

                         case "text":
                              $placeholder = isset($field['placeholder']) ? esc_attr($field['placeholder']) : '';
                              $maxLength = isset($field['maxlength']) ? esc_attr($field['maxlength']) : '';
                              $wlgf_field_value = isset($field['value']) ? esc_attr($field['value']) : '';
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';
                              
                              // Determine the subtype
                              $inputType = 'text';
                              if (isset($field['subtype'])) {
                                   $subType = esc_attr($field['subtype']);
                                   if (in_array($subType, array('password', 'email', 'color', 'tel', 'url'))) {
                                        $inputType = $subType;
                                   }
                              }

                              // Sanitize and escape output
                              $fieldName = esc_attr($field['name']);
                              $fieldId   = esc_attr($fieldName . '_' . $wlgf_form_id);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';

                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='" . esc_attr( $field['type'] ) . "' " . $data_required_attr . ">";
                              echo "<label for='" . $fieldId . "' class='form-label'>" . esc_html( $fieldLabel ) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              echo "<input type='" . esc_attr( $inputType ) . "' id='" . $fieldId . "' name='" . esc_attr( $fieldName ) . "' class='" . esc_attr( $fieldClassName ) . "' value='" . esc_attr( $wlgf_field_value ) . "' placeholder='" . esc_attr( $placeholder ) . "' maxlength='" . esc_attr( $maxLength ) . "' " . esc_attr( $required ) . ">";
                              echo "</div>";
                              break;

                         case "number":
                              $placeholder = isset($field['placeholder']) ? esc_attr($field['placeholder']) : '';
                              $min = isset($field['min']) ? esc_attr($field['min']) : '';
                              $max = isset($field['max']) ? esc_attr($field['max']) : '';
                              $step = isset($field['step']) ? esc_attr($field['step']) : '';
                              $wlgf_field_value = isset($field['value']) ? esc_attr($field['value']) : '';
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';

                              // Determine the subtype
                              $inputType = 'number';
                              if (isset($field['subtype'])) {
                                   $subType = esc_attr($field['subtype']);
                                   if (in_array($subType, array('range'))) {
                                        $inputType = $subType;
                                   }
                              }

                              // Sanitize and escape output
                              $fieldName = esc_attr($field['name']);
                              $fieldId   = esc_attr($fieldName . '_' . $wlgf_form_id);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';

                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='" . esc_attr( $field['type'] ) . "' " . $data_required_attr . ">";
                              echo "<label for='" . $fieldId . "' class='form-label'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses($wlgfDescription, $wlgf_tooltip) . "</label>";
                              if ($inputType === 'range') {
                                   echo "<div class='range-value-display' id='range-value-display-" . $fieldId . "'>" . esc_html($wlgf_field_value) . "</div>";
                                   echo "<input type='" . esc_attr($inputType) . "' id='" . $fieldId . "' name='" . esc_attr($fieldName) . "' class='" . esc_attr($fieldClassName) . "' value='" . esc_attr($wlgf_field_value) . "' placeholder='" . esc_attr($placeholder) . "' min='" . esc_attr($min) . "' max='" . esc_attr($max) . "' step='" . esc_attr($step) . "' " . esc_attr($required) . ">";
                              } else {
                                   echo "<input type='" . esc_attr($inputType) . "' id='" . $fieldId . "' name='" . esc_attr($fieldName) . "' class='" . esc_attr($fieldClassName) . "' value='" . esc_attr($wlgf_field_value) . "' placeholder='" . esc_attr($placeholder) . "' min='" . esc_attr($min) . "' max='" . esc_attr($max) . "' step='" . esc_attr($step) . "' " . esc_attr($required) . ">";
                              }
                              echo "</div>";
                              break;

                         case "date":
                              $placeholder = isset($field['placeholder']) ? esc_attr($field['placeholder']) : '';
                              $wlgf_field_value = isset($field['value']) ? esc_attr($field['value']) : '';
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';
                              
                              // Sanitize and escape output
                              $fieldName = esc_attr($field['name']);
                              $fieldId   = esc_attr($fieldName . '_' . $wlgf_form_id);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';
                              
                              $min = isset($field['min']) && '' !== trim($field['min']) ? esc_attr($field['min']) : date('Y-m-d');
                              $max = isset($field['max']) && '' !== trim($field['max']) ? esc_attr($field['max']) : '';
                              $min_attr = "min='" . $min . "'";
                              $max_attr = $max !== '' ? "max='" . $max . "'" : "";

                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='" . esc_attr( $field['type'] ) . "' " . $data_required_attr . ">";
                              echo "<label for='" . $fieldId . "' class='form-label'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              echo "<div class='wlgf-date-input-wrap'>";
                              echo "<input type='date' id='" . $fieldId . "' name='" . esc_attr($fieldName) . "' class='" . esc_attr($fieldClassName) . "' value='" . esc_attr($wlgf_field_value) . "' placeholder='" . esc_attr($placeholder) . "' " . $min_attr . " " . $max_attr . " " . esc_attr($required) . ">";
                              echo "<span class='wlgf-date-icon' role='button' tabindex='-1' aria-label='Choose date'><i class='fa-regular fa-calendar-days'></i></span>";
                              echo "</div>";
                              echo "</div>";
                              break;

                         case "file":
                              $is_multi = isset($field['multiple']) && $field['multiple'];
                              $multiple_attr = $is_multi ? 'multiple' : '';
                              $input_name = $is_multi ? esc_attr($field['name']) . '[]' : esc_attr($field['name']);
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';
                              $fieldName = esc_attr($field['name']);
                              $fieldId   = esc_attr($fieldName . '_' . $wlgf_form_id);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';
                              // Dynamic Allowed File Types & Max Size Configuration
                              $raw_allowed_types = ! empty( $field['allowed_types'] ) ? (string) $field['allowed_types'] : 'pdf, doc, docx, png, jpg';
                              $raw_exts_arr = array_filter( array_map( 'trim', explode( ',', str_replace( array( '.', ' ' ), array( '', '' ), strtolower( $raw_allowed_types ) ) ) ) );
                              if ( empty( $raw_exts_arr ) ) {
                                  $raw_exts_arr = array( 'pdf', 'doc', 'docx', 'png', 'jpg' );
                              }
                              $allowed_types_clean = implode( ',', $raw_exts_arr );
                              $accept_attr = 'accept="' . esc_attr( implode( ',', array_map( function( $ext ) { return '.' . $ext; }, $raw_exts_arr ) ) ) . '"';
                              $display_types_label = strtoupper( implode( ', ', $raw_exts_arr ) );

                              $max_size_mb = ( isset( $field['max_file_size'] ) && '' !== (string) $field['max_file_size'] && floatval( $field['max_file_size'] ) > 0 )
                                  ? floatval( $field['max_file_size'] )
                                  : ( ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ? 50 : 5 );
                              $max_size_bytes = intval( $max_size_mb * 1024 * 1024 );
                              $max_size_label = ( $max_size_mb == intval( $max_size_mb ) ? intval( $max_size_mb ) : $max_size_mb ) . 'MB';

                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='" . esc_attr( $field['type'] ) . "' " . $data_required_attr . ">";
                              echo "<label for='" . $fieldId . "' class='form-label'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              echo "<div class='wlgf-dropzone-wrap' data-field-name='" . esc_attr( $fieldName ) . "' data-max-size='" . esc_attr( $max_size_bytes ) . "' data-allowed-types='" . esc_attr( $allowed_types_clean ) . "'>";
                              echo "<div class='wlgf-dropzone' tabindex='0' role='button' aria-label='" . esc_attr__( 'Drag and drop files here or click to browse', 'lead-generation-form' ) . "'>";
                              echo "<i class='fa-solid fa-cloud-arrow-up wlgf-dropzone__icon'></i>";
                              echo "<p class='wlgf-dropzone__text'>" . esc_html__( 'Drag & drop files here or', 'lead-generation-form' ) . " <span class='wlgf-dropzone__browse'>" . esc_html__( 'Browse', 'lead-generation-form' ) . "</span></p>";
                              echo "<span class='wlgf-dropzone__hint'>" . sprintf( esc_html__( 'Accepted formats: %s (Max %s total)', 'lead-generation-form' ), esc_html( $display_types_label ), esc_html( $max_size_label ) ) . "</span>";
                              echo "<input type='file' id='" . $fieldId . "' name='" . $input_name . "' class='wlgf-dropzone__input wlgf-d-none " . esc_attr($fieldClassName) . "' " . $accept_attr . " " . esc_attr($multiple_attr) . " " . esc_attr($required) . ">";
                              echo "</div>";
                              echo "<div class='wlgf-file-list' aria-live='polite'></div>";
                              echo "</div>";
                              echo "</div>";
                              break;

                         case "star-rating":
                              if ( ! ( defined( "WLGF_IS_PRO" ) && WLGF_IS_PRO ) ) {
                                  break;
                              }
                              $required = $is_field_required ? "required" : '';
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';
                              $fieldName = esc_attr($field['name']);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';

                              $required_badge = $is_field_required ? '<span class="wlgf-required">*</span>' : '';
                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='star-rating' " . $data_required_attr . ">";
                              echo "<label class='form-label d-block'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              echo "<div class='wlgf-star-rating' role='radiogroup' aria-label='" . esc_attr( $fieldLabel ? $fieldLabel : __( 'Star Rating', 'lead-generation-form' ) ) . "' data-field-name='" . esc_attr( $fieldName ) . "'>";
                              for ($star = 1; $star <= 5; $star++) {
                                  echo "<input type='radio' name='" . esc_attr( $fieldName ) . "' id='" . esc_attr( $fieldName ) . "_star_" . $star . "_" . esc_attr( $wlgf_form_id ) . "' value='" . $star . "' class='wlgf-star-radio wlgf-d-none' " . esc_attr($required) . ">";
                                  echo "<span class='wlgf-star' data-value='" . $star . "' role='radio' aria-checked='false' tabindex='0' aria-label='" . sprintf( esc_attr__( '%d star', 'lead-generation-form' ), $star ) . "'><i class='fa-solid fa-star'></i></span>";
                              }
                              echo "</div>";
                              echo "</div>";
                              break;

                         case "nps-matrix":
                              if ( ! ( defined( "WLGF_IS_PRO" ) && WLGF_IS_PRO ) ) {
                                  break;
                              }
                              $required = $is_field_required ? "required" : '';
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';
                              $fieldName = esc_attr($field['name']);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';

                              $required_badge = $is_field_required ? '<span class="wlgf-required">*</span>' : '';
                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='nps-matrix' " . $data_required_attr . ">";
                              echo "<label class='form-label d-block'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              echo "<div class='wlgf-nps-matrix' role='radiogroup' aria-label='" . esc_attr( $fieldLabel ? $fieldLabel : __( 'Net Promoter Score', 'lead-generation-form' ) ) . "' data-field-name='" . esc_attr( $fieldName ) . "'>";
                              echo "<div class='wlgf-nps-chips-group'>";
                              for ($score = 0; $score <= 10; $score++) {
                                  echo "<input type='radio' name='" . esc_attr( $fieldName ) . "' id='" . esc_attr( $fieldName ) . "_nps_" . $score . "_" . esc_attr( $wlgf_form_id ) . "' value='" . $score . "' class='wlgf-nps-radio wlgf-d-none' " . esc_attr($required) . ">";
                                  echo "<button type='button' class='wlgf-nps-btn' data-value='" . $score . "' role='radio' aria-checked='false' aria-label='" . sprintf( esc_attr__( 'Score %d', 'lead-generation-form' ), $score ) . "'>" . $score . "</button>";
                              }
                              echo "</div>";
                              echo "<div class='wlgf-nps-labels d-flex justify-content-between mt-1 text-muted small'>";
                              echo "<span>" . esc_html__( '0 - Not at all likely', 'lead-generation-form' ) . "</span>";
                              echo "<span>" . esc_html__( '10 - Extremely likely', 'lead-generation-form' ) . "</span>";
                              echo "</div>";
                              echo "</div>";
                              echo "</div>";
                              break;

                         case "calculation":
                              if ( ! ( defined( "WLGF_IS_PRO" ) && WLGF_IS_PRO ) ) {
                                  break;
                              }
                              $fieldName       = isset($field['name']) ? esc_attr($field['name']) : 'calc_' . wp_rand(100, 999);
                              $fieldId         = esc_attr($fieldName . '_' . $wlgf_form_id);
                              $fieldLabel      = isset($field['label']) ? esc_html($field['label']) : esc_html__('Estimated Total', 'lead-generation-form');
                              $formula         = isset($field['formula']) ? esc_attr($field['formula']) : '';
                              $prefix          = isset($field['prefix']) ? esc_attr($field['prefix']) : '$';
                              $postfix         = isset($field['postfix']) ? esc_attr($field['postfix']) : '';
                              $decimals        = isset($field['decimals']) ? intval($field['decimals']) : 2;
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';

                              echo "<div class='wlgf-field-wrap wlgf-calc-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr($fieldName) . "' data-field-type='calculation' data-formula='" . esc_attr($formula) . "' data-prefix='" . esc_attr($prefix) . "' data-postfix='" . esc_attr($postfix) . "' data-decimals='" . esc_attr($decimals) . "'>";
                              echo "<label class='form-label'>" . esc_html($fieldLabel) . " " . wp_kses($wlgfDescription, $wlgf_tooltip) . "</label>";
                              echo "<div class='wlgf-calc-display'><span class='wlgf-calc-prefix'>" . esc_html($prefix) . "</span><span class='wlgf-calc-val'>0.00</span><span class='wlgf-calc-postfix'>" . esc_html($postfix) . "</span></div>";
                              echo "<input type='hidden' id='" . $fieldId . "' name='" . esc_attr($fieldName) . "' class='wlgf-calc-hidden-input' value='0.00'>";
                              echo "</div>";
                              break;

                         case "textarea":
                              $placeholder = isset($field['placeholder']) ? esc_attr($field['placeholder']) : '';
                              $maxlength = isset($field['maxlength']) ? esc_attr($field['maxlength']) : '';
                              $rows = isset($field['rows']) ? esc_attr($field['rows']) : '';
                              $wlgf_field_value = isset($field['value']) ? esc_attr($field['value']) : '';
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';

                              // Sanitize and escape output
                              $fieldName = esc_attr($field['name']);
                              $fieldId   = esc_attr($fieldName . '_' . $wlgf_form_id);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';

                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='" . esc_attr( $field['type'] ) . "' " . $data_required_attr . ">";
                              echo "<label for='" . $fieldId . "' class='form-label'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              echo "<textarea id='" . $fieldId . "' name='" . esc_attr($fieldName) . "' class='" . esc_attr($fieldClassName) . "' placeholder='" . esc_attr($placeholder) . "' rows='" . esc_attr($rows) . "' maxlength='" . esc_attr($maxlength) . "' " . esc_attr($required) . ">" . esc_textarea($wlgf_field_value) . "</textarea>";
                              echo "</div>";
                              break;

                         case "select":

                              // Sanitize and escape output
                              $fieldName = esc_attr($field['name']);
                              $fieldId   = esc_attr($fieldName . '_' . $wlgf_form_id);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';

                              $placeholder = isset($field['placeholder']) ? esc_html($field['placeholder']) : "Select An Option";

                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='" . esc_attr( $field['type'] ) . "' " . $data_required_attr . ">";
                              echo "<label for='" . $fieldId . "' class='form-label'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              echo "<select id='" . $fieldId . "' name='" . esc_attr($fieldName) . "' class='" . esc_attr($fieldClassName) . "' " . esc_attr($required) . ">";
                              echo "<option value='' disabled selected>" . esc_html($placeholder) . "</option>";
                              if ( isset( $field['values'] ) && is_array( $field['values'] ) ) {
                                   foreach ($field['values'] as $option) {
                                        $rawVal   = isset($option['value']) ? (string) $option['value'] : '';
                                        $rawLabel = isset($option['label']) ? (string) $option['label'] : '';
                                        if ( '' === trim($rawVal) && '' === trim($rawLabel) ) {
                                             continue;
                                        }
                                        $optionValue = esc_attr($rawVal);
                                        $optionLabel = esc_html($rawLabel);
                                        $selected = isset($option['selected']) && $option['selected'] ? "selected" : "";
                                        echo "<option value='" . esc_attr($optionValue) . "' " . esc_attr($selected) . ">" . esc_html($optionLabel) . "</option>";
                                   }
                              }
                              echo "</select>";
                              echo "</div>";
                              break;
                         
                         case "checkbox-group":
                              $fieldName = esc_attr($field['name']);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';

                              echo "<div class='wlgf-field-wrap mb-3 wlgf-cb-group " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='checkbox-group' " . $data_required_attr . ">";
                              echo "<label class='form-label d-block'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              $wlgf_cbc = 1;
                              if ( isset( $field['values'] ) && is_array( $field['values'] ) ) {
                                   foreach ($field['values'] as $checkbox) {
                                        $checkboxValue = esc_attr($checkbox['value']);
                                        $checkboxLabel = esc_html($checkbox['label']);
                                        $checked = isset($checkbox['selected']) && $checkbox['selected'] ? "checked" : "";
                                        $cb_input_id = esc_attr($fieldName . '_' . $wlgf_cbc . '_' . $wlgf_form_id);
                                        echo "<div class='wlgf-checkbox-option'>";
                                        echo "<label class='form-check-label wlgf-checkbox-label' for='" . $cb_input_id . "'>";
                                        echo "<input type='checkbox' class='form-check-input wlgf-checkbox-input' id='" . $cb_input_id . "' name='" . esc_attr($fieldName . '[' . $wlgf_cbc . ']') . "' value='" . esc_attr($checkboxValue) . "' " . esc_attr($checked) . ">";
                                        echo "<span class='wlgf-checkbox-text'>" . esc_html($checkboxLabel) . "</span>";
                                        echo "</label>";
                                        echo "</div>";
                                        $wlgf_cb_names[] = $fieldName.'['.$wlgf_cbc.']';
                                        $wlgf_cbc++;
                                   }
                              }
                              $wlgf_checkbox_fields[] = array('field_name' => $fieldName, 'required' => $required, 'names' => $wlgf_cb_names);
                              echo "</div>";
                              unset($wlgf_cb_names);
                              $wlgf_checkbox_count++;
                              break;

                         case "radio-group":
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';

                              $fieldName = esc_attr($field['name']);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';

                              // Payment Disconnect Safeguard: If this is a payment method selector but payments are disabled
                              $is_payment_field = ( 0 === strcasecmp( $fieldName, 'payment_method' ) || 0 === strcasecmp( $fieldName, 'Payment_Method' ) );
                              if ( ! $is_payment_field && isset( $field['values'] ) && is_array( $field['values'] ) ) {
                                   foreach ( $field['values'] as $v ) {
                                        $val_str = strtolower( ( isset( $v['value'] ) ? $v['value'] : '' ) . ' ' . ( isset( $v['label'] ) ? $v['label'] : '' ) );
                                        if ( strpos( $val_str, 'stripe' ) !== false || strpos( $val_str, 'paypal' ) !== false ) {
                                             $is_payment_field = true;
                                             break;
                                        }
                                   }
                              }

                              $is_form_payment_on = ( ! empty( $wlgf_form_info['payment_enabled'] ) && 1 === (int) $wlgf_form_info['payment_enabled'] );
                              if ( $is_payment_field && ! $is_form_payment_on ) {
                                   if ( current_user_can( 'manage_options' ) ) {
                                        echo "<div class='wlgf-field-wrap wlgf-notice-wrap " . esc_attr( $col_class ) . "'>";
                                        echo "<div class='wlgf-alert wlgf-alert--warning'>";
                                        echo "<i class='fa-solid fa-triangle-exclamation'></i> <strong>" . esc_html__( 'Payment Notice (Admin only):', 'lead-generation-form' ) . "</strong> ";
                                        echo esc_html__( 'This payment method field is hidden from visitors because "Enable Payment" is unchecked in Form Settings.', 'lead-generation-form' );
                                        echo "</div></div>";
                                   }
                                   break;
                              }

                              echo "<div class='wlgf-field-wrap mb-3 wlgf-rd-group " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='radio-group' " . $data_required_attr . ">";
                              echo "<label class='form-label d-block'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses( $wlgfDescription, $wlgf_tooltip ) . "</label>";
                              if ( isset( $field['values'] ) && is_array( $field['values'] ) ) {
                                   $wlgf_rd_idx = 0;
                                   foreach ($field['values'] as $radio) {
                                        $radioValue = esc_attr($radio['value']);
                                        $radioLabel = esc_html($radio['label']);
                                        $checked = isset($radio['selected']) && $radio['selected'] ? "checked" : "";
                                        $radio_id = 'wlgf-radio-' . esc_attr($wlgf_form_id) . '-' . esc_attr($fieldName) . '-' . $wlgf_rd_idx;
                                        echo "<div class='wlgf-radio-option'>";
                                        echo "<label class='wlgf-radio-label' for='" . esc_attr($radio_id) . "'>";
                                        echo "<input type='radio' class='wlgf-radio-input' id='" . esc_attr($radio_id) . "' name='" . esc_attr($fieldName) . "' value='" . esc_attr($radioValue) . "' " . esc_attr($checked) . " " . esc_attr($required) . ">";
                                        echo "<span class='wlgf-radio-text'>" . esc_html($radioLabel) . "</span>";
                                        echo "</label>";
                                        echo "</div>";
                                        $wlgf_rd_idx++;
                                   }
                              }
                              echo "</div>";
                              break;

                         case "terms":
                              $terms_label = ! empty( $field['label'] ) ? wp_kses_post( $field['label'] ) : __( 'I agree to the Terms of Service and Privacy Policy', 'lead-generation-form' );
                              $fieldName   = ! empty( $field['name'] ) ? esc_attr( $field['name'] ) : 'terms_agreement';
                              $fieldId     = esc_attr( $fieldName . '_' . $wlgf_form_id );
                              $required    = ( isset( $field['required'] ) && 0 == $field['required'] ) ? '' : 'required';
                              echo "<div class='wlgf-field-wrap wlgf-terms-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='terms'>";
                              echo "<label class='wlgf-terms-label' for='" . $fieldId . "'>";
                              echo "<input type='checkbox' class='wlgf-terms-input' id='" . $fieldId . "' name='" . esc_attr( $fieldName ) . "' value='agreed' " . esc_attr( $required ) . "> ";
                              echo "<span class='wlgf-terms-text'>" . $terms_label . " <span class='wlgf-text-danger'>*</span></span>";
                              echo "</label>";
                              echo "</div>";
                              break;

                         case "header":
                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-type='header'>";
                              $allowed_header_tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
                              $subtype = ( isset( $field['subtype'] ) && in_array($field['subtype'], $allowed_header_tags) ) ? esc_html($field['subtype']) : 'h1';
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';
                              echo "<" . esc_attr($subtype) . " class='" . esc_attr($fieldClassName) . "'>" . esc_html($fieldLabel) . "</" . esc_attr($subtype) . ">";
                              echo "</div>";
                              break;

                         case "paragraph":
                              echo "<div class='wlgf-field-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-type='paragraph'>";
                              $allowed_tags = array('p', 'div', 'span', 'section');
                              $subtype = ( isset( $field['subtype'] ) && in_array($field['subtype'], $allowed_tags) ) ? esc_html($field['subtype']) : 'p';
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : '';
                              $fieldClassName = isset($field['className']) ? esc_html($field['className']) : '';
                              echo "<" . esc_attr($subtype) . " class='" . esc_attr($fieldClassName) . "'>" . esc_html($fieldLabel) . "</" . esc_attr($subtype) . ">";
                              echo "</div>";
                              break;

                         case "hidden":
                              $wlgf_field_value = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
                              $fieldName = isset( $field['name'] ) ? esc_attr( $field['name'] ) : 'hidden_field';
                              if ( ! empty( $field['utm_param'] ) ) {
                                   $utm_key = sanitize_key( $field['utm_param'] );
                                   if ( isset( $_GET[ $utm_key ] ) ) {
                                        $wlgf_field_value = sanitize_text_field( wp_unslash( $_GET[ $utm_key ] ) );
                                   }
                              }
                              echo "<div class='wlgf-field-wrap wlgf-d-none " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr( $fieldName ) . "' data-field-type='hidden'>";
                              echo "<input type='hidden' id='" . esc_attr( $fieldName ) . "' name='" . esc_attr( $fieldName ) . "' value='" . esc_attr( $wlgf_field_value ) . "'>";
                              echo "</div>";
                              break;
                              
                         case "button":
                              if ( ! empty( $wlgf_form_info['payment_enabled'] ) && 1 === (int) $wlgf_form_info['payment_enabled'] && defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO && $is_payments_enabled ) {
                                   $pay_type      = isset( $wlgf_form_info['payment_type'] ) ? $wlgf_form_info['payment_type'] : 'fixed';
                                   $pay_amt       = isset( $wlgf_form_info['payment_amount'] ) ? $wlgf_form_info['payment_amount'] : '0.00';
                                   $pay_calc      = isset( $wlgf_form_info['payment_calc_field'] ) ? $wlgf_form_info['payment_calc_field'] : '';
                                   $pay_item      = ! empty( $wlgf_form_info['payment_item_name'] ) ? $wlgf_form_info['payment_item_name'] : esc_html__( 'Total Due', 'lead-generation-form' );
                                   $settings_cur  = get_option( 'wlgf_settings', array() );
                                   $pay_curr      = ! empty( $settings_cur['payment_currency'] ) ? $settings_cur['payment_currency'] : 'USD';
                                   $is_stripe     = ! empty( $settings_cur['stripe_enabled'] ) && 1 === (int) $settings_cur['stripe_enabled'];
                                   $stripe_mode   = ! empty( $settings_cur['stripe_test_mode'] ) && 1 === (int) $settings_cur['stripe_test_mode'] ? 'test' : 'live';
                                   $stripe_pub_key = ( 'test' === $stripe_mode ) ? ( isset( $settings_cur['stripe_test_pub_key'] ) ? $settings_cur['stripe_test_pub_key'] : '' ) : ( isset( $settings_cur['stripe_live_pub_key'] ) ? $settings_cur['stripe_live_pub_key'] : '' );
                                   $is_paypal     = ! empty( $settings_cur['paypal_enabled'] ) && 1 === (int) $settings_cur['paypal_enabled'];
                                   $paypal_mode   = isset( $settings_cur['paypal_mode'] ) ? $settings_cur['paypal_mode'] : 'sandbox';
                                   $paypal_client_id = ( 'sandbox' === $paypal_mode ) ? ( isset( $settings_cur['paypal_sandbox_client_id'] ) ? $settings_cur['paypal_sandbox_client_id'] : '' ) : ( isset( $settings_cur['paypal_live_client_id'] ) ? $settings_cur['paypal_live_client_id'] : '' );

                                   echo "<div class='wlgf-field-wrap wlgf-payment-checkout-wrap wlgf-col-12 mb-4' data-field-type='payment-checkout' data-pay-type='" . esc_attr( $pay_type ) . "' data-pay-amount='" . esc_attr( $pay_amt ) . "' data-pay-calc='" . esc_attr( $pay_calc ) . "' data-currency='" . esc_attr( $pay_curr ) . "'>";
                                   echo "<div class='wlgf-payment-summary'>";
                                   echo "<div class='wlgf-payment-header-left'>";
                                   echo "<div class='wlgf-pay-icon-badge'><i class='fa-solid fa-shield-halved'></i></div>";
                                   echo "<div class='wlgf-pay-header-text'>";
                                   echo "<span class='wlgf-payment-item-title'>" . esc_html( $pay_item ) . "</span>";
                                   echo "<span class='wlgf-payment-subtitle'>" . esc_html__( 'Secure Online Payment', 'lead-generation-form' ) . "</span>";
                                   echo "</div>";
                                   echo "</div>";
                                   echo "<div class='wlgf-payment-amount-badge'>";
                              echo "<span class='wlgf-pay-currency'>" . esc_html( $pay_curr ) . "</span>";
                                   echo "<span class='wlgf-pay-amt-val'>" . esc_html( ( 'fixed' === $pay_type ) ? number_format( floatval( $pay_amt ), 2 ) : '0.00' ) . "</span>";
                                   echo "</div>";
                                   echo "</div>";

                                   echo "<div class='wlgf-payment-methods'>";
                                   if ( $is_stripe && ! empty( $stripe_pub_key ) && $is_paypal && ! empty( $paypal_client_id ) ) {
                                        echo "<div class='wlgf-gateway-tabs-wrap mb-3'>";
                                        echo "<button type='button' class='wlgf-gateway-tab active' data-target='stripe'><i class='fa-regular fa-credit-card'></i> " . esc_html__( 'Credit / Debit Card', 'lead-generation-form' ) . "</button>";
                                        echo "<button type='button' class='wlgf-gateway-tab' data-target='paypal'><i class='fa-brands fa-paypal'></i> " . esc_html__( 'PayPal', 'lead-generation-form' ) . "</button>";
                                        echo "</div>";
                                   }
                                   if ( $is_stripe && ! empty( $stripe_pub_key ) ) {
                                        echo "<div class='wlgf-payment-option wlgf-stripe-option mb-3'>";
                                        echo "<label class='wlgf-pay-label mb-2 d-block'><i class='fa-regular fa-credit-card'></i> " . esc_html__( 'Enter Card Information', 'lead-generation-form' ) . "</label>";
                                        echo "<div id='wlgf-stripe-card-element-" . esc_attr( $wlgf_form_id ) . "' class='wlgf-stripe-element-container' data-pub-key='" . esc_attr( $stripe_pub_key ) . "'></div>";
                                        echo "<div id='wlgf-stripe-errors-" . esc_attr( $wlgf_form_id ) . "' class='wlgf-stripe-errors text-danger small mt-1'></div>";
                                        echo "</div>";
                                   }
                                   if ( $is_paypal && ! empty( $paypal_client_id ) ) {
                                        echo "<div class='wlgf-payment-option wlgf-paypal-option' style='display: none;'>";
                                        echo "<div class='wlgf-paypal-button-container my-2' id='wlgf-paypal-button-" . esc_attr( $wlgf_form_id ) . "' data-client-id='" . esc_attr( $paypal_client_id ) . "' data-currency='" . esc_attr( $pay_curr ) . "' data-mode='" . esc_attr( $paypal_mode ) . "'></div>";
                                        echo "</div>";
                                   }
                                   echo "</div>";

                                   echo "<div class='wlgf-payment-trust-footer'>";
                                   echo "<i class='fa-solid fa-lock'></i> " . esc_html__( 'Guaranteed safe & 256-bit SSL encrypted checkout', 'lead-generation-form' );
                                   echo "</div>";

                                   echo "<input type='hidden' name='wlgf_payment_transaction_id' id='wlgf_payment_transaction_id' value=''>";
                                   echo "<input type='hidden' name='wlgf_payment_status' id='wlgf_payment_status' value=''>";
                                   echo "<input type='hidden' name='wlgf_payment_amount_paid' id='wlgf_payment_amount_paid' value=''>";
                                   echo "<input type='hidden' name='wlgf_payment_gateway' id='wlgf_payment_gateway' value=''>";
                                   echo "</div>";
                              }

                              if ( ! empty( $wlgf_form_info['newsletter_enabled'] ) && 1 === (int) $wlgf_form_info['newsletter_enabled'] ) {
                                   $nl_mode = isset( $wlgf_form_info['newsletter_mode'] ) ? $wlgf_form_info['newsletter_mode'] : 'explicit';
                                   if ( 'explicit' === $nl_mode ) {
                                        $nl_label   = ! empty( $wlgf_form_info['newsletter_label'] ) ? $wlgf_form_info['newsletter_label'] : __( 'Subscribe to our newsletter for exclusive updates and offers', 'lead-generation-form' );
                                        $nl_checked = ( ! empty( $wlgf_form_info['newsletter_default_checked'] ) && 1 === (int) $wlgf_form_info['newsletter_default_checked'] ) ? 'checked' : '';
                                        $nl_input_id = 'wlgf_newsletter_optin_' . esc_attr( $wlgf_form_id );
                                        echo "<div class='wlgf-field-wrap wlgf-newsletter-optin-wrap wlgf-checkbox-option mb-3' data-field-type='newsletter-optin'>";
                                        echo "<label class='form-check-label wlgf-checkbox-label' for='" . $nl_input_id . "'>";
                                        echo "<input type='checkbox' class='form-check-input wlgf-checkbox-input' id='" . $nl_input_id . "' name='wlgf_newsletter_optin' value='1' " . esc_attr( $nl_checked ) . ">";
                                        echo "<span class='wlgf-checkbox-text'>" . esc_html( $nl_label ) . "</span>";
                                        echo "</label>";
                                        echo "</div>";
                                   }
                              }

                              $wlgf_field_class_name = isset( $field['className'] ) ? esc_attr( $field['className'] ) : '';
                              $wlgf_field_value = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
                              $fieldName = isset( $field['name'] ) ? esc_attr( $field['name'] ) : '';
                              $fieldSubtype = isset( $field['subtype'] ) ? esc_attr( $field['subtype'] ) : 'button';
                              $fieldLabel = isset( $field['label'] ) ? esc_html($field['label']) : '';

                               if ( $has_multi_step && $current_step > 1 ) {
                                    echo "<div class='wlgf-step-nav wlgf-col-12 mt-4 d-flex justify-content-between gap-2'>";
                                    echo "<button type='button' class='wlgf-btn wlgf-btn--outline wlgf-btn--prev'>" . esc_html( $wizard_prev_text ) . "</button>";
                                    echo "<button type='" . esc_attr( $fieldSubtype ) . "' id='" . esc_attr( $fieldName ) . "-".esc_attr($wlgf_form_id)."' class='wlgf-btn wlgf-btn--primary " . esc_attr( $wlgf_field_class_name ) . "' value='" . esc_attr( $wlgf_field_value ) . "'>" . esc_html( $fieldLabel ) . "</button>";
                                    echo "</div>";
                               } else {
                                    echo "<div class='wlgf-field-wrap wlgf-button-wrap wlgf-col-12 mb-3 " . esc_attr( $col_class ) . "' data-field-type='button'>";
                                    echo "<button type='" . esc_attr( $fieldSubtype ) . "' id='" . esc_attr( $fieldName ) . "-".esc_attr($wlgf_form_id)."' class='wlgf-btn wlgf-btn--primary " . esc_attr( $wlgf_field_class_name ) . "' value='" . esc_attr( $wlgf_field_value ) . "'>" . esc_html( $fieldLabel ) . "</button>";
                                    echo "</div>";
                               }
                              break;
                              
                         case "signature":
                              if ( ! ( defined( "WLGF_IS_PRO" ) && WLGF_IS_PRO ) ) {
                                  break;
                              }
                              $sig_pad_ver = file_exists(plugin_dir_path(__FILE__) . 'assets/js/wlgf-signature-pad.js') ? (defined('WLGF_PLUGIN_VERSION') ? WLGF_PLUGIN_VERSION : '3.2.0') . '.' . filemtime(plugin_dir_path(__FILE__) . 'assets/js/wlgf-signature-pad.js') : (defined('WLGF_PLUGIN_VERSION') ? WLGF_PLUGIN_VERSION : '3.2.0');
                              wp_enqueue_script('wlgf-signature-pad', plugin_dir_url(__FILE__) . 'assets/js/wlgf-signature-pad.js', array('jquery'), $sig_pad_ver, true);
                              $fieldName = isset($field['name']) ? esc_attr($field['name']) : 'signature_' . wp_rand(100, 999);
                              $fieldId   = esc_attr($fieldName . '_' . $wlgf_form_id);
                              $fieldLabel = isset($field['label']) ? esc_html($field['label']) : esc_html__('Digital Signature', 'lead-generation-form');
                              $wlgfDescription = ( isset($field['description']) && '' !== trim($field['description']) ) ? "<span class='wlgf-tooltip-icon' data-wlgf-tooltip='" . esc_attr($field['description']) . "' data-bs-toggle='tooltip' title='" . esc_attr($field['description']) . "'>?</span>" : '';

                              echo "<div class='wlgf-field-wrap wlgf-signature-wrap mb-3 " . esc_attr( $col_class ) . "' data-field-name='" . esc_attr($fieldName) . "' data-field-type='signature'>";
                              echo "<label for='" . $fieldId . "' class='form-label'>" . esc_html($fieldLabel) . $required_badge . " " . wp_kses($wlgfDescription, $wlgf_tooltip) . "</label>";
                              echo "<div class='wlgf-signature-container'>";
                              echo "<canvas class='wlgf-signature-canvas' width='500' height='160'></canvas>";
                              echo "<div class='wlgf-signature-actions'>";
                              echo "<span class='wlgf-signature-hint'><i class='fa-solid fa-pen-nib'></i> " . esc_html__('Sign above using finger or mouse', 'lead-generation-form') . "</span>";
                              echo "<button type='button' class='wlgf-signature-clear-btn wlgf-signature-pad-clear'><i class='fa-solid fa-rotate-left'></i> " . esc_html__('Clear', 'lead-generation-form') . "</button>";
                              echo "</div>";
                              echo "<input type='hidden' id='" . $fieldId . "' name='" . esc_attr($fieldName) . "' class='wlgf-signature-input' value='' " . esc_attr($required) . ">";
                              echo "</div>";
                              echo "</div>";
                              break;
                              
                         case "turnstile":
                              if (!empty($field['sitekey'])) {
                                   echo "<div class='wlgf-col-12 wlgf-turnstile-wrap mb-3' style='margin-bottom: 15px;'>";
                                   echo "<div class='cf-turnstile' data-sitekey='" . esc_attr($field['sitekey']) . "' data-theme='auto'></div>";
                                   echo "</div>";
                              }
                              break;

                         case "recaptcha":
                              if($wlgf_recaptcha == 1 && $wlgf_sitekey && $wlgf_secretkey) {
                                   $wlgf_fieldRecaptcha = "";
                                   $wlgf_fieldRecaptcha .= "<input type='hidden' name='recaptcha_response' id='recaptchaResponse'>";
                                   $safe_sitekey = esc_js($field['sitekey']);

                                   echo wp_kses($wlgf_fieldRecaptcha, array(
                                        'input' => array(
                                             'type' => array(),
                                             'name' => array(),
                                             'id' => array(),
                                             'value' => array()
                                        ),
                                        'script' => array(),
                                   ));
                              }
                              break;
                    }
               }

               if ( $has_multi_step ) {
                   echo "</div>"; // close final .wlgf-step-card
               }

               echo "<input type='hidden' name='wlgf_form_id' value='" . esc_attr($wlgf_form_id) . "'>";
               echo "<input type='hidden' name='wlgf_form_nonce' value='" . esc_attr($wlgf_form_nonce) . "'>";
               if ( ! empty( $wlgf_carbon_copy ) && defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
                    echo "<input type='hidden' name='wlgf_carbon_copy' value='" . esc_attr($wlgf_carbon_copy) . "'>";
               }
               echo "<input type='hidden' name='wlgf_honeypot' value=''>";
               $wlgf_render_settings = get_option('wlgf_settings', array());
               $wlgf_hp_active = isset($wlgf_render_settings['honeypot_status']) ? (string)$wlgf_render_settings['honeypot_status'] : '1';
               if ($wlgf_hp_active !== '2') {
                    echo "<div class='wlgf-hp-decoy-trap' style='position:absolute !important;left:-9999px !important;top:-9999px !important;opacity:0 !important;height:0 !important;width:0 !important;overflow:hidden !important;pointer-events:none !important;' aria-hidden='true'>";
                    echo "<label for='wlgf_hp_website_confirm_" . esc_attr($wlgf_form_id) . "'>Leave this field blank</label>";
                    echo "<input type='text' id='wlgf_hp_website_confirm_" . esc_attr($wlgf_form_id) . "' name='wlgf_hp_website_confirm' value='' tabindex='-1' autocomplete='off'>";
                    echo "</div>";
               }
               echo "<input type='hidden' name='wlgf_form_load_time' id='wlgf_form_load_time' value=''>";
               echo "<div class='wlgf-spinner wlgf-spinner-wrap wlgf-col-12 text-center' style='width: 100%; text-align: center; margin: 20px auto 10px auto;'>";
                    echo "<img aria-hidden='true' class='wlgf-spinner wlgf-spinner-img' width='65' height='65' src='" . esc_url($wlgf_loader) . "' alt='" . esc_attr('Loading...') . "' style='display: block; margin: 0 auto;'>";
               echo "</div>";
               echo "<div id='wlgf-response-output' class='wlgf-col-12 wlgf-response-output mb-3' aria-hidden='true'></div>";
               echo "<div id='wlgf-recaptcha-output' class='wlgf-col-12 wlgf-recaptcha-output mb-3' aria-hidden='true'></div>";
               echo "</form>";
               
          echo "</div>";
          // form structure output end
          
          
          // handling form submission start
          echo "<div class='wpfrank-lgf'>";
          if (empty($_POST['wlgf_honeypot']) && isset($_POST['wlgf_form_nonce']) && !empty($_POST['wlgf_form_load_time'])) {
               
               //load saved settings early for security checks
               $wlgf_settings = get_option('wlgf_settings', array());
               $wlgf_user_message = (isset($wlgf_settings['user_message'])) ? sanitize_text_field($wlgf_settings['user_message']) : '';

               // 1. Decoy Smart Honeypot Trap (catches automated bots filling off-screen inputs)
               $wlgf_hp_sub_active = isset($wlgf_settings['honeypot_status']) ? (string)$wlgf_settings['honeypot_status'] : '1';
               if ($wlgf_hp_sub_active !== '2' && !empty($_POST['wlgf_hp_website_confirm'])) {
                    $success_msg = !empty($wlgf_user_message) ? $wlgf_user_message : __('Thank you! Your query has been successfully submitted.', 'lead-generation-form');
                    if (function_exists('wlgf_get_confirmation_html')) {
                         echo wlgf_get_confirmation_html($success_msg, $wlgf_settings);
                    } else {
                         echo '<div class="wlgf-alert wlgf-alert--success">' . wp_kses_post($success_msg) . '</div>';
                    }
                    return;
               }

               // 2. Vulnerability Scanner & SQLi Exploit Shield (drops sqlmap / Acunetix / fuzzing probes)
               $exploit_check = function_exists('wlgf_detect_exploit_payload') ? wlgf_detect_exploit_payload($_POST) : array('is_exploit' => false);
               if (!empty($exploit_check['is_exploit'])) {
                    $success_msg = !empty($wlgf_user_message) ? $wlgf_user_message : __('Thank you! Your query has been successfully submitted.', 'lead-generation-form');
                    if (function_exists('wlgf_get_confirmation_html')) {
                         echo wlgf_get_confirmation_html($success_msg, $wlgf_settings);
                    } else {
                         echo '<div class="wlgf-alert wlgf-alert--success">' . wp_kses_post($success_msg) . '</div>';
                    }
                    return;
               }

               // 3. Submission Rate Limiting (stops rapid-fire automated scripts from flooding inbox)
               $form_id_sub = isset($_POST['wlgf_form_id']) ? sanitize_text_field(wp_unslash($_POST['wlgf_form_id'])) : '0';
               $rate_check = function_exists('wlgf_check_submission_rate_limit') ? wlgf_check_submission_rate_limit($form_id_sub) : array('allowed' => true);
               if (empty($rate_check['allowed'])) {
                    $rate_msg = !empty($rate_check['message']) ? $rate_check['message'] : __('Too many submissions detected from your IP address. Please wait a moment before trying again.', 'lead-generation-form');
                    echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-clock"></i> ' . esc_html($rate_msg) . '</div>';
                    return;
               }

               // Anti-spam timing check (2 seconds minimum)
               $wlgf_minimum_time = 2; // 2 seconds
               $wlgf_form_load_time = (float)$_POST['wlgf_form_load_time'];
               $wlgf_current_time = microtime(true) * 1000; // in milliseconds
               
               $wlgf_time_difference = ($wlgf_current_time - $wlgf_form_load_time) / 1000; // in seconds
               if ($wlgf_time_difference >= 0 && $wlgf_time_difference < $wlgf_minimum_time) {
                    // Reject submission
                    wp_die( esc_html__( 'Spam bot detected.', 'lead-generation-form' ), '', array( 'response' => 403 ) );
               }
              
               $wlgf_date_format = get_option('date_format');
               $wlgf_time_format = get_option('time_format');
               // Get the current date and time based on the WordPress timezone setting
               $wlgf_current_datetime = current_time('mysql');
               // Convert the MySQL date/time to PHP date/time
               $wlgf_formatted_datetime = date_i18n("{$wlgf_date_format} {$wlgf_time_format}", strtotime($wlgf_current_datetime));
               
               $wlgf_recaptcha = (isset($wlgf_settings['recaptcha'])) ? sanitize_text_field($wlgf_settings['recaptcha']) : 2;
               $wlgf_captcha_provider = isset($wlgf_settings['captcha_provider']) ? sanitize_text_field($wlgf_settings['captcha_provider']) : (($wlgf_recaptcha == '1') ? 'recaptcha' : (($wlgf_recaptcha == '3') ? 'turnstile' : 'none'));
               $wlgf_sitekey = (isset($wlgf_settings['sitekey'])) ? sanitize_text_field($wlgf_settings['sitekey']) : '';
               $wlgf_secretkey = (isset($wlgf_settings['secretkey'])) ? sanitize_text_field($wlgf_settings['secretkey']) : '';
               $wlgf_turnstile_sitekey = isset($wlgf_settings['turnstile_sitekey']) ? sanitize_text_field($wlgf_settings['turnstile_sitekey']) : '';
               $wlgf_turnstile_secretkey = isset($wlgf_settings['turnstile_secretkey']) ? sanitize_text_field($wlgf_settings['turnstile_secretkey']) : '';
               
               $wlgf_notify_admin = (isset($wlgf_settings['notify_admin'])) ? sanitize_text_field($wlgf_settings['notify_admin']) : '';
               $wlgf_email_engine = (!empty($wlgf_settings['email_engine'])) ? sanitize_text_field($wlgf_settings['email_engine']) : '1';
               $wlgf_smtp_host = (isset($wlgf_settings['smtp_host'])) ? sanitize_text_field($wlgf_settings['smtp_host']) : '';
               $wlgf_smtp_username = (isset($wlgf_settings['smtp_username'])) ? sanitize_text_field($wlgf_settings['smtp_username']) : '';
               $wlgf_smtp_password = (isset($wlgf_settings['smtp_password'])) ? sanitize_text_field($wlgf_settings['smtp_password']) : '';
               $wlgf_smtp_encryption = (isset($wlgf_settings['smtp_encryption'])) ? sanitize_text_field($wlgf_settings['smtp_encryption']) : '';
               $wlgf_smtp_port = (isset($wlgf_settings['smtp_port'])) ? sanitize_text_field($wlgf_settings['smtp_port']) : '';
               $sanitized_data = array();

               // 4. Cloudflare Turnstile Verification
               if ('turnstile' === $wlgf_captcha_provider && !empty($wlgf_turnstile_sitekey) && !empty($wlgf_turnstile_secretkey)) {
                    $turnstile_token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';
                    $turnstile_result = function_exists('wlgf_verify_turnstile_response')
                         ? wlgf_verify_turnstile_response($turnstile_token, $wlgf_turnstile_secretkey)
                         : array('success' => false, 'message' => __('Turnstile verification unavailable.', 'lead-generation-form'));

                    if (empty($turnstile_result['success'])) {
                         $err_msg = !empty($turnstile_result['message']) ? $turnstile_result['message'] : __('Cloudflare Turnstile verification failed. Please try again.', 'lead-generation-form');
                         echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-shield-halved"></i> ' . esc_html($err_msg) . '</div>';
                         return;
                    }
               }
               
               // reCAPTCHA enable check
               if(('recaptcha' === $wlgf_captcha_provider || $wlgf_recaptcha == 1) && 'turnstile' !== $wlgf_captcha_provider && $wlgf_sitekey && $wlgf_secretkey) {
                    
                    // reCAPTCHA validate
                    // Define the reCAPTCHA URL
                    $wlgf_recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';

                    // Sanitize incoming POST data
                    $wlgf_recaptcha_response = isset($_POST['recaptcha_response']) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_response'] ) ) : '';
                    $wlgf_remote_address = (isset($_SERVER['REMOTE_ADDR'])) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

                    // Prepare the data for the POST request
                    $wlgf_recaptcha_data = array(
                        'body' => array(
                            'secret'   => $wlgf_secretkey,
                            'response' => $wlgf_recaptcha_response,
                            'remoteip' => $wlgf_remote_address
                        )
                    );
                    
                    // Make a POST request
                    $wlgf_recaptcha_result = wp_remote_post($wlgf_recaptcha_url, $wlgf_recaptcha_data);
                    
                    // Check for a WP_Error
                     if (is_wp_error($wlgf_recaptcha_result)) {
                          $error_message = esc_html($wlgf_recaptcha_result->get_error_message());
                          echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-triangle-exclamation"></i> ' . sprintf( esc_html__( 'reCAPTCHA verification error: %s', 'lead-generation-form' ), $error_message ) . '</div>';
                          return;
                     } else {
                         // Decode the JSON response
                         $wlgf_recaptcha_result = json_decode(wp_remote_retrieve_body($wlgf_recaptcha_result), true);
                         
                         // Check the reCAPTCHA score and success status
                         if (!empty($wlgf_recaptcha_result['success']) && $wlgf_recaptcha_result['score'] >= 0.5) {
                              
                              if ( isset( $_POST['wlgf_form_id'] ) && isset( $_POST['wlgf_form_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['wlgf_form_nonce'] ) ), 'wlgf-form-post' ) ) {
                                   
                                   // saving post data into database start
                                   $wlgf_form_id = sanitize_text_field( wp_unslash($_POST['wlgf_form_id']));
                                   $wlgf_form_info = get_option('wlgf_form_'.$wlgf_form_id);
                                   if ( empty( $wlgf_form_info ) || ! is_array( $wlgf_form_info ) ) {
                                       echo '<div class="wlgf-alert wlgf-alert--danger">' . esc_html__( 'Form does not exist.', 'lead-generation-form' ) . '</div>';
                                       return;
                                   }

                                   // Backend Form Scheduling & Submission Quotas Check
                                   $now_timestamp   = current_time( 'timestamp' );
                                   $schedule_start  = ! empty( $wlgf_form_info['schedule_start'] ) ? strtotime( $wlgf_form_info['schedule_start'] ) : false;
                                   $schedule_end    = ! empty( $wlgf_form_info['schedule_end'] ) ? strtotime( $wlgf_form_info['schedule_end'] ) : false;
                                   $max_submissions = ! empty( $wlgf_form_info['max_submissions'] ) ? intval( $wlgf_form_info['max_submissions'] ) : 0;
                                   $closed_msg      = ! empty( $wlgf_form_info['closed_message'] ) ? $wlgf_form_info['closed_message'] : __( 'This form is currently closed for new submissions.', 'lead-generation-form' );

                                   if ( $schedule_start && $now_timestamp < $schedule_start ) {
                                       $start_date_formatted = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $schedule_start );
                                       echo '<div class="wlgf-alert wlgf-alert--warning"><i class="fa-solid fa-clock"></i> ' . sprintf( esc_html__( 'This form will open for submissions on %s.', 'lead-generation-form' ), esc_html( $start_date_formatted ) ) . '</div>';
                                       return;
                                   }
                                   if ( $schedule_end && $now_timestamp > $schedule_end ) {
                                       echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $closed_msg ) . '</div>';
                                       return;
                                   }
                                   if ( $max_submissions > 0 ) {
                                       $existing_leads = get_option( 'wlgf_saved_form_data_' . $wlgf_form_id, array() );
                                       if ( is_array( $existing_leads ) && count( $existing_leads ) >= $max_submissions ) {
                                           echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $closed_msg ) . '</div>';
                                           return;
                                       }
                                   }

                                   $wlgf_form_name = sanitize_text_field($wlgf_form_info['form_name']);
                                   
                                   // Extract textarea field names to preserve multiline formatting
                                   $wlgf_textarea_fields = array();
                                   $wlgf_file_field_configs = array();
                                   $wlgf_form_fields_raw = isset( $wlgf_form_info['form'] ) ? $wlgf_form_info['form'] : array();
                                   if ( is_string( $wlgf_form_fields_raw ) ) {
                                        $wlgf_form_fields_raw = json_decode( $wlgf_form_fields_raw, true );
                                   }
                                   if ( is_array( $wlgf_form_fields_raw ) ) {
                                        foreach ( $wlgf_form_fields_raw as $wlgf_fld ) {
                                             $f_type = is_array( $wlgf_fld ) ? ( isset( $wlgf_fld['type'] ) ? $wlgf_fld['type'] : '' ) : ( is_object( $wlgf_fld ) ? ( isset( $wlgf_fld->type ) ? $wlgf_fld->type : '' ) : '' );
                                             $f_name = is_array( $wlgf_fld ) ? ( isset( $wlgf_fld['name'] ) ? $wlgf_fld['name'] : '' ) : ( is_object( $wlgf_fld ) ? ( isset( $wlgf_fld->name ) ? $wlgf_fld->name : '' ) : '' );
                                             if ( 'textarea' === $f_type && ! empty( $f_name ) ) {
                                                  $wlgf_textarea_fields[] = $f_name;
                                              }
                                              if ( 'file' === $f_type && ! empty( $f_name ) ) {
                                                   $wlgf_file_field_configs[ $f_name ] = (array) $wlgf_fld;
                                             }
                                        }
                                   }

                                   $req_check = wlgf_validate_required_submission_fields( $wlgf_form_fields_raw, $_POST, isset( $_FILES ) ? $_FILES : array() );
                                   if ( ! $req_check['valid'] ) {
                                       echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-triangle-exclamation"></i> ' . esc_html( $req_check['message'] ) . '</div>';
                                       return;
                                   }

                                   foreach ( $_POST as $key => $value ) {
                                        if ( $key !== 'recaptcha_response' && $key !== 'wlgf_form_nonce' ) {
                                             // For array values like checkboxes
                                             if ( is_array( $value ) ) {
                                                  foreach ( $value as $index => $item ) {
                                                       $sanitized_data[$key][$index] = sanitize_text_field( wp_unslash( $item ) );
                                                  }
                                             } else {
                                                  $is_textarea = in_array( $key, $wlgf_textarea_fields, true )
                                                       || ( is_string( $value ) && ( false !== strpos( $value, "\n" ) || false !== strpos( $value, "\r" ) ) )
                                                       || false !== stripos( $key, 'message' )
                                                       || false !== stripos( $key, 'comment' )
                                                       || false !== stripos( $key, 'description' )
                                                       || false !== stripos( $key, 'detail' );

                                                  if ( $is_textarea ) {
                                                       $sanitized_data[$key] = sanitize_textarea_field( wp_unslash( $value ) );
                                                  } else {
                                                       $sanitized_data[$key] = sanitize_text_field( wp_unslash( $value ) );
                                                  }
                                             }
                                        }
                                   }
                                   
                                   // Check email and domains for blacklist
                                   $blacklist_check = function_exists('wlgf_check_submission_blacklist')
                                       ? wlgf_check_submission_blacklist( $sanitized_data, $wlgf_form_info, $wlgf_settings )
                                       : array('is_blocked' => false);

                                   if ( ! empty( $blacklist_check['is_blocked'] ) ) {
                                       $wlgf_blacklist_flag = 1;
                                       if ( 'silent_drop' === $blacklist_check['action'] ) {
                                           $success_msg = ! empty( $wlgf_form_info['custom_message'] ) ? $wlgf_form_info['custom_message'] : ( ! empty( $wlgf_settings['user_message'] ) ? $wlgf_settings['user_message'] : __( 'Thank you! Your query has been successfully submitted.', 'lead-generation-form' ) );
                                           if ( function_exists( 'wlgf_get_confirmation_html' ) ) {
                                               echo wlgf_get_confirmation_html( $success_msg, $wlgf_settings );
                                           } else {
                                               echo '<div class="wlgf-alert wlgf-alert--success">' . wp_kses_post( $success_msg ) . '</div>';
                                           }
                                           return;
                                       } else {
                                           $block_msg = ! empty( $blacklist_check['message'] ) ? $blacklist_check['message'] : __( 'Sorry! Your email address or domain is not permitted to submit this form.', 'lead-generation-form' );
                                           echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $block_msg ) . '</div>';
                                           return;
                                       }
                                   } else {
                                        
                                        // removing honeypot field from saved data
                                        if (array_key_exists('wlgf_form_id', $sanitized_data)) {
                                             unset($sanitized_data['wlgf_honeypot']);
                                             unset($sanitized_data['wlgf_hp_website_confirm']);
                                             unset($sanitized_data['cf-turnstile-response']);
                                             unset($sanitized_data['wlgf_form_load_time']);
                                             unset($sanitized_data['action']);
                                        }

                                        // Edition guard: strip PRO field submissions if Free edition
                                        if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) {
                                             foreach ( $sanitized_data as $k => $v ) {
                                                  if ( strpos( $k, 'signature' ) === 0 || strpos( $k, 'calc_' ) === 0 || strpos( $k, 'star_' ) === 0 || strpos( $k, 'nps_' ) === 0 ) {
                                                       unset( $sanitized_data[$k] );
                                                  }
                                             }
                                        }

                                        // Server-side payment verification (PRO-016)
                                        $payment_check = wlgf_verify_form_payment( $wlgf_form_id, $wlgf_form_info, $wlgf_settings, $sanitized_data );
                                        if ( empty( $payment_check['success'] ) ) {
                                             $err_msg = ! empty( $payment_check['error'] ) ? $payment_check['error'] : esc_html__( 'Payment verification failed.', 'lead-generation-form' );
                                             echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-triangle-exclamation"></i> ' . esc_html( $err_msg ) . '</div>';
                                             return;
                                        }

                                        // Check if newsletter opt-in was requested (PRO-008)
                                        $is_newsletter_optin = false;
                                        if ( ! empty( $wlgf_form_info['newsletter_enabled'] ) && 1 === (int) $wlgf_form_info['newsletter_enabled'] ) {
                                             $nl_mode = isset( $wlgf_form_info['newsletter_mode'] ) ? $wlgf_form_info['newsletter_mode'] : 'explicit';
                                             if ( 'automatic' === $nl_mode ) {
                                                  $is_newsletter_optin = true;
                                             } else {
                                                  $is_newsletter_optin = ( isset( $_POST['wlgf_newsletter_optin'] ) && '1' === (string) $_POST['wlgf_newsletter_optin'] );
                                             }
                                        }

                                        if ( $is_newsletter_optin ) {
                                             $current_settings = get_option( 'wlgf_settings', array() );
                                             $sanitized_data['_newsletter_optin']    = 1;
                                             $sanitized_data['_newsletter_date']     = current_time( 'mysql' );
                                             $sanitized_data['_newsletter_provider'] = isset( $current_settings['newsletter_provider'] ) ? sanitize_text_field( $current_settings['newsletter_provider'] ) : 'local';
                                             $sanitized_data['_newsletter_status']   = ( 'local' === $sanitized_data['_newsletter_provider'] || empty( $sanitized_data['_newsletter_provider'] ) ) ? 'local_only' : 'pending';
                                        }
                                        unset( $sanitized_data['wlgf_newsletter_optin'] );
                                        
                                        // Handle file upload start
                                        if (isset($_FILES) && !empty($_FILES)) {
                                             
                                             if ( ! function_exists( 'wp_handle_upload' ) ) {
                                                  require_once( ABSPATH . 'wp-admin/includes/file.php' );
                                             }
                                             
                                             foreach ($_FILES as $key => $file) {
                                                  
                                                  if ( is_array( $file ) && isset( $file['error'] ) ) {
                                                       if ( is_array( $file['error'] ) ) {
                                                            $has_real_files = false;
                                                            foreach ( $file['error'] as $err ) {
                                                                 $err_int = intval( $err );
                                                                 if ( $err_int === UPLOAD_ERR_OK ) {
                                                                      $has_real_files = true;
                                                                 } elseif ( $err_int !== UPLOAD_ERR_NO_FILE ) {
                                                                      echo 'File upload error. Code: ' . esc_html( $err_int );
                                                                      return;
                                                                 }
                                                            }
                                                            if ( ! $has_real_files ) {
                                                                 continue;
                                                            }
                                                       } else {
                                                            $error_code = intval( $file['error'] );
                                                            if ( $error_code === UPLOAD_ERR_NO_FILE ) {
                                                                 continue;
                                                            }
                                                            if ( $error_code !== UPLOAD_ERR_OK && $error_code >= 0 && $error_code <= 8 ) {
                                                                 echo 'File upload error. Code: ' . esc_html( $error_code );
                                                                 return;
                                                            }
                                                       }
                                                  }

                                                  // Prepare the upload directory
                                                  $wlgf_update_form_name = sanitize_file_name(str_replace(' ', '-', strtolower($wlgf_form_name)));
                                                  $wlgf_uploads = wp_upload_dir();
                                                  $wlgf_upload_path = wp_normalize_path($wlgf_uploads['basedir']) . '/' . $wlgf_update_form_name . '/';
                                                  $wlgf_upload_url = esc_url($wlgf_uploads['baseurl'] . '/' . $wlgf_update_form_name . '/');

                                                  if (!file_exists($wlgf_upload_path)) {
                                                       wp_mkdir_p($wlgf_upload_path);
                                                       if (!file_exists($wlgf_upload_path . 'index.php')) {
                                                            @file_put_contents($wlgf_upload_path . 'index.php', "<?php\n// Silence is golden.\n");
                                                       }
                                                       if (!file_exists($wlgf_upload_path . '.htaccess')) {
                                                            @file_put_contents($wlgf_upload_path . '.htaccess', "Options -Indexes\n<FilesMatch \"\.(php|phtml|php3|php4|php5|php7|phps|inc|pl|py|cgi)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>\n");
                                                       }
                                                  }
                                                   // Multi-file & single file upload handling with validation
                                                   $file_cfg = isset( $wlgf_file_field_configs[ $key ] ) ? $wlgf_file_field_configs[ $key ] : null;
                                                    $raw_allowed_types = ( $file_cfg && ! empty( $file_cfg['allowed_types'] ) ) ? (string) $file_cfg['allowed_types'] : 'jpg, jpeg, png, gif, webp, pdf, doc, docx, xls, xlsx, txt, csv, zip';
                                                    $wlgf_allowed_exts = array_filter( array_map( 'trim', explode( ',', str_replace( array( '.', ' ' ), array( '', '' ), strtolower( $raw_allowed_types ) ) ) ) );
                                                    if ( empty( $wlgf_allowed_exts ) ) {
                                                        $wlgf_allowed_exts = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip' );
                                                    }
                                                    $max_size_mb = ( $file_cfg && isset( $file_cfg['max_file_size'] ) && '' !== (string) $file_cfg['max_file_size'] && floatval( $file_cfg['max_file_size'] ) > 0 )
                                                        ? floatval( $file_cfg['max_file_size'] )
                                                        : ( ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ? 50 : 5 );
                                                    $wlgf_max_size = intval( $max_size_mb * 1024 * 1024 );

                                                   $wlgf_prefilter_callback = function ($f) {
                                                        $f['test_form'] = false;
                                                        return $f;
                                                   };
                                                   add_filter('wp_handle_upload_prefilter', $wlgf_prefilter_callback);

                                                   if (is_array($file['name'])) {
                                                        $uploaded_urls = array();
                                                        $file_count = count($file['name']);
                                                        for ($i = 0; $i < $file_count; $i++) {
                                                            if (empty($file['name'][$i]) || intval($file['error'][$i]) !== UPLOAD_ERR_OK) {
                                                                continue;
                                                            }
                                                            $file_ext = strtolower(pathinfo($file['name'][$i], PATHINFO_EXTENSION));
                                                            if (!in_array($file_ext, $wlgf_allowed_exts, true) || intval($file['size'][$i]) > $wlgf_max_size) {
                                                                continue;
                                                            }
                                                            $sub_file_array = array(
                                                                'name'     => sanitize_file_name($file['name'][$i]),
                                                                'type'     => sanitize_mime_type($file['type'][$i]),
                                                                'tmp_name' => $file['tmp_name'][$i],
                                                                'error'    => intval($file['error'][$i]),
                                                                'size'     => intval($file['size'][$i]),
                                                            );
                                                            $sub_uploaded = wp_handle_upload($sub_file_array, array('test_form' => false, 'test_type' => true));
                                                            if (!isset($sub_uploaded['error']) && !empty($sub_uploaded['url'])) {
                                                                $uploaded_urls[] = esc_url_raw($sub_uploaded['url']);
                                                            }
                                                        }
                                                        if (!empty($uploaded_urls)) {
                                                            $sanitized_data[$key] = count($uploaded_urls) === 1 ? $uploaded_urls[0] : wp_json_encode($uploaded_urls);
                                                        }
                                                   } else {
                                                        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                                        if (in_array($file_ext, $wlgf_allowed_exts, true) && intval($file['size']) <= $wlgf_max_size) {
                                                            $file_array = array(
                                                                'name'     => sanitize_file_name($file['name']),
                                                                'type'     => sanitize_mime_type($file['type']),
                                                                'tmp_name' => $file['tmp_name'],
                                                                'error'    => intval($file['error']),
                                                                'size'     => intval($file['size']),
                                                            );
                                                            $uploaded_file = wp_handle_upload($file_array, array('test_form' => false, 'test_type' => true));
                                                            if (!isset($uploaded_file['error']) && !empty($uploaded_file['url'])) {
                                                                $sanitized_data[$key] = esc_url_raw($uploaded_file['url']);
                                                            }
                                                        }
                                                   }
                                                   remove_filter('wp_handle_upload_prefilter', $wlgf_prefilter_callback);
                                             }
                                        } // Handle file upload end
                                        
                                        //append Date and Time key
                                        $sanitized_data['Date Time'] =  $wlgf_formatted_datetime;
                                        
                                         // get and append old data (or update matching abandoned draft)
                                         $wlgf_current_form_data = get_option("wlgf_saved_form_data_" . $wlgf_form_id, array());
                                         if ( ! is_array( $wlgf_current_form_data ) ) {
                                              $wlgf_current_form_data = array();
                                         }
                                         $draft_token = isset($_POST['wlgf_draft_token']) ? sanitize_text_field(wp_unslash($_POST['wlgf_draft_token'])) : '';
                                         $found_draft_idx = -1;
                                         if ( ! empty( $draft_token ) ) {
                                              foreach ( $wlgf_current_form_data as $idx => $lead_item ) {
                                                   if ( isset( $lead_item['_draft_token'] ) && $lead_item['_draft_token'] === $draft_token ) {
                                                        $found_draft_idx = $idx;
                                                        break;
                                                   }
                                              }
                                         }

                                         if ( ! isset( $sanitized_data['_status'] ) ) {
                                              $sanitized_data['_status'] = 'New';
                                         }
                                         unset( $sanitized_data['_abandoned'] );

                                         if ( $found_draft_idx >= 0 ) {
                                              $wlgf_current_form_data[ $found_draft_idx ] = $sanitized_data;
                                              $new_lead_index = $found_draft_idx;
                                         } else {
                                              $wlgf_current_form_data[] = $sanitized_data;
                                              $new_lead_index = count( $wlgf_current_form_data ) - 1;
                                         }
                                         if(update_option('wlgf_saved_form_data_'.$wlgf_form_id, $wlgf_current_form_data)){
                                              // Track Analytics Submission (PRO-017)
                                              $wlgf_analytics_key = 'wlgf_analytics_' . $wlgf_form_id;
                                              $wlgf_analytics = get_option( $wlgf_analytics_key, array(
                                                  'total_views'       => 0,
                                                  'total_submissions' => 0,
                                                  'daily'             => array(),
                                                  'step_views'        => array(),
                                                  'device_breakdown'  => array(),
                                              ) );
                                              $wlgf_analytics['total_submissions'] = isset( $wlgf_analytics['total_submissions'] ) ? ( $wlgf_analytics['total_submissions'] + 1 ) : 1;
                                              $wlgf_today = gmdate( 'Y-m-d' );
                                              if ( ! isset( $wlgf_analytics['daily'][ $wlgf_today ] ) ) {
                                                  $wlgf_analytics['daily'][ $wlgf_today ] = array( 'views' => 0, 'submissions' => 0 );
                                              }
                                              $wlgf_analytics['daily'][ $wlgf_today ]['submissions']++;
                                              update_option( $wlgf_analytics_key, $wlgf_analytics, false );

                                              $final_redirect_url = '';
                                              if ( isset( $wlgf_form_info['confirmation_type'] ) && 'redirect' === $wlgf_form_info['confirmation_type'] && ! empty( $wlgf_form_info['redirect_url'] ) ) {
                                                   $redirect_template = $wlgf_form_info['redirect_url'];
                                                   foreach ( $sanitized_data as $tag_k => $tag_v ) {
                                                        if ( is_scalar( $tag_v ) ) {
                                                             $redirect_template = str_replace( '{' . $tag_k . '}', rawurlencode( (string) $tag_v ), $redirect_template );
                                                        }
                                                   }
                                                   $redirect_template = str_replace( '{Submission_ID}', rawurlencode( (string) ( $new_lead_index + 1 ) ), $redirect_template );
                                                   $redirect_template = str_replace( '{Form_ID}', rawurlencode( (string) $wlgf_form_id ), $redirect_template );
                                                   $final_redirect_url = esc_url_raw( $redirect_template );
                                              }
                                              $redirect_attr = ! empty( $final_redirect_url ) ? ' data-redirect-url="' . esc_url( $final_redirect_url ) . '"' : '';
                                              $is_paid = ! empty( $sanitized_data['wlgf_payment_transaction_id'] ) && 'completed' === ( isset( $sanitized_data['wlgf_payment_status'] ) ? $sanitized_data['wlgf_payment_status'] : '' );
                                              if ( $is_paid ) {
                                                   $txn_ref = esc_html( $sanitized_data['wlgf_payment_transaction_id'] );
                                                   $amt_ref = isset( $sanitized_data['wlgf_payment_amount_paid'] ) ? esc_html( $sanitized_data['wlgf_payment_amount_paid'] ) : '';
                                                   $gw_ref  = isset( $sanitized_data['wlgf_payment_gateway'] ) ? strtoupper( esc_html( $sanitized_data['wlgf_payment_gateway'] ) ) : 'PAYMENT';
                                                   $custom_payment_msg = sprintf(
                                                        __( 'Payment Successful! We have received your %s payment of %s (Transaction ID: %s). Your order has been placed.', 'lead-generation-form' ),
                                                        $gw_ref,
                                                        ( ! empty( $amt_ref ) ? '$' . $amt_ref : '' ),
                                                        $txn_ref
                                                   );
                                                   $conf_card_html = function_exists( 'wlgf_get_confirmation_html' ) ? wlgf_get_confirmation_html( $custom_payment_msg, $wlgf_settings ) : esc_html( $custom_payment_msg );
                                                   $conf_fadeout_sec = 0;
                                              } else {
                                                   $base_msg = ! empty( $wlgf_form_info['custom_message'] ) ? $wlgf_form_info['custom_message'] : $wlgf_user_message;
                                                   $conf_fadeout_sec = isset( $wlgf_settings['confirmation_fadeout'] ) ? max( 5, min( 50, intval( $wlgf_settings['confirmation_fadeout'] ) ) ) : 10;
                                                   $conf_card_html = function_exists( 'wlgf_get_confirmation_html' ) ? wlgf_get_confirmation_html( $base_msg, $wlgf_settings ) : esc_html( $base_msg );
                                              }
                                              echo '<div id="' . esc_attr('wlgf-msg') . '"' . $redirect_attr . ' data-fadeout="' . esc_attr( $conf_fadeout_sec ) . '" data-is-paid="' . ( $is_paid ? '1' : '0' ) . '">' . $conf_card_html . '</div>';
                                         }
                                        
                                        // saving post data into database end
                                   }
                                   //end of blacklist check
                              }
                         } else {
                               $err_codes = ! empty( $wlgf_recaptcha_result['error-codes'] ) ? (array) $wlgf_recaptcha_result['error-codes'] : array();
                               $err_detail = '';
                               if ( current_user_can( 'manage_options' ) && ! empty( $err_codes ) ) {
                                   $err_detail = ' (Google reCAPTCHA: ' . implode( ', ', $err_codes ) . ')';
                               }
                               echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-triangle-exclamation"></i> ' . esc_html__( 'Sorry! your request has not been verified.', 'lead-generation-form' ) . esc_html( $err_detail ) . '</div>';
                               $wlgf_blacklist_flag = 1;
                               $wlgf_error_message = __( 'Sorry! your request has not been verified.', 'lead-generation-form' );
                         }
                    }
               } else {
                    // reCAPTCHA is disabled check
                    if ( isset( $_POST['wlgf_form_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['wlgf_form_nonce'] ) ), 'wlgf-form-post' ) ) {
                         // saving post data into database start
                          $wlgf_form_id = sanitize_text_field( wp_unslash( $_POST['wlgf_form_id'] ) );
                          $wlgf_form_info = get_option('wlgf_form_'.$wlgf_form_id);
                          if ( empty( $wlgf_form_info ) || ! is_array( $wlgf_form_info ) ) {
                              echo '<div class="wlgf-alert wlgf-alert--danger">' . esc_html__( 'Form does not exist.', 'lead-generation-form' ) . '</div>';
                              return;
                          }

                          // Backend Form Scheduling & Submission Quotas Check
                          $now_timestamp   = current_time( 'timestamp' );
                          $schedule_start  = ! empty( $wlgf_form_info['schedule_start'] ) ? strtotime( $wlgf_form_info['schedule_start'] ) : false;
                          $schedule_end    = ! empty( $wlgf_form_info['schedule_end'] ) ? strtotime( $wlgf_form_info['schedule_end'] ) : false;
                          $max_submissions = ! empty( $wlgf_form_info['max_submissions'] ) ? intval( $wlgf_form_info['max_submissions'] ) : 0;
                          $closed_msg      = ! empty( $wlgf_form_info['closed_message'] ) ? $wlgf_form_info['closed_message'] : __( 'This form is currently closed for new submissions.', 'lead-generation-form' );

                          if ( $schedule_start && $now_timestamp < $schedule_start ) {
                              $start_date_formatted = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $schedule_start );
                              echo '<div class="wlgf-alert wlgf-alert--warning"><i class="fa-solid fa-clock"></i> ' . sprintf( esc_html__( 'This form will open for submissions on %s.', 'lead-generation-form' ), esc_html( $start_date_formatted ) ) . '</div>';
                              return;
                          }
                          if ( $schedule_end && $now_timestamp > $schedule_end ) {
                              echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $closed_msg ) . '</div>';
                              return;
                          }
                          if ( $max_submissions > 0 ) {
                              $existing_leads = get_option( 'wlgf_saved_form_data_' . $wlgf_form_id, array() );
                              if ( is_array( $existing_leads ) && count( $existing_leads ) >= $max_submissions ) {
                                  echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $closed_msg ) . '</div>';
                                  return;
                              }
                          }
                          $wlgf_form_name = sanitize_text_field($wlgf_form_info['form_name']);
                         $sanitized_data = array();

                         // Extract textarea field names to preserve multiline formatting
                         $wlgf_textarea_fields = array();
                         $wlgf_form_fields_raw = isset( $wlgf_form_info['form'] ) ? $wlgf_form_info['form'] : array();
                         if ( is_string( $wlgf_form_fields_raw ) ) {
                              $wlgf_form_fields_raw = json_decode( $wlgf_form_fields_raw, true );
                         }
                         if ( is_array( $wlgf_form_fields_raw ) ) {
                              foreach ( $wlgf_form_fields_raw as $wlgf_fld ) {
                                   $f_type = is_array( $wlgf_fld ) ? ( isset( $wlgf_fld['type'] ) ? $wlgf_fld['type'] : '' ) : ( is_object( $wlgf_fld ) ? ( isset( $wlgf_fld->type ) ? $wlgf_fld->type : '' ) : '' );
                                   $f_name = is_array( $wlgf_fld ) ? ( isset( $wlgf_fld['name'] ) ? $wlgf_fld['name'] : '' ) : ( is_object( $wlgf_fld ) ? ( isset( $wlgf_fld->name ) ? $wlgf_fld->name : '' ) : '' );
                                   if ( 'textarea' === $f_type && ! empty( $f_name ) ) {
                                        $wlgf_textarea_fields[] = $f_name;
                                   }
                              }
                         }

                         $req_check = wlgf_validate_required_submission_fields( $wlgf_form_fields_raw, $_POST, isset( $_FILES ) ? $_FILES : array() );
                         if ( ! $req_check['valid'] ) {
                             echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-triangle-exclamation"></i> ' . esc_html( $req_check['message'] ) . '</div>';
                             return;
                         }

                         foreach ( $_POST as $key => $value ) {
                              if ( $key !== 'recaptcha_response' && $key !== 'wlgf_form_nonce' ) {
                                   // For array values like checkboxes
                                   if ( is_array( $value ) ) {
                                        foreach ( $value as $index => $item ) {
                                             $sanitized_data[$key][$index] = sanitize_text_field( wp_unslash( $item ) );
                                        }
                                   } else {
                                        $is_textarea = in_array( $key, $wlgf_textarea_fields, true )
                                             || ( is_string( $value ) && ( false !== strpos( $value, "\n" ) || false !== strpos( $value, "\r" ) ) )
                                             || false !== stripos( $key, 'message' )
                                             || false !== stripos( $key, 'comment' )
                                             || false !== stripos( $key, 'description' )
                                             || false !== stripos( $key, 'detail' );

                                        if ( $is_textarea ) {
                                             $sanitized_data[$key] = sanitize_textarea_field( wp_unslash( $value ) );
                                        } else {
                                             $sanitized_data[$key] = sanitize_text_field( wp_unslash( $value ) );
                                        }
                                   }
                              }
                         }
                         
                         // Check email and domains for blacklist
                         $blacklist_check = function_exists('wlgf_check_submission_blacklist')
                             ? wlgf_check_submission_blacklist( $sanitized_data, $wlgf_form_info, $wlgf_settings )
                             : array('is_blocked' => false);

                         if ( ! empty( $blacklist_check['is_blocked'] ) ) {
                             $wlgf_blacklist_flag = 1;
                             if ( 'silent_drop' === $blacklist_check['action'] ) {
                                 $success_msg = ! empty( $wlgf_form_info['custom_message'] ) ? $wlgf_form_info['custom_message'] : ( ! empty( $wlgf_settings['user_message'] ) ? $wlgf_settings['user_message'] : __( 'Thank you! Your query has been successfully submitted.', 'lead-generation-form' ) );
                                 if ( function_exists( 'wlgf_get_confirmation_html' ) ) {
                                     echo wlgf_get_confirmation_html( $success_msg, $wlgf_settings );
                                 } else {
                                     echo '<div class="wlgf-alert wlgf-alert--success">' . wp_kses_post( $success_msg ) . '</div>';
                                 }
                                 return;
                             } else {
                                 $block_msg = ! empty( $blacklist_check['message'] ) ? $blacklist_check['message'] : __( 'Sorry! Your email address or domain is not permitted to submit this form.', 'lead-generation-form' );
                                 echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $block_msg ) . '</div>';
                                 return;
                             }
                         } else {
                              
                              // removing honeypot field from saved data
                              if (array_key_exists('wlgf_form_id', $sanitized_data)) {
                                   unset($sanitized_data['wlgf_honeypot']);
                                   unset($sanitized_data['wlgf_hp_website_confirm']);
                                   unset($sanitized_data['cf-turnstile-response']);
                                   unset($sanitized_data['wlgf_form_load_time']);
                                   unset($sanitized_data['action']);
                              }

                              // Server-side payment verification (PRO-016)
                              $payment_check = wlgf_verify_form_payment( $wlgf_form_id, $wlgf_form_info, $wlgf_settings, $sanitized_data );
                              if ( empty( $payment_check['success'] ) ) {
                                   $err_msg = ! empty( $payment_check['error'] ) ? $payment_check['error'] : esc_html__( 'Payment verification failed.', 'lead-generation-form' );
                                   echo '<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-triangle-exclamation"></i> ' . esc_html( $err_msg ) . '</div>';
                                   return;
                              }

                              // Check if newsletter opt-in was requested (PRO-008)
                              $is_newsletter_optin = false;
                              if ( ! empty( $wlgf_form_info['newsletter_enabled'] ) && 1 === (int) $wlgf_form_info['newsletter_enabled'] ) {
                                   $nl_mode = isset( $wlgf_form_info['newsletter_mode'] ) ? $wlgf_form_info['newsletter_mode'] : 'explicit';
                                   if ( 'automatic' === $nl_mode ) {
                                        $is_newsletter_optin = true;
                                   } else {
                                        $is_newsletter_optin = ( isset( $_POST['wlgf_newsletter_optin'] ) && '1' === (string) $_POST['wlgf_newsletter_optin'] );
                                   }
                              }

                              if ( $is_newsletter_optin ) {
                                   $current_settings = get_option( 'wlgf_settings', array() );
                                   $sanitized_data['_newsletter_optin']    = 1;
                                   $sanitized_data['_newsletter_date']     = current_time( 'mysql' );
                                   $sanitized_data['_newsletter_provider'] = isset( $current_settings['newsletter_provider'] ) ? sanitize_text_field( $current_settings['newsletter_provider'] ) : 'local';
                                   $sanitized_data['_newsletter_status']   = ( 'local' === $sanitized_data['_newsletter_provider'] || empty( $sanitized_data['_newsletter_provider'] ) ) ? 'local_only' : 'pending';
                              }
                              unset( $sanitized_data['wlgf_newsletter_optin'] );
                              
                              // Handle file upload start
                              if (isset($_FILES) && !empty($_FILES)) {
                                  if (!function_exists('wp_handle_upload')) {
                                      require_once(ABSPATH . 'wp-admin/includes/file.php');
                                  }

                                  foreach ($_FILES as $key => $file) {
                                       if (is_array($file) && isset($file['error'])) {
                                           if (is_array($file['error'])) {
                                               $has_real_files = false;
                                               foreach ($file['error'] as $err) {
                                                   $err_int = intval($err);
                                                   if ($err_int === UPLOAD_ERR_OK) {
                                                       $has_real_files = true;
                                                   } elseif ($err_int !== UPLOAD_ERR_NO_FILE) {
                                                       echo 'File upload error. Code: ' . esc_html($err_int);
                                                       return;
                                                   }
                                               }
                                               if (!$has_real_files) {
                                                   continue;
                                               }
                                           } else {
                                               $error_code = intval($file['error']);
                                               if ($error_code === UPLOAD_ERR_NO_FILE) {
                                                   continue;
                                               }
                                               if ($error_code !== UPLOAD_ERR_OK && $error_code >= 0 && $error_code <= 8) {
                                                   echo 'File upload error. Code: ' . esc_html($error_code);
                                                   return;
                                               }
                                           }
                                       }

                                      // Prepare the upload directory
                                      $wlgf_update_form_name = sanitize_file_name(str_replace(' ', '-', strtolower($wlgf_form_name)));
                                      $wlgf_uploads = wp_upload_dir();
                                      $wlgf_upload_path = wp_normalize_path($wlgf_uploads['basedir']) . '/' . $wlgf_update_form_name . '/';
                                      $wlgf_upload_url = esc_url($wlgf_uploads['baseurl'] . '/' . $wlgf_update_form_name . '/');

                                      // Create the upload directory if it doesn't exist
                                      if (!file_exists($wlgf_upload_path)) {
                                          wp_mkdir_p($wlgf_upload_path);
                                          if (!file_exists($wlgf_upload_path . 'index.php')) {
                                               @file_put_contents($wlgf_upload_path . 'index.php', "<?php\n// Silence is golden.\n");
                                          }
                                          if (!file_exists($wlgf_upload_path . '.htaccess')) {
                                               @file_put_contents($wlgf_upload_path . '.htaccess', "Options -Indexes\n<FilesMatch \"\.(php|phtml|php3|php4|php5|php7|phps|inc|pl|py|cgi)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>\n");
                                          }
                                      }
                                                   // Multi-file & single file upload handling with validation
                                                   $wlgf_allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip');
                                                   $wlgf_max_size     = defined('WLGF_IS_PRO') && WLGF_IS_PRO ? 52428800 : 5242880;

                                                    $wlgf_prefilter_callback2 = function ($f) {
                                                         $f['test_form'] = false;
                                                         return $f;
                                                    };
                                                    add_filter('wp_handle_upload_prefilter', $wlgf_prefilter_callback2);

                                                   if (is_array($file['name'])) {
                                                        $uploaded_urls = array();
                                                        $file_count = count($file['name']);
                                                        for ($i = 0; $i < $file_count; $i++) {
                                                            if (empty($file['name'][$i]) || intval($file['error'][$i]) !== UPLOAD_ERR_OK) {
                                                                continue;
                                                            }
                                                            $file_ext = strtolower(pathinfo($file['name'][$i], PATHINFO_EXTENSION));
                                                            if (!in_array($file_ext, $wlgf_allowed_exts, true) || intval($file['size'][$i]) > $wlgf_max_size) {
                                                                continue;
                                                            }
                                                            $sub_file_array = array(
                                                                'name'     => sanitize_file_name($file['name'][$i]),
                                                                'type'     => sanitize_mime_type($file['type'][$i]),
                                                                'tmp_name' => $file['tmp_name'][$i],
                                                                'error'    => intval($file['error'][$i]),
                                                                'size'     => intval($file['size'][$i]),
                                                            );
                                                            $sub_uploaded = wp_handle_upload($sub_file_array, array('test_form' => false, 'test_type' => true));
                                                            if (!isset($sub_uploaded['error']) && !empty($sub_uploaded['url'])) {
                                                                $uploaded_urls[] = esc_url_raw($sub_uploaded['url']);
                                                            }
                                                        }
                                                        if (!empty($uploaded_urls)) {
                                                            $sanitized_data[$key] = count($uploaded_urls) === 1 ? $uploaded_urls[0] : wp_json_encode($uploaded_urls);
                                                        }
                                                   } else {
                                                        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                                        if (in_array($file_ext, $wlgf_allowed_exts, true) && intval($file['size']) <= $wlgf_max_size) {
                                                            $file_array = array(
                                                                'name'     => sanitize_file_name($file['name']),
                                                                'type'     => sanitize_mime_type($file['type']),
                                                                'tmp_name' => $file['tmp_name'],
                                                                'error'    => intval($file['error']),
                                                                'size'     => intval($file['size']),
                                                            );
                                                            $uploaded_file = wp_handle_upload($file_array, array('test_form' => false, 'test_type' => true));
                                                            if (!isset($uploaded_file['error']) && !empty($uploaded_file['url'])) {
                                                                $sanitized_data[$key] = esc_url_raw($uploaded_file['url']);
                                                            }
                                                        }
                                                   }
                                                   remove_filter('wp_handle_upload_prefilter', $wlgf_prefilter_callback2);
                                  }
                              } // Handle file upload end

                              
                              //append Date and Time key
                              $sanitized_data['Date Time'] =  $wlgf_formatted_datetime;

                              // get and append old data (or update matching abandoned draft)
                              $wlgf_current_form_data = get_option("wlgf_saved_form_data_" . $wlgf_form_id, array());
                              if ( ! is_array( $wlgf_current_form_data ) ) {
                                   $wlgf_current_form_data = array();
                              }
                              $draft_token = isset($_POST['wlgf_draft_token']) ? sanitize_text_field(wp_unslash($_POST['wlgf_draft_token'])) : '';
                              $found_draft_idx = -1;
                              if ( ! empty( $draft_token ) ) {
                                   foreach ( $wlgf_current_form_data as $idx => $lead_item ) {
                                        if ( isset( $lead_item['_draft_token'] ) && $lead_item['_draft_token'] === $draft_token ) {
                                             $found_draft_idx = $idx;
                                             break;
                                        }
                                   }
                              }

                              if ( ! isset( $sanitized_data['_status'] ) ) {
                                   $sanitized_data['_status'] = 'New';
                              }
                              unset( $sanitized_data['_abandoned'] );

                              if ( $found_draft_idx >= 0 ) {
                                   $wlgf_current_form_data[ $found_draft_idx ] = $sanitized_data;
                                   $new_lead_index = $found_draft_idx;
                              } else {
                                   $wlgf_current_form_data[] = $sanitized_data;
                                   $new_lead_index = count( $wlgf_current_form_data ) - 1;
                              }
                              if(update_option('wlgf_saved_form_data_'.$wlgf_form_id, $wlgf_current_form_data)){
                                   // Track Analytics Submission (PRO-017)
                                   $wlgf_analytics_key = 'wlgf_analytics_' . $wlgf_form_id;
                                   $wlgf_analytics = get_option( $wlgf_analytics_key, array(
                                       'total_views'       => 0,
                                       'total_submissions' => 0,
                                       'daily'             => array(),
                                       'step_views'        => array(),
                                       'device_breakdown'  => array(),
                                   ) );
                                   $wlgf_analytics['total_submissions'] = isset( $wlgf_analytics['total_submissions'] ) ? ( $wlgf_analytics['total_submissions'] + 1 ) : 1;
                                   $wlgf_today = gmdate( 'Y-m-d' );
                                   if ( ! isset( $wlgf_analytics['daily'][ $wlgf_today ] ) ) {
                                       $wlgf_analytics['daily'][ $wlgf_today ] = array( 'views' => 0, 'submissions' => 0 );
                                   }
                                   $wlgf_analytics['daily'][ $wlgf_today ]['submissions']++;
                                   update_option( $wlgf_analytics_key, $wlgf_analytics, false );

                                   $final_redirect_url = '';
                                   if ( isset( $wlgf_form_info['confirmation_type'] ) && 'redirect' === $wlgf_form_info['confirmation_type'] && ! empty( $wlgf_form_info['redirect_url'] ) ) {
                                        $redirect_template = $wlgf_form_info['redirect_url'];
                                        foreach ( $sanitized_data as $tag_k => $tag_v ) {
                                             if ( is_scalar( $tag_v ) ) {
                                                  $redirect_template = str_replace( '{' . $tag_k . '}', rawurlencode( (string) $tag_v ), $redirect_template );
                                             }
                                        }
                                        $redirect_template = str_replace( '{Submission_ID}', rawurlencode( (string) ( $new_lead_index + 1 ) ), $redirect_template );
                                        $redirect_template = str_replace( '{Form_ID}', rawurlencode( (string) $wlgf_form_id ), $redirect_template );
                                        $final_redirect_url = esc_url_raw( $redirect_template );
                                   }
                                   $redirect_attr = ! empty( $final_redirect_url ) ? ' data-redirect-url="' . esc_url( $final_redirect_url ) . '"' : '';
                                   $is_paid = ! empty( $sanitized_data['wlgf_payment_transaction_id'] ) && 'completed' === ( isset( $sanitized_data['wlgf_payment_status'] ) ? $sanitized_data['wlgf_payment_status'] : '' );
                                   if ( $is_paid ) {
                                        $txn_ref = esc_html( $sanitized_data['wlgf_payment_transaction_id'] );
                                        $amt_ref = isset( $sanitized_data['wlgf_payment_amount_paid'] ) ? esc_html( $sanitized_data['wlgf_payment_amount_paid'] ) : '';
                                        $gw_ref  = isset( $sanitized_data['wlgf_payment_gateway'] ) ? strtoupper( esc_html( $sanitized_data['wlgf_payment_gateway'] ) ) : 'PAYMENT';
                                        $custom_payment_msg = sprintf(
                                             __( 'Payment Successful! We have received your %s payment of %s (Transaction ID: %s). Your order has been placed.', 'lead-generation-form' ),
                                             $gw_ref,
                                             ( ! empty( $amt_ref ) ? '$' . $amt_ref : '' ),
                                             $txn_ref
                                        );
                                        $conf_card_html = function_exists( 'wlgf_get_confirmation_html' ) ? wlgf_get_confirmation_html( $custom_payment_msg, $wlgf_settings ) : esc_html( $custom_payment_msg );
                                        $conf_fadeout_sec = 0;
                                   } else {
                                        $base_msg = ! empty( $wlgf_form_info['custom_message'] ) ? $wlgf_form_info['custom_message'] : $wlgf_user_message;
                                        $conf_fadeout_sec = isset( $wlgf_settings['confirmation_fadeout'] ) ? max( 5, min( 50, intval( $wlgf_settings['confirmation_fadeout'] ) ) ) : 10;
                                        $conf_card_html = function_exists( 'wlgf_get_confirmation_html' ) ? wlgf_get_confirmation_html( $base_msg, $wlgf_settings ) : esc_html( $base_msg );
                                   }
                                   echo '<div id="' . esc_attr('wlgf-msg') . '"' . $redirect_attr . ' data-fadeout="' . esc_attr( $conf_fadeout_sec ) . '" data-is-paid="' . ( $is_paid ? '1' : '0' ) . '">' . $conf_card_html . '</div>';
                              }
                              // saving post data into database end
                         }
                         //end of blacklist check
                    }
               }
               // end reCaptcha else



               // send email start
               if($wlgf_notify_admin == 1 && $wlgf_blacklist_flag == 0) {
                    
                    $wlgf_blog_name = wp_specialchars_decode( get_bloginfo( 'name', 'raw' ), ENT_QUOTES );
                    $wlgf_email_to = get_option('admin_email'); // admin email for wp_mail()
                    // Define email recipient, subject and Prepare email body
                    
                    $wlgf_body_allowed_tags = array(
                        'strong' => array(),
                        'br' => array(),
                        'hr' => array(),
                        'h3' => array()
                    );
                    $wlgf_email_txt1 = __("Details are as follows", 'lead-generation-form').": "."<br><br>";
                    $wlgf_email_body = wp_kses("$wlgf_email_txt1", $wlgf_body_allowed_tags);
                    $wlgf_email_txt2 = wp_kses("<strong>", $wlgf_body_allowed_tags) . __("Form ID", 'lead-generation-form').wp_kses("</strong>", $wlgf_body_allowed_tags).": ".$wlgf_form_id.wp_kses("<br>", $wlgf_body_allowed_tags);
                    $wlgf_email_body .= wp_kses($wlgf_email_txt2, $wlgf_body_allowed_tags);
                    
                    // Filter system, token, and hidden internal keys from email data
                    $wlgf_email_lead_data = array();
                    $wlgf_system_keys = array( 
                        'wlgf_form_id', 
                        'wlgf_form_load_time', 
                        'wlgf_honeypot', 
                        'wlgf_hp_website_confirm', 
                        'wlgf_draft_token', 
                        'wlgf_carbon_copy', 
                        'wlgf_nonce', 
                        'wlgf_form_nonce', 
                        'action', 
                        '_wpnonce', 
                        '_wp_http_referer', 
                        'recaptcha_response', 
                        'g-recaptcha-response', 
                        'cf-turnstile-response' 
                    );

                    foreach ($sanitized_data as $key => $value) {
                         $key_lower = strtolower(trim($key));
                         if ( 
                             in_array($key, $wlgf_system_keys, true) || 
                             in_array($key_lower, $wlgf_system_keys, true) || 
                             0 === strpos($key, '_') || 
                             0 === strpos($key_lower, 'wlgf_') || 
                             false !== strpos($key_lower, 'draft_token') 
                         ) {
                              continue;
                         }
                         $wlgf_email_lead_data[$key] = is_string($value) ? wp_unslash($value) : $value;
                    }
                    
                    $wlgf_AddReplyToFieldName = '';
                    $wlgf_AddReplyToFieldEmail = '';

                    foreach ($wlgf_email_lead_data as $key => $value) {
                         if (is_array($value)) {
                              $value = implode(', ', $value); // Convert array values to comma-separated string
                         }
                         $val_trimmed = is_string($value) ? trim($value) : '';
                         $key_clean = strtolower(trim($key));

                         // Smart detection of submitter Name
                         if (empty($wlgf_AddReplyToFieldName) && !empty($val_trimmed)) {
                              if (strcasecmp($key, "Name") == 0 || false !== stripos($key_clean, 'name') || false !== stripos($key_clean, 'fname')) {
                                   $wlgf_AddReplyToFieldName = sanitize_text_field($val_trimmed);
                              }
                         }

                         // Smart detection of submitter Email
                         if (empty($wlgf_AddReplyToFieldEmail) && !empty($val_trimmed)) {
                              if (strcasecmp($key, "Email") == 0 || false !== stripos($key_clean, 'email') || is_email($val_trimmed)) {
                                   if (is_email($val_trimmed)) {
                                        $wlgf_AddReplyToFieldEmail = sanitize_email($val_trimmed);
                                   }
                              }
                         }

                         $wlgf_email_body .=  wp_kses("<strong>", $wlgf_body_allowed_tags) . ucfirst(str_replace(array('-', '_'), ' ', $key)) . wp_kses("</strong>", $wlgf_body_allowed_tags).": " . nl2br(esc_html($value)) . "<br><br>";
                    }

                    // Wrap in modern responsive email template if available
                    if ( class_exists( 'WLGF_Email_Template' ) ) {
                         $wlgf_email_body = WLGF_Email_Template::render_admin_notification( $wlgf_form_id, $wlgf_form_name, $wlgf_email_lead_data, $wlgf_formatted_datetime );
                    }
                    
                    $wlgf_reply_name_display = ! empty( $wlgf_AddReplyToFieldName ) ? $wlgf_AddReplyToFieldName : __('a prospective lead', 'lead-generation-form');
                    $wlgf_email_subject = wp_specialchars_decode( $wlgf_blog_name . " - " . __('A new query received by', 'lead-generation-form') . " " . $wlgf_reply_name_display, ENT_QUOTES );
                    
                    // Set headers safely
                    $wlgf_headers = array(
                         'Content-Type: text/html; charset=UTF-8',
                    );

                    $clean_blog_name = function_exists('wlgf_get_from_name') ? wlgf_get_from_name() : str_replace( array( '"', "\r", "\n" ), '', wp_specialchars_decode( $wlgf_blog_name, ENT_QUOTES ) );
                    $wlgf_from_email = function_exists('wlgf_get_from_email') ? wlgf_get_from_email( $wlgf_email_engine ) : $wlgf_email_to;
                    $wlgf_headers[]  = 'From: "' . $clean_blog_name . '" <' . $wlgf_from_email . '>';

                    // Only add Reply-To header if a valid submitter email was captured (prevents PHPMailer empty address exception)
                    if ( ! empty( $wlgf_AddReplyToFieldEmail ) && is_email( $wlgf_AddReplyToFieldEmail ) ) {
                         $clean_reply_name = ! empty( $wlgf_AddReplyToFieldName ) ? str_replace( array( '"', "\r", "\n" ), '', wp_specialchars_decode( $wlgf_AddReplyToFieldName, ENT_QUOTES ) ) : 'Lead';
                         $wlgf_headers[]   = 'Reply-To: "' . $clean_reply_name . '" <' . $wlgf_AddReplyToFieldEmail . '>';
                    }

                    // Add CC Additional Recipients from Global Settings and Form
                    $all_cc = array();
                    if ( ! empty( $wlgf_settings['cc_admin_emails'] ) ) {
                         $split_cc = preg_split( '/[\r\n,]+/', $wlgf_settings['cc_admin_emails'] );
                         foreach ( $split_cc as $cc_em ) {
                              $cc_em = sanitize_email( trim( $cc_em ) );
                              if ( is_email( $cc_em ) && ! in_array( $cc_em, $all_cc, true ) && strtolower( $cc_em ) !== strtolower( $wlgf_email_to ) ) {
                                   $all_cc[] = $cc_em;
                              }
                         }
                    }

                    if ( ! empty( $_POST['wlgf_carbon_copy'] ) && defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
                         $cc_raw = sanitize_text_field( wp_unslash( $_POST['wlgf_carbon_copy'] ) );
                         $split_cc = preg_split( '/[\r\n,]+/', $cc_raw );
                         foreach ( $split_cc as $cc_em ) {
                              $cc_em = sanitize_email( trim( $cc_em ) );
                              if ( is_email( $cc_em ) && ! in_array( $cc_em, $all_cc, true ) && strtolower( $cc_em ) !== strtolower( $wlgf_email_to ) ) {
                                   $all_cc[] = $cc_em;
                              }
                         }
                    }

                    foreach ( $all_cc as $cc_email ) {
                         $wlgf_headers[] = 'Cc: ' . $cc_email;
                    }
                    
                     // Send the email via wp_mail() or SMTP
                     if ( '2' === (string) $wlgf_email_engine ) {
                          // Hook into PHPMailer to set SMTP settings
                          add_action( 'phpmailer_init', 'wlgf_configure_smtp', 999 );
                     }
                     $wlgf_sent = wp_mail( $wlgf_email_to, $wlgf_email_subject, $wlgf_email_body, $wlgf_headers );
                     if ( ! $wlgf_sent && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                          error_log( 'WLGF: Admin lead notification email failed to dispatch via wp_mail() to ' . $wlgf_email_to . ' for Form ID #' . $wlgf_form_id );
                     }
                     if ( '2' === (string) $wlgf_email_engine ) {
                          remove_action( 'phpmailer_init', 'wlgf_configure_smtp', 999 );
                     }
                     if ( class_exists( 'WLGF_Email_Template' ) ) {
                          WLGF_Email_Template::clear_pending_embedded_images();
                     }
                    
                     // send email end
               } else {
                    // unable to send email
               }

               // Submitter Auto-Responder Email Dispatch
               if ( $wlgf_blacklist_flag == 0 && ! empty( $wlgf_form_info['auto_reply_enabled'] ) && ! empty( $wlgf_AddReplyToFieldEmail ) && is_email( $wlgf_AddReplyToFieldEmail ) ) {
                    $clean_blog_name = function_exists('wlgf_get_from_name') ? wlgf_get_from_name() : str_replace( array( '"', "\r", "\n" ), '', wp_specialchars_decode( $wlgf_blog_name, ENT_QUOTES ) );
                    $auto_reply_subj = ! empty( $wlgf_form_info['auto_reply_subject'] ) ? $wlgf_form_info['auto_reply_subject'] : ( $clean_blog_name . ' - Thank you for contacting us' );
                    $auto_reply_tmpl = ! empty( $wlgf_form_info['auto_reply_body'] ) ? $wlgf_form_info['auto_reply_body'] : "Hi {Name},\n\nThank you for reaching out to us. We have received your inquiry regarding \"{Form_Name}\" and will get back to you shortly.\n\nBest regards,\n{Site_Name}";

                    $replacements = array(
                         '{Name}'            => ! empty( $wlgf_AddReplyToFieldName ) ? $wlgf_AddReplyToFieldName : 'Friend',
                         '{Email}'           => $wlgf_AddReplyToFieldEmail,
                         '{Site_Name}'       => $clean_blog_name,
                         '{Site_Title}'      => $clean_blog_name,
                         '{Website_Name}'    => $clean_blog_name,
                         '{Form_Name}'       => $wlgf_form_name,
                         '{Submission_Date}' => $wlgf_formatted_datetime,
                    );

                    if ( ! empty( $sanitized_data['wlgf_payment_status'] ) && 'completed' === $sanitized_data['wlgf_payment_status'] ) {
                         $curr = ! empty( $wlgf_settings['payment_currency'] ) ? $wlgf_settings['payment_currency'] : 'USD';
                         $replacements['{Payment_Transaction_ID}'] = ! empty( $sanitized_data['wlgf_payment_transaction_id'] ) ? $sanitized_data['wlgf_payment_transaction_id'] : '';
                         $replacements['{Payment_Amount}']         = ! empty( $sanitized_data['wlgf_payment_amount_paid'] ) ? ( $curr . ' ' . $sanitized_data['wlgf_payment_amount_paid'] ) : '';
                         $replacements['{Payment_Gateway}']        = ! empty( $sanitized_data['wlgf_payment_gateway'] ) ? ucfirst( $sanitized_data['wlgf_payment_gateway'] ) : '';
                         $replacements['{Payment_Status}']         = 'Completed';
                    }

                    $parsed_subject = wp_specialchars_decode( str_replace( array_keys( $replacements ), array_values( $replacements ), $auto_reply_subj ), ENT_QUOTES );
                    $parsed_message = str_replace( array_keys( $replacements ), array_values( $replacements ), $auto_reply_tmpl );

                    if ( class_exists( 'WLGF_Email_Template' ) ) {
                         $parsed_body = WLGF_Email_Template::render_auto_responder( $wlgf_form_id, $wlgf_form_name, $parsed_subject, $parsed_message, $wlgf_formatted_datetime );
                    } else {
                         $parsed_body = nl2br( esc_html( $parsed_message ) );
                    }

                    $auto_from_email = function_exists('wlgf_get_from_email') ? wlgf_get_from_email( $wlgf_email_engine ) : $wlgf_email_to;
                    $auto_headers = array(
                         'Content-Type: text/html; charset=UTF-8',
                         'From: "' . $clean_blog_name . '" <' . $auto_from_email . '>',
                    );

                    if ( $wlgf_email_engine == 2 ) {
                         add_action( 'phpmailer_init', 'wlgf_configure_smtp', 999 );
                    }

                    wp_mail( $wlgf_AddReplyToFieldEmail, $parsed_subject, $parsed_body, $auto_headers );

                    if ( $wlgf_email_engine == 2 ) {
                         remove_action( 'phpmailer_init', 'wlgf_configure_smtp', 999 );
                    }
                    if ( class_exists( 'WLGF_Email_Template' ) ) {
                         WLGF_Email_Template::clear_pending_embedded_images();
                    }
               }

               // Asynchronous Webhook Dispatch (PRO-007)
               if ( $wlgf_blacklist_flag == 0 && ! empty( $wlgf_form_info['webhook_url'] ) && filter_var( $wlgf_form_info['webhook_url'], FILTER_VALIDATE_URL ) ) {
                    $webhook_headers = array(
                         'Content-Type' => 'application/json',
                    );
                    if ( ! empty( $wlgf_form_info['webhook_auth'] ) ) {
                         $webhook_headers['Authorization'] = sanitize_text_field( $wlgf_form_info['webhook_auth'] );
                    }

                    $webhook_payload = array(
                         'form_id'         => $wlgf_form_id,
                         'form_name'       => $wlgf_form_name,
                         'submission_date' => $wlgf_formatted_datetime,
                         'data'            => $sanitized_data,
                    );

                    wp_remote_post(
                         $wlgf_form_info['webhook_url'],
                         array(
                              'blocking'    => false,
                              'timeout'     => 5,
                              'headers'     => $webhook_headers,
                              'body'        => wp_json_encode( $webhook_payload ),
                              'data_format' => 'body',
                         )
                    );
               }

               // Submissions Tracking (PRO-010)
               if ( $wlgf_blacklist_flag == 0 ) {
                    $current_submissions = intval( get_option( 'wlgf_form_submissions_' . $wlgf_form_id, 0 ) );
                    update_option( 'wlgf_form_submissions_' . $wlgf_form_id, $current_submissions + 1, false );
               }

               // Newsletter Audience Sync Dispatch (PRO-008 / CRIT-1 / R1)
               if ( $wlgf_blacklist_flag == 0 && ! empty( $is_newsletter_optin ) ) {
                    if ( function_exists( 'fastcgi_finish_request' ) ) {
                         fastcgi_finish_request();
                    }
                    wlgf_dispatch_newsletter_sync( $wlgf_form_id, $wlgf_form_info, $sanitized_data, isset( $new_lead_index ) ? $new_lead_index : null );
               }
          }
          echo "</div>";
          // handling form submission end
          
          return ob_get_clean();
     } // end of isset id
}