<?php
/**
 * Lead Generation Form - Free vs Pro Comparison Page
 *
 * @package LeadGenerationForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wlgf_upgrade_url = 'https://wpfrank.com/wordpress-plugins/lead-generation-form-pro/';
$wlgf_demo_url    = 'https://wpfrank.com/demo/lead-generation-form-pro/';
$wlgf_is_tab_embed = ! empty( $is_wlgf_tab_embed );
?>

<?php if ( ! $wlgf_is_tab_embed ) : ?>
<div class="wlgf-admin-wrap">
	<!-- Page Header -->
	<header class="wlgf-header">
		<div class="wlgf-header__main">
			<div class="wlgf-header__icon" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff;">
				<i class="fa-solid fa-crown"></i>
			</div>
			<div class="wlgf-header__titles">
				<h1 class="wlgf-header__title"><?php esc_html_e( 'Free vs. Pro Comparison', 'lead-generation-form' ); ?></h1>
				<p class="wlgf-header__subtitle"><?php esc_html_e( 'Discover how upgrading to Lead Generation Form Pro unlocks enterprise lead recovery, WhatsApp contact hubs, e-signatures, and instant payments.', 'lead-generation-form' ); ?></p>
			</div>
		</div>
		<div class="wlgf-header__actions">
			<a href="<?php echo esc_url( $wlgf_demo_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--secondary">
				<i class="fa-solid fa-arrow-up-right-from-square"></i> <?php esc_html_e( 'Live Demo', 'lead-generation-form' ); ?>
			</a>
			<a href="<?php echo esc_url( $wlgf_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--primary" style="background: #f59e0b; border-color: #d97706; color: #1e1b4b; font-weight: 700;">
				<i class="fa-solid fa-rocket"></i> <?php esc_html_e( 'Upgrade to Pro — $49', 'lead-generation-form' ); ?>
			</a>
		</div>
	</header>
<?php endif; ?>

	<!-- Hero Callout Banner -->
	<div class="wlgf-upgrade-hero" style="margin-top: 20px;">
		<span class="wlgf-upgrade-hero__badge">
			<i class="fa-solid fa-bolt"></i> <?php esc_html_e( 'One-Time Payment • Zero Monthly Fees', 'lead-generation-form' ); ?>
		</span>
		<h2 class="wlgf-upgrade-hero__title">
			<?php esc_html_e( 'Stop Losing 70% of Your Website Visitors. Turn Every Click into a Qualified Lead.', 'lead-generation-form' ); ?>
		</h2>
		<p class="wlgf-upgrade-hero__desc">
			<?php esc_html_e( 'Most WordPress forms sit quietly on contact pages while 97% of visitors browse and leave. Pro gives you 1-tap WhatsApp chat, exit-intent popups, abandoned lead auto-capture, digital signature pads, and visual Kanban sales pipelines — all inside your WordPress dashboard.', 'lead-generation-form' ); ?>
		</p>

		<!-- Callout Action Buttons -->
		<div style="display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; align-items: center; margin-bottom: 24px;">
			<a href="<?php echo esc_url( $wlgf_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-upgrade-hero__cta" style="font-size: 16px; padding: 14px 32px;">
				<i class="fa-solid fa-crown"></i> <?php esc_html_e( 'Get Lead Generation Form Pro — $49 (One-Time)', 'lead-generation-form' ); ?>
			</a>
			<a href="<?php echo esc_url( $wlgf_demo_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-btn" style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3); font-weight: 700; padding: 14px 24px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
				<i class="fa-solid fa-laptop"></i> <?php esc_html_e( 'Explore Live Interactive Demo', 'lead-generation-form' ); ?>
			</a>
		</div>

		<!-- Trust Badges -->
		<div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; font-size: 13px; color: #cbd5e1;">
			<span><i class="fa-solid fa-check text-success" style="color:#4ade80;"></i> <?php esc_html_e( 'One-Time Payment ($49)', 'lead-generation-form' ); ?></span>
			<span><i class="fa-solid fa-check text-success" style="color:#4ade80;"></i> <?php esc_html_e( 'Lifetime Free Updates', 'lead-generation-form' ); ?></span>
			<span><i class="fa-solid fa-check text-success" style="color:#4ade80;"></i> <?php esc_html_e( '1 Year Priority Direct Support', 'lead-generation-form' ); ?></span>
			<span><i class="fa-solid fa-check text-success" style="color:#4ade80;"></i> <?php esc_html_e( '100% GDPR Compliant (Zero SaaS tracking)', 'lead-generation-form' ); ?></span>
		</div>
	</div>

	<!-- Full 33-Row Feature Comparison Table -->
	<div class="wlgf-comparison-table-wrapper">
		<table class="wlgf-comparison-table">
			<thead>
				<tr>
					<th class="wlgf-col-feature"><?php esc_html_e( 'Feature / Capability', 'lead-generation-form' ); ?></th>
					<th class="wlgf-col-free"><?php esc_html_e( 'Free Edition', 'lead-generation-form' ); ?></th>
					<th class="wlgf-col-pro" style="background: #fffbeb; color: #92400e; border-left: 2px solid #f59e0b; border-right: 2px solid #f59e0b;">
						<span style="display: flex; align-items: center; justify-content: center; gap: 6px;">
							<i class="fa-solid fa-crown" style="color: #f59e0b;"></i> <?php esc_html_e( 'Pro Edition ($49 One-Time)', 'lead-generation-form' ); ?>
						</span>
					</th>
				</tr>
			</thead>
			<tbody>
				<!-- Section: Pricing & Licensing -->
				<tr style="background: #f8fafc;">
					<td colspan="3" style="font-weight: 700; color: #334155; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; padding: 10px 20px;">
						<i class="fa-solid fa-tag text-primary"></i> <?php esc_html_e( 'Pricing & Licensing', 'lead-generation-form' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Pricing & License Model', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'License terms and renewal cost.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><span class="wlgf-pill-limit"><?php esc_html_e( '$0 (WordPress.org)', 'lead-generation-form' ); ?></span></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<span class="wlgf-pill-unlimited"><i class="fa-solid fa-crown"></i> <?php esc_html_e( '$49 (One-Time, Lifetime Updates)', 'lead-generation-form' ); ?></span>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Website Usage', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Permitted domain usage per license.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><?php esc_html_e( '1 Website', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;"><strong><?php esc_html_e( '1 Website (Upgrades Available)', 'lead-generation-form' ); ?></strong></td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Customer Support Level', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Direct assistance and turnaround SLA.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><?php esc_html_e( 'Community Forum', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( '1 Year Priority Direct Support', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>

				<!-- Section: Form Builder & Layouts -->
				<tr style="background: #f8fafc;">
					<td colspan="3" style="font-weight: 700; color: #334155; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; padding: 10px 20px;">
						<i class="fa-solid fa-palette text-primary"></i> <?php esc_html_e( 'Form Builder & Column Layouts', 'lead-generation-form' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Visual Drag-and-Drop Builder', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Intuitive layout builder with real-time field reordering.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <?php esc_html_e( 'Yes', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Enhanced UI)', 'lead-generation-form' ); ?></strong></td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Unlimited Forms & Fields', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Create as many lead forms and questions as your site needs.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <?php esc_html_e( 'Yes (Unlimited)', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Unlimited)', 'lead-generation-form' ); ?></strong></td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( '1-Click Multi-Column Switcher (1/2, 1/3, 1/4)', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Build compact, professional side-by-side field grids without CSS code.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><span class="wlgf-pill-limit"><?php esc_html_e( 'Single Column Only', 'lead-generation-form' ); ?></span></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Instant 100%, 50%, 33%, 25%)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Pre-Built Industry Templates Marketplace', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Ready-to-deploy lead forms optimized for conversion.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><span class="wlgf-pill-limit"><?php esc_html_e( '5 Basic Forms', 'lead-generation-form' ); ?></span></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( '25+ Enterprise Niche Templates', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Multi-Step Form Wizard & Progress Bar', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Break complex forms into interactive step cards (Circles, Bars, Breadcrumbs).', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Circles, Bar & Breadcrumbs)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Per-Step Client-Side Validation', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Validates required inputs before allowing users to advance to the next step.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>

				<!-- Section: High-Converting Lead Capture & Sales -->
				<tr style="background: #f8fafc;">
					<td colspan="3" style="font-weight: 700; color: #334155; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; padding: 10px 20px;">
						<i class="fa-solid fa-bullhorn text-primary"></i> <?php esc_html_e( 'Traffic Capture, CRO & Sales Acceleration', 'lead-generation-form' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Floating Multi-Channel Contact Launcher', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Pulsing corner contact button with 1-tap WhatsApp chat & phone calling.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (7 Positions, WhatsApp, Call)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Exit-Intent & Scroll Depth Popups', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Capture abandoning visitors right as their mouse moves toward the exit button.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Exit, Timed, Scroll Depth)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Real-Time Abandoned Lead Auto-Capture', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Silently captures email and phone the moment typed, even if they never hit submit.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Instant Auto-Save on Blur)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Mini-CRM Kanban Sales Pipeline', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Track prospects across 6 visual draggable stages from New to Converted.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><span class="wlgf-pill-limit"><?php esc_html_e( 'Table View Only', 'lead-generation-form' ); ?></span></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (6 Draggable Sales Stages)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( '1-Click WhatsApp & Phone from Lead Cards', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Launch instant sales outreach directly from lead records.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Instant Outreach)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>

				<!-- Section: E-Signatures, Calculations & Advanced Controls -->
				<tr style="background: #f8fafc;">
					<td colspan="3" style="font-weight: 700; color: #334155; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; padding: 10px 20px;">
						<i class="fa-solid fa-signature text-primary"></i> <?php esc_html_e( 'E-Signatures, Dynamic Math & Conditional Logic', 'lead-generation-form' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'HTML5 Canvas Digital E-Signature Pad', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'High-DPI touch, mouse, and stylus signature capture for waivers and contracts.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Touch, Mouse & Stylus)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Pure-PHP Vector PDF Invoices & Receipts', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Automated PDF generation with legal verification certificate and signature box.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (With Legal Signature Box)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Real-Time Dynamic Math & Price Estimator', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Zero-eval secure formula engine for price quotes, loan rates, and calculators.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Zero-eval Secure Parser)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Visual Conditional Logic (AND / OR)', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Show or hide fields dynamically based on user selections.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Multi-Rule Branching)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>

				<!-- Section: Payments & Checkout -->
				<tr style="background: #f8fafc;">
					<td colspan="3" style="font-weight: 700; color: #334155; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; padding: 10px 20px;">
						<i class="fa-solid fa-credit-card text-primary"></i> <?php esc_html_e( 'Online Payments & Checkout', 'lead-generation-form' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Stripe Elements Live Payments (SCA 3D Secure)', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Accept Credit Cards, Apple Pay, and Google Pay directly inside forms.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Credit Card, Apple/Google Pay)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'PayPal Smart Buttons Checkout', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Accept PayPal balance, Pay in 4, and major debit/credit cards.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (PayPal, Pay in 4)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>

				<!-- Section: Security & Anti-Spam -->
				<tr style="background: #f8fafc;">
					<td colspan="3" style="font-weight: 700; color: #334155; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; padding: 10px 20px;">
						<i class="fa-solid fa-shield-halved text-primary"></i> <?php esc_html_e( 'Enterprise Anti-Abuse & Security Suite', 'lead-generation-form' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Server-Side Automated IP Rate Limiting', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Strict limit of 5 submissions per 120s per IP to halt brute-force bot flood.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (5 submissions / 120s limit)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Smart Decoy Honeypot Trap', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Invisible bot field that silently discards automated spam without human friction.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <?php esc_html_e( 'Basic Honeypot', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Stealth Silent Drop)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Exploit & SQLi Heuristic Fuzzing Filter', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Detects and drops malicious SQL injection and attack payloads automatically.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Active Attack Drop)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Cloudflare Turnstile Bot Prevention', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Modern privacy-first, frictionless verification without image puzzles.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Native Turnstile)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Google reCAPTCHA v3', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Invisible score-based bot defense.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <?php esc_html_e( 'Basic reCAPTCHA v3', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (With Admin Verification Logs)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>

				<!-- Section: Email Dispatch & Notifications -->
				<tr style="background: #f8fafc;">
					<td colspan="3" style="font-weight: 700; color: #334155; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; padding: 10px 20px;">
						<i class="fa-solid fa-envelope text-primary"></i> <?php esc_html_e( 'Email Delivery & Notifications', 'lead-generation-form' ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Custom Authenticated SMTP Engine', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Bypass flaky hosting wp_mail with custom SMTP (Gmail, Outlook, Host).', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <?php esc_html_e( 'Yes', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (DMARC-Safe Header Engine)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Branded Responsive HTML Auto-Responder', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Send polished, modern branded email receipts with logo and custom colors.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><span class="wlgf-pill-limit"><?php esc_html_e( 'Plain Text', 'lead-generation-form' ); ?></span></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Branded HTML Template)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Dynamic Email Merge Tags ({Name}, {Form})', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Personalize notification subject lines and greetings automatically.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Full Token Resolver)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Multi-Admin CC Notification Routing', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Deliver copies of new leads to multiple team emails or via shortcode.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Global + Shortcode CC)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Excel & CSV Lead Data Export', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Export captured leads with clean column formatting.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <?php esc_html_e( 'Yes', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (With Filtered Views)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Headless REST API & Webhooks', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Connect forms to Next.js, mobile applications, Zapier, and custom CRMs.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><i class="fa-solid fa-circle-xmark wlgf-icon-cross"></i> <?php esc_html_e( 'No', 'lead-generation-form' ); ?></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Full JSON REST Suite)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong class="wlgf-feature-title"><?php esc_html_e( 'Drop-off Funnel Diagnostics & Analytics', 'lead-generation-form' ); ?></strong>
						<span class="wlgf-feature-desc"><?php esc_html_e( 'Visualize conversion drop-offs and device performance in native charts.', 'lead-generation-form' ); ?></span>
					</td>
					<td class="wlgf-cell-free"><span class="wlgf-pill-limit"><?php esc_html_e( 'Basic Counts', 'lead-generation-form' ); ?></span></td>
					<td class="wlgf-cell-pro" style="border-left: 2px solid #fef3c7; border-right: 2px solid #fef3c7;">
						<i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Yes (Chart.js Funnels)', 'lead-generation-form' ); ?></strong>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- SaaS Replacement & Cost Savings Card -->
	<div class="wlgf-card" style="margin-bottom: 24px;">
		<div class="wlgf-card__header">
			<h2 class="wlgf-card__title">
				<i class="fa-solid fa-piggy-bank text-primary"></i> <?php esc_html_e( 'Replace $1,500+/Year in Fragmented SaaS Subscriptions', 'lead-generation-form' ); ?>
			</h2>
			<span class="wlgf-badge wlgf-badge--success"><?php esc_html_e( 'Save $1,479 in Year 1', 'lead-generation-form' ); ?></span>
		</div>
		<div class="wlgf-card__body">
			<p class="wlgf-help-text" style="margin-bottom: 16px;">
				<?php esc_html_e( 'Why pay monthly recurring subscription fees to 5 different software companies when Lead Generation Form Pro includes everything inside your WordPress site for a single one-time payment?', 'lead-generation-form' ); ?>
			</p>

			<div style="overflow-x: auto;">
				<table class="wlgf-comparison-table" style="box-shadow: none; border-radius: 8px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Independent SaaS Tool', 'lead-generation-form' ); ?></th>
							<th><?php esc_html_e( 'What It Does', 'lead-generation-form' ); ?></th>
							<th><?php esc_html_e( 'Typical Cost / Year', 'lead-generation-form' ); ?></th>
							<th style="background: #fffbeb; color: #92400e;"><?php esc_html_e( 'Included in Lead Generation Form Pro?', 'lead-generation-form' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong>OptinMonster / Hustle Pro</strong></td>
							<td><?php esc_html_e( 'Exit-Intent Popups & Floating Bars', 'lead-generation-form' ); ?></td>
							<td><span style="color: #ef4444; font-weight: 700;">$350 / year</span></td>
							<td style="background: #fffbeb;"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Included ($0 recurring)', 'lead-generation-form' ); ?></strong></td>
						</tr>
						<tr>
							<td><strong>SiteLeads / Boei</strong></td>
							<td><?php esc_html_e( 'Floating WhatsApp & Call Launcher', 'lead-generation-form' ); ?></td>
							<td><span style="color: #ef4444; font-weight: 700;">$190 / year</span></td>
							<td style="background: #fffbeb;"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Included ($0 recurring)', 'lead-generation-form' ); ?></strong></td>
						</tr>
						<tr>
							<td><strong>WPForms Pro (Abandonment Add-on)</strong></td>
							<td><?php esc_html_e( 'Abandoned Lead Auto-Recovery', 'lead-generation-form' ); ?></td>
							<td><span style="color: #ef4444; font-weight: 700;">$399 / year</span></td>
							<td style="background: #fffbeb;"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Included ($0 recurring)', 'lead-generation-form' ); ?></strong></td>
						</tr>
						<tr>
							<td><strong>Forminator Pro / Signature Add-ons</strong></td>
							<td><?php esc_html_e( 'Digital E-Signatures & PDF Invoices', 'lead-generation-form' ); ?></td>
							<td><span style="color: #ef4444; font-weight: 700;">$90 / year</span></td>
							<td style="background: #fffbeb;"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Included ($0 recurring)', 'lead-generation-form' ); ?></strong></td>
						</tr>
						<tr>
							<td><strong>HubSpot Starter CRM</strong></td>
							<td><?php esc_html_e( 'Visual Kanban Pipeline & Lead Tracking', 'lead-generation-form' ); ?></td>
							<td><span style="color: #ef4444; font-weight: 700;">$240 / year</span></td>
							<td style="background: #fffbeb;"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Included ($0 recurring)', 'lead-generation-form' ); ?></strong></td>
						</tr>
						<tr>
							<td><strong>Gravity Forms + Calculations & Stripe</strong></td>
							<td><?php esc_html_e( 'Cost Estimators & Online Payments', 'lead-generation-form' ); ?></td>
							<td><span style="color: #ef4444; font-weight: 700;">$259 / year</span></td>
							<td style="background: #fffbeb;"><i class="fa-solid fa-circle-check wlgf-icon-check"></i> <strong><?php esc_html_e( 'Included ($0 recurring)', 'lead-generation-form' ); ?></strong></td>
						</tr>
						<tr style="background: #f1f5f9; font-weight: 800; font-size: 15px;">
							<td colspan="2"><strong><?php esc_html_e( 'TOTAL ANNUAL SOFTWARE COST', 'lead-generation-form' ); ?></strong></td>
							<td><span style="color: #dc2626; text-decoration: line-through;">$1,528 / year</span></td>
							<td style="background: #fef08a; color: #1e1b4b;"><i class="fa-solid fa-crown" style="color: #f59e0b;"></i> <?php esc_html_e( 'Only $49 One-Time Payment', 'lead-generation-form' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Bottom Sticky-Style CTA Section -->
	<div class="wlgf-card" style="text-align: center; padding: 40px 24px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
		<span class="wlgf-badge wlgf-badge--warning" style="margin-bottom: 12px; font-weight: 700;">
			<i class="fa-solid fa-rocket"></i> <?php esc_html_e( 'Real Form Builder All You Need', 'lead-generation-form' ); ?>
		</span>
		<h2 style="color: #ffffff; font-size: 24px; font-weight: 800; margin: 0 0 10px;">
			<?php esc_html_e( 'Ready to Triple Your Website Lead Conversion Rates?', 'lead-generation-form' ); ?>
		</h2>
		<p style="color: #94a3b8; max-width: 620px; margin: 0 auto 24px; line-height: 1.6; font-size: 15px;">
			<?php esc_html_e( 'Join smart business owners and marketing agencies who have eliminated expensive monthly subscriptions. Instant delivery, lifetime updates, and priority support.', 'lead-generation-form' ); ?>
		</p>
		<div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
			<a href="<?php echo esc_url( $wlgf_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--primary" style="background: #f59e0b; border-color: #d97706; color: #1e1b4b; font-weight: 800; font-size: 16px; padding: 14px 32px; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);">
				<i class="fa-solid fa-rocket"></i> <?php esc_html_e( 'Get Lead Generation Form Pro — $49 (One-Time)', 'lead-generation-form' ); ?>
			</a>
			<a href="<?php echo esc_url( $wlgf_demo_url ); ?>" target="_blank" rel="noopener noreferrer" class="wlgf-btn wlgf-btn--secondary" style="font-size: 16px; padding: 14px 24px;">
				<i class="fa-solid fa-arrow-up-right-from-square"></i> <?php esc_html_e( 'Explore Live Demo', 'lead-generation-form' ); ?>
			</a>
		</div>
	</div>
<?php if ( ! $wlgf_is_tab_embed ) : ?>
</div>
<?php endif; ?>
