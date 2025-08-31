<?php
// auth.php — Sign in / Sign up with Google, Apple, Facebook (social buttons BELOW signup)
// reorder.php — Just-Eat style list with image, full history, reorder -> checkout
require_once __DIR__ . '/config.php';  // <-- ensure this is first
date_default_timezone_set('Europe/London');

const USERS_DB = __DIR__ . '/data/users.json';

// ---- OAUTH CONFIG (fill these) ----
const GOOGLE_CLIENT_ID    = '153992415074-equfjnb24t97m5lpsbqkrvk55r47be9b.apps.googleusercontent.com';
// -----------------------------------

// ---------- After-login destination (supports ?next=...) ----------
$default_after_login = 'account.php';
$next = $_POST['next'] ?? $_GET['next'] ?? $default_after_login;
// whitelist to avoid open redirects
$allowed_next = ['account.php','index.php','reorder.php','about.php','auth.php'];
if (!in_array($next, $allowed_next, true)) {
  $next = $default_after_login;
}

// Tiny JSON user store
function load_users(){ return file_exists(USERS_DB) ? (json_decode(file_get_contents(USERS_DB), true) ?: []) : []; }
function save_users($u){ if(!is_dir(dirname(USERS_DB))) mkdir(dirname(USERS_DB),0777,true); file_put_contents(USERS_DB, json_encode($u, JSON_PRETTY_PRINT)); }
function find_user_by_email($email){ $u=load_users(); $k=strtolower(trim($email)); return $u[$k]??null; }
function create_user($email,$hash,$name='',$provider='password'){
  $u=load_users(); $k=strtolower(trim($email));
  if(isset($u[$k])) return false;
  $u[$k]=['email'=>$k,'name'=>$name,'password_hash'=>$hash,'provider'=>$provider,'created_at'=>date('c')];
  save_users($u); return $u[$k];
}
function sign_in($user){ $_SESSION['user']=['email'=>$user['email'],'name'=>$user['name']??'','provider'=>$user['provider']??'password']; }

$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??''; $email=trim($_POST['email']??''); $pw=$_POST['password']??''; $name=trim($_POST['name']??'');
  if($action==='signin'){
    $u=find_user_by_email($email);
    if(!$u || empty($u['password_hash']) || !password_verify($pw,$u['password_hash'])) $error='Invalid email or password.';
    else { sign_in($u); header('Location: ' . $next); exit; }
  }
  if($action==='signup'){
    if(!$email || !$pw) $error='Please enter email and password.';
    elseif(find_user_by_email($email)) $error='An account with that email already exists.';
    else {
      $u=create_user($email,password_hash($pw,PASSWORD_DEFAULT),$name,'password');
      if($u){ sign_in($u); header('Location: ' . $next); exit; }
      else $error='Could not create account.';
    }
  }
}

// URLs
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
$root   = $scheme.'://'.$host.($base==='/'?'':$base);

// Google server endpoint (pass ?next=...)
$googleLoginURL = $root.'/google-login.php?next='.rawurlencode($next);

// Apple/Facebook start endpoints
$appleStartURL    = $root.'/oauth/apple-start.php';
$facebookStartURL = $root.'/oauth/facebook-start.php';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php if (file_exists(__DIR__ . '/partials/theme-head.php')) include __DIR__ . '/partials/theme-head.php'; ?>
<title>Account</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
/* sizing sanity so inputs/buttons stay inside the card */
*,*::before,*::after{ box-sizing:border-box; }

:root{
  --bg:#f9f9f9; --text:#111; --muted:#666; --line:#ddd;
  --card:#fff; --shadow:0 1px 3px rgba(0,0,0,.1); --primary:#ff5722;
}

/* page */
html,body{ margin:0; padding:0; font-family:'Segoe UI',Tahoma,Verdana,sans-serif; background:var(--bg); color:var(--text); }
.auth-wrap{ min-height:calc(100svh - 56px); padding:20px 20px 88px; display:flex; align-items:center; justify-content:center; }
.about-card{ background:var(--card); border-radius:10px; box-shadow:var(--shadow); padding:18px; overflow:hidden; border:1px solid var(--line); }
.auth-card{ max-width:440px; width:100%; }
.h1{ font-size:22px; font-weight:800; margin:0 0 12px; }

/* tabs */
.tabs{ display:flex; gap:8px; margin:12px 0; }
.tab{ flex:1; text-align:center; padding:10px; border-radius:8px; border:1px solid var(--line); background:var(--card); color:var(--text); font-weight:700; cursor:pointer; }
.tab.active{ background:var(--primary); border-color:var(--primary); color:#fff; }

/* forms */
.stack{ gap:16px; width:100%; }
.stack > * + *{ margin-top:16px; }
.input{ width:100%; padding:12px; border:1px solid var(--line); border-radius:8px; font-size:16px; background:var(--card); color:var(--text); outline:none; }
.btn{
  display:inline-flex; align-items:center; justify-content:center; gap:10px;
  padding:12px; border-radius:8px; border:1px solid var(--line);
  background:var(--card); color:var(--text); font-weight:700; font-size:16px; text-decoration:none; width:100%;
}
.btn.primary{ background:var(--primary); border-color:var(--primary); color:#fff; }
.divider{ height:1px; background:var(--line); margin:18px 0; }
.center{ text-align:center; }
.small{ font-size:13px; color:var(--muted); }

/* alert */
.alert{ background:#fff3cd; border:1px solid #ffe69c; color:#7a5d00; border-radius:8px; padding:10px; margin-bottom:12px; }
[data-theme="dark"] .alert{ background:#2a2616; border-color:#63551a; color:#f5e6a7; }

/* Social buttons — below signup */
.btn.social i{ font-size:18px; }
.btn.google{ background:var(--card); border-color:var(--line); }
.btn.apple{ background:#000; color:#fff; border-color:#000; }
.btn.facebook{ background:#1877F2; color:#fff; border-color:#1877F2; }

/* Make Google’s official button align visually & full-width */
.gwrap{ display:flex; }
.g_id_signin{ width:100%; display:flex; justify-content:center; }
.g_id_signin > div{ width:100% !important; } /* stretch button to full width */

/* --- Robust visibility control (no more random reappearing) --- */
#form-in, #form-up{ display:none; }
body[data-auth="in"]  #form-in{ display:block; }
body[data-auth="up"]  #form-up{ display:block; }
</style>

<!-- Google Identity Services -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<!-- Default to sign-in view -->
<body data-auth="in">

<div class="auth-wrap">
  <section class="about-card auth-card">
    <div class="h1">Account</div>

    <!-- Tabs -->
    <div class="tabs" role="tablist" aria-label="Auth tabs">
      <button class="tab active" id="tab-in"  type="button" role="tab" aria-selected="true"  aria-controls="form-in">Sign in</button>
      <button class="tab"        id="tab-up"  type="button" role="tab" aria-selected="false" aria-controls="form-up">Create account</button>
    </div>

    <?php if(!empty($error)): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Sign in -->
    <form id="form-in" method="post" class="stack">
      <input type="hidden" name="action" value="signin">
      <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
      <input class="input" type="email" name="email" placeholder="Email" required>
      <input class="input" type="password" name="password" placeholder="Password" required>
      <button class="btn primary" type="submit">Sign in</button>
    </form>

    <!-- Sign up -->
    <form id="form-up" method="post" class="stack">
      <input type="hidden" name="action" value="signup">
      <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
      <input class="input" type="text" name="name" placeholder="Full name (optional)">
      <input class="input" type="email" name="email" placeholder="Email" required>
      <input class="input" type="password" name="password" placeholder="Password" required>
      <button class="btn primary" type="submit">Create account</button>
    </form>

    <!-- SOCIAL BUTTONS (below Sign up) -->
    <div class="divider"></div>
    <div class="center small">or continue with</div>
    <div class="divider"></div>

    <div class="stack social-section">
      <!-- Google official button -->
      <div class="gwrap">
        <div id="g_id_onload"
             data-client_id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"
             data-login_uri="<?= htmlspecialchars($googleLoginURL) ?>"
             data-auto_prompt="false"></div>
        <div class="g_id_signin"
             data-type="standard"
             data-shape="rectangular"
             data-theme="outline"
             data-text="continue_with"
             data-size="large"
             data-logo_alignment="left">
        </div>
      </div>

     
    <!-- /SOCIAL -->
  </section>
</div>

<script>
  // Single source of truth for which form is visible
  const body   = document.body;
  const tabIn  = document.getElementById('tab-in');
  const tabUp  = document.getElementById('tab-up');

  function setAuth(mode){
    body.setAttribute('data-auth', mode);
    const isIn = mode === 'in';
    tabIn.classList.toggle('active', isIn);
    tabUp.classList.toggle('active', !isIn);
    tabIn.setAttribute('aria-selected', isIn ? 'true' : 'false');
    tabUp.setAttribute('aria-selected', !isIn ? 'true' : 'false');
  }

  tabIn?.addEventListener('click', ()=> setAuth('in'));
  tabUp?.addEventListener('click', ()=> setAuth('up'));

  // Ensure initial state (in case browser restored wrong state)
  setAuth('in');
</script>

<?php
// bottom nav + theme toggle
$navPath = __DIR__ . '/partials/bottom-nav.php';
if (file_exists($navPath)) include $navPath;

$togglePath = __DIR__ . '/partials/theme-toggle.php';
if (file_exists($togglePath)) include $togglePath;
?>
</body>
</html>



