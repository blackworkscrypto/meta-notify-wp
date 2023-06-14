<?php

/**
 * Plugin Name: Meta Notify
 * Description: web  push notifications for users into their wallet account
 * Author: Blackworks.io
 * Author URI: https://blackworks.io
 * Version: 1.0.0
 */

// Plugin version.
define('META_NOTIFY_VER', '1.0.0');



// Plugin base DIR.
define('META_NOTIFY_DIR', __DIR__ . '/');

// Plugin base URI.
define('META_NOTIFY_URI', plugins_url('/', __FILE__));

// Load vendor resources.
require __DIR__ . '/vendor/autoload.php';

// Load common resources.
require __DIR__ . '/common/class-rest-api.php';
require __DIR__ . '/common/functions.php';
require __DIR__ . '/common/hooks.php';

/**
 * Do activation
 *
 * @see https://developer.wordpress.org/reference/functions/register_activation_hook/
 *
 * @param bool $network Being activated on multisite or not.
 * @throws Exception
 */
function metanotify_activate($network)
{
	global $wpdb;

	try {
		if (version_compare(PHP_VERSION, '7.2', '<')) {
			throw new Exception(__('This plugin requires PHP version 7.2 at least!', 'meta-notify'));
		}

		if (!get_option('metanotifyActivated') && !get_transient('metanotify_init_activation') && !set_transient('metanotify_init_activation', 1)) {
			throw new Exception(__('Failed to initialize setup wizard.', 'meta-notify'));
		}

		if (!function_exists('dbDelta')) {
			require ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$wpdb->query('DROP TABLE IF EXISTS metanotify_sessions;');

		dbDelta("CREATE TABLE IF NOT EXISTS metanotify_sessions (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			ip VARCHAR(32) NOT NULL DEFAULT '',
			agent VARCHAR(512) NOT NULL DEFAULT '',
			link VARCHAR(255) NOT NULL DEFAULT '',
			email VARCHAR(126) NOT NULL DEFAULT '',
			balance VARCHAR(32) NOT NULL DEFAULT '',
			wallet_type VARCHAR(16) NOT NULL DEFAULT '0',
			wallet_address VARCHAR(126) NOT NULL DEFAULT '',
			visited_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			synced TINYINT DEFAULT 0,
			PRIMARY KEY  (id)
		);");

		dbDelta("CREATE TABLE IF NOT EXISTS metanotify_notifications (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			notification_id BIGINT NOT NULL,
			notification_title VARCHAR(512) NOT NULL DEFAULT '',
			notification_body VARCHAR(255) NOT NULL DEFAULT '',
			notification_image VARCHAR(126) NOT NULL DEFAULT '',
			notification_status VARCHAR(512) NOT NULL DEFAULT '',
			PRIMARY KEY  (id)
		);");

		MetanotifyRestApi::setupKeypair();


		if (!wp_next_scheduled('metanotify_sync_data')) {
			if (!wp_schedule_event(time(), 'every_sixty_minutes', 'metanotify_sync_data')) {
				throw new Exception(__('Failed to connect to remote server!', 'meta-notify'));
			}
		}
	} catch (Exception $e) {
		if (defined('DOING_AJAX') && DOING_AJAX) {
			header('Content-Type: application/json; charset=' . get_option('blog_charset'), true, 500);
			exit(wp_json_encode([
				'success' => false,
				'name' => __('Plugin Activation Error', 'meta-notify'),
				'message' => $e->getMessage(),
			]));
		} else {
			exit($e->getMessage());
		}
	}
}
add_action('activate_meta-notify/meta-notify.php', 'metanotify_activate');

function metanotify_run_every_sixty_minutes($schedules)
{
	$schedules['every_sixty_minutes'] = array(
		'interval' => 3600,
		'display' => __('Every 60 Minutes', 'textdomain')
	);
	return $schedules;
}

add_filter('cron_schedules', 'metanotify_run_every_sixty_minutes');

/**
 * Do installation
 *
 * @see https://developer.wordpress.org/reference/hooks/plugins_loaded/
 */
function metanotify_install()
{
	// Make sure translation is available.
	load_plugin_textdomain('meta-notify', false, 'meta-notify/languages');

	// Load resources.
	if (is_admin()) {
		if (!class_exists('WP_List_Table')) {
			require ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		if (!function_exists('deactivate_plugins')) {
			require ABSPATH . 'wp-admin/includes/plugin.php';
		}
		require __DIR__ . '/admin/class-terms-page.php';
		require __DIR__ . '/admin/class-categories-page.php';
		// require_once __DIR__ . '/admin/notify-codestar-framework/codestar-framework.php';
		// require __DIR__ . '/admin/menu.php';
		// require __DIR__ . '/admin/mrv-notify-settings.php';
		require __DIR__ . '/admin/class-settings-page.php';
		require __DIR__ . '/admin/class-license-manager.php';
		require __DIR__ . '/admin/hooks.php';
	} else {
		require __DIR__ . '/frontend/hooks.php';
	}
}
add_action('plugins_loaded', 'metanotify_install', 10, 0);
/*
|--------------------------------------------------------------------------
|  admin noticce for add infura project key
|--------------------------------------------------------------------------
*/
function meta_admin_notice_warning()
{
	$settings = (array) get_option('metaNotifySettings');
	static $initialized = false;
	if ((!isset($settings['infura_project_id']) && empty($settings['infura_project_id'])) || $check == true) {
		echo '<div class="notice notice-error is-dismissible">
<p>Important:Please enter an infura API-KEY for WalletConnect to work <a style="font-weight:bold" href="' . esc_url(get_admin_url(null, 'admin.php?page=metanotify-settings')) . '">Link</a></p>
</div>';
		$check = false;
		$initialized = true;
	}

	$infura_id = isset($settings['infura_project_id_2']) ? $settings['infura_project_id_2'] : "";
	if ($infura_id == "") {
		$settings['infura_project_id_2'] = MetanotifyRestApi::getInfuraId();

		update_option('metaNotifySettings', $settings);
		$check = true;
	} else {
		$check = false;
	}
}
if (is_admin()) {
	add_action('admin_notices', 'meta_admin_notice_warning');
}