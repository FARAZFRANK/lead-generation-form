# Implementation Plan: CC Lead Notifications & Email/Domain Blacklist Protection

## Overview
Add two high-demand enterprise features directly into existing, logical tabs in `http://localhost/dev/wp-admin/admin.php?page=wlgf-settings` without creating any new tabs and with zero breaking changes or conflicts:
1. **CC Lead Notification to More Admins**: Placed inside the existing **Email & SMTP** tab (`#wlgf-tab-smtp`) under the *Admin Email Notifications* card.
2. **Blacklist Email and Domains to Prevent Spam & Phishing**: Placed inside the existing **Anti-Spam** tab (`#wlgf-tab-security`) alongside Google reCAPTCHA v3 in a new, modern security card.

---

## 1. Feature Specifications

### 1.1 CC Lead Notification to More Admins
- **Existing Tab**: `wlgf-tab-smtp` ("Email & SMTP")
- **Target Section**: Inside the "Admin Email Notifications" card, right after `notify_admin` / `email_engine`.
- **Field Name**: `cc_admin_emails`
- **UI Elements**:
  - Multi-line textarea or clean input for multiple admin/team email addresses (comma or newline separated).
  - Badge: "Multi-Recipient"
  - Descriptive help text: Explaining that a carbon copy (`Cc:`) of all new lead notifications will be automatically delivered to these addresses along with the primary WordPress site admin email.
- **Backend Sanitization**: Split by commas/newlines, validate each address with `is_email()`, deduplicate against `admin_email`, and store clean values.
- **Dispatch Integration**:
  - `includes/shortcode-ajax.php`: Injects `Cc: recipient@domain.com` headers into `$wlgf_headers` for both `wp_mail` and SMTP engines.
  - `includes/class-wlgf-rest-api.php`: Injects `Cc:` headers in `send_notification_emails()`.

---

### 1.2 Blacklist Email and Domains (Spam & Phishing Prevention)
- **Existing Tab**: `wlgf-tab-security` (Updated label: "Anti-Spam & Security Protection")
- **Target Section**: Dedicated Card inside `#wlgf-tab-security` below/alongside reCAPTCHA.
- **Settings Fields**:
  1. `blacklist_status`: Select toggle (`1` = ON - Active Protection, `2` = OFF - Disabled).
  2. `blacklist_emails`: Textarea for specific blocked email addresses (e.g. `spammer@badactor.com, scam@phishing.net`).
  3. `blacklist_domains`: Textarea for blocked domain names or wildcard TLDs (e.g. `mailinator.com, tempmail.com, guerrillamail.com, *.ru, .xyz`).
  4. `blacklist_action`: Dropdown:
     - `reject_error`: "Show rejection notice to submitter (Explicit Block)"
     - `silent_drop`: "Silent Drop / Honeypot mode (Shows fake success message to spammer, but discards data without storing lead or sending emails)"
  5. `blacklist_message`: Custom rejection message to display when `reject_error` is selected. Default: *"Sorry! Your email address or domain is not permitted to submit this form."*
- **Helper Function**: `wlgf_check_submission_blacklist( $sanitized_data, $form_info = array(), $settings = null )`
  - Robust domain parsing (handles `@domain`, full email, subdomains, wildcards).
  - Case-insensitive comparison.
- **Submission Enforcement**:
  - `includes/shortcode-ajax.php`: Evaluated in both reCAPTCHA-active and reCAPTCHA-disabled flows before database commit or email dispatch.
  - `includes/class-wlgf-rest-api.php`: Evaluated before database save and webhook/email triggers.

---

## 2. Step-by-Step Execution Plan

- [x] **Step 1**: Implement core helper functions in `lead-generation-form.php`:
  - `wlgf_check_submission_blacklist( $sanitized_data, $form_info, $settings )`
  - Update `wlgf_save_settings_callback()` to sanitize and persist `cc_admin_emails`, `blacklist_status`, `blacklist_emails`, `blacklist_domains`, `blacklist_action`, `blacklist_message`.
- [x] **Step 2**: Update `admin/settings.php`:
  - In `#wlgf-tab-smtp`: Add CC email input & description in the Admin Email Notifications card.
  - In `#wlgf-tab-security`: Add the new "Email & Domain Blacklist Protection" card with responsive layout and clean badges.
- [x] **Step 3**: Update `admin/assets/js/settings.js`:
  - Add live toggle behavior for blacklist settings container.
  - Collect and pass new fields in the AJAX save payload.
- [x] **Step 4**: Update `includes/shortcode-ajax.php`:
  - Integrate blacklist checking (handling both `reject_error` and `silent_drop`).
  - Add CC email recipients to admin notification email headers.
- [x] **Step 5**: Update `includes/class-wlgf-rest-api.php`:
  - Integrate blacklist verification and CC email headers in REST API endpoint.
- [x] **Step 6**: Update `includes/install-script.php`:
  - Add default settings values for clean installs and factory resets.
- [x] **Step 7**: Verification and Testing:
  - Verify settings page rendering and saving.
  - Test CC email header generation.
  - Test email and domain blacklist enforcement (exact email match, domain match, wildcard, reject vs silent drop).
