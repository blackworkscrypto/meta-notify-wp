<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * MetanotifyRestApi
 *
 * Handle the activation and registration of the website with the central server.
 */
final class MetanotifyCategoryApi
{
    const BASE_URL = 'https://epns-service-staging.service.metaplugins.io';
    const NAMESPACE = 'meta-notify/v1';
    /**
     * Register REST routes
     *
     * @see https://developer.wordpress.org/reference/functions/register_rest_route/
     */
    static function registerRoutes()
    {




        register_rest_route(self::NAMESPACE , 'categories', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => __CLASS__ . '::fetchCategories',
                'permission_callback' => '__return_true'
            ]
        ]);
    }
    static function getCategories($siteID)
    {

        $transient = MetanotifyRestApi::getAuthToken("meta-notify");
        $resp = self::epns_request_api(
            '/v1/categories/',
            'GET',
            [],
            [
                "siteID: $siteID",
            ],
            $transient
        );
        if ($resp['status'] === 200) {
            return json_decode($resp['body']);
        }
        return false;

    }
    static function getNotifications($siteID)
    {

        $transient = MetanotifyRestApi::getAuthToken("meta-notify");

        $resp = self::epns_request_api('/v1/notifications/', 'GET', [], [
            "siteID: $siteID",
        ], $transient);

        if ($resp['status'] === 200) {
            return json_decode($resp['body']);
        }
        return false;

    }
    static function addSite($siteEmail, $plugin)
    {
        $transient = MetanotifyRestApi::getAuthToken($plugin);

        $resp = self::epns_request_api(
            '/v1/site',
            'POST',
            ['email' => $siteEmail],
            [],
            $transient
        );


        $response_json = json_encode($resp);
        $response = json_decode($response_json, true);


        add_option('metanotify_site_id', $response['id']);

        $categories = get_terms(
            array(
                'taxonomy' => 'category',
                'hide_empty' => false,
            )
        );
        $arr = [];
        foreach ($categories as $category) {
            // Check if category name is "Uncategorized"
            if ($category->name === 'Uncategorized') {
                continue; // Skip this category
            }
            array_push($arr, $category->name);
            $metanotify_site_id = get_option('metanotify_site_id', "");
            foreach ($arr as $categoryName) {
                $response = MetanotifyCategoryApi::addCategory($metanotify_site_id, $categoryName);
                error_log(print_r($categoryName, true));
                error_log(print_r($response, true));
            }

        }
        ;

        if ($resp['status'] === 200) {
            return json_decode($resp['body']);
        }
        return false;

    }
    static function getVisitors($siteID)
    {


        $transient = MetanotifyRestApi::getAuthToken("meta-notify");

        $resp = self::epns_request_api(
            '/v1/visitors/',
            'GET',
            [],
            [
                "siteID: $siteID",
            ],
            $transient
        );

        if ($resp['status'] === 200) {
            return json_decode($resp['body']);
        }
        return false;

    }
    static function addCategory($siteID, $categoryName)
    {
        $transient = MetanotifyRestApi::getAuthToken("meta-notify");

        $resp = self::epns_request_api(
            '/v1/categories/',
            'POST',
            ['name' => $categoryName],
            [
                "siteID: $siteID",

            ],
            $transient
        );



        if ($resp['status'] === 200) {
            wp_insert_term(
                $categoryName,
                'category',
                [
                    'slug' => meta_notify_slugify($categoryName),
                ]
            );
            return json_decode($resp['body']);
        }
        return false;

    }
    static function searchCategory($siteID, $categoryId)
    {
        $transient = MetanotifyRestApi::getAuthToken("meta-notify");

        $resp = self::epns_request_api(
            '/v1/categories/' . $categoryId,
            'GET',
            [],
            [
                "siteID: $siteID",

            ],
            $transient
        );

        if ($resp['status'] === 200) {
            return json_decode($resp['body']);
        }
        return false;

    }
    static function searchNotification($siteID, $notificationId)
    {

        $transient = MetanotifyRestApi::getAuthToken("meta-notify");
        $resp = self::epns_request_api(
            '/v1/notifications/' . $notificationId,
            'GET',
            [],
            [
                "siteID: $siteID",

            ],
            $transient
        );


        if ($resp['status'] === 200) {
            return json_decode($resp['body']);
        }
        return false;

    }
    static function deleteCategory($siteID, $categoryId)
    {
        $transient = MetanotifyRestApi::getAuthToken("meta-notify");
        $resp = self::epns_request_api(
            '/v1/categories/' . $categoryId,
            'DELETE',
            [],
            [
                "siteID: $siteID",
            ],
            $transient
        );
        if ($resp['status'] === 204) {
            exit(json_encode(["success" => true]));
        }

        exit(json_encode(["success" => false]));

    }
    static function addNotification($siteID, $notificationTitle, $notificationBody, $notificationImage, $notificationCategory, $notificationVisitors)
    {

        global $wpdb;

        $transient = MetanotifyRestApi::getAuthToken("meta-notify");
        $resp = self::epns_request_api(
            '/v1/notifications/',
            'POST',
            [
                'title' => $notificationTitle,
                'body' => $notificationBody,
                'categories' => $notificationCategory,
                'imageURL' => $notificationImage,
                'visitorIDs' => $notificationVisitors
            ],
            [

                "siteID: $siteID",

            ],
            $transient
        );

        error_log(print_r($resp, true));
        if ($resp['status'] === 200) {

            $body = json_decode($resp['body']);
            $notification_id = $body->notificationID;
            $notification_status = $body->notificationStatus;
            $inserted = $wpdb->insert(
                'metanotify_notifications',
                array(
                    'notification_id' => $notification_id,
                    'notification_title' => $notificationTitle,
                    'notification_body' => $notificationBody,
                    'notification_image' => $notificationImage,
                    'notification_status' => $notification_status
                )
            );
            return json_decode($resp['body']);


        }
        return false;

    }
    static function addVisitor($siteID, $walletAddress)
    {
        $transient = MetanotifyRestApi::getAuthToken("meta-notify");


        $resp = self::epns_request_api(
            '/v1/visitors/',
            'POST',
            ['walletAddress' => $walletAddress],
            [
                "siteID: $siteID",

            ],
            $transient
        );

        if ($resp['status'] === 200) {
            return json_decode($resp['body']);
        }
        return false;

    }
    static function updateVisitor($siteID, $visitorId, $categoryId)
    {
        $transient = MetanotifyRestApi::getAuthToken("meta-notify");


        $categoryId = explode(',', $categoryId);


        $resp = self::epns_request_api(
            '/v1/visitors/' . $visitorId,
            'PATCH',
            ['categoryIDs' => $categoryId, 'siteStatus' => 'Enabled'],
            [
                "siteID: $siteID",

            ],
            $transient
        );

        if ($resp['status'] === 200) {
            return json_decode($resp['body']);
        }
        return false;

    }


    static function epns_request_api($endpoint, $method, array $params, $passedHeaders = [], $auth = false, $timeout = 60)
    {
        $url = self::BASE_URL . $endpoint;

        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'MetaPlugins',
        ];

        $headers = array_merge($headers, $passedHeaders);

        if ($auth) {
            $headers['Authorization'] = 'Bearer ' . $auth;
        }
        $args = array(
            'timeout' => $timeout,
            'headers' => $headers,
            'method' => $method,
        );

        if (!empty($params)) {
            $args['headers']['Content-Type'] = 'application/json';
            $args['body'] = json_encode($params);
        }

        $response = wp_remote_request($url, $args);

        $code = wp_remote_retrieve_response_code($response);

        $body = wp_remote_retrieve_body($response);

        return ['status' => $code, 'body' => $body];
    }


}

final class MetanotifyRestApi
{
    const BASE_URL = 'https://metaplugins-staging-backend.service.metaplugins.io';
    const NAMESPACE = 'meta-notify/v1';

    /**
     * Register REST routes
     *
     * @see https://developer.wordpress.org/reference/functions/register_rest_route/
     */
    static function registerRoutes()
    {

        register_rest_route(self::NAMESPACE , 'key', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => __CLASS__ . '::getPubKey',
                'permission_callback' => '__return_true'
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => __CLASS__ . '::editKey',
                'permission_callback' => '__return_true'
            ]
        ]);

        register_rest_route(self::NAMESPACE , 'license', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => __CLASS__ . '::getLicense',
                'permission_callback' => '__return_true'
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => __CLASS__ . '::editLicense',
                'permission_callback' => '__return_true'
            ]
        ]);

        register_rest_route(self::NAMESPACE , 'data', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => __CLASS__ . '::fetchData',
                'permission_callback' => '__return_true'
            ]
        ]);
    }


    /**
     * Retrieve default Infura Id from database
     */
    public static function getInfuraId()
    {
        $site_url = get_site_url();
        $localHeaders = ["Origin: $site_url"];
        $resp = self::request_api('/v1/key/blockchain', 'GET', [], $localHeaders, self::getAuthToken($plugin), 60);

        if ($resp['status'] !== 200) {
            return false;
        } else {
            $body = json_decode($resp['body']);
            return $body->key;
        }
    }

    /**
     * Get registration status
     *
     * @return bool
     */
    static function getActivationStatus($plugin)
    {


        $resp = self::request('v1/plugin/status', 'GET', [], self::getAuthToken($plugin));

        if ($resp['status'] === 400) {
            return false;
        } else {
            $body = json_decode($resp['body']);
            return isset($body->status) ? $body->status : false;
        }

    }


    /**
     * Do registration
     *
     * @param string $plugin The slug of the plugin.
     * @param string $email The user email.
     * @return bool
     */
    static function registerSite($plugin, $email)
    {

        $trans = self::getAuthToken($plugin);
        $resp = self::request('/v1/auth/register', 'POST', ['email' => $email], $trans);


        if ($resp['status'] === 200) {
            $body = json_decode($resp['body']);
            return $body->status;
        } else {
            return false;
        }

    }

    /**
     * Fetch collected data
     *
     * @param ArrayObject $request
     * @return object
     */
    static function fetchData($request)
    {

        $key = self::getAuthKey($request);

        if (empty($key) || !self::authorizeKey($key)) {
            return new WP_Error('bad_request', 'Unauthorized request!', ['status' => 401]);
        }

        global $wpdb;

        $start_date = empty($request['start_date']) ? false : sanitize_text_field($request['start_date']);
        $end_date = empty($request['end_date']) ? false : sanitize_text_field($request['end_date']);

        if ($start_date && $end_date) {
            $query = sprintf("SELECT * FROM metanotify_sessions WHERE visited_time >= '%s' AND visited_time <= '%s' ORDER BY id DESC;", $start_date, $end_date);
        } elseif ($start_date && !$end_date) {
            $query = sprintf("SELECT * FROM metanotify_sessions WHERE visited_time >= '%s' AND visited_time <= '%s' ORDER BY id DESC;", $start_date, $last_visit);
        } elseif (!$start_date && $end_date) {
            $query = sprintf("SELECT * FROM metanotify_sessions WHERE visited_time >= '%s' AND visited_time <= '%s' ORDER BY id DESC;", $first_visit, $end_date);
        } else {
            $query = "SELECT * FROM metanotify_sessions ORDER BY id DESC;";
        }

        $results = $wpdb->get_results($query, ARRAY_A);

        return rest_ensure_response($results);
    }

    /**
     * Get the public key
     */
    static function getPubKey($request)
    {

        $public_key = get_option('meta_public_key');

        if (!$public_key) {
            return new WP_Error('not_found', __('Public key not found!', 'meta-notify'), ['status' => 404]);
        }


        return rest_ensure_response(['publicKey' => trim(preg_replace('/\s+/', ' ', $public_key))]);
    }

    /**
     * Setup keypair
     */
    static function setupKeypair($force = false)
    {

        if (get_option('meta_public_key') && get_option('meta_private_key') && !$force) {
            return;
        }

        if (!function_exists('openssl_pkey_new')) {
            throw new Exception(__('OpenSSL extension is not installed!', 'meta-notify'));
        } else {
            $rsa_key = openssl_pkey_new([
                'digest_alg' => 'sha256',
                'private_key_bits' => 4096,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);

            if (!$rsa_key) {
                throw new Exception(sprintf(__('Unable to setup the private key. %s. Please try activating the plugin again!', 'meta-notify'), openssl_error_string()));
            }

            $public_key = openssl_pkey_get_details($rsa_key)['key'];

            openssl_pkey_export($rsa_key, $private_key);

            if (!update_option('meta_public_key', trim($public_key)) || !update_option('meta_private_key', trim($private_key))) {
                throw new Exception(__('Failed to update credentials!', 'meta-notify'));
            }
        }
    }

    /**
     * Authorize the Bearer JWT
     */
    static function authorizeKey($key)
    {

        $site_url = get_site_url();
        $public_key = self::getServerPubKey();

        if (!$public_key) {
            return false;
        }

        try {
            $jwt_token = JWT::decode($key, new Key($public_key, 'RS256'));
        } catch (Throwable $e) {
            $jwt_token = false;
        }

        if ($jwt_token->website !== $site_url) {
            return false;
        }

        return $jwt_token;
    }

    /**                                                                                                 
     * Retrieve the JWT from the authorization header.
     *
     * @param array $request
     * @return string
     */
    static function getAuthKey($request)
    {

        preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches);

        return empty($matches[1]) ? '' : $matches[1];
    }

    /**
     * Retrieve public key of the central server
     *
     * @return bool|string
     */
    static function getServerPubKey()
    {

        $resp = self::request('/v1/key/public', 'GET', []);

        if ($resp['status'] !== 200) {
            return false;
        } else {
            $body = json_decode($resp['body']);
            return trim($body->publicKey);
        }
    }

    /**
     * Get generated private key
     *
     * @return string
     */
    static function getPrivateKey()
    {

        return wp_unslash(get_option('meta_private_key'));
    }

    /**
     * Do a CURL request
     *
     * @param string $endpoint Endpoint path. Relative to the BASE_URL.
     * @param string $method
     * @param array $params
     * @return array
     */

    //  baseUrl 
    static function request($endpoint, $method, array $params, $auth = false, $timeout = 60)
    {
       $url = self::BASE_URL . $endpoint;
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'MetaPlugins',
        ];
        if ($auth) {
            $headers['Authorization'] = 'Bearer ' . $auth;
        }
        $args = array(
            'timeout' => $timeout,
            'headers' => $headers,
            'method' => $method,
        );

        if (!empty($params)) {
            $args['headers']['Content-Type'] = 'application/json';
            $args['body'] = json_encode($params);
        }

        $response = wp_remote_request($url, $args);            
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        return ['status' => $code, 'body' => $body];
    }

    static function request_api($endpoint, $method, array $params, $passedHeaders = [], $auth = false, $timeout = 60)
    {                                      

        $url = self::BASE_URL . $endpoint;

        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'MetaPlugins',
        ];

        $headers = array_merge($headers, $passedHeaders);

        if ($auth) {
            $headers['Authorization'] = 'Bearer ' . $auth;
        }
        $args = array(
            'timeout' => $timeout,
            'headers' => $headers,
            'method' => $method,
        );

        if (!empty($params)) {
            $args['headers']['Content-Type'] = 'application/json';
            $args['body'] = json_encode($params);
        }

        $response = wp_remote_request($url, $args);

        $code = wp_remote_retrieve_response_code($response);

        $body = wp_remote_retrieve_body($response);

        return ['status' => $code, 'body' => $body];

    }

    /**
     * Create a Bearer token for authorization
     *
     * @param string $plugin Slug of the plugin.
     * @return string
     */
    static function getAuthToken($plugin)
    {

        $transient = get_transient('meta_notify_token'); //Get token transient if exist


        if ($transient == false) {
            $time = new DateTimeImmutable();
            $claims = [
                'iat' => $time->modify('-5 minutes')->getTimestamp(),
                'exp' => $time->modify('+2 hour')->getTimestamp(),
                'slug' => '/wp-json/' . $plugin,
                'website' => get_site_url(),
                'name' => $plugin,
                'ver' => META_NOTIFY_VER,
            ];
            $token = JWT::encode($claims, self::getPrivateKey(), 'RS256');
            set_transient('meta_notify_token', $token, 60 * MINUTE_IN_SECONDS);
            //Set 60 minute token transient

            return $token;

        } else {

            return $transient; //Return transient of token
        }

    }
}