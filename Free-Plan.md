# Master Implementation Plan: Lead Generation Form (Free / Lite Edition)

**Target Directory:** `D:\wamp64\www\dev\wp-content\plugins\lead-generation-form`  
**Original Pro Reference Directory:** `D:\wamp64\www\dev\wp-content\plugins\lead-generation-form-pro`  
**GitHub Repository:** `https://github.com/FARAZFRANK/lead-generation-form`  
**Plugin Version Ground Truth:** Free v1.3.1 (or matching SemVer) derived from Pro v3.4.8  
**Compliance Standard:** [WordPress.org Plugin Review Guidelines 2026 (Guidelines 1, 5, 6, 9)](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)

---

## 1. Executive Summary & Freemium Strategy

### The Goal
Convert the enterprise-grade **Lead Generation Form Pro** codebase into an exceptionally reliable, high-converting, and WordPress.org-compliant **Free / Lite Edition**.

### The Freemium Philosophy (Win-Win Architecture)
1. **Free Version Must Be Complete & Genuinely Useful:**
   - Free users get **unlimited forms**, **unlimited fields**, **unlimited database lead storage**, drag-and-drop form building, 5 ready-to-use starter templates, email notifications via standard `wp_mail` or custom authenticated SMTP, Google reCAPTCHA v3, and full CSV export.
   - It is **not** crippled trialware. Users will genuinely enjoy using it and happily leave 5-star reviews on WordPress.org.
2. **Pro Version Must Be Irresistible for Growing Businesses:**
   - Pro solves high-ticket commercial pain points: **Multi-Step Form Wizards**, **Floating Contact Launcher (WhatsApp & Call)**, **Exit-Intent & Scroll Popups**, **Real-Time Abandoned Lead Recovery**, **Mini-CRM Kanban Sales Pipeline**, **E-Signatures & PDF Invoices**, **Dynamic Math Calculations & Price Estimators**, **Stripe & PayPal Live Payments**, and **25+ Enterprise Niche Templates**.
   - These are features businesses gladly pay $49 (one-time) for because it replaces $1,500+/year in fragmented SaaS subscriptions (OptinMonster, WPForms Pro, SiteLeads, Forminator Pro).

---

## 2. WordPress.org 2026 Guideline Compliance (Strict Enforcement)

### Guideline 5: No Locked Code or Trialware in Repository
> *"Plugins must not restrict or lock functionality. You may not include locked features in the code 'just in case the user upgrades.' You may point out features available in a separate Pro plugin, but the Pro code must be hosted elsewhere and not included in the WordPress.org repo version."*

### Technical Implementation Rule:
| UI Component | Free Version Behavior | Backend Code Rule |
| :--- | :--- | :--- |
| **Settings Tabs (Payments, Headless API, Floating Widget, Blacklist)** | Render controls with `disabled` HTML attributes, clean `PRO` badge, and an informational banner explaining the feature with an **Upgrade to Pro** button. | **Zero Pro Execution Code:** Remove the backend payment charging logic, floating widget frontend display engine, popup triggers script, and abandoned lead background worker from the Free plugin. |
| **Form Builder Palette (E-Signature, Calculation, Step-Breaker)** | Visible with a lock icon / `PRO` badge. Clicking triggers an interactive **Upgrade Modal** showcasing demo & benefits. | Free builder JS does not instantiate canvas signatures or formula parsing engines. |
| **Form Builder Layout Switcher (2-col, 3-col, 4-col)** | Single column (100%) active; 2/3/4 column buttons show `PRO` badge and modal on click. | Frontend CSS handles 100% width cleanly. |
| **Leads Admin Screen (Kanban Board & PDF Download)** | Standard responsive table view is 100% free; Kanban toggle & "Download PDF" button display `PRO` badge and open upgrade modal. | Free version does not bundle `class-wlgf-pdf-generator.php` or Kanban drag-and-drop state mutation endpoint. |

---

## 3. Feature Matrix: Free vs. Pro Allocation

*Grounded in `PRODUCT_PAGE_CONTENT_UPDATE.md` (PART 6: Updated Pricing Table)*

| Feature / Capability | Free Edition ($0) | Pro Edition ($49 One-Time) | Strategic Rationale |
| :--- | :---: | :---: | :--- |
| **Pricing & License** | Free (WordPress.org) | $49 One-Time (Lifetime Updates) | Clear entry-level funnel |
| **Website Usage** | 1 Website | 1 Website (Multi-site Upgrades) | Standard model |
| **Customer Support** | WordPress.org Forum | 1 Year Priority Support | Premium incentive |
| **Forms & Fields** | **Unlimited** | **Unlimited** | Complies with WP.org non-crippleware rule |
| **Lead Submissions in DB** | **Unlimited** | **Unlimited** | Complies with WP.org (no artificial database cap) |
| **Drag & Drop Builder** | ✅ Yes | ✅ Yes (Enhanced UI) | Excellent free experience |
| **Responsive Mobile Layout** | ✅ Yes | ✅ Yes | Core necessity |
| **Column Layout Switcher** | Single Column (100%) | Multi-Column (50%, 33%, 25%) | Pro incentive for compact/agency forms |
| **Pre-Built Form Templates** | 5 Core Starter Forms | 25+ Enterprise Niche Templates | High value for busy agencies |
| **Multi-Step Form Wizard** | ❌ No | ✅ Yes (Circles, Bar, Breadcrumbs) | Massive conversion driver for Pro |
| **Per-Step Client Validation** | ❌ No | ✅ Yes | Required for multi-step |
| **Floating Contact Launcher** | ❌ No (Disabled UI Preview) | ✅ Yes (7 Positions, WhatsApp, Call) | Replaces SiteLeads ($190/yr) |
| **Exit-Intent & Scroll Popups**| ❌ No (Disabled UI Preview) | ✅ Yes (Exit, Timed, Scroll Depth) | Replaces OptinMonster ($350/yr) |
| **Abandoned Lead Auto-Capture**| ❌ No | ✅ Yes (Real-time blur capture) | Replaces WPForms Pro Addon ($399/yr) |
| **Mini-CRM Kanban Pipeline** | ❌ Table View Only | ✅ Yes (6 Draggable Sales Stages) | Replaces HubSpot Starter ($240/yr) |
| **1-Click WhatsApp / Phone** | ❌ No | ✅ Yes (Direct Outreach from Lead) | Pro sales acceleration |
| **HTML5 E-Signature Pad** | ❌ No (Palette Pro Badge) | ✅ Yes (Touch, Mouse & Stylus) | Replaces Forminator Addon ($90/yr) |
| **Pure-PHP Vector PDF Invoices**| ❌ No | ✅ Yes (Legal Signature Certificate) | High-end B2B requirement |
| **Dynamic Math & Estimator** | ❌ No (Palette Pro Badge) | ✅ Yes (Instant live price calculator)| Powerful quote generator |
| **Visual Conditional Logic** | ❌ No | ✅ Yes (Multi-rule AND/OR branching)| Enterprise workflows |
| **Stripe Elements (SCA 3D)** | ❌ No (Settings Disabled UI) | ✅ Yes (Credit Card, Apple/Google Pay)| Direct monetization in Pro |
| **PayPal Smart Buttons** | ❌ No (Settings Disabled UI) | ✅ Yes (PayPal, Pay in 4) | Direct monetization in Pro |
| **Server-Side IP Rate Limiting**| ❌ No | ✅ Yes (5 submissions / 120s limit) | Anti-abuse enterprise protection |
| **Smart Honeypot Anti-Spam** | ✅ Basic Decoy | ✅ Stealth Silent Drop Trap | Essential free protection |
| **SQLi Heuristic Shield** | ❌ No | ✅ Yes (Automatic exploit drop) | Enterprise defense |
| **Google reCAPTCHA v3** | ✅ Standard reCAPTCHA | ✅ Enhanced + Admin Verification Logs| Safe and easy free bot defense |
| **Cloudflare Turnstile** | ❌ No (Settings Disabled UI) | ✅ Yes (Frictionless privacy-first) | Modern Pro alternative |
| **Email Dispatch Engine** | ✅ wp_mail + Custom SMTP | ✅ DMARC-Safe Header Engine | Reliable email delivery for all |
| **Submissions Auto-Responder** | ✅ Plain Text / Standard | ✅ Branded HTML Responsive Template | Brand polish in Pro |
| **Dynamic Email Merge Tags** | ❌ No | ✅ Yes ({Name}, {Form}, {Date}) | Personalized emails in Pro |
| **Multi-Admin CC Routing** | ❌ No | ✅ Yes (Global & Shortcode routing) | Agency/team requirement |
| **Excel & CSV Lead Export** | ✅ Full CSV Export | ✅ CSV + Filtered Views + PDF | Easy data mobility |
| **Headless REST API** | ❌ No (Settings Disabled UI) | ✅ Yes (Full JSON Endpoints & CORS) | Developers / App creators |
| **Conversion Funnel Analytics**| ✅ Basic Counts & Views | ✅ Chart.js Drop-off Funnel Charts | Deep CRO insights |

---

## 4. Seamless Free-to-Pro Upgrade & Data Preservation Architecture

### 100% Shared Schema (Zero Migration Loss)
- **Forms Option Key:** `wlgf_form_{form_id}` — Free and Pro share the exact same JSON / associative array structure. When a user activates Pro, all forms created in Free load immediately without conversion.
- **Leads Option Key:** `wlgf_saved_form_data_{form_id}` — All leads captured in Free remain untouched and immediately visible in Pro (and unlock Kanban view & PDF generation).
- **Form Views & Analytics:** `wlgf_form_views_{form_id}` & `wlgf_analytics_{form_id}` — Preserved identically.
- **Global Settings:** `wlgf_settings` — Free and Pro share the same option array. Pro simply reads the additional keys.

### Collision-Free Activation & Mutual Deactivation
- In `lead-generation-form.php` (Free):
  ```php
  if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
      // Pro is active! Free yields gracefully without error or duplicate notices
      return;
  }
  define( 'WLGF_IS_PRO', false );
  define( 'WLGF_PLUGIN_VERSION', '1.3.0' );
  ```
- When Pro is activated on the same WordPress site:
  - Pro automatically deactivates Free via `deactivate_plugins()` without triggering Free's `uninstall.php`.
  - Zero data is purged.
- In `uninstall.php` (Free):
  - Add check: If Pro is currently installed or active, **DO NOT delete** any `wlgf_*` options or leads!

---

## 5. Dedicated "Free vs Pro" Comparison Admin Page

### Navigation & Placement
- Added as a top-level visible submenu: **`Lead Generation Form` -> `Free vs Pro`** (`admin.php?page=wlgf-free-vs-pro`).
- Menu icon: High-visibility badge or star.
- Also linked from:
  - Settings Page header & "Upgrade to Pro" tab
  - Form Builder header banner
  - Leads page upgrade CTA

### Page Layout & Components:
1. **Hero Callout Section:**
   - Badge: `🚀 ELEVATE YOUR WORDPRESS CONVERSIONS`
   - Title: **Lead Generation Form Pro — The Complete Lead & Sales Suite**
   - Subtitle: *"Stop losing 70% of potential leads. Add WhatsApp chat, exit popups, e-signatures, payments, and multi-step funnels with zero monthly subscriptions."*
   - Two Prominent Buttons:
     - **Primary Action (Blue):** `Upgrade to Pro — $49 (One-Time)` -> `https://wpfrank.com/wordpress-plugins/lead-generation-form-pro/`
     - **Secondary Action (Ghost):** `Explore Live Interactive Demo` -> `https://wpfrank.com/demo/lead-generation-form-pro/`
   - Trust Badges: `✓ One-Time Payment ($49)` | `✓ Lifetime Updates` | `✓ 1 Year Priority Support` | `✓ 100% GDPR Compliant` | `✓ 30-Day Money-Back Guarantee`
2. **Complete 30-Row Comparison Table:**
   - Grouped logically (Forms & Layouts, Lead Recovery & CRO, Payments & Checkout, Security & Anti-Spam, Email & Delivery).
   - Clear visual indicators (`✓` in green, `—` in muted grey, badges for Pro).
3. **SaaS Cost Replacement Breakdown (PART 7):**
   - Shows visitors how Pro replaces OptinMonster ($350/yr), SiteLeads ($190/yr), WPForms Pro ($399/yr), Forminator Pro ($90/yr), and HubSpot ($240/yr) — saving $1,528/year.
4. **FAQ Section:**
   - Quick answers to top pre-purchase questions (No subscriptions, Fast performance, Compatibility with Elementor/Gutenberg).

---

## 6. Step-by-Step Execution Plan

### Phase 1: Core Configuration & Constants (Free Branch)
- [ ] Define `WLGF_IS_PRO` as `false` in `lead-generation-form.php`.
- [ ] Set plugin headers: Plugin Name: `Lead Generation Form`, Version: `1.3.0`, Text Domain: `lead-generation-form`.
- [ ] Ensure collision prevention: If Pro is active, Free steps aside gracefully.
- [ ] Verify `uninstall.php` guards against deleting data if Pro is present.

### Phase 2: Codebase Decoupling (Strict WordPress.org Compliance)
- [ ] **Remove Pro-only backend execution files** from Free:
  - `includes/class-wlgf-pdf-generator.php` (Remove from Free package)
  - `includes/class-wlgf-floating-widget.php` (Remove from Free package)
  - `includes/class-wlgf-popup-triggers.php` (Remove from Free package)
  - `includes/class-wlgf-abandoned-leads.php` (Remove from Free package)
  - `includes/class-wlgf-rest-api.php` (Remove execution logic / headless submit endpoints)
- [ ] Clean up `shortcode-ajax.php`:
  - Retain full single-step form rendering, field generation, AJAX submission, email dispatch via `wp_mail`/SMTP, CSV export, reCAPTCHA v3.
  - Remove Stripe & PayPal server charge handlers, abandoned lead auto-save endpoint, and PDF download endpoint.
- [ ] Clean up `lead-generation-form.php`:
  - Keep 5 starter templates in `includes/class-wlgf-templates.php` (Basic Contact, Simple Lead, Quote Request, Feedback, Newsletter). Remove 20+ niche Pro templates.

### Phase 3: Admin UI Disabled States & Pro Badging (The Upsell Experience)
- [ ] **Settings (`admin/settings.php` & `admin/assets/js/settings.js`):**
  - Payments tab: Keep all fields visible but disabled (`disabled` attribute) with clear `PRO` badges and upgrade CTA banner.
  - Floating Widget & REST API tabs: Display disabled UI preview with educational callout and upgrade link.
  - Email tab: CC admin notification field marked with `PRO` badge.
  - Anti-Spam tab: Google reCAPTCHA v3 fully functional; Cloudflare Turnstile & Blacklist shown as Pro preview.
- [ ] **Form Builder (`admin/form-generator.php` & builder JS):**
  - Field Palette: Standard 14 fields fully interactive. Signature, Calculation, and Step-Breaker fields display a `PRO` badge. Clicking opens the **Upgrade Modal**.
  - Layout toolbar: 1-Column active. 2-Col, 3-Col, 4-Col buttons show `PRO` badge and modal on click.
  - Conditional Logic tab: Shows preview illustration with "Unlock Conditional Branching with Pro".
- [ ] **Leads Screen (`admin/leads.php`):**
  - Standard table, search, pagination, and CSV export are 100% active.
  - Kanban toggle and PDF export buttons show `PRO` badge and trigger the upgrade modal.

### Phase 4: Create the Dedicated "Free vs Pro" Admin Page
- [ ] Create `admin/free-vs-pro.php`.
- [ ] Register submenu in `lead-generation-form.php`:
  ```php
  add_submenu_page(
      'lead-generation-form',
      __( 'Free vs Pro', 'lead-generation-form' ),
      __( 'Free vs Pro ★', 'lead-generation-form' ),
      'manage_options',
      'wlgf-free-vs-pro',
      'wlgf_free_vs_pro_page'
  );
  ```
- [ ] Add dedicated styling in `admin/assets/css/` for the modern responsive pricing table, hero callout, demo buttons, and trust badges.
- [ ] Wire up Live Demo (`https://wpfrank.com/demo/lead-generation-form-pro/`) and Upgrade to Pro (`https://wpfrank.com/wordpress-plugins/lead-generation-form-pro/`) buttons.

### Phase 5: Documentation & WordPress.org Packaging
- [ ] Update `readme.txt` with standard WordPress.org format (Tags, Short Description, Tested up to 6.7, Stable tag: 1.3.0, FAQ, Screenshots).
- [ ] Verify zero PHP warnings/errors on PHP 7.4 through 8.3.
- [ ] Perform manual test: Create a form in Free, submit a lead, export CSV, then verify that upgrading to Pro immediately loads the form and lead seamlessly.
