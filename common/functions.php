<?php

/**
 * Common functions
 */

/**
 * Guess client IP
 */
global $wp;
function metanotify_guess_client_ip()
{
    // Equally untrustworthy.
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
    } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']);
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED']);
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_FORWARDED_FOR']);
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_FORWARDED']);
    }

    // Maybe trustworthy.
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }

    return filter_var(trim($ip), FILTER_VALIDATE_IP) ?: '';
}

if (!function_exists('truncate')) {
    function truncate($string, $length, $dots = "...")
    {
        return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
    }
    $current_url = add_query_arg($wp->query_vars);
}

function bootstrap_notify_scripts()
{
    if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'metanotify-categories') {
        wp_enqueue_style('meta-notify-bootstrap', META_NOTIFY_URI . 'assets/css/bootstrap.min.css', [], META_NOTIFY_VER);
        wp_enqueue_script('meta-notify-bootstrap', META_NOTIFY_URI . 'assets/js/bootstrap.min.js', ['jquery'], META_NOTIFY_VER, true);
    }
}
add_action('admin_enqueue_scripts', 'bootstrap_notify_scripts', 11);


if (!function_exists('meta_notify_slugify')) {
    function meta_notify_slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}