<?php
// privacy.php — Privacy Policy (UK/GDPR-ready)
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
<title>Privacy Policy • <?= htmlspecialchars($bizName) ?></title>
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
  .muted{ color:var(--muted); }
  .toc{ display:grid; gap:8px; grid-template-columns:1fr 1fr; }
  .toc a{ display:block; padding:10px 12px; border:1px solid var(--line); border-radius:10px; text-decoration:none; color:inherit; }
  .toc a:hover{ border-color:#3a3a42; }
  a{ color:inherit; text-decoration:underline; text-underline-offset:2px; }
  code, .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
</style>
</head>
<body>

<div class="page">
  <div class="h1">Privacy Policy</div>
  <div class="sub">Last updated: <?= htmlspecialchars($lastUpdated) ?> • Controller: <?= htmlspecialchars($bizName) ?></div>

  <div class="card">
    <p>We respect your privacy. This Privacy Policy explains how <?= htmlspecialchars($bizName) ?> (“we”, “us”, “our”) collects, uses, shares, and protects your personal data when you browse our website, place an order, or contact us. We operate in the United Kingdom and process personal data in accordance with the UK GDPR and Data Protection Act 2018.</p>
  </div>

  <div class="card">
    <h2>Quick navigation</h2>
    <div class="toc">
      <a href="#data-we-collect">Data we collect</a>
      <a href="#how-we-use-data">How we use data</a>
      <a href="#legal-bases">Legal bases</a>
      <a href="#sharing">Sharing your data</a>
      <a href="#cookies">Cookies & local storage</a>
      <a href="#retention">Data retention</a>
      <a href="#security">Security</a>
      <a href="#international">International transfers</a>
      <a href="#your-rights">Your rights</a>
      <a href="#children">Children</a>
      <a href="#changes">Changes</a>
      <a href="#contact">Contact</a>
    </div>
  </div>

  <div id="data-we-collect" class="card">
    <h2>Data we collect</h2>
    <p>We collect the following categories of data when you use our site or services:</p>
    <ul>
      <li><strong>Identity & contact</strong>: name, email, phone number.</li>
      <li><strong>Order & delivery</strong>: items ordered, notes, delivery or collection preference, delivery address (line 1/2, town, postcode).</li>
      <li><strong>Payment</strong>: payments are processed by Stripe. We receive confirmation of payment status and an anonymised token/ID. <em>We do not store or process full card numbers on our servers.</em></li>
      <li><strong>Account credit</strong>: top-ups you make and your available balance.</li>
      <li><strong>Communications</strong>: messages or feedback you send us.</li>
      <li><strong>Technical</strong>: IP address, device type, basic diagnostic logs. Where enabled, analytics cookies may also measure usage (see “Cookies & local storage”).</li>
    </ul>
  </div>

  <div id="how-we-use-data" class="card">
    <h2>How we use your data</h2>
    <ul>
      <li>To accept, prepare, and deliver or make available your order.</li>
      <li>To process payments and account credit top-ups via Stripe.</li>
      <li>To provide customer support and service updates.</li>
      <li>To detect and prevent fraud or misuse.</li>
      <li>To improve our menu, website, and user experience (e.g., measuring which pages are used most).</li>
      <li>To comply with legal and regulatory obligations (e.g., tax records).</li>
    </ul>
  </div>

  <div id="legal-bases" class="card">
    <h2>Our legal bases</h2>
    <ul>
      <li><strong>Contract</strong>: to fulfil your order and provide customer service.</li>
      <li><strong>Legitimate interests</strong>: running and securing our website, preventing fraud, improving our services.</li>
      <li><strong>Consent</strong>: optional analytics and certain marketing communications.</li>
      <li><strong>Legal obligation</strong>: retaining records for tax/audit and handling complaints.</li>
    </ul>
  </div>

  <div id="sharing" class="card">
    <h2>Sharing your data</h2>
    <p>We only share what is necessary for the purpose described.</p>
    <ul>
      <li><strong>Stripe</strong> (payments): card processing and fraud prevention. See Stripe’s Privacy Policy at <a href="https://stripe.com/privacy" target="_blank" rel="noopener">stripe.com/privacy</a>.</li>
      <li><strong>Couriers / staff</strong>: delivery address, name, and phone for order fulfilment.</li>
      <li><strong>Service providers</strong>: secure hosting, email service, and optional analytics.</li>
      <li><strong>Authorities</strong>: where required by law or to protect our rights.</li>
    </ul>
    <p>We do not sell your personal data.</p>
  </div>

  <div id="cookies" class="card">
    <h2>Cookies &amp; local storage</h2>
    <p>We use essential cookies and browser storage to make the site work. Some examples used by this site:</p>
    <ul>
      <li><span class="mono">cart_items</span> (Local Storage): your basket items.</li>
      <li><span class="mono">checkout_details_v1</span> (Local Storage): checkout name, phone, and address you entered.</li>
      <li><span class="mono">order_mode</span> (Local Storage): delivery or collection preference.</li>
      <li><span class="mono">site_lang</span> (Local Storage): your chosen language.</li>
      <li><span class="mono">user_address</span> (Local Storage): address you saved in <em>Account</em> for faster checkout.</li>
    </ul>
    <p>Where we use optional analytics cookies, we’ll ask for your consent and you can withdraw it at any time in your browser or cookie banner settings.</p>
  </div>

  <div id="retention" class="card">
    <h2>Data retention</h2>
    <ul>
      <li><strong>Orders</strong>: typically 6 years for tax/audit purposes (HMRC).</li>
      <li><strong>Account credit</strong>: as long as your account remains active, then deleted or anonymised after 24 months of inactivity.</li>
      <li><strong>Support messages</strong>: usually up to 12 months.</li>
      <li><strong>Server logs</strong>: short-term for security and troubleshooting.</li>
    </ul>
  </div>

  <div id="security" class="card">
    <h2>Security</h2>
    <p>We apply reasonable technical and organisational measures to protect your data, including TLS encryption in transit and restricted access to operational systems. Card data is handled by Stripe; we never store full card details on our servers.</p>
  </div>

  <div id="international" class="card">
    <h2>International transfers</h2>
    <p>Some providers (e.g., Stripe or analytics) may process data outside the UK, including in the EEA or US. Where this happens, we rely on appropriate safeguards such as adequacy decisions or Standard Contractual Clauses.</p>
  </div>

  <div id="your-rights" class="card">
    <h2>Your rights</h2>
    <p>Under UK GDPR, you can:</p>
    <ul>
      <li>Request access to your personal data and a copy of it.</li>
      <li>Ask us to correct inaccurate data, or delete it where we have no lawful reason to keep it.</li>
      <li>Restrict or object to certain processing, including profiling based on legitimate interests.</li>
      <li>Withdraw consent where we rely on consent (e.g., analytics/marketing).</li>
      <li>Request data portability for information you provided to us.</li>
      <li>Lodge a complaint with the ICO: <a href="https://ico.org.uk/make-a-complaint/" target="_blank" rel="noopener">ico.org.uk</a>.</li>
    </ul>
    <p>To exercise your rights, see <a href="#contact">Contact</a> below.</p>
  </div>

  <div id="children" class="card">
    <h2>Children</h2>
    <p>Our services are intended for customers aged 16+ (or the legal age for entering into contracts in your jurisdiction). If you believe a child has provided us personal data, please contact us and we will delete it where required.</p>
  </div>

  <div id="changes" class="card">
    <h2>Changes to this policy</h2>
    <p>We may update this Privacy Policy from time to time. We will post any changes on this page and update the “Last updated” date above. Material changes may also be notified in-app or by email where appropriate.</p>
  </div>

  <div id="contact" class="card">
    <h2>Contact</h2>
    <p>To ask a question or exercise your rights, contact:</p>
    <ul>
      <li><strong>Controller:</strong> <?= htmlspecialchars($bizName) ?></li>
      <li><strong>Email:</strong> <a href="mailto:hello@example.com">hello@example.com</a></li>
      <li><strong>Address:</strong> (Add your business address here)</li>
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


