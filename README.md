# Lead Generation Form - Drag & Drop Form Builder for WordPress

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.3.1-blue.svg)](https://wordpress.org/plugins/lead-generation-form/)
[![WordPress Tested](https://img.shields.io/badge/WordPress-6.7%2B-green.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20--%208.3-indigo.svg)](https://php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

> **The ultimate modern visual drag-and-drop contact form builder, lead capture engine, inquiry manager, and survey solution for WordPress.**

---

## 🚀 Overview

**Lead Generation Form** is an intuitive, fast, and modern drag-and-drop Contact Form Builder and Lead Capture solution for WordPress. Whether you need a simple **Contact Us form**, a clean **inquiry form**, a **request a quote form**, an **appointment booking form**, an **event registration form**, or a **customer feedback survey**, this plugin helps you launch high-converting, mobile-responsive forms in minutes — without writing code.

---

## 🌟 Key Features

- **Visual Drag & Drop Canvas:** Build complex or simple forms visually with instant real-time canvas preview.
- **100% Mobile Responsive:** Adapts seamlessly to smartphones, tablets, laptops, and desktop screens.
- **Mini-CRM Lead Manager:** Store, inspect, filter, and manage captured leads directly inside the WordPress dashboard.
- **Searchable Form Selector:** Quickly search and filter through forms by title or ID number to access inquiries in seconds.
- **Lead Follow-Up Internal Notes:** Add timestamped notes and status tracking to any captured lead submission.
- **Anti-Spam Shield:** Invisible Google reCAPTCHA v3, speed traps, honeypot traps, and domain/email blacklists.
- **SMTP Email Notifications:** Reliable admin alerts and customer auto-responders with custom SMTP credentials and live connection testing.
- **Mailchimp Audience Sync:** Automatically sync subscriber emails to Mailchimp with custom tags and double opt-in.
- **Gutenberg & Elementor Ready:** Dedicated `/lead-form` Gutenberg block, native Elementor widget, and shortcodes `[WLFG id="..."]`.
- **Pre-Built Form Templates:** 5 instant starter templates (Contact, Quote, Real Estate, Booking, Feedback) + Pro templates.
- **CSV / Excel Export:** 1-click UTF-8 BOM compliant CSV export with formula injection protection.
- **Theme & Design Customizer:** Customize primary accent colors, border radius, typography, button hover states, and dark mode.

---

## 📋 Changelog

### 1.3.1 (23 Oct 2026)
- **Added:** Real-time searchable combobox for the "Choose Form" selector in Captured Leads with instant title and ID filtering.
- **Added:** Dedicated "Free vs Pro" header action buttons across all admin screens (Dashboard, Manage Forms, Form Builder, Captured Leads, Import & Export, Settings).
- **Improved:** Form Template Library popup with clean icon and text spacing on Pro badge indicators.
- **Fixed:** Enforced 5 starter templates in Template Library for the Free edition and protected Pro multi-step/calculation templates.
- **Fixed:** Form builder canvas safety restrictions for Pro-only fields in the Free edition.
- **Fixed:** Button and tab UI alignment issues across the form generator canvas.
- **Performance:** Optimized asset loading and cache busting across admin pages.

### 1.3.0 (23 Oct 2026)
- **Freemium Compliance:** Complete architecture alignment complying with WordPress.org Guideline 5.
- **Decoupled Backend:** Clean modular separation for Pro-exclusive services (Stripe, PayPal, floating widget, abandoned lead recovery, REST API).
- **Free vs Pro Matrix:** Interactive feature comparison screen and upgrade badges across the form generator.
- **Compatibility:** Fully tested and verified with WordPress 6.7 and PHP 8.0 - 8.3.

### 1.2.1 (04 Sep 2026)
- Synchronized dynamic plugin version across all asset enqueues and Gutenberg block registrations.
- Enhanced asset caching and cache busting for CSS stylesheets and JavaScript bundles.
- Added live plugin version indicator in Settings system status diagnostics.

### 1.2.0 (04 Sep 2026)
- Major release featuring modern Admin UI redesign.
- Added pre-built form templates in the Form Generator.
- Added Mini-CRM lead pipeline status selector (`New`, `In Review`, `Contacted`, `Qualified`, `Won`, `Lost`).
- Added internal sticky notes system for captured leads.
- Added Mailchimp audience sync with double opt-in support.
- Added custom authenticated SMTP engine with instant test email tool.

---

## 💻 Installation

1. Upload the `lead-generation-form` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Lead Generation Form** > **Form Builder** to create your first contact form.
4. Embed your form on any page using the Gutenberg Block `/lead-form`, Elementor widget, or shortcode `[WLFG id="1"]`.

---

## 📄 License

This plugin is licensed under the [GNU General Public License v2 or later](http://www.gnu.org/licenses/gpl-2.0.html).
