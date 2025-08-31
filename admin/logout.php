<?php
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() !== PHP_SESSION_ACTIVE) {
  $cookieParams = ['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>$secure,'httponly'=>true];
  if (PHP_VERSION_ID >= 70300) { $cookieParams['samesite'] = 'Lax'; }
  session_set_cookie_params($cookieParams);
  session_start();
}

$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/'); // /websites/shop/admin
$BASE  = rtrim(dirname($scriptDir), '/');             // /websites/shop
$ADMIN = $BASE . '/admin';

// Clear session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: ' . $ADMIN . '/login.php');
exit;
