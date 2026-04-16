<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');

$api_key = get_option('sit_openai_api_key');
echo "API Key Length: " . strlen($api_key) . "\n";

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Force IPv4 because MAMP often hangs indefinitely trying to resolve IPv6 for APIs
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
// Ignore SSL errors on local
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 3); // MAX 3 seconds!

$body = [
    'model' => 'gpt-4o-mini',
    'messages' => [['role' => 'user', 'content' => 'Say test']],
    'max_tokens' => 10
];

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

echo "Executing cURL...\n";
$start = microtime(true);
$response = curl_exec($ch);
$err = curl_error($ch);
echo "Time taken: " . round(microtime(true) - $start, 2) . "s\n";

if ($err) {
    echo "cURL Error: " . $err . "\n";
} else {
    echo "Response: " . substr($response, 0, 200) . "...\n";
}
curl_close($ch);
