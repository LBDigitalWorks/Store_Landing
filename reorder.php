<?php
// reorder.php — THEME-MATCHED (light UI with banner, cards, bottom nav)
// - Uses your existing theme styles (no Tailwind)
// - Works without DB (uses session-stored last order + ?demo=1)
// - Posts items into $_SESSION['cart'] and redirects to /store/?reordered=1

require_once __DIR__ . '/config.php';   // add this
// your existing PHP (hours, $recommended_items, etc.) can stay


if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('Europe/London');

// Business hours: 5 PM to 11 PM
$current_hour = (int)date('G');
$restaurant_open = ($current_hour >= 17 && $current_hour < 23);

// Helpers
function £($n) { return '£' . number_format((float)$n, 2); }
function compute_total(array $items): float { $t=0.0; foreach($items as $it){ $t += ((float)$it['price']) * max(0,(int)($it['qty']??1)); } return $t; }

// Recommended add-ons
$recommended_items = [
  ['name' => 'Pilau Rice', 'price' => 5.00],
  ['name' => 'Onion Bhaji', 'price' => 4.50],
  ['name' => 'Paneer Tikka', 'price' => 6.50],
  ['name' => 'Chicken Biryani', 'price' => 7.00],
  ['name' => 'Garlic Naan', 'price' => 3.50],
];

// Demo seed for last order
if (isset($_GET['demo'])) {
  $_SESSION['last_order'] = [
    'order_code' => 'WEB12345',
    'placed_at'  => date('Y-m-d H:i:s', strtotime('-2 days 18:42')),
    'items' => [
      ['name' => 'Chicken Biryani', 'price' => 7.00, 'qty' => 1],
      ['name' => 'Garlic Naan',     'price' => 3.50, 'qty' => 2],
      ['name' => 'Onion Bhaji',     'price' => 4.50, 'qty' => 1],
    ]
  ];
  header('Location: /reorder.php');
  exit;
}

// Handle submit → add items to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
  $incoming = $_POST['items'] ?? [];
  if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
  foreach ($incoming as $row) {
    $name = trim($row['name'] ?? '');
    $price = (float)($row['price'] ?? 0);
    $qty = max(0, (int)($row['qty'] ?? 0));
    if ($name === '' || $qty <= 0) continue;
    $key = 'itm_' . substr(hash('crc32b', $name), 0, 8);
    if (!isset($_SESSION['cart'][$key])) {
      $_SESSION['cart'][$key] = ['name' => $name, 'price' => $price, 'qty' => 0];
    }
    $_SESSION['cart'][$key]['price'] = $price;
    $_SESSION['cart'][$key]['qty'] += $qty;
  }
  header('Location: /store/?reordered=1');
  exit;
}

$lastOrder = $_SESSION['last_order'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Re‑order • Premi Spice</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:0; overflow-x:hidden; background:#f9f9f9; }
    /* --- Top banner & logo (kept from your theme) --- */
    .banner { position:relative; width:100%; height:200px; background:url('assets/images/burgers2.jfif') no-repeat center/cover; box-shadow:0 2px 5px rgba(0,0,0,.2); }
    .banner::after { content:''; position:absolute; inset:0; background:rgba(0,0,0,.4); }
    .icons { position:absolute; top:15px; inset-inline:15px; display:flex; justify-content:space-between; z-index:2; }
    .icons i { color:#fff; font-size:18px; background:rgba(0,0,0,.4); padding:8px; border-radius:50%; }
    .logo-container { position:absolute; bottom:-30px; left:50%; transform:translateX(-50%); width:100px; height:100px; border-radius:50%; overflow:hidden; border:3px solid #fff; z-index:3; box-shadow:0 2px 5px rgba(0,0,0,.2); }
    .logo-container img { width:100%; height:100%; object-fit:cover; }

    /* --- Page container --- */
    .container { padding:20px 20px 90px; text-align:center; margin-top:10px; }
    .restaurant-name { font-size:24px; font-weight:600; margin:10px 0 2px; }
    .address { color:#555; font-size:14px; display:flex; justify-content:center; align-items:center; gap:6px; margin:0; }
    .rating { color:#f6b100; font-weight:600; font-size:15px; margin:8px 0 20px; }

    /* --- Pills/buttons --- */
    .buttons { display:flex; justify-content:center; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
    .btn { display:flex; align-items:center; gap:6px; padding:10px 16px; border-radius:25px; background:#fff; border:1px solid #ccc; font-size:14px; font-weight:500; color:#333; }
    .btn .pill { margin-left:8px; background:#eee; color:#333; padding:2px 8px; border-radius:10px; font-size:13px; font-weight:600; }

    /* --- Banners --- */
    .closed-banner,.open-banner,.alert-note { padding:14px 16px; margin:20px auto; border-radius:10px; max-width:92%; font-size:14px; text-align:center; font-weight:500; display:flex; justify-content:center; align-items:center; gap:8px; box-shadow:0 1px 3px rgba(0,0,0,.05); }
    .closed-banner{ background:#ffe6e6; color:#b30000; }
    .open-banner{ background:#e6ffe6; color:#006600; }

    /* --- Cards / forms (new, but matching your look) --- */
    .card { background:#fff; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.1); text-align:left; margin:20px auto; padding:16px; max-width:920px; }
    .card h3 { margin:0 0 10px; font-size:18px; }
    .row { display:flex; gap:10px; flex-wrap:wrap; }
    .col { flex:1 1 220px; }
    .input { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px; }
    .btn-primary { background:#f04f32; color:#fff; border:none; padding:10px 16px; border-radius:8px; font-size:14px; cursor:pointer; }
    .btn-ghost { background:#f1f1f1; border:1px solid #ccc; color:#333; padding:10px 16px; border-radius:8px; font-size:14px; cursor:pointer; }

    /* --- Order items --- */
    .order-item { background:#fff; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.08); padding:12px; display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px; }
    .order-item .meta { color:#666; font-size:13px; }
    .qty { display:flex; align-items:center; gap:6px; }
    .qty button { width:32px; height:32px; border-radius:8px; border:1px solid #ccc; background:#fff; cursor:pointer; font-size:18px; }
    .qty input { width:46px; text-align:center; border:1px solid #ddd; border-radius:8px; padding:6px 8px; }

    .total-bar { display:flex; justify-content:space-between; align-items:center; padding:12px 0 0; margin-top:8px; border-top:1px solid #eee; }

    /* --- Recommended carousel (kept) --- */
    .recommended-section { text-align:left; padding:10px 15px; max-width:960px; margin:0 auto; }
    .recommended-items { position:relative; display:flex; overflow-x:auto; gap:10px; padding-bottom:10px; width:100%; scroll-snap-type:x mandatory; }
    .recommended-items::after { content:""; position:absolute; top:0; right:0; width:40px; height:100%; background:linear-gradient(to right, rgba(255,255,255,0), #f9f9f9); pointer-events:none; }
    .item-card { min-width:130px; background:#fff; padding:12px; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.1); text-align:center; flex-shrink:0; scroll-snap-align:start; }
    .scroll-hint { font-size:13px; color:#aaa; text-align:right; padding-right:15px; margin-top:4px; }

    /* --- Bottom nav (kept) --- */
    .bottom-nav { position:fixed; bottom:0; left:0; width:100%; background:#fff; border-top:1px solid #ccc; display:flex; justify-content:space-around; align-items:center; padding:10px 0; z-index:9999; height:40px; box-shadow:0 -1px 4px rgba(0,0,0,.05); }
    .nav-item { text-align:center; font-size:12px; color:#333; }
    .nav-item i { display:block; font-size:18px; margin-bottom:4px; }
    .nav-item.active { color:#f04f32; }
  </style>
</head>
<body>
<div class="banner">
  <div class="icons"><i class="fas fa-arrow-left"></i><i class="fas fa-search"></i></div>
  <div class="logo-container"><img src="assets/images/logo.jpg" alt="Logo"></div>
</div>

<div class="container">
  <div class="restaurant-name">Your Restaurant Name</div>
  <div class="address"><i class="fas fa-map-marker-alt"></i> 12 Park Lane, Northside</div>
  <div class="rating"><i class="fas fa-star"></i> 4.6 (44 reviews)</div>

  <?php if (!$restaurant_open): ?>
    <div class="buttons">
      <div class="btn"><i class="fas fa-truck"></i> Delivery <span class="pill" style="color:#fff;background:#6c757d;">Closed</span></div>
      <div class="btn"><i class="fas fa-shopping-bag"></i> Pickup <span class="pill" style="color:#fff;background:#6c757d;">Closed</span></div>
    </div>
    <div class="closed-banner"><i class="fas fa-door-closed"></i> We're closed at the moment. Please check back during our opening hours.</div>
  <?php else: ?>
    <div class="buttons">
      <div class="btn"><i class="fas fa-truck"></i> Delivery <span class="pill">30–45 mins</span></div>
      <div class="btn"><i class="fas fa-shopping-bag"></i> Collection <span class="pill">15 mins</span></div>
    </div>
    <div class="open-banner"><i class="fas fa-door-open"></i> We're open now – place your order!</div>
  <?php endif; ?>

  <!-- FIND ORDER -->
  <form class="card" method="GET" action="/reorder.php" onsubmit="safeTrack('reorder_lookup_submit')">
    <h3><i class="fas fa-magnifying-glass"></i> Find your order</h3>
    <input type="hidden" name="lookup" value="1" />
    <div class="row">
      <div class="col"><input class="input" name="code" placeholder="Order # (e.g., WEB12345)" value="<?= htmlspecialchars($_GET['code'] ?? '') ?>"/></div>
      <div class="col"><input class="input" type="email" name="email" placeholder="Email address" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>"/></div>
      <div class="col" style="display:flex; gap:10px; align-items:center;">
        <button class="btn-primary" type="submit">Find order</button>
        <a class="btn-ghost" href="/reorder.php?demo=1" onclick="safeTrack('reorder_load_demo')">Load demo</a>
      </div>
    </div>
    <?php if (!empty($order_lookup_error)): ?>
      <div style="color:#b30000; margin-top:10px; font-size:14px;"><?= htmlspecialchars($order_lookup_error) ?></div>
    <?php endif; ?>
    <div style="margin-top:8px; font-size:12px; color:#888;">Tip: If you haven't ordered before, tap <strong>Load demo</strong> to try this page.</div>
  </form>

  <!-- LAST ORDER -->
  <div class="card">
    <h3><i class="fas fa-history"></i> Your last order</h3>
    <?php if ($lastOrder): ?>
      <div style="color:#666; font-size:14px; margin-bottom:10px;">Placed <?= htmlspecialchars(date('M j, Y g:ia', strtotime($lastOrder['placed_at'] ?? 'now'))) ?> • Ref <?= htmlspecialchars($lastOrder['order_code'] ?? '—') ?></div>
      <form method="POST" action="/reorder.php" onsubmit="safeTrack('reorder_submit',{ref:'<?= htmlspecialchars($lastOrder['order_code'] ?? '') ?>'})">
        <input type="hidden" name="action" value="add_to_cart" />
        <?php foreach ($lastOrder['items'] as $i => $it): ?>
          <div class="order-item">
            <div>
              <div style="font-weight:600;"><?= htmlspecialchars($it['name']) ?></div>
              <div class="meta"><?= £($it['price']) ?> each</div>
              <input type="hidden" name="items[<?= $i ?>][name]" value="<?= htmlspecialchars($it['name']) ?>" />
              <input type="hidden" name="items[<?= $i ?>][price]" value="<?= htmlspecialchars($it['price']) ?>" />
            </div>
            <div class="qty">
              <button type="button" class="qty-minus" data-index="<?= $i ?>">−</button>
              <input name="items[<?= $i ?>][qty]" id="qty-<?= $i ?>" value="<?= (int)($it['qty'] ?? 1) ?>" />
              <button type="button" class="qty-plus" data-index="<?= $i ?>">+</button>
            </div>
          </div>
        <?php endforeach; ?>
        <div class="total-bar">
          <div style="color:#666;">Adjust quantities, then add everything to your cart.</div>
          <div style="font-weight:700;">Total: <?= £(compute_total($lastOrder['items'])) ?></div>
        </div>
        <div style="margin-top:10px; display:flex; justify-content:flex-end;">
          <button class="btn-primary"><i class="fas fa-cart-plus"></i> Add to cart</button>
        </div>
      </form>
    <?php else: ?>
      <div style="color:#666;">No previous order found yet — use <strong>Find your order</strong> above or load the demo.</div>
    <?php endif; ?>
  </div>

  <!-- RECOMMENDED FOR YOU (kept) -->
  <div class="recommended-section">
    <h3><i class="fas fa-thumbs-up"></i> RECOMMENDED FOR YOU</h3>
    <div class="recommended-items">
      <?php foreach ($recommended_items as $item): ?>
        <div class="item-card">
          <p><strong><?= htmlspecialchars($item['name']) ?></strong></p>
          <p><?= £($item['price']) ?></p>
          <form method="POST" action="/reorder.php" onsubmit="safeTrack('reorder_addon_add',{item:'<?= htmlspecialchars($item['name']) ?>'})">
            <input type="hidden" name="action" value="add_to_cart" />
            <input type="hidden" name="items[0][name]" value="<?= htmlspecialchars($item['name']) ?>" />
            <input type="hidden" name="items[0][price]" value="<?= htmlspecialchars($item['price']) ?>" />
            <input type="hidden" name="items[0][qty]" value="1" />
            <button class="btn-primary" type="submit" style="margin-top:6px; width:100%;"><i class="fas fa-plus"></i> Add</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="scroll-hint"><i class="fas fa-angle-right"></i> Swipe</div>
  </div>
</div>

<!-- JS: qty controls + safe analytics hooks -->
<script>
  function safeTrack(name, meta){ try{ if(window.ANALYTICS && ANALYTICS.track){ ANALYTICS.track(name, meta||{}); } }catch(e){} }
  document.addEventListener('click', function(e){
    var minus = e.target.closest('.qty-minus');
    var plus  = e.target.closest('.qty-plus');
    if (minus){ var i = minus.dataset.index; var el = document.getElementById('qty-'+i); el.value = Math.max(0, parseInt(el.value||'0',10)-1); }
    if (plus){  var i = plus.dataset.index;  var el = document.getElementById('qty-'+i); el.value = Math.max(0, parseInt(el.value||'0',10)+1); }
  });
</script>

<?php include __DIR__ . '/partials/bottom-nav.php'; ?>

</body>
</html>