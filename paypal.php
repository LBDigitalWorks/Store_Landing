<?php
// paypal.php — create PayPal order via curl
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

header('Content-Type: application/json');

// 1. Get access token
$auth = base64_encode(PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
$paypal_base = PAYPAL_ENV === 'live'
  ? 'https://api-m.paypal.com'
  : 'https://api-m.sandbox.paypal.com';

$ch = curl_init("$paypal_base/v1/oauth2/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Basic $auth",
  "Content-Type: application/x-www-form-urlencoded"
]);
$tokenRes = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($tokenRes['access_token'])) {
  echo json_encode(['error' => 'PayPal auth failed']);
  exit;
}

$access_token = $tokenRes['access_token'];

// 2. Create order
$amount = isset($_POST['amount']) ? number_format($_POST['amount']/100, 2, '.', '') : '0.00';

$orderData = [
  'intent' => 'CAPTURE',
  'purchase_units' => [[
    'amount' => [
      'currency_code' => 'GBP',
      'value' => $amount
    ]
  ]],
  'application_context' => [
    'return_url' => PAYPAL_RETURN_URL,
    'cancel_url' => PAYPAL_CANCEL_URL
  ]
];

$ch = curl_init("$paypal_base/v2/checkout/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer $access_token"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));

$res = json_decode(curl_exec($ch), true);
curl_close($ch);

if (isset($res['links'])) {
  foreach ($res['links'] as $link) {
    if ($link['rel'] === 'approve') {
      header('Location: ' . $link['href']);
      exit;
    }
  }
}

echo json_encode($res);
