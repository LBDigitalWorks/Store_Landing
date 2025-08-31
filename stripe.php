<?php
// stripe.php — JSON endpoint for checkout, cash orders, credit top-ups, and paying with account credit
// Actions:
//   - create_checkout:  create a Stripe Checkout Session for a basket and return {url}
//   - record_cash:      save pending order and return {redirect:"order-tracking.php?provider=cash"}
//   - create_topup:     create a Stripe Checkout Session to add account credit and return {url}
//   - pay_with_credit:  deduct account credit for the basket and return {redirect:"order-tracking.php?provider=credit"}
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); } // ensure session for pending_order

// --- URLs for basket checkout (used by create_checkout) ---
$successUrl = (defined('STRIPE_SUCCESS_URL') && STRIPE_SUCCESS_URL)
  ? STRIPE_SUCCESS_URL
  : (BASE_URL . 'order-tracking.php?provider=stripe&session_id={CHECKOUT_SESSION_ID}');
$cancelUrl  = (defined('STRIPE_CANCEL_URL') && STRIPE_CANCEL_URL)
  ? STRIPE_CANCEL_URL
  : (BASE_URL . 'checkout.php?canceled=1');

// --- Input parsing ---
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (!$in && !empty($_POST)) $in = $_POST;

$action  = $in['action']  ?? null;
$payload = $in['payload'] ?? null;

if (!$action) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid request (missing action)']); exit;
}

// some actions require a payload
$requiresPayload = in_array($action, ['create_checkout', 'record_cash', 'pay_with_credit'], true);
if ($requiresPayload && !is_array($payload)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid request payload']); exit;
}

// Helper: save a pending order into session
function save_pending_order(array $payload): void {
  $_SESSION['pending_order'] = [
    'items' => array_map(function($it){
      return [
        'name' => (string)($it['name'] ?? ''),
        'price'=> (float)($it['price'] ?? 0),
        'qty'  => max(1, (int)($it['qty'] ?? 1)),
      ];
    }, $payload['cart'] ?? []),
    'totals'    => $payload['totals'] ?? [],
    'fees'      => $payload['fees'] ?? [],
    'customer'  => $payload['customer'] ?? [],
    'placed_at' => date('c'),
  ];
}

/* ------------------------------------------------------------------
   Account credit helpers (shared by account.php / checkout / stripe)
-------------------------------------------------------------------*/
function data_dir(){ return __DIR__ . '/data'; }
function credits_file(){ return data_dir() . '/credits.json'; }
function ensure_data_dir(){ $d = data_dir(); if (!is_dir($d)) @mkdir($d, 0775, true); }
function load_credits(){
  ensure_data_dir();
  $f = credits_file();
  if (!file_exists($f)) return [];
  $j = @file_get_contents($f);
  $a = json_decode($j, true);
  return is_array($a) ? $a : [];
}
function save_credits(array $a){
  ensure_data_dir();
  $f = credits_file(); $tmp = $f . '.tmp';
  @file_put_contents($tmp, json_encode($a, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
  @rename($tmp, $f);
}
function user_key_for_credit(){
  if (!empty($_SESSION['user']['email'])) return 'user:' . strtolower($_SESSION['user']['email']);
  if (!empty($_SESSION['user']['id']))    return 'user:' . $_SESSION['user']['id'];
  if (!empty($_SESSION['user_id']))       return 'user:' . $_SESSION['user_id'];
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  return 'ip:' . sha1($ip);
}

/* ================= pay_with_credit (no Stripe) ================= */
if ($action === 'pay_with_credit') {
  if (empty($payload['cart']) || !is_array($payload['cart'])) {
    http_response_code(400); echo json_encode(['error' => 'Empty cart']); exit;
  }
  $total  = isset($payload['totals']['total']) ? floatval($payload['totals']['total']) : 0;
  $amount = (int) round($total * 100);
  if ($amount <= 0) {
    http_response_code(400); echo json_encode(['error' => 'Invalid total']); exit;
  }

  $all = load_credits(); $key = user_key_for_credit();
  $have = (int)($all[$key] ?? 0);
  if ($have < $amount) {
    http_response_code(400); echo json_encode(['error' => 'Not enough account credit']); exit;
  }

  // Deduct and persist
  $all[$key] = $have - $amount;
  save_credits($all);

  // Record pending order so tracking can promote it
  save_pending_order($payload);

  echo json_encode([
    'ok' => true,
    'redirect' => BASE_URL . 'order-tracking.php?provider=credit'
  ]);
  exit;
}

/* ===================== record_cash (no Stripe) ===================== */
if ($action === 'record_cash') {
  if (empty($payload['cart']) || !is_array($payload['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty cart']); exit;
  }
  save_pending_order($payload);
  echo json_encode([
    'ok' => true,
    'redirect' => BASE_URL . 'order-tracking.php?provider=cash'
  ]);
  exit;
}

/* ================= create_checkout (basket → Stripe) ================= */
if ($action === 'create_checkout') {
  if (!defined('STRIPE_SECRET_KEY') || !STRIPE_SECRET_KEY) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe secret key not configured.']); exit;
  }

  // Amount (GBP -> pence)
  $total  = isset($payload['totals']['total']) ? floatval($payload['totals']['total']) : 0;
  $amount = (int) round($total * 100);
  if ($amount < 50) { // Stripe minimum ~£0.50
    http_response_code(400);
    echo json_encode(['error' => 'Minimum charge is £0.50']); exit;
  }

  // Save pending order so tracking can promote on success
  save_pending_order($payload);

  // Metadata (flatten address if structured)
  $addrMeta = $payload['customer']['address'] ?? '';
  if (is_array($addrMeta)) {
    $addrMeta = implode(', ', array_filter([
      $addrMeta['line1']   ?? '',
      $addrMeta['line2']   ?? '',
      $addrMeta['city']    ?? '',
      $addrMeta['postcode']?? ''
    ]));
  }
  $meta = [
    'customer_name'  => (string)($payload['customer']['name'] ?? ''),
    'customer_phone' => (string)($payload['customer']['phone'] ?? ''),
    'delivery_mode'  => (string)($payload['customer']['mode'] ?? ''),
    'address'        => (string)$addrMeta,
  ];

  try {
    $productName = (defined('RESTAURANT_NAME') ? RESTAURANT_NAME : 'Restaurant') . ' order';

    // Build form-encoded fields for Stripe API
    $fields = [
      'mode' => 'payment',
      'success_url' => $successUrl,
      'cancel_url'  => $cancelUrl,

      // Apple Pay works automatically on Stripe Checkout for verified domains
      'payment_method_types[0]' => 'card',

      // Single line item for the whole order
      'line_items[0][price_data][currency]' => 'gbp',
      'line_items[0][price_data][product_data][name]' => $productName,
      'line_items[0][price_data][unit_amount]' => $amount, // pence
      'line_items[0][quantity]' => 1,
    ];

    // If we know the user's email, pass it
    if (!empty($_SESSION['user']['email'])) {
      $fields['customer_email'] = $_SESSION['user']['email'];
    }

    // Append metadata
    foreach ($meta as $k => $v) {
      if ($v !== '') $fields["metadata[$k]"] = $v;
    }

    // Fire request
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => http_build_query($fields),
      CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded'
      ],
      CURLOPT_TIMEOUT        => 20,
    ]);

    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
      http_response_code(502);
      echo json_encode(['error' => 'Stripe connection failed: ' . $err]); exit;
    }

    $json = json_decode($resp, true);
    if ($http >= 400) {
      $msg = $json['error']['message'] ?? ('Stripe error (HTTP ' . $http . ')');
      http_response_code($http);
      echo json_encode(['error' => $msg]); exit;
    }

    if (empty($json['url'])) {
      http_response_code(500);
      echo json_encode(['error' => 'Stripe did not return a checkout URL']); exit;
    }

    echo json_encode(['url' => $json['url']]); exit;

  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage() ]); exit;
  }
}

/* =============== create_topup (account credit → Stripe) =============== */
if ($action === 'create_topup') {
  if (!defined('STRIPE_SECRET_KEY') || !STRIPE_SECRET_KEY) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe secret key not configured.']); exit;
  }

  // amount can come flat or inside payload
  $amount = (int) ($in['amount'] ?? ($payload['amount'] ?? 0)); // pence
  // enforce sane minimum (e.g., £1)
  if ($amount < 100) {
    http_response_code(400);
    echo json_encode(['error' => 'Minimum top-up is £1.00']); exit;
  }

  // where to send the user back to (defaults to account.php)
  $returnTo = $in['return_to'] ?? ($payload['return_to'] ?? 'account.php');
  $base = preg_match('/^https?:/i', $returnTo) ? $returnTo : (BASE_URL . ltrim($returnTo, '/'));
  $sep  = (strpos($base, '?') !== false) ? '&' : '?';
  $success = $base . $sep . 'topup=success&amt=' . $amount . '&session_id={CHECKOUT_SESSION_ID}';
  $cancel  = $base . $sep . 'topup=failed';

  try {
    // Build form-encoded fields for Stripe API
    $fields = [
      'mode' => 'payment',
      'success_url' => $success,
      'cancel_url'  => $cancel,
      'payment_method_types[0]' => 'card',

      'line_items[0][price_data][currency]' => 'gbp',
      'line_items[0][price_data][product_data][name]' => 'Account Credit Top-Up',
      'line_items[0][price_data][unit_amount]' => $amount,
      'line_items[0][quantity]' => 1,
    ];

    // If we know the user's email, pass it to Stripe Checkout
    if (!empty($_SESSION['user']['email'])) {
      $fields['customer_email'] = $_SESSION['user']['email'];
      $fields['metadata[user_email]'] = $_SESSION['user']['email'];
    }
    if (!empty($_SESSION['user']['id'])) {
      $fields['metadata[user_id]'] = $_SESSION['user']['id'];
    }

    // Fire request
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => http_build_query($fields),
      CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded'
      ],
      CURLOPT_TIMEOUT        => 20,
    ]);

    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
      http_response_code(502);
      echo json_encode(['error' => 'Stripe connection failed: ' . $err]); exit;
    }

    $json = json_decode($resp, true);
    if ($http >= 400) {
      $msg = $json['error']['message'] ?? ('Stripe error (HTTP ' . $http . ')');
      http_response_code($http);
      echo json_encode(['error' => $msg]); exit;
    }

    if (empty($json['url'])) {
      http_response_code(500);
      echo json_encode(['error' => 'Stripe did not return a checkout URL']); exit;
    }

    echo json_encode(['url' => $json['url']]); exit;

  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage() ]); exit;
  }
}

// Unknown action
http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
exit;
