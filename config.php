<?php
// ---- Unified session bootstrap (include this file FIRST on every page) ----
if (session_status() === PHP_SESSION_NONE) {
  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

  // One consistent session name for your whole site
  session_name('premi_session');

  session_set_cookie_params([
    'lifetime' => 60*60*24*7, // 7 days
    'path'     => '/',
    // If you use multiple subdomains, uncomment and set your base domain:
    // 'domain'   => '.lbdigitalworks.com',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);

  session_start();
}

// Admin credentials
define('ADMIN_EMAIL', 'Admin@your-restaurant.com');
define('ADMIN_PASS_HASH', '$2y$10$r0V2gZor6T6lXudlX30f.uhhL71lMx769kscOddt9I/JgDfHdQ08G'); // e.g. $2y$10$...

// Timezone
date_default_timezone_set('Europe/London');

// Where your shop lives (keep trailing slash)
if (!defined('BASE_URL')) {
  define('BASE_URL', 'https://lbdigitalworks.com/websites/shop/');
}

// Admin folder name (change if yours is different)
if (!defined('ADMIN_PATH')) {
  define('ADMIN_PATH', 'Admin_CP/'); // e.g. 'admin/' if that's your folder
}

// Restaurant/Site name
if (!defined('RESTAURANT_NAME')) {
  define('RESTAURANT_NAME', 'Your Restaurant');
}
if (!defined('SITE_NAME')) {
  // Some pages fall back to SITE_NAME; mirror RESTAURANT_NAME to keep things consistent
  define('SITE_NAME', RESTAURANT_NAME);
}

/* ------------------------------
   Payment Gateway Configuration
   ------------------------------ */

// Stripe
if (!defined('STRIPE_SECRET_KEY')) {
  define('STRIPE_SECRET_KEY', 'sk_test_51S0zOmDjeSJwQhojiQlpiDyU5DAGEiQdSsPScauXS35i3Z8R9z9ICb15rnbZ5EEWD5OLoxDRtT1bTjtiKf4qeziY00l07uhrEF');   // 🔑 Your Stripe Secret Key
}
if (!defined('STRIPE_PUBLISHABLE_KEY')) {
  define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51S0zOmDjeSJwQhojsoxJOsppUXFuyOtUGMF2xgMPTQVT9UpdVd07opn8Od4uNSCcF8YyfwetF4pBstFKLVdwmmhO00swEFVxYZ'); // 🔑 Your Stripe Publishable Key
}
if (!defined('STRIPE_SUCCESS_URL')) {
  // On success go to tracking (Stripe will redirect here)
  define('STRIPE_SUCCESS_URL', BASE_URL . 'order-tracking.php?provider=stripe');
}
if (!defined('STRIPE_CANCEL_URL')) {
  define('STRIPE_CANCEL_URL', BASE_URL . 'checkout.php?canceled=1');
}
