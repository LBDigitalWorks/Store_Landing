<?php
// checkout.php — review & pay page (Stripe + Cash + Account Credit)
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('Europe/London');

/*
  EXPECTED in config.php:
  -----------------------
  define('STRIPE_SECRET_KEY', 'sk_test_xxx');  // YOUR STRIPE SECRET KEY
  define('STRIPE_SUCCESS_URL', BASE_URL . 'order-tracking.php?provider=stripe&session_id={CHECKOUT_SESSION_ID}');
  define('STRIPE_CANCEL_URL',  BASE_URL . 'checkout.php?canceled=1');
*/
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php include __DIR__ . '/partials/theme-head.php'; ?>
<title>Checkout • Premi Spice</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
:root{
  --bg:#f7f8fa; --text:#111; --muted:#6b7280; --line:#e5e7eb; --card:#ffffff;
  --primary:#f04f32;
  --nav-h: 56px;
}
*,*::before,*::after{ box-sizing:border-box; }
html,body{
  margin:0; padding:0; background:var(--bg); color:var(--text);
  font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-text-size-adjust:100%;
}
.page{ min-height:calc(100svh - 56px); padding:16px 16px 120px; max-width:960px; margin:0 auto; }

.h1{ font-size:24px; font-weight:900; margin:6px 4px 14px; display:flex; align-items:center; gap:10px; }
.h1 i{ color:#fff; background:var(--primary); width:36px; height:36px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; }

.card{
  background:var(--card); border:1px solid var(--line); border-radius:16px;
  box-shadow:0 1px 3px rgba(0,0,0,.06); padding:14px; margin:12px 0;
}
.card h3{ margin:0 0 10px; font-size:16px; font-weight:800; }

.row{ display:flex; gap:10px; flex-wrap:wrap; }
.input, .textarea{
  width:100%; padding:12px; font-size:15px; border-radius:12px;
  border:1.5px solid var(--line); background:#fff; color:#111;
}
.textarea{ min-height:84px; resize:vertical; }
[data-theme="dark"] .input, [data-theme="dark"] .textarea{
  background:#1b222b; color:var(--text); border-color:#27303a;
}

.option-row{
  display:flex; align-items:center; justify-content:space-between; gap:10px;
  padding:12px; border:1px solid var(--line); border-radius:12px; background:var(--card);
}
.option-row .left{ display:flex; align-items:center; gap:10px; font-weight:900; }

.icon-circle{
  width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center;
  background:var(--primary); color:#fff; border:none;
}

/* Tips */
.tip-grid{ display:flex; gap:10px; flex-wrap:wrap; }
.tip{
  display:inline-flex; align-items:center; justify-content:center; gap:6px;
  padding:10px 14px; min-height:40px; min-width:60px;
  border-radius:999px; border:2px solid var(--line);
  background:var(--card); color:var(--text);
  font-weight:900; font-size:15px; line-height:1; cursor:pointer;
  -webkit-appearance:none; appearance:none; user-select:none;
}
.tip.active, .tip[aria-pressed="true"]{
  background:var(--primary) !important; border-color:var(--primary) !important; color:#fff !important;
}
[data-theme="dark"] .tip{ border-color:#27303a; background:#151a1f; color:var(--text); }

/* Payment selector */
.pay-select{
  display:flex; align-items:center; justify-content:space-between; gap:10px;
  padding:12px; border:1px solid var(--line); border-radius:12px; background:var(--card); cursor:pointer;
}
.pay-select .meth{ display:flex; align-items:center; gap:10px; font-weight:900; }
.pay-select .logo{ width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; }
.pay-select .current{ color:var(--muted); }

/* Items & Summary */
.items{ display:flex; flex-direction:column; gap:8px; margin-top:6px; }
.item{ display:flex; justify-content:space-between; gap:10px; }
.item .name{ font-weight:800; }
.item .meta{ color:var(--muted); font-size:13px; }
.sum{ border-top:1px solid var(--line); margin-top:10px; padding-top:10px; }
.sum .row{ display:flex; justify-content:space-between; margin:6px 0; color:var(--muted); }
.sum .row.total{ color:var(--text); font-weight:900; font-size:16px; }

/* Primary button */
.cta-wrap{ position:sticky; bottom:0; background:transparent; padding-top:12px; }
.btn{
  width:100%; display:inline-flex; align-items:center; justify-content:center; gap:10px;
  padding:12px 14px; font-weight:900; font-size:16px; text-decoration:none; cursor:pointer;
  background:var(--primary); color:#fff; border:2px solid var(--primary); border-radius:12px;
}
[data-theme="dark"] .btn{ background:#f04f32; border-color:#f04f32; color:#fff; }
.btn:disabled{ opacity:.6; cursor:not-allowed; }

/* Payment modal (bottom sheet) */
.modal{
  position:fixed; inset:0; background:rgba(0,0,0,.35);
  display:none; align-items:flex-end; justify-content:center;
  z-index:100000;
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
.modal.show{ display:flex; }
.sheet{
  width:100%; max-width:700px; background:var(--card); color:var(--text);
  border-top-left-radius:16px; border-top-right-radius:16px; box-shadow:0 -8px 30px rgba(0,0,0,.25); padding:14px;
  margin-bottom:12px;
}
.sheet .h{ display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px; }
.sheet .x{ background:#f3f4f6; border:1px solid #e5e7eb; padding:8px 10px; border-radius:10px; }
[data-theme="dark"] .sheet .x{ background:#1b222b; border-color:#27303a; color:var(--text); }
.pay-list{ display:grid; gap:10px; }
.pay-btn{
  display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px;
  border:1px solid var(--line); border-radius:12px; background:var(--card); cursor:pointer; font-weight:900;
}
.pay-btn .left{ display:flex; align-items:center; gap:10px; }
.pay-logo{ width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; }
.pay-logo.apple{ font-size:22px; }
.pay-logo.card{ font-size:18px; }
.pay-logo.cash{ font-size:18px; }
.pay-logo.credit{ font-size:18px; }

/* Dark tweaks */
[data-theme="dark"] .option-row,
[data-theme="dark"] .pay-select,
[data-theme="dark"] .tip,
[data-theme="dark"] .card{ background:#151a1f; border-color:#27303a; color:var(--text); }
[data-theme="dark"] .sum .row{ color:#9aa1ab; }

/* Hide theme toggle while sheet is open */
.sheet-open .theme-toggle,
.sheet-open [data-theme-toggle],
.sheet-open #themeToggle { display:none !important; }
</style>
</head>
<body>

<?php
// Inline seed: if you came from reorder (&cart is in session), copy it to localStorage on load
$sessionCart = array_values($_SESSION['cart'] ?? []); // ensure simple numeric array
?>
<script>
// Seed localStorage from PHP session when arriving from reorder, or if cart is empty
(function(){
  try{
    const params = new URLSearchParams(location.search);
    const shouldImport = params.has('reorder') || !localStorage.getItem('cart_items');
    const sessionCart = <?php echo json_encode($sessionCart, JSON_UNESCAPED_UNICODE); ?>;
    if (shouldImport && Array.isArray(sessionCart) && sessionCart.length){
      localStorage.setItem('cart_items', JSON.stringify(sessionCart));
    }
  }catch(e){}
})();
</script>

<div class="page">
  <div class="h1"><i class="fas fa-bag-shopping"></i> Checkout</div>

  <!-- Order details -->
  <section class="card">
    <h3>Order details</h3>

    <!-- Profile -->
    <div class="option-row" style="margin-bottom:8px;">
      <div class="left"><span class="icon-circle"><i class="fas fa-user"></i></span> Your details</div>
    </div>
    <div class="row" style="margin-bottom:10px;">
      <input class="input" id="name" placeholder="Full name" autocomplete="name">
      <input class="input" id="phone" placeholder="Phone number" inputmode="tel" autocomplete="tel">
    </div>

   <!-- Address -->
   <div class="option-row" style="margin-bottom:8px;">
     <div class="left"><span class="icon-circle"><i class="fas fa-house"></i></span> Delivery address</div>
     <small id="addrHint" class="current"></small>
   </div>

   <div class="row" style="margin-bottom:10px;">
     <input class="input" id="address1" placeholder="Address line 1" autocomplete="address-line1">
     <input class="input" id="address2" placeholder="Address line 2 (optional)" autocomplete="address-line2">
   </div>

   <div class="row" style="margin-bottom:10px;">
     <input class="input" id="city" placeholder="Town/City" autocomplete="address-level2">
     <input class="input" id="postcode" placeholder="Postcode" autocomplete="postal-code" style="max-width:200px;">
   </div>

   <div class="help" style="font-size:12px; color:var(--muted); margin-top:6px;">If you’ve saved an address on the Account page, we’ll prefill it here.</div>

    <!-- Notes -->
    <div class="option-row" style="margin:12px 0 8px;">
      <div class="left"><span class="icon-circle"><i class="fas fa-comment-dots"></i></span> Order notes</div>
    </div>
    <textarea class="textarea" id="notes" placeholder="Add cooking or delivery notes (optional)"></textarea>

    <!-- ASAP -->
    <div class="option-row" style="margin-top:12px;">
      <div class="left"><span class="icon-circle"><i class="fas fa-clock"></i></span> Deliver as soon as possible</div>
      <small>~ 30–45 min</small>
    </div>
  </section>

  <!-- Delivery options -->
  <section class="card">
    <h3>Delivery options</h3>
    <div class="row">
      <label class="option-row" style="flex:1; cursor:pointer;">
        <div class="left"><span class="icon-circle"><i class="fas fa-truck"></i></span> Delivery</div>
        <div><strong>£2.99</strong> <input type="radio" name="ship" value="delivery" id="shipDelivery" style="margin-left:10px;" checked></div>
      </label>
      <label class="option-row" style="flex:1; cursor:pointer;">
        <div class="left"><span class="icon-circle"><i class="fas fa-bag-shopping"></i></span> Collection</div>
        <div><strong>Free</strong> <input type="radio" name="ship" value="collection" id="shipCollection" style="margin-left:10px;"></div>
      </label>
    </div>
  </section>

  <!-- Vouchers -->
  <section class="card">
    <h3>Vouchers & discounts</h3>
    <div class="row">
      <input class="input" id="voucher" placeholder="Enter voucher code">
      <button id="applyVoucher" class="btn" type="button" style="width:auto; padding-inline:16px;">Apply</button>
    </div>
    <div id="voucherMsg" class="help" style="font-size:12px; color:var(--muted); margin-top:6px;"></div>
  </section>

  <!-- Tips -->
  <section class="card">
    <h3>Tip your courier?</h3>
    <div class="tip-grid" id="tipGrid">
      <button class="tip" data-tip="1" type="button" aria-pressed="false">£1</button>
      <button class="tip" data-tip="2" type="button" aria-pressed="false">£2</button>
      <button class="tip" data-tip="3" type="button" aria-pressed="false">£3</button>
      <button class="tip" data-tip="other" type="button" aria-pressed="false">Other</button>
      <input class="input" id="tipOther" placeholder="Enter tip amount" inputmode="decimal" style="display:none; max-width:160px;">
    </div>
    <div class="help" style="font-size:12px; color:var(--muted); margin-top:6px;">Tips go 100% to your courier.</div>
  </section>

  <!-- Payment -->
  <section class="card">
    <h3>How would you like to pay?</h3>
    <div class="pay-select" id="openPay" tabindex="0" role="button" aria-haspopup="dialog" aria-controls="payModal">
      <div class="meth">
        <span class="logo"><i class="fas fa-credit-card"></i></span>
        <span id="payLabel">Credit or debit card</span>
      </div>
      <div class="current"><i class="fas fa-chevron-right"></i></div>
    </div>
  </section>

  <!-- Order summary -->
  <section class="card">
    <h3>Order summary</h3>
    <div id="items" class="items"></div>

    <div class="sum">
      <div class="row"><span>Subtotal</span><span id="sumSub">£0.00</span></div>
      <div class="row" id="rowDiscount" style="display:none;"><span>Discount</span><span id="sumDiscount">−£0.00</span></div>
      <div class="row"><span>Delivery fee</span><span id="sumFee">£2.99</span></div>
      <div class="row" id="rowTip" style="display:none;"><span>Tip</span><span id="sumTip">£0.00</span></div>
      <div class="row total"><span>Total</span><span id="sumTotal">£0.00</span></div>
    </div>

    <div class="cta-wrap">
      <button id="payBtn" class="btn" type="button">Pay by Card (£0.00)</button>
    </div>
  </section>
</div>

<!-- Payment modal -->
<div id="payModal" class="modal" aria-hidden="true">
  <div class="sheet" role="dialog" aria-modal="true" aria-labelledby="payTitle">
    <div class="h">
      <div id="payTitle" style="font-weight:800;">Select a payment method</div>
      <button class="x" id="payClose" type="button"><i class="fas fa-times"></i></button>
    </div>
    <div class="pay-list" id="payList">
      <button class="pay-btn" data-method="Apple Pay">
        <span class="left"><span class="pay-logo apple"><i class="fab fa-apple"></i></span> Apple Pay</span>
        <i class="fas fa-chevron-right"></i>
      </button>
      <button class="pay-btn" data-method="Card">
        <span class="left"><span class="pay-logo card"><i class="fas fa-credit-card"></i></span> Credit or debit card</span>
        <i class="fas fa-chevron-right"></i>
      </button>
      <button class="pay-btn" data-method="Account Credit">
        <span class="left"><span class="pay-logo credit"><i class="fas fa-wallet"></i></span> Account credit</span>
        <i class="fas fa-chevron-right"></i>
      </button>
      <button class="pay-btn" data-method="Cash">
        <span class="left"><span class="pay-logo cash"><i class="fas fa-coins"></i></span> Cash</span>
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</div>

<?php
$navPath = __DIR__ . '/partials/bottom-nav.php';
if (file_exists($navPath)) include $navPath;
$togglePath = __DIR__ . '/partials/theme-toggle.php';
if (file_exists($togglePath)) include $togglePath;
?>

<script>
"use strict";
(function(){
  const CART_KEY   = "cart_items";
  const STATE_KEY  = "checkout_details_v1";
  const DELIVERY_FEE = 2.99;

  const $ = (sel,root=document)=>root.querySelector(sel);
  const $$= (sel,root=document)=>Array.from(root.querySelectorAll(sel));
  const money = n => '£' + (Number(n||0)).toFixed(2);
  const esc   = s => (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[m]));

  // Robust JSON fetch
  const postJSON = async (url, data) => {
    const res = await fetch(url, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(data)
    });
    const text = await res.text();
    let json; try{ json = JSON.parse(text); }catch(_){ json = { error:'Invalid JSON from server', raw:text }; }
    if (!res.ok) {
      const msg = typeof json.error === 'string' ? json.error : (json.error?.message || 'Network error');
      throw new Error(msg);
    }
    return json;
  };

  function loadCart(){
    try{ return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); }
    catch(e){ return []; }
  }
  function saveState(st){ localStorage.setItem(STATE_KEY, JSON.stringify(st||{})); }
  function loadState(){ try{ return JSON.parse(localStorage.getItem(STATE_KEY)||'{}'); }catch(e){ return {}; } }

  const dom = {
    items:      $('#items'),
    sumSub:     $('#sumSub'),
    sumFee:     $('#sumFee'),
    sumTip:     $('#sumTip'),
    rowTip:     $('#rowTip'),
    sumDisc:    $('#sumDiscount'),
    rowDisc:    $('#rowDiscount'),
    sumTotal:   $('#sumTotal'),
    payBtn:     $('#payBtn'),

    name:       $('#name'),
    phone:      $('#phone'),

    addr1:      $('#address1'),
    addr2:      $('#address2'),
    city:       $('#city'),
    postcode:   $('#postcode'),

    addrHint:   $('#addrHint'),
    notes:      $('#notes'),

    shipDelivery:   $('#shipDelivery'),
    shipCollection: $('#shipCollection'),

    tipGrid:    $('#tipGrid'),
    tipOther:   $('#tipOther'),

    voucher:    $('#voucher'),
    applyV:     $('#applyVoucher'),
    voucherMsg: $('#voucherMsg'),

    openPay:    $('#openPay'),
    payLabel:   $('#payLabel'),
    payModal:   $('#payModal'),
    payClose:   $('#payClose'),
    payList:    $('#payList'),
  };

  // ---- ADD: prefer ?mode= from Basket, else persist/localStorage ----
  let deliveryMode = localStorage.getItem('order_mode') || 'delivery';

  // If basket sent ?mode=..., prefer it and persist for consistency
  let urlModeParam = null;
  try {
    const params = new URLSearchParams(location.search);
    const urlMode = params.get('mode');
    if (urlMode === 'collection' || urlMode === 'delivery') {
      urlModeParam = urlMode;
      deliveryMode = urlMode; // override current var immediately
      localStorage.setItem('order_mode', deliveryMode);
    }
  } catch(_) {}

  let tip = 0, discount = 0, voucherApplied = null, paymentMethod = 'Card';

  // Build a full address string from fields
  function fullAddressFromInputs(){
    return [dom.addr1?.value, dom.addr2?.value, dom.city?.value, dom.postcode?.value]
      .map(v => (v||'').trim()).filter(Boolean).join(', ');
  }

  // restore persisted fields
  (function restore(){
    const s = loadState();
    if (s){
      if (s.name)    dom.name.value = s.name;
      if (s.phone)   dom.phone.value = s.phone;

      if (s.address1) dom.addr1.value = s.address1;
      else if (s.address) dom.addr1.value = s.address;

      if (s.address2) dom.addr2.value = s.address2;
      if (s.city)     dom.city.value = s.city;
      if (s.postcode) dom.postcode.value = s.postcode;

      if (s.notes)   dom.notes.value = s.notes;
      if (s.tip)     tip = Number(s.tip)||0;
      if (s.discount)discount = Number(s.discount)||0;
      if (s.voucher) { voucherApplied = s.voucher; dom.voucher.value = s.voucher; }
      if (s.paymentMethod) paymentMethod = s.paymentMethod;

      // (was: deliveryMode = s.deliveryMode) — BUT we prefer URL param if present:
      if (urlModeParam) {
        deliveryMode = urlModeParam;
      } else if (s.deliveryMode) {
        deliveryMode = s.deliveryMode;
      }
    }

    // prefill saved account address if empty
    try{
      const a = JSON.parse(localStorage.getItem('user_address')||'null');
      const hasAny = (dom.addr1.value || dom.addr2.value || dom.city.value || dom.postcode.value);
      if (a && !hasAny){
        if (a.line1)   dom.addr1.value = a.line1;
        if (a.line2)   dom.addr2.value = a.line2;
        if (a.city)    dom.city.value = a.city;
        if (a.postcode)dom.postcode.value = a.postcode;
        if (dom.addrHint) dom.addrHint.textContent = 'Loaded from saved address';
      }
    }catch(e){}

    // reflect radio
    if (dom.shipCollection && dom.shipDelivery){
      dom.shipCollection.checked = (deliveryMode === 'collection');
      dom.shipDelivery.checked   = (deliveryMode !== 'collection');
    }
    // tip pill state (also set aria-pressed)
    if (tip > 0) {
      const pill = dom.tipGrid && dom.tipGrid.querySelector(`.tip[data-tip="${tip}"]`);
      if (pill) { pill.classList.add('active'); pill.setAttribute('aria-pressed','true'); }
      else if (dom.tipOther){ dom.tipOther.style.display=''; dom.tipOther.value = tip; }
    }
    // label
    if (dom.payLabel) dom.payLabel.textContent =
      (paymentMethod === 'Card') ? 'Credit or debit card' : paymentMethod;

    // ensure order_mode is persisted
    localStorage.setItem('order_mode', deliveryMode);
  })();

  function persist(){
    const addr1 = dom.addr1?.value.trim() || '';
    const addr2 = dom.addr2?.value.trim() || '';
    const city  = dom.city?.value.trim() || '';
    const pc    = dom.postcode?.value.trim() || '';
    const full  = [addr1, addr2, city, pc].filter(Boolean).join(', ');

    // keep order-tracking in sync
    localStorage.setItem('order_mode', deliveryMode);

    saveState({
      name: dom.name?.value.trim(),
      phone: dom.phone?.value.trim(),
      // store both parts and combined for tracking page compatibility
      address1: addr1,
      address2: addr2,
      city:     city,
      postcode: pc,
      address:  full,
      notes: dom.notes?.value.trim(),
      deliveryMode, tip, paymentMethod, voucher: voucherApplied, discount
    });
  }

  function render(){
    const cart = loadCart();
    if (dom.items){
      dom.items.innerHTML = '';
      if (!cart.length){
        dom.items.innerHTML = '<div class="item"><div><div class="name">Your basket is empty</div><div class="meta">Add items to continue</div></div><div>£0.00</div></div>';
      }else{
        cart.forEach(it=>{
          const el = document.createElement('div');
          el.className = 'item';
          const safeName = String(it?.name ?? '');
          const displayName = safeName.replace(/(\d+)\s*$/, '$1"');
          el.innerHTML = `
            <div>
              <div class="name">${esc(displayName)}</div>
              <div class="meta">${Number(it?.qty||1)} × £${(Number(it?.price||0)).toFixed(2)}</div>
            </div>
            <div><strong>£${(Number(it?.price||0) * Number(it?.qty||1)).toFixed(2)}</strong></div>
          `;
          dom.items.appendChild(el);
        });
      }
    }

    const sub = cart.reduce((a,b)=> a + (Number(b.price)||0)*(Number(b.qty)||1), 0);
    const fee = (deliveryMode === 'delivery' && cart.length) ? DELIVERY_FEE : 0;
    const tipVal = Number(tip||0);
    const disc   = Number(discount||0);
    const total  = Math.max(0, sub + fee + tipVal - disc);

    dom.sumSub.textContent   = money(sub);
    dom.sumFee.textContent   = money(fee);
    dom.sumTip.textContent   = money(tipVal);
    dom.rowTip.style.display = tipVal>0 ? '' : 'none';
    dom.sumDisc.textContent  = '−' + money(disc);
    dom.rowDisc.style.display= disc>0 ? '' : 'none';
    const btnLabel = (paymentMethod === 'Card' || paymentMethod === 'Apple Pay')
      ? `Pay by ${paymentMethod} (${money(total)})`
      : (paymentMethod === 'Account Credit'
          ? `Pay with Account Credit (${money(total)})`
          : `Pay by ${paymentMethod} (${money(total)})`);
    dom.payBtn.textContent   = btnLabel;
    dom.payBtn.disabled      = !cart.length;
  }

  // persist fields
  ['input','change'].forEach(ev=>{
    dom.name?.addEventListener(ev, persist);
    dom.phone?.addEventListener(ev, persist);
    dom.addr1?.addEventListener(ev, persist);
    dom.addr2?.addEventListener(ev, persist);
    dom.city?.addEventListener(ev, persist);
    dom.postcode?.addEventListener(ev, persist);
    dom.notes?.addEventListener(ev, persist);
  });

  dom.shipDelivery?.addEventListener('change', ()=>{
    if (dom.shipDelivery.checked){
      deliveryMode='delivery';
      persist(); render();
    }
  });
  dom.shipCollection?.addEventListener('change', ()=>{
    if (dom.shipCollection.checked){
      deliveryMode='collection';
      persist(); render();
    }
  });

  dom.tipGrid?.addEventListener('click', (e)=>{
    const btn = e.target.closest('.tip'); if(!btn) return;
    const val = btn.dataset.tip;
    $$('.tip', dom.tipGrid).forEach(b=>{ b.classList.remove('active'); b.setAttribute('aria-pressed','false'); });
    if (val === 'other'){
      dom.tipOther.style.display=''; dom.tipOther.focus(); tip = Number(dom.tipOther.value||0)||0;
    }else{
      dom.tipOther.style.display='none'; tip = Number(val)||0; btn.classList.add('active'); btn.setAttribute('aria-pressed','true');
    }
    persist(); render();
  });
  dom.tipOther?.addEventListener('input', ()=>{ tip = Math.max(0, parseFloat((dom.tipOther.value||'0').replace(',','.'))||0); persist(); render(); });

  function openPay(){ dom.payModal.classList.add('show'); dom.payModal.setAttribute('aria-hidden','false'); document.body.classList.add('sheet-open'); }
  function closePay(){ dom.payModal.classList.remove('show'); dom.payModal.setAttribute('aria-hidden','true'); document.body.classList.remove('sheet-open'); }
  dom.openPay?.addEventListener('click', openPay);
  dom.openPay?.addEventListener('keydown', e=>{ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); openPay(); } });
  dom.payClose?.addEventListener('click', closePay);
  dom.payModal?.addEventListener('click', (e)=>{ if(e.target===dom.payModal) closePay(); });
  dom.payList?.addEventListener('click', (e)=>{
    const btn = e.target.closest('.pay-btn'); if(!btn) return;
    const method = btn.dataset.method || 'Card';
    const map = { 'Card':'Credit or debit card', 'Apple Pay':'Apple Pay', 'Cash':'Cash', 'Account Credit':'Account credit' };
    paymentMethod = method;
    dom.payLabel.textContent = map[method] || method;
    persist(); render(); closePay();
  });

  // Start payment
  dom.payBtn?.addEventListener('click', async ()=>{
    const cart = loadCart();
    if (!cart.length){ alert('Your basket is empty.'); return; }

    const modeNow = (document.getElementById('shipCollection')?.checked) ? 'collection' : 'delivery';
    deliveryMode = modeNow; // sync with radios

    if (modeNow==='delivery'){
      if (!dom.addr1.value.trim() || !dom.postcode.value.trim()){
        alert('Please enter your delivery address (line 1 and postcode).');
        return;
      }
    }
    if (!dom.name.value.trim()){ alert('Please enter your name.'); return; }
    if (!dom.phone.value.trim()){ alert('Please enter your phone number.'); return; }

    // Save current details and timestamp so order-tracking can show ETA
    localStorage.setItem('order_time_iso', new Date().toISOString());
    localStorage.setItem('order_mode', deliveryMode);
    persist();

    const address = {
      line1: dom.addr1.value.trim(),
      line2: dom.addr2.value.trim(),
      city:  dom.city.value.trim(),
      postcode: dom.postcode.value.trim(),
      full: [dom.addr1.value, dom.addr2.value, dom.city.value, dom.postcode.value]
              .map(v => (v||'').trim()).filter(Boolean).join(', ')
    };

    const payload = {
      customer: {
        name: dom.name.value.trim(),
        phone: dom.phone.value.trim(),
        address,
        notes: dom.notes.value.trim(),
        mode: deliveryMode
      },
      cart: cart,
      fees: {
        delivery: (deliveryMode==='delivery' && cart.length) ? DELIVERY_FEE : 0,
        tip: Number(dom.sumTip.textContent.replace(/[£,]/g,'')||0),
        discount: 0
      },
      totals: {
        subtotal: Number(dom.sumSub.textContent.replace(/[£,]/g,'')) || 0,
        total:    Number(dom.sumTotal.textContent.replace(/[£,]/g,'')) || 0,
        currency: 'GBP'
      }
    };

    try{
      if (paymentMethod === 'Card' || paymentMethod === 'Apple Pay'){
        const data = await postJSON('stripe.php', { action:'create_checkout', payload });
        if (data && data.url) { window.location.href = data.url; return; }
        const errMsg = typeof data?.error === 'string' ? data.error : (data?.message || 'Could not start Stripe Checkout.');
        alert('Stripe: ' + errMsg);
        return;
      } else if (paymentMethod === 'Account Credit') {
        // NEW: Pay using stored account credit (server will deduct & return tracking redirect)
        const data = await postJSON('stripe.php', { action:'pay_with_credit', payload });
        if (data?.redirect) { window.location.href = data.redirect; return; }
        throw new Error(data?.error || 'Could not pay with account credit');
      } else if (paymentMethod === 'Cash'){
        // Save the order server-side, then go to tracking
        const data = await postJSON('stripe.php', { action:'record_cash', payload });
        if (data?.redirect){ window.location.href = data.redirect; return; }
        throw new Error(data?.error || 'Could not record cash order');
      } else {
        alert('Please select a payment method.');
      }
    }catch(err){
      alert(err.message || 'Payment failed to start.');
    }
  });

  window.addEventListener('focus', render);
  window.addEventListener('storage', (e)=>{ if (e.key === CART_KEY) render(); });

  render();

  // Optional: soft message if user returned with ?canceled=1 (Stripe cancel)
  try{
    const params = new URLSearchParams(location.search);
    if (params.get('canceled') === '1') {
      const note = document.createElement('div');
      note.className = 'card';
      note.innerHTML = '<div style="color:#b91c1c;font-weight:800;">Payment canceled</div><div style="color:#6b7280;margin-top:4px;">You can try again or choose a different method.</div>';
      document.querySelector('.page').insertBefore(note, document.querySelector('.page').firstChild.nextSibling);
    }
  }catch(e){}
})();
</script>

<style>
  #payBtn.btn{
    background:#f04f32 !important; border-color:#f04f32 !important; color:#fff !important;
  }
  [data-theme="dark"] #payBtn.btn{
    background:#f04f32 !important; border-color:#f04f32 !important; color:#fff !important;
  }
  #payBtn.btn:hover{ filter:brightness(0.95); }
  #payBtn.btn:active{ transform:translateY(1px); }
</style>

</body>
</html>
