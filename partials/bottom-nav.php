<?php
// partials/bottom-nav.php
// NOTE: Do NOT start sessions or include config here.
// Start the session at the very top of entry pages (index.php, about.php, etc)
// via: require_once __DIR__ . '/config.php';

if (!defined('BASE_URL')) {
  // Safe fallback; your entry pages should define this in config.php
  define('BASE_URL', 'https://lbdigitalworks.com/websites/shop/');
}

// Decide where "Account" should go.
$sessionActive = (session_status() === PHP_SESSION_ACTIVE);
$loggedIn = $sessionActive && !empty($_SESSION['user']);

$accountTarget = defined('ACCOUNT_PAGE')
  ? ACCOUNT_PAGE
  : ($loggedIn ? 'account.php' : 'auth.php');

// Active tab detection
$script = basename($_SERVER['SCRIPT_NAME'] ?? '');
$uri    = $_SERVER['REQUEST_URI'] ?? '';

$isIndex   = ($script === 'index.php');
$isAbout   = ($script === 'about.php');
$isReorder = ($script === 'reorder.php');
$isAccount = ($script === basename($accountTarget))
          || strpos($uri, '/account') !== false
          || strpos($uri, '/auth') !== false;
?>
<style>
  .bottom-nav{
    position:fixed;
    bottom:0;
    left:0;
    width:100%;
    background:var(--card,#fff);   /* use theme variable instead of hard white */
    border-top:1px solid var(--line,#ccc);
    display:flex;
    justify-content:space-around;
    align-items:center;
    padding:10px 0;
    z-index:9999;
    height:56px;
    box-shadow:0 -1px 4px rgba(0,0,0,.05);
  }
  .bottom-nav .nav-item{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    color:var(--text,#333);
    font-size:12px;
    flex:1;
    height:100%;
  }
  .bottom-nav .nav-item i{
    display:block;
    font-size:18px;
    margin-bottom:4px;
  }
  .bottom-nav .nav-item.active{
    color:var(--primary,#f04f32);
    font-weight:600;
  }

  /* === OVERRIDES: ensure nav sits above backdrops but keep theme colors === */
  .bottom-nav{
    z-index:10020 !important;   /* higher than backdrops/sheets */
    isolation:isolate;          /* block blending from overlays */
  }
  .bottom-nav::before{
    content:"";
    position:absolute;
    inset:0;
    background:inherit;          /* inherit var(--card) color (light OR dark) */
    z-index:-1;
  }
</style>


<div class="bottom-nav">
  <a class="nav-item <?= $isIndex ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">
    <i class="fas fa-utensils"></i>Order Now
  </a>
  <a class="nav-item <?= $isAbout ? 'active' : '' ?>" href="<?= BASE_URL ?>about.php">
    <i class="fas fa-info-circle"></i>About
  </a>
  <a class="nav-item <?= $isReorder ? 'active' : '' ?>" href="<?= BASE_URL ?>reorder.php">
    <i class="fas fa-history"></i>Reorder
  </a>
  <a class="nav-item <?= $isAccount ? 'active' : '' ?>" href="<?= BASE_URL . $accountTarget ?>">
    <i class="fas fa-user"></i>Account
  </a>
</div>
