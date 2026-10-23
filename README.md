# Lead Generation Form (WordPress Plugin)

<p align="center">
  <img src="https://wpfrank.com/wp-content/uploads/lead-generation-form-banner.png" alt="Lead Generation Form Banner" width="100%" style="border-radius: 10px;" />
</p>

<p align="center">
  <strong>Fast, Modern Drag & Drop Form Builder, Mini-CRM Lead Manager, Survey & Inquiry Solution for WordPress.</strong>
</p>

<p align="center">
  <a href="https://wordpress.org/plugins/lead-generation-form/"><img src="https://img.shields.io/badge/WordPress.org-Plugin-0073aa?logo=wordpress&logoColor=white" alt="WordPress Plugin" /></a>
  <a href="https://github.com/FARAZFRANK/lead-generation-form/releases/tag/v1.3.1"><img src="https://img.shields.io/badge/Release-v1.3.1-blue.svg?logo=github" alt="Latest Release" /></a>
  <a href="https://wordpress.org/"><img src="https://img.shields.io/badge/WordPress-5.6%20to%206.7%2B-21759b.svg?logo=wordpress" alt="WordPress Compatibility" /></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-7.4%20--%208.3-777bb4.svg?logo=php&logoColor=white" alt="PHP Compatibility" /></a>
  <a href="http://www.gnu.org/licenses/gpl-2.0.html"><img src="https://img.shields.io/badge/License-GPL%20v2%2B-green.svg" alt="License" /></a>
  <a href="https://github.com/FARAZFRANK/lead-generation-form/pulls"><img src="https://img.shields.io/badge/PRs-Welcome-brightgreen.svg" alt="PRs Welcome" /></a>
</p>

<p align="center">
  <a href="#-overview">Overview</a> •
  <a href="#-key-features">Key Features</a> •
  <a href="#-quick-start">Quick Start</a> •
  <a href="#-free-vs-pro-matrix">Free vs Pro</a> •
  <a href="#-developer-hooks--api">Developer Hooks</a> •
  <a href="#-file-structure">File Structure</a> •
  <a href="#-changelog">Changelog</a> •
  <a href="#-contributing">Contributing</a>
</p>

---

## 🌟 Overview

**Lead Generation Form** is an intuitive, fast, lightweight, and modern drag-and-drop form builder and lead management plugin for WordPress. 

Whether you need a simple **Contact Us form**, a high-converting **lead generation form**, a **request a quote form**, an **appointment booking form**, a **property tour inquiry**, or a **customer feedback survey**, this plugin provides all the tools required to launch mobile-responsive forms in minutes — without writing code.

Designed as a modern, clean, and bloat-free alternative to Contact Form 7, WPForms, Ninja Forms, and Formidable Forms, Lead Generation Form bundles an intuitive visual canvas with a built-in Mini-CRM lead manager, Google reCAPTCHA v3 anti-spam defenses, authenticated SMTP email delivery, Mailchimp audience synchronization, Gutenberg block, Elementor widget, and instant CSV data export.

---

## 🚀 Key Features

### 🎨 Visual Drag & Drop Canvas
* **Instant Drag & Drop Interface:** Reorder, add, duplicate, configure, or remove fields with instantaneous live canvas updates.
* **100% Mobile Responsive:** Automatically scales and renders across iPhones, Android phones, tablets, and desktop viewports.
* **Theme Customizer:** Fine-tune brand colors, border radius, input shadows, typography, and dark mode per form.
* **Pre-Built Starter Templates:** 1-click launch starter forms including Contact Us, Service Quote, Real Estate Tour, Appointment Booking, and Customer Feedback.

### 📊 Built-In Mini-CRM & Captured Leads Manager
* **Zero Data Loss:** Never miss a client inquiry. All submissions are safely captured in your database before emails are triggered.
* **Searchable Form Selector:** Instant real-time combobox selector allowing you to search forms by name or ID (#3, Dental, Quote, etc.).
* **Pipeline Status Tracking:** Track progress of inquiries with 6 workflow statuses (`New Lead`, `In Review`, `Contacted`, `Qualified`, `Won / Converted`, `Abandoned / Lost`).
* **Internal Team Notes:** Add timestamped notes, call logs, and client conversation history to any submission.
* **1-Click Export:** Stream RFC-4180 compliant CSV exports with UTF-8 BOM encoding and formula injection protection (`=`, `@`, `+`, `-`).

### 🛡️ Bulletproof Anti-Spam Shield
* **Invisible Google reCAPTCHA v3:** Background verification with no disruptive user puzzles.
* **Hidden Honeypot Traps:** Traps malicious spambots silently without impacting genuine users.
* **Submission Speed Trap:** Blocks instant robotic scripted submissions.
* **Domain & Email Blacklisting:** Filter out disposable and known fraudulent domains.

### ✉️ Reliable Email & Marketing Automation
* **Custom Authenticated SMTP:** Built-in SMTP engine (TLS/SSL) with instant live test email delivery tool.
* **Dynamic Auto-Responder:** Send automated, personalized customer confirmations using smart tags (`{Name}`, `{Email}`, `{Form_Name}`).
* **Mailchimp Audience Sync:** Automatically push new leads into your Mailchimp marketing lists with custom tag routing and double opt-in.
* **Webhook Dispatcher:** Push lead submission payloads to Zapier, Make, Pabbly, or custom webhooks.

### 🧩 Embedding & Page Builder Integration
* **Gutenberg Block:** Native `/lead-form` block with live form preview and dropdown selector.
* **Elementor Widget:** Dedicated Elementor widget with live styling and typography controls.
* **Shortcode Support:** Standard WordPress shortcode `[WLFG id="1"]` usable in any page builder, widget, or theme template.

---

## 📸 Screenshots & Demos

| Visual Form Builder | Captured Leads Mini-CRM |
|:---:|:---:|
| Interactive drag-and-drop canvas with live preview and field palette. | Searchable leads database with status pipeline and CSV export. |

| Starter Template Library | Anti-Spam & SMTP Settings |
|:---:|:---:|
| 1-click industry templates tailored for quick deployment. | Authenticated SMTP configuration with live email connectivity test. |

---

## ⚡ Quick Start

### Requirements
* WordPress **5.6** or higher (fully tested on **WordPress 6.7**)
* PHP **7.4** to **8.3**
* MySQL **5.6+** or MariaDB **10.1+**

### Installation via WordPress Admin Dashboard
1. Log in to your WordPress Admin panel (`wp-admin`).
2. Navigate to **Plugins** > **Add New**.
3. Search for `Lead Generation Form`.
4. Click **Install Now** and then **Activate**.
5. Navigate to **Lead Generation Form** > **Form Builder** to create your first form.

### Manual Installation via Git
```bash
# Navigate to WordPress plugins folder
cd wp-content/plugins/

# Clone this repository
git clone https://github.com/FARAZFRANK/lead-generation-form.git lead-generation-form

# Activate the plugin in WordPress
wp plugin activate lead-generation-form
```

---

## 📖 Usage Guide

### Embedding with Shortcode
Place the shortcode anywhere on your pages, posts, or custom post types:
```html
[WLFG id="1"]
```

### Embedding with Gutenberg Block
1. In the Block Editor, press `/` and type `Lead Form`.
2. Select **Lead Generation Form Block**.
3. Pick your desired form from the inspector dropdown on the right.

### Embedding with PHP in Theme Templates
```php
<?php echo do_shortcode( '[WLFG id="1"]' ); ?>
```

---

## ⚖️ Free vs Pro Comparison

| Feature | Free Edition | Pro Edition |
|:---|:---:|:---:|
| **Drag & Drop Visual Form Builder** | ✅ Full Access | ✅ Full Access |
| **Mobile-Responsive Forms** | ✅ Full Access | ✅ Full Access |
| **Starter Form Templates** | ✅ 5 Templates | ✅ 25+ Industry Templates |
| **Mini-CRM Lead Manager** | ✅ Table View | ✅ Table + Kanban Pipeline |
| **Searchable Form Selector in Leads** | ✅ Real-Time Search | ✅ Real-Time Search |
| **Internal Team Follow-Up Notes** | ✅ Unlimited | ✅ Unlimited |
| **CSV & Excel Export** | ✅ RFC-4180 Compliant | ✅ Advanced Filters & Columns |
| **Spam Protection (reCAPTCHA v3 & Honeypot)** | ✅ Standard | ✅ Advanced AI Shield |
| **Custom Authenticated SMTP Delivery** | ✅ Included | ✅ Included |
| **Mailchimp Audience Sync** | ✅ Single List | ✅ Multi-List & Conditional |
| **Gutenberg Block & Elementor Widget** | ✅ Included | ✅ Included |
| **Multi-Column Responsive Grid Layouts** | ❌ 1-Column | ✅ 2, 3, 4 Columns |
| **E-Signature Drawing Pad** | ❌ Pro Only | ✅ Canvas + PDF Export |
| **Math Formula & Dynamic Cost Estimator** | ❌ Pro Only | ✅ Live Calculation Engine |
| **Multi-Step Form Wizard & Progress Bars** | ❌ Pro Only | ✅ Unlimited Steps |
| **Online Payment Collection (Stripe & PayPal)** | ❌ Pro Only | ✅ SCA & PCI Compliant |
| **Floating Contact Launcher & Exit-Intent Popups**| ❌ Pro Only | ✅ Interactive Triggers |
| **Headless REST API & Webhooks** | ❌ Basic Webhook | ✅ Full REST Endpoints |

---

## 🛠️ Developer Hooks & Extensibility

Lead Generation Form is built with clean architecture, providing action hooks and filters for developers.

### Action Hooks
```php
/**
 * Triggered immediately after a lead submission is stored into the database.
 *
 * @param int   $form_id Form ID that was submitted.
 * @param array $lead_data Key-value array of submitted field inputs.
 */
add_action( 'wlgf_after_lead_created', function( $form_id, $lead_data ) {
    // Custom CRM sync or custom API call
}, 10, 2 );

/**
 * Triggered when a lead status is changed in the Mini-CRM.
 *
 * @param int    $form_id    Form ID.
 * @param int    $lead_index Index of the lead row.
 * @param string $new_status Updated status (New Lead, Qualified, Won, etc.).
 */
add_action( 'wlgf_lead_status_updated', function( $form_id, $lead_index, $new_status ) {
    // Notify team via Slack, SMS, or Telegram
}, 10, 3 );
```

### Filters
```php
/**
 * Filter sanitized form entry data before saving.
 *
 * @param array $sanitized_data Cleaned form payload.
 * @param int   $form_id        Form ID.
 */
add_filter( 'wlgf_sanitize_submission_payload', function( $sanitized_data, $form_id ) {
    // Apply custom validation or field formatting
    return $sanitized_data;
}, 10, 2 );

/**
 * Customize CSV export row output.
 *
 * @param array $csv_row Prepared row for CSV export.
 * @param array $raw_lead Original database record.
 */
add_filter( 'wlgf_csv_export_row', function( $csv_row, $raw_lead ) {
    return $csv_row;
}, 10, 2 );
```

---

## 📂 File Structure

```text
lead-generation-form/
├── admin/                           # WordPress Admin UI & Page Controllers
│   ├── assets/                      # Bundled CSS, JS, Fonts & Libraries
│   │   ├── css/                     # Page-specific stylesheets
│   │   ├── js/                      # Page controllers (leads.js, form-generator.js, etc.)
│   │   ├── datatables-2.0.8/        # DataTables table management
│   │   ├── fontawesome-free-6.4.2/  # FontAwesome icons
│   │   └── formbuilder-3.19.12/     # Canvas visual engine
│   ├── dashboard.php                # Plugin dashboard overview
│   ├── form-generator.php           # Visual form builder canvas
│   ├── free-vs-pro.php              # Comparison matrix screen
│   ├── import-export.php            # Forms backup & restore
│   ├── leads.php                    # Mini-CRM leads manager
│   ├── manage-forms.php             # Forms directory table
│   └── settings.php                 # Global plugin & SMTP settings
├── includes/                        # Core plugin engine & integrations
│   ├── blocks/                      # Native Gutenberg block implementation
│   ├── elementor/                   # Elementor widget integration
│   ├── class-wlgf-email-template.php# Email templating & auto-responders
│   ├── class-wlgf-newsletter.php    # Mailchimp API v3 sync integration
│   ├── class-wlgf-templates.php     # 1-click starter template library
│   ├── install-script.php           # Database schema & activation routines
│   └── shortcode-ajax.php           # Frontend submission handler & AJAX API
├── languages/                       # Internationalization (.po/.mo files)
├── lead-generation-form.php         # Plugin bootstrap file
├── README.md                        # GitHub documentation
├── readme.txt                       # WordPress.org repository specifications
└── uninstall.php                    # Safe database cleanup on deletion
```

---

## 🔒 Security & Privacy

Lead Generation Form follows enterprise WordPress security guidelines:
- **Direct Access Prevention:** All PHP files feature `if ( ! defined( 'ABSPATH' ) ) exit;` guards.
- **CSRF & Nonce Protection:** All administrative AJAX and public form submissions are verified with cryptographically secure nonces (`wp_verify_nonce`).
- **Input Sanitization & Output Escaping:** Every input is passed through strict WordPress sanitization (`sanitize_text_field`, `sanitize_email`, `wp_kses_post`), and database queries use prepared statements (`$wpdb->prepare`).
- **CSV Injection Protection:** Prevents Spreadsheet Formula Injection (CSV Injection) by prepending safety ticks to cells starting with `=`, `@`, `+`, or `-`.
- **GDPR Compliance:** Supports consent checkboxes and allows leads to be permanently deleted on request.

---

## 📋 Changelog

Refer to [readme.txt](readme.txt) for the full changelog history.

### Version 1.3.1 (23 Oct 2026)
* **Added:** Real-time searchable combobox for the "Choose Form" selector in Captured Leads with instant title and ID filtering.
* **Added:** Dedicated "Free vs Pro" header action buttons across all admin page headers (Dashboard, Manage Forms, Form Builder, Captured Leads, Import & Export, Settings).
* **Improved:** Form Template Library popup with clean icon and text spacing on Pro badge indicators.
* **Fixed:** Enforced 5 starter templates in Template Library for the Free edition and protected Pro multi-step/calculation templates.
* **Fixed:** Form builder canvas safety restrictions for Pro-only fields in the Free edition.
* **Fixed:** Button and tab UI alignment issues across the form generator canvas.
* **Performance:** Optimized asset loading and cache busting across admin pages.

---

## 🤝 Contributing

Contributions, feature requests, and bug reports are welcome!
To contribute:
1. **Fork** this repository.
2. Create a descriptive feature branch (`git checkout -b feature/amazing-feature`).
3. Commit your changes (`git commit -m 'Add amazing feature'`).
4. Push to the branch (`git push origin feature/amazing-feature`).
5. Open a **Pull Request**.

Please follow the [WordPress PHP Coding Standards (WPCS)](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) for all code submissions.

---

## 📞 Support & Community

* **WordPress Support Forum:** [wordpress.org/support/plugin/lead-generation-form](https://wordpress.org/support/plugin/lead-generation-form/)
* **Plugin Documentation:** [wpfrank.com/docs/lead-generation-form](https://wpfrank.com/wordpress-plugins/lead-generation-form-pro/)
* **Official Website:** [wpfrank.com](https://wpfrank.com/)
* **GitHub Issues:** [github.com/FARAZFRANK/lead-generation-form/issues](https://github.com/FARAZFRANK/lead-generation-form/issues)

---

## 📜 License

Lead Generation Form is open-source software licensed under the [GNU General Public License v2.0 or later](http://www.gnu.org/licenses/gpl-2.0.html).
