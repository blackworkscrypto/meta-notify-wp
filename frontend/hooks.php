<?php

/**
 * Copyright (c) Author <contact@website.com>
 *
 * This source code is licensed under the license
 * included in the root directory of this application.
 */

/**
 * Include popup
 */
add_action('wp_footer', function () {
	require META_NOTIFY_DIR . 'frontend/templates/popup.php';
});

/**
 * Print callout appearance CSS
 */
function metanotify_print_callout_css()
{
	// $notify_settings = get_option('mrv_notify_settings');
	// $notify_font = (isset($notify_settings['notify_font']) && !empty($notify_settings['notify_font'])) ? $notify_settings['notify_font'] : "";
	// $notify_font_size = (isset($notify_font['font-size']) && !empty($notify_font['font-size'])) ? '--wallet-title-font-size:' . $notify_font['font-size'] . 'px' : "";
	// $notify_bg_color = (isset($notify_settings['notify_bg_color']) && !empty($notify_settings['notify_bg_color'])) ? '--bg_color:' . $notify_settings['notify_bg_color'] : "";
	// $notify_border = (isset($notify_settings['notify_border']) && !empty($notify_settings['notify_border'])) ? $notify_settings['notify_border'] : "";
	// $notify_border_width = (isset($notify_settings['notify_border']) && !empty($notify_settings['notify_border'])) ? $notify_settings['notify_border'] : "";
	// $notify_border_color = (isset($notify_settings['notify_border']) && !empty($notify_settings['notify_border'])) ? $notify_settings['notify_border'] : "";
	// $notify_border_radius = (isset($notify_settings['notify_border']) && !empty($notify_settings['notify_border'])) ? $notify_settings['notify_border'] : "";
	// $notify_border_style = (isset($notify_settings['notify_border']) && !empty($notify_settings['notify_border'])) ? $notify_settings['notify_border'] : "";
	// $connect_button_border = (isset($notify_settings['connect_button_border']) && !empty($notify_settings['connect_button_border'])) ? $notify_settings['connect_button_border'] : "";
	// $thanks_button_border = (isset($notify_settings['thanks_button_border']) && !empty($notify_settings['thanks_button_border'])) ? $notify_settings['thanks_button_border'] : "";
	$settings = array_merge(
		array(
			'bg_color' => '#FFFF',
			'connect_button_text_color' => 'rgba(209, 228, 221, 1)',
			'connect_button_bg_color' => '#841212',
			'connect_button_bg_hover_color' => '#C17985',
			'connect_button_text_hover_color' => 'rgba(40, 48, 61, 1)',
			'thanks_button_text_color' => '#000000',
			'thanks_button_bg_color' => '#A7A7A7',
			'thanks_button_bg_hover_color' => 'rgba(209, 228, 221, 1)',
			'thanks_button_text_hover_color' => 'rgba(40, 48, 61, 1)',
			'message_color' => '#000000',
			'button_text_size' => 18,
			'connect_button_border_size' => 3,
			'thanks_button_border_size' => 2,
			'message_text_size' => 24,
			'button_padding_x' => 15,
			'button_padding_y' => 30,
		),
		(array) get_option('metanotifySettings')
	);

	echo '<style type="text/css">
    :root {
        --metanotify-bg-color:' . $settings['bg_color'] . ';
        --metanotify-bg-color-0:' . substr($settings['bg_color'], 0, -2) . '0);
        --metanotify-connect-text-button-color:' . $settings['connect_button_text_color'] . ';
		--metanotify-thanks-text-button-color:' . $settings['thanks_button_text_color'] . ';
		--metanotify-message-color:' . $settings['message_color'] . ';
        --metanotify-connect-button-bg-color:' . $settings['connect_button_bg_color'] . ';
		--metanotify-thanks-button-bg-color:' . $settings['thanks_button_bg_color'] . ';
		--metanotify-button-padding-x:' . $settings['button_padding_x'] . 'px;
        --metanotify-button-padding-y:' . $settings['button_padding_y'] . 'px;
        --metanotify-button-text-size:' . $settings['button_text_size'] . 'px;
        --metanotify-message-text-size:' . $settings['message_text_size'] . 'px;
		--metanotify-connect-button-border-size:' . $settings['connect_button_border_size'] . 'px;
        --metanotify-connect-button-hover-color:' . $settings['connect_button_text_hover_color'] . ';
        --metanotify-connect-button-hover-bg-color:' . $settings['connect_button_bg_hover_color'] . ';
		--metanotify-thanks-button-border-size:' . $settings['thanks_button_border_size'] . 'px;
        --metanotify-thanks-button-hover-color:' . $settings['thanks_button_text_hover_color'] . ';
        --metanotify-thanks-button-hover-bg-color:' . $settings['thanks_button_bg_hover_color'] . ';
    }
    </style>';
}
add_action('wp_head', 'metanotify_print_callout_css', PHP_INT_MAX);

/**
 * Enqueue scripts
 */
function metanotify_on_wp_enqueue_scripts()
{
	$settings = (array) get_option('metanotifySettings');

	wp_enqueue_style('meta-notify', META_NOTIFY_URI . 'assets/css/frontend.min.css', [], META_NOTIFY_VER);

	wp_enqueue_script('meta-notify', META_NOTIFY_URI . 'assets/js/frontend.min.js', [], META_NOTIFY_VER, true);

	wp_enqueue_script('metanotify-bundle', META_NOTIFY_URI . 'assets/js/bundle.min.js', [], META_NOTIFY_VER, true);

	wp_localize_script(
		'meta-notify',
		'metanotify',
		array(
			'nonce' => wp_create_nonce('m3t4n0t1fy'),
			'ajaxURL' => admin_url('admin-ajax.php'),
			'pluginVer' => META_NOTIFY_VER,
			'pluginUri' => META_NOTIFY_URI,
			'infuraKey' => 'e7cdb73a875e4f33b04a8e5488a620f4',
			'solanaCluster' => 'mainnet-beta',
			'settings' => array_merge(
				array(
					'name' => 'Metanotify',
					'button_text' => __('Connect Wallet', 'meta-notify'),
					'message' => __('We would like to send Notifications, Connect your wallet!', 'meta-notify'),
					'minimum_balance' => 0,
					'balance_message' => __('Sorry, insufficient balance!', 'meta-notify')
				),
				$settings
			),
		)
	);

	wp_localize_script(
		'meta-notify',
		'metanotifyI18n',
		array(
			'unknowErr' => __('Unknown error occured. Please try again!', 'meta-notify'),
			'serviceErr' => __('Service unavailable! Please try again!', 'meta-notify'),
			'balanceErr' => __('Sorry, insufficient balance!', 'meta-notify'),
			'invalidEmail' => __('Invalid email address. Please correct the email!', 'meta-notify'),
			'emptyEmail' => __('Please enter your mailee address!', 'meta-notify'),
			'consentText' => __('You must agree to our Privacy Policy!', 'meta-notify'),
		)
	);
}
add_action('wp_enqueue_scripts', 'metanotify_on_wp_enqueue_scripts', 9999);

/**
 * Maybe show the callout
 */
function metanotify_maybe_show_callout($metanotify_content)
{
	global $wp_query;

	if (defined('REST_REQUEST') || !$wp_query->is_main_query() || current_user_can('edit_posts')) {
		return $metanotify_content;
	}

	if (get_post_meta($wp_query->post->ID, 'metanotifyDisabled', true)) {
		return $metanotify_content;
	}

	$crawler_detector = new Jaybizzle\CrawlerDetect\CrawlerDetect();

	if (empty($_COOKIE['isValidChannelUser']) && !$crawler_detector->isCrawler()) {
		$metanotify_content = trim($metanotify_content);
		$settings = array_merge(
			array(
				'metanotify_message' => __('Sign up and get notified when we release new content by connecting your wallet.', 'meta-notify'),
				'button_text' => __('Connect Wallet', 'meta-notify'),
				'metanotify_consent_text' => __('I accept the Privacy Policy', 'meta-notify'),
				'consent_error_text' => __('You must agree to our Privacy Policy!.', 'meta-notify'),
				'excerpt_length' => 150,
				'disable_auto_insert' => false,
				'metanotify_checkbox_consent_state' => 1,
				'metanotify_privacy_policy_url' => 'https://www.blackworks.io/metaplugin-terms',
			),
			(array) get_option('metanotifySettings')
		);
		$class_name = 'metanotify';
		preg_match('/<div\sclass="meta-notify-callout.+/s', $metanotify_content, $matches);
		if (!empty($matches[0])) {
			$position = strpos($metanotify_content, '<div class="meta-notify-callout');
			$metanotify_content = str_replace($matches[0], '', $metanotify_content);
			if (0 === $position) {
				$class_name .= ' hide-backdrop';
			}
			if (false !== strpos($matches[0], 'meta-notify-callout hide-email')) {
				$class_name .= ' hide-email';
			}
		} else {
			if ($settings['disable_auto_insert']) {
				return $metanotify_content;
			} else {
				// Remove the substring starting
				$metanotify_content = preg_replace('/<div id="metaContainer"><div id="metaNotifyTextbox".*?<input id="metaNotifyAgree" type="checkbox"/s', '', $metanotify_content);
				error_log(print_r($metanotify_content, true));
				$metanotify_content = preg_replace('/><a href.*?<\/div><\/div><\/div>/s', '', $metanotify_content);
				error_log(print_r($metanotify_content, true));
			}
		}
		$metanotify_checked = $settings['metanotify_checkbox_consent_state'] ? ' checked' : '';

		// Check if the content has already been displayed
		static $content_displayed = false;
		if ($content_displayed) {
			return ''; // Return empty string if content has already been displayed
		}
		$content_displayed = true; // Set flag to indicate content has been displayed


		// Get the number of categories
		$categories_count = wp_count_terms('category');
		// Set the disabled attribute for the button if there are no categories
		$metanotify_message_snippet = $categories_count <= 1 ? '' : '<h3 id="meta-notify-box-message" class="metanotifyMessage">' . $settings['metanotify_message'] . ' </h3>';
		$metanotify_content = '<div id="metaContainer"><div id="metaNotifyTextbox" style="margin-bottom: 5rem" class="metanotify">
			<div class="metanotifyMask">
			  <img class="metanotifyImage" src="' . META_NOTIFY_URI . 'assets/images/notify.png">
			  <div style="padding-left: 15px;">
			  <h3 id="meta-notify-box-message" class="metanotifyMessage">' . $settings['metanotify_message'] . ' </h3>
			  </div>
			  <div style="clear: both;"></div>
			</div>
			<div id="buttonGroup">
			  <div style="text-align: center;">
			  <button class="metaNoThanks" id="metaNoThanks">NO THANKS</button>
		  	  <button class="metanotifyConnect">GET NOTIFIED</button>
		  
			  </div>
			  <p class="metaNotifyTick"><label><input id="metaNotifyAgree" type="checkbox" 
			   value="1"' . $metanotify_checked . '><a href="' . $settings['metanotify_privacy_policy_url'] . '" target="_blank">' . $settings['metanotify_consent_text'] . '</a></label></p></div></div></div>' . $metanotify_content;
	}

	return $metanotify_content;
}

add_filter('the_content', 'metanotify_maybe_show_callout', PHP_INT_MAX - 1);