<?php
require_once('wp-load.php');

$opts = get_option('sit_recommender_openrouter', []);
if (empty($opts['api_key'])) {
    echo "NO API KEY FOUND IN DB! Please save it in WP Admin.\n";
    exit;
}

echo "Testing OpenRouter API...\n";
$response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', array(
    'headers' => array(
        'Authorization' => 'Bearer ' . $opts['api_key'],
        'Content-Type' => 'application/json',
        'HTTP-Referer' => home_url(),
        'X-Title' => 'SIT Recommender'
    ),
    'body' => json_encode(array(
        'model' => $opts['model'] ?? 'openai/gpt-4o-mini',
        'messages' => array(
            array('role' => 'user', 'content' => 'Say hello in valid JSON format {"hello": "world"}')
        ),
        'response_format' => array('type' => 'json_object'),
        'max_tokens' => 50
    )),
    'timeout' => 15
));

if (is_wp_error($response)) {
    echo "HTTP/cURL ERROR: " . $response->get_error_message() . "\n";
} else {
    echo "STATUS: " . wp_remote_retrieve_response_code($response) . "\n";
    echo "BODY:\n" . wp_remote_retrieve_body($response) . "\n";
}
