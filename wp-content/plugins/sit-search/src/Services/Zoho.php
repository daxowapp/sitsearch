<?php

namespace SIT\Search\Services;

use CURLFile;
use Exception;

class Zoho
{
    private string $client_id = '';
    private string $client_secret = '';
    private string $refresh_token = '';
    private string $access_token = '';
    private string $base_url = 'https://www.zohoapis.com/crm/v2/';
    private SIT_Logger $logger;

    public function __construct()
    {
        /**
         * Logger Initialization
         * @var SIT_Logger $logger
         */
        $this->logger = SIT_Logger::get_instance();

        $this->client_id = Constants::get_zoho_client_id();
        $this->client_secret = Constants::get_zoho_client_secret();
        $this->refresh_token = Constants::get_zoho_refresh_token();
        $this->access_token = $this->get_access_token();
    }

    public function get_access_token(): ?string
    {
        try {
            if ($access_token = Functions::get_transient('zoho_access_token')) {

                $this->logger->log_message('info', 'Access token from transient: ' . $access_token);

                return $access_token;
            }

            $data = array(
                'refresh_token' => $this->refresh_token,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'refresh_token',
                'redirect_uri' => 'https://www.zohoapis.com'
            );

            $url = 'https://accounts.zoho.com/oauth/v2/token' . '?' . http_build_query($data);

            $request = wp_remote_post(
                $url,
                array(
                    'headers' => array(
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    )
                )
            );

            $response = json_decode(wp_remote_retrieve_body($request));

            $this->logger->log_message('info', 'Access token from Zoho: ' . $response->access_token);

            if ($response->access_token) {
                Functions::save_transient('zoho_access_token', $response->access_token, 3600);
                return $response->access_token;
            } else {
                $this->logger->log_message('error', 'Error getting access token from Zoho: ' . json_encode($response));
                return null;
            }
        } catch (Exception $e) {
            $this->logger->log_message('error', 'Error getting access token from Zoho: ' . $e->getMessage());
            return null;
        }
    }

    public function get_fields(string $module): ?array
    {
        $this->logger->log_message('info', 'Getting fields for module: ' . $module);

        $transient_key = 'zoho_fields_' . $module;
//        if ($cached_fields = Functions::get_transient($transient_key)) {
//            $this->logger->log_message('info', 'Fields [' . $module . '] from transient: Total - ' . count($cached_fields));
//            return $cached_fields;
//        }

        $response = $this->request('settings/fields?module=' . $module);

        if (isset($response['fields'])) {
            $fields = array_map(function ($field) {
                return array(
                    'id' => $field['id'],
                    'field_name' => $field['api_name'],
                    'field_label' => $field['field_label'],
                    'display_label' => $field['display_label'],
                    'data_type' => $field['data_type'],
                );
            }, $response['fields']);

            Functions::save_transient($transient_key, $fields, 3600 * 24 * 7);
            $this->logger->log_message('info', 'Fields fetched and cached: Total - ' . count($fields));
            return $fields;
        } else {
            $this->logger->log_message('error', 'Error getting fields for module: ' . $module);
            return null;
        }
    }


    public function request(string $url, string $method = 'GET', array $data = array()): ?array
    {
        $this->logger->log_message('info', 'Request URL: ' . $url);
        $this->logger->log_message('info', 'Request method: ' . $method);
        $this->logger->log_message('info', 'Request data: ' . json_encode($data));

        $headers = array(
            'Authorization' => 'Zoho-oauthtoken ' . $this->access_token
        );
        $data = !empty($data) ? json_encode($data) : '';
        $request = wp_remote_request(
            $this->base_url . $url,
            array(
                'method' => $method,
                'headers' => $headers,
                'body' => $data
            )
        );

        $response = json_decode(wp_remote_retrieve_body($request), true);

        $this->logger->log_message('info', 'Response: ' . json_encode($response));

        return $response;
    }


    public function send_request($url, $method = 'GET', $data = []) {
        $this->logger->log_message('info', "Request URL: {$url}");
        $this->logger->log_message('info', "Request method: {$method}");
        $this->logger->log_message('info', "Request data: " . json_encode($data));

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $this->access_token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json'
        ];

        $body = !empty($data) ? json_encode($data) : '';

        $args = [
            'method'  => strtoupper($method),
            'headers' => $headers,
            'body'    => $body,
        ];

        $request = wp_remote_request($this->base_url . $url, $args);

        if (is_wp_error($request)) {
            $error_message = $request->get_error_message();
            $this->logger->log_message('error', "Request failed: {$error_message}");
            return ['error' => $error_message];
        }

        $response_body = wp_remote_retrieve_body($request);
        $response = json_decode($response_body, true);

        $this->logger->log_message('info', "Response: " . json_encode($response));

        return $response;
    }

    public function request_image(string $url, string $image_name = '', string $method = 'GET', array $data = array()): ?string
    {
        $this->logger->log_message('info', 'Request URL: ' . $url);
        $this->logger->log_message('info', 'Request method: ' . $method);
        $this->logger->log_message('info', 'Request data: ' . json_encode($data));

        $headers = array(
            'Authorization' => 'Zoho-oauthtoken ' . $this->access_token
        );

        $request = wp_remote_request(
            $this->base_url . $url,
            array(
                'method' => $method,
                'headers' => $headers,
                'body' => $data
            )
        );

        if (is_wp_error($request)) {
            $this->logger->log_message('error', 'Request failed: ' . $request->get_error_message());
            return null;
        }

        $response_body = wp_remote_retrieve_body($request);
        $http_code = wp_remote_retrieve_response_code($request);
        $content_type = wp_remote_retrieve_header($request, 'content-type');

        $this->logger->log_message('info', "HTTP Code: $http_code | Content-Type: $content_type");

        if ($http_code !== 200 || strpos($content_type, 'image') === false) {
            $this->logger->log_message('error', 'Invalid response received.');
            return null;
        }

        $upload_dir = wp_upload_dir();
        $custom_dir = $upload_dir['basedir'] . '/uni-pic';
        $relative_path = $upload_dir['baseurl'] . '/uni-pic/';

        if (!file_exists($custom_dir)) {
            wp_mkdir_p($custom_dir);
        }

        $timestamp = time();
        $sanitized_name = sanitize_file_name($image_name);
        $filename = !empty($sanitized_name) ? $timestamp . '_' . $sanitized_name : 'zoho_image_' . $timestamp . '.jpg';
        $file_path = $custom_dir . '/' . $filename;
        $relative_file_path = $relative_path . $filename;

        if (file_put_contents($file_path, $response_body)) {
            $this->logger->log_message('info', '✅ Image saved successfully: ' . $relative_file_path);
            return $relative_file_path;
        } else {
            $this->logger->log_message('error', '❌ Failed to save the image.');
            return null;
        }
    }


    public function request_faculty(string $url, string $method = 'GET', array $data = array()): ?string
    {
        $this->logger->log_message('info', 'Request URL: ' . $url);
        $this->logger->log_message('info', 'Request method: ' . $method);
        $this->logger->log_message('info', 'Request data: ' . json_encode($data));

        $headers = array(
            'Authorization' => 'Zoho-oauthtoken ' . $this->access_token
        );

        $request = wp_remote_request(
            $this->base_url . $url,
            array(
                'method' => $method,
                'headers' => $headers,
                'body' => $data
            )
        );

        if (is_wp_error($request)) {
            $this->logger->log_message('error', 'Request failed: ' . $request->get_error_message());
            return null;
        }

        $response_body = wp_remote_retrieve_body($request);
        $http_code = wp_remote_retrieve_response_code($request);
        $content_type = wp_remote_retrieve_header($request, 'content-type');

        $this->logger->log_message('info', "HTTP Code: $http_code | Content-Type: $content_type");

        if ($http_code !== 200) {
            $this->logger->log_message('error', 'Invalid response received.');
            return null;
        }
        return $response_body;
    }


    public function request_att(string $url, string $method = 'POST', array $data = []): ?array
    {
        $this->logger->log_message('info', 'Request URL: ' . $url);
        $this->logger->log_message('info', 'Request method: ' . $method);

        if (!isset($data['file_path'], $data['file_name'], $data['file_type'])) {
            $this->logger->log_message('error', 'Missing file data');
            return ['status' => 'error', 'message' => 'Missing file data'];
        }

        $file_path = $data['file_path'];
        $file_name = $data['file_name'];
        $file_type = $data['file_type'];

        if (!file_exists($file_path)) {
            $this->logger->log_message('error', 'File not found: ' . $file_path);
            return ['status' => 'error', 'message' => 'File not found'];
        }

        $file = new CURLFile(realpath($file_path), $file_type, $file_name);

        $post_data = ['file' => $file];

        $api_url = $this->base_url . $url;

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Zoho-oauthtoken ' . $this->access_token
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL check if needed

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->logger->log_message('error', 'cURL Error: ' . $error);
            return ['status' => 'error', 'message' => $error];
        }

        $decoded_response = json_decode($response, true);
        $this->logger->log_message('info', 'Response: ' . json_encode($decoded_response));

        return $decoded_response;
    }
}