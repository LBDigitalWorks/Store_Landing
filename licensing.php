<?php
// Safe footer include: looks in this folder first, then /partials
$footer_candidates = [
  __DIR__ . '/footer.php',
  __DIR__ . '/partials/footer.php',
];

$__footer_included = false;
foreach ($footer_candidates as $fp) {
  if (is_file($fp)) { include $fp; $__footer_included = true; break; }
}

if (!$__footer_included) {
  // Stay silent in production, but you can log if you want:
  // error_log("Footer not found. Looked for: " . implode(', ', $footer_candidates));
}
require_once __DIR__ . '/config.php';
date_default_timezone_set('Europe/London');

$bizName      = defined('RESTAURANT_NAME') ? RESTAURANT_NAME : 'Your Restaurant';
$lastUpdated  = date('F j, Y');

/*
  If you have actual licence details, set them here or define as constants in config.php.
  Example (config.php):
    define('LIC_AUTHORITY', 'Anytown Borough Council');
    define('LIC_NUMBER', 'PRM-123456');
    define('LIC_DPS', 'Jane Smith');
    define('LIC_ON_HOURS', 'Mon–Sun 10:00–23:00');
    define('LIC_OFF_HOURS','Mon–Sun 10:00–23:00 (off-sales/delivery)');
*/
$licAuthority = defined('LIC_AUTHORITY') ? LIC_AUTHORITY : 'Your Local Licensing Authority';
$licNumber    = defined('LIC_NUMBER')    ? LIC_NUMBER    : 'LIC-XXXXX';
$licDps       = defined('LIC_DPS')       ? LIC_DPS       : 'Designated Premises Supervisor (DPS) Name';
$onHours      = defined('LIC_ON_HOURS')  ? LIC_ON_HOURS  : 'Stated on premises licence';
$offHours     = defined('LIC_OFF_HOURS') ? LIC_OFF_HOURS : 'Stated on premises licence';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php if (file_exists(__DIR__ . '/partials/theme-head.php')) include __DIR__ . '/partials/theme-head.php'; ?>
<title>Licensing &amp; Age Policy • <?= htmlspecialchars($bizName) ?></title>
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
  .two-col{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  @media (max-width:720px){ .two-col{ grid-template-columns:1fr; } }
</style>
</head>
<body>

<div class="page">
  <div class="h1">Licensing &amp; Age Policy</div>
  <div class="sub">Last updated: <?= htmlspecialchars($lastUpdated) ?> • Applies to orders placed directly with <?= htmlspecialchars($bizName) ?></div>

  <div class="card">
    <p><span class="badge">Summary</span></p>
    <p>This page explains our licensing status and how we prevent underage or unlawful sales. Where we sell age-restricted products (e.g., alcohol), we operate a <strong>Challenge 25</strong> policy and verify age on collection or delivery. We will refuse service if suitable ID is not provided or where the law requires refusal.</p>
  </div>

  <div class="card">
    <h2>Premises Licence</h2>
    <div class="two-col">
      <div><strong>Licensing authority:</strong><br><?= htmlspecialchars($licAuthority) ?></div>
      <div><strong>Premises licence no.:</strong><br><?= htmlspecialchars($licNumber) ?></div>
      <div><strong>Designated Premises Supervisor (DPS):</strong><br><?= htmlspecialchars($licDps) ?></div>
      <div><strong>Permitted hours (on/off sales):</strong><br>On: <?= htmlspecialchars($onHours) ?><br>Off/Delivery: <?= htmlspecialchars($offHours) ?></div>
    </div>
    <p class="muted" style="margin-top:8px;">If you believe any information above is inaccurate, please contact us and we’ll correct it.</p>
  </div>

  <div class="card">
    <h2>Age-Restricted Sales (Alcohol)</h2>
    <ul>
      <li>We do not sell alcohol to anyone under 18 (<em>Licensing Act 2003</em>).</li>
      <li><strong>Challenge 25:</strong> If you’re lucky enough to look under 25, we’ll ask for <strong>valid photo ID</strong> (PASS card, UK/ROI driving licence, or passport).</li>
      <li><strong>Delivery &amp; collection:</strong> Age is verified at the door/collection point. We only hand over to the purchaser or the named adult customer.</li>
      <li><strong>No ID, no sale:</strong> Without acceptable ID we will remove alcohol from the order and, where applicable, provide a partial refund/credit for the alcohol items only.</li>
      <li><strong>No proxy sales:</strong> We will refuse where we suspect the purchase is for someone under 18.</li>
      <li><strong>Intoxication:</strong> We will not sell alcohol to anyone who appears intoxicated (s.141).</li>
    </ul>
  </div>

  <div class="card">
    <h2>Delivery Controls</h2>
    <ul>
      <li>Drivers carry guidance on acceptable IDs and will decline handover if age cannot be verified.</li>
      <li>We may record a refusal in our incident log and notify the premises management/DPS.</li>
      <li>Where alcohol is removed at the door due to failed checks, non-alcohol items may still be delivered if appropriate.</li>
    </ul>
  </div>

  <div class="card">
    <h2>Staff Training &amp; Records</h2>
    <ul>
      <li>All staff receive induction on the Licensing Objectives and Challenge 25.</li>
      <li>Refusals and incidents are logged and reviewed regularly.</li>
      <li>Only authorised staff under the DPS’s authority may complete alcohol sales.</li>
    </ul>
  </div>

  <div class="card">
    <h2>Complaints &amp; Contact</h2>
    <p>If you have a concern about our licensing practices, please contact us and we’ll respond promptly. You can also contact the local licensing authority listed above.</p>
    <ul>
      <li><strong>Business:</strong> <?= htmlspecialchars($bizName) ?></li>
      <li><strong>Email:</strong> <a href="mailto:hello@example.com">hello@example.com</a></li>
    </ul>
  </div>

  <div class="card">
    <h2>Legal Notes</h2>
    <p>We support the four Licensing Objectives: preventing crime and disorder, public safety, prevention of public nuisance, and protection of children from harm. Nothing in this page limits your statutory rights.</p>
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

