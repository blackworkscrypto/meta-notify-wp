<?php

/**
 * Copyright (c) Author <contact@website.com>
 *
 * This source code is licensed under the license
 * included in the root directory of this application.
 */

/**
 * Handle AJAX activation request
 */
function metanotify_activate_site()
{
	error_log("inside " . __FUNCTION__);
	error_log(print_r($_POST['nonce'], true));
	if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'm3t4n0t1fy')) {
		exit(json_encode(array('success' => false)));
	}
	if (empty($_POST['email']) || empty($_POST['plugin'])) {

		exit(json_encode([
			'success' => false,
			'message' => __('Please enter your email address!', 'meta-notify')
		]));
	}
	MetanotifyRestApi::setupKeypair();

	$email = sanitize_email($_POST['email']);
	$plugin = sanitize_title($_POST['plugin']);
	$status = MetanotifyRestApi::getActivationStatus($plugin);


	if (!$status) {
		$status = MetanotifyRestApi::registerSite($plugin, $email);


		sleep(1);
		if ($status) {
			if ($status === 'registered') {
				$resp = MetanotifyCategoryApi::addSite($email, $plugin);

				exit(json_encode([
					'success' => true,
					'message' => __('The plugin has been activated successfully!', 'meta-notify')
				]));

			}
		} else {
			exit(json_encode([
				'success' => false,
				'message' => __('Failed to activate the plugin. Please try again!', 'meta-notify')
			]));
		}
	}
}
add_action('wp_ajax_metanotify_activate_site', 'metanotify_activate_site');


/**
 * Handle CATEGORY ADD  request
 */
function metanotify_add_category()
{
	if (empty($_POST['categoryName'])) {

		exit(json_encode([
			'success' => false,
			'message' => __('Please enter correct category name!', 'meta-notify')
		]));
	}


	$categoryName = sanitize_title($_POST['categoryName']);
	$response = MetanotifyCategoryApi::addCategory(get_option('metanotify_site_id', ""), $categoryName);

	exit(json_encode($response));


}
add_action('wp_ajax_metanotify_add_category', 'metanotify_add_category');

/**
 * Handle CATEGORY Serach  request
 */
function metanotify_search_category()
{
	if (empty($_GET['categoryId'])) {

		exit(json_encode([
			'success' => false,
			'message' => __('Please enter correct   category id!', 'meta-notify')
		]));
	}


	$categoryId = sanitize_title($_GET['categoryId']);
	$response = MetanotifyCategoryApi::searchCategory(get_option('metanotify_site_id', ""), $categoryId);

	exit(json_encode($response));


}
add_action('wp_ajax_metanotify_search_category', 'metanotify_search_category');
/**
 * Handle Notification Serach  request
 */
function metanotify_search_notification()
{
	if (empty($_GET['notificationId'])) {

		exit(json_encode([
			'success' => false,
			'message' => __('Please enter correct  notification id!', 'meta-notify')
		]));
	}


	$notificationId = sanitize_title($_GET['notificationId']);
	$response = MetanotifyCategoryApi::searchNotification(get_option('metanotify_site_id', ""), $notificationId);
	exit(json_encode($response));


}
add_action('wp_ajax_metanotify_search_notification', 'metanotify_search_notification');
/**
 * Handle CATEGORY DELETE  request
 */
function metanotify_delete_category()
{
	if (empty($_POST['categoryId'])) {

		exit(json_encode([
			'success' => false,
			'message' => __('Please select a category!', 'meta-notify')
		]));
	}


	$categoryId = sanitize_title($_POST['categoryId']);
	$response = MetanotifyCategoryApi::deleteCategory(get_option('metanotify_site_id', ""), $categoryId);
	exit(json_encode($response));


}
add_action('wp_ajax_metanotify_delete_category', 'metanotify_delete_category');
/**
 * Handle NOTIFICATION ADD  request
 */
function metanotify_add_notification()
{
	if (empty($_POST['notificationTitle'])) {

		exit(json_encode([
			'success' => false,
			'message' => __('Please enter correct Title !', 'meta-notify')
		]));
	}

	$notificationTitle = sanitize_text_field( $_POST['notificationTitle'] );
	$notificationBody = sanitize_textarea_field( $_POST['notificationBody'] );
	$notificationImage = esc_url( $_POST['notificationImage'] );
	$notificationBody = substr($notificationBody, 0, 100);
	$notificationCategory = $_POST['notificationCategory'];
	$notificationCategory = ($notificationCategory === '') ? [] : explode(",", $notificationCategory);

	$notificationVisitors = $_POST['notificationVisitors'];
	$notificationVisitors = ($notificationVisitors === '') ? [] : explode(",", $notificationVisitors);

	$response = MetanotifyCategoryApi::addNotification(get_option('metanotify_site_id', ""), $notificationTitle, $notificationBody, $notificationImage, $notificationCategory, $notificationVisitors);

	exit(json_encode($response));


}
add_action('wp_ajax_metanotify_add_notification', 'metanotify_add_notification');



/**
 * Handle VISITOR ADD  request
 */
function metanotify_add_visitor()
{
	if (empty($_POST['walletAddress'])) {

		exit(json_encode([
			'success' => false,
			'message' => __('Please enter correct visitor !', 'meta-notify')
		]));
	}

	$walletAddress = sanitize_title($_POST['walletAddress']);
	$response = MetanotifyCategoryApi::addVisitor(get_option('metanotify_site_id', ""), $walletAddress);
	exit(json_encode($response));


}
add_action('wp_ajax_metanotify_add_visitor', 'metanotify_add_visitor');
function metanotify_on_enqueue_block_editor_assets()
{
	wp_enqueue_script('metanotify-sidebar-plugin', META_NOTIFY_URI . 'assets/js/block-sidebar-plugin.min.js', array('wp-blocks', 'wp-element', 'wp-components'), META_NOTIFY_VER, true);
}
add_action('enqueue_block_editor_assets', 'metanotify_on_enqueue_block_editor_assets');

