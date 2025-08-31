<?php
// OPTIONS.PHP — REUSABLE “OPTIONS” BOTTOM SHEET (SIZES/EXTRAS/QTY) FOR MENU ITEMS
// INCLUDE ONCE ON PAGES THAT NEED IT (E.G., INDEX.PHP). IT EXPOSES window.openOptions({ name, type })
if (!defined('OPTIONS_SHEET_LOADED')) define('OPTIONS_SHEET_LOADED', true);
if (!OPTIONS_SHEET_LOADED) return;
?>
<style>
/* ===== OPTIONS BOTTOM SHEET (STYLING MIRRORS BASKET) ===== */

/* Height of your bottom nav so the sheet/backdrop stop above it */
:root { --nav-h: 56px; }

.OPTS-BACKDROP{
  position:fixed; left:0; right:0; top:0; bottom:var(--nav-h);
  background:rgba(0,0,0,.35);
  opacity:0; pointer-events:none; z-index:10008; transition:opacity .18s ease;
}
.OPTS-BACKDROP.show{ opacity:1; pointer-events:auto; }

.OPTS-SHEET{
  position:fixed; left:0; right:0; bottom:var(--nav-h);
  background:var(--card,#fff); color:var(--text,#111);
  border-top:1px solid var(--line,#e5e7eb);
  border-top-left-radius:16px; border-top-right-radius:16px;
  box-shadow:0 -10px 30px rgba(0,0,0,.2);
  transform:translateY(100%); transition:transform .22s ease;
  z-index:10011; max-height:86svh; display:flex; flex-direction:column;
  padding-bottom:calc(12px + env(safe-area-inset-bottom));
  overscroll-behavior: contain; /* prevent background bounce */
}
.OPTS-SHEET.show{ transform:translateY(0); }

.OPTS-HEAD{
  display:flex; align-items:center; justify-content:space-between;
  padding:14px 16px; border-bottom:1px solid var(--line,#e5e7eb);
}
.OPTS-TITLE{ font-size:18px; font-weight:800; }
.OPTS-CLOSE{
  border:1px solid var(--line,#e5e7eb); background:var(--bg,#f9f9f9);
  padding:8px 10px; border-radius:10px; color:var(--text,#111); cursor:pointer;
}

.OPTS-BODY{
  padding:12px 16px; overflow:auto; display:flex; flex-direction:column; gap:12px;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior: contain; /* stop scrolling from escaping */
}

.GROUP{ background:var(--card); border:1px solid var(--line,#e5e7eb); border-radius:12px; }
.GROUP h4{ margin:0; padding:12px 12px 8px; font-size:15px; font-weight:800; }
.OPT-ROW{
  display:flex; align-items:center; justify-content:space-between; gap:12px;
  padding:10px 12px; border-top:1px solid var(--line,#e5e7eb);
}
.OPT-ROW:first-of-type{ border-top:none; }
.OPT-LEFT{ display:flex; align-items:center; gap:10px; }
.OPT-LEFT small{ display:block; color:var(--muted,#666); font-size:12px; }
.PRICE{ font-weight:800; }

.QTYBAR{ display:flex; align-items:center; justify-content:space-between; gap:12px; padding:0 2px; }
.QTY{ display:flex; align-items:center; gap:8px; }
.QTY button {
  width:36px; height:36px; border-radius:10px; border:1px solid var(--line,#e5e7eb);
  background:var(--card,#fff); font-size:18px; cursor:pointer;
  display:flex; align-items:center; justify-content:center; line-height:1;
}
.QTY input{
  width:52px; text-align:center; border:1px solid var(--line,#e5e7eb); border-radius:10px; padding:8px;
}

/* Dock the Add button to the bottom edge of the scrollable sheet content */
.ADD-BTN{
  position: sticky; bottom: 0;
  margin:10px 0 0; padding:12px 14px; border-radius:10px; width:100%;
  background:var(--primary,#f04f32); color:#fff; border:none; font-weight:900; font-size:16px; cursor:pointer;
  box-shadow:0 6px 16px rgba(0,0,0,.08);
}
.HELP{ font-size:12px; color:var(--muted,#666); padding:0 2px 6px; }

/* Lock the page when the options sheet is open */
html.no-scroll, body.no-scroll{ overflow:hidden; height:100%; }

/* DARK TWEAKS */
[data-theme="dark"] .OPTS-CLOSE{ background:#1b222b; border-color:#27303a; color:var(--text); }
[data-theme="dark"] .GROUP{ background:#151a1f; border-color:#27303a; }
[data-theme="dark"] .QTY input{ background:#1b222b; color:var(--text); border-color:#27303a; }
</style>

<div id="OPTSBACKDROP" class="OPTS-BACKDROP" aria-hidden="true"></div>
<aside id="OPTSSHEET" class="OPTS-SHEET" role="dialog" aria-labelledby="OPTSTITLE" aria-hidden="true">
  <div class="OPTS-HEAD">
    <div id="OPTSTITLE" class="OPTS-TITLE">Choose options</div>
    <button id="OPTSCLOSE" class="OPTS-CLOSE" type="button"><i class="fas fa-times"></i></button>
  </div>
  <div id="OPTSBODY" class="OPTS-BODY">
    <!-- DYNAMIC CONTENT -->
  </div>
</aside>

<script>
(function(){
  // ===== REGISTRY OF ITEMS & OPTIONS (SIZES + DEFAULT EXTRAS) =====
  // PRICES MIRROR YOUR index.php LISTS
  // Use lowercase type keys to match data-type="pizza"/"special" from index.php
  const REGISTRY = {
    pizza: {
      "Margherita Pizza": [["10\"",6.50],["12\"",7.50],["14\"",9.00],["16\"",13.00]],
      "Garlic Margherita Pizza": [["10\"",7.00],["12\"",8.50],["14\"",9.00],["16\"",13.00]],
      "Caribbean Pizza": [["10\"",7.00],["12\"",10.00],["14\"",11.50],["16\"",12.50]],
      "Al Funghi Pizza": [["10\"",8.50],["12\"",10.00],["14\"",12.50],["16\"",14.50]],
      "Pepperoni Pizza": [["10\"",9.50],["12\"",11.50],["14\"",14.00],["16\"",16.50]],
      "Bolognese Pizza": [["10\"",9.50],["12\"",11.50],["14\"",14.00],["16\"",16.50]],
      "Spicy Beef Pizza": [["10\"",9.50],["12\"",10.00],["14\"",14.00],["16\"",16.50]],
      "Tuna Delight Pizza": [["10\"",9.00],["12\"",11.00],["14\"",13.50],["16\"",15.50]],
      "Pollo Pizza": [["10\"",9.50],["12\"",12.50],["14\"",14.50],["16\"",16.50]],
      "Hot Pollo Pizza": [["10\"",9.50],["12\"",12.50],["14\"",14.50],["16\"",16.50]],
      "Vegetarian Pizza": [["10\"",9.00],["12\"",11.00],["14\"",14.50],["16\"",16.50]],
      "Donner Pizza": [["10\"",9.50],["12\"",12.50],["14\"",14.50],["16\"",16.50]],
      "Pollo Funghi Pizza": [["10\"",9.50],["12\"",13.50],["14\"",14.50],["16\"",16.50]],
      "Seafood Pizza": [["10\"",9.50],["12\"",11.00],["14\"",14.50],["16\"",16.50]],
      "Toscana Pizza": [["10\"",9.50],["12\"",12.00],["14\"",14.50],["16\"",16.50]],
      "Hawaiian Pizza": [["10\"",9.00],["12\"",11.00],["14\"",13.00],["16\"",15.00]]
    },
    special: {
      'Quattro Stagioni Pizza': [['10"',9.00],['12"',12.00],['14"',14.00],['16"',16.50]],
      'Garlic Chilli Chicken Tikka': [['10"',10.00],['12"',12.00],['14"',14.50],['16"',16.50]],
      'Chicken Korma Pizza': [['10"',10.00],['12"',12.50],['14"',14.50],['16"',16.50]],
      "Big Daddy's Pizza": [['10"',10.00],['12"',12.50],['14"',14.50],['16"',16.50]],
      'Barbecue Chicken Bite Pizza': [['10"',9.50],['12"',11.50],['14"',14.00],['16"',16.50]],
      'Hot Shot Pizza': [['10"',9.50],['12"',12.50],['14"',14.50],['16"',16.50]],
      'Sweet & Sour Pizza': [['10"',9.50],['12"',12.00],['14"',14.00],['16"',16.50]],
      'Magic Combination Pizza': [['10"',9.50],['12"',12.00],['14"',14.00],['16"',16.50]],
      'Tandoori Pizza': [['10"',10.00],['12"',12.50],['14"',14.50],['16"',16.50]],
      'Chicken Tikka & Donner Meat': [['10"',10.00],['12"',12.00],['14"',15.00],['16"',17.50]],
      "Chef's Special Pizza": [['10"',10.50],['12"',13.50],['14"',14.50],['16"',16.50]],
      'Chicago Bear Pizza': [['10"',10.50],['12"',13.50],['14"',15.00],['16"',18.00]],
      'Super Asian Style Pizza': [['10"',10.00],['12"',12.50],['14"',15.00],['16"',17.00]],
      'Chicken Lover Pizza': [['10"',9.50],['12"',13.00],['14"',15.50],['16"',17.50]],
      'Crazy One Pizza': [['10"',10.00],['12"',13.50],['14"',16.00],['16"',17.00]],
      'Meat Feast Pizza': [['10"',10.00],['12"',13.00],['14"',15.00],['16"',17.00]],
      'Mixed Grill Pizza': [['10"',10.00],['12"',13.00],['14"',15.00],['16"',16.50]],
      'Mighty Mac Pizza': [['10"',10.00],['12"',12.50],['14"',13.50],['16"',16.50]],
      'Return Of Rocky Pizza': [['10"',10.00],['12"',12.00],['14"',14.50],['16"',16.50]],
      'Half Way Pizza': [['10"',10.00],['12"',13.50],['14"',15.50],['16"',16.50]],
      'BBQ Special Pizza': [['10"',10.50],['12"',13.50],['14"',15.50],['16"',16.50]],
      'Asian Special Pizza': [['10"',10.50],['12"',13.00],['14"',15.50],['16"',16.50]],
      '1/2 & 1/2 Pizza': [['10"',11.00],['12"',13.00],['14"',15.00],['16"',16.50]],
      'Special Mixed Kebab Pizza': [['10"',11.50],['12"',13.50],['14"',15.00],['16"',17.50]]
    },
    // >>> NEW: Garlic Bread sizes so data-type="garlicbread" works
    garlicbread: {
      'Garlic Bread Cheese': [['10"',6.00],['12"',7.00]],
      'Garlic Bread Pomodoro': [['10"',6.00],['12"',7.00]],
      'Garlic Bread Supreme': [['10"',6.00],['12"',7.50]],
      'Garlic Bread Special': [['10"',7.00],['12"',8.00]]
    },
        // >>> NEW: Calzone sizes so data-type="calzone" works
    calzone: {
      'Calzone Kiev': [['12"',10.00],['14"',12.00]],
      'Calzone Della': [['12"',10.00],['14"',12.50]],
      'Calzone Mushroom': [['12"',10.00],['14"',12.50]],
      'Calzone Bbq Pollo': [['12"',10.00],['14"',12.50]],
      'Hot Calzone': [['12"',10.00],['14"',12.50]],
      'Calzone Vegetarian': [['12"',10.00],['14"',12.50]],
      'Lash Gosht': [['12"',10.00],['14"',12.50]],
      'House Special Calzone': [['12"',10.00],['14"',14.00]]
    },    // >>> NEW: House Special sizes so data-type="housespecial" works
    housespecial: {
      'Piri Piri Chicken': [['Half', 8.00], ['Full', 14.00]],
      'Bbq Chicken':       [['Half', 8.00], ['Full', 14.00]]
    },
    // >>> NEW: Donner Kebabs sizes so data-type="donnerkebabs" works
    donnerkebabs: {
      'Donner Kebab': [
        ['Regular', 5.50],
        ['Large', 7.00]
      ],
      'Donner Meat & Chips': [
        ['Regular', 5.50],
        ['Large', 7.00]
      ],
      'Donner Meat In Tray': [
        ['Regular', 6.00],
        ['Large', 7.50]
      ]
    },
        // >>> NEW: Burger Bar sizes so data-type="burgerbar" works
    burgerbar: {
      'Plain Burger':          [['1/4lb', 5.00], ['1/2lb', 6.50]],
      'Cheese Burger':         [['1/4lb', 6.00], ['1/2lb', 7.50]],
      'Vegi Burger':           [['1/4lb', 4.50], ['1/2lb', 5.50]],
      'American Burger':       [['1/4lb', 6.00], ['1/2lb', 7.50]],
      'Hawaiian Burger':       [['1/4lb', 6.50], ['1/2lb', 7.50]],
      'Supreme Burger':        [['1/4lb', 6.50], ['1/2lb', 7.50]],
      'Chicken Fillet Burger': [['1/4lb', 6.00], ['1/2lb', 7.50]]
    },
        // >>> NEW: Special Burgers sizes so data-type="specialburgers" works
    specialburgers: {
      'Hot Bbq Wafer':             [['1/4lb', 6.00], ['1/2lb', 7.50]],
      'Garlic Mushroom Burger':    [['1/4lb', 6.00], ['1/2lb', 7.50]],
      'Chicken Tikka Burger':      [['1/4lb', 6.00], ['1/2lb', 7.50]],
      'Egg American Burger':       [['1/4lb', 6.00], ['1/2lb', 7.50]],
      'Chicken Supreme Burger':    [['1/4lb', 6.00], ['1/2lb', 7.50]],
      'King Chicken Burger':       [['1/4lb', 6.00], ['1/2lb', 7.50]],
      'Mexican Burger':            [['1/4lb', 5.50], ['1/2lb', 6.50]],
      'Donner Burger':             [['1/4lb', 5.50], ['1/2lb', 6.50]],
      'Spicy Donner Burger':       [['1/4lb', 6.00], ['1/2lb', 7.00]],
      'Spicy Chicken Grill Burger':[['1/4lb', 6.00], ['1/2lb', 7.00]],
      'Pepperoni Burger':          [['1/4lb', 6.00], ['1/2lb', 7.00]],
      'Chicken Mexicano':          [['1/4lb', 8.00], ['1/2lb', 8.50]]
      // Note: "Mega Munch" and "Chiraag Special Burger" are single-price and don't go here
    },
        // >>> NEW: New Grilled sizes so data-type="newgrilled" works
    newgrilled: {
      'Bbq Chicken':   [['1/4lb Meal', 7.00], ['1/2lb Meal', 8.00]],
      'Fillet Burger': [['1/4lb Meal', 7.00], ['1/2lb Meal', 8.00]],
      'Peri Peri':     [['1/4lb Meal', 7.00], ['1/2lb Meal', 8.00]],
      'Chicken Fillet':[['1/4lb Meal', 7.00], ['1/2lb Meal', 8.00]]
    },
        // >>> NEW: Extras sizes so data-type="extras" works (for Chips)
    extras: {
      'Chips': [
        ['Regular', 2.00],
        ['Large', 3.50]
      ]
    }






  };

  // DEFAULT EXTRAS ACROSS PIZZAS (title case for display)
  const DEFAULT_EXTRAS = [
    { name:'Extra cheese', price:1.00 },
    { name:'Garlic sauce', price:0.50 },
    { name:'Chilli sauce', price:0.50 },
    { name:'Tandoori chicken', price:1.50 },
    { name:'Pepperoni', price:1.20 }
  ];

  // ===== ELEMENTS =====
  const BACKDROP = document.getElementById('OPTSBACKDROP');
  const SHEET    = document.getElementById('OPTSSHEET');
  const CLOSEBTN = document.getElementById('OPTSCLOSE');
  const BODY     = document.getElementById('OPTSBODY');
  const TITLEEL  = document.getElementById('OPTSTITLE');

  // Helper: case-insensitive lookup for item names
  function getSizes(type, name){
    const t = (type || '').toLowerCase();
    const n = (name || '').toLowerCase();
    const byType = REGISTRY[t];
    if (!byType) return null;
    // Find key ignoring case to avoid exact-casing issues
    for (const key in byType){
      if (key.toLowerCase() === n) return byType[key];
    }
    return null;
  }

  // ===== PUBLIC OPENER =====
  window.openOptions = function({ name, type = 'pizza' }){
    const sizes = getSizes(type, name);
    TITLEEL.textContent = name || '';
    BODY.innerHTML = renderContent(name || '', sizes, DEFAULT_EXTRAS);
    openSheet();
    wireUp(name || '', sizes, DEFAULT_EXTRAS);
  };

  function openSheet(){
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
  CLOSEBTN.addEventListener('click', closeSheet);
  BACKDROP.addEventListener('click', (e)=>{ if(e.target===BACKDROP) closeSheet(); });

  // ===== RENDER CONTENT =====
  function renderContent(name, sizes, extras){
    const sizeRows = sizes ? sizes.map(([label, price], i) => `
      <label class="OPT-ROW">
        <span class="OPT-LEFT">
          <input type="radio" name="OPT-SIZE" value="${label}" data-price="${price}" ${i===0?'checked':''}>
          <span><strong>${label}</strong></span>
        </span>
        <span class="PRICE">£${price.toFixed(2)}</span>
      </label>
    `).join('') : '<div class="OPT-ROW">No sizes configured</div>';

    const extraRows = extras.map(ex => `
      <label class="OPT-ROW">
        <span class="OPT-LEFT">
          <input type="checkbox" name="OPT-EXTRA" value="${ex.name}" data-price="${ex.price}">
          <span><strong>${ex.name}</strong><small> + £${ex.price.toFixed(2)}</small></span>
        </span>
      </label>
    `).join('');

    return `
      <div class="GROUP">
        <h4>Choose one <span style="font-weight:600;opacity:.8;">(required)</span></h4>
        ${sizeRows}
      </div>

      <div class="GROUP">
        <h4>Add extras <span style="font-weight:600;opacity:.8;">(optional)</span></h4>
        ${extraRows}
      </div>

      <div class="QTYBAR">
        <div class="HELP">Customise your ${name} and set quantity</div>
        <div class="QTY">
          <button type="button" data-act="MINUS">−</button>
          <input id="OPTQTY" type="number" min="1" value="1">
          <button type="button" data-act="PLUS">+</button>
        </div>
      </div>

      <button id="OPTADD" class="ADD-BTN">Add</button>
    `;
  }

  // ===== WIRE HANDLERS INSIDE THE SHEET =====
  function wireUp(name, sizes, extras){
    const qtyInp = BODY.querySelector('#OPTQTY');
    const addBtn = BODY.querySelector('#OPTADD');

    function calcTotal(){
      const sizeEl = BODY.querySelector('input[name="OPT-SIZE"]:checked');
      const sizePrice = sizeEl ? parseFloat(sizeEl.dataset.price) : 0;
      const extrasTotal = Array.from(BODY.querySelectorAll('input[name="OPT-EXTRA"]:checked'))
        .reduce((a,b)=> a + parseFloat(b.dataset.price), 0);
      const qty = Math.max(1, parseInt(qtyInp.value || '1', 10));
      const total = (sizePrice + extrasTotal) * qty;
      addBtn.textContent = `Add (£${total.toFixed(2)})`;
      return { size: sizeEl ? sizeEl.value : '', unitPrice: sizePrice + extrasTotal, qty };
    }

    BODY.addEventListener('change', calcTotal);
    BODY.addEventListener('click', (e)=>{
      const b = e.target.closest('button[data-act]');
      if(!b) return;
      const act = b.dataset.act;
      const v = Math.max(1, parseInt(qtyInp.value || '1', 10) + (act === 'PLUS' ? 1 : -1));
      qtyInp.value = v;
      calcTotal();
    });
    qtyInp.addEventListener('input', calcTotal);
    calcTotal();

    addBtn.addEventListener('click', ()=>{
      const { size, unitPrice, qty } = calcTotal();
      const chosenExtras = Array.from(BODY.querySelectorAll('input[name="OPT-EXTRA"]:checked')).map(x=>x.value);
      const finalName = name + (size ? ` ${size}` : '') + (chosenExtras.length ? ` + ${chosenExtras.join(', ')}` : '');
      addToCart(finalName, unitPrice, qty);
      closeSheet();
      nudgeFab();
    });
  }

// ===== CART HELPERS (SAME STORAGE/SHAPE AS partials/basket.php) =====
const CART_KEY = 'cart_items';

function loadCart(){
  try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); }
  catch(e){ return []; }
}
function saveCart(list){
  localStorage.setItem(CART_KEY, JSON.stringify(list));
}

function keyFor(it){
  // same logic as basket.php (case-insensitive, collapse spaces)
  return (it.name + '|' + it.price).toLowerCase().replace(/\s+/g,'_');
}

function addToCart(name, price, qty){
  const list = loadCart();
  const k = keyFor({ name, price });
  const idx = list.findIndex(i => keyFor(i) === k);
  if (idx > -1) list[idx].qty += qty;
  else list.push({ name, price: Number(price), qty: Number(qty) });
  saveCart(list);

  // Update FAB immediately — use the SAME selectors/classes basket.php uses
  try{
    const badge = document.querySelector('.cart-fab .badge');
    const fab   = document.querySelector('.cart-fab');
    const count = list.reduce((a,b)=> a + (b.qty||0), 0);
    if (badge) badge.textContent = count;
    if (fab){
      fab.classList.toggle('show', count > 0); // class is "show", not "SHOW"
      fab.classList.add('WIGGLE');
      setTimeout(()=>fab.classList.remove('WIGGLE'), 300);
    }
  }catch(e){}
}

// Ensure wiggle CSS targets the same class name casing as basket.php (.cart-fab)
(function ensureWiggle(){
  if (document.getElementById('FAB-WIGGLE-CSS')) return;
  const css = document.createElement('style');
  css.id = 'FAB-WIGGLE-CSS';
  css.textContent =
    '@keyframes wig{0%{transform:scale(1)}30%{transform:scale(1.08)}100%{transform:scale(1)}} ' +
    '.cart-fab.WIGGLE{animation:wig .28s ease}';
  document.head.appendChild(css);
})();

// (optional) handy no-op used in addBtn click
function nudgeFab(){ /* handled in addToCart */ }

})(); // close IIFE
</script>

