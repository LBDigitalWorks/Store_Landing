<?php
// cookies.php — Cookie & Tracking Policy (UK GDPR + PECR)
require_once __DIR__ . '/config.php';
date_default_timezone_set('Europe/London');

$bizName     = defined('RESTAURANT_NAME') ? RESTAURANT_NAME : 'Your Restaurant';
$contactMail = 'hello@example.com'; // update if needed
$lastUpdated = date('F j, Y');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php if (file_exists(__DIR__ . '/partials/theme-head.php')) include __DIR__ . '/partials/theme-head.php'; ?>
<title>Cookie Policy • <?= htmlspecialchars($bizName) ?></title>
<style>
  :root{ --bg:#0b0f14; --text:#eaeaea; --muted:#9aa1ab; --line:#27272a; --card:#12171e; --primary:#f04f32; }
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
  table{ width:100%; border-collapse:collapse; font-size:14px; }
  th,td{ border:1px solid var(--line); padding:10px; vertical-align:top; }
  th{ text-align:left; background:#0f151c; }
  .badge{ display:inline-block; background:#1c252f; border:1px solid var(--line); padding:3px 8px; border-radius:999px; font-size:12px; color:#cbd5e1; }
  .btn{ display:inline-flex; align-items:center; gap:8px; padding:10px 12px; border-radius:10px; border:1px solid var(--line); background:#161d25; color:#eaeaea; cursor:pointer; font-weight:800; }
  .btn:hover{ filter:brightness(1.05); }
</style>
</head>
<body>

<div class="page">
  <div class="h1">Cookie &amp; Tracking Policy</div>
  <div class="sub">Last updated: <?= htmlspecialchars($lastUpdated) ?> • Applies to orders placed directly with <?= htmlspecialchars($bizName) ?></div>

  <div class="card">
    <p><span class="badge">Summary</span></p>
    <p>We use strictly necessary cookies to run our site (e.g., your session and checkout), and limited third-party cookies when you use integrated services such as <strong>Stripe Checkout</strong> and <strong>Google Maps</strong>. We also store certain preferences in your browser’s <strong>localStorage</strong> (not cookies) to make checkout faster. We do not run advertising cookies.</p>
  </div>

  <div class="card">
    <h2>What are Cookies &amp; Similar Technologies?</h2>
    <p>Cookies are small text files placed on your device by a website. We also use similar technologies like <strong>localStorage</strong> (key–value storage in your browser) to remember your basket and address details. Cookies can be:</p>
    <ul>
      <li><strong>Essential:</strong> required to operate the website and provide the service you request.</li>
      <li><strong>Functional/Performance:</strong> optional tools that improve features/measure performance.</li>
      <li><strong>Advertising:</strong> used to track you across sites (we do <em>not</em> use these).</li>
    </ul>
  </div>

  <div class="card">
    <h2>Cookies We Use</h2>
    <p>Exact names and lifetimes may vary by browser. Third-party services may set additional cookies when their components load.</p>
    <table aria-label="Cookie table">
      <thead>
        <tr><th>Cookie</th><th>Provider</th><th>Purpose</th><th>Type</th><th>Expires</th></tr>
      </thead>
      <tbody>
        <tr>
          <td><code>PHPSESSID</code></td>
          <td><?= htmlspecialchars($bizName) ?></td>
          <td>Maintains your server session (e.g., authentication, checkout flow).</td>
          <td>Essential</td>
          <td>End of session</td>
        </tr>
        <tr>
          <td><code>cookie_consent</code> (if enabled)</td>
          <td><?= htmlspecialchars($bizName) ?></td>
          <td>Remembers your cookie preferences so we don’t keep asking.</td>
          <td>Functional</td>
          <td>Up to 12 months</td>
        </tr>
        <tr>
          <td><code>__stripe_mid</code>, <code>__stripe_sid</code> (may vary)</td>
          <td>Stripe</td>
          <td>Fraud prevention and secure payment session during Stripe Checkout.</td>
          <td>Essential for payments</td>
          <td>Up to ~1 year / ~30 mins</td>
        </tr>
        <tr>
          <td><code>NID</code> / similar</td>
          <td>Google (Maps)</td>
          <td>Preferences and anti-abuse when loading Google Maps/Geocoder.</td>
          <td>Functional</td>
          <td>~6 months (typical)</td>
        </tr>
      </tbody>
    </table>
    <p class="muted" style="margin-top:8px;">Third-party providers control their own cookies. See their policies for details: Stripe (<em>Payments</em>) and Google (<em>Maps</em>).</p>
  </div>

  <div class="card">
    <h2>LocalStorage We Use (Not Cookies)</h2>
    <p>These entries stay in your browser until you clear them. They never leave your device unless you submit an order.</p>
    <table aria-label="LocalStorage table">
      <thead>
        <tr><th>Key</th><th>Purpose</th></tr>
      </thead>
      <tbody>
        <tr><td><code>cart_items</code></td><td>Your current basket items.</td></tr>
        <tr><td><code>checkout_details_v1</code></td><td>Checkout name, phone, address, notes, tip, etc., to prefill forms.</td></tr>
        <tr><td><code>user_address</code></td><td>Address saved from the Account page for faster checkout.</td></tr>
        <tr><td><code>order_mode</code></td><td>Delivery vs. collection preference.</td></tr>
        <tr><td><code>order_time_iso</code></td><td>Timestamp of your last order for ETA display.</td></tr>
        <tr><td><code>site_lang</code></td><td>Your language selection for the UI (if used).</td></tr>
      </tbody>
    </table>
    <p><button class="btn" id="resetPref" type="button"><i class="fas fa-rotate-left"></i> Reset saved preferences</button></p>
  </div>

  <div class="card">
    <h2>Managing Cookies</h2>
    <ul>
      <li>You can block or delete cookies in your browser settings. Blocking essential cookies may break sign-in or checkout.</li>
      <li>Stripe and Google set their own cookies when used; see their policies for opt-outs and details.</li>
      <li>If we deploy a consent banner, you can revisit your choice using the button above (which clears our saved preference).</li>
    </ul>
  </div>

  <div class="card">
    <h2>Changes</h2>
    <p>We may update this policy to reflect changes in technology or law. We’ll adjust the date at the top and, when material, provide additional notice.</p>
  </div>

  <div class="card">
    <h2>Contact</h2>
    <p>Questions about this policy? Contact us:</p>
    <ul>
      <li><strong>Business:</strong> <?= htmlspecialchars($bizName) ?></li>
      <li><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($contactMail) ?>"><?= htmlspecialchars($contactMail) ?></a></li>
    </ul>
  </div>
</div>

<script>
  // Simple “reset preferences” button: clears our localStorage keys + consent cookie (if present)
  (function(){
    const btn = document.getElementById('resetPref');
    if(!btn) return;
    btn.addEventListener('click', function(){
      try{
        const keys = ['cart_items','checkout_details_v1','user_address','order_mode','order_time_iso','site_lang','cookie_consent'];
        keys.forEach(k => localStorage.removeItem(k));
        // Expire cookie_consent cookie (if you use one)
        document.cookie = 'cookie_consent=; Max-Age=0; Path=/; SameSite=Lax';
        alert('Saved preferences cleared. You may need to refresh the page.');
      }catch(e){
        alert('Could not clear preferences. Check your browser settings.');
      }
    });
  })();
</script>

<?php
// Optional UI elements
$togglePath = __DIR__ . '/partials/theme-toggle.php';
if (file_exists($togglePath)) include $togglePath;


// Optional: include your global footer/bottom nav if present
$footer = __DIR__ . '/footer.php';
if (file_exists($footer)) include $footer;
?>


