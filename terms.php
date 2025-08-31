<?php
// terms.php — Terms of Service (UK-focused for food ordering / delivery)
require_once __DIR__ . '/config.php';

$BRAND = defined('RESTAURANT_NAME') ? RESTAURANT_NAME : 'Your Restaurant';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php if (file_exists(__DIR__ . '/partials/theme-head.php')) include __DIR__ . '/partials/theme-head.php'; ?>
<title>Terms of Service • <?= htmlspecialchars($BRAND) ?></title>
<style>
  .legal-wrap{max-width:960px;margin:0 auto;padding:24px 16px 96px;}
  .legal-wrap h1{font-size:28px;font-weight:900;margin:0 0 8px;}
  .legal-wrap .muted{color:#9aa1ab;font-size:14px;margin-bottom:18px;}
  .legal-wrap h2{margin-top:22px;font-size:18px;font-weight:800;}
  .legal-wrap p{line-height:1.6;margin:10px 0;}
  .legal-wrap ul{margin:8px 0 16px 18px;}
  .legal-wrap li{margin:6px 0; line-height:1.6;}
  .legal-wrap a{color:inherit;text-decoration:underline;}
  @media (prefers-color-scheme: dark){
    .legal-wrap .muted{color:#a6adbb;}
  }
</style>
</head>
<body>

<div class="legal-wrap">
  <h1>Terms of Service</h1>
  <div class="muted">Last updated: <?= date('j F Y') ?></div>

  <p>Welcome to <?= htmlspecialchars($BRAND) ?> (“we”, “us”, “our”). These Terms of Service (“Terms”) set out the agreement between you and us when you browse our website, place an order for collection or delivery, or use any related services (together, the “Services”). By using the Services, you agree to these Terms.</p>

  <h2>1) Who we are & how to contact us</h2>
  <p><?= htmlspecialchars($BRAND) ?> operates this website and provides food and beverage items for collection and/or delivery. If you need help, contact us using the details shown on the site (see “Contact” or the footer). If you require our registered business details or VAT number, please request them and we’ll provide them.</p>

  <h2>2) Eligibility</h2>
  <p>You must be at least 18 years old to place an order or to create an account. Additional age restrictions may apply to alcohol or other age-restricted items (see our <a href="/licensing.php">Licensing &amp; Age Policy</a>).</p>

  <h2>3) Your account</h2>
  <ul>
    <li>You’re responsible for maintaining the confidentiality of any login details and for all activity under your account.</li>
    <li>Please keep your contact details accurate so we can reach you about your order.</li>
  </ul>

  <h2>4) Ordering process</h2>
  <ul>
    <li>After you place an order, we’ll show an on-screen confirmation and/or email. This acknowledges we received your order—it is not acceptance.</li>
    <li>We accept your order when we start preparing it. We may refuse or cancel any order (for example, where an item is unavailable, the address is outside our delivery area, the order seems fraudulent, or we cannot meet an allergen/safety request).</li>
    <li>Menu items and prices may change at any time. Obvious pricing errors may be corrected and orders cancelled/refunded.</li>
  </ul>

  <h2>5) Allergens & dietary information</h2>
  <p>Our kitchen handles allergens and cross-contamination risks can exist. If you have an allergy or dietary requirement, <strong>contact us before ordering</strong> so we can advise if a dish is suitable. Where you add notes at checkout, we’ll do our best to follow them but cannot guarantee allergen-free preparation.</p>

  <h2>6) Delivery & collection</h2>
  <ul>
    <li>Delivery times are estimates and not guaranteed. Traffic, weather, and volume may affect timing.</li>
    <li>You must provide an accurate, accessible delivery address and phone number. If we can’t deliver due to incorrect details or no answer, we may treat the order as completed and no refund will be due.</li>
    <li>For collection orders, please check your order on pickup and keep hot food appropriately.</li>
  </ul>

  <h2>7) Payments</h2>
  <ul>
    <li>We accept card payments via Stripe and, where shown, cash on delivery/collection.</li>
    <li>Card payments are processed by Stripe. We don’t store full card details on our servers. See our <a href="/privacy.php">Privacy Policy</a> for more on how we handle personal data.</li>
    <li>Prices are shown in GBP and include VAT where applicable.</li>
  </ul>

  <h2>8) Account credit</h2>
  <ul>
    <li>You may add funds to your account balance (“Account Credit”) using Stripe. Account Credit is non-transferable and not redeemable for cash, except where required by law.</li>
    <li>We may limit top-up amounts, apply anti-fraud checks, and suspend credit in cases of suspected misuse, chargebacks or refunds.</li>
    <li>Unless stated otherwise, Account Credit does not expire. Promotional credit may have additional terms or expiry dates.</li>
  </ul>

  <h2>9) Vouchers & promotions</h2>
  <ul>
    <li>Voucher codes and promotions are subject to their stated terms, may be withdrawn at any time, and cannot be combined unless we say so.</li>
    <li>We may decline a promotion if it has expired, doesn’t meet eligibility criteria, or shows signs of misuse.</li>
  </ul>

  <h2>10) Cancellations, changes & refunds</h2>
  <p>Once we start preparing an order, it usually cannot be cancelled. Please see our <a href="/refunds.php">Refunds &amp; Cancellations Policy</a> for details on when a refund may be available (e.g., where an order is incorrect or not delivered).</p>

  <h2>11) Age-restricted items</h2>
  <p>If your order includes alcohol or other age-restricted items, we will require ID on delivery/collection. We reserve the right to refuse supply if suitable ID is not provided or if we believe the purchase is unlawful. See our <a href="/licensing.php">Licensing &amp; Age Policy</a>.</p>

  <h2>12) User content & reviews</h2>
  <ul>
    <li>When you submit reviews or other content, you grant us a non-exclusive licence to use and display that content in connection with the Services.</li>
    <li>Do not post unlawful, defamatory, or infringing content. We may remove content at our discretion.</li>
  </ul>

  <h2>13) Acceptable use of the site</h2>
  <ul>
    <li>Don’t misuse the site (e.g., attempt to gain unauthorised access, scrape data, or disrupt the Services).</li>
    <li>We may suspend or stop providing Services if you break the law or these Terms.</li>
  </ul>

  <h2>14) Intellectual property</h2>
  <p>All menus, graphics, logos, text and software on the site are owned by or licensed to us. You may not copy or reuse any part of the site except for personal, non-commercial use as necessary to place orders and use the Services.</p>

  <h2>15) Privacy & cookies</h2>
  <p>We process personal data in line with our <a href="/privacy.php">Privacy Policy</a> and use cookies as described in our <a href="/cookies.php">Cookie Policy</a>.</p>

  <h2>16) Service changes & availability</h2>
  <p>We may change or suspend the Services (including menus, prices, opening hours and delivery zones) at any time. We’re not liable for unavailability due to maintenance, technical issues or events beyond our control.</p>

  <h2>17) Liability</h2>
  <ul>
    <li>Nothing in these Terms limits liability for death or personal injury caused by negligence, fraud, or anything which cannot legally be limited.</li>
    <li>Subject to the above, we shall not be liable for: (a) losses not caused by our breach; (b) business losses or loss of profit; (c) any indirect or consequential losses.</li>
  </ul>

  <h2>18) Complaints & dispute resolution</h2>
  <p>If something isn’t right, please contact us promptly so we can help. Most issues are resolved informally. If a dispute remains, these Terms and any disputes arising out of them are governed by the laws of England and Wales, and the courts of England and Wales will have exclusive jurisdiction.</p>

  <h2>19) Changes to these Terms</h2>
  <p>We may update these Terms from time to time. The version posted on this page applies to your use of the Services at that time. Material changes will apply to future orders only.</p>

  <h2>20) Contact</h2>
  <p>Questions about these Terms? Email us using the address shown on the site footer or contact page.</p>
</div>

<?php
// Optional: include your global footer/bottom nav if present
$footer = __DIR__ . '/footer.php';
if (file_exists($footer)) include $footer;
?>
</body>
</html>
