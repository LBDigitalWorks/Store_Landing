<?php
// logout.php — destroy session + redirect to auth
require_once __DIR__ . '/config.php'; // starts the session

// Wipe all session data
$_SESSION = [];
unset($_SESSION['user']);

// Kill the session cookie
if (ini_get('session.use_cookies')) {
  $p = session_get_cookie_params();
  setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Destroy the session
session_destroy();

// Where to go next (whitelist to avoid open redirects)
$next = $_GET['next'] ?? 'auth.php';
$allowed = ['auth.php','index.php','menu.php'];
if (!in_array($next, $allowed, true)) {
  $next = 'auth.php';
}

// Redirect
header('Location: ' . $next);
exit;
