<?php
/**
 * Lead Generation Form - Native Elementor Widget Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register WLGF Category in Elementor
 */
function wlgf_register_elementor_category( $elements_manager ) {
	if ( method_exists( $elements_manager, 'register_category' ) ) {
		$elements_manager->register_category(
			'wlgf-category',
			array(
				'title' => __( 'WLGF / Lead Forms', 'lead-generation-form' ),
				'icon'  => 'fa fa-plug',
			)
		);
	} elseif ( method_exists( $elements_manager, 'add_category' ) ) {
		$elements_manager->add_category(
			'wlgf-category',
			array(
				'title' => __( 'WLGF / Lead Forms', 'lead-generation-form' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}
}
add_action( 'elementor/elements/categories_registered', 'wlgf_register_elementor_category' );

/**
 * Register WLGF Elementor Widget
 */
function wlgf_register_elementor_widget( $widgets_manager ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return;
	}

	class WLGF_Elementor_Widget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'wlgf_lead_form';
		}

		public function get_title() {
			return __( 'Lead Generation Form', 'lead-generation-form' );
		}

		public function get_icon() {
			return 'eicon-form-horizontal';
		}

		public function get_categories() {
			return array( 'wlgf-category', 'general' );
		}

		public function get_keywords() {
			return array( 'lead', 'form', 'contact', 'quote', 'inquiry', 'crm', 'wizard', 'survey' );
		}

		protected function register_controls() {
			// 1. Content Section
			$this->start_controls_section(
				'section_content',
				array(
					'label' => __( 'Form Configuration', 'lead-generation-form' ),
				)
			);

			// Query available forms with transient caching (5 min TTL)
			$forms_options = get_transient( 'wlgf_elementor_forms_list' );
			if ( false === $forms_options || ! is_array( $forms_options ) ) {
				global $wpdb;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wlgf_form_entries = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT option_name, option_value FROM `$wpdb->options` WHERE `option_name` LIKE %s AND `option_name` NOT LIKE %s AND `option_name` NOT LIKE %s ORDER BY option_id ASC",
						$wpdb->esc_like( 'wlgf_form_' ) . '%',
						$wpdb->esc_like( 'wlgf_form_views_' ) . '%',
						$wpdb->esc_like( 'wlgf_form_submissions_' ) . '%'
					),
					ARRAY_A
				);

				$forms_options = array();
				if ( ! empty( $wlgf_form_entries ) ) {
					foreach ( $wlgf_form_entries as $entry ) {
						$form_data = maybe_unserialize( $entry['option_value'] );
						if ( is_array( $form_data ) && isset( $form_data['form_id'] ) ) {
							$form_id    = (string) $form_data['form_id'];
							$form_title = ! empty( $form_data['form_name'] ) ? $form_data['form_name'] : ( 'Form #' . $form_id );
							$forms_options[ $form_id ] = sprintf( '%s (ID: #%s)', $form_title, $form_id );
						}
					}
				}

				if ( empty( $forms_options ) ) {
					$forms_options['1'] = __( 'Default Form (ID: #1)', 'lead-generation-form' );
				}

				set_transient( 'wlgf_elementor_forms_list', $forms_options, 5 * MINUTE_IN_SECONDS );
			}

			$default_id = array_key_first( $forms_options );

			$this->add_control(
				'form_id',
				array(
					'label'   => __( 'Select Form', 'lead-generation-form' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => $default_id ? $default_id : '1',
					'options' => $forms_options,
				)
			);

			$this->add_control(
				'show_title',
				array(
					'label'        => __( 'Display Form Title', 'lead-generation-form' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'lead-generation-form' ),
					'label_off'    => __( 'Hide', 'lead-generation-form' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->end_controls_section();

			// 2. Style Section (Pro Feature - Gated cleanly for byte-identical lockstep)
			if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
				$this->start_controls_section(
					'section_style',
					array(
						'label' => __( 'Form Styling (Pro)', 'lead-generation-form' ),
						'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
					)
				);

				$this->add_control(
					'primary_color',
					array(
						'label'     => __( 'Primary Accent Color', 'lead-generation-form' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'default'   => '',
						'selectors' => array(
							'{{WRAPPER}} .wlgf-shortcode-form-wrap' => '--wlgf-primary: {{VALUE}};',
						),
					)
				);

				$this->add_control(
					'border_radius',
					array(
						'label'      => __( 'Border Radius (px)', 'lead-generation-form' ),
						'type'       => \Elementor\Controls_Manager::SLIDER,
						'size_units' => array( 'px' ),
						'range'      => array(
							'px' => array(
								'min'  => 0,
								'max'  => 30,
								'step' => 1,
							),
						),
						'selectors'  => array(
							'{{WRAPPER}} .wlgf-shortcode-form-wrap' => '--wlgf-radius: {{SIZE}}{{UNIT}};',
						),
					)
				);

				$this->add_control(
					'container_bg',
					array(
						'label'     => __( 'Container Background', 'lead-generation-form' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .wlgf-shortcode-form-wrap' => 'background-color: {{VALUE}};',
						),
					)
				);

				$this->end_controls_section();
			}
		}

		protected function render() {
			$settings   = $this->get_settings_for_display();
			$form_id    = ! empty( $settings['form_id'] ) ? sanitize_text_field( $settings['form_id'] ) : '1';
			$show_title = isset( $settings['show_title'] ) && 'yes' === $settings['show_title'];

			// Check form scheduling / closed state
			$form_option = get_option( 'wlgf_form_' . $form_id );
			if ( is_array( $form_option ) ) {
				$now_timestamp   = current_time( 'timestamp' );
				$schedule_start  = ! empty( $form_option['schedule_start'] ) ? strtotime( $form_option['schedule_start'] ) : false;
				$schedule_end    = ! empty( $form_option['schedule_end'] ) ? strtotime( $form_option['schedule_end'] ) : false;
				$max_submissions = ! empty( $form_option['max_submissions'] ) ? intval( $form_option['max_submissions'] ) : 0;
				$closed_msg      = ! empty( $form_option['closed_message'] ) ? $form_option['closed_message'] : __( 'This form is currently closed for new submissions.', 'lead-generation-form' );

				if ( $schedule_start && $now_timestamp < $schedule_start ) {
					$start_date_formatted = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $schedule_start );
					/* translators: %s: form scheduled opening date/time */
					echo '<div class="wlgf-container"><div class="wlgf-alert wlgf-alert--warning"><i class="fa-solid fa-clock"></i> ' . sprintf( esc_html__( 'This form will open for submissions on %s.', 'lead-generation-form' ), esc_html( $start_date_formatted ) ) . '</div></div>';
					return;
				}

				if ( $schedule_end && $now_timestamp > $schedule_end ) {
					echo '<div class="wlgf-container"><div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $closed_msg ) . '</div></div>';
					return;
				}

				if ( $max_submissions > 0 ) {
					$existing_leads = get_option( 'wlgf_saved_form_data_' . $form_id, array() );
					if ( is_array( $existing_leads ) && count( $existing_leads ) >= $max_submissions ) {
						echo '<div class="wlgf-container"><div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-ban"></i> ' . esc_html( $closed_msg ) . '</div></div>';
						return;
					}
				}
			}

			echo do_shortcode( sprintf( '[WLFG id="%s"]', esc_attr( $form_id ) ) );
		}
	}

	$widgets_manager->register( new WLGF_Elementor_Widget() );
}
add_action( 'elementor/widgets/register', 'wlgf_register_elementor_widget' );
