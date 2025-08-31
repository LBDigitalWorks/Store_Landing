<?php require __DIR__ . '/_guard.php';

// ---- Paths (work in subfolders like /websites/shop) ----
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/'); // /websites/shop/admin
$BASE      = rtrim(dirname($scriptDir), '/');        // /websites/shop
$ADMIN     = $BASE . '/admin';
$HOME_URL  = ($BASE === '' ? '/' : $BASE . '/');
$LOGO_URL  = $BASE . '/assets/images/logo.jpg';

// Default to dark if no cookie set
$theme = $_COOKIE['theme'] ?? 'dark';

$me = $_SESSION['admin_email'] ?? 'owner@your-restaurant.com';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($theme); ?>">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
  <?php if (file_exists(__DIR__ . '/../partials/theme-head.php')) include __DIR__ . '/../partials/theme-head.php'; ?>
  <title>Admin • Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    :root{
      --primary:#f04f32;
      --text:#0f1113; --muted:#666; --bg:#f7f7f8; --panel:#fff; --line:#e6e6e6; --chip:#eee;
    }
    [data-theme="dark"] :root{
      --text:#e9eef3; --muted:#aeb6bf; --bg:#0f1113; --panel:#131417; --line:#1f1f25; --chip:#1e1f22;
    }

    *{box-sizing:border-box}
    html{scroll-behavior:smooth; -webkit-text-size-adjust:100%;}
    body{
      margin:0; font-family:system-ui,-apple-system,"Segoe UI",Roboto,Ubuntu,Cantarell,"Noto Sans",sans-serif;
      background:var(--bg); color:var(--text);
      padding-top:env(safe-area-inset-top); padding-left:env(safe-area-inset-left); padding-right:env(safe-area-inset-right);
    }
    body.no-scroll{overflow:hidden}
    a, a:visited{color:inherit; text-decoration:none} /* no blue links anywhere */

    /* Layout */
    .shell{min-height:100vh; display:grid; grid-template-columns:260px 1fr; overflow:hidden}
    @media (max-width:900px){
      .shell{grid-template-columns:1fr}
      .sidebar{
        position:fixed; inset:0 auto 0 0; width:min(86vw, 320px);
        transform:translateX(-102%); transition:transform .2s ease; z-index:4000;
        box-shadow:0 20px 60px rgba(0,0,0,.35);
      }
      .sidebar.is-open{transform:none}
      .backdrop{position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; z-index:3000}
      .backdrop.is-open{display:block}
      .topbar{position:sticky; top:0}
      .topbar .burger{display:inline-flex}
      .actions .hide-sm{display:none}
      .actions .btn{padding:8px 10px}
      .user .meta{display:none}
    }

    /* Sidebar */
    .sidebar{background:var(--panel); border-right:1px solid var(--line); padding:16px; display:flex; flex-direction:column; gap:14px}
    .brand{display:flex; align-items:center; gap:10px}
    .brand .logo{width:42px; height:42px; border-radius:50%; overflow:hidden; background:#fff; border:2px solid #fff}
    .brand .logo img{width:100%; height:100%; object-fit:cover; display:block}
    .brand .name{font-weight:800}
    .nav{list-style:none; padding:6px 0 0; margin:0}
    .nav li{
      display:flex; align-items:center; gap:10px; padding:11px 10px; border-radius:10px; cursor:pointer; color:var(--text);
    }
    .nav li i{width:18px; text-align:center}
    .nav li .badge{margin-left:auto; background:var(--chip); border:1px solid var(--line); padding:0 8px; height:20px; border-radius:10px; font-size:12px; display:inline-flex; align-items:center}
    .nav li:hover{background:rgba(255,255,255,.06)}
    [data-theme="dark"] .nav li:hover{background:#17191d}
    .nav li.active{background:rgba(240,79,50,.16); color:#fff; font-weight:800}

    .sidebar .foot{margin-top:auto; font-size:12px; color:var(--muted); border-top:1px solid var(--line); padding-top:10px}

    /* Topbar */
    .main{display:flex; flex-direction:column; min-width:0}
    .topbar{
      display:flex; align-items:center; justify-content:space-between; gap:10px;
      padding:10px 12px; background:var(--panel); border-bottom:1px solid var(--line); z-index:1000;
      backdrop-filter:saturate(120%) blur(6px);
    }
    .topbar .left{display:flex; align-items:center; gap:8px; min-width:0; flex:1}
    .burger{display:none; width:36px; height:36px; align-items:center; justify-content:center; border:1px solid var(--line); border-radius:10px; background:transparent; cursor:pointer}
    .search{flex:1; min-width:0; display:flex; align-items:center; gap:8px; background:var(--panel); border:1px solid var(--line); border-radius:10px; padding:8px 12px}
    .search input{border:none; outline:none; background:transparent; color:var(--text); width:100%}
    .actions{display:flex; align-items:center; gap:8px}
    .btn{display:inline-flex; align-items:center; gap:8px; padding:10px 12px; border-radius:10px; border:1px solid var(--line); background:var(--panel); cursor:pointer; font-weight:700}
    .btn-primary{background:var(--primary); color:#fff; border:none}
    .btn-ghost{background:transparent}
    .user{display:flex; align-items:center; gap:10px; padding:8px 10px; border:1px solid var(--line); border-radius:10px}
    .avatar{width:28px; height:28px; border-radius:50%; overflow:hidden; background:#ddd}

    /* Content */
    .content{padding:16px; display:block}
    .page-head{display:flex; align-items:center; justify-content:space-between; gap:8px; margin:0 0 12px}
    .page-head h1{font-size:20px; margin:0}
    .crumbs{color:var(--muted); font-size:13px}
    .grid{display:grid; grid-template-columns:repeat(12,1fr); gap:12px}
    @media (max-width:700px){ .grid{grid-template-columns:repeat(6,1fr)} }

    .card{background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:14px; box-shadow:0 2px 8px rgba(0,0,0,.04)}
    .card h3{margin:0 0 8px; font-size:16px}
    .muted{color:var(--muted)}
    .chip{display:inline-flex; align-items:center; gap:6px; padding:2px 8px; border-radius:999px; background:var(--chip); border:1px solid var(--line); font-size:12px}
    .status-new{background:#4e1b12; border-color:#7e2a1a; color:#ffd5ca}
    .status-prep{background:#4b3a12; border-color:#7a5c18; color:#ffe0a1}
    .status-ready{background:#103a19; border-color:#1f6a2e; color:#b7e0b9}
    .status-out{background:#0e2e49; border-color:#25577f; color:#b9dafb}

    .table{width:100%; border-collapse:collapse}
    .table th,.table td{border-bottom:1px solid var(--line); padding:10px; text-align:left; font-size:14px}
    .table th{font-size:12px; text-transform:uppercase; letter-spacing:.02em; color:var(--muted)}

    .kpi{display:flex; align-items:center; justify-content:space-between}
    .kpi .num{font-size:22px; font-weight:800}
    .kpi .sub{font-size:12px; color:var(--muted)}

    /* Panels */
    .panel{display:none}
    .panel.is-active{display:block}
    .order-list{display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px}
    .order-card .head{display:flex; justify-content:space-between; align-items:center; margin-bottom:8px}
    .order-card .items{font-size:13px; color:var(--muted); margin:8px 0}
    .order-card .foot{display:flex; gap:8px}
    .order-card .btn{flex:1; justify-content:center}
    .empty{text-align:center; color:var(--muted); padding:24px; border:1px dashed var(--line); border-radius:12px; background:var(--panel)}

    .hide-sm{display:inline}
    @media (max-width:600px){ .hide-sm{display:none} }

    /* Menu Manager helpers */
    .list > .row{display:flex; align-items:center; justify-content:space-between; gap:8px; padding:10px; border:1px solid var(--line); border-radius:10px; margin-bottom:8px; background:var(--panel)}
    .row .meta{color:var(--muted); font-size:12px}
    .row .actions{display:flex; gap:6px}
    .input-like{border:1px solid var(--line); background:var(--panel); color:inherit; border-radius:10px; padding:10px; outline:none}
    .pill{display:inline-flex; align-items:center; gap:6px; padding:2px 8px; border-radius:999px; background:var(--chip); border:1px solid var(--line); font-size:12px}
  </style>
</head>
<body>
  <div class="backdrop" id="backdrop"></div>
  <div class="shell">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar" aria-label="Sidebar">
      <div class="brand">
        <div class="logo"><img src="<?php echo htmlspecialchars($LOGO_URL); ?>" alt="Logo"></div>
        <div class="name">Your Restaurant</div>
      </div>

      <ul class="nav" id="nav">
        <li class="active" data-section="live-orders"><i class="fa-solid fa-receipt"></i> Live Orders <span class="badge">3</span></li>
        <li data-section="dashboard"><i class="fa-solid fa-gauge-high"></i> Overview</li>
        <li data-section="order-history"><i class="fa-regular fa-clock"></i> Order History</li>
        <li data-section="menu-manager"><i class="fa-solid fa-pizza-slice"></i> Menu Manager</li>
        <li data-section="opening-hours"><i class="fa-regular fa-calendar"></i> Opening Hours</li>
        <li data-section="delivery-settings"><i class="fa-solid fa-truck-fast"></i> Delivery Settings</li>
        <li data-section="promotions"><i class="fa-solid fa-tags"></i> Promotions</li>
        <li data-section="banners"><i class="fa-regular fa-flag"></i> Banners/Announcements</li>
        <li data-section="customers"><i class="fa-regular fa-user"></i> Customers</li>
        <li data-section="reports"><i class="fa-solid fa-chart-line"></i> Reports</li>
        <li data-section="staff"><i class="fa-solid fa-user-gear"></i> Staff / Users</li>
        <li data-section="payments"><i class="fa-regular fa-credit-card"></i> Payments</li>
        <li data-section="printers"><i class="fa-solid fa-print"></i> Printers / KDS</li>
        <li data-section="settings"><i class="fa-solid fa-gear"></i> Settings</li>
        <li data-section="support"><i class="fa-regular fa-circle-question"></i> Support</li>
      </ul>

      <div class="foot">
        Signed in as <strong><?php echo htmlspecialchars($me); ?></strong><br>
        <a href="<?php echo htmlspecialchars($ADMIN . '/logout.php'); ?>"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
      </div>
    </aside>

    <!-- Main -->
    <main class="main">
      <div class="topbar">
        <div class="left">
          <button class="burger" id="burger" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
          <div class="search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" placeholder="Search orders, customers, items…" aria-label="Search"/>
          </div>
        </div>
        <div class="actions">
          <button class="btn btn-ghost" id="themeBtn" title="Theme"><i class="fa-regular fa-moon"></i><span class="hide-sm"> Theme</span></button>
          <a href="<?php echo htmlspecialchars($HOME_URL); ?>" class="btn btn-ghost" title="View site"><i class="fa-solid fa-house"></i><span class="hide-sm"> View site</span></a>
          <div class="user">
            <div class="avatar"><img alt="" src="<?php echo htmlspecialchars($LOGO_URL); ?>" style="width:100%;height:100%;object-fit:cover"></div>
            <div class="meta" style="font-size:12px">
              <div style="font-weight:700">Admin</div>
              <div class="muted"><?php echo htmlspecialchars($me); ?></div>
            </div>
          </div>
        </div>
      </div>

      <section class="content">
        <div class="page-head">
          <h1 id="pageTitle">Live Orders</h1>
          <div class="right-actions">
            <button class="btn btn-primary" disabled title="Demo"><i class="fa-solid fa-plus"></i> New order</button>
            <button class="btn" disabled title="Demo"><i class="fa-solid fa-bell"></i> Sound</button>
          </div>
        </div>
        <div class="crumbs" id="crumbs">Home / Live Orders</div>

        <!-- PANELS -->
        <div class="panel is-active" id="panel-live-orders">
          <div class="grid">
            <div class="card" style="grid-column:span 3;"><h3>New</h3><div class="kpi"><div class="num">2</div><div class="sub">waiting</div></div></div>
            <div class="card" style="grid-column:span 3;"><h3>Preparing</h3><div class="kpi"><div class="num">1</div><div class="sub">in kitchen</div></div></div>
            <div class="card" style="grid-column:span 3;"><h3>Ready</h3><div class="kpi"><div class="num">0</div><div class="sub">for pickup</div></div></div>
            <div class="card" style="grid-column:span 3;"><h3>Out for delivery</h3><div class="kpi"><div class="num">0</div><div class="sub">on the road</div></div></div>
          </div>

          <div class="order-list" style="margin-top:12px;">
            <div class="card order-card">
              <div class="head"><strong>#1042 • £21.50</strong><span class="chip status-new"><i class="fa-solid fa-bolt"></i> New</span></div>
              <div class="muted">Delivery • 32-45 mins • John D</div>
              <div class="items">1× Chicken Biryani, 1× Garlic Naan, 1× Coke</div>
              <div class="foot"><button class="btn btn-primary" disabled>Accept</button><button class="btn" disabled>Reject</button></div>
            </div>

            <div class="card order-card">
              <div class="head"><strong>#1041 • £13.00</strong><span class="chip status-prep"><i class="fa-solid fa-fire-burner"></i> Preparing</span></div>
              <div class="muted">Collection • 15 mins • Sarah P</div>
              <div class="items">1× Pepperoni 12", 1× Fries</div>
              <div class="foot"><button class="btn btn-primary" disabled>Mark Ready</button><button class="btn" disabled>Details</button></div>
            </div>

            <div class="card order-card">
              <div class="head"><strong>#1040 • £8.50</strong><span class="chip status-ready"><i class="fa-regular fa-circle-check"></i> Ready</span></div>
              <div class="muted">Collection • Mark T</div>
              <div class="items">1× Loaded Fries Original</div>
              <div class="foot"><button class="btn btn-primary" disabled>Complete</button><button class="btn" disabled>Details</button></div>
            </div>
          </div>
        </div>

        <div class="panel" id="panel-dashboard">
          <div class="grid">
            <div class="card" style="grid-column:span 4;"><h3>Today’s revenue</h3><div class="kpi"><div class="num">£342.10</div><div class="sub">+12% vs yesterday</div></div></div>
            <div class="card" style="grid-column:span 4;"><h3>Orders</h3><div class="kpi"><div class="num">27</div><div class="sub">avg £12.67</div></div></div>
            <div class="card" style="grid-column:span 4;"><h3>Top item</h3><div class="kpi"><div class="num">Chicken Biryani</div><div class="sub">8 sold</div></div></div>
            <div class="card" style="grid-column:span 12;"><h3>Activity</h3><div class="empty">Charts/graphs placeholder</div></div>
          </div>
        </div>

        <div class="panel" id="panel-order-history">
          <div class="card">
            <h3>Order History</h3>
            <table class="table">
              <thead><tr><th>ID</th><th>Time</th><th>Type</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
              <tbody>
                <tr><td>#1039</td><td>18:24</td><td>Delivery</td><td>A. Khan</td><td>£24.00</td><td><span class="chip">Completed</span></td></tr>
                <tr><td>#1038</td><td>18:02</td><td>Collection</td><td>J. Singh</td><td>£10.50</td><td><span class="chip">Completed</span></td></tr>
                <tr><td>#1037</td><td>17:55</td><td>Delivery</td><td>M. Doe</td><td>£16.00</td><td><span class="chip">Cancelled</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Menu Manager (functional) -->
        <div class="panel" id="panel-menu-manager">
          <div class="grid" id="mm-grid">
            <!-- Categories -->
            <div class="card" style="grid-column:span 4;">
              <h3>Categories</h3>

              <form id="catForm" style="display:flex;gap:8px;align-items:center;margin:8px 0 12px">
                <input class="input-like" id="catName" type="text" placeholder="e.g. Pizzas" required style="flex:1;">
                <button class="btn btn-primary" type="submit">Add</button>
              </form>

              <div id="catList" class="list"></div>
            </div>

            <!-- Items -->
            <div class="card" style="grid-column:span 8;">
              <h3>Items <span id="mm-cat-title" class="muted"></span></h3>

              <form id="itemForm" style="display:grid; grid-template-columns:1fr 1fr 120px; gap:8px; margin:8px 0 12px;">
                <input class="input-like" id="itemName" placeholder="Item name" required>
                <input class="input-like" id="itemDesc" placeholder="Short description">
                <button class="btn btn-primary" type="submit">Add item</button>
                <label style="grid-column:1/span 1; display:flex; align-items:center; gap:6px; font-size:13px">
                  <input type="checkbox" id="itemVeg"> Veg
                </label>
                <label style="display:flex; align-items:center; gap:6px; font-size:13px">
                  <input type="checkbox" id="itemSpicy"> Spicy
                </label>
                <input class="input-like" id="itemImg" placeholder="Image URL (optional)" style="grid-column:1 / span 2;">
              </form>

              <div id="itemList" class="list"></div>
            </div>

            <!-- Item editor (sizes & options) -->
            <div class="card" id="itemEditor" style="grid-column:span 12; display:none;">
              <h3 id="edTitle">Edit item</h3>

              <div class="grid">
                <div class="card" style="grid-column:span 6;">
                  <h3>Sizes & Prices</h3>
                  <form id="sizeForm" style="display:flex; gap:8px; margin:8px 0 12px;">
                    <input class="input-like" id="sizeLabel" placeholder='e.g. 10"' required>
                    <input class="input-like" id="sizePrice" placeholder="Price" inputmode="decimal" required>
                    <button class="btn btn-primary" type="submit">Add size</button>
                  </form>
                  <div id="sizeList" class="list"></div>
                </div>

                <div class="card" style="grid-column:span 6;">
                  <h3>Option Groups / Extras</h3>
                  <form id="groupForm" style="display:grid; grid-template-columns:1fr 130px 130px 120px; gap:8px; margin:8px 0 12px;">
                    <input class="input-like" id="groupName" placeholder="Group name (e.g. Toppings)" required>
                    <select class="input-like" id="groupType">
                      <option value="multi">Multiple</option>
                      <option value="single">Single</option>
                    </select>
                    <input class="input-like" id="groupMax" placeholder="Max (0 = unlimited)" inputmode="numeric" value="0">
                    <button class="btn btn-primary" type="submit">Add group</button>
                  </form>

                  <div id="groupList" class="list"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="panel" id="panel-opening-hours">
          <div class="card"><h3>Opening Hours</h3>
            <div class="grid">
              <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
                <div class="card" style="grid-column:span 4"><strong><?php echo $d; ?></strong><div class="muted">17:00 — 23:00</div><div style="margin-top:8px"><button class="btn" disabled>Edit</button></div></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="panel" id="panel-delivery-settings">
          <div class="grid">
            <div class="card" style="grid-column:span 6;"><h3>Zones</h3><div class="muted">Define zones and fees.</div><div class="empty" style="margin-top:8px;">Map/zone editor placeholder</div></div>
            <div class="card" style="grid-column:span 6;"><h3>Fees & Minimums</h3>
              <table class="table"><tbody>
                <tr><td>Base fee</td><td>£2.00</td></tr>
                <tr><td>Free over</td><td>£20.00</td></tr>
                <tr><td>Min order</td><td>£10.00</td></tr>
              </tbody></table>
            </div>
          </div>
        </div>

        <div class="panel" id="panel-promotions"><div class="card"><h3>Promotions</h3><div class="empty">Voucher codes & deals placeholders</div></div></div>
        <div class="panel" id="panel-banners"><div class="card"><h3>Banners / Announcements</h3><div class="empty">Homepage banner / alert placeholder</div></div></div>
        <div class="panel" id="panel-customers"><div class="card"><h3>Customers</h3><div class="empty">Customers table placeholder</div></div></div>
        <div class="panel" id="panel-reports"><div class="card"><h3>Reports</h3><div class="empty">Charts placeholders</div></div></div>
        <div class="panel" id="panel-staff"><div class="card"><h3>Staff / Users</h3><div class="empty">Roles & permissions placeholder</div></div></div>
        <div class="panel" id="panel-payments"><div class="card"><h3>Payments</h3><div class="empty">Stripe/Square connection placeholder</div></div></div>
        <div class="panel" id="panel-printers"><div class="card"><h3>Printers / KDS</h3><div class="empty">Tickets & KDS setup placeholder</div></div></div>
        <div class="panel" id="panel-settings"><div class="card"><h3>General Settings</h3><div class="empty">Name, address, taxes placeholder</div></div></div>
        <div class="panel" id="panel-support"><div class="card"><h3>Support</h3><div class="empty">Docs/FAQ placeholder</div></div></div>
      </section>
    </main>
  </div>

  <!-- Core nav/theme script -->
  <script>
    (function(){
      const nav = document.getElementById('nav');
      const title = document.getElementById('pageTitle');
      const crumbs = document.getElementById('crumbs');
      const sidebar = document.getElementById('sidebar');
      const backdrop = document.getElementById('backdrop');
      const burger = document.getElementById('burger');
      const themeBtn = document.getElementById('themeBtn');

      const names = {
        "live-orders":"Live Orders","dashboard":"Overview","order-history":"Order History",
        "menu-manager":"Menu Manager","opening-hours":"Opening Hours","delivery-settings":"Delivery Settings",
        "promotions":"Promotions","banners":"Banners/Announcements","customers":"Customers","reports":"Reports",
        "staff":"Staff / Users","payments":"Payments","printers":"Printers / KDS","settings":"Settings","support":"Support"
      };

      function show(section){
        [...nav.children].forEach(li => li.classList.toggle('active', li.dataset.section === section));
        document.querySelectorAll('.panel').forEach(p => p.classList.remove('is-active'));
        const panel = document.getElementById('panel-' + section);
        if(panel) panel.classList.add('is-active');
        const nice = names[section] || section;
        title.textContent = nice;
        crumbs.textContent = 'Home / ' + nice;
        history.replaceState(null, '', '#' + section);
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-open');
        document.body.classList.remove('no-scroll');
      }

      nav.addEventListener('click', (e)=>{
        const li = e.target.closest('li[data-section]');
        if(!li) return;
        show(li.dataset.section);
      });

      if(burger){
        burger.addEventListener('click', ()=>{
          sidebar.classList.add('is-open');
          backdrop.classList.add('is-open');
          document.body.classList.add('no-scroll');
        });
        backdrop.addEventListener('click', ()=>{
          sidebar.classList.remove('is-open');
          backdrop.classList.remove('is-open');
          document.body.classList.remove('no-scroll');
        });
      }

      // Dark <-> light toggle (starts dark)
      themeBtn?.addEventListener('click', ()=>{
        const root = document.documentElement;
        const cur = root.getAttribute('data-theme') || 'dark';
        const next = cur === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        document.cookie = 'theme=' + next + '; path=/; SameSite=Lax; max-age=' + (3600*24*365);
        themeBtn.innerHTML = (next==='dark'
          ? '<i class="fa-regular fa-moon"></i><span class="hide-sm"> Theme</span>'
          : '<i class="fa-regular fa-sun"></i><span class="hide-sm"> Theme</span>');
      });

      // deep link from hash
      const initial = (location.hash || '#live-orders').replace('#','');
      show(initial);
    })();
  </script>

  <!-- Menu Manager logic (talks to admin/api/menu.php) -->
  <script>
  (function(){
   const API = 'api/menu.php';


    // State
    let data = [];           // full tree from API
    let currentCat = null;   // category id
    let currentItem = null;  // item id

    const $ = (sel, p=document) => p.querySelector(sel);
    const $$= (sel, p=document) => Array.from(p.querySelectorAll(sel));

    // Elements
    const catForm  = $('#catForm');
    const catName  = $('#catName');
    const catList  = $('#catList');

    const itemForm = $('#itemForm');
    const itemName = $('#itemName');
    const itemDesc = $('#itemDesc');
    const itemVeg  = $('#itemVeg');
    const itemSpicy= $('#itemSpicy');
    const itemImg  = $('#itemImg');
    const itemList = $('#itemList');
    const catTitle = $('#mm-cat-title');

    const editor   = $('#itemEditor');
    const edTitle  = $('#edTitle');

    const sizeForm = $('#sizeForm');
    const sizeLabel= $('#sizeLabel');
    const sizePrice= $('#sizePrice');
    const sizeList = $('#sizeList');

    const groupForm= $('#groupForm');
    const groupName= $('#groupName');
    const groupType= $('#groupType');
    const groupMax = $('#groupMax');
    const groupList= $('#groupList');

    // Utils
    async function call(action, payload={}){
      const res = await fetch(API, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action, ...payload })
      });
      return await res.json();
    }
    function money(n){ return '£' + (Number(n||0).toFixed(2)); }

    // Renderers
    function renderCats(){
      catList.innerHTML = '';
      data.forEach(c => {
        const row = document.createElement('div');
        row.className = 'row';
        row.innerHTML = `
          <div>
            <strong>${c.name}</strong>
            <div class="meta">ID ${c.id} • ${c.active?'Active':'Hidden'}</div>
          </div>
          <div class="actions">
            <button class="btn" data-open="${c.id}">Open</button>
            <button class="btn" data-del="${c.id}" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
          </div>`;
        row.addEventListener('click', (e)=>{
          if (e.target.closest('[data-del]')) {
            const id = Number(e.target.closest('[data-del]').dataset.del);
            if (confirm('Delete this category (and all its items)?')) delCategory(id);
          } else if (e.target.closest('[data-open]')) {
            const id = Number(e.target.closest('[data-open]').dataset.open);
            currentCat = id; currentItem = null;
            renderItems();
          }
        });
        catList.appendChild(row);
      });
    }

    function renderItems(){
      const cat = data.find(c => c.id === currentCat) || data[0];
      if (!cat) { itemList.innerHTML = ''; catTitle.textContent=''; editor.style.display='none'; return; }
      currentCat = cat.id;
      catTitle.textContent = `(in ${cat.name})`;

      itemList.innerHTML = '';
      cat.items.forEach(it => {
        const sizes = (it.sizes||[]).map(s => `${s.label} ${money(s.price)}`).join(' • ');
        const row = document.createElement('div');
        row.className = 'row';
        row.innerHTML = `
          <div>
            <strong>${it.name}</strong>
            <div class="meta">${it.description || ''}</div>
            <div class="meta">${sizes || 'no sizes yet'}</div>
          </div>
          <div class="actions">
            ${it.veg ? '<span class="pill">Veg</span>' : ''}
            ${it.spicy ? '<span class="pill">Spicy</span>' : ''}
            <button class="btn" data-edit="${it.id}">Manage</button>
            <button class="btn" data-del="${it.id}" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
          </div>`;
        row.addEventListener('click', (e)=>{
          if (e.target.closest('[data-del]')) {
            const id = Number(e.target.closest('[data-del]').dataset.del);
            if (confirm('Delete this item?')) delItem(id);
          } else if (e.target.closest('[data-edit]')) {
            const id = Number(e.target.closest('[data-edit]').dataset.edit);
            editItem(id);
          }
        });
        itemList.appendChild(row);
      });

      editor.style.display = currentItem ? 'block' : 'none';
    }

    function editItem(itemId){
      currentItem = itemId;
      const cat = data.find(c => c.id === currentCat);
      const it = cat?.items?.find(x => x.id === currentItem);
      if (!it) return;
      editor.style.display = 'block';
      edTitle.textContent = `Edit: ${it.name}`;
      renderSizes(it);
      renderGroups(it);
    }

    function renderSizes(it){
      sizeList.innerHTML = '';
      (it.sizes||[]).forEach(s => {
        const row = document.createElement('div');
        row.className = 'row';
        row.innerHTML = `<div><strong>${s.label}</strong> <span class="meta">${money(s.price)}</span></div>
                         <div class="actions"><button class="btn" data-del-size="${s.id}"><i class="fa-regular fa-trash-can"></i></button></div>`;
        row.addEventListener('click', async (e)=>{
          if (e.target.closest('[data-del-size]')) {
            const id = Number(e.target.closest('[data-del-size]').dataset.delSize);
            await call('delete_size', {id});
            await reload();
            editItem(it.id);
          }
        });
        sizeList.appendChild(row);
      });
    }

    function renderGroups(it){
      groupList.innerHTML = '';
      (it.option_groups||[]).forEach(g => {
        const wrap = document.createElement('div');
        wrap.className = 'row';
        const opts = (g.options||[]).map(o => `${o.name}${o.price_delta? ' '+money(o.price_delta):''}`).join(' • ') || 'no options yet';
        wrap.innerHTML = `
          <div>
            <strong>${g.name}</strong> <span class="pill">${g.type === 'single' ? 'Single' : 'Multiple'}</span>
            <div class="meta">${opts}</div>
            <form data-add-op="${g.id}" style="display:flex; gap:6px; margin-top:8px">
              <input class="input-like" name="name" placeholder="Option name" required>
              <input class="input-like" name="delta" placeholder="+ price" inputmode="decimal">
              <button class="btn" type="submit">Add</button>
            </form>
          </div>
          <div class="actions">
            <button class="btn" data-del-group="${g.id}" title="Delete group"><i class="fa-regular fa-trash-can"></i></button>
          </div>
        `;
        wrap.addEventListener('submit', async (e)=>{
          e.preventDefault();
          const f = e.target.closest('form[data-add-op]');
          if (!f) return;
          const gid = Number(f.dataset.addOp);
          const name = f.name.value.trim();
          const price_delta = parseFloat(f.delta.value || '0') || 0;
          if (!name) return;
          await call('add_option', { group_id: gid, name, price_delta });
          f.reset();
          await reload();
          editItem(it.id);
        });
        wrap.addEventListener('click', async (e)=>{
          if (e.target.closest('[data-del-group]')) {
            // Not implemented in compact API; keeping UI button present
            alert('Group deletion is disabled in this compact demo. Remove options instead.');
          }
        });
        groupList.appendChild(wrap);
      });
    }

    // Actions
    async function reload(){
      const res = await call('list');
      data = (res && res.ok) ? res.data : [];
      if (!currentCat && data[0]) currentCat = data[0].id;
      renderCats();
      renderItems();
    }
    async function delCategory(id){
      await call('delete_category', {id});
      if (currentCat === id) currentCat = null;
      await reload();
    }
    async function delItem(id){
      await call('delete_item', {id});
      if (currentItem === id) currentItem = null;
      await reload();
    }

    // Form handlers
    catForm?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const name = catName.value.trim();
      if (!name) return;
      await call('create_category', { name });
      catForm.reset();
      await reload();
    });

    itemForm?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if (!currentCat) { alert('Create/select a category first.'); return; }
      const payload = {
        category_id: currentCat,
        name: itemName.value.trim(),
        description: itemDesc.value.trim(),
        veg: itemVeg.checked ? 1 : 0,
        spicy: itemSpicy.checked ? 1 : 0,
        image_url: itemImg.value.trim()
      };
      if (!payload.name) return;
      await call('create_item', payload);
      itemForm.reset();
      await reload();
    });

    sizeForm?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if (!currentItem) return;
      const label = sizeLabel.value.trim();
      const price = parseFloat(sizePrice.value || '0') || 0;
      if (!label) return;
      await call('add_size', { item_id: currentItem, label, price });
      sizeForm.reset();
      await reload();
      editItem(currentItem);
    });

    groupForm?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if (!currentItem) return;
      const name = groupName.value.trim();
      const type = groupType.value;
      const max  = parseInt(groupMax.value || '0', 10) || 0;
      if (!name) return;
      await call('create_group', { item_id: currentItem, name, type, max_select: max });
      groupForm.reset();
      await reload();
      editItem(currentItem);
    });

    // boot
    reload();
  })();
  </script>
</body>
</html>

