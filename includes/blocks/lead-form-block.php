<?php
/**
 * Lead Generation Form - Native Gutenberg Block Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wlgf_register_gutenberg_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$script_version = defined( 'WLGF_PLUGIN_VERSION' ) ? WLGF_PLUGIN_VERSION : '1.1.8';

	wp_register_script(
		'wlgf-gutenberg-block-js',
		plugins_url( '../../admin/assets/js/gutenberg-block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		$script_version,
		true
	);

	// Query available forms for the block selector with transient caching
	$forms_options = get_transient( 'wlgf_gutenberg_forms_list' );

	if ( false === $forms_options ) {
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
					$forms_options[] = array(
						'label' => sprintf( '%s (ID: #%s)', $form_title, $form_id ),
						'value' => $form_id,
					);
				}
			}
		}

		if ( empty( $forms_options ) ) {
			$forms_options[] = array(
				'label' => __( 'Contact Form (ID: #1)', 'lead-generation-form' ),
				'value' => '1',
			);
		}

		set_transient( 'wlgf_gutenberg_forms_list', $forms_options, 5 * MINUTE_IN_SECONDS );
	}

	wp_localize_script(
		'wlgf-gutenberg-block-js',
		'WLGF_BlockData',
		array(
			'forms' => $forms_options,
		)
	);

	register_block_type(
		'wlgf/lead-form',
		array(
			'editor_script'   => 'wlgf-gutenberg-block-js',
			'render_callback' => 'wlgf_render_gutenberg_block',
			'attributes'      => array(
				'formId' => array(
					'type'    => 'string',
					'default' => '1',
				),
			),
		)
	);
}
add_action( 'init', 'wlgf_register_gutenberg_block' );

function wlgf_render_gutenberg_block( $attributes ) {
	$form_id = ! empty( $attributes['formId'] ) ? sanitize_text_field( $attributes['formId'] ) : '1';
	return do_shortcode( sprintf( '[WLFG id="%s"]', esc_attr( $form_id ) ) );
}
