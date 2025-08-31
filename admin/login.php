<?php
require_once __DIR__ . '/../config.php';

// ---- Safe session bootstrap ----
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() !== PHP_SESSION_ACTIVE) {
  $cookieParams = ['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>$secure,'httponly'=>true];
  if (PHP_VERSION_ID >= 70300) { $cookieParams['samesite'] = 'Lax'; }
  session_set_cookie_params($cookieParams);
  session_start();
} else {
  if (PHP_VERSION_ID >= 70300) {
    setcookie(session_name(), session_id(), ['expires'=>0,'path'=>'/','domain'=>'','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
  } else {
    setcookie(session_name(), session_id(), 0, '/', '', $secure, true);
  }
}

/* ---- Paths that work in subfolders ----
   e.g. if this file is /websites/shop/admin/login.php
   $BASE = /websites/shop   $ADMIN = /websites/shop/admin
*/
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');      // /websites/shop/admin
$BASE      = rtrim(dirname($scriptDir), '/');              // /websites/shop  ('' if at domain root)
$ADMIN     = $BASE . '/admin';
$HOME_URL  = ($BASE === '' ? '/' : $BASE . '/');
$LOGO_URL  = $BASE . '/assets/images/logo.jpg';

// Already logged in? Go to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
  header('Location: ' . $ADMIN . '/');
  exit;
}

// CSRF token
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$window   = 15 * 60; // 15 minutes
$attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'first' => time()];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (time() - $attempts['first'] > $window) {
    $attempts = ['count' => 0, 'first' => time()];
  }

  if ($attempts['count'] >= 5) {
    $error = 'Too many failed attempts. Please try again in a few minutes.';
  } else {
    $csrf_ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    if (!$csrf_ok) {
      $error = 'Invalid session. Please refresh and try again.';
    } else {
      $email = trim((string)($_POST['email'] ?? ''));
      $pass  = (string)($_POST['password'] ?? '');

      $email_ok = defined('ADMIN_EMAIL') && (strcasecmp($email, ADMIN_EMAIL) === 0);
      $pass_ok  = defined('ADMIN_PASS_HASH') && password_verify($pass, ADMIN_PASS_HASH);

      if ($email_ok && $pass_ok) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email']     = ADMIN_EMAIL;
        $_SESSION['login_attempts']  = ['count' => 0, 'first' => time()];

        $r = $_GET['r'] ?? '';
        $is_safe = is_string($r) && strpos($r, $ADMIN) === 0; // only allow redirects under /.../admin
        header('Location: ' . ($is_safe ? $r : $ADMIN . '/'));
        exit;
      } else {
        $attempts['count']++;
        $_SESSION['login_attempts'] = $attempts;
        $remaining = max(0, 5 - $attempts['count']);
        $error = $remaining > 0
          ? "Incorrect email or password. {$remaining} attempt(s) left."
          : 'Too many failed attempts. Please try again in a few minutes.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($_COOKIE['theme'] ?? 'light'); ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <?php if (file_exists(__DIR__ . '/../partials/theme-head.php')) include __DIR__ . '/../partials/theme-head.php'; ?>
  <title>Admin Login • Your Restaurant</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <style>
    :root { --primary:#f04f32; }
    html { scroll-behavior:smooth; }
    body{ font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:0; min-height:100vh; background:#f7f7f8; display:flex; align-items:center; justify-content:center; }
    [data-theme="dark"] body{ background:#0f1113; }
    .wrap{ width:100%; max-width:420px; padding:20px; }
    .brand{ text-align:center; margin-bottom:12px; }
    .logo{ width:86px; height:86px; border-radius:50%; overflow:hidden; margin:0 auto 10px; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,.15); background:#fff; }
    .logo img{ width:100%; height:100%; object-fit:cover; display:block; }
    .brand h1{
  font-size:22px;
  margin:6px 0 0;
  color:#fff;           /* make “Admin Login” white */
}
    .card{ background:#fff; border:1px solid #e6e6e6; border-radius:16px; box-shadow:0 4px 14px rgba(0,0,0,.06); padding:18px; }
    [data-theme="dark"] .card{ background:#131417; border-color:#1e1f25; box-shadow:0 2px 10px rgba(0,0,0,.55); }
    .top-note{ background:#fff8e6; color:#7a4b00; border-left:4px solid #f0ad4e; padding:10px 12px; border-radius:10px; margin-bottom:12px; font-size:13px; }
    .error{ background:#ffe6e6; color:#8a0000; border-left:4px solid #e40000; padding:10px 12px; border-radius:10px; margin-bottom:12px; font-size:13px; }
    .field{ margin:12px 0; }
    .label{ font-weight:600; font-size:13px; margin-bottom:6px; color:#333; }
    [data-theme="dark"] .label{ color:#e8e8e8; }
    .input{ width:100%; border:1px solid #ddd; background:#fff; color:#222; border-radius:10px; padding:12px 14px; font-size:15px; box-sizing:border-box; outline:none; }
    [data-theme="dark"] .input{ background:#1e1f22; border-color:#2b2d31; color:#e8e8e8; }
    .pw-wrap{ position:relative; }
    .pw-toggle{ position:absolute; right:10px; top:50%; transform:translateY(-50%); background:transparent; border:none; cursor:pointer; color:#777; font-size:16px; }
    [data-theme="dark"] .pw-toggle{ color:#b9b9b9; }
    .btn-orange{ width:100%; padding:12px 16px; border:none; border-radius:10px; background:var(--primary); color:#fff; font-weight:700; font-size:15px; cursor:pointer; }
    .meta{ display:flex; align-items:center; justify-content:space-between; font-size:13px; color:#666; margin-top:8px; }
    [data-theme="dark"] .meta{ color:#bbb; }
    .foot{ text-align:center; margin-top:10px; font-size:13px; color:#666; }
    [data-theme="dark"] .foot{ color:#bbb; }
    .link{ color:var(--primary); text-decoration:none; font-weight:600; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="brand">
      <div class="logo">
        <img src="<?php echo htmlspecialchars($LOGO_URL, ENT_QUOTES); ?>" alt="Logo">
      </div>
      <h1>Admin Login</h1>
    </div>

    <div class="card">
      <div class="top-note"><i class="fa-solid fa-shield-halved"></i> For staff only.</div>

      <?php if (!empty($error)): ?>
        <div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off" novalidate>
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">

        <div class="field">
          <div class="label">Email</div>
          <input class="input" type="email" name="email" inputmode="email" required placeholder="owner@your-restaurant.com" />
        </div>

        <div class="field">
          <div class="label">Password</div>
          <div class="pw-wrap">
            <input id="pw" class="input" type="password" name="password" required placeholder="••••••••••" />
            <button type="button" class="pw-toggle" aria-label="Show password" onclick="togglePw()">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
        </div>

        <button class="btn-orange" type="submit">
          <i class="fa-solid fa-right-to-bracket"></i>&nbsp; Sign in
        </button>

        <div class="meta">
          <span><i class="fa-solid fa-clock"></i> Session secured</span>
          <a class="link" href="<?php echo htmlspecialchars($HOME_URL, ENT_QUOTES); ?>" title="Back to website">Back to site</a>
        </div>
      </form>
    </div>

    <div class="foot">
      <a class="link" href="<?php echo htmlspecialchars($ADMIN . '/password-help.txt', ENT_QUOTES); ?>" target="_blank" rel="noopener">Forgot password?</a>
    </div>
  </div>

  <script>
    function togglePw(){
      const pw = document.getElementById('pw');
      const btn = event.currentTarget;
      const icon = btn.querySelector('i');
      if(pw.type === 'password'){ pw.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); btn.setAttribute('aria-label','Hide password'); }
      else { pw.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); btn.setAttribute('aria-label','Show password'); }
    }
  </script>
</body>
</html>

