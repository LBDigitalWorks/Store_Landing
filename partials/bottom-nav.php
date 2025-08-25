<?php
// partials/bottom-nav.php

// Ensure config is loaded even if the page forgot to include it
if (!defined('BASE_URL') || !defined('ADMIN_PATH')) {
  $configPath = dirname(__DIR__) . '/config.php'; // one level up from /partials
  if (file_exists($configPath)) {
    require_once $configPath;
  }
}

// Safe fallbacks
if (!defined('BASE_URL'))  define('BASE_URL', 'https://lbdigitalworks.com/websites/shop/'); // keep trailing slash
if (!defined('ADMIN_PATH')) define('ADMIN_PATH', 'Admin_CP/'); // change if your admin folder name differs

// Active tab detection
$script    = basename($_SERVER['SCRIPT_NAME'] ?? '');
$uri       = $_SERVER['REQUEST_URI'] ?? '';
$adminSlug = trim(ADMIN_PATH, '/');

$isIndex   = ($script === 'index.php');
$isAbout   = ($script === 'about.php');
$isReorder = ($script === 'reorder.php');
$isAdmin   = ($adminSlug && strpos($uri, '/' . $adminSlug) !== false);
?>
<style>
  .bottom-nav {
    position: fixed; bottom: 0; left: 0; width: 100%; background: white; border-top: 1px solid #ccc;
    display: flex; justify-content: space-around; align-items: center; padding: 10px 0; z-index: 9999; height: 40px;
    box-shadow: 0 -1px 4px rgba(0,0,0,0.05);
  }
  .bottom-nav .nav-item {
    text-decoration: none; color: #333; display: block; text-align: center; font-size: 12px;
  }
  .bottom-nav .nav-item i { display: block; font-size: 18px; margin-bottom: 4px; }
  .bottom-nav .nav-item.active { color: #f04f32; font-weight: 600; }
</style>

<div class="bottom-nav">
  <!-- Order Now (jumps to menu on index) -->
  <a class="nav-item <?= $isIndex ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">
    <i class="fas fa-utensils"></i>Order Now
  </a>

  <!-- About page -->
  <a class="nav-item <?= $isAbout ? 'active' : '' ?>" href="<?= BASE_URL ?>about.php">
    <i class="fas fa-info-circle"></i>About
  </a>

  <!-- Reorder page -->
  <a class="nav-item <?= $isReorder ? 'active' : '' ?>" href="<?= BASE_URL ?>reorder.php">
    <i class="fas fa-history"></i>Reorder
  </a>

  <!-- Admin area -->
  <a class="nav-item <?= $isAdmin ? 'active' : '' ?>" href="<?= BASE_URL . ADMIN_PATH ?>">
    <i class="fas fa-ellipsis-h"></i>More
  </a>
</div>
