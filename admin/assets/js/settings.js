/**
 * Lead Generation Form - Settings Script
 */

jQuery(document).ready(function($) {
	// Tab Switching Handler
	$('.wlgf-tab-btn').on('click', function(e) {
		e.preventDefault();
		const target = $(this).data('target');

		$('.wlgf-tab-btn').removeClass('wlgf-tab-btn--active');
		$(this).addClass('wlgf-tab-btn--active');

		$('.wlgf-tab-content').removeClass('wlgf-tab-content--active');
		$(target).addClass('wlgf-tab-content--active');

		if (target === '#wlgf-tab-upgrade' || target === '#wlgf-tab-help') {
			$('#wlgf-header-save-btn').addClass('d-none');
		} else {
			$('#wlgf-header-save-btn').removeClass('d-none');
		}
	});

	// Check URL hash for direct tab navigation (e.g. #wlgf-tab-help)
	if (window.location.hash) {
		const $hashTab = $('.wlgf-tab-btn[data-target="' + window.location.hash + '"]');
		if ($hashTab.length) {
			$hashTab.trigger('click');
		}
	}

	// CAPTCHA Provider visibility toggle
	$('#captcha_provider').on('change', function() {
		const provider = $(this).val();
		if (provider === 'turnstile') {
			$('#wlgf-turnstile-keys').removeClass('d-none');
			$('#wlgf-recaptcha-keys').addClass('d-none');
			$('#recaptcha').val('3');
		} else if (provider === 'recaptcha') {
			$('#wlgf-recaptcha-keys').removeClass('d-none');
			$('#wlgf-turnstile-keys').addClass('d-none');
			$('#recaptcha').val('1');
		} else {
			$('#wlgf-recaptcha-keys').addClass('d-none');
			$('#wlgf-turnstile-keys').addClass('d-none');
			$('#recaptcha').val('2');
		}
	});

	// Email Engine & Admin Notify toggle
	$('#notify_admin, #email_engine').on('change', function() {
		const notify = $('#notify_admin').val();
		const engine = $('#email_engine').val();

		if (notify === '2') {
			$('.wlgf-email-engine-group').addClass('d-none');
			$('.wlgf-smtp-card').addClass('d-none');
			$('.wlgf-quick-test-group').addClass('d-none');
			$('.wlgf-cc-admin-group').addClass('d-none');
		} else {
			$('.wlgf-email-engine-group').removeClass('d-none');
			$('.wlgf-quick-test-group').removeClass('d-none');
			$('.wlgf-cc-admin-group').removeClass('d-none');
			if (engine === '2') {
				$('.wlgf-smtp-card').removeClass('d-none');
				$('.wlgf-wpmail-note').addClass('d-none');
			} else {
				$('.wlgf-smtp-card').addClass('d-none');
				$('.wlgf-wpmail-note').removeClass('d-none');
			}
		}

		if (engine === '2') {
			$('#wlgf-active-engine-hint').text('Active Dispatch: Custom SMTP Server');
		} else {
			$('#wlgf-active-engine-hint').text('Active Dispatch: Default WordPress Mail (wp_mail)');
		}
	});

	// Email & Domain Blacklist visibility & badge toggles
	$('#blacklist_status').on('change', function() {
		const val = $(this).val();
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

	$('#blacklist_action').on('change', function() {
		if ($(this).val() === 'reject_error') {
			$('#wlgf-blacklist-msg-wrap').removeClass('d-none');
		} else {
			$('#wlgf-blacklist-msg-wrap').addClass('d-none');
		}
	});

	// Quick Add domain chips handler
	$(document).on('click', '.wlgf-quick-add-domain', function(e) {
		e.preventDefault();
		const domain = $(this).data('domain');
		const $textarea = $('#blacklist_domains');
		let currentVal = $textarea.val().trim();
		const lines = currentVal ? currentVal.split(/[\r\n,]+/).map(s => s.trim().toLowerCase()) : [];
		if (lines.indexOf(domain.toLowerCase()) === -1) {
			if (currentVal.length > 0) {
				$textarea.val(currentVal + '\n' + domain);
			} else {
				$textarea.val(domain);
			}
			$(this).css({'background': '#dcfce7', 'color': '#166534', 'border-color': '#86efac'});
		}
	});

	// Newsletter Provider visibility toggle (PRO-008)
	$('#newsletter_provider').on('change', function() {
		const provider = $(this).val();
		$('#wlgf-card-provider-local, #wlgf-card-provider-mailchimp, #wlgf-card-provider-brevo, #wlgf-card-provider-mailerlite, #wlgf-card-provider-sendgrid, #wlgf-card-provider-activecampaign').addClass('d-none');
		if (provider === 'local' || !provider) {
			$('#wlgf-card-provider-local').removeClass('d-none');
		} else if (provider === 'mailchimp') {
			$('#wlgf-card-provider-mailchimp').removeClass('d-none');
		} else if (provider === 'brevo') {
			$('#wlgf-card-provider-brevo').removeClass('d-none');
		} else if (provider === 'mailerlite') {
			$('#wlgf-card-provider-mailerlite').removeClass('d-none');
		} else if (provider === 'sendgrid') {
			$('#wlgf-card-provider-sendgrid').removeClass('d-none');
		} else if (provider === 'activecampaign') {
			$('#wlgf-card-provider-activecampaign').removeClass('d-none');
		}
	});

	// Save Settings Handler
	$('#wlgf-header-save-btn').on('click', function(e) {
		e.preventDefault();
		const $btn = $(this);

		const captcha_provider = $('#captcha_provider').val() || 'none';
		let recaptcha = $('#recaptcha').val() || '2';
		const sitekey = $('#sitekey').val().trim();
		const secretkey = $('#secretkey').val().trim();
		const turnstile_sitekey = $('#turnstile_sitekey').length ? $('#turnstile_sitekey').val().trim() : '';
		const turnstile_secretkey = $('#turnstile_secretkey').length ? $('#turnstile_secretkey').val().trim() : '';
		const submission_rate_limit = $('#submission_rate_limit').val() || '1';
		const honeypot_status = $('#honeypot_status').val() || '1';
		const scanner_shield_status = $('#scanner_shield_status').val() || '1';

		if (captcha_provider === 'turnstile') {
			recaptcha = '3';
			if (!turnstile_sitekey) {
				$('.wlgf-tab-btn[data-target="#wlgf-tab-security"]').trigger('click');
				$('#turnstile_sitekey').focus();
				return false;
			}
			if (!turnstile_secretkey) {
				$('.wlgf-tab-btn[data-target="#wlgf-tab-security"]').trigger('click');
				$('#turnstile_secretkey').focus();
				return false;
			}
		} else if (captcha_provider === 'recaptcha') {
			recaptcha = '1';
			if (!sitekey) {
				$('.wlgf-tab-btn[data-target="#wlgf-tab-security"]').trigger('click');
				$('#sitekey').focus();
				return false;
			}
			if (!secretkey) {
				$('.wlgf-tab-btn[data-target="#wlgf-tab-security"]').trigger('click');
				$('#secretkey').focus();
				return false;
			}
		} else {
			recaptcha = '2';
		}

		const user_message = $('#user_message').val().trim();
		if (!user_message) {
			$('.wlgf-tab-btn[data-target="#wlgf-tab-general"]').trigger('click');
			$('#user_message').focus();
			return false;
		}

		const notify_admin = $('#notify_admin').val();
		const email_engine = $('#email_engine').val();
		const smtp_host = $('#smtp_host').val().trim();
		const smtp_username = $('#smtp_username').val().trim();
		const smtp_password = $('#smtp_password').val();
		const smtp_encryption = $('#smtp_encryption').val();
		const smtp_port = $('#smtp_port').val().trim();

		// Newsletter parameters (PRO-008)
		const newsletter_provider = $('#newsletter_provider').val() || 'local';
		const mailchimp_api_key = $('#mailchimp_api_key').val() ? $('#mailchimp_api_key').val().trim() : '';
		const mailchimp_list_id = $('#mailchimp_list_id').val() ? $('#mailchimp_list_id').val().trim() : '';
		const mailchimp_double_optin = $('#mailchimp_double_optin').is(':checked') ? '1' : '0';
		const brevo_api_key = $('#brevo_api_key').length ? $('#brevo_api_key').val().trim() : '';
		const brevo_list_id = $('#brevo_list_id').length ? $('#brevo_list_id').val().trim() : '';
		const mailerlite_api_key = $('#mailerlite_api_key').length ? $('#mailerlite_api_key').val().trim() : '';
		const mailerlite_group_id = $('#mailerlite_group_id').length ? $('#mailerlite_group_id').val().trim() : '';
		const sendgrid_api_key = $('#sendgrid_api_key').length ? $('#sendgrid_api_key').val().trim() : '';
		const sendgrid_list_id = $('#sendgrid_list_id').length ? $('#sendgrid_list_id').val().trim() : '';
		const activecampaign_api_url = $('#activecampaign_api_url').length ? $('#activecampaign_api_url').val().trim() : '';
		const activecampaign_api_key = $('#activecampaign_api_key').length ? $('#activecampaign_api_key').val().trim() : '';
		const activecampaign_list_id = $('#activecampaign_list_id').length ? $('#activecampaign_list_id').val().trim() : '';

		// Payment parameters (PRO-016)
		let enable_payments = $('#enable_payments').length ? $('#enable_payments').val() : '0';
		const payment_currency = $('#payment_currency').length ? $('#payment_currency').val() : 'USD';
		const stripe_enabled = $('#stripe_enabled').is(':checked') ? '1' : '0';
		const stripe_test_mode = $('#stripe_test_mode').length ? $('#stripe_test_mode').val() : '1';
		const stripe_test_pub_key = $('#stripe_test_pub_key').length ? $('#stripe_test_pub_key').val().trim() : '';
		const stripe_test_sec_key = $('#stripe_test_sec_key').length ? $('#stripe_test_sec_key').val().trim() : '';
		const stripe_live_pub_key = $('#stripe_live_pub_key').length ? $('#stripe_live_pub_key').val().trim() : '';
		const stripe_live_sec_key = $('#stripe_live_sec_key').length ? $('#stripe_live_sec_key').val().trim() : '';
		const paypal_enabled = $('#paypal_enabled').is(':checked') ? '1' : '0';
		if (enable_payments === '0' && (stripe_enabled === '1' || paypal_enabled === '1')) {
			enable_payments = '1';
			$('#enable_payments').val('1');
		}
		const paypal_mode = $('#paypal_mode').length ? $('#paypal_mode').val() : 'sandbox';
		const paypal_sandbox_client_id = $('#paypal_sandbox_client_id').length ? $('#paypal_sandbox_client_id').val().trim() : '';
		const paypal_sandbox_secret = $('#paypal_sandbox_secret').length ? $('#paypal_sandbox_secret').val().trim() : '';
		const paypal_live_client_id = $('#paypal_live_client_id').length ? $('#paypal_live_client_id').val().trim() : '';
		const paypal_live_secret = $('#paypal_live_secret').length ? $('#paypal_live_secret').val().trim() : '';

		$btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving Settings...');

		$.ajax({
			type: 'POST',
			url: Settings.ajaxUrl,
			data: {
				action: 'wlgf_save_settings',
				nonce: Settings.nonce,
				captcha_provider: captcha_provider,
				recaptcha: recaptcha,
				sitekey: sitekey,
				secretkey: secretkey,
				turnstile_sitekey: turnstile_sitekey,
				turnstile_secretkey: turnstile_secretkey,
				submission_rate_limit: submission_rate_limit,
				honeypot_status: honeypot_status,
				scanner_shield_status: scanner_shield_status,
				user_message: user_message,
				notify_admin: notify_admin,
				email_engine: email_engine,
				smtp_host: smtp_host,
				smtp_username: smtp_username,
				smtp_password: smtp_password,
				smtp_encryption: smtp_encryption,
				smtp_port: smtp_port,
				newsletter_provider: newsletter_provider,
				mailchimp_api_key: mailchimp_api_key,
				mailchimp_list_id: mailchimp_list_id,
				mailchimp_double_optin: mailchimp_double_optin,
				brevo_api_key: brevo_api_key,
				brevo_list_id: brevo_list_id,
				mailerlite_api_key: mailerlite_api_key,
				mailerlite_group_id: mailerlite_group_id,
				sendgrid_api_key: sendgrid_api_key,
				sendgrid_list_id: sendgrid_list_id,
				activecampaign_api_url: activecampaign_api_url,
				activecampaign_api_key: activecampaign_api_key,
				activecampaign_list_id: activecampaign_list_id,
				enable_payments: enable_payments,
				payment_currency: payment_currency,
				stripe_enabled: stripe_enabled,
				stripe_test_mode: stripe_test_mode,
				stripe_test_pub_key: stripe_test_pub_key,
				stripe_test_sec_key: stripe_test_sec_key,
				stripe_live_pub_key: stripe_live_pub_key,
				stripe_live_sec_key: stripe_live_sec_key,
				paypal_enabled: paypal_enabled,
				paypal_mode: paypal_mode,
				paypal_sandbox_client_id: paypal_sandbox_client_id,
				paypal_sandbox_secret: paypal_sandbox_secret,
				paypal_live_client_id: paypal_live_client_id,
				paypal_live_secret: paypal_live_secret,
				email_logo_url: $('#email_logo_url').val() ? $('#email_logo_url').val().trim() : '',
				email_logo_width: $('#email_logo_width').val() ? $('#email_logo_width').val() : '180',
				email_header_title: $('#email_header_title').val() ? $('#email_header_title').val().trim() : '',
				email_header_bg: $('#email_header_bg').val() || '#1e293b',
				email_header_color: $('#email_header_color').val() || '#ffffff',
				email_body_greeting: $('#email_body_greeting').val() ? $('#email_body_greeting').val().trim() : '',
				email_body_bg: $('#email_body_bg').val() || '#f1f5f9',
				email_card_bg: $('#email_card_bg').val() || '#ffffff',
				email_text_color: $('#email_text_color').val() || '#334155',
				email_footer_text: $('#email_footer_text').val() ? $('#email_footer_text').val().trim() : '',
				email_footer_bg: $('#email_footer_bg').val() || '#f8fafc',
				email_footer_color: $('#email_footer_color').val() || '#64748b',
				email_show_dashboard_btn: $('#email_show_dashboard_btn').is(':checked') ? '1' : '0',
				confirmation_heading: $('#confirmation_heading').length ? $('#confirmation_heading').val().trim() : '',
				confirmation_style: $('#confirmation_style').length ? $('#confirmation_style').val() : 'modern-card',
				confirmation_icon: $('#confirmation_icon').length ? $('#confirmation_icon').val() : 'circle-check',
				confirmation_animated: $('#confirmation_animated').is(':checked') ? '1' : '0',
				confirmation_fadeout: $('#confirmation_fadeout').length ? $('#confirmation_fadeout').val() : '10',
				confirmation_bg: $('#confirmation_bg').length ? $('#confirmation_bg').val().trim() : '#f0fdf4',
				confirmation_border_color: $('#confirmation_border_color').length ? $('#confirmation_border_color').val().trim() : '#86efac',
				confirmation_heading_color: $('#confirmation_heading_color').length ? $('#confirmation_heading_color').val().trim() : '#14532d',
				confirmation_text_color: $('#confirmation_text_color').length ? $('#confirmation_text_color').val().trim() : '#166534',
				confirmation_icon_color: $('#confirmation_icon_color').length ? $('#confirmation_icon_color').val().trim() : '#16a34a',
				confirmation_icon_bg: $('#confirmation_icon_bg').length ? $('#confirmation_icon_bg').val().trim() : '#dcfce7',
				cc_admin_emails: $('#cc_admin_emails').length ? $('#cc_admin_emails').val().trim() : '',
				blacklist_status: $('#blacklist_status').length ? $('#blacklist_status').val() : '2',
				blacklist_emails: $('#blacklist_emails').length ? $('#blacklist_emails').val().trim() : '',
				blacklist_domains: $('#blacklist_domains').length ? $('#blacklist_domains').val().trim() : '',
				blacklist_action: $('#blacklist_action').length ? $('#blacklist_action').val() : 'reject_error',
				blacklist_message: $('#blacklist_message').length ? $('#blacklist_message').val().trim() : '',
				floating_widget_enabled: $('#floating_widget_enabled').is(':checked') ? '1' : '0',
				floating_position: $('#floating_position').length ? $('#floating_position').val() : 'bottom-right',
				floating_color: $('#floating_color').length ? $('#floating_color').val() : '#2563eb',
				floating_greeting: $('#floating_greeting').length ? $('#floating_greeting').val().trim() : '',
				floating_title: $('#floating_title').length ? $('#floating_title').val().trim() : '',
				floating_subtitle: $('#floating_subtitle').length ? $('#floating_subtitle').val().trim() : '',
				floating_whatsapp: $('#floating_whatsapp').length ? $('#floating_whatsapp').val().trim() : '',
				floating_whatsapp_msg: $('#floating_whatsapp_msg').length ? $('#floating_whatsapp_msg').val().trim() : '',
				floating_phone: $('#floating_phone').length ? $('#floating_phone').val().trim() : '',
				floating_telegram: $('#floating_telegram').length ? $('#floating_telegram').val().trim() : '',
				floating_sms: $('#floating_sms').length ? $('#floating_sms').val().trim() : '',
				floating_email: $('#floating_email').length ? $('#floating_email').val().trim() : '',
				floating_form_id: $('#floating_form_id').length ? $('#floating_form_id').val() : ''
			},
			success: function() {
				$('#wlgf-save-result').html('<div class="wlgf-alert wlgf-alert--success"><i class="fa-solid fa-circle-check"></i> <span>Settings saved successfully!</span></div>');
				setTimeout(function() {
					$('#wlgf-save-result').empty();
				}, 4000);
			},
			error: function() {
				$('#wlgf-save-result').html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>Failed to save settings. Please try again.</span></div>');
			},
			complete: function() {
				$btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i> Save Settings');
			}
		});
	});

	// Comprehensive Test Email Dispatcher (wp_mail & SMTP with Branded HTML Template)
	function sendTestEmail(recipientEmail, $btn, $resultContainer) {
		recipientEmail = (recipientEmail || '').trim();
		if (!recipientEmail) {
			$resultContainer.html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>Please enter a valid recipient email address.</span></div>');
			return;
		}

		const originalHtml = $btn.html();
		$btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Sending...');
		$resultContainer.empty();

		const data = {
			action: 'wlgf_send_test_email',
			nonce: Settings.nonce,
			test_email: recipientEmail,
			email_engine: $('#email_engine').val() || '1',
			smtp_host: $('#smtp_host').val() ? $('#smtp_host').val().trim() : '',
			smtp_username: $('#smtp_username').val() ? $('#smtp_username').val().trim() : '',
			smtp_password: $('#smtp_password').val() ? $('#smtp_password').val() : '',
			smtp_encryption: $('#smtp_encryption').val() || '1',
			smtp_port: $('#smtp_port').val() || '587',
			email_logo_url: $('#email_logo_url').val() ? $('#email_logo_url').val().trim() : '',
			email_logo_width: $('#email_logo_width').val() ? $('#email_logo_width').val() : '180',
			email_header_title: $('#email_header_title').val() ? $('#email_header_title').val().trim() : '',
			email_header_bg: $('#email_header_bg').val() || '#1e293b',
			email_header_color: $('#email_header_color').val() || '#ffffff',
			email_body_greeting: $('#email_body_greeting').val() ? $('#email_body_greeting').val().trim() : '',
			email_body_bg: $('#email_body_bg').val() || '#f1f5f9',
			email_card_bg: $('#email_card_bg').val() || '#ffffff',
			email_text_color: $('#email_text_color').val() || '#334155',
			email_footer_text: $('#email_footer_text').val() ? $('#email_footer_text').val().trim() : '',
			email_footer_bg: $('#email_footer_bg').val() || '#f8fafc',
			email_footer_color: $('#email_footer_color').val() || '#64748b',
			email_show_dashboard_btn: $('#email_show_dashboard_btn').is(':checked') ? '1' : '0'
		};

		$.ajax({
			type: 'POST',
			url: Settings.ajaxUrl,
			data: data,
			success: function(response) {
				if (response && response.success) {
					$resultContainer.html('<div class="wlgf-alert wlgf-alert--success"><i class="fa-solid fa-circle-check"></i> <span>' + $('<div>').text(response.data.message).html() + '</span></div>');
				} else {
					const msg = (response && response.data && response.data.message) ? response.data.message : 'Failed to dispatch test email.';
					$resultContainer.html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>' + $('<div>').text(msg).html() + '</span></div>');
				}
			},
			error: function() {
				$resultContainer.html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>Server communication error occurred while dispatching test email.</span></div>');
			},
			complete: function() {
				$btn.prop('disabled', false).html(originalHtml);
			}
		});
	}

	// 1. Quick Test Button in Section 1
	$(document).on('click', '#wlgf-quick-test-email-btn', function(e) {
		e.preventDefault();
		sendTestEmail($('#wlgf_quick_test_email').val(), $(this), $('#wlgf-quick-test-email-result'));
	});

	// 2. SMTP Card Test Button
	$(document).on('click', '#wlgf-test-smtp-btn', function(e) {
		e.preventDefault();
		sendTestEmail($('#wlgf_test_email').val(), $(this), $('#wlgf-smtp-test-result'));
	});

	// 3. Modal Test Button
	$(document).on('click', '#wlgf-confirm-send-test-btn', function(e) {
		e.preventDefault();
		sendTestEmail($('#wlgf_modal_test_email').val(), $(this), $('#wlgf-modal-test-email-result'));
	});

	// Toggle SMTP Password Visibility
	$(document).on('click', '#wlgf-toggle-smtp-pw', function(e) {
		e.preventDefault();
		const $pw = $('#smtp_password');
		const $icon = $('#wlgf-toggle-smtp-pw-icon');
		if ($pw.attr('type') === 'password') {
			$pw.attr('type', 'text');
			$icon.removeClass('fa-eye').addClass('fa-eye-slash');
		} else {
			$pw.attr('type', 'password');
			$icon.removeClass('fa-eye-slash').addClass('fa-eye');
		}
	});

	// Open/Close Test Email Modal Handlers
	const openTestEmailModal = function() {
		const engine = $('#email_engine').val();
		if (engine === '2') {
			$('#wlgf-active-engine-hint').text('Active Dispatch: Custom SMTP Server');
		} else {
			$('#wlgf-active-engine-hint').text('Active Dispatch: Default WordPress Mail (wp_mail)');
		}
		$('#wlgf-modal-test-email-result').empty();
		$('#wlgf-test-email-modal').removeClass('d-none').addClass('wlgf-modal-overlay--active').css({
			display: 'flex',
			opacity: '1',
			visibility: 'visible'
		});
	};

	const closeTestEmailModal = function() {
		$('#wlgf-test-email-modal').removeClass('wlgf-modal-overlay--active').addClass('d-none').css({
			display: 'none',
			opacity: '0',
			visibility: 'hidden'
		});
	};

	$(document).on('click', '#wlgf-open-test-modal-btn, #wlgf-preview-modal-test-btn', function(e) {
		e.preventDefault();
		openTestEmailModal();
	});

	$(document).on('click', '#wlgf-close-test-email-modal-btn, #wlgf-cancel-test-email-btn', function(e) {
		e.preventDefault();
		closeTestEmailModal();
	});

	$(document).on('click', '#wlgf-test-email-modal', function(e) {
		if (e.target === this) {
			closeTestEmailModal();
		}
	});

	$(document).on('keydown', function(e) {
		if (e.key === 'Escape' && $('#wlgf-test-email-modal').hasClass('wlgf-modal-overlay--active')) {
			closeTestEmailModal();
		}
	});

	// Test Newsletter Connection Button Handler (PRO-008)
	$('.wlgf-test-connection-btn').on('click', function(e) {
		e.preventDefault();
		const $btn = $(this);
		const provider = $btn.data('provider');
		const $feedback = $btn.siblings('.wlgf-test-connection-feedback');

		let apiKey = '';
		let listId = '';

		if (provider === 'mailchimp') {
			apiKey = $('#mailchimp_api_key').val().trim();
			listId = $('#mailchimp_list_id').val().trim();
		} else if (provider === 'brevo') {
			apiKey = $('#brevo_api_key').val().trim();
			listId = $('#brevo_list_id').val().trim();
		} else if (provider === 'mailerlite') {
			apiKey = $('#mailerlite_api_key').val().trim();
			listId = $('#mailerlite_group_id').val().trim();
		} else if (provider === 'sendgrid') {
			apiKey = $('#sendgrid_api_key').val().trim();
			listId = $('#sendgrid_list_id').val().trim();
		} else if (provider === 'activecampaign') {
			apiKey = $('#activecampaign_api_key').val().trim();
			listId = $('#activecampaign_list_id').val().trim();
		}

		$btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Verifying...');
		$feedback.empty();

		$.ajax({
			type: 'POST',
			url: Settings.ajaxUrl,
			data: {
				action: 'wlgf_verify_newsletter_api',
				nonce: Settings.nonce,
				provider: provider,
				api_key: apiKey,
				list_id: listId,
				api_url: (provider === 'activecampaign') ? $('#activecampaign_api_url').val().trim() : ''
			},
			success: function(response) {
				if (response && response.success) {
					$feedback.html('<span class="wlgf-badge wlgf-badge--success"><i class="fa-solid fa-circle-check"></i> ' + $('<div>').text(response.data.message).html() + '</span>');
				} else {
					const msg = (response && response.data && response.data.message) ? response.data.message : 'Connection verification failed.';
					$feedback.html('<span class="wlgf-badge wlgf-badge--danger"><i class="fa-solid fa-circle-exclamation"></i> ' + $('<div>').text(msg).html() + '</span>');
				}
			},
			error: function() {
				$feedback.html('<span class="wlgf-badge wlgf-badge--danger"><i class="fa-solid fa-circle-exclamation"></i> Verification request failed.</span>');
			},
			complete: function() {
				$btn.prop('disabled', false).html('<i class="fa-solid fa-plug"></i> Test ' + provider.charAt(0).toUpperCase() + provider.slice(1) + ' Connection');
			}
		});
	});

	// Email Logo Media Uploader
	let emailLogoFrame;
	$('#wlgf-upload-logo-btn').on('click', function(e) {
		e.preventDefault();
		if (typeof wp !== 'undefined' && wp.media) {
			if (emailLogoFrame) {
				emailLogoFrame.open();
				return;
			}
			emailLogoFrame = wp.media({
				title: 'Select or Upload Brand Logo',
				button: { text: 'Use this Logo' },
				multiple: false
			});
			emailLogoFrame.on('select', function() {
				const attachment = emailLogoFrame.state().get('selection').first().toJSON();
				$('#email_logo_url').val(attachment.url);
				$('#wlgf-logo-preview-img').attr('src', attachment.url);
				$('#wlgf-logo-preview-wrap').removeClass('d-none');
				$('#wlgf-remove-logo-btn').removeClass('d-none');
			});
			emailLogoFrame.open();
		} else {
			const manualUrl = prompt('Enter Logo Image URL:');
			if (manualUrl) {
				$('#email_logo_url').val(manualUrl);
				$('#wlgf-logo-preview-img').attr('src', manualUrl);
				$('#wlgf-logo-preview-wrap').removeClass('d-none');
				$('#wlgf-remove-logo-btn').removeClass('d-none');
			}
		}
	});

	$('#wlgf-remove-logo-btn').on('click', function(e) {
		e.preventDefault();
		$('#email_logo_url').val('');
		$('#wlgf-logo-preview-img').attr('src', '');
		$('#wlgf-logo-preview-wrap').addClass('d-none');
		$(this).addClass('d-none');
	});

	// Two-way synchronization between color pickers and hex text inputs
	$('.wlgf-color-picker').on('input change', function() {
		const val = $(this).val();
		$(this).siblings('.wlgf-color-code').val(val);
	});
	$('.wlgf-color-code').on('input change', function() {
		let val = $(this).val().trim();
		if (val && val.charAt(0) !== '#') {
			val = '#' + val;
		}
		if (/^#[0-9A-F]{6}$/i.test(val)) {
			$(this).siblings('.wlgf-color-picker').val(val);
		}
	});

	// Email Template Live Preview Modal
	// Email Template Live Preview Modal
	$(document).on('click', '#wlgf-preview-template-btn', function(e) {
		e.preventDefault();
		const $modal = $('#wlgf-email-preview-modal');
		const $content = $('#wlgf-email-preview-content');

		$modal.removeClass('d-none').addClass('wlgf-modal-overlay--active').css({
			display: 'flex',
			opacity: '1',
			visibility: 'visible'
		});
		$content.html('<div style="text-align: center; padding: 50px 20px; color: #475569;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 28px; margin-bottom: 12px; color: #2563eb;"></i><br><strong style="font-size: 15px;">Rendering Email Template Preview...</strong></div>');

		$.ajax({
			type: 'POST',
			url: Settings.ajaxUrl,
			data: {
				action: 'wlgf_preview_email_template',
				nonce: Settings.nonce,
				email_logo_url: $('#email_logo_url').val() ? $('#email_logo_url').val().trim() : '',
				email_logo_width: $('#email_logo_width').val() ? $('#email_logo_width').val() : '180',
				email_header_title: $('#email_header_title').val() ? $('#email_header_title').val().trim() : '',
				email_header_bg: $('#email_header_bg').val() || '#1e293b',
				email_header_color: $('#email_header_color').val() || '#ffffff',
				email_body_greeting: $('#email_body_greeting').val() ? $('#email_body_greeting').val().trim() : '',
				email_body_bg: $('#email_body_bg').val() || '#f1f5f9',
				email_card_bg: $('#email_card_bg').val() || '#ffffff',
				email_text_color: $('#email_text_color').val() || '#334155',
				email_footer_text: $('#email_footer_text').val() ? $('#email_footer_text').val().trim() : '',
				email_footer_bg: $('#email_footer_bg').val() || '#f8fafc',
				email_footer_color: $('#email_footer_color').val() || '#64748b',
				email_show_dashboard_btn: $('#email_show_dashboard_btn').is(':checked') ? '1' : '0'
			},
			success: function(response) {
				if (response && response.success && response.data && response.data.html) {
					$content.html(response.data.html);
				} else {
					$content.html('<div class="wlgf-alert wlgf-alert--danger" style="margin: 20px;">Failed to generate template preview.</div>');
				}
			},
			error: function() {
				$content.html('<div class="wlgf-alert wlgf-alert--danger" style="margin: 20px;">An unexpected server error occurred while rendering the preview.</div>');
			}
		});
	});

	// Close Preview Modal
	const closePreviewModal = function() {
		$('#wlgf-email-preview-modal').removeClass('wlgf-modal-overlay--active').addClass('d-none').css({
			display: 'none',
			opacity: '0',
			visibility: 'hidden'
		});
	};

	$(document).on('click', '#wlgf-close-email-preview-btn', function(e) {
		e.preventDefault();
		closePreviewModal();
	});

	$(document).on('click', '#wlgf-email-preview-modal', function(e) {
		if (e.target === this) {
			closePreviewModal();
		}
	});

	$(document).on('keydown', function(e) {
		if (e.key === 'Escape' && $('#wlgf-email-preview-modal').hasClass('wlgf-modal-overlay--active')) {
			closePreviewModal();
		}
	});

	// Viewport size toggle (Desktop vs Mobile)
	$(document).on('click', '.wlgf-preview-size-btn', function(e) {
		e.preventDefault();
		$('.wlgf-preview-size-btn').removeClass('wlgf-preview-size-btn--active').css('background', 'transparent').css('color', 'var(--wlgf-text-muted)');
		$(this).addClass('wlgf-preview-size-btn--active').css('background', 'var(--wlgf-primary)').css('color', '#ffffff');
		const targetWidth = $(this).data('width');
		$('#wlgf-email-preview-viewport').css('max-width', targetWidth);
	});

	// Complete Factory Reset Handler
	$('#wlgf-factory-reset-btn').on('click', function(e) {
		e.preventDefault();
		const $btn = $(this);

		const exportUrl = (typeof Settings !== 'undefined' && Settings.exportUrl) ? Settings.exportUrl : 'admin.php?page=wlgf-import-export';

		const confirmDialogHtml = `
			<div style="text-align: left; font-size: 14px; line-height: 1.55;">
				<p style="margin-bottom: 12px; font-weight: 700; color: #dc2626; font-size: 15px;">
					Are you absolutely sure you want to perform a complete factory reset?
				</p>
				<p style="margin-bottom: 14px; color: #4b5563;">
					This will permanently delete <strong>all custom forms</strong>, <strong>all captured lead records</strong>, <strong>customer follow-up notes</strong>, <strong>analytics metrics</strong>, and <strong>custom settings</strong>. Default starter forms and original configuration will be re-installed.
				</p>
				<div style="background: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 14px; border-radius: 6px; font-size: 13px; color: #92400e; margin-bottom: 14px;">
					<i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px; color: #f59e0b; font-size: 15px;"></i>
					<strong>Important Backup Notice:</strong> We strongly advise you to take a complete backup of your forms and leads before proceeding.
					<div style="margin-top: 6px;">
						<a href="${exportUrl}" target="_blank" style="color: #b45309; text-decoration: underline; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
							Go to Import & Export Page to Download Backup <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px;"></i>
						</a>
					</div>
				</div>
				<p style="margin: 0; font-size: 12.5px; color: #6b7280;">
					<i class="fa-solid fa-circle-exclamation"></i> This operation cannot be undone.
				</p>
			</div>
		`;

		const executeReset = function() {
			$btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Resetting Everything...');

			$.ajax({
				type: 'POST',
				url: Settings.ajaxUrl,
				data: {
					action: 'wlgf_factory_reset',
					nonce: Settings.resetNonce
				},
				success: function(response) {
					if (response && response.success) {
						if (typeof wlgfToast === 'function') {
							wlgfToast(response.data.message || 'Plugin reset successfully!', 'success', 3500);
						}
						setTimeout(function() {
							window.location.href = 'admin.php?page=wlgf-settings#wlgf-tab-help';
							window.location.reload();
						}, 1200);
					} else {
						const err = (response && response.data && response.data.message) ? response.data.message : 'Factory reset failed.';
						if (typeof wlgfAlert === 'function') {
							wlgfAlert({
								title: 'Reset Failed',
								message: err,
								type: 'danger'
							});
						} else {
							alert(err);
						}
						$btn.prop('disabled', false).html('<i class="fa-solid fa-rotate-left"></i> Reset Plugin to Factory Defaults');
					}
				},
				error: function() {
					if (typeof wlgfAlert === 'function') {
						wlgfAlert({
							title: 'Server Error',
							message: 'An unexpected server error occurred during reset. Please try again.',
							type: 'danger'
						});
					} else {
						alert('An unexpected server error occurred during reset. Please try again.');
					}
					$btn.prop('disabled', false).html('<i class="fa-solid fa-rotate-left"></i> Reset Plugin to Factory Defaults');
				}
			});
		};

		if (typeof wlgfConfirm === 'function') {
			wlgfConfirm({
				title: '⚠️ Confirm Complete Factory Reset',
				message: confirmDialogHtml,
				type: 'danger',
				confirmText: 'Yes, Factory Reset Everything',
				cancelText: 'Cancel'
			}).then(function(confirmed) {
				if (confirmed) {
					executeReset();
				}
			});
		} else {
			if (window.confirm('WARNING: Are you sure you want to completely factory reset the plugin? Please make sure to backup your data via the Import & Export page first!')) {
				executeReset();
			}
		}
	});

	// ==========================================
	// Submission Confirmation Live Preview & Controls
	// ==========================================
	function bindColorSync(pickerSelector, inputSelector, onChangeCallback) {
		$(pickerSelector).on('input change', function() {
			$(inputSelector).val($(this).val());
			if (typeof onChangeCallback === 'function') {
				onChangeCallback();
			}
		});
		$(inputSelector).on('input change', function() {
			const val = $(this).val().trim();
			if (/^#[0-9A-F]{6}$/i.test(val)) {
				$(pickerSelector).val(val);
			}
			if (typeof onChangeCallback === 'function') {
				onChangeCallback();
			}
		});
	}

	function updateConfirmationLivePreview() {
		const heading = $('#confirmation_heading').val() || '';
		const msg = $('#user_message').val() || '';
		const style = $('#confirmation_style').val() || 'modern-card';
		const iconKey = $('#confirmation_icon').val() || 'circle-check';
		const isAnimated = $('#confirmation_animated').is(':checked');

		const bg = $('#confirmation_bg').val() || '#f0fdf4';
		const border = $('#confirmation_border_color').val() || '#86efac';
		const headingColor = $('#confirmation_heading_color').val() || '#14532d';
		const textColor = $('#confirmation_text_color').val() || '#166534';
		const iconColor = $('#confirmation_icon_color').val() || '#16a34a';
		const iconBg = $('#confirmation_icon_bg').val() || '#dcfce7';

		const iconMap = {
			'circle-check': 'fa-solid fa-circle-check',
			'paper-plane': 'fa-solid fa-paper-plane',
			'sparkles': 'fa-solid fa-wand-magic-sparkles',
			'thumbs-up': 'fa-solid fa-thumbs-up',
			'envelope-circle-check': 'fa-solid fa-envelope-circle-check',
			'heart': 'fa-solid fa-heart',
			'bell': 'fa-solid fa-bell',
			'none': ''
		};

		const iconClass = iconMap[iconKey] || '';

		let cardClass = 'wlgf-confirmation-card wlgf-confirmation--' + style;
		if (isAnimated) {
			cardClass += ' wlgf-confirmation--animated';
		}

		const inlineStyle = '--wlgf-conf-bg:' + bg + ';--wlgf-conf-border:' + border + ';--wlgf-conf-heading:' + headingColor + ';--wlgf-conf-text:' + textColor + ';--wlgf-conf-icon:' + iconColor + ';--wlgf-conf-icon-bg:' + iconBg + ';';

		let html = '<div class="' + cardClass + '" style="' + inlineStyle + '" role="alert">';
		if (iconClass) {
			html += '<div class="wlgf-confirmation-badge"><i class="' + iconClass + '"></i></div>';
		}
		html += '<div class="wlgf-confirmation-content">';
		if (heading) {
			html += '<h4 class="wlgf-confirmation-title">' + $('<div>').text(heading).html() + '</h4>';
		}
		html += '<div class="wlgf-confirmation-desc">' + $('<div>').text(msg).html().replace(/\n/g, '<br>') + '</div>';
		html += '</div>';
		html += '</div>';

		$('#wlgf-conf-live-preview').html(html);
	}

	// Bind Color Pickers
	bindColorSync('#confirmation_bg_picker', '#confirmation_bg', updateConfirmationLivePreview);
	bindColorSync('#confirmation_border_color_picker', '#confirmation_border_color', updateConfirmationLivePreview);
	bindColorSync('#confirmation_icon_color_picker', '#confirmation_icon_color', updateConfirmationLivePreview);
	bindColorSync('#confirmation_icon_bg_picker', '#confirmation_icon_bg', updateConfirmationLivePreview);
	bindColorSync('#confirmation_heading_color_picker', '#confirmation_heading_color', updateConfirmationLivePreview);
	bindColorSync('#confirmation_text_color_picker', '#confirmation_text_color', updateConfirmationLivePreview);

	// Inputs that trigger live preview update
	$('#confirmation_heading, #user_message').on('input', updateConfirmationLivePreview);
	$('#confirmation_style, #confirmation_icon, #confirmation_animated').on('change', updateConfirmationLivePreview);

	// Confirmation Fadeout Duration Slider & Number Sync
	$('#confirmation_fadeout_range').on('input change', function() {
		const val = $(this).val();
		$('#confirmation_fadeout').val(val);
		$('#confirmation_fadeout_badge').text(val + 's');
	});
	$('#confirmation_fadeout').on('input change', function() {
		let val = parseInt($(this).val(), 10);
		if (isNaN(val)) val = 10;
		if (val < 5) val = 5;
		if (val > 50) val = 50;
		$(this).val(val);
		$('#confirmation_fadeout_range').val(val);
		$('#confirmation_fadeout_badge').text(val + 's');
	});

	// Replay Animation Button
	$('#wlgf-replay-conf-animation-btn').on('click', function(e) {
		e.preventDefault();
		const $badge = $('#wlgf-conf-live-preview .wlgf-confirmation-badge');
		const $card = $('#wlgf-conf-live-preview .wlgf-confirmation-card');
		if ($badge.length) {
			$badge.css('animation', 'none');
			$card.css('animation', 'none');
			setTimeout(function() {
				$badge.css('animation', '');
				$card.css('animation', '');
			}, 40);
		}
	});

	// Reset Defaults Button
	$('#wlgf-reset-conf-defaults-btn').on('click', function(e) {
		e.preventDefault();
		$('#confirmation_heading').val('Thank you! Submission Received');
		$('#user_message').val('Your query has been successfully submitted. We will get back to you shortly.');
		$('#confirmation_style').val('modern-card');
		$('#confirmation_icon').val('circle-check');
		$('#confirmation_animated').prop('checked', true);
		$('#confirmation_fadeout').val('10');
		$('#confirmation_fadeout_range').val('10');
		$('#confirmation_fadeout_badge').text('10s');

		$('#confirmation_bg').val('#f0fdf4');
		$('#confirmation_bg_picker').val('#f0fdf4');
		$('#confirmation_border_color').val('#86efac');
		$('#confirmation_border_color_picker').val('#86efac');
		$('#confirmation_icon_color').val('#16a34a');
		$('#confirmation_icon_color_picker').val('#16a34a');
		$('#confirmation_icon_bg').val('#dcfce7');
		$('#confirmation_icon_bg_picker').val('#dcfce7');
		$('#confirmation_heading_color').val('#14532d');
		$('#confirmation_heading_color_picker').val('#14532d');
		$('#confirmation_text_color').val('#166534');
		$('#confirmation_text_color_picker').val('#166534');

		updateConfirmationLivePreview();
	});

	// Sticky Header Scroll State Toggle
	const $stickyHeader = $('.wlgf-header--sticky');
	if ($stickyHeader.length) {
		const handleStickyScroll = function() {
			const isStuck = window.scrollY > 16;
			$stickyHeader.toggleClass('wlgf-header--is-stuck', isStuck);
		};
		window.addEventListener('scroll', handleStickyScroll, { passive: true });
		handleStickyScroll();
	}
});