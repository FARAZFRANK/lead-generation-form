<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly */
}

//add sample forms
$wlgf_form_id_1 = sanitize_text_field('1');
$wlgf_form_name_1 = sanitize_text_field('Contact Form'); // contact form
$wlgf_form_1 = sanitize_text_field('[{"type": "text", "required": true, "label": "Name", "description": "Please enter your full name.", "placeholder": "Type Your Full Name", "className": "form-control", "name": "Name", "subtype": "text", "maxlength": 30 }, {"type": "text", "subtype": "email", "required": true, "label": "Email", "description": "Enter a valid email address where we can reach you.", "placeholder": "Type Your Email Address", "className": "form-control", "name": "Email", "maxlength": 50 }, {"type": "select", "required": true, "label": "Select Topic", "description": "Choose the topic that best fits your inquiry from the dropdown menu.", "className": "form-control", "name": "Product", "values": [ {"label": "General Question", "value": "General Question", "selected": false}, {"label": "Support", "value": "Support", "selected": false}, {"label": "Advertisement", "value": "Advertisement", "selected": false}, {"label": "Other", "value": "Other", "selected": false} ]}, {"type": "text", "required": true, "label": "Subject", "description": "Provide a brief summary of your query.", "placeholder": "Type A Subject About Your Query", "className": "form-control", "name": "Subject", "subtype": "text", "maxlength": 1000 }, {"type": "textarea", "required": true, "label": "Message", "description": "Type your message here in detailed as possible to ensure we understand your needs.", "placeholder": "Write Brief Message About Your Query", "className": "form-control", "name": "Message", "subtype": "textarea", "rows": 7}, {"type": "button", "subtype": "submit", "label": "Submit", "className": "btn-primary btn btn-lg", "name": "submit", "style": "primary"}]');
add_option('wlgf_form_1', array('form_id' => $wlgf_form_id_1, 'form_name' => $wlgf_form_name_1, 'form' => $wlgf_form_1));

$wlgf_form_id_2 = sanitize_text_field('2');
$wlgf_form_name_2 = sanitize_text_field('Sample Lead Form'); // sample lead form
$wlgf_form_2 = sanitize_text_field('[ { "type": "header", "subtype": "h3", "label": "Contact Information", "access": false }, { "type": "text", "required": true, "label": "First Name", "placeholder": "type your first name", "className": "form-control", "name": "first-name", "subtype": "text" }, { "type": "text", "required": true, "label": "Last Name", "placeholder": "type your last name", "className": "form-control", "name": "last-name", "subtype": "text" }, { "type": "text", "subtype": "email", "required": true, "label": "Email", "placeholder": "type your email address", "className": "form-control", "name": "email", "access": false }, { "type": "text", "subtype": "tel", "required": false, "label": "Phone Number", "placeholder": "type your phone number", "className": "form-control", "name": "phone-number", "access": false }, { "type": "header", "subtype": "h3", "label": "Business or Personal Details", "access": false }, { "type": "text", "required": false, "label": "Company Name", "className": "form-control", "name": "company-name", "subtype": "text" }, { "type": "text", "required": false, "label": "Job Title", "className": "form-control", "name": "job-title", "subtype": "text" }, { "type": "text", "required": false, "label": "Website", "className": "form-control", "name": "text-1693828833052-0", "subtype": "text" }, { "type": "header", "subtype": "h3", "label": "Interest or Intent", "access": false }, { "type": "select", "required": false, "label": "Interested In", "className": "form-control", "name": "interested-in", "multiple": false, "values": [ { "label": "None", "value": "none", "selected": true }, { "label": "Option 1", "value": "option-1", "selected": false }, { "label": "Option 2", "value": "option-2", "selected": false }, { "label": "Option 3", "value": "option-3", "selected": false } ] }, { "type": "number", "required": false, "label": "Budget", "className": "form-control", "name": "budget", "access": true }, { "type": "select", "required": false, "label": "Purchase Timeframe", "className": "form-control", "name": "purchase-timeframe", "multiple": false, "values": [ { "label": "None", "value": "none", "selected": true }, { "label": "Daily", "value": "daily", "selected": false }, { "label": "Weekly", "value": "weekly", "selected": false }, { "label": "Bi-weekly", "value": "biweekly", "selected": false }, { "label": "Monthly", "value": "monthly", "selected": false }, { "label": "Yearly", "value": "yearly", "selected": false } ] }, { "type": "header", "subtype": "h3", "label": "Geographic Information", "access": false }, { "type": "select", "required": false, "label": "Country", "className": "form-control", "name": "country", "multiple": false, "values": [ { "label": "Select Country", "value": "none", "selected": true }, { "label": "USA", "value": "usa", "selected": false }, { "label": "United Kingdom", "value": "gb", "selected": false }, { "label": "Brazil", "value": "br", "selected": false }, { "label": "United Arab Emirates", "value": "ae", "selected": false }, { "label": "Zimbabwe", "value": "zw", "selected": false } ] }, { "type": "header", "subtype": "h3", "label": "Optional", "access": false }, { "type": "select", "required": false, "label": "How did you hear about us?", "className": "form-control", "name": "hear-about-us", "multiple": false, "values": [ { "label": "Internet", "value": "internet", "selected": true }, { "label": "Newspaper", "value": "newspaper", "selected": false }, { "label": "Media", "value": "Media", "selected": false }, { "label": "Ads", "value": "Ads", "selected": false }, { "label": "Friends", "value": "friends", "selected": false }, { "label": "Family", "value": "family", "selected": false }, { "label": "Ohters", "value": "others", "selected": false } ] }, { "type": "textarea", "required": false, "label": "Comments/Questions", "className": "form-control", "name": "comments-questions", "subtype": "textarea", "rows": 5 }, { "type": "button", "subtype": "submit", "label": "Submit", "className": "btn-primary btn btn-lg", "name": "submit", "style": "primary" } ]');
add_option('wlgf_form_2', array('form_id' => $wlgf_form_id_2, 'form_name' => $wlgf_form_name_2, 'form' => $wlgf_form_2));


// Add default settings
$wlgf_user_message = __('Thank you! Your query has been successfully submitted.', 'lead-generation-form');
add_option('wlgf_settings', array(
	'recaptcha' => 2,
	'captcha_provider' => 'none',
	'sitekey' => '',
	'secretkey' => '',
	'turnstile_sitekey' => '',
	'turnstile_secretkey' => '',
	'submission_rate_limit' => '1',
	'honeypot_status' => '1',
	'scanner_shield_status' => '1',
	'notify_admin' => 1,
	'email_engine' => 1,
	'smtp_host' => '',
	'smtp_username' => '',
	'smtp_password' => '',
	'smtp_encryption' => '',
	'smtp_port' => '',
	'user_message' => $wlgf_user_message,
	'cc_admin_emails' => '',
	'blacklist_status' => '2',
	'blacklist_emails' => '',
	'blacklist_domains' => '',
	'blacklist_action' => 'reject_error',
	'blacklist_message' => __('Sorry! Your email address or domain is not permitted to submit this form.', 'lead-generation-form')
));