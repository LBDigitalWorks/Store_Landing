<?php
// google-login.php — handles POST from Google Identity Services button (auth.php data-login_uri points here)
session_start();
date_default_timezone_set('Europe/London');

// ==== CONFIG ====
const USERS_DB = __DIR__ . '/data/users.json';
const GOOGLE_CLIENT_ID = '153992415074-equfjnb24t97m5lpsbqkrvk55r47be9b.apps.googleusercontent.com';

// Where to go after login (can be passed as ?next=account.php etc.)
$default_after_login = 'account.php';
$next = $_GET['next'] ?? $_POST['next'] ?? $default_after_login;
$allowed_next = ['account.php','index.php','reorder.php','about.php','auth.php'];
if (!in_array($next, $allowed_next, true)) $next = $default_after_login;

// ==== Tiny JSON user store helpers (match your auth.php) ====
function load_users(){ return file_exists(USERS_DB) ? (json_decode(file_get_contents(USERS_DB), true) ?: []) : []; }
function save_users($u){ if(!is_dir(dirname(USERS_DB))) mkdir(dirname(USERS_DB),0777,true); file_put_contents(USERS_DB, json_encode($u, JSON_PRETTY_PRINT)); }
function find_user_by_email($email){ $u=load_users(); $k=strtolower(trim($email)); return $u[$k]??null; }
function create_or_update_social_user($email,$name,$provider='google'){
  $u=load_users(); $k=strtolower(trim($email));
  if(!isset($u[$k])){
    $u[$k]=['email'=>$k,'name'=>$name,'provider'=>$provider,'created_at'=>date('c')];
  }else{
    // keep existing provider if set; update name if empty
    if (empty($u[$k]['provider'])) $u[$k]['provider'] = $provider;
    if (empty($u[$k]['name']) && $name) $u[$k]['name'] = $name;
  }
  save_users($u); return $u[$k];
}
function sign_in($user){ $_SESSION['user']=['email'=>$user['email'],'name'=>$user['name']??'','provider'=>$user['provider']??'google']; }

// ==== Require POST with credential ====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

$credential = $_POST['credential'] ?? '';
if (!$credential) {
  http_response_code(400);
  exit('Missing Google credential');
}

// (Recommended) CSRF check for GIS one-tap / button flows:
if (isset($_POST['g_csrf_token'], $_COOKIE['g_csrf_token'])) {
  if (!hash_equals($_COOKIE['g_csrf_token'], $_POST['g_csrf_token'])) {
    http_response_code(400);
    exit('Bad CSRF token');
  }
}

// Validate ID token with Google tokeninfo endpoint
// NOTE: tokeninfo is fine for server-side verification in small apps. For high-scale, verify JWT locally via JWKS.
$verifyUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
$ch = curl_init($verifyUrl);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 10,
  CURLOPT_SSL_VERIFYPEER => true,
  CURLOPT_SSL_VERIFYHOST => 2,
]);
$resp = curl_exec($ch);
if ($resp === false) {
  http_response_code(502);
  exit('Failed to contact Google for verification');
}
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code !== 200) {
  http_response_code(401);
  exit('Invalid Google token (tokeninfo rejected)');
}

$payload = json_decode($resp, true);
if (!is_array($payload)) {
  http_response_code(401);
  exit('Invalid Google token (bad payload)');
}

// Required checks
$aud = $payload['aud'] ?? '';
$iss = $payload['iss'] ?? '';
$exp = isset($payload['exp']) ? (int)$payload['exp'] : 0;

if ($aud !== GOOGLE_CLIENT_ID) {
  http_response_code(401);
  exit('Audience mismatch');
}
if ($iss !== 'https://accounts.google.com' && $iss !== 'accounts.google.com') {
  http_response_code(401);
  exit('Issuer mismatch');
}
if ($exp < time()) {
  http_response_code(401);
  exit('Token expired');
}

// Extract user info
$email = $payload['email'] ?? '';
$name  = $payload['name']  ?? ($payload['given_name'] ?? '');
$emailVerified = ($payload['email_verified'] ?? 'false') === 'true' || ($payload['email_verified'] ?? false) === true;

if (!$email || !$emailVerified) {
  http_response_code(403);
  exit('Email not present or not verified by Google');
}

// Create / update user, sign in
$user = create_or_update_social_user($email, $name, 'google');
sign_in($user);

// Redirect back
header('Location: ' . $next);
exit;

