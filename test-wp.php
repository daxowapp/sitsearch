<?php
require_once('wp-load.php');
$opts = get_option('sit_recommender_openrouter');
var_dump($opts);
$response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', array(
    'headers' => array(
        'Authorization' => 'Bearer ' . ($opts['api_key'] ?? ''),
        'Content-Type' => 'application/json',
        'HTTP-Referer' => home_url(),
        'X-Title' => 'SIT Recommender'
    ),
    'body' => json_encode(array(
        'model' => $opts['model'] ?? 'openai/gpt-4o-mini',
        'messages' => array(
            array('role' => 'user', 'content' => 'Test')
        ),
        'max_tokens' => 5
    )),
    'timeout' => 30
));
if (is_wp_error($response)) {
    echo "WP Error: " . $response->get_error_message() . "\n";
} else {
    echo "Status code: " . wp_remote_retrieve_response_code($response) . "\n";
    echo "Body: " . wp_remote_retrieve_body($response) . "\n";
}
