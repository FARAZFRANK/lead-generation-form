<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package LeadGenerationForm
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// If Pro edition is active or installed, preserve all forms, settings, and leads!
if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
	return;
}
$wlgf_active_plugins = (array) get_option( 'active_plugins', array() );
if ( in_array( 'lead-generation-form-pro/lead-generation-form.php', $wlgf_active_plugins, true ) ) {
	return;
}

global $wpdb;

// Delete all known static options
$wlgf_options = array(
	'wlgf_settings',
	'wlgf_current_version',
	'wlgf_last_version',
	'wlgf_newsletter_dispatch_log',
	'wlgf_email_setting',
	'wlgf_email_leads',
	'wlgf_blacklist',
	'wlgf_analytics',
	'wlgf_form_list',
	'wlgf_forms_count',
	'wlgf_pro_license_status',
	'wlgf_pro_license_key',
);

foreach ( $wlgf_options as $option_name ) {
	delete_option( $option_name );
}

// Delete dynamic options: forms, leads, views, submissions, per-form analytics
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM `$wpdb->options` WHERE `option_name` LIKE %s OR `option_name` LIKE %s OR `option_name` LIKE %s OR `option_name` LIKE %s OR `option_name` LIKE %s",
		$wpdb->esc_like( 'wlgf_form_' ) . '%',
		$wpdb->esc_like( 'wlgf_saved_form_data_' ) . '%',
		$wpdb->esc_like( 'wlgf_form_views_' ) . '%',
		$wpdb->esc_like( 'wlgf_form_submissions_' ) . '%',
		$wpdb->esc_like( 'wlgf_analytics_' ) . '%'
	)
);

// Delete transients
delete_transient( 'wlgf_version_check' );
delete_transient( 'wlgf_gutenberg_forms_list' );
delete_transient( 'wlgf_elementor_forms_list' );

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM `$wpdb->options` WHERE `option_name` LIKE %s OR `option_name` LIKE %s",
		$wpdb->esc_like( '_transient_wlgf_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_wlgf_' ) . '%'
	)
);
