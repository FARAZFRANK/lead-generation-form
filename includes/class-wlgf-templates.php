<?php
/**
 * Lead Generation Form - 1-Click Starter Template Library
 * Provides pre-built high-converting starter form templates for Free edition.
 * Version: 1.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WLGF_Templates {

	public function __construct() {
		add_action( 'wp_ajax_wlgf_get_template', array( $this, 'ajax_get_template' ) );
	}

	/**
	 * Return list of all available templates with meta
	 */
	public static function get_all_templates() {
		return array(
			'contact_us' => array(
				'id'            => 'contact_us',
				'title'         => __( 'Classic Contact Form', 'lead-generation-form' ),
				'description'   => __( 'Clean contact form with Name, Email, Subject, Message, and submit button.', 'lead-generation-form' ),
				'badge'         => __( 'Essential', 'lead-generation-form' ),
				'icon'          => 'fa-envelope',
				'primary_color' => '#2563eb',
			),
			'simple_lead' => array(
				'id'            => 'simple_lead',
				'title'         => __( 'Simple Lead Capture Form', 'lead-generation-form' ),
				'description'   => __( 'High-converting lead generation form for capturing prospective client inquiries.', 'lead-generation-form' ),
				'badge'         => __( 'Lead Gen', 'lead-generation-form' ),
				'icon'          => 'fa-user-plus',
				'primary_color' => '#0284c7',
			),
			'quote_request' => array(
				'id'            => 'quote_request',
				'title'         => __( 'Service Quote Request', 'lead-generation-form' ),
				'description'   => __( 'Structured inquiry form allowing clients to specify service needs, timeline, and project details.', 'lead-generation-form' ),
				'badge'         => __( 'Business', 'lead-generation-form' ),
				'icon'          => 'fa-file-invoice-dollar',
				'primary_color' => '#0d9488',
			),
			'customer_feedback' => array(
				'id'            => 'customer_feedback',
				'title'         => __( 'Customer Feedback Form', 'lead-generation-form' ),
				'description'   => __( 'Collect valuable client feedback, experience ratings, and suggestions.', 'lead-generation-form' ),
				'badge'         => __( 'Feedback', 'lead-generation-form' ),
				'icon'          => 'fa-comment-dots',
				'primary_color' => '#8b5cf6',
			),
			'newsletter_signup' => array(
				'id'            => 'newsletter_signup',
				'title'         => __( 'Newsletter Subscription', 'lead-generation-form' ),
				'description'   => __( 'Quick subscriber sign-up form with privacy consent for building your email audience.', 'lead-generation-form' ),
				'badge'         => __( 'Marketing', 'lead-generation-form' ),
				'icon'          => 'fa-paper-plane',
				'primary_color' => '#16a34a',
			),
		);
	}

	/**
	 * Get full template schema and formBuilder JSON
	 */
	public static function get_template_data( $template_id ) {
		switch ( $template_id ) {
			case 'contact_us':
				return array(
					'form_name'     => 'Classic Contact Form',
					'theme_primary' => '#2563eb',
					'theme_radius'  => '8px',
					'theme_mode'    => 'light',
					'display_mode'  => 'inline',
					'fields'        => array(
						array( 'type' => 'header', 'subtype' => 'h3', 'label' => 'Get in Touch with Us' ),
						array( 'type' => 'text', 'subtype' => 'text', 'label' => 'Full Name', 'name' => 'full_name', 'required' => 1, 'placeholder' => 'Jane Doe' ),
						array( 'type' => 'text', 'subtype' => 'email', 'label' => 'Email Address', 'name' => 'email', 'required' => 1, 'placeholder' => 'jane@example.com' ),
						array( 'type' => 'text', 'subtype' => 'tel', 'label' => 'Phone Number', 'name' => 'phone', 'placeholder' => '+1 (555) 000-0000' ),
						array( 'type' => 'text', 'subtype' => 'text', 'label' => 'Subject', 'name' => 'subject', 'placeholder' => 'How can we help you?' ),
						array( 'type' => 'textarea', 'label' => 'Your Message', 'name' => 'message', 'required' => 1, 'placeholder' => 'Please share your inquiry details...' ),
						array( 'type' => 'button', 'subtype' => 'submit', 'label' => 'Send Message', 'className' => 'btn-primary' ),
					),
				);

			case 'simple_lead':
				return array(
					'form_name'     => 'Simple Lead Capture Form',
					'theme_primary' => '#0284c7',
					'theme_radius'  => '8px',
					'theme_mode'    => 'light',
					'display_mode'  => 'inline',
					'fields'        => array(
						array( 'type' => 'header', 'subtype' => 'h3', 'label' => 'Request a Consultation' ),
						array( 'type' => 'text', 'subtype' => 'text', 'label' => 'Full Name', 'name' => 'full_name', 'required' => 1, 'placeholder' => 'John Smith' ),
						array( 'type' => 'text', 'subtype' => 'email', 'label' => 'Work Email', 'name' => 'email', 'required' => 1, 'placeholder' => 'john@company.com' ),
						array( 'type' => 'text', 'subtype' => 'tel', 'label' => 'Phone Number', 'name' => 'phone', 'required' => 1, 'placeholder' => '+1 (555) 123-4567' ),
						array( 'type' => 'text', 'subtype' => 'text', 'label' => 'Company / Organization', 'name' => 'company_name', 'placeholder' => 'Acme Inc.' ),
						array( 'type' => 'button', 'subtype' => 'submit', 'label' => 'Request Free Consultation', 'className' => 'btn-primary' ),
					),
				);

			case 'quote_request':
				return array(
					'form_name'     => 'Service Quote Request',
					'theme_primary' => '#0d9488',
					'theme_radius'  => '8px',
					'theme_mode'    => 'light',
					'display_mode'  => 'inline',
					'fields'        => array(
						array( 'type' => 'header', 'subtype' => 'h3', 'label' => 'Request a Project Quote' ),
						array( 'type' => 'text', 'subtype' => 'text', 'label' => 'Your Name', 'name' => 'client_name', 'required' => 1 ),
						array( 'type' => 'text', 'subtype' => 'email', 'label' => 'Email Address', 'name' => 'email', 'required' => 1 ),
						array( 'type' => 'text', 'subtype' => 'tel', 'label' => 'Contact Number', 'name' => 'phone', 'required' => 1 ),
						array(
							'type'    => 'select',
							'label'   => 'Service of Interest',
							'name'    => 'service_type',
							'values'  => array(
								array( 'label' => 'Website Development & Design', 'value' => 'web_design', 'selected' => true ),
								array( 'label' => 'Search Engine Optimization (SEO)', 'value' => 'seo' ),
								array( 'label' => 'Brand Strategy & Marketing', 'value' => 'marketing' ),
								array( 'label' => 'Ongoing Maintenance & Support', 'value' => 'maintenance' ),
							),
						),
						array(
							'type'    => 'select',
							'label'   => 'Estimated Budget Range',
							'name'    => 'budget_range',
							'values'  => array(
								array( 'label' => 'Under $1,000', 'value' => 'under_1k', 'selected' => true ),
								array( 'label' => '$1,000 - $3,000', 'value' => '1k_3k' ),
								array( 'label' => '$3,000 - $5,000', 'value' => '3k_5k' ),
								array( 'label' => '$5,000+', 'value' => '5k_plus' ),
							),
						),
						array( 'type' => 'textarea', 'label' => 'Project Overview & Goals', 'name' => 'project_details', 'required' => 1 ),
						array( 'type' => 'button', 'subtype' => 'submit', 'label' => 'Submit Quote Request', 'className' => 'btn-primary' ),
					),
				);

			case 'customer_feedback':
				return array(
					'form_name'     => 'Customer Feedback Form',
					'theme_primary' => '#8b5cf6',
					'theme_radius'  => '8px',
					'theme_mode'    => 'light',
					'display_mode'  => 'inline',
					'fields'        => array(
						array( 'type' => 'header', 'subtype' => 'h3', 'label' => 'Tell Us About Your Experience' ),
						array( 'type' => 'text', 'subtype' => 'text', 'label' => 'Your Name', 'name' => 'customer_name' ),
						array( 'type' => 'text', 'subtype' => 'email', 'label' => 'Your Email', 'name' => 'email' ),
						array(
							'type'    => 'radio-group',
							'label'   => 'How satisfied are you with our service?',
							'name'    => 'satisfaction_rating',
							'values'  => array(
								array( 'label' => 'Extremely Satisfied', 'value' => 'very_satisfied', 'selected' => true ),
								array( 'label' => 'Satisfied', 'value' => 'satisfied' ),
								array( 'label' => 'Neutral', 'value' => 'neutral' ),
								array( 'label' => 'Needs Improvement', 'value' => 'needs_improvement' ),
							),
						),
						array( 'type' => 'textarea', 'label' => 'What did you like best or what can we improve?', 'name' => 'feedback_notes', 'required' => 1 ),
						array( 'type' => 'button', 'subtype' => 'submit', 'label' => 'Send Feedback', 'className' => 'btn-primary' ),
					),
				);

			case 'newsletter_signup':
				return array(
					'form_name'     => 'Newsletter Subscription',
					'theme_primary' => '#16a34a',
					'theme_radius'  => '8px',
					'theme_mode'    => 'light',
					'display_mode'  => 'inline',
					'fields'        => array(
						array( 'type' => 'header', 'subtype' => 'h3', 'label' => 'Stay Updated With Our Newsletter' ),
						array( 'type' => 'text', 'subtype' => 'text', 'label' => 'First Name', 'name' => 'first_name', 'required' => 1, 'placeholder' => 'Alex' ),
						array( 'type' => 'text', 'subtype' => 'email', 'label' => 'Email Address', 'name' => 'email', 'required' => 1, 'placeholder' => 'alex@example.com' ),
						array(
							'type'    => 'checkbox-group',
							'label'   => 'Preferences',
							'name'    => 'preferences',
							'values'  => array(
								array( 'label' => 'Weekly Product Tips & Articles', 'value' => 'weekly_tips', 'selected' => true ),
								array( 'label' => 'Special Promotions & Discounts', 'value' => 'promotions' ),
							),
						),
						array( 'type' => 'button', 'subtype' => 'submit', 'label' => 'Subscribe Now', 'className' => 'btn-success' ),
					),
				);

			default:
				return false;
		}
	}

	/**
	 * AJAX endpoint to fetch template data
	 */
	public function ajax_get_template() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'lead-generation-form' ) ) );
		}

		check_ajax_referer( 'wlgf-formbuilder-nonce', 'nonce' );

		$template_id = isset( $_POST['template_id'] ) ? sanitize_key( $_POST['template_id'] ) : '';
		$template_data = self::get_template_data( $template_id );

		if ( ! $template_data ) {
			wp_send_json_error( array( 'message' => __( 'Template not found.', 'lead-generation-form' ) ) );
		}

		wp_send_json_success( $template_data );
	}
}

new WLGF_Templates();
