<?php
require_once __DIR__ . '/../config.php';

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() !== PHP_SESSION_ACTIVE) {
  $cookieParams = ['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>$secure,'httponly'=>true];
  if (PHP_VERSION_ID >= 70300) { $cookieParams['samesite'] = 'Lax'; }
  session_set_cookie_params($cookieParams);
  session_start();
}

$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/'); // e.g. /websites/shop/admin
$BASE  = rtrim(dirname($scriptDir), '/');             // /websites/shop
$ADMIN = $BASE . '/admin';

if (empty($_SESSION['admin_logged_in'])) {
  $req = $_SERVER['REQUEST_URI'] ?? $ADMIN . '/';
  header('Location: ' . $ADMIN . '/login.php?r=' . urlencode($req));
  exit;
}
