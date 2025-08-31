<?php
// about.php — About page with map pin, hygiene badge, times & fees

// 1) Load shared config (defines BASE_URL, ADMIN_PATH, $address, $postcode, etc.)
$cfg = __DIR__ . '/config.php';
if (file_exists($cfg)) {
  require_once $cfg;
}

// 2) Safe fallbacks in case config.php is missing or variables aren't set
$address        = $address        ?? '123 Takeaway Street';
$postcode       = $postcode       ?? 'Doncaster DN12 3AA';
$hygiene_rating = $hygiene_rating ?? '5';
$delivery_eta   = $delivery_eta   ?? '30–45 min';
$collection_eta = $collection_eta ?? '15–20 min';
$delivery_fee   = $delivery_fee   ?? '£1.99';
$min_order      = $min_order      ?? '£12.00';
$extra_fee_note = $extra_fee_note ?? 'Additional distance fee may apply';
$lat            = $lat            ?? 52.596606;
$lng            = $lng            ?? -1.031949;

// 3) Helper functions (only define if not already present from config.php)
if (!function_exists('map_embed_url')) {
  function map_embed_url(string $address, string $postcode, ?float $lat=null, ?float $lng=null): string {
    if ($lat !== null && $lng !== null) return "https://www.google.com/maps?q={$lat},{$lng}&z=18&output=embed";
    return "https://www.google.com/maps?q=" . urlencode($address.' '.$postcode) . "&z=18&output=embed";
  }
}
if (!function_exists('link_to_maps')) {
  function link_to_maps(string $address, string $postcode): string {
    return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address.' '.$postcode);
  }
}

// 4) Asset URL fallback (absolute) for hygiene image
if (!defined('BASE_URL')) define('BASE_URL', 'https://lbdigitalworks.com/websites/shop/');
$food_hygiene_img_url = $food_hygiene_img_url ?? rtrim(BASE_URL, '/') . '/assets/images/foodrating.png?v=5';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

<?php include __DIR__ . '/partials/theme-head.php'; ?> 

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<title>About</title>
<style>
  /* make page colors respond to theme */
  body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:0; overflow-x:hidden; background:var(--bg); color:var(--text); }

  .banner { position:relative; width:100%; height:200px; background:url('assets/images/burgers2.jfif') no-repeat center/cover; box-shadow:0 2px 5px rgba(0,0,0,0.2); }
  .banner::after { content:''; position:absolute; inset:0; background:rgba(0,0,0,0.4); }
  .icons { position:absolute; top:15px; inset-inline:15px; display:flex; justify-content:space-between; z-index:2; }
  .icons i { color:#fff; font-size:18px; background:rgba(0,0,0,0.4); padding:8px; border-radius:50%; }
  .logo-container { position:absolute; bottom:-30px; left:50%; transform:translateX(-50%); width:100px; height:100px; border-radius:50%; overflow:hidden; border:3px solid #fff; z-index:3; box-shadow:0 2px 5px rgba(0,0,0,0.2); }
  .logo-container img { width:100%; height:100%; object-fit:cover; }

  .container { padding:20px 20px 80px; text-align:center; margin-top:10px; }
  .restaurant-name { font-size:24px; font-weight:600; margin:10px 0 2px; }
  .address { color:#555; font-size:14px; margin:0; display:flex; justify-content:center; align-items:center; gap:5px; }
  .rating { color:#f6b100; font-weight:600; font-size:15px; margin:8px 0 20px; }

  .section{margin:12px 15px;}
  .about-card{background:#fff;padding:12px;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,0.1);}
  .row{display:flex;align-items:center;gap:10px;}
  .space{justify-content:space-between;}
  .muted{color:#666;}
  .btn{display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border-radius:8px;border:1px solid #ddd;background:#f7f7f7;font-weight:600;color:#111;text-decoration:none;font-size:14px;}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
  @media(max-width:520px){.grid2{grid-template-columns:1fr 1fr}}
  .big{font-size:20px;font-weight:700;}
  .badge{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:1px solid #eee;background:#fafafa;font-size:14px;}

  /* THEME-AWARE MAP (no API key required)
     We “darken” the standard embed in dark mode using a safe CSS filter.
     When your project has Google Maps map IDs for light/dark, you can swap the src
     instead—this works immediately without any keys. */
  .map-wrap{ aspect-ratio:16/10; background:#e9e9e9; }
  .map-frame{ width:100%; height:100%; border:0; display:block; filter:none; }
  [data-theme="dark"] .map-wrap{ background:#0e1116; }
  [data-theme="dark"] .map-frame{
    /* tasteful darkening without making labels unreadable */
    filter: invert(0.9) hue-rotate(180deg) saturate(0.5) brightness(0.9) contrast(0.9);
  }

  /* Dark theme card/borders */
  [data-theme="dark"] .about-card{ background:#151a1f; border:1px solid #27303a; box-shadow:none; }
  [data-theme="dark"] .muted{ color:#9aa4b2; }
  [data-theme="dark"] .btn{ background:#1b222b; color:var(--text); border-color:#27303a; }
</style>
</head>
<body>

<!-- Top banner (optional) -->
<div class="banner">
  <div class="icons"><i class="fas fa-arrow-left"></i><i class="fas fa-search"></i></div>
  <div class="logo-container"><img src="assets/images/logo.jpg" alt="Logo"></div>
</div>

<div class="container" id="about">
  <div class="restaurant-name">Your Restaurant Name</div>
  <p class="address"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($address) ?>, <?= htmlspecialchars($postcode) ?></p>
  <div class="rating"><i class="fas fa-star"></i> 4.6 (44 reviews)</div>

  <!-- 1) GOOGLE MAP WITH PIN (theme-aware) -->
  <section class="section about-card" style="overflow:hidden;padding:0">
    <div class="map-wrap">
      <iframe
        class="map-frame"
        title="Map"
        loading="lazy"
        src="<?= htmlspecialchars(map_embed_url($address, $postcode, $lat, $lng)) ?>">
      </iframe>
    </div>
    <div style="padding:12px" class="row space">
      <div class="row"><span>📍</span><span class="muted"><?= htmlspecialchars($address) ?>, <?= htmlspecialchars($postcode) ?></span></div>
      <a class="btn" href="<?= htmlspecialchars(link_to_maps($address, $postcode)) ?>" target="_blank" rel="noopener">Directions ↗</a>
    </div>
  </section>

  <!-- 2) UK FOOD HYGIENE RATING -->
  <section class="section about-card">
    <div class="row" style="gap:12px;flex-wrap:wrap">
      <img
        src="<?= htmlspecialchars($food_hygiene_img_url) ?>"
        alt="Food Hygiene Rating <?= htmlspecialchars($hygiene_rating) ?>"
        style="height:130px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.2);background:#fff;display:block"
      >
    </div>
  </section>

  <!-- 3) DELIVERY & COLLECTION TIMES -->
  <section class="section">
    <h2 style="text-align:left;margin:0 0 10px 4px;">Delivery & Collection Times</h2>
    <div class="grid2">
      <div class="about-card">
        <div class="row"><span>🚚</span><strong>Delivery</strong></div>
        <div class="muted" style="margin-top:6px">Estimated time</div>
        <div class="big"><?= htmlspecialchars($delivery_eta) ?></div>
      </div>
      <div class="about-card">
        <div class="row"><span>👜</span><strong>Collection</strong></div>
        <div class="muted" style="margin-top:6px">Estimated time</div>
        <div class="big"><?= htmlspecialchars($collection_eta) ?></div>
      </div>
    </div>
  </section>

  <!-- 4) OPENING TIMES -->
  <section class="section about-card">
    <h2 style="text-align:left;margin:0 0 10px 4px;">Opening Times</h2>
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <tbody>
      <?php
        $opening_hours = [
          'Monday'    => '16:00 – 22:30',
          'Tuesday'   => '16:00 – 22:30',
          'Wednesday' => '16:00 – 22:30',
          'Thursday'  => '16:00 – 23:00',
          'Friday'    => '16:00 – 23:30',
          'Saturday'  => '16:00 – 23:30',
          'Sunday'    => '16:00 – 22:00',
        ];
        $today = date('l');
        foreach ($opening_hours as $day => $hours):
          $isToday = ($day === $today);
      ?>
        <tr style="border-bottom:1px solid var(--line);<?= $isToday ? 'font-weight:600;color:var(--text);' : '' ?>">
          <td style="padding:6px 0;width:100px;"><?= htmlspecialchars($day) ?></td>
          <td style="padding:6px 0;"><?= htmlspecialchars($hours) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <!-- 5) DELIVERY FEES -->
  <section class="section about-card">
    <h2 style="text-align:left;margin:0 0 10px 4px;">Delivery Fees</h2>
    <div class="row" style="flex-wrap:wrap;gap:10px">
      <div class="badge">Delivery fee from <strong><?= htmlspecialchars($delivery_fee) ?></strong></div>
      <div class="badge">Minimum order <strong><?= htmlspecialchars($min_order) ?></strong></div>
      <?php if(!empty($extra_fee_note)): ?>
        <div class="badge muted"><?= htmlspecialchars($extra_fee_note) ?></div>
      <?php endif; ?>
    </div>
    <p class="muted" style="margin-top:10px">Fees may vary by distance and time.</p>
  </section>
</div>

<?php include __DIR__ . '/partials/bottom-nav.php'; ?>
<?php include __DIR__ . '/partials/theme-toggle.php'; ?>

</body>
</html>

