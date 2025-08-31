<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('Europe/London');

/* ---- Simple per-user/ip order history store ---- */
function orders_file(){ return __DIR__ . '/data/order_history.json'; }
function ensure_data_dir(){
  $dir = __DIR__ . '/data';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
}
function load_history(){
  ensure_data_dir();
  $f = orders_file();
  if (!file_exists($f)) return [];
  $j = @file_get_contents($f);
  $a = json_decode($j, true);
  return is_array($a) ? $a : [];
}
function save_history($a){
  ensure_data_dir();
  @file_put_contents(orders_file(), json_encode($a, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

/* 🔑 Use the SAME key everywhere: prefer signed-in email, else user_id, else IP */
function user_key(){
  if (!empty($_SESSION['user']['email'])) {
    return 'user:' . strtolower(trim($_SESSION['user']['email']));
  }
  if (!empty($_SESSION['user_id'])) {
    return 'user:' . $_SESSION['user_id'];
  }
  return 'ip:' . sha1($_SERVER['REMOTE_ADDR'] ?? '');
}

/* ---- Promote pending order -> history (no provider param required) ---- */
if (!empty($_SESSION['pending_order'])) {
  $order = $_SESSION['pending_order'];

  // ensure timestamps/ids
  $order['placed_at']  = $order['placed_at'] ?? date('c');
  $order['order_code'] = $order['order_code'] ?? strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

  // remember the most recent order in-session too
  $_SESSION['last_order'] = $order;

  // clear the pending flag
  unset($_SESSION['pending_order']);

  // append to per-user history (keyed by email/user/ip)
  $hist = load_history();
  $key  = user_key();
  if (!isset($hist[$key])) {
    $hist[$key] = [];
  }
  array_unshift($hist[$key], $order);  // newest first
  // no limit — keep all past orders
  save_history($hist);
}

/* ---- Restaurant config & map helper ---- */
$RESTAURANT_ADDRESS = defined('RESTAURANT_ADDRESS') ? RESTAURANT_ADDRESS : '12 Park Lane, Northside';
$address  = isset($address)  ? $address  : '123 Takeaway Street';
$postcode = isset($postcode) ? $postcode : 'Doncaster DN12 3AA';
$lat      = isset($lat) ? $lat : null;
$lng      = isset($lng) ? $lng : null;

if (!function_exists('map_embed_url')) {
  function map_embed_url(string $address, string $postcode, ?float $lat=null, ?float $lng=null): string {
    if ($lat !== null && $lng !== null) return "https://www.google.com/maps?q={$lat},{$lng}&z=18&output=embed";
    return "https://www.google.com/maps?q=" . urlencode(trim($address . ' ' . $postcode)) . "&z=18&output=embed";
  }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php include __DIR__ . '/partials/theme-head.php'; ?>
<title>Track your order • Premi Spice</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
*,*::before,*::after{ box-sizing:border-box; }
html,body{
  margin:0; padding:0; background:var(--bg); color:var(--text);
  font-family:'Segoe UI',Tahoma,Verdana,sans-serif; -webkit-text-size-adjust:100%;
}
.page{ min-height:calc(100svh - 56px); padding-bottom:100px; }

/* Header/status */
.topbar{ padding:16px 16px 10px; display:flex; align-items:center; gap:12px; }
.eta{ font-size:14px; color:var(--muted); }
.eta strong{ font-size:22px; color:var(--text); display:block; margin-top:2px; }
.status{
  display:flex; align-items:center; gap:10px; font-weight:800; margin-top:8px;
  color:var(--primary); /* make status text orange */
}
.status i{
  font-size:28px;                /* bigger icon */
  color:var(--primary);          /* orange icon */
  width:28px; text-align:center;
}
.spin{ animation:spin 1.6s linear infinite; }
@keyframes spin{ from{ transform:rotate(0deg);} to{ transform:rotate(360deg);} }

/* THEME-AWARE MAP */
.mapwrap{ position:relative; margin:6px 12px 0; border-radius:14px; overflow:hidden;
  border:1px solid var(--line); box-shadow:0 1px 4px rgba(0,0,0,.08); }
.mapframe{ width:100%; height:56vh; min-height:360px; border:0; display:block; filter:none; }
[data-theme="dark"] .mapwrap{ background:#0e1116; }
[data-theme="dark"] .mapframe{
  /* tasteful darkening similar to about.php */
  filter: invert(0.9) hue-rotate(180deg) saturate(0.5) brightness(0.9) contrast(0.9);
}

/* Cards */
.card{ margin:12px; background:var(--card); border:1px solid var(--line); border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.card h3{ font-size:16px; font-weight:800; margin:0; padding:12px 14px; border-bottom:1px solid var(--line); }
.card .row{ display:flex; align-items:flex-start; gap:10px; padding:12px 14px; }
.icon{ width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;
  background:#f7f7f7; border:1px solid var(--line); color:#333; }
.details{ flex:1; min-width:0; }
.details .label{ font-size:12px; color:var(--muted); margin-bottom:2px; }
.details .val{ font-weight:700; }

/* Summary layout */
.items{ display:flex; flex-direction:column; gap:8px; padding:10px 14px; }
.item{ display:flex; justify-content:space-between; gap:10px; }
.item .name{ font-weight:800; }
.item .meta{ color:var(--muted); font-size:13px; }

.sum{ margin:0 14px 14px; padding-top:10px; }
.sum .row{
  display:flex; justify-content:space-between; align-items:center;
  margin:0; padding:10px 0; color:var(--muted);
}
.sum .row:not(.total){ border-bottom:1px solid var(--line); } /* separators */
.sum .row.total{
  color:var(--text); font-weight:900; font-size:16px;
  border-bottom:0; margin-top:6px; padding-top:12px;
}

/* Contact card */
.tel, .tel:link, .tel:visited{
  color:var(--text) !important; /* black in light, white in dark */
  text-decoration:none;
  font-weight:800;
}
.tel:hover{ text-decoration:underline; }

/* Dark tweaks */
[data-theme="dark"] .icon{ background:#151a1f; border-color:#27303a; color:var(--text); }
[data-theme="dark"] .sum .row{ color:#9aa1ab; }

/* --- Force orange for the live status line --- */
:root { --primary: #f04f32; } /* safety fallback if not set */
.topbar .status,
.topbar .status i { color: var(--primary) !important; }

/* Optional: step styles if you add a visual step list later */
.status-steps .s-step.active .dot,
.status-steps .s-step.active .label {
  color: var(--primary) !important;
  border-color: var(--primary) !important;
}

/* --- Make the live status + ETA text bigger --- */
:root{
  --status-text-size: clamp(16px, 3.2vw, 20px);
  --status-icon-size: clamp(18px, 3.6vw, 24px);
  --eta-small-size:   clamp(14px, 2.8vw, 18px);
  --eta-time-size:    clamp(22px, 4.2vw, 30px);
  --step-label-size:  clamp(14px, 2.8vw, 17px);
}
.topbar .status{ font-size: var(--status-text-size) !important; }
.topbar .status i{ font-size: var(--status-icon-size) !important; }
.eta{ font-size: var(--eta-small-size); }
.eta strong{ font-size: var(--eta-time-size); }
.status-steps .s-step .label{ font-size: var(--step-label-size); }
.status-steps .s-step .dot{ width:10px; height:10px; }
</style>
</head>
<body>
<div class="page">

  <div class="topbar">
    <div class="eta">
      Estimated <span id="etaLabel">delivery around</span>
      <strong id="etaTime">—:—</strong>
      <div class="status">
        <i id="statusIcon" class="fas fa-hourglass-half spin" aria-hidden="true"></i>
        <span id="statusText">Waiting for restaurant to accept</span>
      </div>
    </div>
  </div>

  <div class="mapwrap">
    <!-- Default src: zoomed pin on RESTAURANT -->
    <iframe id="gmapsFrame" class="mapframe" title="Order map"
            src="<?= htmlspecialchars(map_embed_url($RESTAURANT_ADDRESS, $postcode, $lat, $lng)) ?>"></iframe>
  </div>

  <section class="card" style="margin-top:12px;">
    <h3>Order details</h3>
    <div class="row">
      <div class="icon" aria-hidden="true"><i class="fas fa-store"></i></div>
      <div class="details">
        <div class="label">From</div>
        <div class="val" id="fromAddress"><?= htmlspecialchars($RESTAURANT_ADDRESS) ?></div>
      </div>
    </div>
    <div class="row">
      <div class="icon" aria-hidden="true"><i class="fas fa-location-dot"></i></div>
      <div class="details">
        <div class="label">To</div>
        <div class="val" id="toAddress">Loading…</div>
      </div>
    </div>
  </section>

  <!-- Order summary -->
  <section class="card" id="summaryCard">
    <h3>Order summary</h3>
    <div id="items" class="items"></div>
    <div class="sum">
      <div class="row"><span>Subtotal</span><span id="sumSub">£0.00</span></div>
      <div class="row"><span>Delivery fee</span><span id="sumFee">£0.00</span></div>
      <div class="row" id="rowTip" style="display:none;"><span>Tip</span><span id="sumTip">£0.00</span></div>
      <div class="row total"><span>Total</span><span id="sumTotal">£0.00</span></div>
    </div>
  </section>

  <!-- Contact -->
  <section class="card">
    <h3>Contact the restaurant</h3>
    <div class="row">
      <div class="icon" aria-hidden="true"><i class="fas fa-phone"></i></div>
      <div class="details">
        <div class="label">Phone</div>
        <div class="val">
          <!-- removed the orange phone <i> so only the number shows -->
          <a class="tel" href="tel:07712345678">07712 345 678</a>
        </div>
        <div class="label" style="margin-top:6px;">If you need to update your order or directions, give us a call.</div>
      </div>
    </div>
  </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', initTracking);

// ----- Helpers
function safeJSON(s){ try{ return JSON.parse(s); }catch(e){ return null; } }
function fmtTime(d){
  try{ return new Intl.DateTimeFormat([], {hour:'2-digit', minute:'2-digit'}).format(d); }
  catch(e){ const h=String(d.getHours()).padStart(2,'0'); const m=String(d.getMinutes()).padStart(2,'0'); return h+':'+m; }
}
function buildSinglePointEmbed(query){
  // Keep zoomed in on the restaurant
  return 'https://www.google.com/maps?output=embed&z=18&q=' + encodeURIComponent(query);
}

// ----- Status model (simple 4-step)
const STATUS_DELIVERY = [
  'Waiting for restaurant to accept',
  'Order accepted • Preparing your order',
  'Your order is on its way',
  'Delivered'
];
const STATUS_COLLECTION = [
  'Waiting for restaurant to accept',
  'Order accepted • Preparing your order',
  'Ready for collection',
  'Collected'
];
const ICONS = [
  'fa-hourglass-half', // waiting
  'fa-utensils',       // preparing
  'fa-truck',          // on the way / ready
  'fa-circle-check'    // delivered/collected
];

function getStoredStep(){
  const v = localStorage.getItem('order_status_step');
  return (v==null) ? 0 : Math.max(0, Math.min(3, parseInt(v,10)||0));
}
function applyStatus(mode, step){
  const statusEl = document.getElementById('statusText');
  const iconEl   = document.getElementById('statusIcon');
  const arr = (mode === 'collection') ? STATUS_COLLECTION : STATUS_DELIVERY;
  statusEl.textContent = arr[step] || arr[0];

  // swap icon + spin only while waiting; keep all orange via CSS
  iconEl.className = 'fas ' + (ICONS[step] || ICONS[0]);
  if (step === 0) iconEl.classList.add('spin'); else iconEl.classList.remove('spin');

  // tweak ETA label wording for collection
  document.getElementById('etaLabel').textContent = (mode === 'collection') ? 'ready around' : 'delivery around';
}

function money(n){ return '£' + (Number(n||0)).toFixed(2); }

function renderSummary(){
  const CART_KEY = 'cart_items';
  const DELIVERY_FEE = 2.99;
  const itemsEl = document.getElementById('items');
  const sumSub  = document.getElementById('sumSub');
  const sumFee  = document.getElementById('sumFee');
  const sumTip  = document.getElementById('sumTip');
  const rowTip  = document.getElementById('rowTip');
  const sumTot  = document.getElementById('sumTotal');

  const cart = safeJSON(localStorage.getItem(CART_KEY)) || [];
  const mode = (localStorage.getItem('order_mode') || 'delivery').toLowerCase();
  const details = safeJSON(localStorage.getItem('checkout_details_v1')) || {};
  const tipVal = Number(details.tip || 0);

  itemsEl.innerHTML = '';
  if (!cart.length){
    itemsEl.innerHTML = '<div class="item"><div><div class="name">Your basket is empty</div><div class="meta">Add items to continue</div></div><div>£0.00</div></div>';
  }else{
    cart.forEach(it=>{
      const el = document.createElement('div');
      el.className = 'item';
      const safeName = String(it?.name ?? '');
      const displayName = safeName.replace(/(\d+)\s*$/, '$1"'); // append " to size numbers
      el.innerHTML = `
        <div>
          <div class="name">${displayName.replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[m]))}</div>
          <div class="meta">${Number(it?.qty||1)} × £${(Number(it?.price||0)).toFixed(2)}</div>
        </div>
        <div><strong>£${(Number(it?.price||0) * Number(it?.qty||1)).toFixed(2)}</strong></div>
      `;
      itemsEl.appendChild(el);
    });
  }

  const sub = cart.reduce((a,b)=> a + (Number(b.price)||0)*(Number(b.qty)||1), 0);
  const fee = (mode === 'delivery' && cart.length) ? DELIVERY_FEE : 0;
  const total = Math.max(0, sub + fee + tipVal);

  sumSub.textContent = money(sub);
  sumFee.textContent = money(fee);
  sumTip.textContent = money(tipVal);
  rowTip.style.display = tipVal > 0 ? '' : 'none';
  sumTot.textContent = money(total);
}

async function initTracking(){
  // --- Mode & details from localStorage (set by checkout flow)
  const details   = safeJSON(localStorage.getItem('checkout_details_v1')) || {};
  const savedAddr = safeJSON(localStorage.getItem('user_address')) || null;
  const mode      = (localStorage.getItem('order_mode') || 'delivery').toLowerCase();

  // --- ETA: order_time + 45 min (saved at payment-success or just before redirect)
  let orderISO = localStorage.getItem('order_time_iso');
  if (!orderISO) {
    orderISO = new Date().toISOString();
    localStorage.setItem('order_time_iso', orderISO);
  }
  const eta = new Date(new Date(orderISO).getTime() + 45*60*1000);
  document.getElementById('etaTime').textContent = fmtTime(eta);

  // --- Addresses
  const destText =
    (details.address && String(details.address).trim()) ||
    (savedAddr ? [savedAddr.line1,savedAddr.line2,savedAddr.city,savedAddr.postcode].filter(Boolean).join(', ') : '') ||
    '';

  const originText = <?php echo json_encode($RESTAURANT_ADDRESS); ?>;

  document.getElementById('toAddress').textContent = destText || 'No address found';

  // --- Status (default to step 0; will be updated by admin later)
  const step = getStoredStep(); // 0..3
  applyStatus(mode, step);

  // --- Map: ALWAYS show single zoomed pin on the RESTAURANT (as requested)
  const iframe = document.getElementById('gmapsFrame');
  iframe.src = buildSinglePointEmbed(originText);

  // --- Render summary from localStorage
  renderSummary();
}
</script>

<?php
// Optional: include bottom nav & theme toggle for consistency
$navPath = __DIR__ . '/partials/bottom-nav.php';
if (file_exists($navPath)) include $navPath;
$togglePath = __DIR__ . '/partials/theme-toggle.php';
if (file_exists($togglePath)) include $togglePath;
?>
</body>
</html>

