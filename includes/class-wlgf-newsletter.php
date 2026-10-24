<?php
/**
 * Lead Generation Form - Unified Newsletter & Marketing Connectors Engine (PRO-006 / PRO-008)
 * Handles live connection verification and automated contact syncing for:
 * Mailchimp, Brevo (Sendinblue), MailerLite, SendGrid, and ActiveCampaign.
 * 100% Pure WordPress HTTP API, Zero external dependencies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WLGF_Newsletter {

	/**
	 * Extract Contact Details from Raw Lead Data
	 */
	public static function extract_contact_info( $lead_data ) {
		$email      = '';
		$first_name = '';
		$last_name  = '';
		$phone      = '';
		$full_name  = '';

		if ( ! is_array( $lead_data ) ) {
			return compact( 'email', 'first_name', 'last_name', 'phone', 'full_name' );
		}

		foreach ( $lead_data as $key => $val ) {
			$k   = strtolower( trim( (string) $key ) );
			$val = is_array( $val ) ? implode( ', ', $val ) : trim( (string) $val );

			if ( empty( $val ) || strpos( $k, '_' ) === 0 || strpos( $k, 'hidden' ) !== false || strpos( $k, 'wlgf_' ) === 0 ) {
				continue;
			}

			// Email detection
			if ( empty( $email ) && ( strpos( $k, 'email' ) !== false || is_email( $val ) ) ) {
				if ( is_email( $val ) ) {
					$email = sanitize_email( $val );
				}
			}

			// First Name / Last Name / Full Name detection
			if ( $k === 'first_name' || $k === 'fname' || $k === 'first name' || strpos( $k, 'first' ) !== false ) {
				$first_name = sanitize_text_field( $val );
			} elseif ( $k === 'last_name' || $k === 'lname' || $k === 'last name' || strpos( $k, 'last' ) !== false ) {
				$last_name = sanitize_text_field( $val );
			} elseif ( empty( $full_name ) && ( $k === 'name' || $k === 'full_name' || $k === 'fullname' || $k === 'your name' ) ) {
				$full_name = sanitize_text_field( $val );
			}

			// Phone detection
			if ( empty( $phone ) && ( strpos( $k, 'phone' ) !== false || strpos( $k, 'mobile' ) !== false || strpos( $k, 'tel' ) !== false ) ) {
				$phone = sanitize_text_field( $val );
			}
		}

		// Split full name if separate first/last names were not provided
		if ( ! empty( $full_name ) && empty( $first_name ) ) {
			$parts = explode( ' ', $full_name, 2 );
			$first_name = $parts[0];
			$last_name  = isset( $parts[1] ) ? $parts[1] : '';
		}

		return compact( 'email', 'first_name', 'last_name', 'phone', 'full_name' );
	}

	/**
	 * Verify Connection Credentials for Any Provider
	 */
	public static function verify_connection( $provider, $credentials ) {
		$api_key  = isset( $credentials['api_key'] ) ? sanitize_text_field( trim( (string) $credentials['api_key'] ) ) : '';
		$list_id  = isset( $credentials['list_id'] ) ? sanitize_text_field( trim( (string) $credentials['list_id'] ) ) : '';
		$api_url  = isset( $credentials['api_url'] ) ? esc_url_raw( trim( (string) $credentials['api_url'] ) ) : '';

		switch ( $provider ) {
			case 'mailchimp':
				if ( empty( $api_key ) ) {
					return array( 'success' => false, 'message' => __( 'Please provide a Mailchimp API Key.', 'lead-generation-form' ) );
				}
				$key_parts = explode( '-', $api_key );
				$dc        = ( count( $key_parts ) > 1 ) ? end( $key_parts ) : 'us1';
				$url       = ! empty( $list_id ) ? "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}" : "https://{$dc}.api.mailchimp.com/3.0/ping";
				
				$response = wp_remote_get( $url, array(
					'headers' => array( 'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ) ),
					'timeout' => 8,
				) );

				if ( is_wp_error( $response ) ) {
					return array( 'success' => false, 'message' => $response->get_error_message() );
				}

				$code = wp_remote_retrieve_response_code( $response );
				$body = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( 200 === $code ) {
					$member_count = ( isset( $body['stats']['member_count'] ) ) ? intval( $body['stats']['member_count'] ) : 0;
					/* translators: 1: audience name, 2: member count */
					$msg = ! empty( $body['name'] ) ? sprintf( __( 'Connected to audience: %1$s (%2$d members)', 'lead-generation-form' ), esc_html( $body['name'] ), $member_count ) : __( 'Mailchimp API connection verified successfully!', 'lead-generation-form' );
					return array( 'success' => true, 'message' => $msg );
				}
				/* translators: %d: HTTP status code */
				return array( 'success' => false, 'message' => isset( $body['detail'] ) ? $body['detail'] : sprintf( __( 'API returned HTTP %d', 'lead-generation-form' ), $code ) );

			case 'brevo':
				if ( empty( $api_key ) ) {
					return array( 'success' => false, 'message' => __( 'Please provide a Brevo API Key.', 'lead-generation-form' ) );
				}
				$url = ! empty( $list_id ) ? "https://api.brevo.com/v3/contacts/lists/{$list_id}" : "https://api.brevo.com/v3/account";
				$response = wp_remote_get( $url, array(
					'headers' => array( 'api-key' => $api_key, 'Content-Type' => 'application/json' ),
					'timeout' => 8,
				) );

				if ( is_wp_error( $response ) ) {
					return array( 'success' => false, 'message' => $response->get_error_message() );
				}

				$code = wp_remote_retrieve_response_code( $response );
				$body = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( 200 === $code ) {
					$sub_count = isset( $body['totalSubscribers'] ) ? intval( $body['totalSubscribers'] ) : 0;
					/* translators: 1: list name, 2: subscriber count */
					$msg = ! empty( $body['name'] ) ? sprintf( __( 'Connected to Brevo list: %1$s (%2$d subscribers)', 'lead-generation-form' ), esc_html( $body['name'] ), $sub_count ) : __( 'Brevo API connection verified successfully!', 'lead-generation-form' );
					return array( 'success' => true, 'message' => $msg );
				}
				/* translators: %d: HTTP status code */
				return array( 'success' => false, 'message' => isset( $body['message'] ) ? $body['message'] : sprintf( __( 'API returned HTTP %d', 'lead-generation-form' ), $code ) );

			case 'mailerlite':
				if ( empty( $api_key ) ) {
					return array( 'success' => false, 'message' => __( 'Please provide a MailerLite API Token.', 'lead-generation-form' ) );
				}
				$url = ! empty( $list_id ) ? "https://connect.mailerlite.com/api/groups/{$list_id}" : "https://connect.mailerlite.com/api/groups?limit=1";
				$response = wp_remote_get( $url, array(
					'headers' => array( 'Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json' ),
					'timeout' => 8,
				) );

				if ( is_wp_error( $response ) ) {
					return array( 'success' => false, 'message' => $response->get_error_message() );
				}

				$code = wp_remote_retrieve_response_code( $response );
				$body = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( 200 === $code ) {
					$active_count = ( isset( $body['data']['active_count'] ) ) ? intval( $body['data']['active_count'] ) : 0;
					/* translators: 1: group name, 2: active subscriber count */
					$msg = ( ! empty( $body['data']['name'] ) ) ? sprintf( __( 'Connected to MailerLite group: %1$s (%2$d active subscribers)', 'lead-generation-form' ), esc_html( $body['data']['name'] ), $active_count ) : __( 'MailerLite API connection verified successfully!', 'lead-generation-form' );
					return array( 'success' => true, 'message' => $msg );
				}
				/* translators: %d: HTTP status code */
				return array( 'success' => false, 'message' => isset( $body['message'] ) ? $body['message'] : sprintf( __( 'API returned HTTP %d', 'lead-generation-form' ), $code ) );

			case 'sendgrid':
				if ( empty( $api_key ) ) {
					return array( 'success' => false, 'message' => __( 'Please provide a SendGrid API Key.', 'lead-generation-form' ) );
				}
				$url = ! empty( $list_id ) ? "https://api.sendgrid.com/v3/marketing/lists/{$list_id}" : "https://api.sendgrid.com/v3/marketing/lists?page_size=1";
				$response = wp_remote_get( $url, array(
					'headers' => array( 'Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json' ),
					'timeout' => 8,
				) );

				if ( is_wp_error( $response ) ) {
					return array( 'success' => false, 'message' => $response->get_error_message() );
				}

				$code = wp_remote_retrieve_response_code( $response );
				$body = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( 200 === $code ) {
					$contact_count = isset( $body['contact_count'] ) ? intval( $body['contact_count'] ) : ( isset( $body['result'][0]['contact_count'] ) ? intval( $body['result'][0]['contact_count'] ) : 0 );
					$list_name     = isset( $body['name'] ) ? $body['name'] : ( isset( $body['result'][0]['name'] ) ? $body['result'][0]['name'] : '' );
					/* translators: 1: list name, 2: contact count */
					$msg = ! empty( $list_name ) ? sprintf( __( 'Connected to SendGrid list: %1$s (%2$d contacts)', 'lead-generation-form' ), esc_html( $list_name ), $contact_count ) : __( 'SendGrid Marketing API connection verified successfully!', 'lead-generation-form' );
					return array( 'success' => true, 'message' => $msg );
				}
				/* translators: %d: HTTP status code */
				$err_msg = isset( $body['errors'][0]['message'] ) ? $body['errors'][0]['message'] : sprintf( __( 'API returned HTTP %d', 'lead-generation-form' ), $code );
				return array( 'success' => false, 'message' => $err_msg );

			case 'activecampaign':
				if ( empty( $api_url ) || empty( $api_key ) ) {
					return array( 'success' => false, 'message' => __( 'Please provide both ActiveCampaign API URL and API Key.', 'lead-generation-form' ) );
				}
				$clean_url = rtrim( $api_url, '/' );
				$target_endpoint = ! empty( $list_id ) ? "{$clean_url}/api/3/lists/{$list_id}" : "{$clean_url}/api/3/users/me";
				$response = wp_remote_get( $target_endpoint, array(
					'headers' => array( 'Api-Token' => $api_key, 'Content-Type' => 'application/json' ),
					'timeout' => 8,
				) );

				if ( is_wp_error( $response ) ) {
					return array( 'success' => false, 'message' => $response->get_error_message() );
				}

				$code = wp_remote_retrieve_response_code( $response );
				$body = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( 200 === $code ) {
					$list_name = isset( $body['list']['name'] ) ? $body['list']['name'] : '';
					/* translators: %s: list name */
					$msg = ! empty( $list_name ) ? sprintf( __( 'Connected to ActiveCampaign list: %s', 'lead-generation-form' ), esc_html( $list_name ) ) : __( 'ActiveCampaign API connection verified successfully!', 'lead-generation-form' );
					return array( 'success' => true, 'message' => $msg );
				}
				/* translators: %d: HTTP status code */
				return array( 'success' => false, 'message' => isset( $body['message'] ) ? $body['message'] : sprintf( __( 'API returned HTTP %d', 'lead-generation-form' ), $code ) );

			default:
				return array( 'success' => false, 'message' => __( 'Unknown newsletter provider.', 'lead-generation-form' ) );
		}
	}

	/**
	 * Synchronize a Lead Contact to the Configured Provider
	 */
	public static function sync_lead( $provider, $lead_data, $settings = null ) {
		if ( empty( $provider ) || 'disabled' === $provider ) {
			return array( 'synced' => false, 'message' => 'Newsletter sync disabled' );
		}

		if ( null === $settings ) {
			$settings = get_option( 'wlgf_settings', array() );
		}

		$contact = self::extract_contact_info( $lead_data );
		if ( empty( $contact['email'] ) || ! is_email( $contact['email'] ) ) {
			return array( 'synced' => false, 'message' => 'No valid email found in submission' );
		}

		switch ( $provider ) {
			case 'mailchimp':
				$api_key = isset( $settings['mailchimp_api_key'] ) ? trim( $settings['mailchimp_api_key'] ) : '';
				$list_id = isset( $settings['mailchimp_list_id'] ) ? trim( $settings['mailchimp_list_id'] ) : '';
				$double_optin = isset( $settings['mailchimp_double_optin'] ) && '1' === (string) $settings['mailchimp_double_optin'];

				if ( empty( $api_key ) || empty( $list_id ) ) {
					return array( 'synced' => false, 'message' => 'Missing Mailchimp credentials' );
				}

				$key_parts = explode( '-', $api_key );
				$dc        = ( count( $key_parts ) > 1 ) ? end( $key_parts ) : 'us1';
				$member_hash = md5( strtolower( $contact['email'] ) );
				$url         = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$member_hash}";

				$payload = array(
					'email_address' => $contact['email'],
					'status_if_new' => $double_optin ? 'pending' : 'subscribed',
					'status'        => $double_optin ? 'pending' : 'subscribed',
					'merge_fields'  => array(
						'FNAME' => $contact['first_name'],
						'LNAME' => $contact['last_name'],
					),
				);
				if ( ! empty( $contact['phone'] ) ) {
					$payload['merge_fields']['PHONE'] = $contact['phone'];
				}

				$res = wp_remote_request( $url, array(
					'method'  => 'PUT',
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ),
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $payload ),
					'timeout' => 5,
				) );

				$code = wp_remote_retrieve_response_code( $res );
				return ( $code >= 200 && $code < 300 ) ? array( 'synced' => true, 'provider' => 'mailchimp' ) : array( 'synced' => false, 'provider' => 'mailchimp', 'error' => wp_remote_retrieve_body( $res ) );

			case 'brevo':
				$api_key = isset( $settings['brevo_api_key'] ) ? trim( $settings['brevo_api_key'] ) : '';
				$list_id = isset( $settings['brevo_list_id'] ) ? intval( $settings['brevo_list_id'] ) : 0;

				if ( empty( $api_key ) ) {
					return array( 'synced' => false, 'message' => 'Missing Brevo credentials' );
				}

				$url = 'https://api.brevo.com/v3/contacts';
				$payload = array(
					'email'         => $contact['email'],
					'updateEnabled' => true,
					'attributes'    => array(
						'FIRSTNAME' => $contact['first_name'],
						'LASTNAME'  => $contact['last_name'],
					),
				);
				if ( ! empty( $contact['phone'] ) ) {
					$payload['attributes']['SMS'] = $contact['phone'];
				}
				if ( $list_id > 0 ) {
					$payload['listIds'] = array( $list_id );
				}

				$res = wp_remote_post( $url, array(
					'headers' => array(
						'api-key'      => $api_key,
						'Content-Type' => 'application/json',
					),
					'body'    => wp_json_encode( $payload ),
					'timeout' => 5,
				) );

				$code = wp_remote_retrieve_response_code( $res );
				return ( $code >= 200 && $code < 300 || 204 === $code ) ? array( 'synced' => true, 'provider' => 'brevo' ) : array( 'synced' => false, 'provider' => 'brevo', 'error' => wp_remote_retrieve_body( $res ) );

			case 'mailerlite':
				$api_key  = isset( $settings['mailerlite_api_key'] ) ? trim( $settings['mailerlite_api_key'] ) : '';
				$group_id = isset( $settings['mailerlite_group_id'] ) ? trim( $settings['mailerlite_group_id'] ) : '';

				if ( empty( $api_key ) ) {
					return array( 'synced' => false, 'message' => 'Missing MailerLite credentials' );
				}

				$url = 'https://connect.mailerlite.com/api/subscribers';
				$payload = array(
					'email'  => $contact['email'],
					'status' => 'active',
					'fields' => array(
						'name'      => $contact['first_name'],
						'last_name' => $contact['last_name'],
					),
				);
				if ( ! empty( $contact['phone'] ) ) {
					$payload['fields']['phone'] = $contact['phone'];
				}
				if ( ! empty( $group_id ) ) {
					$payload['groups'] = array( $group_id );
				}

				$res = wp_remote_post( $url, array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $payload ),
					'timeout' => 5,
				) );

				$code = wp_remote_retrieve_response_code( $res );
				return ( $code >= 200 && $code < 300 ) ? array( 'synced' => true, 'provider' => 'mailerlite' ) : array( 'synced' => false, 'provider' => 'mailerlite', 'error' => wp_remote_retrieve_body( $res ) );

			case 'sendgrid':
				$api_key = isset( $settings['sendgrid_api_key'] ) ? trim( $settings['sendgrid_api_key'] ) : '';
				$list_id = isset( $settings['sendgrid_list_id'] ) ? trim( $settings['sendgrid_list_id'] ) : '';

				if ( empty( $api_key ) ) {
					return array( 'synced' => false, 'message' => 'Missing SendGrid credentials' );
				}

				$url = 'https://api.sendgrid.com/v3/marketing/contacts';
				$contact_obj = array(
					'email'      => $contact['email'],
					'first_name' => $contact['first_name'],
					'last_name'  => $contact['last_name'],
				);
				if ( ! empty( $contact['phone'] ) ) {
					$contact_obj['phone_number'] = $contact['phone'];
				}

				$payload = array(
					'contacts' => array( $contact_obj ),
				);
				if ( ! empty( $list_id ) ) {
					$payload['list_ids'] = array( $list_id );
				}

				$res = wp_remote_request( $url, array(
					'method'  => 'PUT',
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $payload ),
					'timeout' => 5,
				) );

				$code = wp_remote_retrieve_response_code( $res );
				return ( $code >= 200 && $code < 300 ) ? array( 'synced' => true, 'provider' => 'sendgrid' ) : array( 'synced' => false, 'provider' => 'sendgrid', 'error' => wp_remote_retrieve_body( $res ) );

			case 'activecampaign':
				$api_url = isset( $settings['activecampaign_api_url'] ) ? trim( $settings['activecampaign_api_url'] ) : '';
				$api_key = isset( $settings['activecampaign_api_key'] ) ? trim( $settings['activecampaign_api_key'] ) : '';
				$list_id = isset( $settings['activecampaign_list_id'] ) ? intval( $settings['activecampaign_list_id'] ) : 0;

				if ( empty( $api_url ) || empty( $api_key ) ) {
					return array( 'synced' => false, 'message' => 'Missing ActiveCampaign credentials' );
				}

				$clean_url = rtrim( $api_url, '/' );
				$contact_url = "{$clean_url}/api/3/contact/sync";

				$payload = array(
					'contact' => array(
						'email'     => $contact['email'],
						'firstName' => $contact['first_name'],
						'lastName'  => $contact['last_name'],
						'phone'     => $contact['phone'],
					),
				);

				$res = wp_remote_post( $contact_url, array(
					'headers' => array(
						'Api-Token'    => $api_key,
						'Content-Type' => 'application/json',
					),
					'body'    => wp_json_encode( $payload ),
					'timeout' => 5,
				) );

				$code = wp_remote_retrieve_response_code( $res );
				$body = json_decode( wp_remote_retrieve_body( $res ), true );

				if ( $code >= 200 && $code < 300 && isset( $body['contact']['id'] ) && $list_id > 0 ) {
					$contact_id = intval( $body['contact']['id'] );
					// Associate contact with list
					wp_remote_post( "{$clean_url}/api/3/contactLists", array(
						'headers' => array(
							'Api-Token'    => $api_key,
							'Content-Type' => 'application/json',
						),
						'body'    => wp_json_encode( array(
							'contactList' => array(
								'list'    => $list_id,
								'contact' => $contact_id,
								'status'  => 1,
							),
						) ),
						'timeout' => 4,
					) );
					return array( 'synced' => true, 'provider' => 'activecampaign' );
				}

				return ( $code >= 200 && $code < 300 ) ? array( 'synced' => true, 'provider' => 'activecampaign' ) : array( 'synced' => false, 'provider' => 'activecampaign', 'error' => wp_remote_retrieve_body( $res ) );

			default:
				return array( 'synced' => false, 'message' => 'Unknown provider' );
		}
	}
}
