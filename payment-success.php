<?php
// payment-success.php — Relay page after Stripe/PayPal/Cash checkout
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('Europe/London');

// Optional: detect provider for analytics/logging
$provider = isset($_GET['provider']) ? htmlspecialchars($_GET['provider']) : 'unknown';

// You could also verify Stripe session or PayPal order here if you want 
// server-side confirmation before redirecting the customer.
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<title>Payment Success • Premi Spice</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
html,body{
  margin:0; padding:0; font-family:'Segoe UI',Tahoma,Verdana,sans-serif;
  display:flex; align-items:center; justify-content:center;
  height:100vh; background:#f7f8fa; color:#111; text-align:center;
}
[data-theme="dark"] body{ background:#0e1116; color:#fff; }
.msg{ max-width:400px; padding:20px; }
.msg i{ font-size:48px; color:#16a34a; margin-bottom:12px; }
</style>
</head>
<body>
<div class="msg">
  <i class="fas fa-check-circle"></i>
  <h1>Payment successful</h1>
  <p>Thank you! Your order is being processed.</p>
  <p>You’ll be redirected to order tracking shortly…</p>
</div>

<script>
// Stamp the order time for ETA (used in order-tracking.php)
localStorage.setItem('order_time_iso', new Date().toISOString());

// Redirect to tracking after 2s
setTimeout(function(){
  window.location.href = "order-tracking.php";
}, 2000);
</script>
</body>
</html>
