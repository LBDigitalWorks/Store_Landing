<?php
// refunds.php — Refunds & Cancellations Policy (UK takeaway; no refunds as standard)
require_once __DIR__ . '/config.php';
date_default_timezone_set('Europe/London');

$bizName = defined('RESTAURANT_NAME') ? RESTAURANT_NAME : 'Your Restaurant';
$lastUpdated = date('F j, Y');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php if (file_exists(__DIR__ . '/partials/theme-head.php')) include __DIR__ . '/partials/theme-head.php'; ?>
<title>Refunds & Cancellations • <?= htmlspecialchars($bizName) ?></title>
<style>
  :root{
    --bg:#0b0f14; --text:#eaeaea; --muted:#9aa1ab; --line:#27272a; --card:#12171e; --primary:#f04f32;
  }
  html,body{ background:var(--bg); color:var(--text); margin:0; -webkit-text-size-adjust:100%; }
  .page{ max-width:900px; margin:0 auto; padding:24px 16px 32px; }
  .h1{ font-size:28px; font-weight:900; margin:6px 0 4px; }
  .sub{ color:var(--muted); font-size:14px; margin-bottom:18px; }
  .card{ background:var(--card); border:1px solid var(--line); border-radius:14px; padding:16px; margin:12px 0; }
  h2{ font-size:18px; margin:0 0 8px; }
  h3{ font-size:16px; margin:16px 0 6px; }
  p{ line-height:1.6; margin:8px 0; color:var(--text); }
  ul{ margin:8px 0 8px 18px; }
  li{ margin:6px 0; color:var(--text); }
  a{ color:inherit; text-decoration:underline; text-underline-offset:2px; }
  .muted{ color:var(--muted); }
  .badge{ display:inline-block; background:#1c252f; border:1px solid var(--line); padding:3px 8px; border-radius:999px; font-size:12px; color:#cbd5e1; }
</style>
</head>
<body>

<div class="page">
  <div class="h1">Refunds &amp; Cancellations</div>
  <div class="sub">Last updated: <?= htmlspecialchars($lastUpdated) ?> • Applies to orders placed directly with <?= htmlspecialchars($bizName) ?></div>

  <div class="card">
    <p><span class="badge">Summary</span></p>
    <p><strong>All sales are final once an order is accepted by the kitchen.</strong> Because food is freshly prepared, we do not offer refunds to the original payment method as a standard policy.</p>
    <p>We will always meet your legal rights. If something goes wrong (e.g., not delivered, the wrong items, or not as described), we will put it right—usually by a <strong>replacement</strong> or <strong>store credit (account credit)</strong>.</p>
  </div>

  <div class="card">
    <h2>1) Cancellations</h2>
    <ul>
      <li><strong>Before acceptance:</strong> If you contact us immediately after placing an order and it has not yet been accepted by the kitchen, we can cancel it. No food will be prepared.</li>
      <li><strong>After acceptance:</strong> Once preparation has started, the order cannot be cancelled and is non-refundable.</li>
    </ul>
  </div>

  <div class="card">
    <h2>2) Problems with your order</h2>
    <p>Please report any issue <strong>as soon as possible</strong> (ideally within 30 minutes of collection or delivery) with your order number and photos if helpful.</p>
    <ul>
      <li><strong>Missing or incorrect items:</strong> We’ll replace the item(s) or issue store credit of equivalent value.</li>
      <li><strong>Quality concerns (e.g., not as described):</strong> We may ask for photos and will arrange a replacement or store credit where appropriate.</li>
      <li><strong>Not delivered:</strong> If we confirm non-delivery, we’ll re-deliver or issue store credit.</li>
    </ul>
    <p>Resolutions are normally <strong>replacement</strong> or <strong>store credit</strong>. Refunds to the original payment method are not provided except where required by law.</p>
  </div>

  <div class="card">
    <h2>3) Allergies &amp; special requirements</h2>
    <p>If you have an allergy or dietary requirement, please contact us before ordering. While we take care, our kitchen handles common allergens; cross-contamination risk cannot be fully eliminated.</p>
  </div>

  <div class="card">
    <h2>4) Account credit</h2>
    <p>Where a remedy is due, we may add a <strong>store credit</strong> to your account which can be used on future orders at checkout. Store credit is not transferable or redeemable for cash.</p>
  </div>

  <div class="card">
    <h2>5) Chargebacks</h2>
    <p>If you initiate a chargeback, we will provide the card processor with order logs and delivery/collection evidence. This does not affect your statutory rights.</p>
  </div>

  <div class="card">
    <h2>6) Your legal rights</h2>
    <p>Nothing in this policy affects your rights under consumer law (e.g., where goods are not as described or services aren’t provided with reasonable care and skill). Where a legal right to a refund exists, we will comply.</p>
  </div>

  <div class="card">
    <h2>Contact</h2>
    <ul>
      <li><strong>Business:</strong> <?= htmlspecialchars($bizName) ?></li>
      <li><strong>Email:</strong> <a href="mailto:hello@example.com">hello@example.com</a></li>
      <li><strong>Order number:</strong> please include it in your message.</li>
    </ul>
  </div>
</div>

<?php
// Optional UI elements
$togglePath = __DIR__ . '/partials/theme-toggle.php';
if (file_exists($togglePath)) include $togglePath;


// Optional: include your global footer/bottom nav if present
$footer = __DIR__ . '/footer.php';
if (file_exists($footer)) include $footer;
?>

