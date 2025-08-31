<?php
// File: partials/basket.php
// Drop-in Floating Basket + Bottom-Sheet (Just Eat style)
// Include this file near the end of any page (before bottom-nav or right before </body>).
// Requirements: Font Awesome already loaded; your theme-head.php defines CSS vars: --bg, --text, --muted, --line, --card, --primary

// Prevent duplicate output if included twice
if (!defined('BASKET_COMPONENT_LOADED')) {
  define('BASKET_COMPONENT_LOADED', true);
}
?>
<?php if (BASKET_COMPONENT_LOADED): ?>
<style>
/* Height reserved for the bottom nav (edit this one line if you want more/less lift) */
:root { --nav-h: 56px; } /* or: calc(56px + env(safe-area-inset-bottom)) */

/* ===== Floating Basket FAB ===== */
.cart-fab{
  position:fixed;
  left:14px;
  bottom:calc(96px + env(safe-area-inset-bottom));
  width:52px; height:52px; border-radius:50%;
  display:none; align-items:center; justify-content:center;
  background:var(--primary, #f04f32);
  color:#fff;
  border:none;
  box-shadow:0 2px 12px rgba(0,0,0,.2);
  z-index:10001; text-decoration:none; cursor:pointer;
}
.cart-fab i{ font-size:20px; }

:root{ --basket-badge-bg:#e11d48; }
.cart-fab .badge{
  position:absolute; top:-6px; right:-6px;
  min-width:20px; height:20px; padding:0 6px;
  border-radius:999px; background:var(--basket-badge-bg); color:#fff;
  font-size:12px; line-height:20px; text-align:center;
  border:1px solid rgba(0,0,0,.15);
}
[data-theme="dark"] .cart-fab .badge{ background:var(--basket-badge-bg); color:#fff; }

.cart-fab.show{ display:inline-flex; }

/* ===== Basket Bottom Sheet ===== */
.basket-backdrop{
  position:fixed; left:0; right:0; top:0; bottom:var(--nav-h);
  background:rgba(0,0,0,.35);
  opacity:0; pointer-events:none; z-index:10009; transition:opacity .18s ease;
}
.basket-backdrop.show{ opacity:1; pointer-events:auto; }

.basket-sheet{
  position:fixed; left:0; right:0; bottom:var(--nav-h);
  background:var(--card, #fff); color:var(--text, #111);
  border-top:1px solid var(--line, #e5e7eb);
  border-top-left-radius:16px; border-top-right-radius:16px;
  box-shadow:0 -10px 30px rgba(0,0,0,.2);
  transform:translateY(100%); transition:transform .22s ease;
  z-index:10010; max-height:85svh; display:flex; flex-direction:column;
  padding-bottom:calc(12px + env(safe-area-inset-bottom));
  overscroll-behavior: contain; /* keep scroll from escaping to the page */
}
.basket-sheet.show{ transform:translateY(0); }

.basket-head{
  display:flex; align-items:center; justify-content:space-between;
  padding:14px 16px; border-bottom:1px solid var(--line, #e5e7eb);
}
.basket-title{ font-size:18px; font-weight:800; }
.basket-close{
  border:1px solid var(--line,#e5e7eb); background:var(--bg,#f9f9f9);
  padding:8px 10px; border-radius:10px; color:var(--text,#111); cursor:pointer;
}

/* Delivery / Collection split bar */
.mode-switch{
  margin:12px 16px 8px;
  display:grid; grid-template-columns:1fr 1fr;
  border:1px solid var(--line,#e5e7eb); border-radius:12px; overflow:hidden;
  background:var(--bg,#f7f7f7);
}
.mode{
  display:flex; align-items:center; gap:10px;
  padding:10px 12px; background:transparent; border:none; cursor:pointer;
  justify-content:center; color:var(--text,#111);
}
.mode > div{
  display:flex;
  flex-direction:column;
  align-items:flex-start;
  min-width:0;         /* allow flex to shrink text block instead of overflowing */
  flex:1;              /* take available space so text doesn't get squeezed away */
  overflow:hidden;     /* pair with ellipsis below to avoid visual overflow */
}
.mode + .mode{ border-left:1px solid var(--line,#e5e7eb); }
.mode i{ font-size:16px; }

/* Ensure titles/subtitles are visible across themes and don't vanish */
.m-title{ 
  font-weight:800; 
  line-height:1; 
  color:var(--text,#111);
  white-space:nowrap; 
  text-overflow:ellipsis; 
  overflow:hidden;
}
.m-sub{ 
  font-size:12px; 
  color:var(--muted,#666); 
  margin-top:2px; 
  white-space:nowrap; 
  text-overflow:ellipsis; 
  overflow:hidden;
}
[data-theme="dark"] .m-title{ color:#ffffff; }
[data-theme="dark"] .m-sub{ color:#a6adbb; }

.mode.active{
  background:var(--card,#fff);
  font-weight:800;
  box-shadow:inset 0 0 0 2px var(--primary,#f04f32);
}

/* Items */
.basket-body{
  padding:4px 16px 8px; overflow:auto;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior: contain;
}
.empty-basket{ color:var(--muted,#666); text-align:center; padding:14px 8px; }
.bi{
  display:grid; grid-template-columns:1fr auto auto;
  gap:10px; align-items:center;
  padding:10px 0; border-bottom:1px solid var(--line,#e5e7eb);
}
.bi-name{ font-weight:700; }
.bi-each{ font-size:12px; color:var(--muted,#666); }
.bi-remove{
  border:none; background:transparent; color:var(--muted,#666);
  font-size:16px; cursor:pointer;
}

/* Qty control */
.qty{ display:flex; align-items:center; gap:6px; }
.qty button {
  width:32px;
  height:32px;
  border-radius:8px;
  border:1px solid var(--line,#e5e7eb);
  background:var(--card,#fff);
  cursor:pointer;
  font-size:18px;
  color:var(--text,#111);
  display:flex;
  align-items:center;
  justify-content:center;
  line-height:1; /* prevent extra vertical offset */
}
.qty input{
  width:46px; text-align:center; border:1px solid var(--line,#e5e7eb);
  border-radius:8px; padding:6px 8px; background:#fff; color:#111;
}
[data-theme="dark"] .qty input{ background:#1b222b; color:var(--text); }

/* Summary + Checkout (sticky docked) */
.basket-summary{
  margin-top:auto; padding:10px 16px 0; border-top:1px solid var(--line,#e5e7eb);
  position:sticky; bottom:0; background:var(--card,#fff);
  box-shadow:0 -6px 16px rgba(0,0,0,.06);
}
.bsum-row{
  display:flex; justify-content:space-between; align-items:center;
  padding:6px 0; color:var(--muted,#666);
}
.bsum-row.total{
  color:var(--text,#111); font-weight:800; border-top:1px dashed var(--line,#e5e7eb);
  margin-top:6px; padding-top:10px;
}
.checkout-btn{
  width:100%; margin-top:12px; padding:12px 14px; border-radius:10px;
  background:var(--primary,#f04f32); color:#fff; font-weight:800; font-size:16px;
  border:none; cursor:pointer;
}
/* Lift basket a bit higher than the nav */
.basket-backdrop{ bottom:70px !important; }
.basket-sheet{ bottom:70px !important; }

/* Lock the page when the basket sheet is open */
html.no-scroll, body.no-scroll{ overflow:hidden; height:100%; }
</style>

<!-- Floating Basket FAB -->
<a href="#" class="cart-fab" id="cartFab" aria-label="Open basket">
  <i class="fas fa-shopping-basket"></i>
  <span class="badge">0</span>
</a>

<!-- Basket Backdrop + Sheet -->
<div class="basket-backdrop" id="basketBackdrop"></div>
<aside class="basket-sheet" id="basketSheet" aria-hidden="true" role="dialog" aria-labelledby="basketTitle">
  <div class="basket-head">
    <div class="basket-title" id="basketTitle">Basket</div>
    <button class="basket-close" id="basketClose" type="button"><i class="fas fa-times"></i></button>
  </div>

  <!-- Delivery / Collection bar -->
  <div class="mode-switch" id="modeSwitch">
    <button class="mode active" data-mode="delivery" type="button">
      <i class="fas fa-truck"></i>
      <div>
        <div class="m-title">Delivery</div>
        <div class="m-sub">30–45 min</div>
      </div>
    </button>
    <button class="mode" data-mode="collection" type="button">
      <i class="fas fa-shopping-bag"></i>
      <div>
        <div class="m-title">Collection</div>
        <div class="m-sub">(available)</div>
      </div>
    </button>
  </div>

  <!-- Items -->
  <div class="basket-body" id="basketItems">
    <div class="empty-basket">Your basket is empty.</div>
  </div>

  <!-- Summary -->
  <div class="basket-summary">
    <div class="bsum-row"><span>Subtotal</span><span id="basketSubtotal">£0.00</span></div>
    <div class="bsum-row"><span>Delivery Fee</span><span id="basketDelivery">£2.99</span></div>
    <div class="bsum-row total"><span>Total</span><span id="basketTotal">£2.99</span></div>
    <button class="checkout-btn" id="checkoutBtn" type="button">Checkout (£0.00)</button>
  </div>
</aside>

<script>
/* Guard against double init if included twice */
if(!window.__BASKET_SHEET_READY__){
window.__BASKET_SHEET_READY__ = true;

(function(){
  const FAB = document.getElementById('cartFab');
  const SHEET = document.getElementById('basketSheet');
  const BACKDROP = document.getElementById('basketBackdrop');
  const CLOSE = document.getElementById('basketClose');
  const ITEMS = document.getElementById('basketItems');
  const SUBTOTAL = document.getElementById('basketSubtotal');
  const DELIVERY = document.getElementById('basketDelivery');
  const TOTAL = document.getElementById('basketTotal');
  const CHECKOUT = document.getElementById('checkoutBtn');
  const MODE_WRAP = document.getElementById('modeSwitch');
  const BADGE = FAB ? FAB.querySelector('.badge') : null;

  const DELIVERY_FEE = 2.99;

  function money(n){ return '£' + (Number(n||0)).toFixed(2); }
  function keyFor(it){ return (it.name + '|' + it.price).toLowerCase().replace(/\s+/g,'_'); }
  function escapeHtml(s){
    return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  function loadCart(){
    try{ return JSON.parse(localStorage.getItem('cart_items')||'[]'); }catch(e){ return []; }
  }
  function saveCart(arr){
    localStorage.setItem('cart_items', JSON.stringify(arr));
    updateBadge(arr);
  }
  function updateBadge(arr){
    const count = (arr||loadCart()).reduce((a,b)=>a + (b.qty||0), 0);
    if (!BADGE) return;
    BADGE.textContent = count;
    FAB.classList.toggle('show', count>0);
  }

  function currentMode(){ return localStorage.getItem('order_mode') || 'delivery'; }
  function setMode(m){
    localStorage.setItem('order_mode', m);
    MODE_WRAP.querySelectorAll('.mode').forEach(btn=>{
      btn.classList.toggle('active', btn.dataset.mode === m);
    });
    render();
  }

  function addItem(name, price, qty=1){
    if(!name || isNaN(price)) return;
    let cart = loadCart();
    const k = keyFor({name,price});
    const idx = cart.findIndex(i => keyFor(i) === k);
    if (idx >= 0) cart[idx].qty += qty;
    else cart.push({name, price:Number(price), qty: qty});
    saveCart(cart);
    render();
  }
  function setQty(k, n){
    let cart = loadCart();
    const idx = cart.findIndex(i => keyFor(i) === k);
    if (idx<0) return;
    cart[idx].qty = Math.max(1, n|0);
    saveCart(cart); render();
  }
  function removeItem(k){
    let cart = loadCart().filter(i => keyFor(i) !== k);
    saveCart(cart); render();
  }

  function render(){
    const cart = loadCart();
    updateBadge(cart);

    if (!cart.length){
      ITEMS.innerHTML = '<div class="empty-basket">Your basket is empty.</div>';
    }else{
      ITEMS.innerHTML = cart.map(it=>{
        const k = keyFor(it);
        return `
          <div class="bi" data-k="${k}">
            <div>
              <div class="bi-name">${escapeHtml(it.name)}</div>
              <div class="bi-each">${money(it.price)} each</div>
            </div>
            <div class="qty">
              <button class="qminus" aria-label="Decrease">−</button>
              <input class="qval" type="number" min="1" value="${it.qty}">
              <button class="qplus" aria-label="Increase">+</button>
            </div>
            <button class="bi-remove" aria-label="Remove"><i class="fas fa-times"></i></button>
          </div>
        `;
      }).join('');
    }

    const sub = cart.reduce((a,b)=> a + b.price * b.qty, 0);
    const mode = currentMode();
    const fee = (mode === 'delivery' && cart.length) ? DELIVERY_FEE : 0;
    const tot = sub + fee;

    SUBTOTAL.textContent = money(sub);
    DELIVERY.textContent = money(fee);
    TOTAL.textContent = money(tot);
    CHECKOUT.textContent = `Checkout (${money(tot)})`;
    CHECKOUT.disabled = !cart.length;
  }

  // Re-render when opening the basket
  function openSheet(e){
    e && e.preventDefault();
    render(); // ensure UI reflects latest localStorage state
    BACKDROP.classList.add('show');
    SHEET.classList.add('show');
    SHEET.setAttribute('aria-hidden','false');
    // Lock background scroll
    document.documentElement.classList.add('no-scroll');
    document.body.classList.add('no-scroll');
  }
  function closeSheet(){
    BACKDROP.classList.remove('show');
    SHEET.classList.remove('show');
    SHEET.setAttribute('aria-hidden','true');
    // Unlock background scroll
    document.documentElement.classList.remove('no-scroll');
    document.body.classList.remove('no-scroll');
  }

  // Hook FAB / backdrop / close
  FAB && FAB.addEventListener('click', openSheet);
  BACKDROP.addEventListener('click', closeSheet);
  CLOSE.addEventListener('click', closeSheet);

  // Hook mode switch
  MODE_WRAP.addEventListener('click', (e)=>{
    const btn = e.target.closest('.mode');
    if(!btn) return;
    setMode(btn.dataset.mode);
  });

  // Hook qty and remove inside sheet
  ITEMS.addEventListener('click', (e)=>{
    const row = e.target.closest('.bi'); if(!row) return;
    const k = row.dataset.k;
    if (e.target.closest('.qminus')) setQty(k, Number(row.querySelector('.qval').value)-1);
    if (e.target.closest('.qplus'))  setQty(k, Number(row.querySelector('.qval').value)+1);
    if (e.target.closest('.bi-remove')) removeItem(k);
  });
  ITEMS.addEventListener('change', (e)=>{
    const inp = e.target.closest('.qval'); if(!inp) return;
    const row = e.target.closest('.bi'); const k = row.dataset.k;
    setQty(k, Number(inp.value));
  });

  // Listen for cart updates dispatched by options.php
  document.addEventListener('cart:updated', render);

  // Hook direct "add-to-cart" buttons — IGNORE options opener
  document.addEventListener('click', (e)=>{
    // Let options opener handle itself
    if (e.target.closest('[data-open-options]')) return;

    // Only true direct-add controls
    const btn = e.target.closest('[data-add-to-cart], .add-to-cart, [data-name][data-price]');
    if (!btn) return;

    const addBtn = btn.closest('button, a, [data-add-to-cart]');
    e.preventDefault();

    let name  = addBtn.getAttribute?.('data-name')?.trim() || '';
    let price = addBtn.hasAttribute?.('data-price') ? parseFloat(addBtn.getAttribute('data-price')) : NaN;
    let sizeLabel = '';

    function findPriceAndLabel(scope){
      if(!scope || !scope.querySelectorAll) return {};
      const nodes = scope.querySelectorAll('span, strong, b, .price, [data-price]');
      for (const n of nodes){
        const t = (n.textContent || '').trim();
        const m = t.match(/£\s*([\d.,]+)/);
        if (m){
          const p = parseFloat(m[1].replace(',', ''));
          const before = t.split('£')[0].trim();
          return { price: p, label: before };
        }
      }
      return {};
    }
    function findWrapper(el){
      let node = el;
      for(let i=0;i<8 && node;i++){
        if (node.querySelector && (node.querySelector('h4') || /£/.test(node.textContent))) return node;
        node = node.parentElement;
      }
      return el.parentElement || el;
    }

    if (isNaN(price)){
      let scope = addBtn;
      for (let hops=0; hops<3 && isNaN(price) && scope; hops++, scope = scope.parentElement){
        const found = findPriceAndLabel(scope);
        if (typeof found.price === 'number') { price = found.price; sizeLabel = found.label || sizeLabel; break; }
      }
    }
    const wrapper = findWrapper(addBtn);
    if (!name){
      const h4 = wrapper.querySelector('h4');
      if (h4) name = h4.textContent.trim();
    }
    if (isNaN(price)){
      const found = findPriceAndLabel(wrapper);
      if (typeof found.price === 'number'){ price = found.price; sizeLabel = found.label || sizeLabel; }
    }
    if (name && sizeLabel && !name.includes(sizeLabel)) name = `${name} ${sizeLabel}`;

    if (!name || isNaN(price)) return;
    addItem(name, price, 1);
  });

  // >>> UPDATED: carry delivery/collection choice into checkout via URL and persist it
  CHECKOUT.addEventListener('click', ()=>{
    const mode = localStorage.getItem('order_mode') || 'delivery';
    const url  = new URL('/websites/shop/checkout.php', location.origin);
    url.searchParams.set('mode', (mode === 'collection') ? 'collection' : 'delivery');
    localStorage.setItem('order_mode', mode);
    window.location.href = url.toString();
  });

  // Init
  setMode(currentMode());
  render();
})();
}
</script>
<?php endif; ?>

