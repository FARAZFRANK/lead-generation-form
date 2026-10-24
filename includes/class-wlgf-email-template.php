<?php
/**
 * Lead Generation Form - Modern Responsive Email Template Builder
 *
 * @package LeadGenerationForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

class WLGF_Email_Template {

	/**
	 * Pending inline MIME image attachments for wp_mail (PHPMailer).
	 * Format: $cid => array( 'type' => 'string'|'file', 'data' => binary, 'path' => filepath, 'name' => filename, 'mime' => mimetype )
	 *
	 * @var array
	 */
	public static $pending_embedded_images = array();

	/**
	 * Clear all pending inline embedded images
	 */
	public static function clear_pending_embedded_images() {
		self::$pending_embedded_images = array();
	}

	/**
	 * Get default settings for the email template
	 *
	 * @return array
	 */
	public static function get_defaults() {
		$site_title = wp_specialchars_decode( get_bloginfo( 'name', 'raw' ), ENT_QUOTES );
		return array(
			'email_logo_url'           => '',
			'email_logo_width'         => '180',
			'email_header_title'       => '{form_name} - New Lead Submission',
			'email_header_bg'          => '#1e293b',
			'email_header_color'       => '#ffffff',
			'email_body_greeting'      => __( 'Hello Admin, a new inquiry has just been submitted on your website. Below are the complete submission details:', 'lead-generation-form' ),
			'email_body_bg'            => '#f1f5f9',
			'email_card_bg'            => '#ffffff',
			'email_text_color'         => '#334155',
			'email_footer_text'        => sprintf(
				( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO )
					/* translators: %s: site title */
					? __( 'This notification was automatically sent by Lead Generation Form Pro on %s.', 'lead-generation-form' )
					/* translators: %s: site title */
					: __( 'This notification was automatically sent by Lead Generation Form on %s.', 'lead-generation-form' ),
				'{site_title}'
			),
			'email_footer_bg'          => '#f8fafc',
			'email_footer_color'       => '#64748b',
			'email_show_dashboard_btn' => '1',
		);
	}

	/**
	 * Get active template settings merging saved options with defaults
	 *
	 * @param array $custom_overrides
	 * @return array
	 */
	public static function get_settings( $custom_overrides = array() ) {
		$defaults       = self::get_defaults();
		$saved_settings = get_option( 'wlgf_settings', array() );
		$merged         = array();

		foreach ( $defaults as $k => $default_val ) {
			if ( isset( $custom_overrides[ $k ] ) ) {
				$merged[ $k ] = $custom_overrides[ $k ];
			} elseif ( isset( $saved_settings[ $k ] ) && '' !== $saved_settings[ $k ] ) {
				$merged[ $k ] = $saved_settings[ $k ];
			} else {
				$merged[ $k ] = $default_val;
			}
		}

		return $merged;
	}

	/**
	 * Render full responsive HTML email for admin lead notifications
	 *
	 * @param string|int $form_id
	 * @param string     $form_name
	 * @param array      $lead_data
	 * @param string     $timestamp
	 * @param array      $custom_settings
	 * @return string Full HTML email
	 */
	public static function render_admin_notification( $form_id, $form_name, $lead_data, $timestamp = '', $custom_settings = array() ) {
		// Reset pending inline attachments for fresh email render
		self::$pending_embedded_images = array();

		$cfg        = self::get_settings( $custom_settings );
		$site_title = wp_specialchars_decode( get_bloginfo( 'name', 'raw' ), ENT_QUOTES );
		$site_url   = home_url();

		if ( empty( $timestamp ) ) {
			$timestamp = current_time( 'mysql' );
		}
		if ( empty( $form_name ) ) {
			$form_name = 'Form #' . $form_id;
		}

		// Placeholder replacements
		$replace_keys = array( '{form_name}', '{site_title}', '{site_url}', '{form_id}' );
		$replace_vals = array( esc_html( $form_name ), esc_html( $site_title ), esc_url( $site_url ), esc_html( $form_id ) );

		$header_title = str_replace( $replace_keys, $replace_vals, $cfg['email_header_title'] );
		$greeting     = str_replace( $replace_keys, $replace_vals, $cfg['email_body_greeting'] );
		$footer_text  = str_replace( $replace_keys, $replace_vals, $cfg['email_footer_text'] );

		// Filter lead data to exclude system/hidden keys
		$filtered_data = array();
		$excluded_keys = array( 
			'wlgf_form_id', 
			'wlgf_form_load_time', 
			'wlgf_honeypot', 
			'wlgf_hp_website_confirm', 
			'wlgf_nonce', 
			'wlgf_form_nonce', 
			'wlgf_draft_token', 
			'wlgf_carbon_copy', 
			'action', 
			'_wpnonce', 
			'_wp_http_referer', 
			'recaptcha_response', 
			'g-recaptcha-response', 
			'cf-turnstile-response', 
			'wlgf_payment_status', 
			'wlgf_payment_transaction_id', 
			'wlgf_payment_amount_paid', 
			'wlgf_payment_gateway', 
			'wlgf_newsletter_optin' 
		);

		if ( is_array( $lead_data ) ) {
			foreach ( $lead_data as $key => $val ) {
				$key_clean = strtolower( trim( $key ) );
				if ( 
					in_array( $key, $excluded_keys, true ) || 
					in_array( $key_clean, $excluded_keys, true ) || 
					0 === strpos( $key, '_' ) || 
					0 === strpos( $key_clean, 'wlgf_' ) || 
					false !== strpos( $key_clean, 'draft_token' ) 
				) {
					continue;
				}
				$val_str = is_array( $val ) ? implode( ', ', $val ) : (string) $val;
				$filtered_data[ $key ] = wp_unslash( $val_str );
			}
		}

		// Fetch form structure to map option values to labels (e.g. eng -> Engineering & Product)
		$field_options_map = array();
		if ( ! empty( $form_id ) ) {
			$form_record = get_option( 'wlgf_form_' . $form_id );
			$raw_form    = isset( $form_record['form'] ) ? $form_record['form'] : '';
			$structure   = is_array( $raw_form ) ? $raw_form : ( is_string( $raw_form ) ? json_decode( $raw_form, true ) : array() );
			if ( is_array( $structure ) ) {
				foreach ( $structure as $item ) {
					$item_name   = is_object( $item ) ? ( $item->name ?? '' ) : ( $item['name'] ?? '' );
					$item_label  = is_object( $item ) ? ( $item->label ?? '' ) : ( $item['label'] ?? '' );
					$item_values = is_object( $item ) ? ( $item->values ?? array() ) : ( $item['values'] ?? array() );
					if ( ! empty( $item_values ) && ( is_array( $item_values ) || is_object( $item_values ) ) ) {
						$map = array();
						foreach ( $item_values as $opt ) {
							$opt_val = is_object( $opt ) ? ( $opt->value ?? '' ) : ( $opt['value'] ?? '' );
							$opt_lbl = is_object( $opt ) ? ( $opt->label ?? '' ) : ( $opt['label'] ?? '' );
							if ( '' !== (string) $opt_val && '' !== (string) $opt_lbl ) {
								$map[ (string) $opt_val ] = (string) $opt_lbl;
							}
						}
						if ( ! empty( $map ) ) {
							if ( ! empty( $item_name ) ) $field_options_map[ $item_name ] = $map;
							if ( ! empty( $item_label ) ) $field_options_map[ $item_label ] = $map;
						}
					}
				}
			}
		}

		// Build table rows
		$rows_html = '';
		$i = 0;
		foreach ( $filtered_data as $field_key => $field_val ) {
			$row_bg        = ( $i % 2 === 0 ) ? '#ffffff' : '#f8fafc';
			$display_label = esc_html( ucfirst( str_replace( array( '-', '_' ), ' ', $field_key ) ) );

			// Check option map
			$resolved_val = $field_val;
			$opt_map      = null;
			$clean_key    = strtolower( preg_replace( '/[^a-z0-9]/', '', (string) $field_key ) );
			foreach ( $field_options_map as $map_k => $m ) {
				$clean_m = strtolower( preg_replace( '/[^a-z0-9]/', '', (string) $map_k ) );
				if ( $clean_m === $clean_key ) {
					$opt_map = $m;
					break;
				}
			}

			if ( $opt_map ) {
				if ( is_array( $resolved_val ) ) {
					$mapped = array();
					foreach ( $resolved_val as $sub_v ) {
						$mapped[] = isset( $opt_map[ (string) $sub_v ] ) ? $opt_map[ (string) $sub_v ] : $sub_v;
					}
					$resolved_val = implode( ', ', $mapped );
				} elseif ( isset( $opt_map[ (string) $resolved_val ] ) ) {
					$resolved_val = $opt_map[ (string) $resolved_val ];
				}
			}

			$str_val = is_string( $resolved_val ) ? trim( $resolved_val ) : ( ( null !== $resolved_val ) ? (string) $resolved_val : '' );

			// Format display value
			if ( '' === $str_val || null === $resolved_val ) {
				$val_html = '<span style="color: #94a3b8; font-size: 13px;">—</span>';
			} elseif ( 0 === strpos( $str_val, 'data:image' ) ) {
				$is_preview = ! empty( $custom_settings['is_preview'] );
				if ( $is_preview ) {
					// In-browser preview (e.g. settings modal): web browsers natively support data:image URIs
					$sig_src = esc_attr( $str_val );
				} else {
					// Outgoing email dispatch: Gmail/webmail strictly blocks data:image URIs!
					// Extract binary data and register as RFC Content-ID (CID) inline MIME attachment via PHPMailer
					$parts = explode( ',', $str_val, 2 );
					$b64   = isset( $parts[1] ) ? $parts[1] : $str_val;
					$bin   = base64_decode( $b64 );

					$mime = 'image/png';
					if ( preg_match( '/^data:(image\/[a-zA-Z0-9\+\-\.]+);base64/', $str_val, $m_matches ) ) {
						$mime = $m_matches[1];
					}

					$cid = 'wlgf_sig_' . substr( md5( $str_val . '_' . $field_key ), 0, 12 );
					self::$pending_embedded_images[ $cid ] = array(
						'type' => 'string',
						'data' => $bin,
						'name' => 'digital-signature.png',
						'mime' => $mime,
					);
					$sig_src = 'cid:' . $cid;
				}

				$val_html = '
				<div style="display: inline-block; background-color: #ffffff; padding: 6px 10px; border: 1px dashed #cbd5e1; border-radius: 6px; text-align: center;">
					<img src="' . $sig_src . '" alt="' . esc_attr__( 'Digital Signature', 'lead-generation-form' ) . '" style="max-height: 65px; max-width: 220px; height: auto; border: 0; outline: none; text-decoration: none; display: block; margin: 0 auto 4px auto;">
					<span style="font-size: 11px; font-weight: 600; color: #059669; display: inline-block; line-height: 1;">&#10003; ' . esc_html__( 'Digitally Signed', 'lead-generation-form' ) . '</span>
				</div>';
			} elseif ( ( 0 === strpos( $str_val, 'http://' ) || 0 === strpos( $str_val, 'https://' ) ) && preg_match( '/\.(jpg|jpeg|png|gif|webp)$/i', $str_val ) ) {
				$img_url       = esc_url( $str_val );
				$img_src       = $img_url;
				$file_name     = esc_html( basename( wp_parse_url( $str_val, PHP_URL_PATH ) ) );
				$is_preview    = ! empty( $custom_settings['is_preview'] );
				$dirs          = wp_upload_dir();
				$is_local_host = ( false !== strpos( $str_val, 'localhost' ) || false !== strpos( $str_val, '127.0.0.1' ) );

				// If hosted on localhost and file exists locally, embed via CID so external webmail (like Gmail) can display the thumbnail
				if ( ! $is_preview && $is_local_host && false !== strpos( $str_val, $dirs['baseurl'] ) ) {
					$local_path = str_replace( $dirs['baseurl'], $dirs['basedir'], $str_val );
					if ( file_exists( $local_path ) ) {
						$cid = 'wlgf_att_' . substr( md5( $local_path ), 0, 12 );
						$ft  = wp_check_filetype( $local_path );
						self::$pending_embedded_images[ $cid ] = array(
							'type' => 'file',
							'path' => $local_path,
							'name' => basename( $local_path ),
							'mime' => ! empty( $ft['type'] ) ? $ft['type'] : 'image/jpeg',
						);
						$img_src = 'cid:' . $cid;
					}
				}

				$val_html = '<a href="' . $img_url . '" target="_blank" style="color: #2563eb; text-decoration: underline;"><img src="' . $img_src . '" style="max-height: 70px; max-width: 140px; border-radius: 4px; border: 1px solid #e2e8f0; display: block; margin-bottom: 4px;" alt="' . esc_attr( $file_name ) . '"><span style="font-size: 11.5px;">' . $file_name . '</span></a>';
			} else {
				$val_html = nl2br( esc_html( $str_val ) );
			}

			$rows_html .= '<tr style="background-color: ' . esc_attr( $row_bg ) . ';">' .
				'<td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13.5px; font-weight: 600; color: #475569; width: 35%; vertical-align: top; line-height: 1.5; margin: 0;">' . $display_label . '</td>' .
				'<td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13.5px; color: #1e293b; width: 65%; vertical-align: top; line-height: 1.5; margin: 0; word-break: break-word;">' . $val_html . '</td>' .
			'</tr>';
			$i++;
		}

		// Logo markup
		$logo_html = '';
		if ( ! empty( $cfg['email_logo_url'] ) ) {
			$logo_width = ! empty( $cfg['email_logo_width'] ) ? intval( $cfg['email_logo_width'] ) : 180;
			$logo_src   = esc_url( $cfg['email_logo_url'] );

			// If sending actual email (not in-browser preview) and logo is on localhost/local uploads, use inline CID attachment
			// This allows email clients (like Gmail) to render the logo without needing public internet access to localhost!
			if ( empty( $custom_settings['is_preview'] ) ) {
				$dirs = wp_upload_dir();
				$is_local_host = ( false !== strpos( $cfg['email_logo_url'], 'localhost' ) || false !== strpos( $cfg['email_logo_url'], '127.0.0.1' ) );
				if ( $is_local_host && false !== strpos( $cfg['email_logo_url'], $dirs['baseurl'] ) ) {
					$local_path = str_replace( $dirs['baseurl'], $dirs['basedir'], $cfg['email_logo_url'] );
					if ( file_exists( $local_path ) ) {
						$logo_src = 'cid:wlgf_email_logo';
					}
				}
			}

			$logo_html = '
			<div style="text-align: center; margin-bottom: 16px;">
				<img src="' . $logo_src . '" alt="' . esc_attr( $site_title ) . '" style="max-width: ' . esc_attr( $logo_width ) . 'px; max-height: 65px; height: auto; border: 0; outline: none; text-decoration: none; display: inline-block;">
			</div>';
		}

		// Dashboard CTA Button
		$dashboard_btn_html = '';
		if ( ! empty( $cfg['email_show_dashboard_btn'] ) && '1' === (string) $cfg['email_show_dashboard_btn'] ) {
			$leads_url = admin_url( 'admin.php?page=wlgf-leads&wlgf-form-id=' . $form_id );
			$dashboard_btn_html = '
			<div style="text-align: center; margin-top: 26px; margin-bottom: 8px;">
				<a href="' . esc_url( $leads_url ) . '" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #ffffff; font-size: 13.5px; font-weight: 600; text-decoration: none; padding: 11px 24px; border-radius: 6px; box-shadow: 0 2px 4px rgba(37,99,235,0.25);">
					' . esc_html__( 'View Lead in Dashboard →', 'lead-generation-form' ) . '
				</a>
			</div>';
		}

		$html = '
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>' . esc_html( $header_title ) . '</title>
	<style>
		body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
		table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
		img { -ms-interpolation-mode: bicubic; }
		body { margin: 0; padding: 0; width: 100% !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
		@media only screen and (max-width: 620px) {
			.wlgf-email-container { width: 100% !important; }
			.wlgf-email-content { padding: 16px !important; }
		}
	</style>
</head>
<body style="margin: 0; padding: 0; background-color: ' . esc_attr( $cfg['email_body_bg'] ) . '; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: ' . esc_attr( $cfg['email_body_bg'] ) . '; padding: 30px 15px;">
		<tr>
			<td align="center">
				<!-- Outer Email Card Container -->
				<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" class="wlgf-email-container" style="max-width: 600px; width: 100%; background-color: ' . esc_attr( $cfg['email_card_bg'] ) . '; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
					
					<!-- Header Banner -->
					<tr>
						<td style="background-color: ' . esc_attr( $cfg['email_header_bg'] ) . '; padding: 30px 24px; text-align: center; color: ' . esc_attr( $cfg['email_header_color'] ) . ';">
							' . $logo_html . '
							<h1 style="margin: 0; font-size: 21px; font-weight: 700; color: ' . esc_attr( $cfg['email_header_color'] ) . '; line-height: 1.35;">
								' . esc_html( $header_title ) . '
							</h1>
							<div style="margin-top: 8px; font-size: 13px; opacity: 0.85; color: ' . esc_attr( $cfg['email_header_color'] ) . ';">
								<span><strong style="color: inherit;">' . esc_html( $form_name ) . '</strong></span>
								<span style="margin: 0 6px;">•</span>
								<span>' . esc_html( $timestamp ) . '</span>
							</div>
						</td>
					</tr>

					<!-- Body Content Area -->
					<tr>
						<td class="wlgf-email-content" style="padding: 28px 26px; color: ' . esc_attr( $cfg['email_text_color'] ) . ';">
							
							<!-- Greeting / Intro -->
							<p style="margin: 0 0 18px 0; font-size: 14.5px; line-height: 1.6; color: ' . esc_attr( $cfg['email_text_color'] ) . ';">
								' . nl2br( esc_html( $greeting ) ) . '
							</p>

							<!-- Field Details Table -->
							<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
								<thead>
									<tr style="background-color: #f1f5f9;">
										<th align="left" style="padding: 10px 16px; border-bottom: 2px solid #cbd5e1; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; width: 35%; line-height: 1.4; vertical-align: middle; margin: 0;">
											' . esc_html__( 'Field', 'lead-generation-form' ) . '
										</th>
										<th align="left" style="padding: 10px 16px; border-bottom: 2px solid #cbd5e1; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; width: 65%; line-height: 1.4; vertical-align: middle; margin: 0;">
											' . esc_html__( 'Submitted Value', 'lead-generation-form' ) . '
										</th>
									</tr>
								</thead>
								<tbody>
									' . $rows_html . '
								</tbody>
							</table>

							<!-- Submission Metadata Box -->
							<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px;">
								<tr>
									<td style="padding: 12px 16px; font-size: 12px; color: #64748b; line-height: 1.55;">
										<div><strong>' . esc_html__( 'Form ID:', 'lead-generation-form' ) . '</strong> #' . esc_html( $form_id ) . ' &nbsp;|&nbsp; <strong>' . esc_html__( 'Received:', 'lead-generation-form' ) . '</strong> ' . esc_html( $timestamp ) . '</div>
										<div><strong>' . esc_html__( 'Source Website:', 'lead-generation-form' ) . '</strong> <a href="' . esc_url( $site_url ) . '" target="_blank" style="color: #2563eb; text-decoration: none;">' . esc_html( $site_title ) . '</a></div>
									</td>
								</tr>
							</table>

							' . $dashboard_btn_html . '
						</td>
					</tr>

					<!-- Footer Banner -->
					<tr>
						<td style="background-color: ' . esc_attr( $cfg['email_footer_bg'] ) . '; padding: 20px 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: ' . esc_attr( $cfg['email_footer_color'] ) . '; line-height: 1.55;">
							<div style="margin-bottom: 6px; color: ' . esc_attr( $cfg['email_footer_color'] ) . ';">
								' . nl2br( esc_html( $footer_text ) ) . '
							</div>
							<div style="font-size: 11px; opacity: 0.8; color: ' . esc_attr( $cfg['email_footer_color'] ) . ';">
								&copy; ' . esc_html( gmdate( 'Y' ) ) . ' ' . esc_html( $site_title ) . '. ' . esc_html__( 'All rights reserved.', 'lead-generation-form' ) . '
							</div>
						</td>
					</tr>

				</table>
				<!-- /Outer Email Card Container -->
			</td>
		</tr>
	</table>
</body>
</html>';

		return $html;
	}

	/**
	 * Render live preview with realistic sample lead data
	 *
	 * @param array $custom_settings
	 * @return string Full preview HTML
	 */
	public static function get_preview_html( $custom_settings = array() ) {
		$sample_data = array(
			'Full Name'            => 'Alexander Wright',
			'Email Address'        => 'alexander.wright@innovations.com',
			'Phone Number'         => '+1 (555) 389-2041',
			'Company Name'         => 'Apex Growth Innovations LLC',
			'Interested Service'   => 'Enterprise Lead Automation & Analytics',
			'Budget Estimate'      => '$5,000 - $10,000 USD',
			'Detailed Inquiry'     => "Hello,\nWe are interested in deploying your dynamic forms across our corporate landing pages with CRM pipeline automation. Please contact us to arrange an onboarding consultation.\n\nThank you!",
		);

		$custom_settings['is_preview'] = true;
		return self::render_admin_notification(
			'1',
			__( 'Lead Inquiry & Consultation Form', 'lead-generation-form' ),
			$sample_data,
			current_time( 'mysql' ),
			$custom_settings
		);
	}

	/**
	 * Render responsive branded HTML email for submitter auto-responder confirmations
	 *
	 * @param string|int $form_id
	 * @param string     $form_name
	 * @param string     $subject
	 * @param string     $message_body
	 * @param string     $timestamp
	 * @param array      $custom_settings
	 * @return string Full HTML email
	 */
	public static function render_auto_responder( $form_id, $form_name, $subject, $message_body, $timestamp = '', $custom_settings = array() ) {
		$cfg        = self::get_settings( $custom_settings );
		$site_title = wp_specialchars_decode( get_bloginfo( 'name', 'raw' ), ENT_QUOTES );
		$site_url   = home_url();

		if ( empty( $timestamp ) ) {
			$timestamp = current_time( 'mysql' );
		}
		if ( empty( $form_name ) ) {
			$form_name = 'Form #' . $form_id;
		}
		if ( empty( $subject ) ) {
			/* translators: %s: site title */
			$subject = sprintf( __( '[%s] We received your submission', 'lead-generation-form' ), $site_title );
		}

		// Logo markup
		$logo_html = '';
		if ( ! empty( $cfg['email_logo_url'] ) ) {
			$logo_width = ! empty( $cfg['email_logo_width'] ) ? intval( $cfg['email_logo_width'] ) : 180;
			$logo_src   = esc_url( $cfg['email_logo_url'] );

			// If sending actual email (not in-browser preview) and logo is on localhost/local uploads, use inline CID attachment
			if ( empty( $custom_settings['is_preview'] ) ) {
				$dirs = wp_upload_dir();
				$is_local_host = ( false !== strpos( $cfg['email_logo_url'], 'localhost' ) || false !== strpos( $cfg['email_logo_url'], '127.0.0.1' ) );
				if ( $is_local_host && false !== strpos( $cfg['email_logo_url'], $dirs['baseurl'] ) ) {
					$local_path = str_replace( $dirs['baseurl'], $dirs['basedir'], $cfg['email_logo_url'] );
					if ( file_exists( $local_path ) ) {
						$logo_src = 'cid:wlgf_email_logo';
					}
				}
			}

			$logo_html = '
			<div style="text-align: center; margin-bottom: 16px;">
				<img src="' . $logo_src . '" alt="' . esc_attr( $site_title ) . '" style="max-width: ' . esc_attr( $logo_width ) . 'px; max-height: 65px; height: auto; border: 0; outline: none; text-decoration: none; display: inline-block;">
			</div>';
		}

		$replace_keys = array( '{form_name}', '{site_title}', '{site_url}', '{form_id}' );
		$replace_vals = array( esc_html( $form_name ), esc_html( $site_title ), esc_url( $site_url ), esc_html( $form_id ) );
		$footer_text  = str_replace( $replace_keys, $replace_vals, $cfg['email_footer_text'] );

		// Clean and format message body
		$formatted_msg = nl2br( wp_kses_post( $message_body ) );

		$html = '
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>' . esc_html( $subject ) . '</title>
	<style>
		body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
		table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
		img { -ms-interpolation-mode: bicubic; }
		body { margin: 0; padding: 0; width: 100% !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
		@media only screen and (max-width: 620px) {
			.wlgf-email-container { width: 100% !important; }
			.wlgf-email-content { padding: 20px 16px !important; }
		}
	</style>
</head>
<body style="margin: 0; padding: 0; background-color: ' . esc_attr( $cfg['email_body_bg'] ) . '; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: ' . esc_attr( $cfg['email_body_bg'] ) . '; padding: 30px 15px;">
		<tr>
			<td align="center">
				<!-- Outer Email Card Container -->
				<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" class="wlgf-email-container" style="max-width: 600px; width: 100%; background-color: ' . esc_attr( $cfg['email_card_bg'] ) . '; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
					
					<!-- Header Banner -->
					<tr>
						<td style="background-color: ' . esc_attr( $cfg['email_header_bg'] ) . '; padding: 30px 24px; text-align: center; color: ' . esc_attr( $cfg['email_header_color'] ) . ';">
							' . $logo_html . '
							<h1 style="margin: 0; font-size: 21px; font-weight: 700; color: ' . esc_attr( $cfg['email_header_color'] ) . '; line-height: 1.35;">
								' . esc_html( $subject ) . '
							</h1>
							<div style="margin-top: 8px; font-size: 13px; opacity: 0.85; color: ' . esc_attr( $cfg['email_header_color'] ) . ';">
								<span>' . esc_html( $timestamp ) . '</span>
							</div>
						</td>
					</tr>

					<!-- Body Content Area -->
					<tr>
						<td class="wlgf-email-content" style="padding: 32px 28px; color: ' . esc_attr( $cfg['email_text_color'] ) . ';">
							
							<!-- Confirmation Status Badge -->
							<div style="display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 20px; padding: 4px 14px; margin-bottom: 22px;">
								<span style="font-size: 12.5px; font-weight: 600; color: #059669;">
									&#10003; ' . esc_html__( 'Submission Successfully Received', 'lead-generation-form' ) . '
								</span>
							</div>

							<!-- Submitter Message Content -->
							<div style="font-size: 15px; line-height: 1.7; color: ' . esc_attr( $cfg['email_text_color'] ) . '; margin-bottom: 24px;">
								' . $formatted_msg . '
							</div>

							<!-- Reference Metadata Card -->
							<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
								<tr>
									<td style="padding: 14px 18px; font-size: 12px; color: #64748b; line-height: 1.6;">
										<div><strong>' . esc_html__( 'Date & Time:', 'lead-generation-form' ) . '</strong> ' . esc_html( $timestamp ) . '</div>
										<div><strong>' . esc_html__( 'Website:', 'lead-generation-form' ) . '</strong> <a href="' . esc_url( $site_url ) . '" target="_blank" style="color: #2563eb; text-decoration: none;">' . esc_html( $site_title ) . '</a></div>
									</td>
								</tr>
							</table>
						</td>
					</tr>

					<!-- Footer Banner -->
					<tr>
						<td style="background-color: ' . esc_attr( $cfg['email_footer_bg'] ) . '; padding: 20px 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: ' . esc_attr( $cfg['email_footer_color'] ) . '; line-height: 1.55;">
							<div style="margin-bottom: 6px; color: ' . esc_attr( $cfg['email_footer_color'] ) . ';">
								' . nl2br( esc_html( $footer_text ) ) . '
							</div>
							<div style="font-size: 11px; opacity: 0.8; color: ' . esc_attr( $cfg['email_footer_color'] ) . ';">
								&copy; ' . esc_html( gmdate( 'Y' ) ) . ' ' . esc_html( $site_title ) . '. ' . esc_html__( 'All rights reserved.', 'lead-generation-form' ) . '
							</div>
						</td>
					</tr>

				</table>
				<!-- /Outer Email Card Container -->
			</td>
		</tr>
	</table>
</body>
</html>';

		return $html;
	}

	/**
	 * Render live preview with sample auto-responder data
	 *
	 * @param array $custom_settings
	 * @return string Full preview HTML
	 */
	public static function get_auto_responder_preview_html( $custom_settings = array() ) {
		$custom_settings['is_preview'] = true;
		$sample_subj = __( 'Thank you for your inquiry! We received your message.', 'lead-generation-form' );
		$sample_body = "Hi Alexander,\n\nThank you for reaching out to us. We have received your inquiry regarding \"Lead Inquiry & Consultation Form\" and our team will get back to you within 24 hours.\n\nBest regards,\nSupport Team";
		return self::render_auto_responder(
			'1',
			__( 'Lead Inquiry & Consultation Form', 'lead-generation-form' ),
			$sample_subj,
			$sample_body,
			current_time( 'mysql' ),
			$custom_settings
		);
	}
}
