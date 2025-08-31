<?php
// account.php — account hub with persistent account credit top-up via Stripe + local address storage
require_once __DIR__ . '/config.php';
date_default_timezone_set('Europe/London');

// ---- simple data helpers for account credit (persisted in /data/credits.json) ----
function data_dir(){ return __DIR__ . '/data'; }
function credits_file(){ return data_dir() . '/credits.json'; }
function ensure_data_dir(){ $d=data_dir(); if(!is_dir($d)) @mkdir($d,0775,true); }
function user_key(){
  if (!empty($_SESSION['user']['email'])) return 'user:'.strtolower($_SESSION['user']['email']);
  if (!empty($_SESSION['user_id']))       return 'user:'.$_SESSION['user_id'];
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  return 'ip:'.sha1($ip);
}
function load_credits(){
  ensure_data_dir();
  $f = credits_file();
  if (!file_exists($f)) return [];
  $j = @file_get_contents($f);
  $a = json_decode($j, true);
  return is_array($a)? $a : [];
}
function save_credits(array $a){
  ensure_data_dir();
  $f = credits_file(); $tmp = $f.'.tmp';
  @file_put_contents($tmp, json_encode($a, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
  @rename($tmp, $f);
}
function get_credit_pennies_for_current(){
  $all = load_credits(); $key = user_key();
  return (int)($all[$key] ?? 0);
}
function add_credit_pennies_for_current($pennies){
  $amt = max(0, (int)$pennies);
  if ($amt <= 0) return;
  $all = load_credits(); $key = user_key();
  $all[$key] = (int)($all[$key] ?? 0) + $amt;
  save_credits($all);
}

// ---- require login ----
if (empty($_SESSION['user'])) { header('Location: auth.php'); exit; }

// ---- handle Stripe return to apply credit once, then clean URL ----
$flash_notice = $flash_error = null;
if (isset($_GET['topup'])) {
  if ($_GET['topup'] === 'success') {
    // Prefer secure session-stored amount (set by stripe.php when creating the top-up). Fallback to query amt.
    $amt = isset($_SESSION['last_topup_amount']) ? (int)$_SESSION['last_topup_amount'] : (int)($_GET['amt'] ?? 0);
    if ($amt > 0) {
      add_credit_pennies_for_current($amt);
      $flash_notice = 'Payment received: £' . number_format($amt/100,2) . ' added to your account credit.';
      unset($_SESSION['last_topup_amount']);
    } else {
      $flash_error = 'Payment completed, but the amount could not be confirmed.';
    }
  } else {
    $flash_error = 'Payment failed or canceled. No charge was made.';
  }
  // Store flash to session and redirect to remove query params (prevents double-credit on refresh)
  $_SESSION['flash_notice'] = $flash_notice;
  $_SESSION['flash_error']  = $flash_error;
  $dest = strtok($_SERVER['REQUEST_URI'], '?'); // same page without query
  header('Location: ' . $dest);
  exit;
}

// pull any flash after redirect
if (!empty($_SESSION['flash_notice'])) { $flash_notice = $_SESSION['flash_notice']; unset($_SESSION['flash_notice']); }
if (!empty($_SESSION['flash_error']))  { $flash_error  = $_SESSION['flash_error'];  unset($_SESSION['flash_error']); }

// ---- user display vars ----
$user = $_SESSION['user'];
$displayName = $user['name'] ?: (explode('@', $user['email'])[0] ?? 'there');
$displayName = ucfirst($displayName);
$initial = strtoupper(mb_substr($displayName, 0, 1));

// live account credit (from file)
$credit_pennies  = get_credit_pennies_for_current();
$account_credit  = '£' . number_format($credit_pennies/100, 2);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php include __DIR__ . '/partials/theme-head.php'; ?> 
<title>My Account</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
*,*::before,*::after{ box-sizing:border-box; }
:root{
  --bg:#f9f9f9; --text:#111; --muted:#666; --line:#eaeaea;
  --card:#fff; --shadow:0 1px 3px rgba(0,0,0,.08); --primary:#ff5722;
  --ok:#16a34a; --warn:#b91c1c;
}
html,body{ margin:0; padding:0; background:var(--bg); color:var(--text);
  font-family:'Segoe UI',Tahoma,Verdana,sans-serif; -webkit-text-size-adjust:100%; }

.page{ min-height:calc(100svh - 56px); padding:16px 16px 100px; }
.header{ display:flex; align-items:center; gap:12px; margin:4px 2px 12px; }
.avatar{
  width:40px; height:40px; border-radius:50%;
  background:var(--card); border:1px solid var(--line);
  display:flex; align-items:center; justify-content:center; font-weight:800;
}
.hi{ font-size:20px; font-weight:800; margin:0; }

.notice{ background:var(--card); border:1px solid var(--line); border-left:4px solid var(--ok);
  padding:10px 12px; border-radius:10px; margin:0 0 12px; font-weight:700; }
.notice.err{ border-left-color:var(--warn); }

.sheet{
  background:var(--card); border-radius:12px; box-shadow:var(--shadow);
  overflow:hidden; max-width:700px; margin:0 auto; border:1px solid var(--line);
}
.list{ list-style:none; margin:0; padding:0; }
.sec{
  padding:10px 14px; font-size:12px; letter-spacing:.4px; text-transform:uppercase;
  color:var(--muted); background:var(--bg); border-top:1px solid var(--line);
}
.li{
  display:flex; align-items:center; gap:12px; padding:14px;
  border-top:1px solid var(--line); background:var(--card); color:var(--text);
}
.li:first-child{ border-top:0; }
.icon{
  width:36px; height:36px; border-radius:10px; flex:0 0 36px;
  display:flex; align-items:center; justify-content:center;
  background:var(--bg); border:1px solid var(--line); color:var(--text);
}
.meta{ display:flex; flex-direction:column; gap:2px; min-width:0; }
.title{ font-weight:700; }
.sub{ font-size:13px; color:var(--muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.tail{ margin-left:auto; display:flex; align-items:center; gap:10px; }
.val{ font-weight:800; }
.chev{ color:#bbb; }

.select{ appearance:none; padding:10px 12px; border:1px solid var(--line); border-radius:10px; background:var(--card); color:var(--text); font-size:14px; }

.a-row{ color:inherit; text-decoration:none; display:block; }
.li.link:active{ background:rgba(0,0,0,.03); }
.li.clickable{ cursor:pointer; }

/* Modals */
.modal{ position:fixed; inset:0; background:rgba(0,0,0,.35); display:none; align-items:flex-end; justify-content:center; z-index:10000; }
.modal.show{ display:flex; }
.sheet-modal{
  width:100%; background:var(--card); border-top-left-radius:16px; border-top-right-radius:16px;
  box-shadow:0 -6px 20px rgba(0,0,0,.15); padding:14px 14px 18px; max-width:700px; border:1px solid var(--line);
}
.sheet-h{ display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px; }
.sheet-title{ font-weight:800; font-size:18px; }
.sheet-close{ background:var(--bg); border:1px solid var(--line); padding:8px 10px; border-radius:10px; color:var(--text); cursor:pointer; }
.input{ width:100%; padding:12px; border:1px solid var(--line); border-radius:10px; font-size:16px; outline:none; background:var(--card); color:var(--text); }
.btn{
  display:inline-flex; align-items:center; justify-content:center; gap:8px;
  padding:12px 14px; border-radius:10px; border:1px solid var(--line); background:var(--card);
  font-weight:800; font-size:16px; color:var(--text); text-decoration:none; cursor:pointer;
}
.btn.primary{ background:var(--primary); border-color:var(--primary); color:#fff; }
.hint{ font-size:12px; color:var(--muted); margin-top:6px; }
.grid{ display:grid; gap:10px; margin-top:10px; }
.row{ display:flex; align-items:center; gap:10px; }

.chips{ display:flex; gap:8px; flex-wrap:wrap; margin-top:6px; }
.chip{ border:1px solid var(--line); background:var(--card); border-radius:999px; padding:8px 12px; cursor:pointer; font-weight:800; font-size:14px; }
.chip:active{ transform:translateY(1px); }
</style>
</head>
<body>

<div class="page" id="page">

  <?php if ($flash_notice): ?>
    <div class="notice"><?= htmlspecialchars($flash_notice) ?></div>
  <?php endif; ?>
  <?php if ($flash_error): ?>
    <div class="notice err"><?= htmlspecialchars($flash_error) ?></div>
  <?php endif; ?>

  <div class="header">
    <div class="avatar" aria-hidden="true"><?= htmlspecialchars($initial) ?></div>
    <h1 class="hi" id="hiText">Hey <?= htmlspecialchars($displayName) ?>!</h1>
  </div>

  <section class="sheet">
    <ul class="list">

      <li class="sec" data-i18n="sec_your_account">Your account</li>

      <!-- Account credit (click to add via Stripe) -->
      <li class="li clickable" id="creditOpen">
        <div class="icon"><i class="fas fa-wallet"></i></div>
        <div class="meta">
          <div class="title" data-i18n="account_credit">Account credit</div>
          <div class="sub" data-i18n="applied_at_checkout">Applied at checkout</div>
        </div>
        <div class="tail">
          <div class="val" id="creditVal"><?= htmlspecialchars($account_credit) ?></div>
          <i class="fas fa-chevron-right chev" aria-hidden="true"></i>
        </div>
      </li>

      <li class="li link">
        <a class="a-row" href="reorder.php" style="display:flex;align-items:center;gap:12px;width:100%">
          <div class="icon"><i class="fas fa-receipt"></i></div>
          <div class="meta">
            <div class="title" data-i18n="recent_orders">Recent orders</div>
            <div class="sub" data-i18n="no_recent_orders">Recent orders</div>
          </div>
          <div class="tail"><i class="fas fa-chevron-right chev"></i></div>
        </a>
      </li>

      <li class="sec" data-i18n="sec_settings">Settings</li>

      <!-- Your address (manual fields; saved to device) -->
      <li class="li link">
        <a href="#" id="addressOpen" class="a-row" style="display:flex;align-items:center;gap:12px;width:100%">
          <div class="icon"><i class="fas fa-location-dot"></i></div>
          <div class="meta" style="min-width:0">
            <div class="title" data-i18n="your_address">Your address</div>
            <div class="sub" id="addressSummary" data-i18n="add_delivery_address">Add your delivery address</div>
          </div>
          <div class="tail"><i class="fas fa-chevron-right chev"></i></div>
        </a>
      </li>

      <li class="li">
        <div class="icon"><i class="fas fa-language"></i></div>
        <div class="meta">
          <div class="title" data-i18n="language">Language</div>
          <div class="sub" data-i18n="choose_language">Choose your language</div>
        </div>
        <div class="tail">
          <select class="select" id="langSelect" aria-label="Language">
            <option value="en">English</option>
            <option value="pl">Polski</option>
            <option value="ro">Română</option>
            <option value="ar">العربية</option>
            <option value="tr">Türkçe</option>
            <option value="uk">Українська</option>
          </select>
        </div>
      </li>

      <li class="sec" data-i18n="sec_actions">Actions</li>

      <li class="li link">
        <a class="a-row" href="logout.php" style="display:flex;align-items:center;gap:12px;width:100%">
          <div class="icon"><i class="fas fa-right-from-bracket"></i></div>
          <div class="meta">
            <div class="title" data-i18n="log_out">Log out</div>
            <div class="sub" data-i18n="sign_out_of_account">Sign out of your account</div>
          </div>
          <div class="tail"><i class="fas fa-chevron-right chev"></i></div>
        </a>
      </li>

      <li class="li link">
        <a class="a-row" href="mailto:hello@example.com?subject=Feedback%20for%20<?= rawurlencode(RESTAURANT_NAME ?? 'Restaurant') ?>" style="display:flex;align-items:center;gap:12px;width:100%">
          <div class="icon"><i class="fas fa-comment-dots"></i></div>
          <div class="meta">
            <div class="title" data-i18n="give_feedback">Give us Feedback</div>
            <div class="sub" data-i18n="tell_us_how">Tell us how we’re doing</div>
          </div>
          <div class="tail"><i class="fas fa-chevron-right chev"></i></div>
        </a>
      </li>

    </ul>
  </section>
</div>

<!-- Address Modal -->
<div class="modal" id="addrModal" aria-hidden="true">
  <div class="sheet-modal" role="dialog" aria-modal="true" aria-labelledby="addrTitle">
    <div class="sheet-h">
      <div class="sheet-title" id="addrTitle" data-i18n="add_address_title">Add your address</div>
      <button class="sheet-close" id="addrClose" type="button"><i class="fas fa-times"></i></button>
    </div>

    <div class="grid">
      <input class="input" id="addrLine1" placeholder="Address line 1" />
      <input class="input" id="addrLine2" placeholder="Address line 2 (optional)" />
      <div class="row">
        <input class="input" id="addrCity" placeholder="Town/City" style="flex:1" />
        <input class="input" id="addrPostcode" placeholder="Postcode" style="flex:0 0 140px" />
      </div>

      <div class="row" style="justify-content:space-between">
        <button class="btn" type="button" id="addrUseLoc"><i class="fas fa-location-arrow"></i> <span data-i18n="use_current_location">Use current location</span></button>
        <button class="btn primary" type="button" id="addrSave"><i class="fas fa-check"></i> <span data-i18n="save_address">Save address</span></button>
      </div>
      <div class="hint" id="addrHint" data-i18n="addr_hint">UK-only autocomplete. Your address is saved on this device for faster checkout.</div>
    </div>
  </div>
</div>

<!-- Top-up Modal -->
<div class="modal" id="topupModal" aria-hidden="true">
  <div class="sheet-modal" role="dialog" aria-modal="true" aria-labelledby="topupTitle">
    <div class="sheet-h">
      <div class="sheet-title" id="topupTitle">Add account credit</div>
      <button class="sheet-close" id="topupClose" type="button"><i class="fas fa-times"></i></button>
    </div>

    <div>
      <div class="chips">
        <button class="chip" data-amt="500">£5</button>
        <button class="chip" data-amt="1000">£10</button>
        <button class="chip" data-amt="2000">£20</button>
        <button class="chip" data-amt="3000">£30</button>
      </div>
      <div class="grid">
        <input class="input" id="topupAmount" placeholder="Enter amount (e.g. 10.00)" inputmode="decimal" />
        <button class="btn primary" id="topupGo" type="button"><i class="fas fa-credit-card"></i> Pay with card / Apple Pay</button>
        <div class="hint">You’ll be taken to a secure Stripe checkout and returned here after payment.</div>
      </div>
    </div>
  </div>
</div>

<script>
const USER_NAME = <?= json_encode($displayName) ?>;
const I18N = {
  en: { greeting:'Hey {name}!', sec_your_account:'Your account', account_credit:'Account credit', applied_at_checkout:'Applied at checkout',
        recent_orders:'Recent orders', no_recent_orders:'Recent orders', sec_settings:'Settings',
        your_address:'Your address', add_delivery_address:'Add your delivery address', language:'Language', choose_language:'Choose your language',
        sec_actions:'Actions', log_out:'Log out', sign_out_of_account:'Sign out of your account', give_feedback:'Give us Feedback', tell_us_how:'Tell us how we’re doing',
        add_address_title:'Add your address', ph_addr_search:'Start typing your UK address…', ph_addr_line1:'Address line 1',
        ph_addr_line2:'Address line 2 (optional)', ph_city:'Town/City', ph_postcode:'Postcode', use_current_location:'Use current location',
        save_address:'Save address', addr_hint:'UK-only autocomplete. Your address is saved on this device for faster checkout.' }
};
function t(key, lang){ const L = I18N[lang] || I18N.en; return L[key] ?? I18N.en[key] ?? key; }
function applyI18n(lang){
  document.documentElement.lang = lang;
  document.documentElement.dir  = (lang === 'ar') ? 'rtl' : 'ltr';
  document.querySelectorAll('[data-i18n]').forEach(el=>{
    const key = el.getAttribute('data-i18n'); if (!key) return;
    el.textContent = t(key, lang);
  });
  const hi = document.getElementById('hiText');
  if (hi){ hi.textContent = t('greeting', lang).replace('{name}', USER_NAME); }
}
const langSelect = document.getElementById('langSelect');
const savedLang = localStorage.getItem('site_lang') || 'en';
langSelect.value = savedLang; applyI18n(savedLang);
langSelect.addEventListener('change', ()=>{ const v=langSelect.value; localStorage.setItem('site_lang', v); applyI18n(v); });

const $ = (s,r=document)=>r.querySelector(s); const $$=(s,r=document)=>Array.from(r.querySelectorAll(s));

// Address modal
const addressOpen  = $('#addressOpen'), addrModal=$('#addrModal'), addrClose=$('#addrClose');
const addrLine1=$('#addrLine1'), addrLine2=$('#addrLine2'), addrCity=$('#addrCity'), addrPostcode=$('#addrPostcode');
const addrSave=$('#addrSave'), addrUseLoc=$('#addrUseLoc');
function openAddr(){ addrModal.classList.add('show'); addrModal.setAttribute('aria-hidden','false'); }
function closeAddr(){ addrModal.classList.remove('show'); addrModal.setAttribute('aria-hidden','true'); }
addressOpen?.addEventListener('click', e=>{ e.preventDefault(); openAddr(); });
addrClose?.addEventListener('click', closeAddr);
addrModal?.addEventListener('click', e=>{ if(e.target===addrModal) closeAddr(); });

(function restoreAddress(){
  let data=null;
  try{
    data = JSON.parse(localStorage.getItem('user_address')||'null');
    if(!data){
      const s = JSON.parse(localStorage.getItem('checkout_details_v1')||'null');
      if (s && (s.address1||s.city||s.postcode)) data = {line1:s.address1||'', line2:s.address2||'', city:s.city||'', postcode:s.postcode||''};
    }
  }catch(e){}
  if(data){
    addrLine1.value=data.line1||''; addrLine2.value=data.line2||''; addrCity.value=data.city||''; addrPostcode.value=data.postcode||'';
  }
  updateSummary();
})();
function updateSummary(){
  const parts=[addrLine1.value, addrLine2.value, addrCity.value, addrPostcode.value].filter(Boolean);
  $('#addressSummary').textContent = parts.length? parts.join(', ') : t('add_delivery_address', localStorage.getItem('site_lang')||'en');
}
addrSave?.addEventListener('click', ()=>{
  const payload={ line1:addrLine1.value.trim(), line2:addrLine2.value.trim(), city:addrCity.value.trim(), postcode:addrPostcode.value.trim(), ts:Date.now() };
  try{
    localStorage.setItem('user_address', JSON.stringify(payload));
    const st = JSON.parse(localStorage.getItem('checkout_details_v1')||'{}');
    st.address1=payload.line1; st.address2=payload.line2; st.city=payload.city; st.postcode=payload.postcode;
    st.address=[payload.line1,payload.line2,payload.city,payload.postcode].filter(Boolean).join(', ');
    localStorage.setItem('checkout_details_v1', JSON.stringify(st));
  }catch(e){ alert('Could not save your address on this device.'); }
  updateSummary(); closeAddr();
});
addrUseLoc?.addEventListener('click', ()=>{
  if(!navigator.geolocation){ alert('Geolocation not supported on this device.'); return; }
  navigator.geolocation.getCurrentPosition(async pos=>{
    const {latitude, longitude}=pos.coords;
    if(window.google && google.maps && google.maps.Geocoder){
      const geocoder=new google.maps.Geocoder();
      geocoder.geocode({location:{lat:latitude,lng:longitude}}, (results,status)=>{
        if(status==='OK' && results && results[0]){ fillFromPlace(results[0]); updateSummary(); }
        else alert('Could not fetch address from your location.');
      });
    }else{ alert('Location captured. Enable Google Maps JavaScript API to reverse-geocode automatically.'); }
  }, _=>alert('Could not access your location. Please allow permission.'), {enableHighAccuracy:true, timeout:10000, maximumAge:0});
});
function fillFromPlace(place){
  const comp=type=>(place.address_components||[]).find(c=>c.types.includes(type))?.long_name||'';
  const line1=[comp('street_number'), comp('route')].filter(Boolean).join(' ');
  const city=comp('postal_town')||comp('locality')||comp('sublocality')||comp('sublocality_level_1');
  const postcode=comp('postal_code');
  addrLine1.value=line1||place.formatted_address||''; addrCity.value=city; addrPostcode.value=postcode;
}

// Top-up modal + Stripe
const creditOpen = $('#creditOpen'), topupModal=$('#topupModal'), topupClose=$('#topupClose');
const topupAmount=$('#topupAmount'), topupGo=$('#topupGo');
function openTopup(){ topupModal.classList.add('show'); topupModal.setAttribute('aria-hidden','false'); setTimeout(()=>topupAmount.focus(), 30); }
function closeTopup(){ topupModal.classList.remove('show'); topupModal.setAttribute('aria-hidden','true'); }
creditOpen?.addEventListener('click', openTopup);
topupClose?.addEventListener('click', closeTopup);
topupModal?.addEventListener('click', e=>{ if(e.target===topupModal) closeTopup(); });

async function postJSON(url, data){
  const res = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) });
  const txt = await res.text(); let json; try{ json=JSON.parse(txt);}catch{ json={error:'Invalid JSON',raw:txt}; }
  if(!res.ok) throw new Error(json.error||'Request failed'); return json;
}
$$('.chip[data-amt]').forEach(chip=>{
  chip.addEventListener('click', ()=> startTopup(parseInt(chip.getAttribute('data-amt'),10)));
});
topupGo?.addEventListener('click', ()=>{
  const val=(topupAmount.value||'').trim().replace(',','.');
  const n=Math.round(parseFloat(val)*100);
  if(!isFinite(n)||n<100){ alert('Enter at least £1.00'); return; }
  startTopup(n);
});
topupAmount?.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); topupGo.click(); } });

async function startTopup(pennies){
  try{
    const data = await postJSON('stripe.php', { action:'create_topup', amount:pennies, return_to:'account.php' });
    if (data && data.url){ window.location.href = data.url; return; }
    alert(data?.error || 'Could not start Stripe Checkout.');
  }catch(err){ alert(err.message||'Could not start Stripe Checkout.'); }
}
</script>

<?php
$navPath = __DIR__ . '/partials/bottom-nav.php';
if (file_exists($navPath)) include $navPath;

$togglePath = __DIR__ . '/partials/theme-toggle.php';
if (file_exists($togglePath)) include $togglePath;
?>
</body>
</html>

