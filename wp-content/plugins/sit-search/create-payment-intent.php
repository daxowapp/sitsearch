<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('ABSPATH')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

header('Content-Type: application/json');

require __DIR__ . '/vendor/autoload.php';

if (!defined('STRIPE_SECRET_KEY') || empty(STRIPE_SECRET_KEY)) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe secret key not defined']);
    exit;
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$body = json_decode(file_get_contents('php://input'), true);

$amount = isset($body['amount']) ? intval($body['amount']) : 0;
$currency = isset($body['currency']) ? sanitize_text_field($body['currency']) : 'usd';

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

try {
    $intent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => $currency,
        'automatic_payment_methods' => ['enabled' => true],
    ]);

    echo json_encode([
        'clientSecret' => $intent->client_secret,
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
