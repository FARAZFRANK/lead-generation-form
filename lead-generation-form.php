<?php
if (!defined('ABSPATH')) {
	exit; /* Exit if accessed directly */
}

/**
 * Plugin Name:       Lead Generation Form
 * Plugin URI:        https://wpfrank.com/wordpress-plugins/lead-generation-form-pro/
 * Description:       Clean, responsive drag-and-drop lead capture and contact form builder for WordPress with anti-spam, custom SMTP delivery, and CSV export.
 * Version:           1.3.1
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            WP Frank
 * Author URI:        https://wpfrank.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lead-generation-form
 * Domain Path:       /languages
 */

/*
Lead Generation Form is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Lead Generation Form is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Lead Generation Form. If not, see <http://www.gnu.org/licenses/>.
*/

// If Pro edition is already loaded and active, yield gracefully to prevent duplicate hooks/classes
if (defined('WLGF_IS_PRO') && WLGF_IS_PRO) {
	return;
}

// Set WLGF_IS_PRO flag for Lead Generation Form (Free / Lite Edition)
if (!defined('WLGF_IS_PRO')) {
	define('WLGF_IS_PRO', false);
}

// Global Plugin Version Constant
if (!defined('WLGF_PLUGIN_VERSION')) {
	define('WLGF_PLUGIN_VERSION', '1.3.1');
}

/* activation */
if (!function_exists('wlgf_activation')) {
	function wlgf_activation()
	{
		/* update current plugin version */
		if (is_admin()) {
			// Auto-deactivate Free edition if active to prevent collisions (handles both WP.org and dev slugs)
			$wlgf_active_plugins = (array) get_option('active_plugins', array());
			$wlgf_free_slugs = array(
				'lead-generation-form-free/lead-generation-form.php',
				'lead-generation-form/lead-generation-form.php',
			);
			foreach ($wlgf_free_slugs as $free_slug) {
				if (in_array($free_slug, $wlgf_active_plugins, true)) {
					if (!function_exists('deactivate_plugins')) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					deactivate_plugins($free_slug);
				}
			}

			// Clean up stale v1.0.9 register_uninstall_hook entry if present
			$uninstall_plugins = get_option('uninstall_plugins');
			if (is_array($uninstall_plugins) && isset($uninstall_plugins[plugin_basename(__FILE__)])) {
				unset($uninstall_plugins[plugin_basename(__FILE__)]);
				update_option('uninstall_plugins', $uninstall_plugins);
			}

			if (!function_exists('get_plugin_data')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$wlgf_plugin_data = get_plugin_data(__FILE__);
			if (isset($wlgf_plugin_data['Version'])) {
				$wlgf_plugin_version = $wlgf_plugin_data['Version'];
				update_option('wlgf_current_version', $wlgf_plugin_version);
			}

			//install-script
			require_once 'includes/install-script.php';
		}
	}
}
register_activation_hook(__FILE__, 'wlgf_activation');

/* legacy uninstall safety fallback to prevent fatal errors on upgrades from v1.0.9 */
if (!function_exists('wlgf_uninstall')) {
	function wlgf_uninstall()
	{
		if (file_exists(plugin_dir_path(__FILE__) . 'uninstall.php')) {
			include_once plugin_dir_path(__FILE__) . 'uninstall.php';
		}
	}
}

/* de-activation */
if (!function_exists('wlgf_deactivation')) {
	function wlgf_deactivation()
	{
		// update last active plugin version
		$wlgf_last_version = get_option('wlgf_current_version');
		if ($wlgf_last_version !== "") {
			update_option('wlgf_last_version', $wlgf_last_version);
		}
	}
}
register_deactivation_hook(__FILE__, 'wlgf_deactivation');



if (!function_exists('wlgf_check_version')) {
	function wlgf_check_version()
	{
		$plugin_version = defined('WLGF_PLUGIN_VERSION') ? WLGF_PLUGIN_VERSION : '1.3.1';
		$current_db_version = sanitize_text_field(get_option('wlgf_current_version', '1.0'));

		if (version_compare($current_db_version, $plugin_version, '<')) {
			update_option('wlgf_last_version', $current_db_version);
			update_option('wlgf_current_version', $plugin_version);
			delete_transient('wlgf_version_check');

			// Seed default automated shields if missing on upgrade
			$existing_settings = get_option('wlgf_settings');
			if (is_array($existing_settings)) {
				$needs_update = false;
				if (!isset($existing_settings['submission_rate_limit'])) {
					$existing_settings['submission_rate_limit'] = '1';
					$needs_update = true;
				}
				if (!isset($existing_settings['honeypot_status'])) {
					$existing_settings['honeypot_status'] = '1';
					$needs_update = true;
				}
				if (!isset($existing_settings['scanner_shield_status'])) {
					$existing_settings['scanner_shield_status'] = '1';
					$needs_update = true;
				}
				if ($needs_update) {
					update_option('wlgf_settings', $existing_settings);
				}
			}
		}
	}
}
add_action('admin_init', 'wlgf_check_version');

// load translation
function wlgf_load_translation()
{
	load_plugin_textdomain('lead-generation-form', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'wlgf_load_translation');

// register styles and scripts for frontend
function wlgf_user_register_scripts()
{
	$plugin_version = defined('WLGF_PLUGIN_VERSION') ? WLGF_PLUGIN_VERSION : '1.3.1';
	wp_enqueue_script('jquery');
	wp_register_script('wlgf-bootstrap-bundle-min-js', plugin_dir_url(__FILE__) . 'admin/assets/bootstrap-5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.3');
	$fa_css_path = plugin_dir_path(__FILE__) . 'admin/assets/fontawesome-free-6.4.2-web/css/all.min.css';
	$fa_css_ver  = file_exists($fa_css_path) ? '6.4.2.' . filemtime($fa_css_path) : '6.4.2.1';
	wp_register_style('wlgf-fontawesome-css', plugin_dir_url(__FILE__) . 'admin/assets/fontawesome-free-6.4.2-web/css/all.min.css', array(), $fa_css_ver, 'all');

	// shortcode assets with cache-busting
	$form_css_ver = file_exists(plugin_dir_path(__FILE__) . 'includes/assets/css/wlgf-shortcode-form.css') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'includes/assets/css/wlgf-shortcode-form.css') : $plugin_version;
	$form_js_ver  = file_exists(plugin_dir_path(__FILE__) . 'includes/assets/js/wlgf-shortcode-form.js') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'includes/assets/js/wlgf-shortcode-form.js') : $plugin_version;
	$ajax_js_ver  = file_exists(plugin_dir_path(__FILE__) . 'includes/assets/js/wlgf-shortcode-ajax-script.js') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'includes/assets/js/wlgf-shortcode-ajax-script.js') : $plugin_version;

	wp_register_style('wlgf-shortcode-form', plugin_dir_url(__FILE__) . 'includes/assets/css/wlgf-shortcode-form.css', array(), $form_css_ver, 'all');

	// Dialog & Toast System
	wp_register_script('wlgf-dialog-js', plugin_dir_url(__FILE__) . 'admin/assets/js/wlgf-dialog.js', array('jquery'), $plugin_version, true);

	// Modern Datepicker (Flatpickr)
	wp_register_style('wlgf-flatpickr-css', plugin_dir_url(__FILE__) . 'admin/assets/css/flatpickr.min.css', array(), '4.6.13', 'all');
	wp_register_script('wlgf-flatpickr-js', plugin_dir_url(__FILE__) . 'admin/assets/js/flatpickr.min.js', array('jquery'), '4.6.13', true);

	wp_register_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true); // load in footer
	wp_register_script('wlgf-shortcode-form-js', plugin_dir_url(__FILE__) . 'includes/assets/js/wlgf-shortcode-form.js', array('jquery', 'wlgf-dialog-js', 'wlgf-flatpickr-js'), $form_js_ver, true); // load in footer
	wp_register_script('wlgf-shortcode-ajax-script', plugin_dir_url(__FILE__) . 'includes/assets/js/wlgf-shortcode-ajax-script.js', array('jquery'), $ajax_js_ver, true);
	wp_localize_script('wlgf-shortcode-ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'wlgf_user_register_scripts');

// register styles and scripts for backend
function wlgf_admin_register_scripts($hook = '')
{
	$plugin_version = defined('WLGF_PLUGIN_VERSION') ? WLGF_PLUGIN_VERSION : '1.3.1';
	$is_wlgf_page = isset($_GET['page']) && in_array(sanitize_text_field(wp_unslash($_GET['page'])), array(
		'lead-generation-form',
		'wlgf-manage-forms',
		'wlgf-form-generator',
		'wlgf-leads',
		'wlgf-import-export',
		'wlgf-settings',
		'wlgf-free-vs-pro',
	), true);

	// Font Awesome Icons (Admin)
	$fa_css_path = plugin_dir_path(__FILE__) . 'admin/assets/fontawesome-free-6.4.2-web/css/all.min.css';
	$fa_css_ver  = file_exists($fa_css_path) ? '6.4.2.' . filemtime($fa_css_path) : '6.4.2.1';
	wp_register_style('wlgf-fontawesome-css', plugin_dir_url(__FILE__) . 'admin/assets/fontawesome-free-6.4.2-web/css/all.min.css', array(), $fa_css_ver, 'all');

	// Modern Admin Design System
	$admin_ui_ver = file_exists(plugin_dir_path(__FILE__) . 'admin/assets/css/wlgf-admin-ui.css') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'admin/assets/css/wlgf-admin-ui.css') : $plugin_version;
	wp_register_style('wlgf-admin-ui-css', plugin_dir_url(__FILE__) . 'admin/assets/css/wlgf-admin-ui.css', array('wlgf-fontawesome-css'), $admin_ui_ver, 'all');

	// DataTables Assets
	wp_register_style('wlgf-datatables-min-css', plugin_dir_url(__FILE__) . 'admin/assets/datatables-2.0.8/datatables.min.css', array(), '2.0.8', 'all');
	wp_register_script('wlgf-datatables-min-js', plugin_dir_url(__FILE__) . 'admin/assets/datatables-2.0.8/datatables.min.js', array('jquery'), '2.0.8', true);

	// Page-Specific Assets
	wp_register_style('wlgf-manage-forms-css', plugin_dir_url(__FILE__) . 'admin/assets/css/manage-forms.css', array('wlgf-admin-ui-css'), $plugin_version, 'all');
	wp_register_script('wlgf-manage-forms-js', plugin_dir_url(__FILE__) . 'admin/assets/js/manage-forms.js', array('jquery', 'wlgf-datatables-min-js'), $plugin_version, true);

	// Modern Datepicker (Flatpickr)
	wp_register_style('wlgf-flatpickr-css', plugin_dir_url(__FILE__) . 'admin/assets/css/flatpickr.min.css', array(), '4.6.13', 'all');
	wp_register_script('wlgf-flatpickr-js', plugin_dir_url(__FILE__) . 'admin/assets/js/flatpickr.min.js', array('jquery'), '4.6.13', true);

	$fg_css_ver = file_exists(plugin_dir_path(__FILE__) . 'admin/assets/css/form-generator.css') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'admin/assets/css/form-generator.css') : $plugin_version;
	$fg_js_ver  = file_exists(plugin_dir_path(__FILE__) . 'admin/assets/js/form-generator.js') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'admin/assets/js/form-generator.js') : $plugin_version;

	wp_register_style('wlgf-form-generator-css', plugin_dir_url(__FILE__) . 'admin/assets/css/form-generator.css', array('wlgf-admin-ui-css', 'wlgf-flatpickr-css'), $fg_css_ver, 'all');
	wp_register_script('wlgf-form-generator-js', plugin_dir_url(__FILE__) . 'admin/assets/js/form-generator.js', array('jquery', 'wlgf-flatpickr-js'), $fg_js_ver, true);

	$leads_css_ver = file_exists(plugin_dir_path(__FILE__) . 'admin/assets/css/leads.css') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'admin/assets/css/leads.css') : $plugin_version;
	$leads_js_ver  = file_exists(plugin_dir_path(__FILE__) . 'admin/assets/js/leads.js') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'admin/assets/js/leads.js') : $plugin_version;

	wp_register_style('wlgf-leads-css', plugin_dir_url(__FILE__) . 'admin/assets/css/leads.css', array('wlgf-admin-ui-css', 'wlgf-datatables-min-css'), $leads_css_ver, 'all');
	wp_register_script('wlgf-leads-js', plugin_dir_url(__FILE__) . 'admin/assets/js/leads.js', array('jquery', 'wlgf-datatables-min-js', 'wlgf-dialog-js'), $leads_js_ver, true);

	wp_register_style('wlgf-import-export-css', plugin_dir_url(__FILE__) . 'admin/assets/css/import-export.css', array('wlgf-admin-ui-css'), $plugin_version, 'all');
	wp_register_script('wlgf-import-export-js', plugin_dir_url(__FILE__) . 'admin/assets/js/import-export.js', array('jquery'), $plugin_version, true);

	$settings_css_ver = file_exists(plugin_dir_path(__FILE__) . 'admin/assets/css/settings.css') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'admin/assets/css/settings.css') : $plugin_version;
	$settings_js_ver  = file_exists(plugin_dir_path(__FILE__) . 'admin/assets/js/settings.js') ? $plugin_version . '.' . filemtime(plugin_dir_path(__FILE__) . 'admin/assets/js/settings.js') : $plugin_version;

	wp_register_style('wlgf-settings-css', plugin_dir_url(__FILE__) . 'admin/assets/css/settings.css', array('wlgf-admin-ui-css'), $settings_css_ver, 'all');
	wp_register_script('wlgf-settings-js', plugin_dir_url(__FILE__) . 'admin/assets/js/settings.js', array('jquery'), $settings_js_ver, true);

	wp_register_script('wlgf-chartjs', plugin_dir_url(__FILE__) . 'admin/assets/js/chart.umd.min.js', array(), '4.4.1', true);
	wp_register_script('wlgf-dashboard-js', plugin_dir_url(__FILE__) . 'admin/assets/js/dashboard.js', array('jquery', 'wlgf-chartjs'), $plugin_version, true);

	wp_register_script('wlgf-dialog-js', plugin_dir_url(__FILE__) . 'admin/assets/js/wlgf-dialog.js', array('jquery'), $plugin_version, true);

	// Enqueue directly in document <head> on plugin admin pages to prevent FOUC / delayed styles
	if ($is_wlgf_page) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('wlgf-flatpickr-js');
		wp_enqueue_style('wlgf-flatpickr-css');
		wp_enqueue_script('wlgf-dialog-js');
		wp_enqueue_style('wlgf-admin-ui-css');

		$current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		if ('wlgf-form-generator' === $current_page) {
			wp_enqueue_style('wlgf-form-generator-css');
		} elseif ('wlgf-manage-forms' === $current_page) {
			wp_enqueue_style('wlgf-manage-forms-css');
		} elseif ('wlgf-leads' === $current_page) {
			wp_enqueue_style('wlgf-leads-css');
		} elseif ('wlgf-settings' === $current_page) {
			wp_enqueue_style('wlgf-settings-css');
		} elseif ('wlgf-import-export' === $current_page) {
			wp_enqueue_style('wlgf-import-export-css');
		}
	}
}
add_action('admin_enqueue_scripts', 'wlgf_admin_register_scripts');

// menu
function wlgf_menus()
{
	// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	add_menu_page(
		'Lead Generation Form',
		'Lead Generation Form',
		'manage_options',
		'lead-generation-form',
		'wlgf_main',
		'dashicons-media-spreadsheet',
		65
	);

	//add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', int $position )
	add_submenu_page('lead-generation-form', __('Dashboard', 'lead-generation-form'), __('Dashboard', 'lead-generation-form'), 'manage_options', 'lead-generation-form', 'wlgf_main');
	add_submenu_page('lead-generation-form', __('Manage Forms', 'lead-generation-form'), __('Manage Forms', 'lead-generation-form'), 'manage_options', 'wlgf-manage-forms', 'wlgf_manage_forms');
	add_submenu_page('lead-generation-form', __('Form Builder', 'lead-generation-form'), __('Form Builder', 'lead-generation-form'), 'manage_options', 'wlgf-form-generator', 'wlgf_form_generator');
	add_submenu_page('lead-generation-form', __('All Captured Leads', 'lead-generation-form'), __('All Captured Leads', 'lead-generation-form'), 'manage_options', 'wlgf-leads', 'wlgf_leads');
	add_submenu_page('lead-generation-form', __('Import & Export', 'lead-generation-form'), __('Import & Export', 'lead-generation-form'), 'manage_options', 'wlgf-import-export', 'wlgf_import_export');
	add_submenu_page('lead-generation-form', __('Settings', 'lead-generation-form'), __('Settings', 'lead-generation-form'), 'manage_options', 'wlgf-settings', 'wlgf_settings');
	add_submenu_page('lead-generation-form', __('Free vs Pro', 'lead-generation-form'), '<span style="color:#f59e0b;font-weight:600;">' . __('Free vs Pro ★', 'lead-generation-form') . '</span>', 'manage_options', 'wlgf-free-vs-pro', 'wlgf_free_vs_pro');
}
add_action('admin_menu', 'wlgf_menus');

include('includes/shortcode-ajax.php');

// main page
function wlgf_main()
{
	require 'admin/dashboard.php';
}

// manage forms page
function wlgf_manage_forms()
{
	require 'admin/manage-forms.php';
}

// form generator page
function wlgf_form_generator()
{
	require 'admin/form-generator.php';
}

// all leads page
function wlgf_leads()
{
	require 'admin/leads.php';
}

// import export page
function wlgf_import_export()
{
	require 'admin/import-export.php';
}

// settings page
function wlgf_settings()
{
	require 'admin/settings.php';
}

// free vs pro comparison page
function wlgf_free_vs_pro()
{
	require 'admin/free-vs-pro.php';
}

function wlgf_get_next_id()
{
	global $wpdb;
	$all_options = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM `$wpdb->options` WHERE `option_name` LIKE %s AND `option_name` NOT LIKE %s",
			$wpdb->esc_like('wlgf_form_') . '%',
			$wpdb->esc_like('wlgf_form_views_') . '%'
		)
	);

	$max_id = 0;
	if (!empty($all_options)) {
		foreach ($all_options as $option_name) {
			$id = (int) substr($option_name, strrpos($option_name, '_') + 1);
			if ($id > $max_id) {
				$max_id = $id;
			}
		}
	}

	$next_id = $max_id + 1;
	while (false !== get_option('wlgf_form_' . $next_id) || false !== get_option('wlgf_saved_form_data_' . $next_id)) {
		$next_id++;
	}

	return $next_id;
}

/**
 * Retrieve normalized created_at and updated_at timestamps and human labels for a form
 */
if (!function_exists('wlgf_get_form_dates')) {
	function wlgf_get_form_dates($form_id, &$form_data = null)
	{
		$date_format = get_option('date_format', 'M j, Y');
		$time_format = get_option('time_format', 'H:i');

		if (empty($form_data) || !is_array($form_data)) {
			$form_data = get_option('wlgf_form_' . $form_id, array());
		}

		$created_raw = !empty($form_data['created_at']) ? $form_data['created_at'] : '';
		$updated_raw = !empty($form_data['updated_at']) ? $form_data['updated_at'] : '';

		// If created_at is missing (forms created prior to v3.2.0), discover or assign a fallback date
		if (empty($created_raw)) {
			$leads = get_option('wlgf_saved_form_data_' . $form_id, array());
			if (is_array($leads) && !empty($leads[0]['Date Time'])) {
				$created_raw = $leads[0]['Date Time'];
			} elseif (is_array($leads) && !empty($leads[0]['_created_at'])) {
				$created_raw = $leads[0]['_created_at'];
			} else {
				$created_raw = current_time('mysql');
			}
			$updated_raw = !empty($updated_raw) ? $updated_raw : $created_raw;

			// Persist back to avoid changing on next refresh
			if (is_array($form_data)) {
				$form_data['created_at'] = $created_raw;
				$form_data['updated_at'] = $updated_raw;
				update_option('wlgf_form_' . $form_id, $form_data);
			}
		}

		if (empty($updated_raw)) {
			$updated_raw = $created_raw;
		}

		$created_time = strtotime($created_raw);
		if (!$created_time) {
			$created_time = current_time('timestamp');
		}
		$updated_time = strtotime($updated_raw);
		if (!$updated_time) {
			$updated_time = $created_time;
		}

		$diff_sec = current_time('timestamp') - $updated_time;
		if ($diff_sec < 60) {
			$updated_human = __('Just now', 'lead-generation-form');
		} else {
			$updated_human = sprintf(__('%s ago', 'lead-generation-form'), human_time_diff($updated_time, current_time('timestamp')));
		}

		return array(
			'created_raw' => $created_raw,
			'created_time' => $created_time,
			'created_formatted' => date_i18n($date_format, $created_time),
			'created_time_fmt' => date_i18n($time_format, $created_time),
			'updated_raw' => $updated_raw,
			'updated_time' => $updated_time,
			'updated_formatted' => date_i18n($date_format, $updated_time),
			'updated_time_fmt' => date_i18n($time_format, $updated_time),
			'updated_human' => $updated_human,
		);
	}
}

// 1. manage form actions start
function wlgf_manage_form_action_callback()
{
	if (current_user_can('manage_options')) {
		$wlgf_do_action = isset($_POST['do']) ? sanitize_text_field(wp_unslash($_POST['do'])) : 'none';

		// clone form start
		if ($wlgf_do_action === 'clone') {
			if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-manage-forms')) {
				if (isset($_POST['wlgf_form_id'])) {
					$wlgf_form_id = sanitize_text_field(wp_unslash($_POST['wlgf_form_id']));
					$wlgf_form_clone_data = get_option('wlgf_form_' . $wlgf_form_id);

					if (empty($wlgf_form_clone_data) || !is_array($wlgf_form_clone_data)) {
						wp_die();
					}

					$wlgf_form_id_new = wlgf_get_next_id();
					$wlgf_form_name_new = sanitize_text_field($wlgf_form_clone_data['form_name'] . ' - Clone');
					$wlgf_form_new = isset($wlgf_form_clone_data['form']) ? $wlgf_form_clone_data['form'] : '';
					$now_mysql = current_time('mysql');

					$wlgf_form_new_details = is_array($wlgf_form_clone_data) ? $wlgf_form_clone_data : array();
					$wlgf_form_new_details['form_id'] = $wlgf_form_id_new;
					$wlgf_form_new_details['form_name'] = $wlgf_form_name_new;
					$wlgf_form_new_details['form'] = $wlgf_form_new;
					$wlgf_form_new_details['created_at'] = $now_mysql;
					$wlgf_form_new_details['updated_at'] = $now_mysql;

					update_option('wlgf_form_' . $wlgf_form_id_new, $wlgf_form_new_details);
					delete_transient('wlgf_elementor_forms_list');
					delete_transient('wlgf_gutenberg_forms_list');

					$dates = wlgf_get_form_dates($wlgf_form_id_new, $wlgf_form_new_details);
					$shortcode = '[WLFG id="' . esc_attr($wlgf_form_id_new) . '"]';
					$edit_url = admin_url('admin.php?page=wlgf-form-generator&form-id=' . $wlgf_form_id_new . '&modify-nonce=' . wp_create_nonce('wlgf-modify-form'));
					$original_form_name = isset($wlgf_form_clone_data['form_name']) ? sanitize_text_field($wlgf_form_clone_data['form_name']) : '';

					ob_start();
					?>
					<tr id="<?php echo esc_attr($wlgf_form_id_new); ?>"
						data-clone-id="<?php echo esc_attr($wlgf_form_id_new); ?>"
						data-clone-name="<?php echo esc_attr($wlgf_form_name_new); ?>"
						data-clone-shortcode="<?php echo esc_attr($shortcode); ?>"
						data-original-name="<?php echo esc_attr($original_form_name); ?>"
						data-edit-url="<?php echo esc_url($edit_url); ?>">
						<td data-order="<?php echo esc_attr( intval( $wlgf_form_id_new ) ); ?>">
							<span class="wlgf-badge wlgf-badge--muted">#<?php echo esc_html($wlgf_form_id_new); ?></span>
						</td>
						<td>
							<strong><?php echo esc_html($wlgf_form_name_new); ?></strong>
						</td>
						<td data-order="0">
							<span class="wlgf-badge wlgf-badge--muted"><i class="fa-regular fa-eye"></i> 0</span>
						</td>
						<td data-order="0">
							<span class="wlgf-badge wlgf-badge--muted"><i class="fa-solid fa-users"></i> 0</span>
						</td>
						<td data-order="0">
							<span class="wlgf-badge wlgf-badge--muted"><i class="fa-solid fa-chart-line"></i> 0%</span>
						</td>
						<td data-order="<?php echo esc_attr($dates['created_time']); ?>">
							<div class="wlgf-date-cell">
								<span class="wlgf-date-main"><i class="fa-regular fa-calendar-plus"></i>
									<?php echo esc_html($dates['created_formatted']); ?></span>
								<span class="wlgf-date-sub"><?php echo esc_html($dates['created_time_fmt']); ?></span>
							</div>
						</td>
						<td data-order="<?php echo esc_attr($dates['updated_time']); ?>">
							<div class="wlgf-date-cell">
								<span class="wlgf-date-main"><i class="fa-solid fa-clock-rotate-left"></i>
									<?php echo esc_html($dates['updated_formatted']); ?></span>
								<span class="wlgf-date-sub"><?php echo esc_html($dates['updated_human']); ?></span>
							</div>
						</td>
						<td>
							<div class="wlgf-code-pill">
								<code class="wlgf-code-pill__text"><?php echo esc_html($shortcode); ?></code>
								<button type="button" class="wlgf-code-pill__copy-btn"
									onclick="return WLGF('<?php echo esc_attr($wlgf_form_id_new); ?>', 'copy');"
									title="<?php esc_attr_e('Copy Shortcode', 'lead-generation-form'); ?>">
									<i class="fa-regular fa-copy"></i>
								</button>
								<span id="wlgf-copied-<?php echo esc_attr($wlgf_form_id_new); ?>"
									class="wlgf-code-pill__copied-tooltip"><?php esc_html_e('Copied!', 'lead-generation-form'); ?></span>
							</div>
						</td>
						<td>
							<div class="wlgf-btn-group">
								<button type="button" onclick="return WLGF('<?php echo esc_attr($wlgf_form_id_new); ?>', 'clone', this);"
									class="wlgf-btn wlgf-btn--sm wlgf-btn--outline"
									title="<?php esc_attr_e('Clone Form', 'lead-generation-form'); ?>">
									<i class="fa-regular fa-clone"></i> <?php esc_html_e('Clone', 'lead-generation-form'); ?>
								</button>
								<a href="<?php echo esc_url($edit_url); ?>"
									class="wlgf-btn wlgf-btn--sm wlgf-btn--primary"
									title="<?php esc_attr_e('Edit Form', 'lead-generation-form'); ?>">
									<i class="fa-solid fa-pen-to-square"></i> <?php esc_html_e('Edit', 'lead-generation-form'); ?>
								</a>
								<button type="button" onclick="return WLGF('<?php echo esc_attr($wlgf_form_id_new); ?>', 'delete', this);"
									class="wlgf-btn wlgf-btn--sm wlgf-btn--danger"
									title="<?php esc_attr_e('Delete Form', 'lead-generation-form'); ?>">
									<i class="fa-regular fa-trash-can"></i> <?php esc_html_e('Delete', 'lead-generation-form'); ?>
								</button>
							</div>
						</td>
					</tr>
					<?php
					$row_html = ob_get_clean();

					wp_send_json_success(array(
						'html'          => $row_html,
						'clone_id'      => (string) $wlgf_form_id_new,
						'clone_name'    => $wlgf_form_name_new,
						'shortcode'     => $shortcode,
						'original_name' => $original_form_name,
						'edit_url'      => $edit_url,
					));
				}
				wp_die();
			} else {
				echo esc_html__('Nonce verification failed.', 'lead-generation-form');
				wp_die();
			}
		} // clone form end

		// delete form start
		if ($wlgf_do_action === 'delete') {
			if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-manage-forms')) {
				if (isset($_POST['wlgf_form_id'])) {
					$wlgf_form_id = sanitize_text_field(wp_unslash($_POST['wlgf_form_id']));

					delete_option('wlgf_form_' . $wlgf_form_id);
					delete_option('wlgf_saved_form_data_' . $wlgf_form_id);
					delete_transient('wlgf_elementor_forms_list');
					delete_transient('wlgf_gutenberg_forms_list');
				}
				wp_die();
			} else {
				echo esc_html__('Nonce verification failed.', 'lead-generation-form');
				wp_die();
			}
		} // delete form end
	}
}
add_action('wp_ajax_wlgf_manage_form_action', 'wlgf_manage_form_action_callback');
// 1. manage form actions end


// 2. form generator page action start
function wlgf_form_save_callback()
{
	if (current_user_can('manage_options')) {
		$wlgf_is_save = (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-save-form'));
		$wlgf_is_modify = (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-modify-form'));

		if ($wlgf_is_save || $wlgf_is_modify) {
			$wlgf_form_id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
			$wlgf_form_name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
			$raw_form_json = isset($_POST['form']) ? wp_unslash($_POST['form']) : '';

			// Safely decode JSON without wp_kses_post corrupting quote boundaries or JSON syntax
			$wlgf_form = json_decode($raw_form_json);
			if (null === $wlgf_form && !empty($raw_form_json)) {
				$wlgf_form = json_decode(stripslashes($raw_form_json));
			}
			if (is_string($wlgf_form)) {
				$second = json_decode($wlgf_form);
				if (null !== $second) {
					$wlgf_form = $second;
				}
			}

			// Sanitize form field attributes safely after JSON decoding
			if (is_array($wlgf_form)) {
				foreach ($wlgf_form as &$field_obj) {
					if (is_object($field_obj)) {
						$f_type = isset($field_obj->type) ? (string) $field_obj->type : '';
						if (isset($field_obj->label)) {
							if ($f_type === 'paragraph' || $f_type === 'header') {
								$field_obj->label = wp_kses_post($field_obj->label);
							} else {
								$clean_lbl = (string) $field_obj->label;
								if (false !== strpos($clean_lbl, '&lt;') && false !== strpos($clean_lbl, '&gt;')) {
									$clean_lbl = html_entity_decode($clean_lbl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
								}
								$field_obj->label = sanitize_text_field(wp_strip_all_tags($clean_lbl));
							}
						}
						if (isset($field_obj->values) && is_array($field_obj->values)) {
							foreach ($field_obj->values as &$opt_item) {
								if (is_object($opt_item) && isset($opt_item->label)) {
									$opt_lbl = (string) $opt_item->label;
									if (false !== strpos($opt_lbl, '&lt;') && false !== strpos($opt_lbl, '&gt;')) {
										$opt_lbl = html_entity_decode($opt_lbl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
									}
									$opt_item->label = sanitize_text_field(wp_strip_all_tags($opt_lbl));
								} elseif (is_array($opt_item) && isset($opt_item['label'])) {
									$opt_lbl = (string) $opt_item['label'];
									if (false !== strpos($opt_lbl, '&lt;') && false !== strpos($opt_lbl, '&gt;')) {
										$opt_lbl = html_entity_decode($opt_lbl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
									}
									$opt_item['label'] = sanitize_text_field(wp_strip_all_tags($opt_lbl));
								}
							}
							unset($opt_item);
						}
						if (isset($field_obj->description)) {
							$field_obj->description = wp_kses_post($field_obj->description);
						}
						if (isset($field_obj->placeholder)) {
							$field_obj->placeholder = sanitize_text_field($field_obj->placeholder);
						}
						if (isset($field_obj->allowed_types)) {
							$field_obj->allowed_types = sanitize_text_field($field_obj->allowed_types);
						}
						if (isset($field_obj->max_file_size)) {
							$field_obj->max_file_size = floatval($field_obj->max_file_size);
						}
					} elseif (is_array($field_obj)) {
						$f_type = isset($field_obj['type']) ? (string) $field_obj['type'] : '';
						if (isset($field_obj['label'])) {
							if ($f_type === 'paragraph' || $f_type === 'header') {
								$field_obj['label'] = wp_kses_post($field_obj['label']);
							} else {
								$clean_lbl = (string) $field_obj['label'];
								if (false !== strpos($clean_lbl, '&lt;') && false !== strpos($clean_lbl, '&gt;')) {
									$clean_lbl = html_entity_decode($clean_lbl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
								}
								$field_obj['label'] = sanitize_text_field(wp_strip_all_tags($clean_lbl));
							}
						}
						if (isset($field_obj['values']) && is_array($field_obj['values'])) {
							foreach ($field_obj['values'] as &$opt_item) {
								if (is_array($opt_item) && isset($opt_item['label'])) {
									$opt_lbl = (string) $opt_item['label'];
									if (false !== strpos($opt_lbl, '&lt;') && false !== strpos($opt_lbl, '&gt;')) {
										$opt_lbl = html_entity_decode($opt_lbl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
									}
									$opt_item['label'] = sanitize_text_field(wp_strip_all_tags($opt_lbl));
								} elseif (is_object($opt_item) && isset($opt_item->label)) {
									$opt_lbl = (string) $opt_item->label;
									if (false !== strpos($opt_lbl, '&lt;') && false !== strpos($opt_lbl, '&gt;')) {
										$opt_lbl = html_entity_decode($opt_lbl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
									}
									$opt_item->label = sanitize_text_field(wp_strip_all_tags($opt_lbl));
								}
							}
							unset($opt_item);
						}
						if (isset($field_obj['description'])) {
							$field_obj['description'] = wp_kses_post($field_obj['description']);
						}
						if (isset($field_obj['placeholder'])) {
							$field_obj['placeholder'] = sanitize_text_field($field_obj['placeholder']);
						}
						if (isset($field_obj['allowed_types'])) {
							$field_obj['allowed_types'] = sanitize_text_field($field_obj['allowed_types']);
						}
						if (isset($field_obj['max_file_size'])) {
							$field_obj['max_file_size'] = floatval($field_obj['max_file_size']);
						}
					}
				}
				unset($field_obj);
			}

			// Edition security guard: Strip PRO field types in Free edition before saving
			if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) {
				$pro_types = array( 'signature', 'calculation', 'step-break', 'star-rating', 'nps-matrix' );
				if ( is_array( $wlgf_form ) ) {
					$filtered_form = array();
					foreach ( $wlgf_form as $f_item ) {
						$f_type = is_object( $f_item ) ? ( $f_item->type ?? '' ) : ( is_array( $f_item ) ? ( $f_item['type'] ?? '' ) : '' );
						if ( ! in_array( $f_type, $pro_types, true ) ) {
							$filtered_form[] = $f_item;
						}
					}
					$wlgf_form = $filtered_form;
				}
			}

			// If updating an existing form and incoming canvas failed to decode or was empty, preserve existing structure
			if (empty($wlgf_form) && !empty($wlgf_form_id) && '0' !== (string) $wlgf_form_id) {
				$existing_opt = get_option('wlgf_form_' . $wlgf_form_id);
				if (!empty($existing_opt)) {
					if (is_array($existing_opt) && isset($existing_opt['form'])) {
						$wlgf_form = $existing_opt['form'];
					} elseif (!is_array($existing_opt)) {
						$wlgf_form = $existing_opt;
					}
				}
			}

			if (empty($wlgf_form_id) || '0' === (string) $wlgf_form_id) {
				$wlgf_form_id = wlgf_get_next_id();
			}

			$wlgf_confirmation_type = isset($_POST['confirmation_type']) ? sanitize_text_field(wp_unslash($_POST['confirmation_type'])) : 'message';
			$wlgf_redirect_url = isset($_POST['redirect_url']) ? esc_url_raw(wp_unslash($_POST['redirect_url'])) : '';
			$wlgf_custom_message = isset($_POST['custom_message']) ? sanitize_text_field(wp_unslash($_POST['custom_message'])) : '';
			$default_auto_reply_subject = __( 'Thank you for your inquiry! We received your message.', 'lead-generation-form' );
			$default_auto_reply_body    = "Hi {Name},\n\nThank you for reaching out to us. We have received your inquiry regarding \"{Form_Name}\" and will get back to you shortly.\n\nBest regards,\n{Site_Name}";

			$wlgf_auto_reply_enabled = isset($_POST['auto_reply_enabled']) ? intval($_POST['auto_reply_enabled']) : 0;
			$raw_auto_reply_subject  = isset($_POST['auto_reply_subject']) ? sanitize_text_field(wp_unslash($_POST['auto_reply_subject'])) : '';
			$raw_auto_reply_body     = isset($_POST['auto_reply_body']) ? wp_kses_post(wp_unslash($_POST['auto_reply_body'])) : '';

			$wlgf_auto_reply_subject = ( '' !== trim( $raw_auto_reply_subject ) ) ? $raw_auto_reply_subject : ( $wlgf_auto_reply_enabled ? $default_auto_reply_subject : '' );
			$wlgf_auto_reply_body    = ( '' !== trim( $raw_auto_reply_body ) ) ? $raw_auto_reply_body : ( $wlgf_auto_reply_enabled ? $default_auto_reply_body : '' );
			$wlgf_theme_primary = isset($_POST['theme_primary']) ? sanitize_hex_color(wp_unslash($_POST['theme_primary'])) : '#2563eb';
			$wlgf_theme_radius = isset($_POST['theme_radius']) ? sanitize_text_field(wp_unslash($_POST['theme_radius'])) : '6px';
			$wlgf_theme_bg = isset($_POST['theme_bg']) ? sanitize_text_field(wp_unslash($_POST['theme_bg'])) : 'card';
			$wlgf_theme_font = isset($_POST['theme_font']) ? sanitize_text_field(wp_unslash($_POST['theme_font'])) : 'inherit';
			$wlgf_theme_btn_width = isset($_POST['theme_btn_width']) ? sanitize_text_field(wp_unslash($_POST['theme_btn_width'])) : 'auto';
			$wlgf_theme_field_size = isset($_POST['theme_field_size']) ? sanitize_text_field(wp_unslash($_POST['theme_field_size'])) : 'default';
			$wlgf_theme_mode = isset($_POST['theme_mode']) ? sanitize_text_field(wp_unslash($_POST['theme_mode'])) : 'light';
			$wlgf_theme_input_style = isset($_POST['theme_input_style']) ? sanitize_text_field(wp_unslash($_POST['theme_input_style'])) : 'bordered';
			$wlgf_theme_max_width = (isset($_POST['theme_max_width']) && !empty($_POST['theme_max_width'])) ? sanitize_text_field(wp_unslash($_POST['theme_max_width'])) : '640px';
			$wlgf_theme_btn_shadow = isset($_POST['theme_btn_shadow']) ? sanitize_text_field(wp_unslash($_POST['theme_btn_shadow'])) : 'default';
			$wlgf_theme_label_style = isset($_POST['theme_label_style']) ? sanitize_text_field(wp_unslash($_POST['theme_label_style'])) : 'default';
			$wlgf_webhook_url = isset($_POST['webhook_url']) ? esc_url_raw(wp_unslash($_POST['webhook_url'])) : '';
			$wlgf_webhook_auth = isset($_POST['webhook_auth']) ? sanitize_text_field(wp_unslash($_POST['webhook_auth'])) : '';
			$wlgf_schedule_start = isset($_POST['schedule_start']) ? sanitize_text_field(wp_unslash($_POST['schedule_start'])) : '';
			$wlgf_schedule_end = isset($_POST['schedule_end']) ? sanitize_text_field(wp_unslash($_POST['schedule_end'])) : '';
			$wlgf_max_submissions = isset($_POST['max_submissions']) ? intval($_POST['max_submissions']) : 0;
			$wlgf_closed_message = isset($_POST['closed_message']) ? sanitize_text_field(wp_unslash($_POST['closed_message'])) : '';
			$wlgf_newsletter_enabled = isset($_POST['newsletter_enabled']) ? intval($_POST['newsletter_enabled']) : 0;
			$wlgf_newsletter_mode = isset($_POST['newsletter_mode']) ? sanitize_text_field(wp_unslash($_POST['newsletter_mode'])) : 'explicit';
			$wlgf_newsletter_label = isset($_POST['newsletter_label']) ? sanitize_text_field(wp_unslash($_POST['newsletter_label'])) : '';
			$wlgf_newsletter_default_checked = isset($_POST['newsletter_default_checked']) ? intval($_POST['newsletter_default_checked']) : 0;
			$wlgf_newsletter_tag = isset($_POST['newsletter_tag']) ? sanitize_text_field(wp_unslash($_POST['newsletter_tag'])) : '';

			// Multi-Step Wizard & Conditional Logic (Phase 2 / PRO-001 & PRO-002)
			$raw_wizard_style = isset($_POST['wizard_style']) ? sanitize_text_field(wp_unslash($_POST['wizard_style'])) : 'circles';
			$wlgf_wizard_style = in_array($raw_wizard_style, array('circles', 'bar', 'breadcrumbs'), true) ? $raw_wizard_style : 'circles';
			$wlgf_wizard_prev_text = isset($_POST['wizard_prev_text']) ? sanitize_text_field(wp_unslash($_POST['wizard_prev_text'])) : __('Previous', 'lead-generation-form');
			$wlgf_wizard_next_text = isset($_POST['wizard_next_text']) ? sanitize_text_field(wp_unslash($_POST['wizard_next_text'])) : __('Next Step', 'lead-generation-form');
			$wlgf_wizard_step_1_title = isset($_POST['wizard_step_1_title']) ? sanitize_text_field(wp_unslash($_POST['wizard_step_1_title'])) : '';

			// Payments Configuration (PRO-016)
			$wlgf_payment_enabled = isset($_POST['payment_enabled']) ? intval($_POST['payment_enabled']) : 0;
			$wlgf_payment_type = isset($_POST['payment_type']) && in_array($_POST['payment_type'], array('fixed', 'calculation'), true) ? sanitize_text_field(wp_unslash($_POST['payment_type'])) : 'fixed';
			$wlgf_payment_amount = isset($_POST['payment_amount']) ? sanitize_text_field(wp_unslash($_POST['payment_amount'])) : '0.00';
			$wlgf_payment_calc_field = isset($_POST['payment_calc_field']) ? sanitize_text_field(wp_unslash($_POST['payment_calc_field'])) : '';
			$wlgf_payment_item_name = isset($_POST['payment_item_name']) ? sanitize_text_field(wp_unslash($_POST['payment_item_name'])) : '';

			$sanitized_conditions = array();
			$allowed_operators = array('equals', 'not_equals', 'contains', 'not_contains', 'greater_than', 'less_than', 'is_empty', 'is_not_empty');
			$allowed_actions = array('show', 'hide');
			$allowed_logics = array('all', 'any');

			if (isset($_POST['conditions']) && is_array($_POST['conditions'])) {
				foreach ($_POST['conditions'] as $target_field => $cond_data) {
					$target_name = sanitize_text_field($target_field);
					if (empty($target_name) || !is_array($cond_data)) {
						continue;
					}

					$action = (isset($cond_data['action']) && in_array($cond_data['action'], $allowed_actions, true)) ? $cond_data['action'] : 'show';
					$logic = (isset($cond_data['logic']) && in_array($cond_data['logic'], $allowed_logics, true)) ? $cond_data['logic'] : 'all';
					$rules = array();

					if (isset($cond_data['rules']) && is_array($cond_data['rules'])) {
						foreach ($cond_data['rules'] as $rule) {
							if (!is_array($rule)) {
								continue;
							}
							$source_field = isset($rule['field']) ? sanitize_text_field($rule['field']) : '';
							$operator = (isset($rule['operator']) && in_array($rule['operator'], $allowed_operators, true)) ? $rule['operator'] : 'equals';
							$value = isset($rule['value']) ? sanitize_text_field($rule['value']) : '';

							if (!empty($source_field)) {
								$rules[] = array(
									'field' => $source_field,
									'operator' => $operator,
									'value' => $value,
								);
							}
						}
					}

					if (!empty($rules)) {
						$sanitized_conditions[$target_name] = array(
							'action' => $action,
							'logic' => $logic,
							'rules' => $rules,
						);
					}
				}
			}

			$wlgf_display_mode = isset($_POST['display_mode']) && in_array($_POST['display_mode'], array('inline', 'popup', 'slidein', 'sticky_bar', 'floating_widget'), true) ? sanitize_text_field(wp_unslash($_POST['display_mode'])) : 'inline';
			$wlgf_display_position = isset($_POST['display_position']) ? sanitize_text_field(wp_unslash($_POST['display_position'])) : 'right';
			$wlgf_trigger_type = isset($_POST['trigger_type']) && in_array($_POST['trigger_type'], array('time_delay', 'exit_intent', 'scroll_depth', 'click_only'), true) ? sanitize_text_field(wp_unslash($_POST['trigger_type'])) : 'time_delay';
			$wlgf_trigger_delay = isset($_POST['trigger_delay']) ? max(0, intval($_POST['trigger_delay'])) : 5;
			$wlgf_trigger_scroll = isset($_POST['trigger_scroll']) ? max(1, min(100, intval($_POST['trigger_scroll']))) : 50;
			$wlgf_trigger_frequency = isset($_POST['trigger_frequency']) ? max(0, intval($_POST['trigger_frequency'])) : 7;
			$wlgf_capture_abandoned = isset($_POST['capture_abandoned']) ? intval($_POST['capture_abandoned']) : 1;
			$existing_form = get_option('wlgf_form_' . $wlgf_form_id);
			$wlgf_created_at = (is_array($existing_form) && !empty($existing_form['created_at'])) ? $existing_form['created_at'] : current_time('mysql');
			$wlgf_updated_at = current_time('mysql');

			// Edition guard: Enforce Free plan restrictions on advanced settings
			if ( ! ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) ) {
				$sanitized_conditions = array();
				$wlgf_wizard_style = 'circles';
				$wlgf_wizard_step_1_title = '';
				$wlgf_display_mode = 'inline';
				$wlgf_capture_abandoned = 0;
				$wlgf_payment_enabled = 0;
			}

			$wlgf_form_details = array(
				'form_id' => $wlgf_form_id,
				'form_name' => $wlgf_form_name,
				'form' => $wlgf_form,
				'created_at' => $wlgf_created_at,
				'updated_at' => $wlgf_updated_at,
				'confirmation_type' => $wlgf_confirmation_type,
				'redirect_url' => $wlgf_redirect_url,
				'custom_message' => $wlgf_custom_message,
				'auto_reply_enabled' => $wlgf_auto_reply_enabled,
				'auto_reply_subject' => $wlgf_auto_reply_subject,
				'auto_reply_body' => $wlgf_auto_reply_body,
				'theme_primary' => $wlgf_theme_primary ? $wlgf_theme_primary : '#2563eb',
				'theme_radius' => $wlgf_theme_radius,
				'theme_bg' => $wlgf_theme_bg,
				'theme_font' => $wlgf_theme_font,
				'theme_btn_width' => $wlgf_theme_btn_width,
				'theme_field_size' => $wlgf_theme_field_size,
				'theme_mode' => $wlgf_theme_mode,
				'theme_input_style' => $wlgf_theme_input_style,
				'theme_max_width' => $wlgf_theme_max_width,
				'theme_btn_shadow' => $wlgf_theme_btn_shadow,
				'theme_label_style' => $wlgf_theme_label_style,
				'webhook_url' => $wlgf_webhook_url,
				'webhook_auth' => $wlgf_webhook_auth,
				'schedule_start' => $wlgf_schedule_start,
				'schedule_end' => $wlgf_schedule_end,
				'max_submissions' => $wlgf_max_submissions,
				'closed_message' => $wlgf_closed_message,
				'newsletter_enabled' => $wlgf_newsletter_enabled,
				'newsletter_mode' => $wlgf_newsletter_mode,
				'newsletter_label' => $wlgf_newsletter_label,
				'newsletter_default_checked' => $wlgf_newsletter_default_checked,
				'newsletter_tag' => $wlgf_newsletter_tag,
				'wizard_style' => $wlgf_wizard_style,
				'wizard_step_1_title' => $wlgf_wizard_step_1_title,
				'wizard_prev_text' => $wlgf_wizard_prev_text,
				'wizard_next_text' => $wlgf_wizard_next_text,
				'conditions' => $sanitized_conditions,
				'payment_enabled' => $wlgf_payment_enabled,
				'payment_type' => $wlgf_payment_type,
				'payment_amount' => $wlgf_payment_amount,
				'payment_calc_field' => $wlgf_payment_calc_field,
				'payment_item_name' => $wlgf_payment_item_name,
				'display_mode' => $wlgf_display_mode,
				'display_position' => $wlgf_display_position,
				'trigger_type' => $wlgf_trigger_type,
				'trigger_delay' => $wlgf_trigger_delay,
				'trigger_scroll' => $wlgf_trigger_scroll,
				'trigger_frequency' => $wlgf_trigger_frequency,
				'capture_abandoned' => $wlgf_capture_abandoned,
			);
			update_option('wlgf_form_' . $wlgf_form_id, $wlgf_form_details);
			delete_transient('wlgf_elementor_forms_list');
			delete_transient('wlgf_gutenberg_forms_list');
			?>
			<div class="wlgf-alert wlgf-alert--success wlgf-mb-3">
				<i class="fa-solid fa-circle-check"></i>
				<span><?php esc_html_e('Form saved successfully! You can copy the shortcode below or continue updating.', 'lead-generation-form'); ?></span>
			</div>
			<input type="hidden" id="wlgf-modify-form-id" value="<?php echo esc_attr($wlgf_form_id); ?>">
			<div class="wlgf-form-actions-row wlgf-flex wlgf-items-center wlgf-gap-3 wlgf-mb-3">
				<button type="button" id="wlgf-modify-form" value="wlgf_modify_form"
					class="wlgf-action-button wlgf-btn wlgf-btn--primary wlgf-btn--lg">
					<i class="fa-solid fa-file-pen"></i> <?php esc_html_e('Update Form', 'lead-generation-form'); ?>
				</button>
			</div>

			<!-- Prominent Form Shortcode Embed Box (Displayed AFTER Save/Update Button) -->
			<div class="wlgf-shortcode-embed-box">
				<div class="wlgf-shortcode-embed-box__info">
					<div class="wlgf-shortcode-embed-box__icon">
						<i class="fa-solid fa-code"></i>
					</div>
					<div class="wlgf-shortcode-embed-box__meta">
						<span class="wlgf-shortcode-embed-box__title"><?php esc_html_e('Form Shortcode', 'lead-generation-form'); ?></span>
						<span class="wlgf-shortcode-embed-box__desc"><?php esc_html_e('Copy & paste into any page, post, or block widget.', 'lead-generation-form'); ?></span>
					</div>
				</div>
				<div class="wlgf-shortcode-embed-box__action">
					<code class="wlgf-shortcode-embed-box__code">[WLFG id="<?php echo esc_attr($wlgf_form_id); ?>"]</code>
					<button type="button" id="wlgf-shortcode-btn-<?php echo esc_attr($wlgf_form_id); ?>" class="wlgf-shortcode-embed-box__btn" onclick="return WLGF('<?php echo esc_attr($wlgf_form_id); ?>', 'copy', this);" title="<?php esc_attr_e('Copy Shortcode', 'lead-generation-form'); ?>">
						<i class="fa-regular fa-copy"></i>
						<span><?php esc_html_e('Copy Shortcode', 'lead-generation-form'); ?></span>
					</button>
					<span id="wlgf-copied-<?php echo esc_attr($wlgf_form_id); ?>" class="wlgf-code-pill__copied-tooltip"><?php esc_html_e('Copied!', 'lead-generation-form'); ?></span>
				</div>
			</div>
			<?php
		} else {
			echo '<div class="wlgf-alert wlgf-alert--danger">' . esc_html__('Security check failed. Please refresh and try again.', 'lead-generation-form') . '</div>';
		}
		wp_die();
	}
}
add_action('wp_ajax_wlgf_save_form', 'wlgf_form_save_callback');
// 2. form generator page action end


// 3. all leads page action start - dynamic stream and manage leads
function wlgf_lead_loader_callback()
{
	if (current_user_can('manage_options')) {
		if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-leads')) {
			if (isset($_POST['wlgf_form_id']) && isset($_POST['do'])) {
				$wlgf_form_id = sanitize_text_field(wp_unslash($_POST['wlgf_form_id']));
				$wlgf_do = sanitize_text_field(wp_unslash($_POST['do']));

				// Load table data
				if ($wlgf_do === 'load') {
					$wlgf_form_body = get_option('wlgf_form_' . $wlgf_form_id);
					$wlgf_form = isset($wlgf_form_body['form']) ? $wlgf_form_body['form'] : '';
					$structure = is_array($wlgf_form) ? $wlgf_form : (is_string($wlgf_form) ? json_decode($wlgf_form) : array());

					$wlgf_form_data = get_option('wlgf_saved_form_data_' . $wlgf_form_id);
					$wlgf_newsletter_only = (isset($_POST['newsletter_only']) && '1' === (string) $_POST['newsletter_only']);
					$wlgf_download_url = admin_url('admin-ajax.php?action=wlgf_export_leads_csv&wlgf_form_id=' . $wlgf_form_id . '&nonce=' . wp_create_nonce('wlgf-export-csv-' . $wlgf_form_id));
					$wlgf_download_subscribers_url = admin_url('admin-ajax.php?action=wlgf_export_leads_csv&wlgf_form_id=' . $wlgf_form_id . '&newsletter_only=1&nonce=' . wp_create_nonce('wlgf-export-csv-' . $wlgf_form_id));

					$wlgf_display_columns = array();
					if (is_array($structure)) {
						foreach ($structure as $item) {
							$item_type = is_object($item) ? ($item->type ?? '') : ($item['type'] ?? '');
							$item_name = is_object($item) ? ($item->name ?? '') : ($item['name'] ?? '');
							$item_label = is_object($item) ? ($item->label ?? '') : ($item['label'] ?? '');
							if (!empty($item_label) && !in_array($item_type, array('header', 'button', 'paragraph', 'hidden', 'divider', 'step-break', 'file'), true)) {
								$wlgf_display_columns[] = array(
									'name' => $item_name,
									'label' => $item_label,
									'type' => $item_type,
								);
							}
						}
						foreach ($structure as $item) {
							$item_type = is_object($item) ? ($item->type ?? '') : ($item['type'] ?? '');
							$item_name = is_object($item) ? ($item->name ?? '') : ($item['name'] ?? '');
							$item_label = is_object($item) ? ($item->label ?? '') : ($item['label'] ?? '');
							if (!empty($item_label) && in_array($item_type, array('file'), true)) {
								$wlgf_display_columns[] = array(
									'name' => $item_name,
									'label' => $item_label,
									'type' => $item_type,
								);
							}
						}

						// Build option value -> option label mapping for select, radio, checkbox fields
						$field_options_map = array();
						foreach ($structure as $item) {
							$item_name = is_object($item) ? ($item->name ?? '') : ($item['name'] ?? '');
							$item_label = is_object($item) ? ($item->label ?? '') : ($item['label'] ?? '');
							$item_values = is_object($item) ? ($item->values ?? array()) : ($item['values'] ?? array());

							if (!empty($item_values) && (is_array($item_values) || is_object($item_values))) {
								$map = array();
								foreach ($item_values as $opt) {
									$opt_label = is_object($opt) ? ($opt->label ?? '') : ($opt['label'] ?? '');
									$opt_value = is_object($opt) ? ($opt->value ?? '') : ($opt['value'] ?? '');
									if ('' !== (string) $opt_value && '' !== (string) $opt_label) {
										$map[(string) $opt_value] = (string) $opt_label;
									}
								}
								if (!empty($map)) {
									if (!empty($item_name)) {
										$field_options_map[$item_name] = $map;
									}
									if (!empty($item_label)) {
										$field_options_map[$item_label] = $map;
									}
								}
							}
						}
					}
					?>
					<!-- Leads Filter & Export Toolbar (PRO-008) -->
					<div class="wlgf-leads-toolbar wlgf-flex wlgf-flex--between wlgf-items-center wlgf-mb-3 wlgf-flex-wrap wlgf-gap-2">
						<div class="wlgf-btn-group">
							<button type="button"
								class="wlgf-btn wlgf-btn--sm <?php echo !$wlgf_newsletter_only ? 'wlgf-btn--primary' : 'wlgf-btn--outline'; ?> wlgf-filter-leads-btn"
								data-form-id="<?php echo esc_attr($wlgf_form_id); ?>" data-newsletter-only="0">
								<i class="fa-solid fa-users"></i> <?php esc_html_e('All Captured Leads', 'lead-generation-form'); ?>
							</button>
							<button type="button"
								class="wlgf-btn wlgf-btn--sm <?php echo $wlgf_newsletter_only ? 'wlgf-btn--primary' : 'wlgf-btn--outline'; ?> wlgf-filter-leads-btn"
								data-form-id="<?php echo esc_attr($wlgf_form_id); ?>" data-newsletter-only="1">
								<i class="fa-solid fa-paper-plane"></i>
								<?php esc_html_e('Newsletter Subscribers Only', 'lead-generation-form'); ?>
							</button>
						</div>
						<div class="wlgf-flex wlgf-items-center wlgf-gap-2">
							<button type="button" id="wlgf-delete-all" class="wlgf-btn wlgf-btn--sm wlgf-btn--danger"
								onclick="return WLGF('<?php echo esc_attr($wlgf_form_id); ?>', 'multiple');"
								title="<?php esc_attr_e('Delete Selected Leads', 'lead-generation-form'); ?>">
								<i class="fa-regular fa-trash-can"></i> <?php esc_html_e('Delete Selected', 'lead-generation-form'); ?>
							</button>
							<a href="<?php echo esc_url($wlgf_download_url); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary"
								title="<?php esc_attr_e('Export all captured form submissions with complete field data', 'lead-generation-form'); ?>">
								<i class="fa-solid fa-file-csv"></i>
								<?php esc_html_e('Export All Leads (CSV)', 'lead-generation-form'); ?>
							</a>
							<a href="<?php echo esc_url($wlgf_download_subscribers_url); ?>"
								class="wlgf-btn wlgf-btn--sm wlgf-btn--outline"
								title="<?php esc_attr_e('Export only contacts opted-in to the newsletter', 'lead-generation-form'); ?>">
								<i class="fa-solid fa-paper-plane"></i>
								<?php esc_html_e('Export Subscribers (CSV)', 'lead-generation-form'); ?>
							</a>
						</div>
					</div>

					<div class="wlgf-table-wrap">
						<table id="forms-tables" class="wlgf-table wlgf-w-full">
							<thead>
								<tr>
									<?php foreach ($wlgf_display_columns as $col): 
										$col_name = isset($col['name']) ? $col['name'] : '';
										$col_label = isset($col['label']) ? $col['label'] : '';
										$col_type = isset($col['type']) ? $col['type'] : '';
										$is_msg_col = (
											'textarea' === $col_type ||
											false !== stripos($col_name, 'message') ||
											false !== stripos($col_label, 'message') ||
											false !== stripos($col_name, 'comment') ||
											false !== stripos($col_label, 'comment')
										);
										$th_class = $is_msg_col ? ' class="wlgf-th-message"' : '';
									?>
										<th<?php echo $th_class; ?>><?php echo esc_html($col['label']); ?></th>
									<?php endforeach; ?>
									<th class="wlgf-th-datetime"><?php esc_html_e('Date Time', 'lead-generation-form'); ?></th>
									<th><?php esc_html_e('Newsletter', 'lead-generation-form'); ?></th>
									<th><?php esc_html_e('Payment', 'lead-generation-form'); ?></th>
									<th><?php esc_html_e('Status', 'lead-generation-form'); ?></th>
									<th><?php esc_html_e('Actions', 'lead-generation-form'); ?></th>
									<th>
										<input type="checkbox" id="wlgf-select-all" name="wlgf-select-all"
											title="<?php esc_attr_e('Select All', 'lead-generation-form'); ?>">
									</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (is_array($wlgf_form_data)) {
									$wlgf_data_counter = 0;
									// Show newest / recent leads first
									$wlgf_form_data = array_reverse($wlgf_form_data, true);
									foreach ($wlgf_form_data as $row_key => $row) {
										if (!is_array($row)) {
											continue;
										}
										if ($wlgf_newsletter_only && (empty($row['_newsletter_optin']) || 1 !== (int) $row['_newsletter_optin'])) {
											continue;
										}
										$lead_status = isset($row['_status']) ? strtolower(sanitize_text_field($row['_status'])) : 'new';
										if ('won' === $lead_status) {
											$lead_status = 'converted';
										} elseif ('lost' === $lead_status) {
											$lead_status = 'abandoned';
										} elseif ('review' === $lead_status) {
											$lead_status = 'followup';
										}
										$lead_notes = isset($row['_notes']) ? $row['_notes'] : array();
										$notes_count = is_array($lead_notes) ? count($lead_notes) : 0;
										$is_optin = (!empty($row['_newsletter_optin']) && 1 === (int) $row['_newsletter_optin']);
										$nl_status = isset($row['_newsletter_status']) ? sanitize_text_field($row['_newsletter_status']) : '';
										$nl_prov = isset($row['_newsletter_provider']) ? sanitize_text_field($row['_newsletter_provider']) : '';

										$pay_status = isset($row['wlgf_payment_status']) ? sanitize_text_field($row['wlgf_payment_status']) : '';
										$pay_tx = isset($row['wlgf_payment_transaction_id']) ? sanitize_text_field($row['wlgf_payment_transaction_id']) : '';
										$pay_amt = isset($row['wlgf_payment_amount_paid']) ? sanitize_text_field($row['wlgf_payment_amount_paid']) : '';
										$pay_gw = isset($row['wlgf_payment_gateway']) ? sanitize_text_field($row['wlgf_payment_gateway']) : '';
										$date_time = isset($row['Date Time']) ? $row['Date Time'] : (isset($row['date_time']) ? $row['date_time'] : 'N/A');
										?>
										<tr id="<?php echo esc_attr($row_key); ?>" data-lead-index="<?php echo esc_attr($row_key); ?>" data-status="<?php echo esc_attr($lead_status); ?>">
											<?php
											foreach ($wlgf_display_columns as $col) {
												$field_name = $col['name'];
												$field_label = $col['label'];
												$field_type = $col['type'];

												// Lookup by field name first, then fallback to field label
												$val = null;
												if (!empty($field_name) && array_key_exists($field_name, $row)) {
													$val = $row[$field_name];
												} elseif (!empty($field_label) && array_key_exists($field_label, $row)) {
													$val = $row[$field_label];
												}

												// Unslash escaped quotes (e.g. you\'re -> you're)
												if (is_string($val)) {
													$val = wp_unslash($val);
												}

												// Resolve Option Label instead of Option Value
												$opt_map = null;
												if (!empty($field_name) && isset($field_options_map[$field_name])) {
													$opt_map = $field_options_map[$field_name];
												} elseif (!empty($field_label) && isset($field_options_map[$field_label])) {
													$opt_map = $field_options_map[$field_label];
												}

												if ($opt_map && null !== $val && '' !== $val) {
													if (is_array($val)) {
														$mapped_vals = array();
														foreach ($val as $v) {
															$str_v = (string) $v;
															$mapped_vals[] = isset($opt_map[$str_v]) ? $opt_map[$str_v] : $v;
														}
														$val = $mapped_vals;
													} else {
														$str_val = (string) $val;
														if (isset($opt_map[$str_val])) {
															$val = $opt_map[$str_val];
														}
													}
												}

												if ('file' === $field_type) {
													echo '<td class="wlgf-col-file-cell">';
													if (!empty($val)) {
														$urls = is_array($val) ? $val : json_decode($val, true);
														if (!is_array($urls)) {
															if (is_string($val) && (false !== strpos($val, ',') || false !== strpos($val, "\n"))) {
																$urls = array_filter(array_map('trim', preg_split('/[,\n\r]+/', $val)));
															} else {
																$urls = array($val);
															}
														}
														if (!empty($urls)) {
															echo '<div class="wlgf-file-previews-wrap wlgf-flex wlgf-gap-1 wlgf-flex-wrap wlgf-items-center">';
															foreach ($urls as $file_url) {
																if (empty($file_url) || !is_string($file_url)) continue;
																$escaped_url = esc_url($file_url);
																$filename = basename(parse_url($file_url, PHP_URL_PATH));
																$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
																$is_img = in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'), true);
																$is_vid = in_array($ext, array('mp4', 'webm', 'ogg', 'mov', 'm4v'), true);
																$is_pdf = ('pdf' === $ext);

																if ($is_img) {
																	echo '<a href="' . $escaped_url . '" class="wlgf-lightbox-trigger wlgf-file-thumb-wrap" data-preview-type="image" data-file-url="' . $escaped_url . '" data-file-name="' . esc_attr($filename) . '" title="' . esc_attr($filename . ' - ' . __('Click to preview in lightbox', 'lead-generation-form')) . '">';
																	echo '<img src="' . $escaped_url . '" class="wlgf-lead-img-thumb" alt="' . esc_attr($filename) . '">';
																	echo '<span class="wlgf-thumb-zoom-icon"><i class="fa-solid fa-magnifying-glass-plus"></i></span>';
																	echo '</a>';
																} elseif ($is_vid) {
																	echo '<a href="' . $escaped_url . '" class="wlgf-lightbox-trigger wlgf-file-thumb-wrap wlgf-file-thumb-video" data-preview-type="video" data-file-url="' . $escaped_url . '" data-file-name="' . esc_attr($filename) . '" title="' . esc_attr($filename . ' - ' . __('Click to play recording in lightbox', 'lead-generation-form')) . '">';
																	echo '<div class="wlgf-thumb-video-placeholder"><i class="fa-solid fa-circle-play"></i><span class="wlgf-thumb-video-ext">' . esc_html(strtoupper($ext ? $ext : 'VID')) . '</span></div>';
																	echo '</a>';
																} elseif ($is_pdf) {
																	echo '<a href="' . $escaped_url . '" class="wlgf-lightbox-trigger wlgf-file-thumb-wrap wlgf-file-thumb-pdf" data-preview-type="pdf" data-file-url="' . $escaped_url . '" data-file-name="' . esc_attr($filename) . '" title="' . esc_attr($filename . ' - ' . __('Click to preview PDF in lightbox', 'lead-generation-form')) . '">';
																	echo '<div class="wlgf-thumb-pdf-placeholder"><i class="fa-solid fa-file-pdf"></i><span class="wlgf-thumb-pdf-ext">PDF</span></div>';
																	echo '</a>';
																} else {
																	echo '<a href="' . $escaped_url . '" class="wlgf-lightbox-trigger wlgf-file-thumb-wrap wlgf-file-thumb-doc" data-preview-type="file" data-file-url="' . $escaped_url . '" data-file-name="' . esc_attr($filename) . '" title="' . esc_attr($filename . ' - ' . __('Click to preview file', 'lead-generation-form')) . '">';
																	echo '<div class="wlgf-thumb-doc-placeholder"><i class="fa-solid fa-file-lines"></i><span class="wlgf-thumb-doc-ext">' . esc_html(strtoupper($ext ? $ext : 'FILE')) . '</span></div>';
																	echo '</a>';
																}
															}
															echo '</div>';
														} else {
															echo '<span class="wlgf-lead-empty-dash">—</span>';
														}
													} else {
														echo '<span class="wlgf-lead-empty-dash">—</span>';
													}
													echo '</td>';
												} elseif ('signature' === $field_type || (is_string($val) && 0 === strpos($val, 'data:image'))) {
													echo '<td class="wlgf-signature-cell">';
													if (!empty($val) && is_string($val) && 0 === strpos($val, 'data:image')) {
														$escaped_data_uri = esc_attr($val);
														echo '<div class="wlgf-signature-card" data-signature="' . $escaped_data_uri . '" title="' . esc_attr__('Click to view full digital signature', 'lead-generation-form') . '">';
														echo '<div class="wlgf-signature-img-wrap">';
														echo '<img src="' . $escaped_data_uri . '" class="wlgf-signature-thumb" alt="' . esc_attr__('Digital Signature', 'lead-generation-form') . '">';
														echo '</div>';
														echo '<span class="wlgf-signature-badge"><i class="fa-solid fa-signature"></i> ' . esc_html__('Signed', 'lead-generation-form') . '</span>';
														echo '</div>';
													} else {
														echo '<span class="wlgf-lead-empty-dash">—</span>';
													}
													echo '</td>';
												} elseif ('checkbox-group' === $field_type || is_array($val)) {
													if (is_array($val)) {
														$val_str = implode(' | ', $val);
														echo '<td>' . esc_html(!empty($val_str) ? $val_str : '—') . '</td>';
													} else {
														echo '<td>' . esc_html((null !== $val && '' !== $val) ? $val : '—') . '</td>';
													}
												} else {
													$is_message_or_long = (
														'textarea' === $field_type ||
														false !== stripos($field_name, 'message') ||
														false !== stripos($field_label, 'message') ||
														false !== stripos($field_name, 'comment') ||
														false !== stripos($field_label, 'comment') ||
														false !== stripos($field_name, 'detail') ||
														false !== stripos($field_label, 'detail') ||
														(is_string($val) && mb_strlen($val) > 40)
													);

													if ($is_message_or_long) {
														$str_val = (null !== $val && '' !== $val) ? trim((string) $val) : '';
														if ('' !== $str_val) {
															$is_truncated = (mb_strlen($str_val) > 40);
															$display_snippet = $is_truncated ? (mb_substr($str_val, 0, 38) . '…') : $str_val;
															?>
															<td class="wlgf-col-message-cell" data-search="<?php echo esc_attr($str_val); ?>" data-order="<?php echo esc_attr($str_val); ?>">
																<div class="wlgf-lead-msg-cell wlgf-view-lead-btn"
																	data-form-id="<?php echo esc_attr($wlgf_form_id); ?>"
																	data-lead-index="<?php echo esc_attr($row_key); ?>"
																	title="<?php echo esc_attr($str_val); ?>">
																	<span class="wlgf-lead-msg-text"><?php echo esc_html($display_snippet); ?></span>
																	<?php if ($is_truncated): ?>
																		<span class="wlgf-lead-msg-expand-hint" title="<?php esc_attr_e('Click to read full message in lead details', 'lead-generation-form'); ?>">
																			<i class="fa-solid fa-up-right-from-square"></i>
																		</span>
																	<?php endif; ?>
																</div>
															</td>
															<?php
														} else {
															echo '<td class="wlgf-col-message-cell"><span class="wlgf-lead-empty-dash">—</span></td>';
														}
													} else {
														echo '<td>' . esc_html((null !== $val && '' !== $val) ? $val : '—') . '</td>';
													}
												}
											}
											?>
												<?php
												$dt_ts = (!empty($date_time) && 'N/A' !== $date_time) ? strtotime($date_time) : 0;
												?>
												<td data-order="<?php echo esc_attr($dt_ts ? $dt_ts : 0); ?>">
													<?php
													if ($dt_ts) {
														echo '<span class="wlgf-lead-date-date">' . esc_html(date_i18n('M j, Y', $dt_ts)) . '</span>';
														echo '<div class="wlgf-lead-date-time">' . esc_html(date_i18n('g:i a', $dt_ts)) . '</div>';
													} else {
														echo '<span class="wlgf-lead-date-date">' . esc_html($date_time) . '</span>';
													}
													?>
												</td>
											<td class="text-center">
												<?php if ($is_optin): ?>
													<?php
													$tooltip = __('Opted-in to newsletter', 'lead-generation-form');
													if (!empty($nl_status)) {
														$tooltip .= ' (' . ucfirst($nl_prov) . ': ' . $nl_status . ')';
													}
													?>
													<span class="wlgf-badge wlgf-badge--success" title="<?php echo esc_attr($tooltip); ?>">
														<i class="fa-solid fa-check"></i> <?php esc_html_e('Yes', 'lead-generation-form'); ?>
													</span>
												<?php else: ?>
													<span class="wlgf-lead-empty-dash">—</span>
												<?php endif; ?>
											</td>
											<td>
												<?php if (!empty($pay_status) && 'completed' === $pay_status): ?>
													<span class="wlgf-badge wlgf-badge--success"
														title="<?php echo esc_attr('TX: ' . $pay_tx . ' via ' . ucfirst($pay_gw)); ?>">
														<i class="fa-solid fa-check"></i>
														<?php echo esc_html('$' . $pay_amt . ' ' . ucfirst($pay_gw)); ?>
													</span>
												<?php elseif (!empty($pay_status) && 'authorized' === $pay_status): ?>
													<span class="wlgf-badge wlgf-badge--primary"
														title="<?php echo esc_attr('TX: ' . $pay_tx . ' via ' . ucfirst($pay_gw)); ?>">
														<i class="fa-solid fa-credit-card"></i>
														<?php echo esc_html('$' . $pay_amt . ' ' . ucfirst($pay_gw)); ?>
													</span>
												<?php else: ?>
													<span class="wlgf-lead-empty-dash">—</span>
												<?php endif; ?>
											</td>
											<td>
												<select class="wlgf-status-select wlgf-lead-status-select" id="wlgf-status-select-<?php echo esc_attr($row_key); ?>" data-form-id="<?php echo esc_attr($wlgf_form_id); ?>"
													data-lead-index="<?php echo esc_attr($row_key); ?>">
													<option value="new" <?php selected($lead_status, 'new'); ?>>
														<?php esc_html_e('New Lead', 'lead-generation-form'); ?></option>
													<option value="contacted" <?php selected($lead_status, 'contacted'); ?>>
														<?php esc_html_e('Contacted', 'lead-generation-form'); ?></option>
													<option value="followup" <?php selected($lead_status, 'followup'); ?>>
														<?php esc_html_e('Follow-Up', 'lead-generation-form'); ?></option>
													<option value="qualified" <?php selected($lead_status, 'qualified'); ?>>
														<?php esc_html_e('Qualified', 'lead-generation-form'); ?></option>
													<option value="converted" <?php selected($lead_status, 'converted'); ?>>
														<?php esc_html_e('Won / Converted', 'lead-generation-form'); ?></option>
													<option value="abandoned" <?php selected($lead_status, 'abandoned'); ?>>
														<?php esc_html_e('Abandoned / Draft', 'lead-generation-form'); ?></option>
												</select>
											</td>
											<td>
												<div class="wlgf-btn-group">
													<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline wlgf-view-lead-btn"
														data-form-id="<?php echo esc_attr($wlgf_form_id); ?>"
														data-lead-index="<?php echo esc_attr($row_key); ?>"
														title="<?php esc_attr_e('View Lead Details', 'lead-generation-form'); ?>">
														<i class="fa-solid fa-eye wlgf-text-primary"></i>
													</button>
													<a href="<?php echo esc_url(add_query_arg(array('action' => 'wlgf_download_lead_pdf', 'form_id' => $wlgf_form_id, 'lead_index' => $row_key, '_wpnonce' => wp_create_nonce('wlgf-download-pdf-' . $wlgf_form_id . '-' . $row_key)), admin_url('admin-ajax.php'))); ?>"
														class="wlgf-btn wlgf-btn--sm wlgf-btn--outline"
														title="<?php echo ( defined('WLGF_IS_PRO') && WLGF_IS_PRO ) ? esc_attr__('Download PDF Summary / Invoice', 'lead-generation-form') : esc_attr__('Download PDF Summary [PRO Feature]', 'lead-generation-form'); ?>"
														target="_blank">
														<i class="fa-solid fa-file-pdf wlgf-text-danger"></i>
													</a>
													<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline wlgf-open-notes-btn"
														data-form-id="<?php echo esc_attr($wlgf_form_id); ?>"
														data-lead-index="<?php echo esc_attr($row_key); ?>"
														data-notes="<?php echo esc_attr(wp_json_encode($lead_notes)); ?>"
														title="<?php esc_attr_e('Internal Notes', 'lead-generation-form'); ?>">
														<i class="fa-regular fa-note-sticky"></i> <span
															class="wlgf-notes-count"><?php echo esc_html($notes_count); ?></span>
													</button>
													<button
														onclick="return WLGF('<?php echo esc_attr($wlgf_form_id); ?>', 'delete', '<?php echo esc_attr($row_key); ?>');"
														type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--danger"
														title="<?php esc_attr_e('Delete Entry', 'lead-generation-form'); ?>">
														<i class="fa-regular fa-trash-can"></i>
													</button>
												</div>
											</td>
											<td>
												<input type="checkbox" name="wlgf-lead-id" value="<?php echo esc_attr($row_key); ?>"
													title="<?php esc_attr_e('Select Lead', 'lead-generation-form'); ?>">
											</td>
										</tr>
										<?php
										$wlgf_data_counter++;
									}
								}
								?>
							</tbody>
						</table>
					</div>
					<?php
				} // end of load if

				// Delete Single Lead Row
				if ($wlgf_do === 'delete') {
					$row_id = isset($_POST['row_id']) ? sanitize_text_field(wp_unslash($_POST['row_id'])) : (isset($_POST['wlgf_row_id']) ? sanitize_text_field(wp_unslash($_POST['wlgf_row_id'])) : null);
					if (null !== $row_id) {
						$wlgf_saved_form_data = get_option('wlgf_saved_form_data_' . $wlgf_form_id);
						if (is_array($wlgf_saved_form_data)) {
							if (array_key_exists($row_id, $wlgf_saved_form_data)) {
								unset($wlgf_saved_form_data[$row_id]);
								update_option('wlgf_saved_form_data_' . $wlgf_form_id, $wlgf_saved_form_data);
							} elseif (is_numeric($row_id) && array_key_exists((int) $row_id, $wlgf_saved_form_data)) {
								unset($wlgf_saved_form_data[(int) $row_id]);
								update_option('wlgf_saved_form_data_' . $wlgf_form_id, $wlgf_saved_form_data);
							}
						}
					}
					wp_send_json_success(array('deleted' => $row_id));
				}

				// Delete Multiple Selected Leads
				if ($wlgf_do === 'multiple') {
					$raw_ids = isset($_POST['row_id']) ? $_POST['row_id'] : (isset($_POST['wlgf_row_id']) ? $_POST['wlgf_row_id'] : null);
					if (is_array($raw_ids)) {
						$unslashed_row_ids = wp_unslash($raw_ids);
						$wlgf_saved_form_data = get_option('wlgf_saved_form_data_' . $wlgf_form_id);
						if (is_array($wlgf_saved_form_data)) {
							foreach ($unslashed_row_ids as $row_id) {
								$sanitized_id = sanitize_text_field($row_id);
								if (array_key_exists($sanitized_id, $wlgf_saved_form_data)) {
									unset($wlgf_saved_form_data[$sanitized_id]);
								} elseif (is_numeric($sanitized_id) && array_key_exists((int) $sanitized_id, $wlgf_saved_form_data)) {
									unset($wlgf_saved_form_data[(int) $sanitized_id]);
								}
							}
							update_option('wlgf_saved_form_data_' . $wlgf_form_id, $wlgf_saved_form_data);
						}
					}
					wp_send_json_success(array('deleted' => $raw_ids));
				}
			}
		}
		wp_die();
	}
}
add_action('wp_ajax_wlgf_lead_loader', 'wlgf_lead_loader_callback');

// 3.01 Mini-CRM Lead Status Update (PRO-011)
function wlgf_update_lead_status_callback()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('Unauthorized access.', 'lead-generation-form')));
	}
	if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-leads')) {
		wp_send_json_error(array('message' => __('Security verification failed.', 'lead-generation-form')));
	}

	$form_id = isset($_POST['form_id']) ? sanitize_text_field(wp_unslash($_POST['form_id'])) : '';
	$raw_lead_index = isset($_POST['lead_index']) ? sanitize_text_field(wp_unslash($_POST['lead_index'])) : '';
	$new_status = isset($_POST['status']) ? strtolower(sanitize_text_field(wp_unslash($_POST['status']))) : 'new';

	if ('' === $form_id || '' === $raw_lead_index) {
		wp_send_json_error(array('message' => __('Invalid parameters.', 'lead-generation-form')));
	}

	// Status normalization
	if ('won' === $new_status) {
		$new_status = 'converted';
	} elseif ('lost' === $new_status) {
		$new_status = 'abandoned';
	} elseif ('review' === $new_status) {
		$new_status = 'followup';
	}

	$allowed_statuses = array('new', 'contacted', 'followup', 'qualified', 'converted', 'abandoned');
	if (!in_array($new_status, $allowed_statuses, true)) {
		$new_status = 'new';
	}

	$leads_data = get_option('wlgf_saved_form_data_' . $form_id, array());
	if (!is_array($leads_data)) {
		$leads_data = array();
	}

	// Check string key or integer key
	$target_key = null;
	if (isset($leads_data[$raw_lead_index])) {
		$target_key = $raw_lead_index;
	} elseif (is_numeric($raw_lead_index) && isset($leads_data[(int) $raw_lead_index])) {
		$target_key = (int) $raw_lead_index;
	}

	if (null !== $target_key) {
		$leads_data[$target_key]['_status'] = $new_status;
		update_option('wlgf_saved_form_data_' . $form_id, $leads_data);
		wp_send_json_success(array(
			'status' => $new_status,
			'lead_index' => $target_key,
			'message' => __('Lead status updated successfully.', 'lead-generation-form')
		));
	}
	wp_send_json_error(array('message' => __('Lead not found.', 'lead-generation-form')));
}
add_action('wp_ajax_wlgf_update_lead_status', 'wlgf_update_lead_status_callback');

// 3.02 Mini-CRM Internal Notes Handler (PRO-011)
function wlgf_add_lead_note_callback()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('Unauthorized access.', 'lead-generation-form')));
	}
	if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-leads')) {
		wp_send_json_error(array('message' => __('Security verification failed.', 'lead-generation-form')));
	}

	$form_id = isset($_POST['form_id']) ? sanitize_text_field(wp_unslash($_POST['form_id'])) : '';
	$lead_index = isset($_POST['lead_index']) ? intval($_POST['lead_index']) : -1;
	$note_text = isset($_POST['note']) ? sanitize_textarea_field(wp_unslash($_POST['note'])) : '';

	if (empty($form_id) || $lead_index < 0 || empty($note_text)) {
		wp_send_json_error(array('message' => __('Invalid parameters or empty note.', 'lead-generation-form')));
	}

	$leads_data = get_option('wlgf_saved_form_data_' . $form_id, array());
	if (isset($leads_data[$lead_index])) {
		if (!isset($leads_data[$lead_index]['_notes']) || !is_array($leads_data[$lead_index]['_notes'])) {
			$leads_data[$lead_index]['_notes'] = array();
		}
		$current_user = wp_get_current_user();
		$new_note = array(
			'text' => $note_text,
			'time' => current_time('mysql'),
			'user' => $current_user->display_name ? $current_user->display_name : 'Admin',
		);
		$leads_data[$lead_index]['_notes'][] = $new_note;
		update_option('wlgf_saved_form_data_' . $form_id, $leads_data);
		wp_send_json_success(array(
			'notes' => $leads_data[$lead_index]['_notes'],
			'count' => count($leads_data[$lead_index]['_notes']),
		));
	}
	wp_send_json_error(array('message' => __('Lead not found.', 'lead-generation-form')));
}
add_action('wp_ajax_wlgf_add_lead_note', 'wlgf_add_lead_note_callback');

// 3.03 Single Lead Details Preview Handler (Popup Modal)
function wlgf_get_lead_details_callback()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('Unauthorized access.', 'lead-generation-form')));
	}
	if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-leads')) {
		wp_send_json_error(array('message' => __('Security verification failed.', 'lead-generation-form')));
	}

	$form_id = isset($_POST['form_id']) ? sanitize_text_field(wp_unslash($_POST['form_id'])) : '';
	$raw_lead_index = isset($_POST['lead_index']) ? sanitize_text_field(wp_unslash($_POST['lead_index'])) : '';

	if ('' === $form_id || '' === $raw_lead_index) {
		wp_send_json_error(array('message' => __('Missing form ID or lead index.', 'lead-generation-form')));
	}

	$wlgf_form_body = get_option('wlgf_form_' . $form_id);
	$form_name = !empty($wlgf_form_body['form_title']) ? $wlgf_form_body['form_title'] : ('Form #' . $form_id);
	$wlgf_form = isset($wlgf_form_body['form']) ? $wlgf_form_body['form'] : '';
	$structure = is_array($wlgf_form) ? $wlgf_form : (is_string($wlgf_form) ? json_decode($wlgf_form) : array());

	$leads_data = get_option('wlgf_saved_form_data_' . $form_id, array());
	$lead = null;
	$resolved_key = null;
	if (isset($leads_data[$raw_lead_index])) {
		$lead = $leads_data[$raw_lead_index];
		$resolved_key = $raw_lead_index;
	} elseif (is_numeric($raw_lead_index) && isset($leads_data[(int) $raw_lead_index])) {
		$lead = $leads_data[(int) $raw_lead_index];
		$resolved_key = (int) $raw_lead_index;
	}

	if (!is_array($lead)) {
		wp_send_json_error(array('message' => __('Lead record not found.', 'lead-generation-form')));
	}

	// Build options map and field labels map
	$field_options_map = array();
	$field_labels_map = array();
	$field_types_map = array();
	if (is_array($structure)) {
		foreach ($structure as $item) {
			$item_name = is_object($item) ? ($item->name ?? '') : ($item['name'] ?? '');
			$item_label = is_object($item) ? ($item->label ?? '') : ($item['label'] ?? '');
			$item_type = is_object($item) ? ($item->type ?? '') : ($item['type'] ?? '');
			$item_values = is_object($item) ? ($item->values ?? array()) : ($item['values'] ?? array());

			if (!empty($item_name)) {
				$field_labels_map[$item_name] = !empty($item_label) ? $item_label : ucfirst(str_replace(array('-', '_'), ' ', $item_name));
				$field_types_map[$item_name] = $item_type;
			}
			if (!empty($item_label)) {
				$field_types_map[$item_label] = $item_type;
			}

			if (!empty($item_values) && (is_array($item_values) || is_object($item_values))) {
				$map = array();
				foreach ($item_values as $opt) {
					$opt_label = is_object($opt) ? ($opt->label ?? '') : ($opt['label'] ?? '');
					$opt_value = is_object($opt) ? ($opt->value ?? '') : ($opt['value'] ?? '');
					if ('' !== (string) $opt_value && '' !== (string) $opt_label) {
						$map[(string) $opt_value] = (string) $opt_label;
					}
				}
				if (!empty($map)) {
					if (!empty($item_name)) $field_options_map[$item_name] = $map;
					if (!empty($item_label)) $field_options_map[$item_label] = $map;
				}
			}
		}
	}

	// Extract standard lead metadata
	$lead_status = isset($lead['_status']) ? strtolower(sanitize_text_field($lead['_status'])) : 'new';
	if ('won' === $lead_status) $lead_status = 'converted';
	elseif ('lost' === $lead_status) $lead_status = 'abandoned';
	elseif ('review' === $lead_status) $lead_status = 'followup';

	$lead_date = isset($lead['Date Time']) ? $lead['Date Time'] : (isset($lead['date_time']) ? $lead['date_time'] : '');
	$lead_notes = isset($lead['_notes']) && is_array($lead['_notes']) ? $lead['_notes'] : array();

	// Extract client contact info
	$client_name = '';
	$client_email = '';
	$client_phone = '';
	foreach ($lead as $k => $v) {
		if (0 === strpos($k, '_')) continue;
		$k_low = strtolower($k);
		if (empty($client_name) && (false !== strpos($k_low, 'name') || 'client' === $k_low || 'customer' === $k_low || 'member' === $k_low) && is_string($v) && !empty($v)) {
			$client_name = $v;
		}
		if (empty($client_email) && false !== strpos($k_low, 'email') && is_string($v) && is_email($v)) {
			$client_email = $v;
		}
		if (empty($client_phone) && (false !== strpos($k_low, 'phone') || false !== strpos($k_low, 'tel') || false !== strpos($k_low, 'mobile')) && is_string($v) && !empty($v)) {
			$client_phone = $v;
		}
	}
	if (empty($client_name)) {
		$client_name = __('Lead #', 'lead-generation-form') . ($resolved_key + 1);
	}

	$pdf_url = add_query_arg(
		array(
			'action'     => 'wlgf_download_lead_pdf',
			'form_id'    => $form_id,
			'lead_index' => $resolved_key,
			'_wpnonce'   => wp_create_nonce('wlgf-download-pdf-' . $form_id . '-' . $resolved_key),
		),
		admin_url('admin-ajax.php')
	);

	// Render details HTML
	ob_start();
	?>
	<div class="wlgf-lead-detail-wrap">
		<!-- Summary Header Card -->
		<div class="wlgf-lead-summary-card">
			<div class="wlgf-lead-summary-avatar">
				<i class="fa-solid fa-user"></i>
			</div>
			<div class="wlgf-lead-summary-info">
				<div class="wlgf-lead-summary-name-row">
					<h3 class="wlgf-lead-summary-name"><?php echo esc_html($client_name); ?></h3>
					<span class="wlgf-badge wlgf-badge--<?php echo esc_attr($lead_status); ?> wlgf-badge--lg" id="wlgf-detail-current-status-badge">
						<?php echo esc_html(strtoupper($lead_status)); ?>
					</span>
				</div>
				<div class="wlgf-lead-summary-meta">
					<span><i class="fa-regular fa-file-lines"></i> <?php echo esc_html($form_name); ?></span>
					<?php if (!empty($lead_date)): ?>
						<span><i class="fa-regular fa-clock"></i> <?php echo esc_html($lead_date); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="wlgf-lead-summary-actions">
				<?php if (!empty($client_phone)): 
					$wa_clean = preg_replace('/[^0-9]/', '', $client_phone);
				?>
					<a href="https://wa.me/<?php echo esc_attr($wa_clean); ?>" target="_blank" class="wlgf-btn wlgf-btn--sm wlgf-btn--success" title="<?php esc_attr_e('WhatsApp Chat', 'lead-generation-form'); ?>">
						<i class="fa-brands fa-whatsapp"></i> <?php esc_html_e('WhatsApp', 'lead-generation-form'); ?>
					</a>
					<a href="tel:<?php echo esc_attr($client_phone); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline" title="<?php esc_attr_e('Call', 'lead-generation-form'); ?>">
						<i class="fa-solid fa-phone"></i> <?php esc_html_e('Call', 'lead-generation-form'); ?>
					</a>
				<?php endif; ?>
				<?php if (!empty($client_email)): ?>
					<a href="mailto:<?php echo esc_attr($client_email); ?>" class="wlgf-btn wlgf-btn--sm wlgf-btn--outline" title="<?php esc_attr_e('Email', 'lead-generation-form'); ?>">
						<i class="fa-regular fa-envelope"></i> <?php esc_html_e('Email', 'lead-generation-form'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Status Changer & Quick Metadata Bar -->
		<div class="wlgf-lead-quick-bar">
			<div class="wlgf-lead-quick-status">
				<label for="wlgf-detail-status-select"><strong><i class="fa-solid fa-chart-gantt"></i> <?php esc_html_e('Lead Pipeline Stage:', 'lead-generation-form'); ?></strong></label>
				<select id="wlgf-detail-status-select" class="wlgf-select wlgf-select--sm" data-form-id="<?php echo esc_attr($form_id); ?>" data-lead-index="<?php echo esc_attr($resolved_key); ?>">
					<option value="new" <?php selected($lead_status, 'new'); ?>><?php esc_html_e('New Lead', 'lead-generation-form'); ?></option>
					<option value="contacted" <?php selected($lead_status, 'contacted'); ?>><?php esc_html_e('Contacted', 'lead-generation-form'); ?></option>
					<option value="followup" <?php selected($lead_status, 'followup'); ?>><?php esc_html_e('Follow-Up', 'lead-generation-form'); ?></option>
					<option value="qualified" <?php selected($lead_status, 'qualified'); ?>><?php esc_html_e('Qualified', 'lead-generation-form'); ?></option>
					<option value="converted" <?php selected($lead_status, 'converted'); ?>><?php esc_html_e('Won / Converted', 'lead-generation-form'); ?></option>
					<option value="abandoned" <?php selected($lead_status, 'abandoned'); ?>><?php esc_html_e('Abandoned / Draft', 'lead-generation-form'); ?></option>
				</select>
			</div>
			<div class="wlgf-lead-quick-tags">
				<?php if (!empty($lead['_newsletter_optin']) && 1 === (int) $lead['_newsletter_optin']): ?>
					<span class="wlgf-badge wlgf-badge--success"><i class="fa-solid fa-paper-plane"></i> <?php esc_html_e('Newsletter Subscriber', 'lead-generation-form'); ?></span>
				<?php endif; ?>
				<?php if (!empty($lead['wlgf_payment_status'])): ?>
					<span class="wlgf-badge wlgf-badge--primary"><i class="fa-solid fa-credit-card"></i> <?php echo esc_html(strtoupper($lead['wlgf_payment_status'])); ?> (<?php echo esc_html(!empty($lead['wlgf_payment_amount_paid']) ? '$' . $lead['wlgf_payment_amount_paid'] : ''); ?>)</span>
				<?php endif; ?>
			</div>
		</div>

		<!-- Form Field Responses Table -->
		<div class="wlgf-lead-detail-section">
			<h4 class="wlgf-lead-section-heading">
				<i class="fa-solid fa-list-check wlgf-text-primary"></i> <?php esc_html_e('Form Submission Answers', 'lead-generation-form'); ?>
			</h4>
			<div class="wlgf-lead-fields-table-wrap">
				<table class="wlgf-lead-fields-table">
					<tbody>
						<?php
						$skip_keys = array( 'wlgf_form_id', 'wlgf_form_load_time', 'wlgf_honeypot', 'wlgf_hp_website_confirm', 'cf-turnstile-response', 'wlgf_nonce', 'wlgf_draft_token', 'draft_token', 'action', '_wpnonce', '_wp_http_referer', 'wlgf_carbon_copy', 'Date Time', 'date_time', 'wlgf_payment_status', 'wlgf_payment_transaction_id', 'wlgf_payment_amount_paid', 'wlgf_payment_gateway', '_status', '_notes', '_newsletter_optin', '_newsletter_status', '_newsletter_provider' );
						foreach ($lead as $raw_k => $raw_v):
							if (in_array($raw_k, $skip_keys, true) || 0 === strpos($raw_k, '_')) continue;

							$display_label = isset($field_labels_map[$raw_k]) ? $field_labels_map[$raw_k] : ucfirst(str_replace(array('-', '_'), ' ', $raw_k));
							$field_type = isset($field_types_map[$raw_k]) ? $field_types_map[$raw_k] : (isset($field_types_map[$display_label]) ? $field_types_map[$display_label] : '');

							// Option value mapping
							$opt_map = isset($field_options_map[$raw_k]) ? $field_options_map[$raw_k] : (isset($field_options_map[$display_label]) ? $field_options_map[$display_label] : null);
							if ($opt_map && null !== $raw_v && '' !== $raw_v) {
								if (is_array($raw_v)) {
									$mapped = array();
									foreach ($raw_v as $sub_v) {
										$mapped[] = isset($opt_map[(string) $sub_v]) ? $opt_map[(string) $sub_v] : $sub_v;
									}
									$raw_v = $mapped;
								} elseif (isset($opt_map[(string) $raw_v])) {
									$raw_v = $opt_map[(string) $raw_v];
								}
							}
						?>
							<tr>
								<td class="wlgf-field-lbl" style="vertical-align: top; line-height: 1.5;"><?php echo esc_html($display_label); ?></td>
								<td class="wlgf-field-val" style="vertical-align: top; line-height: 1.5; white-space: normal;"><?php
									if ('signature' === $field_type || (is_string($raw_v) && 0 === strpos($raw_v, 'data:image'))) {
										$data_uri = esc_attr($raw_v);
										echo '<div class="wlgf-detail-signature-box">';
										echo '<img src="' . $data_uri . '" alt="' . esc_attr__('Signature', 'lead-generation-form') . '" class="wlgf-detail-signature-img">';
										echo '<div class="wlgf-detail-signature-footer"><i class="fa-solid fa-circle-check"></i> ' . esc_html__('Verified Digital Signature', 'lead-generation-form') . '</div>';
										echo '</div>';
									} elseif ('file' === $field_type || (is_string($raw_v) && (false !== strpos($raw_v, 'http://') || false !== strpos($raw_v, 'https://')) && preg_match('/\.(jpg|jpeg|png|gif|webp|pdf|doc|docx|mp4|webm|mov)$/i', $raw_v))) {
										$files = is_array($raw_v) ? $raw_v : json_decode($raw_v, true);
										if (!is_array($files)) {
											if (is_string($raw_v) && (false !== strpos($raw_v, ',') || false !== strpos($raw_v, "\n"))) {
												$files = array_filter(array_map('trim', preg_split('/[,\n\r]+/', $raw_v)));
											} else {
												$files = array($raw_v);
											}
										}
										echo '<div class="wlgf-flex wlgf-gap-2 wlgf-flex-wrap wlgf-items-center">';
										foreach ($files as $f_url) {
											if (empty($f_url) || !is_string($f_url)) continue;
											$esc_f = esc_url($f_url);
											$filename = basename(parse_url($esc_f, PHP_URL_PATH));
											$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
											$is_img = in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'), true);
											$is_vid = in_array($ext, array('mp4', 'webm', 'ogg', 'mov', 'm4v'), true);
											$is_pdf = ('pdf' === $ext);

											if ($is_img) {
												echo '<a href="' . $esc_f . '" class="wlgf-lightbox-trigger wlgf-file-thumb-wrap" data-preview-type="image" data-file-url="' . $esc_f . '" data-file-name="' . esc_attr($filename) . '" title="' . esc_attr($filename . ' - ' . __('Click to preview in lightbox', 'lead-generation-form')) . '"><img src="' . $esc_f . '" class="wlgf-lead-img-thumb" alt="' . esc_attr($filename) . '"><span class="wlgf-thumb-zoom-icon"><i class="fa-solid fa-magnifying-glass-plus"></i></span></a>';
											} elseif ($is_vid) {
												echo '<a href="' . $esc_f . '" class="wlgf-lightbox-trigger wlgf-btn wlgf-btn--sm wlgf-btn--outline" data-preview-type="video" data-file-url="' . $esc_f . '" data-file-name="' . esc_attr($filename) . '"><i class="fa-solid fa-video"></i> ' . esc_html($filename) . '</a>';
											} elseif ($is_pdf) {
												echo '<a href="' . $esc_f . '" class="wlgf-lightbox-trigger wlgf-btn wlgf-btn--sm wlgf-btn--outline" data-preview-type="pdf" data-file-url="' . $esc_f . '" data-file-name="' . esc_attr($filename) . '"><i class="fa-solid fa-file-pdf wlgf-text-danger"></i> ' . esc_html($filename) . '</a>';
											} else {
												echo '<a href="' . $esc_f . '" class="wlgf-lightbox-trigger wlgf-btn wlgf-btn--sm wlgf-btn--outline" data-preview-type="file" data-file-url="' . $esc_f . '" data-file-name="' . esc_attr($filename) . '"><i class="fa-solid fa-paperclip"></i> ' . esc_html($filename) . '</a>';
											}
										}
										echo '</div>';
									} elseif (is_array($raw_v)) {
										echo '<div class="wlgf-flex wlgf-gap-1 wlgf-flex-wrap">';
										foreach ($raw_v as $item_val) {
											echo '<span class="wlgf-badge wlgf-badge--secondary">' . esc_html($item_val) . '</span> ';
										}
										echo '</div>';
									} elseif (null === $raw_v || '' === trim((string) $raw_v)) {
										echo '<span class="wlgf-text-muted">—</span>';
									} else {
										echo nl2br(esc_html(is_string($raw_v) ? trim(wp_unslash($raw_v)) : $raw_v));
									}
								?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Internal Notes Box Inside Lead Details -->
		<div class="wlgf-lead-detail-section">
			<h4 class="wlgf-lead-section-heading">
				<i class="fa-regular fa-note-sticky wlgf-text-primary"></i> <?php esc_html_e('Internal Follow-up Notes', 'lead-generation-form'); ?>
			</h4>
			<div class="wlgf-detail-notes-list" id="wlgf-detail-modal-notes-list">
				<?php if (empty($lead_notes)): ?>
					<p class="wlgf-text-muted wlgf-m-0 wlgf-text-sm" id="wlgf-detail-no-notes-msg"><?php esc_html_e('No internal notes recorded yet for this lead.', 'lead-generation-form'); ?></p>
				<?php else: ?>
					<?php foreach ($lead_notes as $note): ?>
						<div class="wlgf-note-item">
							<div class="wlgf-note-item__meta">
								<span><i class="fa-regular fa-user"></i> <?php echo esc_html(!empty($note['user']) ? $note['user'] : 'Admin'); ?></span>
								<span><i class="fa-regular fa-clock"></i> <?php echo esc_html(!empty($note['time']) ? $note['time'] : ''); ?></span>
							</div>
							<div class="wlgf-note-item__body"><?php echo esc_html(!empty($note['text']) ? $note['text'] : ''); ?></div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="wlgf-detail-add-note wlgf-mt-2">
				<div class="wlgf-flex wlgf-gap-2">
					<input type="text" id="wlgf-detail-quick-note-input" class="wlgf-input" placeholder="<?php esc_attr_e('Add quick follow-up note...', 'lead-generation-form'); ?>">
					<button type="button" id="wlgf-detail-quick-note-btn" class="wlgf-btn wlgf-btn--primary" data-form-id="<?php echo esc_attr($form_id); ?>" data-lead-index="<?php echo esc_attr($resolved_key); ?>">
						<i class="fa-solid fa-plus"></i> <?php esc_html_e('Add', 'lead-generation-form'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php
	$html = ob_get_clean();

	$all_lead_keys = array_keys(is_array($leads_data) ? $leads_data : array());
	$current_pos = false;
	foreach ($all_lead_keys as $idx => $k) {
		if ((string) $k === (string) $resolved_key) {
			$current_pos = $idx;
			break;
		}
	}
	$prev_key = ($current_pos !== false && $current_pos > 0) ? $all_lead_keys[$current_pos - 1] : null;
	$next_key = ($current_pos !== false && $current_pos < count($all_lead_keys) - 1) ? $all_lead_keys[$current_pos + 1] : null;

	wp_send_json_success(array(
		'html' => $html,
		'pdf_url' => $pdf_url,
		'client_name' => $client_name,
		'lead_index' => $resolved_key,
		'status' => $lead_status,
		'form_name' => $form_name,
		'prev_lead_index' => $prev_key,
		'next_lead_index' => $next_key,
		'current_lead_num' => ($current_pos !== false) ? ($current_pos + 1) : null,
		'total_leads' => count($all_lead_keys),
	));
}
add_action('wp_ajax_wlgf_get_lead_details', 'wlgf_get_lead_details_callback');

// 3.1 Streamed on-demand CSV Export handler
function wlgf_export_leads_csv_callback()
{
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('Unauthorized access.', 'lead-generation-form'));
	}
	$wlgf_form_id = isset($_GET['wlgf_form_id']) ? sanitize_text_field(wp_unslash($_GET['wlgf_form_id'])) : '';
	if (empty($wlgf_form_id) || !isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'wlgf-export-csv-' . $wlgf_form_id)) {
		wp_die(esc_html__('Security check failed.', 'lead-generation-form'));
	}

	$wlgf_form_body = get_option('wlgf_form_' . $wlgf_form_id);
	$wlgf_form_name = isset($wlgf_form_body['form_name']) ? $wlgf_form_body['form_name'] : 'form';
	$wlgf_form = isset($wlgf_form_body['form']) ? $wlgf_form_body['form'] : '';
	$structure = is_array($wlgf_form) ? $wlgf_form : (is_string($wlgf_form) ? json_decode($wlgf_form) : array());

	$wlgf_form_data = get_option('wlgf_saved_form_data_' . $wlgf_form_id);
	$wlgf_newsletter_only = (isset($_GET['newsletter_only']) && '1' === (string) $_GET['newsletter_only']);

	if (ob_get_level()) {
		ob_end_clean();
	}

	$file_suffix = $wlgf_newsletter_only ? '-subscribers-' : '-leads-';
	$filename = sanitize_file_name($wlgf_form_name) . $file_suffix . gmdate('Y-m-d') . '.csv';
	header('Content-Type: text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Pragma: no-cache');
	header('Expires: 0');

	$output = fopen('php://output', 'w');
	// UTF-8 BOM for Excel
	fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

	$wlgf_display_columns = array();
	if (is_array($structure)) {
		foreach ($structure as $item) {
			$item_type = is_object($item) ? ($item->type ?? '') : ($item['type'] ?? '');
			$item_name = is_object($item) ? ($item->name ?? '') : ($item['name'] ?? '');
			$item_label = is_object($item) ? ($item->label ?? '') : ($item['label'] ?? '');
			if (!empty($item_label) && !in_array($item_type, array('header', 'button', 'paragraph', 'hidden', 'divider', 'step-break', 'file'), true)) {
				$wlgf_display_columns[] = array(
					'name' => $item_name,
					'label' => $item_label,
					'type' => $item_type,
				);
			}
		}
		foreach ($structure as $item) {
			$item_type = is_object($item) ? ($item->type ?? '') : ($item['type'] ?? '');
			$item_name = is_object($item) ? ($item->name ?? '') : ($item['name'] ?? '');
			$item_label = is_object($item) ? ($item->label ?? '') : ($item['label'] ?? '');
			if (!empty($item_label) && in_array($item_type, array('file'), true)) {
				$wlgf_display_columns[] = array(
					'name' => $item_name,
					'label' => $item_label,
					'type' => $item_type,
				);
			}
		}
		// Build option value -> option label mapping
		$field_options_map = array();
		foreach ($structure as $item) {
			$item_name = is_object($item) ? ($item->name ?? '') : ($item['name'] ?? '');
			$item_label = is_object($item) ? ($item->label ?? '') : ($item['label'] ?? '');
			$item_values = is_object($item) ? ($item->values ?? array()) : ($item['values'] ?? array());

			if (!empty($item_values) && (is_array($item_values) || is_object($item_values))) {
				$map = array();
				foreach ($item_values as $opt) {
					$opt_label = is_object($opt) ? ($opt->label ?? '') : ($opt['label'] ?? '');
					$opt_value = is_object($opt) ? ($opt->value ?? '') : ($opt['value'] ?? '');
					if ('' !== (string) $opt_value && '' !== (string) $opt_label) {
						$map[(string) $opt_value] = (string) $opt_label;
					}
				}
				if (!empty($map)) {
					if (!empty($item_name)) {
						$field_options_map[$item_name] = $map;
					}
					if (!empty($item_label)) {
						$field_options_map[$item_label] = $map;
					}
				}
			}
		}
	}

	$headers = array();
	foreach ($wlgf_display_columns as $col) {
		$headers[] = $col['label'];
	}
	$headers[] = __('Date Time', 'lead-generation-form');
	$headers[] = __('Newsletter Opt-in', 'lead-generation-form');
	$headers[] = __('Payment Status', 'lead-generation-form');
	$headers[] = __('Transaction ID', 'lead-generation-form');
	$headers[] = __('Amount Paid', 'lead-generation-form');
	$headers[] = __('Lead Status', 'lead-generation-form');
	fputcsv($output, $headers);

	if (is_array($wlgf_form_data)) {
		// Pro version: unlimited leads exported, recent first
		$wlgf_form_data = array_reverse($wlgf_form_data, true);
		foreach ($wlgf_form_data as $row) {
			if (!is_array($row)) {
				continue;
			}
			if ($wlgf_newsletter_only && (empty($row['_newsletter_optin']) || 1 !== (int) $row['_newsletter_optin'])) {
				continue;
			}
			$csv_row = array();
			foreach ($wlgf_display_columns as $col) {
				$field_name = $col['name'];
				$field_label = $col['label'];
				$val = null;
				if (!empty($field_name) && array_key_exists($field_name, $row)) {
					$val = $row[$field_name];
				} elseif (!empty($field_label) && array_key_exists($field_label, $row)) {
					$val = $row[$field_label];
				}

				// Resolve Option Label instead of Option Value
				$opt_map = null;
				if (!empty($field_name) && isset($field_options_map[$field_name])) {
					$opt_map = $field_options_map[$field_name];
				} elseif (!empty($field_label) && isset($field_options_map[$field_label])) {
					$opt_map = $field_options_map[$field_label];
				}

				if ($opt_map && null !== $val && '' !== $val) {
					if (is_array($val)) {
						$mapped_vals = array();
						foreach ($val as $v) {
							$str_v = (string) $v;
							$mapped_vals[] = isset($opt_map[$str_v]) ? $opt_map[$str_v] : $v;
						}
						$val = $mapped_vals;
					} else {
						$str_val = (string) $val;
						if (isset($opt_map[$str_val])) {
							$val = $opt_map[$str_val];
						}
					}
				}

				if ('signature' === $col['type'] || (is_string($val) && 0 === strpos($val, 'data:image'))) {
					$csv_row[] = (!empty($val) && is_string($val) && 0 === strpos($val, 'data:image')) ? __('Yes (Digitally Signed)', 'lead-generation-form') : __('No', 'lead-generation-form');
				} elseif (is_array($val)) {
					$csv_row[] = implode(' | ', $val);
				} else {
					$csv_row[] = (null !== $val && '' !== $val) ? $val : '';
				}
			}
			$csv_row[] = isset($row['Date Time']) ? $row['Date Time'] : (isset($row['date_time']) ? $row['date_time'] : '');
			$is_optin = (!empty($row['_newsletter_optin']) && 1 === (int) $row['_newsletter_optin']);
			$csv_row[] = $is_optin ? __('Yes (Opted-in)', 'lead-generation-form') : __('No', 'lead-generation-form');
			$csv_row[] = !empty($row['wlgf_payment_status']) ? $row['wlgf_payment_status'] : 'N/A';
			$csv_row[] = !empty($row['wlgf_payment_transaction_id']) ? $row['wlgf_payment_transaction_id'] : '';
			$csv_row[] = !empty($row['wlgf_payment_amount_paid']) ? $row['wlgf_payment_amount_paid'] : '';
			$csv_row[] = isset($row['_status']) ? $row['_status'] : 'new';

			$escaped_csv_row = array();
			foreach ($csv_row as $cell) {
				$cell_str = is_scalar($cell) ? (string) $cell : '';
				if (preg_match('/^[=+\-@\t\r]/', $cell_str)) {
					$cell_str = "'" . $cell_str;
				}
				$escaped_csv_row[] = $cell_str;
			}
			fputcsv($output, $escaped_csv_row);
		}
	}

	fclose($output);
	exit;
}
add_action('wp_ajax_wlgf_export_leads_csv', 'wlgf_export_leads_csv_callback');
// 3. all leads page action end - load + file, delete, multiple delete [end]

// 4. import export form start
function wlgf_import_export_callback()
{
	if (current_user_can('manage_options')) {
		if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-import-export')) {
			$wlgf_do_action = isset($_POST['do']) ? sanitize_text_field(wp_unslash($_POST['do'])) : '';

			// Export Form
			if ($wlgf_do_action === 'export') {
				$wlgf_form_id = isset($_POST['wlgf_form_id']) ? sanitize_text_field(wp_unslash($_POST['wlgf_form_id'])) : '';
				$wlgf_form_details = get_option('wlgf_form_' . $wlgf_form_id);
				if (!empty($wlgf_form_details) && isset($wlgf_form_details['form'])) {
					$form_payload = is_array($wlgf_form_details['form']) ? wp_json_encode($wlgf_form_details['form'], JSON_PRETTY_PRINT) : $wlgf_form_details['form'];
					echo '<textarea id="wlgf-form-code" class="wlgf-code-box" rows="7" readonly>' . esc_textarea($form_payload) . '</textarea>';
				} else {
					echo '<div class="wlgf-alert wlgf-alert--danger">' . esc_html__('Error: Form data not found.', 'lead-generation-form') . '</div>';
				}
				wp_die();
			}

			// Import Form
			if ($wlgf_do_action === 'import') {
				$wlgf_form_name = isset($_POST['wlgf_form_name']) ? sanitize_text_field(wp_unslash($_POST['wlgf_form_name'])) : '';
				$raw_json = isset($_POST['wlgf_form_data']) ? wp_unslash($_POST['wlgf_form_data']) : '';
				$decoded = json_decode($raw_json, true);

				if (empty($decoded) || !is_array($decoded)) {
					wp_send_json_error(array('message' => __('Invalid JSON format. Please paste valid form schema JSON.', 'lead-generation-form')));
				}

				$allowed_types = array('text', 'number', 'date', 'file', 'textarea', 'select', 'checkbox-group', 'radio-group', 'header', 'paragraph', 'hidden', 'button', 'autocomplete', 'divider', 'terms');
				if ( defined( 'WLGF_IS_PRO' ) && WLGF_IS_PRO ) {
					$allowed_types = array_merge( $allowed_types, array( 'step-break', 'star-rating', 'nps-matrix', 'calculation', 'signature' ) );
				}
				$valid_fields = array();
				foreach ($decoded as $field) {
					if (is_array($field) && isset($field['type']) && in_array($field['type'], $allowed_types, true)) {
						$valid_fields[] = $field;
					}
				}

				if (empty($valid_fields)) {
					wp_send_json_error(array('message' => __('No valid form fields found in the imported JSON payload.', 'lead-generation-form')));
				}

				$wlgf_form_id = wlgf_get_next_id();
				$wlgf_form_details = array(
					'form_id' => $wlgf_form_id,
					'form_name' => $wlgf_form_name ? $wlgf_form_name : ('Imported Form #' . $wlgf_form_id),
					'form' => wp_json_encode($valid_fields)
				);

				update_option('wlgf_form_' . $wlgf_form_id, $wlgf_form_details);
				wp_send_json_success(array('message' => sprintf(__('Form "%s" (ID: #%s) imported successfully!', 'lead-generation-form'), esc_html($wlgf_form_details['form_name']), esc_html($wlgf_form_id))));
			}

			// Combine Forms
			if ($wlgf_do_action === 'combine') {
				$wlgf_form_one_id = isset($_POST['wlgf_form_one_id']) ? sanitize_text_field(wp_unslash($_POST['wlgf_form_one_id'])) : '';
				$wlgf_form_two_id = isset($_POST['wlgf_form_two_id']) ? sanitize_text_field(wp_unslash($_POST['wlgf_form_two_id'])) : '';

				$wlgf_form_one = get_option('wlgf_form_' . $wlgf_form_one_id);
				$wlgf_form_two = get_option('wlgf_form_' . $wlgf_form_two_id);

				if (empty($wlgf_form_one) || empty($wlgf_form_two)) {
					wp_send_json_error(array('message' => __('Please select two valid forms to combine.', 'lead-generation-form')));
				}

				$raw_one = isset($wlgf_form_one['form']) ? $wlgf_form_one['form'] : array();
				$raw_two = isset($wlgf_form_two['form']) ? $wlgf_form_two['form'] : array();
				$form_one_data = is_array($raw_one) ? $raw_one : (is_string($raw_one) ? json_decode($raw_one, true) : array());
				$form_two_data = is_array($raw_two) ? $raw_two : (is_string($raw_two) ? json_decode($raw_two, true) : array());

				if (!is_array($form_one_data)) {
					$form_one_data = array();
				}
				if (!is_array($form_two_data)) {
					$form_two_data = array();
				}

				$combined_data = array_merge($form_one_data, $form_two_data);
				$wlgf_form_id_new = wlgf_get_next_id();
				$wlgf_form_name_new = sanitize_text_field($wlgf_form_one['form_name'] . ' & ' . $wlgf_form_two['form_name'] . ' - [Combined]');

				$wlgf_form_details_new = array(
					'form_id' => $wlgf_form_id_new,
					'form_name' => $wlgf_form_name_new,
					'form' => wp_json_encode($combined_data)
				);

				update_option('wlgf_form_' . $wlgf_form_id_new, $wlgf_form_details_new);
				wp_send_json_success(array('message' => sprintf(__('Combined form "%s" (ID: #%s) created successfully!', 'lead-generation-form'), esc_html($wlgf_form_name_new), esc_html($wlgf_form_id_new))));
			}
		}
		wp_die();
	}
}
add_action('wp_ajax_wlgf_import_export', 'wlgf_import_export_callback');
// 4. import export form end

// 5. save settings start
function wlgf_save_settings_callback()
{
	if (current_user_can('manage_options')) {
		if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-save-settings')) {
			$existing_settings = get_option('wlgf_settings', array());

			$wlgf_recaptcha = isset($_POST['recaptcha']) ? sanitize_text_field(wp_unslash($_POST['recaptcha'])) : '2';
			$wlgf_captcha_provider = isset($_POST['captcha_provider']) ? sanitize_text_field(wp_unslash($_POST['captcha_provider'])) : '';
			if (empty($wlgf_captcha_provider)) {
				$wlgf_captcha_provider = ('1' === (string)$wlgf_recaptcha) ? 'recaptcha' : (('3' === (string)$wlgf_recaptcha) ? 'turnstile' : 'none');
			}
			$wlgf_sitekey = isset($_POST['sitekey']) ? sanitize_text_field(wp_unslash($_POST['sitekey'])) : '';
			$wlgf_secretkey = isset($_POST['secretkey']) ? sanitize_text_field(wp_unslash($_POST['secretkey'])) : '';
			if ((empty($wlgf_secretkey) || '••••••••••••' === $wlgf_secretkey) && !empty($existing_settings['secretkey'])) {
				$wlgf_secretkey = $existing_settings['secretkey'];
			}

			// Cloudflare Turnstile keys
			$wlgf_turnstile_sitekey = isset($_POST['turnstile_sitekey']) ? sanitize_text_field(wp_unslash($_POST['turnstile_sitekey'])) : '';
			$wlgf_turnstile_secretkey = isset($_POST['turnstile_secretkey']) ? sanitize_text_field(wp_unslash($_POST['turnstile_secretkey'])) : '';
			if ((empty($wlgf_turnstile_secretkey) || '••••••••••••' === $wlgf_turnstile_secretkey) && !empty($existing_settings['turnstile_secretkey'])) {
				$wlgf_turnstile_secretkey = $existing_settings['turnstile_secretkey'];
			}

			// Automated Shields
			$wlgf_submission_rate_limit = isset($_POST['submission_rate_limit']) ? sanitize_text_field(wp_unslash($_POST['submission_rate_limit'])) : '1';
			$wlgf_honeypot_status = isset($_POST['honeypot_status']) ? sanitize_text_field(wp_unslash($_POST['honeypot_status'])) : '1';
			$wlgf_scanner_shield_status = isset($_POST['scanner_shield_status']) ? sanitize_text_field(wp_unslash($_POST['scanner_shield_status'])) : '1';

			// Keep recaptcha option in sync for backward compatibility
			if ('turnstile' === $wlgf_captcha_provider) {
				$wlgf_recaptcha = '3';
			} elseif ('recaptcha' === $wlgf_captcha_provider) {
				$wlgf_recaptcha = '1';
			} else {
				$wlgf_recaptcha = '2';
			}

			$wlgf_notify_admin = isset($_POST['notify_admin']) ? sanitize_text_field(wp_unslash($_POST['notify_admin'])) : '2';
			$wlgf_email_engine = isset($_POST['email_engine']) ? sanitize_text_field(wp_unslash($_POST['email_engine'])) : '1';
			$wlgf_smtp_host = isset($_POST['smtp_host']) ? sanitize_text_field(wp_unslash($_POST['smtp_host'])) : '';
			$wlgf_smtp_username = isset($_POST['smtp_username']) ? sanitize_text_field(wp_unslash($_POST['smtp_username'])) : '';

			// SMTP Password preservation rule (R5)
			$wlgf_smtp_password = isset($_POST['smtp_password']) ? trim(wp_unslash($_POST['smtp_password'])) : '';
			if ((empty($wlgf_smtp_password) || false !== strpos($wlgf_smtp_password, '••')) && !empty($existing_settings['smtp_password'])) {
				$wlgf_smtp_password = $existing_settings['smtp_password'];
			}

			$wlgf_smtp_encryption = isset($_POST['smtp_encryption']) ? sanitize_text_field(wp_unslash($_POST['smtp_encryption'])) : '1';
			$wlgf_smtp_port = isset($_POST['smtp_port']) ? sanitize_text_field(wp_unslash($_POST['smtp_port'])) : '587';

			$wlgf_user_message = isset($_POST['user_message']) ? sanitize_textarea_field(wp_unslash($_POST['user_message'])) : '';
			$wlgf_msg_allowed_tags = array(
				'p' => array('class' => array(), 'style' => array()),
				'strong' => array(),
				'br' => array(),
				'a' => array('href' => array(), 'title' => array(), 'target' => array(), 'class' => array()),
				'hr' => array(),
				'i' => array('class' => array()),
				'em' => array(),
				'span' => array('class' => array(), 'style' => array())
			);
			$wlgf_user_message = wp_kses($wlgf_user_message, $wlgf_msg_allowed_tags);

			// Submission Confirmation Appearance Settings (PRO)
			$wlgf_confirmation_heading       = isset($_POST['confirmation_heading']) && '' !== trim($_POST['confirmation_heading']) ? sanitize_text_field(wp_unslash($_POST['confirmation_heading'])) : __('Thank you! Submission Received', 'lead-generation-form');
			$wlgf_confirmation_style         = isset($_POST['confirmation_style']) ? sanitize_key(wp_unslash($_POST['confirmation_style'])) : 'modern-card';
			$wlgf_confirmation_icon          = isset($_POST['confirmation_icon']) ? sanitize_text_field(wp_unslash($_POST['confirmation_icon'])) : 'circle-check';
			$wlgf_confirmation_animated      = isset($_POST['confirmation_animated']) ? sanitize_text_field(wp_unslash($_POST['confirmation_animated'])) : '1';
			$wlgf_confirmation_fadeout       = isset($_POST['confirmation_fadeout']) ? max(5, min(50, intval($_POST['confirmation_fadeout']))) : 10;
			$wlgf_confirmation_bg            = isset($_POST['confirmation_bg']) && '' !== $_POST['confirmation_bg'] ? sanitize_hex_color(wp_unslash($_POST['confirmation_bg'])) : '#f0fdf4';
			$wlgf_confirmation_border_color  = isset($_POST['confirmation_border_color']) && '' !== $_POST['confirmation_border_color'] ? sanitize_hex_color(wp_unslash($_POST['confirmation_border_color'])) : '#86efac';
			$wlgf_confirmation_heading_color = isset($_POST['confirmation_heading_color']) && '' !== $_POST['confirmation_heading_color'] ? sanitize_hex_color(wp_unslash($_POST['confirmation_heading_color'])) : '#14532d';
			$wlgf_confirmation_text_color    = isset($_POST['confirmation_text_color']) && '' !== $_POST['confirmation_text_color'] ? sanitize_hex_color(wp_unslash($_POST['confirmation_text_color'])) : '#166534';
			$wlgf_confirmation_icon_color    = isset($_POST['confirmation_icon_color']) && '' !== $_POST['confirmation_icon_color'] ? sanitize_hex_color(wp_unslash($_POST['confirmation_icon_color'])) : '#16a34a';
			$wlgf_confirmation_icon_bg       = isset($_POST['confirmation_icon_bg']) && '' !== $_POST['confirmation_icon_bg'] ? sanitize_hex_color(wp_unslash($_POST['confirmation_icon_bg'])) : '#dcfce7';

			// Newsletter Settings (PRO-008)
			$wlgf_newsletter_provider = isset($_POST['newsletter_provider']) ? sanitize_text_field(wp_unslash($_POST['newsletter_provider'])) : 'local';
			$allowed_newsletter_providers = array('local', 'mailchimp', 'brevo', 'mailerlite', 'sendgrid', 'activecampaign');
			if (!in_array($wlgf_newsletter_provider, $allowed_newsletter_providers, true)) {
				$wlgf_newsletter_provider = 'local';
			}

			// Mailchimp API Key leave-blank-to-keep
			$wlgf_mailchimp_api_key = isset($_POST['mailchimp_api_key']) ? sanitize_text_field(wp_unslash($_POST['mailchimp_api_key'])) : '';
			if (empty($wlgf_mailchimp_api_key) && !empty($existing_settings['mailchimp_api_key'])) {
				$wlgf_mailchimp_api_key = $existing_settings['mailchimp_api_key'];
			}
			$wlgf_mailchimp_list_id = isset($_POST['mailchimp_list_id']) ? sanitize_text_field(wp_unslash($_POST['mailchimp_list_id'])) : '';
			$wlgf_mailchimp_optin = isset($_POST['mailchimp_double_optin']) ? sanitize_text_field(wp_unslash($_POST['mailchimp_double_optin'])) : '0';

			// Brevo API Key leave-blank-to-keep (Pro)
			$wlgf_brevo_api_key = isset($_POST['brevo_api_key']) ? sanitize_text_field(wp_unslash($_POST['brevo_api_key'])) : '';
			if (empty($wlgf_brevo_api_key) && !empty($existing_settings['brevo_api_key'])) {
				$wlgf_brevo_api_key = $existing_settings['brevo_api_key'];
			}
			$wlgf_brevo_list_id = isset($_POST['brevo_list_id']) ? sanitize_text_field(wp_unslash($_POST['brevo_list_id'])) : '';

			// MailerLite API Key leave-blank-to-keep (Pro)
			$wlgf_mailerlite_api_key = isset($_POST['mailerlite_api_key']) ? sanitize_text_field(wp_unslash($_POST['mailerlite_api_key'])) : '';
			if (empty($wlgf_mailerlite_api_key) && !empty($existing_settings['mailerlite_api_key'])) {
				$wlgf_mailerlite_api_key = $existing_settings['mailerlite_api_key'];
			}
			$wlgf_mailerlite_group_id = isset($_POST['mailerlite_group_id']) ? sanitize_text_field(wp_unslash($_POST['mailerlite_group_id'])) : '';

			// SendGrid API Key (Pro)
			$wlgf_sendgrid_api_key = isset($_POST['sendgrid_api_key']) ? sanitize_text_field(wp_unslash($_POST['sendgrid_api_key'])) : '';
			if (empty($wlgf_sendgrid_api_key) && !empty($existing_settings['sendgrid_api_key'])) {
				$wlgf_sendgrid_api_key = $existing_settings['sendgrid_api_key'];
			}
			$wlgf_sendgrid_list_id = isset($_POST['sendgrid_list_id']) ? sanitize_text_field(wp_unslash($_POST['sendgrid_list_id'])) : '';

			// ActiveCampaign API (Pro)
			$wlgf_activecampaign_api_url = isset($_POST['activecampaign_api_url']) ? esc_url_raw(wp_unslash($_POST['activecampaign_api_url'])) : '';
			$wlgf_activecampaign_api_key = isset($_POST['activecampaign_api_key']) ? sanitize_text_field(wp_unslash($_POST['activecampaign_api_key'])) : '';
			if (empty($wlgf_activecampaign_api_key) && !empty($existing_settings['activecampaign_api_key'])) {
				$wlgf_activecampaign_api_key = $existing_settings['activecampaign_api_key'];
			}
			$wlgf_activecampaign_list_id = isset($_POST['activecampaign_list_id']) ? sanitize_text_field(wp_unslash($_POST['activecampaign_list_id'])) : '';

			// Payment Settings (PRO-016)
			if (!isset($_POST['enable_payments']) && !isset($_POST['stripe_test_pub_key'])) {
				$wlgf_enable_payments = isset($existing_settings['enable_payments']) ? $existing_settings['enable_payments'] : '0';
				$wlgf_payment_currency = isset($existing_settings['payment_currency']) ? $existing_settings['payment_currency'] : 'USD';
				$wlgf_stripe_enabled = isset($existing_settings['stripe_enabled']) ? $existing_settings['stripe_enabled'] : '0';
				$wlgf_stripe_test_mode = isset($existing_settings['stripe_test_mode']) ? $existing_settings['stripe_test_mode'] : '1';
				$wlgf_stripe_test_pub_key = isset($existing_settings['stripe_test_pub_key']) ? $existing_settings['stripe_test_pub_key'] : '';
				$wlgf_stripe_test_sec_key = isset($existing_settings['stripe_test_sec_key']) ? $existing_settings['stripe_test_sec_key'] : '';
				$wlgf_stripe_live_pub_key = isset($existing_settings['stripe_live_pub_key']) ? $existing_settings['stripe_live_pub_key'] : '';
				$wlgf_stripe_live_sec_key = isset($existing_settings['stripe_live_sec_key']) ? $existing_settings['stripe_live_sec_key'] : '';
				$wlgf_paypal_enabled = isset($existing_settings['paypal_enabled']) ? $existing_settings['paypal_enabled'] : '0';
				$wlgf_paypal_mode = isset($existing_settings['paypal_mode']) ? $existing_settings['paypal_mode'] : 'sandbox';
				$wlgf_paypal_sandbox_client = isset($existing_settings['paypal_sandbox_client_id']) ? $existing_settings['paypal_sandbox_client_id'] : '';
				$wlgf_paypal_sandbox_secret = isset($existing_settings['paypal_sandbox_secret']) ? $existing_settings['paypal_sandbox_secret'] : '';
				$wlgf_paypal_live_client = isset($existing_settings['paypal_live_client_id']) ? $existing_settings['paypal_live_client_id'] : '';
				$wlgf_paypal_live_secret = isset($existing_settings['paypal_live_secret']) ? $existing_settings['paypal_live_secret'] : '';
			} else {
				$wlgf_enable_payments = isset($_POST['enable_payments']) ? sanitize_text_field(wp_unslash($_POST['enable_payments'])) : '0';
				if ( '0' === $wlgf_enable_payments && ( ( isset( $_POST['stripe_enabled'] ) && '1' === (string) $_POST['stripe_enabled'] ) || ( isset( $_POST['paypal_enabled'] ) && '1' === (string) $_POST['paypal_enabled'] ) ) ) {
					$wlgf_enable_payments = '1';
				}
				$wlgf_payment_currency = isset($_POST['payment_currency']) ? sanitize_text_field(wp_unslash($_POST['payment_currency'])) : 'USD';
				$wlgf_stripe_enabled = isset($_POST['stripe_enabled']) ? sanitize_text_field(wp_unslash($_POST['stripe_enabled'])) : '0';
				$wlgf_stripe_test_mode = isset($_POST['stripe_test_mode']) ? sanitize_text_field(wp_unslash($_POST['stripe_test_mode'])) : '1';
				$wlgf_stripe_test_pub_key = isset($_POST['stripe_test_pub_key']) ? sanitize_text_field(wp_unslash($_POST['stripe_test_pub_key'])) : '';
				$wlgf_stripe_test_sec_key = isset($_POST['stripe_test_sec_key']) ? sanitize_text_field(wp_unslash($_POST['stripe_test_sec_key'])) : '';
				if (empty($wlgf_stripe_test_sec_key) && !empty($existing_settings['stripe_test_sec_key'])) {
					$wlgf_stripe_test_sec_key = $existing_settings['stripe_test_sec_key'];
				}

				$wlgf_stripe_live_pub_key = isset($_POST['stripe_live_pub_key']) ? sanitize_text_field(wp_unslash($_POST['stripe_live_pub_key'])) : '';
				$wlgf_stripe_live_sec_key = isset($_POST['stripe_live_sec_key']) ? sanitize_text_field(wp_unslash($_POST['stripe_live_sec_key'])) : '';
				if (empty($wlgf_stripe_live_sec_key) && !empty($existing_settings['stripe_live_sec_key'])) {
					$wlgf_stripe_live_sec_key = $existing_settings['stripe_live_sec_key'];
				}

				$wlgf_paypal_enabled = isset($_POST['paypal_enabled']) ? sanitize_text_field(wp_unslash($_POST['paypal_enabled'])) : '0';
				$wlgf_paypal_mode = isset($_POST['paypal_mode']) ? sanitize_text_field(wp_unslash($_POST['paypal_mode'])) : 'sandbox';
				$wlgf_paypal_sandbox_client = isset($_POST['paypal_sandbox_client_id']) ? sanitize_text_field(wp_unslash($_POST['paypal_sandbox_client_id'])) : '';
				$wlgf_paypal_sandbox_secret = isset($_POST['paypal_sandbox_secret']) ? sanitize_text_field(wp_unslash($_POST['paypal_sandbox_secret'])) : '';
				if (empty($wlgf_paypal_sandbox_secret) && !empty($existing_settings['paypal_sandbox_secret'])) {
					$wlgf_paypal_sandbox_secret = $existing_settings['paypal_sandbox_secret'];
				}

				$wlgf_paypal_live_client = isset($_POST['paypal_live_client_id']) ? sanitize_text_field(wp_unslash($_POST['paypal_live_client_id'])) : '';
				$wlgf_paypal_live_secret = isset($_POST['paypal_live_secret']) ? sanitize_text_field(wp_unslash($_POST['paypal_live_secret'])) : '';
				if (empty($wlgf_paypal_live_secret) && !empty($existing_settings['paypal_live_secret'])) {
					$wlgf_paypal_live_secret = $existing_settings['paypal_live_secret'];
				}
			}

			// Floating Contact Widget Settings
			$wlgf_floating_widget_enabled = isset($_POST['floating_widget_enabled']) ? sanitize_text_field(wp_unslash($_POST['floating_widget_enabled'])) : '0';
			$allowed_positions = array( 'bottom-right', 'bottom-left', 'center-right', 'center-left', 'bottom-center', 'top-right', 'top-left' );
			$wlgf_floating_position = ( isset($_POST['floating_position']) && in_array($_POST['floating_position'], $allowed_positions, true) ) ? sanitize_text_field(wp_unslash($_POST['floating_position'])) : 'bottom-right';
			$wlgf_floating_color = isset($_POST['floating_color']) ? sanitize_hex_color(wp_unslash($_POST['floating_color'])) : '#2563eb';
			$wlgf_floating_greeting = isset($_POST['floating_greeting']) ? sanitize_text_field(wp_unslash($_POST['floating_greeting'])) : '';
			$wlgf_floating_title = isset($_POST['floating_title']) ? sanitize_text_field(wp_unslash($_POST['floating_title'])) : '';
			$wlgf_floating_subtitle = isset($_POST['floating_subtitle']) ? sanitize_text_field(wp_unslash($_POST['floating_subtitle'])) : '';
			$wlgf_floating_whatsapp = isset($_POST['floating_whatsapp']) ? sanitize_text_field(wp_unslash($_POST['floating_whatsapp'])) : '';
			$wlgf_floating_whatsapp_msg = isset($_POST['floating_whatsapp_msg']) ? sanitize_text_field(wp_unslash($_POST['floating_whatsapp_msg'])) : '';
			$wlgf_floating_phone = isset($_POST['floating_phone']) ? sanitize_text_field(wp_unslash($_POST['floating_phone'])) : '';
			$wlgf_floating_telegram = isset($_POST['floating_telegram']) ? sanitize_text_field(wp_unslash($_POST['floating_telegram'])) : '';
			$wlgf_floating_sms = isset($_POST['floating_sms']) ? sanitize_text_field(wp_unslash($_POST['floating_sms'])) : '';
			$wlgf_floating_email = isset($_POST['floating_email']) ? sanitize_email(wp_unslash($_POST['floating_email'])) : '';
			$wlgf_floating_form_id = isset($_POST['floating_form_id']) ? sanitize_text_field(wp_unslash($_POST['floating_form_id'])) : '';

			// Email Notification Template & Branding Settings
			$wlgf_email_logo_url = isset($_POST['email_logo_url']) ? esc_url_raw(wp_unslash($_POST['email_logo_url'])) : '';
			$wlgf_email_logo_width = isset($_POST['email_logo_width']) ? intval($_POST['email_logo_width']) : 180;
			$wlgf_email_header_title = isset($_POST['email_header_title']) ? sanitize_text_field(wp_unslash($_POST['email_header_title'])) : '';
			$wlgf_email_header_bg = isset($_POST['email_header_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_header_bg'])) : '#1e293b';
			$wlgf_email_header_color = isset($_POST['email_header_color']) ? sanitize_hex_color(wp_unslash($_POST['email_header_color'])) : '#ffffff';
			$wlgf_email_body_greeting = isset($_POST['email_body_greeting']) ? sanitize_textarea_field(wp_unslash($_POST['email_body_greeting'])) : '';
			$wlgf_email_body_bg = isset($_POST['email_body_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_body_bg'])) : '#f1f5f9';
			$wlgf_email_card_bg = isset($_POST['email_card_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_card_bg'])) : '#ffffff';
			$wlgf_email_text_color = isset($_POST['email_text_color']) ? sanitize_hex_color(wp_unslash($_POST['email_text_color'])) : '#334155';
			$wlgf_email_footer_text = isset($_POST['email_footer_text']) ? sanitize_textarea_field(wp_unslash($_POST['email_footer_text'])) : '';
			$wlgf_email_footer_bg = isset($_POST['email_footer_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_footer_bg'])) : '#f8fafc';
			$wlgf_email_footer_color = isset($_POST['email_footer_color']) ? sanitize_hex_color(wp_unslash($_POST['email_footer_color'])) : '#64748b';
			$wlgf_email_show_dashboard_btn = isset($_POST['email_show_dashboard_btn']) ? sanitize_text_field(wp_unslash($_POST['email_show_dashboard_btn'])) : '0';

			// CC Additional Admin Email Notifications
			$wlgf_cc_admin_emails_raw = isset($_POST['cc_admin_emails']) ? sanitize_textarea_field(wp_unslash($_POST['cc_admin_emails'])) : '';
			$cc_clean = array();
			if (!empty($wlgf_cc_admin_emails_raw)) {
				$split_cc = preg_split('/[\r\n,]+/', $wlgf_cc_admin_emails_raw);
				foreach ($split_cc as $cc_em) {
					$cc_em = sanitize_email(trim($cc_em));
					if (is_email($cc_em) && !in_array($cc_em, $cc_clean, true)) {
						$cc_clean[] = $cc_em;
					}
				}
			}
			$wlgf_cc_admin_emails = implode("\n", $cc_clean);

			// Email & Domain Blacklist Protection Settings
			$wlgf_blacklist_status = isset($_POST['blacklist_status']) ? sanitize_text_field(wp_unslash($_POST['blacklist_status'])) : '2';
			$wlgf_blacklist_emails_raw = isset($_POST['blacklist_emails']) ? sanitize_textarea_field(wp_unslash($_POST['blacklist_emails'])) : '';
			$emails_clean = array();
			if (!empty($wlgf_blacklist_emails_raw)) {
				$split_emails = preg_split('/[\r\n,]+/', $wlgf_blacklist_emails_raw);
				foreach ($split_emails as $em) {
					$em = sanitize_email(trim($em));
					if (is_email($em) && !in_array($em, $emails_clean, true)) {
						$emails_clean[] = strtolower($em);
					}
				}
			}
			$wlgf_blacklist_emails = implode("\n", $emails_clean);

			$wlgf_blacklist_domains_raw = isset($_POST['blacklist_domains']) ? sanitize_textarea_field(wp_unslash($_POST['blacklist_domains'])) : '';
			$domains_clean = array();
			if (!empty($wlgf_blacklist_domains_raw)) {
				$split_domains = preg_split('/[\r\n,]+/', $wlgf_blacklist_domains_raw);
				foreach ($split_domains as $dm) {
					$dm = strtolower(trim($dm));
					$dm = preg_replace('/^https?:\/\//', '', $dm);
					$dm = ltrim($dm, '@');
					$dm = trim($dm, '/');
					if (!empty($dm) && !in_array($dm, $domains_clean, true)) {
						$domains_clean[] = $dm;
					}
				}
			}
			$wlgf_blacklist_domains = implode("\n", $domains_clean);

			$wlgf_blacklist_action = isset($_POST['blacklist_action']) && in_array($_POST['blacklist_action'], array('reject_error', 'silent_drop'), true) ? sanitize_text_field(wp_unslash($_POST['blacklist_action'])) : 'reject_error';
			$wlgf_blacklist_message = isset($_POST['blacklist_message']) && '' !== trim($_POST['blacklist_message']) ? sanitize_text_field(wp_unslash($_POST['blacklist_message'])) : __('Sorry! Your email address or domain is not permitted to submit this form.', 'lead-generation-form');

			$wlgf_settings = array(
				'recaptcha' => $wlgf_recaptcha,
				'captcha_provider' => $wlgf_captcha_provider,
				'sitekey' => $wlgf_sitekey,
				'secretkey' => $wlgf_secretkey,
				'turnstile_sitekey' => $wlgf_turnstile_sitekey,
				'turnstile_secretkey' => $wlgf_turnstile_secretkey,
				'submission_rate_limit' => $wlgf_submission_rate_limit,
				'honeypot_status' => $wlgf_honeypot_status,
				'scanner_shield_status' => $wlgf_scanner_shield_status,
				'notify_admin' => $wlgf_notify_admin,
				'email_engine' => $wlgf_email_engine,
				'smtp_host' => $wlgf_smtp_host,
				'smtp_username' => $wlgf_smtp_username,
				'smtp_password' => $wlgf_smtp_password,
				'smtp_encryption' => $wlgf_smtp_encryption,
				'smtp_port' => $wlgf_smtp_port,
				'user_message' => $wlgf_user_message,
				'newsletter_provider' => $wlgf_newsletter_provider,
				'mailchimp_api_key' => $wlgf_mailchimp_api_key,
				'mailchimp_list_id' => $wlgf_mailchimp_list_id,
				'mailchimp_double_optin' => $wlgf_mailchimp_optin,
				'brevo_api_key' => $wlgf_brevo_api_key,
				'brevo_list_id' => $wlgf_brevo_list_id,
				'mailerlite_api_key' => $wlgf_mailerlite_api_key,
				'mailerlite_group_id' => $wlgf_mailerlite_group_id,
				'sendgrid_api_key' => $wlgf_sendgrid_api_key,
				'sendgrid_list_id' => $wlgf_sendgrid_list_id,
				'activecampaign_api_url' => $wlgf_activecampaign_api_url,
				'activecampaign_api_key' => $wlgf_activecampaign_api_key,
				'activecampaign_list_id' => $wlgf_activecampaign_list_id,
				'enable_payments' => $wlgf_enable_payments,
				'payment_currency' => $wlgf_payment_currency,
				'stripe_enabled' => $wlgf_stripe_enabled,
				'stripe_test_mode' => $wlgf_stripe_test_mode,
				'stripe_test_pub_key' => $wlgf_stripe_test_pub_key,
				'stripe_test_sec_key' => $wlgf_stripe_test_sec_key,
				'stripe_live_pub_key' => $wlgf_stripe_live_pub_key,
				'stripe_live_sec_key' => $wlgf_stripe_live_sec_key,
				'paypal_enabled' => $wlgf_paypal_enabled,
				'paypal_mode' => $wlgf_paypal_mode,
				'paypal_sandbox_client_id' => $wlgf_paypal_sandbox_client,
				'paypal_sandbox_secret' => $wlgf_paypal_sandbox_secret,
				'paypal_live_client_id' => $wlgf_paypal_live_client,
				'paypal_live_secret' => $wlgf_paypal_live_secret,
				'floating_widget_enabled' => $wlgf_floating_widget_enabled,
				'floating_position' => $wlgf_floating_position,
				'floating_color' => $wlgf_floating_color,
				'floating_greeting' => $wlgf_floating_greeting,
				'floating_title' => $wlgf_floating_title,
				'floating_subtitle' => $wlgf_floating_subtitle,
				'floating_whatsapp' => $wlgf_floating_whatsapp,
				'floating_whatsapp_msg' => $wlgf_floating_whatsapp_msg,
				'floating_phone' => $wlgf_floating_phone,
				'floating_telegram' => $wlgf_floating_telegram,
				'floating_sms' => $wlgf_floating_sms,
				'floating_email' => $wlgf_floating_email,
				'floating_form_id' => $wlgf_floating_form_id,
				'email_logo_url' => $wlgf_email_logo_url,
				'email_logo_width' => $wlgf_email_logo_width,
				'email_header_title' => $wlgf_email_header_title,
				'email_header_bg' => $wlgf_email_header_bg,
				'email_header_color' => $wlgf_email_header_color,
				'email_body_greeting' => $wlgf_email_body_greeting,
				'email_body_bg' => $wlgf_email_body_bg,
				'email_card_bg' => $wlgf_email_card_bg,
				'email_text_color' => $wlgf_email_text_color,
				'email_footer_text' => $wlgf_email_footer_text,
				'email_footer_bg' => $wlgf_email_footer_bg,
				'email_footer_color' => $wlgf_email_footer_color,
				'email_show_dashboard_btn' => $wlgf_email_show_dashboard_btn,
				'confirmation_heading' => $wlgf_confirmation_heading,
				'confirmation_style' => $wlgf_confirmation_style,
				'confirmation_icon' => $wlgf_confirmation_icon,
				'confirmation_animated' => $wlgf_confirmation_animated,
				'confirmation_fadeout' => $wlgf_confirmation_fadeout,
				'confirmation_bg' => $wlgf_confirmation_bg,
				'confirmation_border_color' => $wlgf_confirmation_border_color,
				'confirmation_heading_color' => $wlgf_confirmation_heading_color,
				'confirmation_text_color' => $wlgf_confirmation_text_color,
				'confirmation_icon_color' => $wlgf_confirmation_icon_color,
				'confirmation_icon_bg' => $wlgf_confirmation_icon_bg,
				'cc_admin_emails' => $wlgf_cc_admin_emails,
				'blacklist_status' => $wlgf_blacklist_status,
				'blacklist_emails' => $wlgf_blacklist_emails,
				'blacklist_domains' => $wlgf_blacklist_domains,
				'blacklist_action' => $wlgf_blacklist_action,
				'blacklist_message' => $wlgf_blacklist_message,
			);

			update_option('wlgf_settings', $wlgf_settings);
			wp_send_json_success(array('message' => __('Settings updated successfully.', 'lead-generation-form')));
		}
		wp_die();
	}
}
add_action('wp_ajax_wlgf_save_settings', 'wlgf_save_settings_callback');

/**
 * Check if a submission contains blacklisted emails or domains
 *
 * @param array $sanitized_data Form submission data
 * @param array|null $form_info Form configuration array
 * @param array|null $settings Global settings array
 * @return array Array with status 'is_blocked' (bool), 'action' ('reject_error'|'silent_drop'), 'message' (string), 'matched' (string), 'reason' ('email'|'domain'|'form_blacklist')
 */
function wlgf_check_submission_blacklist($sanitized_data = array(), $form_info = array(), $settings = null)
{
	if (null === $settings) {
		$settings = get_option('wlgf_settings', array());
	}

	$default_result = array(
		'is_blocked' => false,
		'action'     => 'reject_error',
		'message'    => '',
		'matched'    => '',
		'reason'     => '',
	);

	// 1. Gather all email values present in the submission
	$submitted_emails = array();
	if (is_array($sanitized_data)) {
		foreach ($sanitized_data as $key => $val) {
			if (is_string($val)) {
				$val_clean = trim($val);
				if (is_email($val_clean) || false !== stripos($key, 'email')) {
					if (is_email($val_clean)) {
						$submitted_emails[] = strtolower($val_clean);
					}
				}
			} elseif (is_array($val)) {
				foreach ($val as $sub_val) {
					if (is_string($sub_val) && is_email(trim($sub_val))) {
						$submitted_emails[] = strtolower(trim($sub_val));
					}
				}
			}
		}
	}
	$submitted_emails = array_unique($submitted_emails);

	$blacklist_status  = isset($settings['blacklist_status']) ? (string)$settings['blacklist_status'] : '2';
	$blacklist_action  = isset($settings['blacklist_action']) ? $settings['blacklist_action'] : 'reject_error';
	$blacklist_message = !empty($settings['blacklist_message']) ? $settings['blacklist_message'] : __('Sorry! Your email address or domain is not permitted to submit this form.', 'lead-generation-form');

	// 2. Global Blacklist Evaluation (if status is ON = 1)
	if ('1' === $blacklist_status && !empty($submitted_emails)) {
		// Prepare blocked emails list
		$raw_emails = isset($settings['blacklist_emails']) ? $settings['blacklist_emails'] : '';
		$blocked_emails = array();
		if (!empty($raw_emails)) {
			$email_lines = preg_split('/[\r\n,]+/', $raw_emails);
			foreach ($email_lines as $line) {
				$line = strtolower(trim($line));
				if (!empty($line)) {
					$blocked_emails[] = $line;
				}
			}
		}

		// Prepare blocked domains list
		$raw_domains = isset($settings['blacklist_domains']) ? $settings['blacklist_domains'] : '';
		$blocked_domains = array();
		if (!empty($raw_domains)) {
			$domain_lines = preg_split('/[\r\n,]+/', $raw_domains);
			foreach ($domain_lines as $dom) {
				$dom = strtolower(trim($dom));
				$dom = preg_replace('/^https?:\/\//', '', $dom);
				$dom = ltrim($dom, '@');
				$dom = trim($dom, '/');
				if (!empty($dom)) {
					$blocked_domains[] = $dom;
				}
			}
		}

		foreach ($submitted_emails as $email) {
			$parts = explode('@', $email, 2);
			$domain = isset($parts[1]) ? $parts[1] : '';

			// Check exact email match
			foreach ($blocked_emails as $bad_email) {
				if ($email === $bad_email) {
					return array(
						'is_blocked' => true,
						'action'     => $blacklist_action,
						'message'    => $blacklist_message,
						'matched'    => $bad_email,
						'reason'     => 'email',
					);
				}
			}

			// Check domain match
			if (!empty($domain)) {
				foreach ($blocked_domains as $bad_domain) {
					$clean_bad = ltrim($bad_domain, '*.');
					if (
						$domain === $bad_domain ||
						$domain === $clean_bad ||
						(substr($domain, -(strlen($clean_bad) + 1)) === '.' . $clean_bad) ||
						(0 === strpos($bad_domain, '.') && substr($domain, -strlen($bad_domain)) === $bad_domain)
					) {
						return array(
							'is_blocked' => true,
							'action'     => $blacklist_action,
							'message'    => $blacklist_message,
							'matched'    => $bad_domain,
							'reason'     => 'domain',
						);
					}
				}
			}
		}
	}

	// 3. Form-level blacklist check (legacy/per-form compatibility)
	$form_blacklist_raw = !empty($form_info['blacklist']) ? $form_info['blacklist'] : '';
	if (!empty($form_blacklist_raw) && !empty($submitted_emails)) {
		$form_bad_list = array_map('trim', explode(',', strtolower($form_blacklist_raw)));
		foreach ($submitted_emails as $email) {
			foreach ($form_bad_list as $bad_entry) {
				if (!empty($bad_entry) && (false !== strpos($email, $bad_entry))) {
					return array(
						'is_blocked' => true,
						'action'     => $blacklist_action,
						'message'    => $blacklist_message,
						'matched'    => $bad_entry,
						'reason'     => 'form_blacklist',
					);
				}
			}
		}
	}

	return $default_result;
}

/**
 * Transient-based submission rate limiter to stop rapid-fire automated scripts
 *
 * @param string|int $form_id Form identifier
 * @param string $remote_ip Client IP address
 * @return array Array with status 'allowed' (bool) and optional 'message'
 */
function wlgf_check_submission_rate_limit($form_id, $remote_ip = '')
{
	$settings = get_option('wlgf_settings', array());
	$rate_limit_enabled = isset($settings['submission_rate_limit']) ? (string)$settings['submission_rate_limit'] : '1';
	if ('1' !== $rate_limit_enabled) {
		return array('allowed' => true);
	}

	if (empty($remote_ip)) {
		$remote_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '127.0.0.1';
	}

	// Limit to maximum 3 submissions per 120 seconds per IP per form
	$transient_key = 'wlgf_sub_lmt_' . md5($remote_ip . '_' . $form_id);
	$count = (int) get_transient($transient_key);

	if ($count >= 3) {
		return array(
			'allowed' => false,
			'message' => __('Too many submissions detected from your IP address. Please wait a moment before trying again.', 'lead-generation-form'),
		);
	}

	$count++;
	set_transient($transient_key, $count, 120);

	return array('allowed' => true);
}

/**
 * Detect automated vulnerability scanner probes and SQLi injection payloads
 *
 * @param array $data Input dataset
 * @return array Array with 'is_exploit' (bool), 'matched' (string), 'field' (string)
 */
function wlgf_detect_exploit_payload($data)
{
	$settings = get_option('wlgf_settings', array());
	$shield_enabled = isset($settings['scanner_shield_status']) ? (string)$settings['scanner_shield_status'] : '1';
	if ('1' !== $shield_enabled || empty($data) || !is_array($data)) {
		return array('is_exploit' => false);
	}

	// Signatures for automated blind SQLi tools (sqlmap, Acunetix, Burp scanner)
	$patterns = array(
		'/\b(?:pg_sleep|sleep\s*\(|waitfor\s+delay|benchmark\s*\()/i',
		'/\b(?:union\s+select|load_file\s*\(|into\s+(?:outfile|dumpfile))/i',
		'/\b(?:if\s*\(\s*(?:now\(\)|sysdate\(\))\s*=\s*(?:sysdate\(\)|now\(\)))/i',
		'/\b(?:information_schema|schema_name|table_schema)\b/i',
		'/[\'"]\s*(?:or|and)\s+[\'"]?[0-9a-z]+[\'"]?\s*=\s*[\'"]?[0-9a-z]+[\'"]?/i',
		'/(?:\bOR\s+\d+[\+\-\*\/]\d+)/i',
		'/(?:<script\b[^>]*>.*?<\/script>)/is',
	);

	$check_items = function($items) use (&$check_items, $patterns) {
		foreach ($items as $key => $val) {
			if (in_array($key, array('wlgf_form_nonce', '_wpnonce', 'recaptcha_response', 'cf-turnstile-response'), true)) {
				continue;
			}
			if (is_array($val)) {
				$res = $check_items($val);
				if (!empty($res['is_exploit'])) {
					return $res;
				}
			} elseif (is_string($val)) {
				$val_clean = trim($val);
				if (strlen($val_clean) > 0) {
					foreach ($patterns as $pattern) {
						if (preg_match($pattern, $val_clean, $matches)) {
							return array(
								'is_exploit' => true,
								'matched'    => $matches[0],
								'field'      => $key,
							);
						}
					}
				}
			}
		}
		return array('is_exploit' => false);
	};

	return $check_items($data);
}

/**
 * Verify Cloudflare Turnstile token via Cloudflare API
 *
 * @param string $response_token The cf-turnstile-response token
 * @param string $secret_key Cloudflare Turnstile Secret Key
 * @param string $remote_ip Client IP address
 * @return array Array with status 'success' (bool) and optional 'message'
 */
function wlgf_verify_turnstile_response($response_token, $secret_key, $remote_ip = '')
{
	if (empty($response_token) || empty($secret_key)) {
		return array(
			'success' => false,
			'message' => __('Missing Cloudflare Turnstile token or secret key.', 'lead-generation-form'),
		);
	}

	if (empty($remote_ip) && isset($_SERVER['REMOTE_ADDR'])) {
		$remote_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
	}

	$verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	$post_data = array(
		'secret'   => $secret_key,
		'response' => $response_token,
	);
	if (!empty($remote_ip)) {
		$post_data['remoteip'] = $remote_ip;
	}

	$response = wp_remote_post($verify_url, array(
		'body'    => $post_data,
		'timeout' => 10,
	));

	if (is_wp_error($response)) {
		return array(
			'success' => false,
			'message' => sprintf(__('Cloudflare Turnstile connection error: %s', 'lead-generation-form'), $response->get_error_message()),
		);
	}

	$body = json_decode(wp_remote_retrieve_body($response), true);
	if (!empty($body['success'])) {
		return array('success' => true);
	}

	$error_codes = isset($body['error-codes']) ? implode(', ', (array)$body['error-codes']) : 'verification_failed';
	return array(
		'success' => false,
		'message' => sprintf(__('Cloudflare Turnstile verification failed (%s).', 'lead-generation-form'), esc_html($error_codes)),
	);
}

/**
 * Generate modern HTML markup for Submission Confirmation / Thank You message
 *
 * @param string $custom_msg Optional custom message body.
 * @param array|null $settings Optional settings array override.
 * @return string Safe sanitized HTML markup.
 */
function wlgf_get_confirmation_html($custom_msg = '', $settings = null)
{
	if (!is_array($settings)) {
		$settings = get_option('wlgf_settings', array());
	}

	$heading = !empty($settings['confirmation_heading']) ? sanitize_text_field($settings['confirmation_heading']) : __('Thank you! Submission Received', 'lead-generation-form');

	if (empty($custom_msg)) {
		$msg = !empty($settings['user_message']) ? $settings['user_message'] : __('Your query has been successfully submitted. We will get back to you shortly.', 'lead-generation-form');
	} else {
		$msg = $custom_msg;
	}

	$wlgf_msg_allowed_tags = array(
		'p'      => array('class' => array(), 'style' => array()),
		'strong' => array(),
		'br'     => array(),
		'a'      => array('href' => array(), 'title' => array(), 'target' => array(), 'class' => array()),
		'hr'     => array(),
		'i'      => array('class' => array()),
		'em'     => array(),
		'span'   => array('class' => array(), 'style' => array()),
	);
	$msg_html = wp_kses($msg, $wlgf_msg_allowed_tags);

	$style = !empty($settings['confirmation_style']) ? sanitize_key($settings['confirmation_style']) : 'modern-card';
	$allowed_styles = array('modern-card', 'banner', 'minimal-pill', 'glassmorphism');
	if (!in_array($style, $allowed_styles, true)) {
		$style = 'modern-card';
	}

	$icon = isset($settings['confirmation_icon']) ? sanitize_text_field($settings['confirmation_icon']) : 'circle-check';
	$allowed_icons = array(
		'circle-check'          => 'fa-solid fa-circle-check',
		'paper-plane'           => 'fa-solid fa-paper-plane',
		'sparkles'              => 'fa-solid fa-wand-magic-sparkles',
		'thumbs-up'             => 'fa-solid fa-thumbs-up',
		'envelope-circle-check' => 'fa-solid fa-envelope-circle-check',
		'heart'                 => 'fa-solid fa-heart',
		'bell'                  => 'fa-solid fa-bell',
		'none'                  => '',
	);
	$fa_class = isset($allowed_icons[$icon]) ? $allowed_icons[$icon] : 'fa-solid fa-circle-check';

	$animated = (!isset($settings['confirmation_animated']) || '1' === (string)$settings['confirmation_animated']);

	$bg            = !empty($settings['confirmation_bg']) ? sanitize_hex_color($settings['confirmation_bg']) : '#f0fdf4';
	$border_color  = !empty($settings['confirmation_border_color']) ? sanitize_hex_color($settings['confirmation_border_color']) : '#86efac';
	$heading_color = !empty($settings['confirmation_heading_color']) ? sanitize_hex_color($settings['confirmation_heading_color']) : '#14532d';
	$text_color    = !empty($settings['confirmation_text_color']) ? sanitize_hex_color($settings['confirmation_text_color']) : '#166534';
	$icon_color    = !empty($settings['confirmation_icon_color']) ? sanitize_hex_color($settings['confirmation_icon_color']) : '#16a34a';
	$icon_bg       = !empty($settings['confirmation_icon_bg']) ? sanitize_hex_color($settings['confirmation_icon_bg']) : '#dcfce7';

	// CSS variables inline style
	$inline_css = sprintf(
		'--wlgf-conf-bg: %s; --wlgf-conf-border: %s; --wlgf-conf-heading: %s; --wlgf-conf-text: %s; --wlgf-conf-icon: %s; --wlgf-conf-icon-bg: %s;',
		esc_attr($bg),
		esc_attr($border_color),
		esc_attr($heading_color),
		esc_attr($text_color),
		esc_attr($icon_color),
		esc_attr($icon_bg)
	);

	$card_classes = array(
		'wlgf-confirmation-card',
		'wlgf-confirmation--' . $style,
	);
	if ($animated) {
		$card_classes[] = 'wlgf-confirmation--animated';
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" style="<?php echo esc_attr($inline_css); ?>" role="alert" aria-live="polite">
		<?php if (!empty($fa_class)) : ?>
			<div class="wlgf-confirmation-badge">
				<i class="<?php echo esc_attr($fa_class); ?>"></i>
			</div>
		<?php endif; ?>
		<div class="wlgf-confirmation-content">
			<?php if (!empty($heading)) : ?>
				<h4 class="wlgf-confirmation-title" style="color: <?php echo esc_attr($heading_color); ?> !important;"><?php echo esc_html($heading); ?></h4>
			<?php endif; ?>
			<div class="wlgf-confirmation-desc">
				<?php echo $msg_html; ?>
			</div>
		</div>
	</div>
	<?php
	return trim(ob_get_clean());
}

// 5.1 verify newsletter connection API callback (PRO-008)
function wlgf_verify_newsletter_api_callback()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('Unauthorized user action.', 'lead-generation-form')));
	}
	if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-save-settings')) {
		wp_send_json_error(array('message' => __('Security verification failed.', 'lead-generation-form')));
	}

	$provider = isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '';
	$api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
	$list_id = isset($_POST['list_id']) ? sanitize_text_field(wp_unslash($_POST['list_id'])) : '';
	$api_url = isset($_POST['api_url']) ? esc_url_raw(wp_unslash($_POST['api_url'])) : '';

	$saved_settings = get_option('wlgf_settings', array());

	if (empty($api_key)) {
		$key_name = $provider . '_api_key';
		if (!empty($saved_settings[$key_name])) {
			$api_key = $saved_settings[$key_name];
		}
	}
	if (empty($list_id)) {
		if ('mailerlite' === $provider && !empty($saved_settings['mailerlite_group_id'])) {
			$list_id = $saved_settings['mailerlite_group_id'];
		} else {
			$list_name = $provider . '_list_id';
			if (!empty($saved_settings[$list_name])) {
				$list_id = $saved_settings[$list_name];
			}
		}
	}
	if (empty($api_url) && 'activecampaign' === $provider && !empty($saved_settings['activecampaign_api_url'])) {
		$api_url = $saved_settings['activecampaign_api_url'];
	}

	require_once plugin_dir_path(__FILE__) . 'includes/class-wlgf-newsletter.php';
	$result = WLGF_Newsletter::verify_connection($provider, array(
		'api_key' => $api_key,
		'list_id' => $list_id,
		'api_url' => $api_url,
	));

	if (!empty($result['success'])) {
		wp_send_json_success(array('message' => $result['message']));
	} else {
		wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : __('Verification failed.', 'lead-generation-form')));
	}
}
add_action('wp_ajax_wlgf_verify_newsletter_api', 'wlgf_verify_newsletter_api_callback');
// 5. save settings end

// 5.1 Send Test Email Handler (wp_mail & SMTP with Branded HTML Template)
function wlgf_smtp_test_mail_callback()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('Unauthorized access.', 'lead-generation-form')));
	}
	if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-save-settings')) {
		wp_send_json_error(array('message' => __('Security verification failed.', 'lead-generation-form')));
	}

	$test_email = isset($_POST['test_email']) ? sanitize_email(wp_unslash($_POST['test_email'])) : '';
	if (!is_email($test_email)) {
		$test_email = get_option('admin_email');
	}

	$wlgf_settings = get_option('wlgf_settings', array());
	$email_engine  = isset($_POST['email_engine']) ? sanitize_text_field(wp_unslash($_POST['email_engine'])) : (isset($wlgf_settings['email_engine']) ? $wlgf_settings['email_engine'] : '1');

	// Collect live or saved template customizations
	$custom_settings = array(
		'email_logo_url'           => isset($_POST['email_logo_url']) ? esc_url_raw(wp_unslash($_POST['email_logo_url'])) : (isset($wlgf_settings['email_logo_url']) ? $wlgf_settings['email_logo_url'] : ''),
		'email_logo_width'         => isset($_POST['email_logo_width']) ? intval($_POST['email_logo_width']) : (isset($wlgf_settings['email_logo_width']) ? intval($wlgf_settings['email_logo_width']) : 180),
		'email_header_title'       => isset($_POST['email_header_title']) ? sanitize_text_field(wp_unslash($_POST['email_header_title'])) : (isset($wlgf_settings['email_header_title']) ? $wlgf_settings['email_header_title'] : ''),
		'email_header_bg'          => isset($_POST['email_header_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_header_bg'])) : (isset($wlgf_settings['email_header_bg']) ? $wlgf_settings['email_header_bg'] : '#1e293b'),
		'email_header_color'       => isset($_POST['email_header_color']) ? sanitize_hex_color(wp_unslash($_POST['email_header_color'])) : (isset($wlgf_settings['email_header_color']) ? $wlgf_settings['email_header_color'] : '#ffffff'),
		'email_body_greeting'      => isset($_POST['email_body_greeting']) ? sanitize_textarea_field(wp_unslash($_POST['email_body_greeting'])) : (isset($wlgf_settings['email_body_greeting']) ? $wlgf_settings['email_body_greeting'] : ''),
		'email_body_bg'            => isset($_POST['email_body_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_body_bg'])) : (isset($wlgf_settings['email_body_bg']) ? $wlgf_settings['email_body_bg'] : '#f1f5f9'),
		'email_card_bg'            => isset($_POST['email_card_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_card_bg'])) : (isset($wlgf_settings['email_card_bg']) ? $wlgf_settings['email_card_bg'] : '#ffffff'),
		'email_text_color'         => isset($_POST['email_text_color']) ? sanitize_hex_color(wp_unslash($_POST['email_text_color'])) : (isset($wlgf_settings['email_text_color']) ? $wlgf_settings['email_text_color'] : '#334155'),
		'email_footer_text'        => isset($_POST['email_footer_text']) ? sanitize_textarea_field(wp_unslash($_POST['email_footer_text'])) : (isset($wlgf_settings['email_footer_text']) ? $wlgf_settings['email_footer_text'] : ''),
		'email_footer_bg'          => isset($_POST['email_footer_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_footer_bg'])) : (isset($wlgf_settings['email_footer_bg']) ? $wlgf_settings['email_footer_bg'] : '#f8fafc'),
		'email_footer_color'       => isset($_POST['email_footer_color']) ? sanitize_hex_color(wp_unslash($_POST['email_footer_color'])) : (isset($wlgf_settings['email_footer_color']) ? $wlgf_settings['email_footer_color'] : '#64748b'),
		'email_show_dashboard_btn' => isset($_POST['email_show_dashboard_btn']) ? sanitize_text_field(wp_unslash($_POST['email_show_dashboard_btn'])) : (isset($wlgf_settings['email_show_dashboard_btn']) ? $wlgf_settings['email_show_dashboard_btn'] : '1'),
	);

	require_once plugin_dir_path(__FILE__) . 'includes/class-wlgf-email-template.php';
	$html_message = WLGF_Email_Template::get_preview_html($custom_settings);

	$from_name    = function_exists('wlgf_get_from_name') ? wlgf_get_from_name() : wp_specialchars_decode( $blog_name, ENT_QUOTES );
	$from_email   = function_exists('wlgf_get_from_email') ? wlgf_get_from_email( $email_engine ) : $admin_email;
	$engine_label = ('2' === (string) $email_engine) ? 'Custom SMTP' : 'Default WordPress Mail (wp_mail)';
	$subject      = wp_specialchars_decode( sprintf(__('[%s] Lead Generation Form - Test Notification (%s)', 'lead-generation-form'), $blog_name, $engine_label), ENT_QUOTES );

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: "' . $from_name . '" <' . $from_email . '>',
	);

	// If SMTP engine selected, configure PHPMailer on the fly
	$smtp_hook = null;
	if ('2' === (string) $email_engine) {
		$smtp_host       = isset($_POST['smtp_host']) && '' !== trim($_POST['smtp_host']) ? sanitize_text_field(wp_unslash($_POST['smtp_host'])) : (isset($wlgf_settings['smtp_host']) ? $wlgf_settings['smtp_host'] : '');
		$smtp_username   = isset($_POST['smtp_username']) && '' !== trim($_POST['smtp_username']) ? sanitize_text_field(wp_unslash($_POST['smtp_username'])) : (isset($wlgf_settings['smtp_username']) ? $wlgf_settings['smtp_username'] : '');
		$raw_smtp_pw     = isset($_POST['smtp_password']) ? trim(wp_unslash($_POST['smtp_password'])) : '';
		if (empty($raw_smtp_pw) || false !== strpos($raw_smtp_pw, '••')) {
			$smtp_password = isset($wlgf_settings['smtp_password']) ? $wlgf_settings['smtp_password'] : '';
		} else {
			$smtp_password = $raw_smtp_pw;
		}

		// Normalize Google App Passwords (strip copy-pasted spaces if host is Gmail)
		if (false !== stripos($smtp_host, 'gmail.com') && strpos($smtp_password, ' ') !== false) {
			$smtp_password = str_replace(' ', '', $smtp_password);
		}

		$smtp_encryption = isset($_POST['smtp_encryption']) ? sanitize_text_field(wp_unslash($_POST['smtp_encryption'])) : (isset($wlgf_settings['smtp_encryption']) ? $wlgf_settings['smtp_encryption'] : '1');
		$smtp_port       = isset($_POST['smtp_port']) ? sanitize_text_field(wp_unslash($_POST['smtp_port'])) : (isset($wlgf_settings['smtp_port']) ? $wlgf_settings['smtp_port'] : '587');

		$smtp_hook = function($phpmailer) use ($smtp_host, $smtp_username, $smtp_password, $smtp_encryption, $smtp_port) {
			$phpmailer->isSMTP();
			$phpmailer->Host       = $smtp_host;
			$phpmailer->SMTPAuth   = true;
			$phpmailer->Username   = $smtp_username;
			$phpmailer->Password   = $smtp_password;
			$phpmailer->Port       = intval($smtp_port);
			$phpmailer->Timeout    = 15;

			if ('1' === (string) $smtp_encryption) {
				$phpmailer->SMTPSecure = 'tls';
				$phpmailer->SMTPAutoTLS = true;
			} elseif ('2' === (string) $smtp_encryption) {
				$phpmailer->SMTPSecure = 'ssl';
				$phpmailer->SMTPAutoTLS = false;
			} else {
				$phpmailer->SMTPSecure = '';
				$phpmailer->SMTPAutoTLS = false;
			}

			// Ensure From & Sender envelope match authenticated user for strict SMTP providers (e.g. Gmail/Outlook)
			if (!empty($smtp_username) && is_email($smtp_username)) {
				$phpmailer->Sender = $smtp_username;
				$phpmailer->From   = $smtp_username;
			}

			// Local environment SSL fallback (handles missing cacert.pem in local WAMP/XAMPP/Docker)
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				),
			);
		};

		add_action('phpmailer_init', $smtp_hook, 999);
	}

	// Track any wp_mail failure details
	$last_mail_error = '';
	$mail_failed_handler = function($wp_error) use (&$last_mail_error) {
		if (is_wp_error($wp_error)) {
			$last_mail_error = $wp_error->get_error_message();
		}
	};
	add_action('wp_mail_failed', $mail_failed_handler);

	$sent = wp_mail($test_email, $subject, $html_message, $headers);

	remove_action('wp_mail_failed', $mail_failed_handler);
	if (null !== $smtp_hook) {
		remove_action('phpmailer_init', $smtp_hook, 999);
	}

	if ($sent) {
		wp_send_json_success(array(
			'message' => sprintf(__('Test email successfully sent to %1$s via %2$s! Please check your inbox (and spam folder).', 'lead-generation-form'), esc_html($test_email), $engine_label)
		));
	} else {
		global $phpmailer;
		$error_msg = !empty($last_mail_error) ? $last_mail_error : ((isset($phpmailer->ErrorInfo) && !empty($phpmailer->ErrorInfo)) ? $phpmailer->ErrorInfo : __('Mail dispatch failed. Please verify your server email settings or SMTP credentials.', 'lead-generation-form'));

		// Detect specific failures and provide actionable guidance
		$hint = '';
		if (false !== stripos($error_msg, 'authenticate') || false !== stripos($error_msg, '535') || false !== stripos($error_msg, 'BadCredentials')) {
			if (isset($smtp_host) && false !== stripos($smtp_host, 'gmail.com')) {
				$hint = ' ' . __('Important for Gmail: Standard Google Account passwords are not accepted. You must generate a dedicated 16-character App Password at myaccount.google.com/apppasswords (2-Step Verification must be enabled). Note that changing your Google password automatically revokes previous App Passwords.', 'lead-generation-form');
			} else {
				$hint = ' ' . __('Please check your SMTP Username and Password. Many providers require a dedicated App Password or API key.', 'lead-generation-form');
			}
		} elseif (false !== stripos($error_msg, 'instantiate mail') || false !== stripos($error_msg, 'mail()')) {
			$hint = ' ' . __('Your hosting server does not have a configured local mail transfer agent (sendmail/postfix) or PHP mail() is blocked. To guarantee email delivery on live servers, switch Dispatch Method to "Custom SMTP Server" and configure SMTP credentials (such as Gmail SMTP, SendGrid, Brevo, or Mailgun).', 'lead-generation-form');
		} elseif (false !== stripos($error_msg, 'Invalid address')) {
			$hint = ' ' . __('Please verify the recipient and sender email addresses for correct syntax.', 'lead-generation-form');
		}

		wp_send_json_error(array(
			'message' => sprintf(__('Failed to dispatch test email: %s%s', 'lead-generation-form'), esc_html($error_msg), esc_html($hint))
		));
	}
}
add_action('wp_ajax_wlgf_send_test_email', 'wlgf_smtp_test_mail_callback');
add_action('wp_ajax_wlgf_smtp_test_mail', 'wlgf_smtp_test_mail_callback');

/**
 * Get sanitized RFC-compliant From Name for emails
 */
if (!function_exists('wlgf_get_from_name')) {
	function wlgf_get_from_name() {
		$blog_name = wp_specialchars_decode(get_bloginfo('name', 'raw'), ENT_QUOTES);
		return str_replace(array('"', "\r", "\n"), '', $blog_name);
	}
}

/**
 * Get safe RFC and DMARC-compliant From Email address.
 * Automatically avoids unauthenticated @gmail/@yahoo from addresses on live servers when using wp_mail.
 */
if (!function_exists('wlgf_get_from_email')) {
	function wlgf_get_from_email($email_engine = '1') {
		$settings = get_option('wlgf_settings', array());

		// 1. If SMTP engine and username is valid email, use SMTP username
		if ('2' === (string) $email_engine && !empty($settings['smtp_username']) && is_email($settings['smtp_username'])) {
			return sanitize_email($settings['smtp_username']);
		}

		$admin_email = get_option('admin_email');
		$site_host   = wp_parse_url(home_url(), PHP_URL_HOST);
		if (!empty($site_host) && 'www.' === substr($site_host, 0, 4)) {
			$site_host = substr($site_host, 4);
		}

		$is_local_host = (empty($site_host) || false !== strpos($site_host, 'localhost') || '127.0.0.1' === $site_host);
		if ($is_local_host) {
			if (!empty($settings['smtp_username']) && is_email($settings['smtp_username'])) {
				return sanitize_email($settings['smtp_username']);
			}
			if (is_email($admin_email)) {
				return sanitize_email($admin_email);
			}
			return 'admin@example.com';
		}

		// 2. Live server check
		if (is_email($admin_email)) {
			$domain = strtolower(substr(strrchr($admin_email, '@'), 1));
			$free_providers = array('gmail.com', 'googlemail.com', 'yahoo.com', 'ymail.com', 'hotmail.com', 'outlook.com', 'live.com', 'icloud.com', 'aol.com', 'zoho.com');
			if (!in_array($domain, $free_providers, true)) {
				return sanitize_email($admin_email);
			}
		}

		// If admin_email is a free public webmail (@gmail.com) on a live server,
		// PHP mail() cannot authenticate as Google and will fail DMARC/SPF checks.
		// Generate standard WordPress domain sender:
		$domain_from = 'wordpress@' . $site_host;
		if (is_email($domain_from)) {
			return $domain_from;
		}

		return is_email($admin_email) ? sanitize_email($admin_email) : 'wordpress@' . $site_host;
	}
}

// 6. smtp setup start - Encryption Mapping Fix (A1-01)
function wlgf_configure_smtp($wlgf_phpmailer)
{
	$wlgf_settings = get_option('wlgf_settings', array());
	$wlgf_email_engine = isset($wlgf_settings['email_engine']) ? sanitize_text_field($wlgf_settings['email_engine']) : '';

	if ('2' === (string) $wlgf_email_engine) {
		$wlgf_smtp_host = isset($wlgf_settings['smtp_host']) ? sanitize_text_field($wlgf_settings['smtp_host']) : '';
		$wlgf_smtp_username = isset($wlgf_settings['smtp_username']) ? sanitize_text_field($wlgf_settings['smtp_username']) : '';
		$wlgf_smtp_password = isset($wlgf_settings['smtp_password']) ? $wlgf_settings['smtp_password'] : '';
		$wlgf_smtp_encryption = isset($wlgf_settings['smtp_encryption']) ? sanitize_text_field($wlgf_settings['smtp_encryption']) : '1';
		$wlgf_smtp_port = isset($wlgf_settings['smtp_port']) ? sanitize_text_field($wlgf_settings['smtp_port']) : '587';

		// Normalize Google App Passwords (strip spaces)
		if (false !== stripos($wlgf_smtp_host, 'gmail.com') && strpos($wlgf_smtp_password, ' ') !== false) {
			$wlgf_smtp_password = str_replace(' ', '', $wlgf_smtp_password);
		}

		$wlgf_phpmailer->isSMTP();
		$wlgf_phpmailer->Host = $wlgf_smtp_host;
		$wlgf_phpmailer->SMTPAuth = true;
		$wlgf_phpmailer->Username = $wlgf_smtp_username;
		$wlgf_phpmailer->Password = $wlgf_smtp_password;
		$wlgf_phpmailer->Port = intval($wlgf_smtp_port);
		$wlgf_phpmailer->Timeout = 15;

		// Accurate encryption mapping fix: '1' => 'tls', '2' => 'ssl'
		if ('1' === (string) $wlgf_smtp_encryption) {
			$wlgf_phpmailer->SMTPSecure = 'tls';
			$wlgf_phpmailer->SMTPAutoTLS = true;
		} elseif ('2' === (string) $wlgf_smtp_encryption) {
			$wlgf_phpmailer->SMTPSecure = 'ssl';
			$wlgf_phpmailer->SMTPAutoTLS = false;
		} else {
			$wlgf_phpmailer->SMTPSecure = '';
			$wlgf_phpmailer->SMTPAutoTLS = false;
		}

		// Ensure From & Sender envelope match authenticated user for strict SMTP providers
		if (!empty($wlgf_smtp_username) && is_email($wlgf_smtp_username)) {
			$wlgf_phpmailer->Sender = $wlgf_smtp_username;
			$wlgf_phpmailer->From   = $wlgf_smtp_username;
		}

		// Local environment SSL fallback (handles missing cacert.pem in local WAMP/XAMPP/Docker)
		$wlgf_phpmailer->SMTPOptions = array(
			'ssl' => array(
				'verify_peer'       => false,
				'verify_peer_name'  => false,
				'allow_self_signed' => true,
			),
		);
	}
}
// 6. smtp setup end

// Fallback to prevent invalid 'wordpress@localhost' From email causing PHPMailer failure
add_filter('wp_mail_from', function($from_email) {
	if (empty($from_email) || strpos($from_email, '@localhost') !== false || !is_email($from_email)) {
		return wlgf_get_from_email();
	}
	return $from_email;
});

// Embed logo as inline CID attachment for localhost environments, and digital signatures / attachments for emails so external webmail clients (like Gmail) can display them without broken images
add_action('phpmailer_init', 'wlgf_attach_email_logo', 998);
function wlgf_attach_email_logo($wlgf_phpmailer) {
	$wlgf_settings = get_option('wlgf_settings', array());
	$logo_url = !empty($wlgf_settings['email_logo_url']) ? $wlgf_settings['email_logo_url'] : '';
	if (!empty($logo_url)) {
		$dirs = wp_upload_dir();
		$is_local_host = (false !== strpos($logo_url, 'localhost') || false !== strpos($logo_url, '127.0.0.1'));
		if ($is_local_host && false !== strpos($logo_url, $dirs['baseurl'])) {
			$logo_path = str_replace($dirs['baseurl'], $dirs['basedir'], $logo_url);
			if (!empty($logo_path) && file_exists($logo_path)) {
				// Only embed if cid:wlgf_email_logo is actually referenced in the email Body!
				// If an email does not use cid:wlgf_email_logo in its HTML, attaching it causes Gmail/mail clients to treat it as a loose file attachment!
				if (empty($wlgf_phpmailer->Body) || false !== strpos($wlgf_phpmailer->Body, 'cid:wlgf_email_logo')) {
					$wlgf_phpmailer->addEmbeddedImage($logo_path, 'wlgf_email_logo', basename($logo_path));
				}
			}
		}
	}

	// Dynamic Lead submission inline attachments (Digital Signatures & local file uploads)
	if (class_exists('WLGF_Email_Template') && !empty(WLGF_Email_Template::$pending_embedded_images)) {
		foreach (WLGF_Email_Template::$pending_embedded_images as $cid => $item) {
			if (!empty($item['type']) && 'string' === $item['type'] && !empty($item['data'])) {
				$name = !empty($item['name']) ? $item['name'] : 'digital-signature.png';
				$mime = !empty($item['mime']) ? $item['mime'] : 'image/png';
				if (method_exists($wlgf_phpmailer, 'addStringEmbeddedImage')) {
					$wlgf_phpmailer->addStringEmbeddedImage($item['data'], $cid, $name, 'base64', $mime);
				}
			} elseif (!empty($item['type']) && 'file' === $item['type'] && !empty($item['path']) && file_exists($item['path'])) {
				$name = !empty($item['name']) ? $item['name'] : basename($item['path']);
				$mime = !empty($item['mime']) ? $item['mime'] : 'image/jpeg';
				if (method_exists($wlgf_phpmailer, 'addEmbeddedImage')) {
					$wlgf_phpmailer->addEmbeddedImage($item['path'], $cid, $name, 'base64', $mime);
				}
			}
		}
	}
}

// 7. Gutenberg Block Registration
require_once plugin_dir_path(__FILE__) . 'includes/blocks/lead-form-block.php';

// 8. Elementor Widget Registration
require_once plugin_dir_path(__FILE__) . 'includes/elementor/class-wlgf-elementor-widget.php';

// 8.1 1-Click Industry Templates Marketplace
require_once plugin_dir_path(__FILE__) . 'includes/class-wlgf-templates.php';

// 8.2 Modern Responsive Email Template Engine
require_once plugin_dir_path(__FILE__) . 'includes/class-wlgf-email-template.php';

// 8.7 Live Preview Email Template AJAX Callback
function wlgf_preview_email_template_callback()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('Unauthorized user action.', 'lead-generation-form')));
	}

	if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wlgf-save-settings')) {
		wp_send_json_error(array('message' => __('Security verification failed.', 'lead-generation-form')));
	}

	$custom_settings = array(
		'email_logo_url'           => isset($_POST['email_logo_url']) ? esc_url_raw(wp_unslash($_POST['email_logo_url'])) : '',
		'email_logo_width'         => isset($_POST['email_logo_width']) ? intval($_POST['email_logo_width']) : 180,
		'email_header_title'       => isset($_POST['email_header_title']) ? sanitize_text_field(wp_unslash($_POST['email_header_title'])) : '',
		'email_header_bg'          => isset($_POST['email_header_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_header_bg'])) : '#1e293b',
		'email_header_color'       => isset($_POST['email_header_color']) ? sanitize_hex_color(wp_unslash($_POST['email_header_color'])) : '#ffffff',
		'email_body_greeting'      => isset($_POST['email_body_greeting']) ? sanitize_textarea_field(wp_unslash($_POST['email_body_greeting'])) : '',
		'email_body_bg'            => isset($_POST['email_body_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_body_bg'])) : '#f1f5f9',
		'email_card_bg'            => isset($_POST['email_card_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_card_bg'])) : '#ffffff',
		'email_text_color'         => isset($_POST['email_text_color']) ? sanitize_hex_color(wp_unslash($_POST['email_text_color'])) : '#334155',
		'email_footer_text'        => isset($_POST['email_footer_text']) ? sanitize_textarea_field(wp_unslash($_POST['email_footer_text'])) : '',
		'email_footer_bg'          => isset($_POST['email_footer_bg']) ? sanitize_hex_color(wp_unslash($_POST['email_footer_bg'])) : '#f8fafc',
		'email_footer_color'       => isset($_POST['email_footer_color']) ? sanitize_hex_color(wp_unslash($_POST['email_footer_color'])) : '#64748b',
		'email_show_dashboard_btn' => isset($_POST['email_show_dashboard_btn']) ? sanitize_text_field(wp_unslash($_POST['email_show_dashboard_btn'])) : '0',
	);

	require_once plugin_dir_path(__FILE__) . 'includes/class-wlgf-email-template.php';
	$html = WLGF_Email_Template::get_preview_html($custom_settings);

	wp_send_json_success(array('html' => $html));
}
add_action('wp_ajax_wlgf_preview_email_template', 'wlgf_preview_email_template_callback');



// 9. Visual Analytics & Drop-off Funnel Tracking (PRO-017)
function wlgf_track_analytics_callback()
{
	$form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
	$step = isset($_POST['step']) ? max(1, absint($_POST['step'])) : 1;
	$device = isset($_POST['device']) ? sanitize_text_field(wp_unslash($_POST['device'])) : 'desktop';

	if (empty($form_id)) {
		wp_send_json_error(array('message' => 'Missing form ID'));
	}

	// Validate form existence to prevent arbitrary unauthenticated option injection
	$form_info = get_option('wlgf_form_' . $form_id);
	if (empty($form_info) || !is_array($form_info)) {
		wp_send_json_error(array('message' => 'Form does not exist'));
	}

	// Transient-based Rate Limiting (prevent automated bot inflation)
	$remote_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '127.0.0.1';
	$rate_key = 'wlgf_tr_lmt_' . md5($remote_ip . '_' . $form_id . '_' . $step);
	if (false !== get_transient($rate_key)) {
		wp_send_json_success(array('tracked' => false, 'rate_limited' => true));
	}
	set_transient($rate_key, 1, 30); // 30-second cooldown per client/form/step

	// Update legacy view counter
	$views_key = 'wlgf_form_views_' . $form_id;
	$views = intval(get_option($views_key, 0));
	update_option($views_key, $views + 1, false);

	// Update rich analytics record
	$analytics_key = 'wlgf_analytics_' . $form_id;
	$analytics = get_option($analytics_key, array(
		'total_views' => 0,
		'total_submissions' => 0,
		'daily' => array(),
		'step_views' => array(),
		'device_breakdown' => array(
			'desktop' => 0,
			'mobile' => 0,
			'tablet' => 0,
		),
	));

	$analytics['total_views'] = isset($analytics['total_views']) ? ($analytics['total_views'] + 1) : 1;

	$today = gmdate('Y-m-d');
	if (!isset($analytics['daily'][$today])) {
		$analytics['daily'][$today] = array('views' => 0, 'submissions' => 0);
	}
	$analytics['daily'][$today]['views']++;

	// Track Step Views for Funnel Analysis
	if (!isset($analytics['step_views'][$step])) {
		$analytics['step_views'][$step] = 0;
	}
	$analytics['step_views'][$step]++;

	// Track Device Breakdown
	if (!in_array($device, array('desktop', 'mobile', 'tablet'), true)) {
		$device = 'desktop';
	}
	if (!isset($analytics['device_breakdown'][$device])) {
		$analytics['device_breakdown'][$device] = 0;
	}
	$analytics['device_breakdown'][$device]++;

	// Keep daily history capped at 90 days to prevent option bloat
	if (count($analytics['daily']) > 90) {
		ksort($analytics['daily']);
		$analytics['daily'] = array_slice($analytics['daily'], -90, 90, true);
	}

	update_option($analytics_key, $analytics, false);
	wp_send_json_success(array('tracked' => true));
}
add_action('wp_ajax_wlgf_track_analytics', 'wlgf_track_analytics_callback');
add_action('wp_ajax_nopriv_wlgf_track_analytics', 'wlgf_track_analytics_callback');

// 10. Download Lead Submission PDF / Invoice (PRO-018 - Feature available in Pro)
function wlgf_download_lead_pdf_callback()
{
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('Unauthorized access.', 'lead-generation-form'), 403);
	}

	wp_die(
		'<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;max-width:540px;margin:60px auto;padding:36px;background:#fff;border-radius:14px;box-shadow:0 12px 30px rgba(0,0,0,0.08);text-align:center;">' .
		'<div style="font-size:42px;color:#f59e0b;margin-bottom:12px;">★</div>' .
		'<h2 style="color:#0f172a;margin:0 0 12px;font-size:22px;">' . esc_html__('PDF Invoices & Receipts with E-Signatures is a Pro Feature', 'lead-generation-form') . '</h2>' .
		'<p style="color:#64748b;line-height:1.6;font-size:15px;margin-bottom:24px;">' . esc_html__('Upgrade to Lead Generation Form Pro to generate pure-PHP vector PDF receipts with legal signature certificates, client verification boxes, and automatic download links.', 'lead-generation-form') . '</p>' .
		'<a href="https://wpfrank.com/wordpress-plugins/lead-generation-form-pro/" target="_blank" rel="noopener noreferrer" style="display:inline-block;background:#0284c7;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;">' . esc_html__('Upgrade to Pro — $49 (One-Time)', 'lead-generation-form') . '</a>' .
		'<div style="margin-top:14px;"><a href="' . esc_url(admin_url('admin.php?page=wlgf-free-vs-pro')) . '" style="color:#64748b;font-size:13px;text-decoration:none;">' . esc_html__('View Free vs Pro Comparison Table', 'lead-generation-form') . ' &rarr;</a></div>' .
		'</div>',
		esc_html__('Available in Pro Edition', 'lead-generation-form'),
		array('response' => 200)
	);
}
add_action('wp_ajax_wlgf_download_lead_pdf', 'wlgf_download_lead_pdf_callback');

/**
 * Factory Reset Callback - Wipes all plugin settings, forms, leads, and analytics,
 * then restores original factory defaults.
 */
function wlgf_factory_reset_callback() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized access. Only administrators can perform a factory reset.', 'lead-generation-form' ) ) );
	}

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wlgf-factory-reset' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security token expired or invalid. Please refresh the page and try again.', 'lead-generation-form' ) ) );
	}

	global $wpdb;

	// 1. Delete all static and dynamic wlgf options from wp_options table
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE %s OR `option_name` LIKE %s OR `option_name` LIKE %s",
			$wpdb->esc_like( 'wlgf_' ) . '%',
			$wpdb->esc_like( '_transient_wlgf_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wlgf_' ) . '%'
		)
	);

	// 2. Clear known transients
	delete_transient( 'wlgf_version_check' );
	delete_transient( 'wlgf_gutenberg_forms_list' );
	delete_transient( 'wlgf_elementor_forms_list' );

	// 3. Re-run default installation script to recreate sample forms & default settings
	require plugin_dir_path( __FILE__ ) . 'includes/install-script.php';

	// 4. Update current version option
	if ( defined( 'WLGF_PLUGIN_VERSION' ) ) {
		update_option( 'wlgf_current_version', WLGF_PLUGIN_VERSION );
	}

	wp_send_json_success( array(
		'message' => __( 'Plugin has been successfully reset to factory defaults. Reloading...', 'lead-generation-form' ),
	) );
}
add_action( 'wp_ajax_wlgf_factory_reset', 'wlgf_factory_reset_callback' );