<?php

/**
 * Copyright (c) Author <contact@website.com>
 *
 * This source code is licensed under the license
 * included in the root directory of this application.
 */

/**
 * Sync data with server
 */
function metanotify_sync_data_with_server()
{
	error_log("inside " . __FUNCTION__);
	global $wpdb;
	$sessions = $wpdb->get_results(sprintf("SELECT * FROM metanotify_notifications  ORDER BY id DESC;"));


	if ($sessions) {
		foreach ($sessions as $session) {
			$notificationId = $session->notification_id;
			$notificationStatus = $session->notification_status;
			$site_id = get_option('metanotify_site_id', "");
			$response = MetanotifyCategoryApi::searchNotification($site_id, $notificationId);
			$response = $response->notificationStatus;
			if ($response != $notificationStatus) {
				$wpdb->query($wpdb->prepare("UPDATE metanotify_notifications set notification_status ='%s' where notification_id='%s'", $response, $notificationId));
			}
		}
	}
}
add_action('metanotify_sync_data', 'metanotify_sync_data_with_server');

function metanotify_send_notification($post_ID, $post_after, $post_before)
{
	error_log("inside " . __FUNCTION__);

	if ($post_before->post_status !== 'auto-draft' || $post_after->post_status !== 'publish') {
        return;
    }

		$notificationTitle = $post_after->post_title;
		$notificationBody = $post_after->post_content;
		$notificationBody = substr($notificationBody, 0, 50);

		$attachments = get_posts(
			array(
				'post_type' => 'attachment',
				'posts_per_page' => 1,
				'post_parent' => $post_ID,
				'post_mime_type' => 'image',
				'orderby' => 'ID',
				'order' => 'ASC'
			)
		);
		$notificationImage = '';
		if ($attachments) {
			$image_url = wp_get_attachment_url($attachments[0]->ID);
			$notificationImage = $image_url;
		}
		
		$post_link = get_permalink($post_ID);

		$link_html = 'View full post';
		$notificationBody = $notificationBody . '...' . '<a href="' . $post_link . '">' . $link_html . '</a>';
		
		$response = MetanotifyCategoryApi::addNotification(get_option('metanotify_site_id', ""), $notificationTitle, $notificationBody, $notificationImage, [], []);
		error_log(print_r($response, true));
		exit(json_encode($response));
	}



add_action('post_updated', 'metanotify_send_notification', 10, 3);

function metanotify_catgeory_insertion($category_id)
{

	$category = get_term_by('id', $category_id, 'category');
	$category_name = $category->name;
	$site_id = get_option('metanotify_site_id', "");
	$response = MetanotifyCategoryApi::addCategory($site_id, $category_name);



}
add_action('create_category', 'metanotify_catgeory_insertion', 10, 1);
function category_deletion($category_id)
{
	$category = get_term_by('id', $category_id, 'category');
	$category_name = $category->name;
	$site_id = get_option('metanotify_site_id', "");
	error_log("inside " . __FUNCTION__);
	error_log(print_r($category_name, true));
	MetanotifyCategoryApi::deleteCategory($site_id, $category_name);
}
add_action('delete_category', 'category_deletion', 10, 1);



/**
 * Bind the `rest_api_init` hook
 *
 * @see https://developer.wordpress.org/reference/hooks/rest_api_init/
 */
function metanotify_on_restapi_init($server)
{
	MetanotifyRestApi::registerRoutes();
}
add_action('rest_api_init', 'metanotify_on_restapi_init');

/**
 * Bind the `init` hook
 *
 * @see https://developer.wordpress.org/reference/hooks/init/
 */
function metanotify_on_wp_init()
{
	wp_register_style('meta-notify-block-editor', META_NOTIFY_URI . 'assets/css/meta-notify-block-editor.min.css', array(), META_NOTIFY_VER);

	wp_register_script('meta-notify-block-editor', META_NOTIFY_URI . 'assets/js/meta-notify-block-editor.min.js', array(), META_NOTIFY_VER, true);

	register_post_meta(
		'',
		'metanotifyDisabled',
		array(
			'type' => 'string',
			'single' => 1,
			'default' => '',
			'show_in_rest' => 1,
		)
	);

	register_block_type(META_NOTIFY_DIR);
}
add_action('init', 'metanotify_on_wp_init');

/**
 * Handle AJAX unlocking view permission
 */
function metanotify_unlock_user()
{
	if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'm3t4n0t1fy')) {
		exit(json_encode(array('success' => false)));
	}

	global $wpdb;

	$settings = array_merge(
		array('cookie_duration' => 48),
		(array) get_option('metanotifySettings')
	);

	if (empty($settings['cookie_duration'])) {
		$settings['cookie_duration'] = 48;
	}

	$ip = metanotify_guess_client_ip();
	$agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
	$link = esc_url_raw($_POST['link']);
	$email = '';
	$address = sanitize_text_field($_POST['address']);
	$balance = floatval($_POST['balance']);
	$wallet_type = sanitize_text_field($_POST['walletType']);
	$expire_time = intval($settings['cookie_duration']) * HOUR_IN_SECONDS + strtotime('now');
	$inserted = $wpdb->get_var(sprintf("SELECT ID FROM metanotify_sessions WHERE wallet_address='%s' LIMIT 1;", $address));
	$notificationCategoryChoosen = $_POST['notificationCategoryChoosen'];
	error_log("inside " . __FUNCTION__);
	error_log(print_r($notificationCategoryChoosen, true));
	// error_log(print_r($inserted, true));
	// $term_id = get_terms([
	// 	'fields' => 'ids',
	// 	'taxonomy' => 'category',
	// 	'name' => 'uncategorized',
	// 	'hide_empty' => false,
	// ]);

	// if ($notificationCategoryChoosen == NULL) {
	// 	array_push($notificationCategoryChoosen,$term_id);
	// 	error_log("after pushing uncatagorized: ");
	// 	error_log(print_r($notificationCategoryChoosen, true));
	// }
	if ($inserted) {
		if (
			setcookie(
				'isValidChannelUser',
				1,
				array(
					'path' => '/',
					'secure' => is_ssl(),
					'expires' => $expire_time,
					'httponly' => true,
					'samesite' => 'Strict'
				)
			)
		) {
			exit(
				json_encode(
					array(
						'success' => true,
						'message' => __('Account connected  successfully and Opted in to our channel...', 'meta-notify'),
					)
				)
			);
		} else {
			exit(
				json_encode(
					array(
						'success' => false,
						'message' => __('Failed to set cookies. Please try again!', 'meta-notify'),
					)
				)
			);
		}
	} else {
		$inserted = $wpdb->insert(
			'metanotify_sessions',
			array(
				'ip' => $ip,
				'agent' => truncate($agent, 500),
				'link' => $link,
				'email' => $email,
				'balance' => $balance,
				'wallet_type' => $wallet_type,
				'wallet_address' => $address
			)
		);
	}

	if ($inserted) {
		$response = MetanotifyCategoryApi::addVisitor(get_option('metanotify_site_id', ""), $address);
		$visitorId = $response->visitorID;
		if ($notificationCategoryChoosen != NULL) {
			$response = MetanotifyCategoryApi::updateVisitor(get_option('metanotify_site_id', ""), $visitorId, $notificationCategoryChoosen);
		}

		if (
			setcookie(
				'isValidChannelUser',
				1,
				array(
					'path' => '/',
					'secure' => is_ssl(),
					'expires' => $expire_time,
					'httponly' => true,
					'samesite' => 'Strict'
				)
			)
		) {
			exit(
				json_encode(
					array(
						'success' => true,
						'message' => __('Account connected successfully! Opted to our Channel...', 'meta-notify'),
					)
				)
			);
		} else {
			exit(
				json_encode(
					array(
						'success' => false,
						'message' => __('Failed to set cookies. Please try again!', 'meta-notify'),
					)
				)
			);
		}
	} else {
		exit(
			json_encode(
				array(
					'success' => false,
					'message' => htmlspecialchars($wpdb->last_error),
				)
			)
		);
	}
}
add_action('wp_ajax_metanotify_unlock_user', 'metanotify_unlock_user');
add_action('wp_ajax_nopriv_metanotify_unlock_user', 'metanotify_unlock_user');