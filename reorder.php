<?php
// reorder.php — Just-Eat style list with image, full history, reorder -> checkout
require_once __DIR__ . '/config.php';  // <-- ensure this is first
date_default_timezone_set('Europe/London');

/* ---------- tiny data helpers ---------- */
function data_dir() { return __DIR__ . '/data'; }
function orders_file(){ return data_dir() . '/order_history.json'; }
function ensure_data_dir(){ $d=data_dir(); if(!is_dir($d)) @mkdir($d,0775,true); }
function load_history(){
  ensure_data_dir();
  $f = orders_file();
  if (!file_exists($f)) return [];
  $j = @file_get_contents($f);
  $a = json_decode($j,true);
  return is_array($a) ? $a : [];
}
function user_key(){
  if (!empty($_SESSION['user']['email'])) return 'user:'.strtolower($_SESSION['user']['email']);
  if (!empty($_SESSION['user_id']))       return 'user:'.$_SESSION['user_id'];
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  return 'ip:'.sha1($ip);
}
function money($n){ return '£' . number_format((float)$n, 2); }
function compute_total(array $items): float {
  $t = 0.0;
  foreach ($items as $it) $t += ((float)($it['price'] ?? 0)) * max(1, (int)($it['qty'] ?? 1));
  return $t;
}
function count_items(array $items): int {
  $c = 0; foreach($items as $it){ $c += max(1,(int)($it['qty'] ?? 1)); } return $c;
}

/* ---------- POST: reorder one historical order ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder_now') {
  if (session_status() === PHP_SESSION_NONE) { session_start(); }

  $raw   = $_POST['items_json'] ?? '[]';
  $items = json_decode($raw, true);
  if (!is_array($items)) $items = [];

  // sanitize
  $clean = [];
  foreach ($items as $it){
    $name  = trim((string)($it['name']  ?? ''));
    $price = (float)($it['price'] ?? 0);
    $qty   = max(1, (int)($it['qty']   ?? 1));
    if ($name === '' || $price < 0) continue;
    $clean[] = ['name'=>$name, 'price'=>$price, 'qty'=>$qty];
  }

  // ✅ session cart for checkout to pick up
  $_SESSION['cart'] = $clean;

  // jump to checkout (flag with ?reorder=1)
  header('Location: checkout.php?reorder=1');
  exit;
}


/* ---------- GET: list all orders for current user ---------- */
$hist   = load_history();
$key    = user_key();
$orders = $hist[$key] ?? [];

// newest → oldest
usort($orders, function($a,$b){
  return strcmp($b['placed_at'] ?? '', $a['placed_at'] ?? '');
});

$RESTAURANT_NAME = defined('RESTAURANT_NAME') ? RESTAURANT_NAME : (defined('SITE_NAME') ? SITE_NAME : 'Premi Spice');
$thumbSrc = 'assets/images/burgers2.jfif';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <?php if (file_exists(__DIR__ . '/partials/theme-head.php')) include __DIR__ . '/partials/theme-head.php'; ?>
  <title>Orders</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    /* ---------- THEME VARIABLES (fixed) ---------- */
    /* Base: LIGHT */
    :root{
      --bg:#f6f7f8; --text:#111; --muted:#6b7280; --line:#e5e7eb;
      --card:#fff; --primary:#f04f32; --pill:#fff; --pill-text:#111;
    }
    /* Prefer DARK if user’s OS is dark (when no manual toggle is set) */
    @media (prefers-color-scheme: dark){
      :root{ --bg:#0f141a; --text:#fff; --muted:#9aa1ab; --line:#27303a; --card:#151a1f; --pill:#fff; --pill-text:#111; }
    }
    /* Manual overrides via data-theme on <html> or <body> */
    html[data-theme="light"], body[data-theme="light"]{
      --bg:#f6f7f8; --text:#111; --muted:#6b7280; --line:#e5e7eb; --card:#fff; --pill:#fff; --pill-text:#111;
    }
    html[data-theme="dark"], body[data-theme="dark"]{
      --bg:#0f141a; --text:#fff; --muted:#9aa1ab; --line:#27303a; --card:#151a1f; --pill:#fff; --pill-text:#111;
    }

    body{ margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial; background:var(--bg); color:var(--text); }
    .wrap{ max-width:960px; margin:18px auto 80px; padding:0 16px; }
    .heading{ font-size:20px; font-weight:900; text-align:center; margin:6px 0 18px; }

    .list{ display:flex; flex-direction:column; gap:14px; }
    .oc{
      background:var(--card); border:1px solid var(--line); border-radius:18px;
      box-shadow:0 10px 22px rgba(0,0,0,.18); padding:14px;
    }
    .oc-inner{
      display:grid; grid-template-columns:96px 1fr; gap:14px; align-items:center;
    }
    .thumb{
      width:96px; height:96px; border-radius:16px; object-fit:cover; border:1px solid var(--line);
      background:#ddd;
    }
    .title{ font-size:18px; font-weight:900; line-height:1.1; }
    .status{ color:var(--muted); margin-top:2px; }

    /* Link colour switches with theme */
    .link{ display:inline-block; margin:6px 0 2px; color:#0b65d8; text-decoration:underline; cursor:pointer; }
    html[data-theme="dark"] .link, body[data-theme="dark"] .link{ color:#cdd5df; }
    @media (prefers-color-scheme: dark){ .link{ color:#cdd5df; } }
    html[data-theme="light"] .link, body[data-theme="light"] .link{ color:#0b65d8; }

    .meta{ color:var(--muted); }

    .pill{
      margin-top:12px; width:100%; border:none; cursor:pointer;
      background:var(--pill); color:var(--pill-text); font-weight:900; font-size:16px;
      padding:12px 14px; border-radius:999px; box-shadow:0 4px 12px rgba(0,0,0,.12);
    }
    .pill:active{ transform:translateY(1px); }

    details.lines{
      margin-top:10px; border-top:1px dashed var(--line); padding-top:8px; color:var(--muted);
    }
    /* Hide the default disclosure/triangle marker completely */
    details.lines > summary{ display:none; list-style:none; }
    details.lines > summary::-webkit-details-marker{ display:none; }
    details.lines > summary::marker{ content:''; }

    .lines .row{ display:flex; justify-content:space-between; gap:10px; padding:6px 0; }
    .lines .name{ color:var(--text); font-weight:700; }
    .lines .qty{ color:var(--muted); width:52px; text-align:right; }
    .lines .price{ width:80px; text-align:right; }

    .empty{ color:var(--muted); text-align:center; padding:30px 10px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="heading">Orders</div>

    <?php if (empty($orders)): ?>
      <div class="oc empty">No previous orders found for this device/account yet.</div>
    <?php else: ?>
      <div class="list">
        <?php foreach ($orders as $idx => $order): ?>
          <?php
            $code   = $order['order_code'] ?? '';
            $when   = $order['placed_at'] ?? '';
            $items  = is_array($order['items'] ?? null) ? $order['items'] : [];
            $fees   = $order['fees'] ?? [];
            $mode   = strtolower($order['customer']['mode'] ?? 'delivery');
            $statusWord = ($mode === 'collection') ? 'Collected' : 'Delivered';
            $itemsCount = count_items($items);
            $total = compute_total($items) + (float)($fees['delivery'] ?? 0) + (float)($fees['tip'] ?? 0);
            $dateStr = $when ? date('d/m/Y', strtotime($when)) : '';
            $detailsId = 'd'.($order['order_code'] ?? ('x'.$idx));
          ?>
          <div class="oc">
            <div class="oc-inner">
              <img class="thumb" src="<?= htmlspecialchars($thumbSrc) ?>" alt="Order thumbnail">
              <div>
                <div class="title"><?= htmlspecialchars($RESTAURANT_NAME) ?></div>
                <div class="status"><?= $statusWord ?> • <?= htmlspecialchars($dateStr) ?></div>

                <a class="link" href="#" data-toggle="<?= $detailsId ?>">View order</a>
                <div class="meta"><?= $itemsCount ?> item<?= $itemsCount!==1?'s':'' ?> • <?= money($total) ?></div>

                <form method="POST" action="reorder.php" style="margin:0;">
                  <input type="hidden" name="action" value="reorder_now">
                  <input type="hidden" name="items_json" value='<?= htmlspecialchars(json_encode($items, JSON_UNESCAPED_UNICODE)) ?>'>
                  <button class="pill" type="submit">Order again</button>
                </form>

                <details id="<?= $detailsId ?>" class="lines">
                  <summary></summary>
                  <?php if (empty($items)): ?>
                    <div class="row"><span>No item lines stored.</span></div>
                  <?php else: ?>
                    <?php foreach ($items as $line): ?>
                      <div class="row">
                        <div class="name"><?= htmlspecialchars((string)($line['name'] ?? 'Item')) ?></div>
                        <div class="qty">× <?= max(1,(int)($line['qty'] ?? 1)) ?></div>
                        <div class="price"><?= money((float)($line['price'] ?? 0)) ?></div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </details>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if (file_exists(__DIR__ . '/partials/bottom-nav.php')) include __DIR__ . '/partials/bottom-nav.php'; ?>
  <?php if (file_exists(__DIR__ . '/partials/theme-toggle.php')) include __DIR__ . '/partials/theme-toggle.php'; ?>

  <script>
    // “View order” toggles the <details>
    document.addEventListener('click', function(e){
      const a = e.target.closest('a[data-toggle]');
      if(!a) return;
      e.preventDefault();
      const id = a.getAttribute('data-toggle');
      const d  = document.getElementById(id);
      if (d) d.open = !d.open;
    }, false);
  </script>
</body>
</html>

