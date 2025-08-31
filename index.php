<?php
require_once __DIR__ . '/config.php';

// Business hours: 5 PM to 11 PM
$current_hour = date("H");
$restaurant_open = ($current_hour >= 17 && $current_hour < 23);

$recommended_items = [
    ['name' => 'Pilau Rice', 'price' => 5.00],
    ['name' => 'Onion Bhaji', 'price' => 4.50],
    ['name' => 'Paneer Tikka', 'price' => 6.50],
    ['name' => 'Chicken Biryani', 'price' => 7.00],
    ['name' => 'Garlic Naan', 'price' => 3.50],
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
    
<head>
    
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <?php include __DIR__ . '/partials/theme-head.php';?> 
  <title>Your Restaurant</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
  /* ===== Dark-only base (uses your CSS vars from theme-head.php) ===== */
  html, body {
    background: var(--bg, #0f1113);
    color: var(--text, #e7e7e7);
  }
  html { scroll-behavior: smooth; }
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin:0; padding:0; overflow-x:hidden;
    background: var(--bg, #0f1113);
    color: var(--text, #e7e7e7);
  }

  /* ===== Banner / logo ===== */
  .banner{
    position:relative; width:100%; height:200px;
    background:url('assets/images/burgers2.jfif') no-repeat center/cover;
    box-shadow:0 2px 5px rgba(0,0,0,0.45);
  }
  .banner::after{ content:''; position:absolute; inset:0; background:rgba(0,0,0,.45); }
  .logo-container{ position:absolute; bottom:-30px; left:50%; transform:translateX(-50%);
    width:100px; height:100px; border-radius:50%; overflow:hidden; border:3px solid #fff;
    z-index:3; box-shadow:0 2px 5px rgba(0,0,0,0.5); }
  .logo-container img{ width:100%; height:100%; object-fit:cover; }

  .container{ padding:20px 20px 80px; text-align:center; margin-top:10px; }
  .restaurant-name{ font-size:24px; font-weight:600; margin:10px 0 2px; }
  .address{ color:#b9b9b9; font-size:14px; margin:0; display:flex; justify-content:center; align-items:center; gap:5px; }
  .rating{ color:#f6b100; font-weight:600; font-size:15px; margin:8px 0 20px; }

  /* ===== Pills / chips ===== */
  .buttons{ display:flex; justify-content:center; gap:10px; flex-wrap:nowrap; margin-bottom:20px; }
  .btn, .buttons > div{
    display:flex; align-items:center; gap:6px; padding:10px 16px; border-radius:12px;
    background:var(--card,#151a1f) !important; border:1px solid var(--line,#27303a) !important;
    font-size:14px; font-weight:500; color:var(--text,#e7e7e7) !important;
    box-shadow:0 1px 3px rgba(0,0,0,.35);
  }
  .btn i{ font-size:14px; }

  /* ===== Status banners ===== */
  .closed-banner,.open-banner,.alert-note{
    padding:14px 16px; margin:20px auto; border-radius:10px; max-width:90%;
    font-size:14px; text-align:center; font-weight:600; display:flex; justify-content:center; align-items:center; gap:8px;
    box-shadow:0 1px 3px rgba(0,0,0,.25);
  }
  .closed-banner{ background:#2a0e0e; color:#ffb3b3; border:1px solid #3b1616; }
  .open-banner{ background:#0f2a12; color:#9fef9f; border:1px solid #1e5a2a; }
  .alert-note{ background:#2a0e0e; color:#ffb3b3; border:1px solid #3b1616; }

  /* ===== Recommended carousel ===== */
  .recommended-section{ text-align:left; padding:10px 15px; }
  .recommended-items{ position:relative; display:flex; overflow-x:auto; gap:10px; padding-bottom:10px; width:100%; box-sizing:border-box; scroll-snap-type:x mandatory; }
  .recommended-items::after{ content:none; }
  .item-card{
    position:relative; min-width:130px; background:var(--card,#151a1f);
    padding:12px; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.6); text-align:center; flex-shrink:0; scroll-snap-align:start;
    border:1px solid var(--line,#27303a);
  }

  /* Add button on card */
  .item-card .card-add {
    all: unset; position: absolute; bottom: 6px; right: 6px;
    width: 26px; height: 26px; border-radius: 50%;
    background: var(--primary, #f04f32); color: #fff;
    display: flex; align-items: center; justify-content: center; cursor: pointer; box-sizing: border-box; line-height: 1;
  }
  .item-card .card-add i { font-size: 14px; line-height: 1; pointer-events: none; }

  /* ===== Search bar (dark) ===== */
  .menu-search-wrap{
    position:sticky; top:0; z-index:50;
    background:linear-gradient(var(--bg,#0f1113), #0f111300);
    padding:8px 0 2px; transition:position .2s ease;
  }
  .menu-search-wrap.is-focused{ position:static; box-shadow:none; }
  input, select, textarea { font-size:16px; }
  .menu-search{
    margin:8px 15px 10px; display:flex; align-items:center; gap:8px;
    background:#1e1f22; border:1px solid #2c2d31; border-radius:12px; padding:8px 12px;
    box-shadow:0 1px 3px rgba(0,0,0,.5);
    color:#e7e7e7;
  }
  .menu-search i{ color:#b9b9b9; }
  .menu-search input{
    border:none; outline:none; flex:1; font-size:16px; line-height:1.2; background:transparent; color:#e7e7e7;
    -webkit-appearance:none; appearance:none;
  }
  .menu-search input::placeholder{ color:#8b8f96; }

  /* ===== Categories / items ===== */
  .menu-categories{
    margin-top:10px; background:#121315; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.6); overflow:hidden; border:1px solid var(--line,#27303a);
  }
  .category-block{ border-bottom:1px solid #1f2023; }
  .category-row{
    padding:16px; display:flex; justify-content:space-between; align-items:center; font-size:16px; font-weight:600; color:#e8e8e8; cursor:pointer; background:#131417;
  }
  .category-row:hover{ background:#15171a; }
  .category-content{ display:none; padding:0 15px; background:#121315; }
  .category-content .item-row{
    display:flex; justify-content:space-between; align-items:center; gap:12px;
    padding:14px 0; border-bottom:2px solid rgba(255,255,255,.12);
  }
  .category-content .item-row:last-child{ border-bottom:none; }

  /* ===== Bottom nav ===== */
  .bottom-nav{
    position:fixed; bottom:0; left:0; width:100%; background:#151a1f; border-top:1px solid #27303a;
    display:flex; justify-content:space-around; align-items:center; padding:10px 0; z-index:9999; height:40px; box-shadow:0 -2px 10px rgba(0,0,0,0.35);
  }
  .bottom-nav .nav-item{ text-decoration:none; color:#e7e7e7; display:block; text-align:center; font-size:12px; }
  .bottom-nav .nav-item i{ display:block; font-size:18px; margin-bottom:4px; }
  .bottom-nav .nav-item.active{ color:var(--primary,#f04f32); }

  /* ===== Buttons ===== */
  .btn-orange,
  [data-add-to-cart]{
    padding: 6px 12px;
    background: var(--primary, #f04f32);
    border: none;
    color: #fff;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
    font-weight: 800;
    display:inline-flex; align-items:center; justify-content:center;
    line-height:1; min-width:64px;
    box-shadow:0 2px 8px rgba(0,0,0,.25);
  }

  /* Force dark on any inline �light� boxes you had */
  .info-box{
    background:var(--card,#151a1f) !important;
    color:var(--text,#e7e7e7) !important;
    border-left:4px solid var(--line,#27303a) !important;
    box-shadow:0 1px 3px rgba(0,0,0,.35) !important;
  }

  /* Hide blocks in search */
  .category-block.is-hidden { display:none; }

  /* Search hit highlight (dark) */
  mark.search-hit{ background:#6a5e00; color:#fff; padding:0 .1em; border-radius:2px; }

  /* No-results alert (dark) */
  .no-results{
    margin:12px 16px 0; padding:12px; border-radius:10px;
    background:#2a0e0e; color:#ffb3b3; font-size:14px; display:none; border:1px solid #3b1616;
  }

  /* Scroll hint (optional styling tweak for visibility on dark) */
  .scroll-hint{ color:#8b8f96; display:flex; align-items:center; gap:6px; margin:6px 2px; font-size:13px; }

  /* Utility */
  .price{ color:#e7e7e7; }
</style>

</head>
<body>
<div class="banner">
  <!-- removed: .icons with back/search -->
  <div class="logo-container"><img src="assets/images/logo.jpg" alt="Logo"></div>
</div>

<div class="container"> 
  <div class="restaurant-name">Your Restaurant Name</div>
  <div class="address"><i class="fas fa-map-marker-alt"></i> 12 Park Lane, Northside</div>
  <div class="rating"><i class="fas fa-star"></i> 4.6 (44 reviews)</div>

  <?php if (!$restaurant_open): ?>
    <div class="buttons">
      <div class="btn" style="background:#fff;">
        <i class="fas fa-truck"></i> Delivery 
        <span class="status" style="margin-left:6px;color:#fff;background:#6c757d;padding:2px 10px;border-radius:12px;font-size:13px;font-weight:500;">Closed</span>
      </div>
      <div class="btn" style="background:#fff;">
        <i class="fas fa-shopping-bag"></i> Pickup 
        <span class="status" style="margin-left:6px;color:#fff;background:#6c757d;padding:2px 10px;border-radius:12px;font-size:13px;font-weight:500;">Closed</span>
      </div>
    </div>
    <div class="closed-banner"><i class="fas fa-door-closed"></i> We're closed at the moment. Please check back during our opening hours.</div>
  <?php else: ?>
    <div class="buttons" style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-top:10px;">
      <div style="display:flex;align-items:center;background:#fff;border:1px solid #ccc;border-radius:12px;padding:8px 12px;font-size:14px;font-weight:500;white-space:nowrap;">
        <i class="fas fa-truck" style="margin-right:6px;"></i> Delivery
        <span style="margin-left:8px;background:#eee;color:#333;padding:2px 8px;border-radius:10px;font-size:13px;font-weight:600;">30–45 mins</span>
      </div>
      <div style="display:flex;align-items:center;background:#fff;border:1px solid #ccc;border-radius:12px;padding:8px 12px;font-size:14px;font-weight:500;white-space:nowrap;">
        <i class="fas fa-shopping-bag" style="margin-right:6px;"></i> Collection
        <span style="margin-left:8px;background:#eee;color:#333;padding:2px 8px;border-radius:10px;font-size:13px;font-weight:600;">15 mins</span>
      </div>
    </div>
    <div class="open-banner"><i class="fas fa-door-open"></i> We're open now – place your order!</div>
  <?php endif; ?>

  <!-- About anchor -->
  <div id="about" style="height:1px;"></div>

  <div class="recommended-section">
    <h3><i class="fas fa-thumbs-up"></i> RECOMMENDED FOR YOU</h3>
    <div class="recommended-items">
      <?php foreach ($recommended_items as $item): ?>
        <div class="item-card">
          <!-- orange add button -->
          <button class="card-add" data-add-to-cart data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>" data-price="<?= number_format($item['price'], 2, ".", "") ?>" aria-label="Add <?= htmlspecialchars($item['name'], ENT_QUOTES) ?>">
            <i class="fa-solid fa-plus"></i>
          </button>
          <p><strong><?= htmlspecialchars($item['name']) ?></strong></p>
          <p>£<?= number_format($item['price'], 2) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="scroll-hint"><i class="fas fa-angle-right"></i> Swipe</div>
  </div>

  <!-- Deal Note Box (auto-hidden while searching) -->
  <div id="deal-note" style="background:#e8f5e9;border-left:4px solid #43a047;padding:14px 16px;margin:20px 15px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);font-size:14px;color:#256029;">
    <i class="fas fa-tag" style="margin-right:6px;"></i>
    Special offers available under <strong>Meal Deals</strong> — scroll down to save big!
  </div>

  <!-- ===== SEARCH (above categories) ===== -->
  <div class="menu-search-wrap" id="menu-search">
    <div class="menu-search" role="search" aria-label="Search menu">
      <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
      <input id="searchInput" type="search" placeholder="Search the menu (e.g. pepperoni, tikka, fries)" aria-label="Search menu items"/>
    </div>
    <div class="no-results" id="noResults"><i class="fa-solid fa-circle-exclamation"></i> No items match your search.</div>
  </div>

  <!-- Menu -->
  <div class="menu-categories" id="menu">
    <?php
      $categories = [
        'Meal Deals', 'Pizzas', 'Special Pizzas', 'Loaded Fries', 'Shawarma', 'Garlic Bread',
        'Calzone', 'Appetisers', 'House Special', 'Donner Kebabs', 'Burger Bar', 'Special Burgers',
        'New Grilled', 'Wraps', 'Fried Chicken', 'Sheesh Mixed Special', 'Tandoori Main Dishes',
        'Paninis', 'Bbq Platter', 'English Dishes', 'Salad Bar', 'Sauces', 'Extras', 'Kids Meals',
        'Desserts', 'Drinks'
      ];

      foreach ($categories as $category) {
        echo '<div class="category-block">';
        echo '<div class="category-row"><span>' . $category . '</span><i class="fas fa-chevron-down"></i></div>';

        if ($category === 'Meal Deals') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>1/2 & 1/2 pizzas not available on all meal deals / no korma pizza.</strong><br>
            All deal drinks are Pepsi or Coke (other drinks 50p extra)
          </div>';

          $meal_deals = [
            ["name" => "Munch Box", "desc" => 'Get any 1/4lb burger (exc special burgers), chips, donner meat, 5 onion rings & a can of pepsi', "price" => 12.00],
            ["name" => "Deal 1", "desc" => 'Any 10" Pizza, Any 10" Garlic Bread, 2x Chips & Bottle of Pepsi', "price" => 20.00],
            ["name" => "Deal 2", "desc" => 'Any 2x 10" Pizzas, Any 10" Garlic Bread, 2x Chips, Coleslaw & Bottle of Pepsi', "price" => 25.00],
            ["name" => "Deal 3", "desc" => 'Any 2x 12" Pizzas, Any 12" Garlic Bread, 2x Chips & Bottle of Pepsi', "price" => 30.00],
            ["name" => "Deal 4", "desc" => 'Any 12" Pizza, Any 12" Garlic Bread, 2x Chips, Bottle of Pepsi', "price" => 22.00],
            ["name" => "Deal 5", "desc" => 'Any 14" Pizza, Any 12" Pizza, Any 12" Garlic Bread, 2x Chips, Coleslaw & Bottle of Pepsi', "price" => 35.00],
            ["name" => "Deal 6", "desc" => 'Any 3x 10" Pizza', "price" => 24.00],
            ["name" => "Deal 7", "desc" => 'Any 2x 1/4lb Burgers with Chips & 2 Cans of Drink, (Excluding Special Burgers)', "price" => 11.50],
            ["name" => "Deal 8", "desc" => 'Any 3x 1/4lb Burgers with Chips, Salad & 3x Cans of Drink, (Excluding Special Burgers)', "price" => 17.00],
            ["name" => "Deal 9", "desc" => 'Any 4x 1/4lb Burgers with Chips, Salad & 4x Cans of Drink. (Excluding Special Burgers)', "price" => 23.00],
            ["name" => "Deal 10", "desc" => 'Any 2x 1/4lb Burgers with Chips & 2 Cans of Drink, Any 10" Pizza, Tray of Donner Meat & a Bottle of Drink. (Excluding Special Burgers)', "price" => 24.00],
            ["name" => "Deal 11", "desc" => '2x Any Wrap, Chips & 2x Cans of Drinks, No Sheesh Wraps in this Deal', "price" => 15.00],
            ["name" => "Deal 12", "desc" => '2x Large Donner Meat, Chips & 2 Cans of Drinks', "price" => 15.00],
            ["name" => "Deal 13", "desc" => '2x Mix Donner Special, Large Chips & 2 Cans of Pepsi', "price" => 20.00],
            ["name" => "Deal 14", "desc" => '2x Large Donner, 1x Large Chips & 2x Cans of Pepsi', "price" => 18.00],
          ];

          foreach ($meal_deals as $deal) {
            echo '<div class="item-row" data-search="' . htmlspecialchars(strtolower($deal["name"] . ' ' . $deal["desc"]), ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . $deal["name"] . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . $deal["desc"] . '</p>';
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">£' . number_format($deal["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($deal["name"], ENT_QUOTES) . '" data-price="' . number_format($deal["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($deal["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }
          echo '</div>';

        } elseif ($category === 'Pizzas') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Freshly baked with mozzarella cheese & our own savoury tomato sauce.</strong><br>
            <em>Please note: salami, pepperoni, ham is turkey meat</em>
          </div>';

          $pizzas = [
            [ "name" => "Margherita Pizza", "desc" => "With tomato & cheese.", "veg" => true, "sizes" => [["10\"", 6.50], ["12\"", 7.50], ["14\"", 9.00], ["16\"", 13.00]] ],
            [ "name" => "Garlic Margherita Pizza", "desc" => "Garlic butter.", "veg" => true, "sizes" => [["10\"", 7.00], ["12\"", 8.50], ["14\"", 9.00], ["16\"", 13.00]] ],
            [ "name" => "Caribbean Pizza", "desc" => "With pineapple & cheese.", "veg" => true, "sizes" => [["10\"", 7.00], ["12\"", 10.00], ["14\"", 11.50], ["16\"", 12.50]] ],
            [ "name" => "Al Funghi Pizza", "desc" => "With mushrooms.", "veg" => true, "sizes" => [["10\"", 8.50], ["12\"", 10.00], ["14\"", 12.50], ["16\"", 14.50]] ],
            [ "name" => "Pepperoni Pizza", "desc" => "Pepperoni", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 11.50], ["14\"", 14.00], ["16\"", 16.50]] ],
            [ "name" => "Bolognese Pizza", "desc" => "With bolognese sauce", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 11.50], ["14\"", 14.00], ["16\"", 16.50]] ],
            [ "name" => "Spicy Beef Pizza", "desc" => "Cheese, spicy ground beef, onions, peppers, pepperoni & jalapenos", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 10.00], ["14\"", 14.00], ["16\"", 16.50]] ],
            [ "name" => "Tuna Delight Pizza", "desc" => "Tuna & sweetcorn", "veg" => false, "sizes" => [["10\"", 9.00], ["12\"", 11.00], ["14\"", 13.50], ["16\"", 15.50]] ],
            [ "name" => "Pollo Pizza", "desc" => "With chicken & cheese", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 12.50], ["14\"", 14.50], ["16\"", 16.50]] ],
            [ "name" => "Hot Pollo Pizza", "desc" => "Chicken, onions, green peppers & jalapenos", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 12.50], ["14\"", 14.50], ["16\"", 16.50]] ],
            [ "name" => "Vegetarian Pizza", "desc" => "Olives, mushrooms, onions, peppers, pineapple & sweetcorn.", "veg" => true, "sizes" => [["10\"", 9.00], ["12\"", 11.00], ["14\"", 14.50], ["16\"", 16.50]] ],
            [ "name" => "Donner Pizza", "desc" => "With slices of donner meat", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 12.50], ["14\"", 14.50], ["16\"", 16.50]] ],
            [ "name" => "Pollo Funghi Pizza", "desc" => "With chicken cheese & mushrooms", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 13.50], ["14\"", 14.50], ["16\"", 16.50]] ],
            [ "name" => "Seafood Pizza", "desc" => "Prawns, tuna & onions", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 11.00], ["14\"", 14.50], ["16\"", 16.50]] ],
            [ "name" => "Toscana Pizza", "desc" => "Mushrooms, chicken, peppers & onions", "veg" => false, "sizes" => [["10\"", 9.50], ["12\"", 12.00], ["14\"", 14.50], ["16\"", 16.50]] ],
            [ "name" => "Hawaiian Pizza", "desc" => "Ham & pineapple", "veg" => false, "sizes" => [["10\"", 9.00], ["12\"", 11.00], ["14\"", 13.00], ["16\"", 15.00]] ]
          ];

          foreach ($pizzas as $pizza) {
            $min = min(array_map(fn($s)=>$s[1], $pizza['sizes']));
            $searchText = strtolower($pizza["name"] . ' ' . $pizza["desc"]);
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:6px 0;">' . $pizza["name"] . (!empty($pizza["veg"]) ? ' <span style="font-size:14px;">🌱</span>' : '') . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#666;">' . $pizza["desc"] . '</p>';
            echo '  </div>';
            echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
            echo '    <span style="color:#666;">from <strong>£' . number_format($min,2) . '</strong></span>';
            echo '    <button class="btn-orange" data-open-options data-type="pizza" data-item="' . htmlspecialchars($pizza["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }
          echo '</div>';

        } elseif ($category === 'Special Pizzas') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Freshly baked with mozzarella cheese & our own savoury tomato sauce.</strong><br>
            <em>Please note: salami, pepperoni, ham is turkey meat</em>
          </div>';

          $special_pizzas = [
            [ "name" => "Quattro Stagioni Pizza", "desc" => "Salami, prawns, mushrooms & green peppers", "sizes" => [['10"', 9.00], ['12"', 12.00], ['14"', 14.00], ['16"', 16.50]] ],
            [ "name" => "Garlic Chilli Chicken Tikka", "desc" => "Chicken, onions, green peppers, tomatoes, garlic, jalapenos & green chillies", "sizes" => [['10"', 10.00], ['12"', 12.00], ['14"', 14.50], ['16"', 16.50]] ],
            [ "name" => "Chicken Korma Pizza", "desc" => "", "sizes" => [['10"', 10.00], ['12"', 12.50], ['14"', 14.50], ['16"', 16.50]] ],
            [ "name" => "Big Daddy's Pizza", "desc" => "Chicken tikka, salami, pepperoni, mince, spicy beef, donner & a few chips", "sizes" => [['10"', 10.00], ['12"', 12.50], ['14"', 14.50], ['16"', 16.50]] ],
            [ "name" => "Barbecue Chicken Bite Pizza", "desc" => "Bbq base, mozzarella cheese & bbq chicken", "sizes" => [['10"', 9.50], ['12"', 11.50], ['14"', 14.00], ['16"', 16.50]] ],
            [ "name" => "Hot Shot Pizza", "desc" => "Spicy beef, pepperoni, salami, mince, peppers, jalapenos & chilli", "sizes" => [['10"', 9.50], ['12"', 12.50], ['14"', 14.50], ['16"', 16.50]] ],
            [ "name" => "Sweet & Sour Pizza", "desc" => "Bbq sauce, chicken, onions, green peppers, pineapple & jalapenos", "sizes" => [['10"', 9.50], ['12"', 12.00], ['14"', 14.00], ['16"', 16.50]] ],
            [ "name" => "Magic Combination Pizza", "desc" => "Salami, garlic sausage & pepperoni", "sizes" => [['10"', 9.50], ['12"', 12.00], ['14"', 14.00], ['16"', 16.50]] ],
            [ "name" => "Tandoori Pizza", "desc" => "Tandoori chicken, green peppers & onions", "sizes" => [['10"', 10.00], ['12"', 12.50], ['14"', 14.50], ['16"', 16.50]] ],
            [ "name" => "Chicken Tikka & Donner Meat", "desc" => "Chicken Tikka & Donner Meat Pizza", "sizes" => [['10"', 10.00], ['12"', 12.00], ['14"', 15.00], ['16"', 17.50]] ],
            [ "name" => "Chef's Special Pizza", "desc" => "\"A magnificent pizza\" chef's choice", "sizes" => [['10"', 10.50], ['12"', 13.50], ['14"', 14.50], ['16"', 16.50]] ],
            [ "name" => "Chicago Bear Pizza", "desc" => "Salami, garlic sausage, pepperoni, chicken & spicy beef", "sizes" => [['10"', 10.50], ['12"', 13.50], ['14"', 15.00], ['16"', 18.00]] ],
            [ "name" => "Super Asian Style Pizza", "desc" => "Chicken tandoori, red onion, green pepper, sweetcorn, pineapple, olives & jalapenos", "sizes" => [['10"', 10.00], ['12"', 12.50], ['14"', 15.00], ['16"', 17.00]] ],
            [ "name" => "Chicken Lover Pizza", "desc" => "Chicken & chicken tikka & sweetcorn", "sizes" => [['10"', 9.50], ['12"', 13.00], ['14"', 15.50], ['16"', 17.50]] ],
            [ "name" => "Crazy One Pizza", "desc" => "Chicken tikka, seekh kebab, pepperoni, garlic sausage, & 4 cheeses", "sizes" => [['10"', 10.00], ['12"', 13.50], ['14"', 16.00], ['16"', 17.00]] ],
            [ "name" => "Meat Feast Pizza", "desc" => "All the meats available", "sizes" => [['10"', 10.00], ['12"', 13.00], ['14"', 15.00], ['16"', 17.00]] ],
            [ "name" => "Mixed Grill Pizza", "desc" => "Chicken tikka, seekh kebab, red onions & peppers", "sizes" => [['10"', 10.00], ['12"', 13.00], ['14"', 15.00], ['16"', 16.50]] ],
            [ "name" => "Mighty Mac Pizza", "desc" => "Garlic sausage, pepperoni, salami, chicken tikka, spicy beef, donner meat & jalapenos", "sizes" => [['10"', 10.00], ['12"', 12.50], ['14"', 13.50], ['16"', 16.50]] ],
            [ "name" => "Return Of Rocky Pizza", "desc" => "Chicken, onion, green peppers & bbq sauce", "sizes" => [['10"', 10.00], ['12"', 12.00], ['14"', 14.50], ['16"', 16.50]] ],
            [ "name" => "Half Way Pizza", "desc" => "Chicken tikka, seekh kebab, pepperoni & garlic sausage", "sizes" => [['10"', 10.00], ['12"', 13.50], ['14"', 15.50], ['16"', 16.50]] ],
            [ "name" => "Bbq Special Pizza", "desc" => "Tandoori chicken, onions, peppers, sweetcorn, jalapenos, chilli & special bbq sauce", "sizes" => [['10"', 10.50], ['12"', 13.50], ['14"', 15.50], ['16"', 16.50]] ],
            [ "name" => "Asian Special Pizza", "desc" => "Chicken tikka, mince, sweetcorn, onions, peppers & jalapenos.", "sizes" => [['10"', 10.50], ['12"', 13.00], ['14"', 15.50], ['16"', 16.50]] ],
            [ "name" => "1/2 & 1/2 Pizza", "desc" => "Two half pizzas of your choice", "sizes" => [['10"', 11.00], ['12"', 13.00], ['14"', 15.00], ['16"', 16.50]] ],
            [ "name" => "Special Mixed Kebab Pizza", "desc" => "Topped with special mixed kebab", "sizes" => [['10"', 11.50], ['12"', 13.50], ['14"', 15.00], ['16"', 17.50]] ],
          ];

          foreach ($special_pizzas as $pizza) {
            $min = min(array_map(fn($s)=>$s[1], $pizza['sizes']));
            $searchText = strtolower($pizza["name"] . ' ' . $pizza["desc"]);
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 5px;">' . $pizza["name"] . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . $pizza["desc"] . '</p>';
            echo '  </div>';
            echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
            echo '    <span style="color:#666;">from <strong>£' . number_format($min,2) . '</strong></span>';
            echo '    <button class="btn-orange" data-open-options data-type="special" data-item="' . htmlspecialchars($pizza["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }
          echo '</div>';

        } elseif ($category === 'Loaded Fries') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>All loaded fries are topped generously and served hot.</strong>
          </div>';

          $Loaded_Fries = [
            [ "name" => "Loaded Fries Original", "desc" => "Chips, cheese, crispy chicken, cheese sauce & Jalapenos", "price" => 8.00 ],
            [ "name" => "Loaded Fries Peri Peri", "desc" => "Chips, cheese, peri peri chicken, fried onions, cheese sauce", "price" => 8.50 ],
            [ "name" => "Special Loaded Fries", "desc" => "Chips, cheese, red donner, fried onions, cheese sauce", "price" => 8.50 ]
          ];

          foreach ($Loaded_Fries as $item) {
            $searchText = strtolower($item["name"] . ' ' . $item["desc"]);
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . $item["name"] . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . $item["desc"] . '</p>';
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">£' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }
          echo '</div>';
        } elseif ($category === 'Shawarma') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Authentic shawarma dishes served hot with fresh salad & sauces.</strong><br>
            <em>Choose naan, chips, or our house special style.</em>
          </div>';

          $shawarmas = [
            [ "name" => "Chicken Shawarma On Naan",  "desc" => "Tender chicken shawarma served on soft naan bread", "price" => 8.00 ],
            [ "name" => "Chicken Shawarma On Chips", "desc" => "Chicken shawarma layered generously over golden chips", "price" => 8.00 ],
            [ "name" => "Special Shawarma",  "desc" => "Chicken shawarma, fried onions & peppers and sheek kebab.", "price" => 9.50 ],
          ];

          foreach ($shawarmas as $item) {
            $searchText = strtolower($item["name"] . ' ' . $item["desc"]);
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';
        } elseif ($category === 'Garlic Bread') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Freshly baked garlic breads with a variety of toppings.</strong><br>
            <em>Available in 10&quot; and 12&quot; sizes.</em>
          </div>';

          $garlic_breads = [
            [ "name" => "Garlic Bread Cheese",   "desc" => "", "sizes" => [['10"', 6.00], ['12"', 7.00]] ],
            [ "name" => "Garlic Bread Pomodoro","desc" => "Garlic bread with tomato sauce", "sizes" => [['10"', 6.00], ['12"', 7.00]] ],
            [ "name" => "Garlic Bread Supreme", "desc" => "Garlic bread with mozzarella cheese & onion", "sizes" => [['10"', 6.00], ['12"', 7.50]] ],
            [ "name" => "Garlic Bread Special", "desc" => "With mushroom & sweetcorn", "sizes" => [['10"', 7.00], ['12"', 8.00]] ],
          ];

          foreach ($garlic_breads as $gb) {
            $min = min(array_map(fn($s)=>$s[1], $gb['sizes']));
            $searchText = strtolower($gb["name"] . ' ' . $gb["desc"]);
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 5px;">' . $gb["name"] . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . $gb["desc"] . '</p>';
            echo '  </div>';
            echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
            echo '    <span style="color:#666;">from <strong>&pound;' . number_format($min,2) . '</strong></span>';
            echo '    <button class="btn-orange" data-open-options data-type="garlicbread" data-item="' . htmlspecialchars($gb["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }
          echo '</div>';
        } elseif ($category === 'Calzone') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Oven-baked folded pizzas packed with your favourite fillings.</strong><br>
            <em>Available in 12&quot; and 14&quot; sizes.</em>
          </div>';

          $calzones = [
            [ "name" => "Calzone Kiev",           "desc" => "Ham, garlic butter, chicken, mushroom & cheese",                     "sizes" => [['12"', 10.00], ['14"', 12.00]] ],
            [ "name" => "Calzone Della",          "desc" => "Mushrooms, onions, kebab meat & garlic butter",                      "sizes" => [['12"', 10.00], ['14"', 12.50]] ],
            [ "name" => "Calzone Mushroom",       "desc" => "Mushroom, ham, salami, pepperoni & cheese",                          "sizes" => [['12"', 10.00], ['14"', 12.50]] ],
            [ "name" => "Calzone Bbq Pollo",      "desc" => "Bbq chicken, mushrooms, onions & garlic",                            "sizes" => [['12"', 10.00], ['14"', 12.50]] ],
            [ "name" => "Hot Calzone",            "desc" => "Mushrooms, jalapenos, pepperoni & garlic",                           "sizes" => [['12"', 10.00], ['14"', 12.50]] ],
            [ "name" => "Calzone Vegetarian",     "desc" => "Mushrooms, red onion, sweetcorn, pineapple, peppers & garlic",       "sizes" => [['12"', 10.00], ['14"', 12.50]] ],
            [ "name" => "Lash Gosht",             "desc" => "Donner meat, onions, garlic & chillies",                             "sizes" => [['12"', 10.00], ['14"', 12.50]] ],
            [ "name" => "House Special Calzone",  "desc" => "New. Mix kebab, onions & peppers",                                   "sizes" => [['12"', 10.00], ['14"', 14.00]] ],
          ];

          foreach ($calzones as $cz) {
            $min = min(array_map(fn($s)=>$s[1], $cz['sizes']));
            $searchText = strtolower($cz["name"] . ' ' . $cz["desc"]);
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 5px;">' . $cz["name"] . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . $cz["desc"] . '</p>';
            echo '  </div>';
            echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
            echo '    <span style="color:#666;">from <strong>&pound;' . number_format($min,2) . '</strong></span>';
            echo '    <button class="btn-orange" data-open-options data-type="calzone" data-item="' . htmlspecialchars($cz["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }
          echo '</div>';
        } elseif ($category === 'Appetisers') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Tasty appetisers to start your meal right.</strong>
          </div>';

          $appetisers = [
            [ "name" => "Chicken Tikka",        "desc" => "Chicken marinated in herbs & spices cooked in an oven", "price" => 5.00 ],
            [ "name" => "Mixed Starter",        "desc" => "Chicken tikka, sheek kebab, shami kebab & onion bhaji", "price" => 6.00 ],
            [ "name" => "1/4 Tandoori Chicken", "desc" => "On bone. Chicken marinated in herbs & spices. Cooked in oven", "price" => 6.50 ],
            [ "name" => "Garlic Mushroom In Batter", "desc" => "", "price" => 5.50 ],
            [ "name" => "Onion Bhaji",          "desc" => "", "price" => 5.00 ],
            [ "name" => "Chicken Pakora",       "desc" => "Fillet of chicken in spicy batter and fried", "price" => 5.00 ],
            [ "name" => "Samosa Mix",           "desc" => "Vegetable & meat", "price" => 5.00 ],
            [ "name" => "Chicken Spring Roll",  "desc" => "", "price" => 5.00 ],
          ];

          foreach ($appetisers as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';
        } elseif ($category === 'House Special') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>House favourites served with sides.</strong>
          </div>';

          $house_special = [
            // Multi-size (Half / Full) � uses options sheet
            [ "name" => "Piri Piri Chicken", "desc" => "Served with chips, salad & piri piri sauce", "sizes" => [['Half', 8.00], ['Full', 14.00]] ],
            [ "name" => "Bbq Chicken",       "desc" => "Served with chips, salad & bbq sauce",       "sizes" => [['Half', 8.00], ['Full', 14.00]] ],

            // Single-price items
            [ "name" => "Special Fried Chicken & Chips", "desc" => "New. Diced chicken breast cooked with onions & mushrooms served with salad", "price" => 11.50 ],
            [ "name" => "Donner Butty",                "desc" => "", "price" => 5.00 ],
            [ "name" => "Special Kebab Butty",         "desc" => "", "price" => 6.00 ],
            [ "name" => "3 Strip Meal",                "desc" => "Chips & drink", "price" => 6.00 ],
            [ "name" => "1 Pc & Chips",                "desc" => "", "price" => 4.00 ],
            [ "name" => "2 Pcs & Chips",               "desc" => "", "price" => 5.00 ],
            [ "name" => "3 Pcs & Chips",               "desc" => "", "price" => 6.00 ],
            [ "name" => "4 Pcs & Chips",               "desc" => "", "price" => 7.00 ],
            [ "name" => "6 Pc Chicken & Large Chips",  "desc" => "", "price" => 11.50 ],
          ];

          foreach ($house_special as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '  </div>';

            if (isset($item["sizes"])) {
              $min = min(array_map(fn($s)=>$s[1], $item['sizes']));
              echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
              echo '    <span style="color:#666;">from <strong>&pound;' . number_format($min,2) . '</strong></span>';
              echo '    <button class="btn-orange" data-open-options data-type="housespecial" data-item="' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
              echo '  </div>';
            } else {
              echo '  <div style="flex-shrink:0;text-align:right;">';
              echo '    <span class="price" style="display:inline-block;margin-right:10px;font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
              echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
              echo '  </div>';
            }

            echo '</div>';
          }

          echo '</div>';
        } elseif ($category === 'Donner Kebabs') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Delicious donner kebabs served fresh with salad & sauces.</strong>
          </div>';

          $donner_kebabs = [
            // Multi-size items (Regular / Large)
            [ "name" => "Donner Kebab",       "desc" => "", "sizes" => [['Regular', 5.50], ['Large', 7.00]] ],
            [ "name" => "Donner Meat & Chips","desc" => "", "sizes" => [['Regular', 5.50], ['Large', 7.00]] ],
            [ "name" => "Donner Meat In Tray","desc" => "", "sizes" => [['Regular', 6.00], ['Large', 7.50]] ],

            // Single-price items
            [ "name" => "Chirag Special Mix Meat & Chips", "desc" => "", "price" => 7.50 ],
            [ "name" => "Chicken Tikka Meat & Chips",      "desc" => "", "price" => 7.50 ],
            [ "name" => "Chicken Kebab",                   "desc" => "", "price" => 7.50 ],
            [ "name" => "Chicken Tikka Kebab",             "desc" => "", "price" => 7.50 ],
            [ "name" => "Aza's Famous Special Mix Kebab",  "desc" => "Cooked with chicken tikka & donner meat in Aza's special tikka sauce", "price" => 8.00 ],
            [ "name" => "Kebab Box",                       "desc" => "Special mix kebab and donner kebab, chips, 1 naan, 2 sauce, 2 pepsi and salad", "price" => 15.00 ],
          ];

          foreach ($donner_kebabs as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '  </div>';

            if (isset($item["sizes"])) {
              $min = min(array_map(fn($s)=>$s[1], $item['sizes']));
              echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
              echo '    <span style="color:#666;">from <strong>&pound;' . number_format($min,2) . '</strong></span>';
              echo '    <button class="btn-orange" data-open-options data-type="donnerkebabs" data-item="' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
              echo '  </div>';
            } else {
              echo '  <div style="flex-shrink:0;text-align:right;">';
              echo '    <span class="price" style="display:inline-block;margin-right:10px;font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
              echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
              echo '  </div>';
            }

            echo '</div>';
          }

          echo '</div>';
        } elseif ($category === 'Burger Bar') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Juicy burgers with your favourite toppings.</strong><br>
            <em>Available in 1/4lb and 1/2lb sizes.</em>
          </div>';

          $burger_bar = [
            [ "name" => "Plain Burger",          "desc" => "",                                   "sizes" => [['1/4lb', 5.00], ['1/2lb', 6.50]] ],
            [ "name" => "Cheese Burger",         "desc" => "Topped with slice of melted cheese", "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
            [ "name" => "Vegi Burger",           "desc" => "",                                   "sizes" => [['1/4lb', 4.50], ['1/2lb', 5.50]] ],
            [ "name" => "American Burger",       "desc" => "Topped with fried onion",            "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
            [ "name" => "Hawaiian Burger",       "desc" => "Cheese & a juicy pineapple ring",    "sizes" => [['1/4lb', 6.50], ['1/2lb', 7.50]] ],
            [ "name" => "Supreme Burger",        "desc" => "With cheese & fried onion",          "sizes" => [['1/4lb', 6.50], ['1/2lb', 7.50]] ],
            [ "name" => "Chicken Fillet Burger", "desc" => "",                                   "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
          ];

          foreach ($burger_bar as $b) {
            $min = min(array_map(fn($s)=>$s[1], $b['sizes']));
            $searchText = strtolower(($b["name"] ?? '') . ' ' . ($b["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($b["name"]) . '</h4>';
            if (!empty($b["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($b["desc"]) . '</p>';
            }
            echo '  </div>';
            echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
            echo '    <span style="color:#666;">from <strong>&pound;' . number_format($min,2) . '</strong></span>';
            echo '    <button class="btn-orange" data-open-options data-type="burgerbar" data-item="' . htmlspecialchars($b["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';
        } elseif ($category === 'Special Burgers') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Stacked and loaded burgers with premium toppings.</strong><br>
            <em>Available in 1/4lb and 1/2lb unless priced as a single item.</em>
          </div>';

          $special_burgers = [
            [ "name" => "Hot Bbq Wafer",            "desc" => "Jalapenos, lettuce, bbq sauce",                                 "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
            [ "name" => "Garlic Mushroom Burger",   "desc" => "",                                                             "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
            [ "name" => "Chicken Tikka Burger",     "desc" => "",                                                             "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
            [ "name" => "Egg American Burger",      "desc" => "Fried onion, fried egg, cheese & salad",                       "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
            [ "name" => "Chicken Supreme Burger",   "desc" => "Chicken, cheese & hash brown",                                 "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
            [ "name" => "King Chicken Burger",      "desc" => "Cheddar cheese, hash brown, cheese slice",                     "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.50]] ],
            [ "name" => "Mega Munch",               "desc" => "3 beef burger, 3 slice of cheese",                             "price" => 8.00 ],
            [ "name" => "Special Burger",   "desc" => "Served with chicken, beef burger cheese and salad",            "price" => 8.00 ],
            [ "name" => "Mexican Burger",           "desc" => "Fried onion, fried peppers � (chicken or beef burger)",        "sizes" => [['1/4lb', 5.50], ['1/2lb', 6.50]] ],
            [ "name" => "Donner Burger",            "desc" => "Beef burger topped with donner",                               "sizes" => [['1/4lb', 5.50], ['1/2lb', 6.50]] ],
            [ "name" => "Spicy Donner Burger",      "desc" => "Beef burger, spicy donner, cheese",                            "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.00]] ],
            [ "name" => "Spicy Chicken Grill Burger","desc"=> "Chicken burger, lettuce & mayo",                               "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.00]] ],
            [ "name" => "Pepperoni Burger",         "desc" => "Beef burger topped with pepperoni & cheese",                   "sizes" => [['1/4lb', 6.00], ['1/2lb', 7.00]] ],
            [ "name" => "Chicken Mexicano",         "desc" => "Nachos, jalapenos, cheese, mexican spice, mexican sauce.",     "sizes" => [['1/4lb', 8.00], ['1/2lb', 8.50]] ],
          ];

          foreach ($special_burgers as $sb) {
            $searchText = strtolower(($sb["name"] ?? '') . ' ' . ($sb["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($sb["name"]) . '</h4>';
            if (!empty($sb["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($sb["desc"]) . '</p>';
            }
            echo '  </div>';

            if (isset($sb["sizes"])) {
              $min = min(array_map(fn($s)=>$s[1], $sb['sizes']));
              echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
              echo '    <span style="color:#666;">from <strong>&pound;' . number_format($min,2) . '</strong></span>';
              echo '    <button class="btn-orange" data-open-options data-type="specialburgers" data-item="' . htmlspecialchars($sb["name"], ENT_QUOTES) . '">Add</button>';
              echo '  </div>';
            } else {
              echo '  <div style="flex-shrink:0;text-align:right;">';
              echo '    <span class="price" style="display:inline-block;margin-right:10px;font-weight:bold;font-size:15px;">&pound;' . number_format($sb["price"], 2) . '</span>';
              echo '    <button data-add-to-cart data-name="' . htmlspecialchars($sb["name"], ENT_QUOTES) . '" data-price="' . number_format($sb["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($sb["name"], ENT_QUOTES) . '">Add</button>';
              echo '  </div>';
            }

            echo '</div>';
          }

          echo '</div>';
        } elseif ($category === 'New Grilled') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Fresh off the grill, served with salad & mayo.</strong><br>
            <em>Available in 1/4lb Meal and 1/2lb Meal.</em>
          </div>';

          $new_grilled = [
            [ "name" => "Bbq Chicken",   "desc" => "Salad & mayo", "sizes" => [['1/4lb Meal', 7.00], ['1/2lb Meal', 8.00]] ],
            [ "name" => "Fillet Burger", "desc" => "Salad & mayo", "sizes" => [['1/4lb Meal', 7.00], ['1/2lb Meal', 8.00]] ],
            [ "name" => "Peri Peri",     "desc" => "Salad & mayo", "sizes" => [['1/4lb Meal', 7.00], ['1/2lb Meal', 8.00]] ],
            [ "name" => "Chicken Fillet","desc" => "Salad & mayo", "sizes" => [['1/4lb Meal', 7.00], ['1/2lb Meal', 8.00]] ],
          ];

          foreach ($new_grilled as $ng) {
            $min = min(array_map(fn($s)=>$s[1], $ng['sizes']));
            $searchText = strtolower(($ng["name"] ?? '') . ' ' . ($ng["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($ng["name"]) . '</h4>';
            if (!empty($ng["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($ng["desc"]) . '</p>';
            }
            echo '  </div>';
            echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
            echo '    <span style="color:#666;">from <strong>&pound;' . number_format($min,2) . '</strong></span>';
            echo '    <button class="btn-orange" data-open-options data-type="newgrilled" data-item="' . htmlspecialchars($ng["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';
        } elseif ($category === 'Wraps') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Freshly made wraps filled with your favourites.</strong>
          </div>';

          $wraps = [
            [ "name" => "Shawarma Wrap",            "desc" => "",                                   "price" => 7.00 ],
            [ "name" => "Chicken Wrap Meal",        "desc" => "Crispy chicken strips & chips",      "price" => 6.50 ],
            [ "name" => "Donner Wrap",              "desc" => "",                                   "price" => 6.50 ],
            [ "name" => "Special Mix Kebab Wrap",   "desc" => "",                                   "price" => 7.00 ],
            [ "name" => "Chicken Tikka Wrap",       "desc" => "",                                   "price" => 7.00 ],
            [ "name" => "Chicken Kebab Wrap",       "desc" => "",                                   "price" => 7.00 ],
            [ "name" => "Chicken Sheesh Wrap",      "desc" => "",                                   "price" => 8.00 ],
            [ "name" => "Cheesy Chip Wrap",         "desc" => "",                                   "price" => 5.50 ],
          ];

          foreach ($wraps as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';
       
        } elseif ($category === 'Fried Chicken') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Crispy fried chicken meals and family feasts.</strong>
          </div>';

          $fried_chicken = [
            [ "name" => "2 Pcs Chicken & 3 Wings", "desc" => "", "price" => 6.00 ],
            [ "name" => "6 Spicy Wings",           "desc" => "", "price" => 5.00 ],
            [ "name" => "2 Pcs Chicken & Spicy Wedges", "desc" => "", "price" => 6.00 ],
            [ "name" => "Family Feast",            "desc" => "10pc Chicken, 2 Chips, Bottle of Pepsi, Coleslaw", "price" => 20.00 ],
          ];

          foreach ($fried_chicken as $fc) {
            $searchText = strtolower(($fc["name"] ?? '') . ' ' . ($fc["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($fc["name"]) . '</h4>';
            if (!empty($fc["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($fc["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($fc["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($fc["name"], ENT_QUOTES) . '" data-price="' . number_format($fc["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($fc["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Sheesh Mixed Special') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>All our kebabs are prepared on the premises using only the finest meats.</strong><br>
            <em>All kebabs are served in fresh nan with your choice of salad & sauces.</em>
          </div>';

          $sheesh_mixed = [
            [ "name" => "Chicken Sheesh",            "desc" => "", "price" => 10.00 ],
            [ "name" => "Lamb Sheesh",               "desc" => "", "price" => 10.00 ],
            [ "name" => "Chicken Tikka Sheesh",      "desc" => "", "price" => 9.00 ],
            [ "name" => "Lamb Tikka Sheesh",         "desc" => "", "price" => 9.00 ],
            [ "name" => "Chicken Tikka With Cheese", "desc" => "", "price" => 8.00 ],
            [ "name" => "Sheesh Mixed Special",      "desc" => "Chicken tikka, chicken sheesh & lamb donner", "price" => 13.50 ],
          ];

          foreach ($sheesh_mixed as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Tandoori Main Dishes') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Traditional tandoori dishes cooked in a clay oven and served with salad & sauces.</strong>
          </div>';

          $tandoori_dishes = [
            [ "name" => "Mixed Grill", 
              "desc" => "A combination of 1/4 tandoori chicken, lamb tikka, seekh kebab, onion bhaji, curry sauce & green salad, chips or nan", 
              "price" => 13.50 ],
            [ "name" => "Chicken Tikka", 
              "desc" => "Fillet of chicken cooked with specially selected herbs & spices, then cooked in a tandoori oven skewers & served with curry sauce & green salad, chips or nan", 
              "price" => 12.00 ],
            [ "name" => "Lamb Tikka", 
              "desc" => "Cooked & prepared and served as above", 
              "price" => 14.50 ],
            [ "name" => "1/2 Tandoori Chicken", 
              "desc" => "Spring chicken marinated in specially selected herbs & spices overnight, cooked in tandoori oven on skewers & then served with green salad", 
              "price" => 11.00 ],
          ];

          foreach ($tandoori_dishes as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Paninis') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Served hot with chips & a can of Pepsi.</strong><br>
            <em>Choose any 2 toppings.</em>
          </div>';

          $paninis = [
            [ "name" => "Panini", "desc" => "Any 2 toppings served with chips & can of Pepsi", "price" => 6.00 ],
          ];

          foreach ($paninis as $p) {
            $searchText = strtolower(($p["name"] ?? '') . ' ' . ($p["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($p["name"]) . '</h4>';
            if (!empty($p["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($p["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($p["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($p["name"], ENT_QUOTES) . '" data-price="' . number_format($p["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($p["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Bbq Platter') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Sharing platter loaded with BBQ favourites.</strong>
          </div>';

          $bbq_platter = [
            [ "name" => "Bbq Platter",
              "desc" => "Bbq Chicken, Bbq Chicken Tikka, Bbq Lamb, Bbq Spicy Wings, Bbq Grill Seekh Kebab, Served with Large Chips & Salad, 2 Nans, 2 Drinks, Chilli Sauce, Garlic Mayo & Bbq Sauce",
              "price" => 22.00
            ],
          ];

          foreach ($bbq_platter as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'English Dishes') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Classic English favourites served hot.</strong>
          </div>';

          $english_dishes = [
            [ "name" => "Plain Omelette",              "desc" => "", "price" => 5.50 ],
            [ "name" => "Prawns Omelette",             "desc" => "", "price" => 7.00 ],
            [ "name" => "Chicken Omelette",            "desc" => "", "price" => 6.00 ],
            [ "name" => "Mushroom Omelette",           "desc" => "", "price" => 6.00 ],
            [ "name" => "Chicken & Cheese Omelette",   "desc" => "", "price" => 6.50 ],
            [ "name" => "Chicken & Mushroom Omelette", "desc" => "", "price" => 6.50 ],
            [ "name" => "Spanish Omelette",            "desc" => "", "price" => 6.50 ],
            [ "name" => "Cheese Omelette",             "desc" => "", "price" => 6.00 ],
            [ "name" => "Fried Scampi & Chips",        "desc" => "", "price" => 7.50 ],
          ];

          foreach ($english_dishes as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Salad Bar') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Freshly prepared salads served with crisp vegetables.</strong>
          </div>';

          $salads = [
            [ "name" => "Chicken Salad",       "desc" => "", "price" => 6.00 ],
            [ "name" => "Chicken Tikka Salad", "desc" => "", "price" => 6.50 ],
            [ "name" => "Prawn Salad",         "desc" => "", "price" => 6.50 ],
            [ "name" => "Mixed Salad",         "desc" => "", "price" => 2.50 ],
          ];

          foreach ($salads as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Salad Bar') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Freshly prepared salads served with crisp vegetables.</strong>
          </div>';

          $salads = [
            [ "name" => "Chicken Salad",       "desc" => "", "price" => 6.00 ],
            [ "name" => "Chicken Tikka Salad", "desc" => "", "price" => 6.50 ],
            [ "name" => "Prawn Salad",         "desc" => "", "price" => 6.50 ],
            [ "name" => "Mixed Salad",         "desc" => "", "price" => 2.50 ],
          ];

          foreach ($salads as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Sauces') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Add a pot of your favourite sauce.</strong>
          </div>';

          $sauces = [
            [ "name" => "Mayonnaise",         "desc" => "", "price" => 0.80 ],
            [ "name" => "Garlic Mayonnaise",  "desc" => "", "price" => 0.80 ],
            [ "name" => "Chilli Sauce",       "desc" => "", "price" => 0.80 ],
          ];

          foreach ($sauces as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Extras') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Sides & extras to round out your meal.</strong>
          </div>';

          $extras = [
            // single-price items
            [ "name" => "Mozzarella Sticks (6)",            "desc" => "",                           "price" => 4.70 ],
            [ "name" => "Chilli Cheese Nuggets (6)",        "desc" => "",                           "price" => 4.00 ],
            [ "name" => "Chips Cheese In Nan",              "desc" => "",                           "price" => 5.00 ],
            [ "name" => "Chips Cheese",                     "desc" => "",                           "price" => 4.00 ],
            [ "name" => "Chips With Chippy Curry Sauce",    "desc" => "",                           "price" => 5.00 ],
            [ "name" => "Salad Nan",                        "desc" => "",                           "price" => 4.00 ],
            [ "name" => "Onion Rings (10)",                 "desc" => "",                           "price" => 4.00 ],
            [ "name" => "Coleslaw",                         "desc" => "",                           "price" => 3.00 ],
            [ "name" => "Potato Wedges",                    "desc" => "",                           "price" => 4.00 ],
            [ "name" => "Cheesy Wedges",                    "desc" => "",                           "price" => 5.00 ],
            [ "name" => "Nan Bread",                        "desc" => "",                           "price" => 2.00 ],
            [ "name" => "Tortilla Wrap",                    "desc" => "",                           "price" => 1.00 ],

            // multi-size item (opens options sheet)
            [ "name" => "Chips", "desc" => "Chip spice available", "sizes" => [['Regular', 2.00], ['Large', 3.50]] ],
          ];

          foreach ($extras as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '  </div>';

            if (isset($item["sizes"])) {
              $min = min(array_map(fn($s)=>$s[1], $item['sizes']));
              echo '  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">';
              echo '    <span style="color:#666;">from <strong>&pound;' . number_format($min,2) . '</strong></span>';
              echo '    <button class="btn-orange" data-open-options data-type="extras" data-item="' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
              echo '  </div>';
            } else {
              echo '  <div style="flex-shrink:0;text-align:right;">';
              echo '    <span class="price" style="display:inline-block;margin-right:10px;font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
              echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
              echo '  </div>';
            }

            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Kids Meals') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Comes with a Fruit-shoot</strong>
          </div>';

          $kids_meals = [
            [ "name" => "Kids Popcorn Chicken Meal",      "desc" => "Chips & a drink",                                "price" => 5.00 ],
            [ "name" => "Chicken Nuggets, Chips & Drink", "desc" => "",                                              "price" => 4.50 ],
            [ "name" => "3x Fish Fingers, Chips & Drink", "desc" => "",                                              "price" => 4.00 ],
            [ "name" => "Small Donner Meat, Chips & Drink","desc" => "",                                             "price" => 4.00 ],
            [ "name" => "Small Burger, Chips & Drink",    "desc" => "Cheese",                                        "price" => 4.00 ],
            [ "name" => "Mini Meal",                      "desc" => '7" Pizza Any 2 Toppings, Chips & Drink',        "price" => 7.50 ],
          ];

          foreach ($kids_meals as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Desserts') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Sweet treats to finish your meal.</strong>
          </div>';

          $desserts = [
            [ "name" => "Oreo Cheesecake",           "desc" => "", "price" => 3.50 ],
            [ "name" => "Fudge Cake With Custard",   "desc" => "", "price" => 3.50 ],
            [ "name" => "Cheese Cake",               "desc" => "", "price" => 3.00 ],
          ];

          foreach ($desserts as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';

        } elseif ($category === 'Drinks') {
          echo '<div class="category-content">';
          echo '<div class="info-box" style="background:#fff8e1;color:#8a6d3b;border-left:4px solid #f0ad4e;padding:14px 16px;margin:12px 0;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:14px;">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            <strong>Chilled soft drinks.</strong> <em>330ml cans unless stated otherwise.</em>
          </div>';

          $drinks = [
            [ "name" => "7up 330ml",                 "desc" => "",            "price" => 1.00 ],
            [ "name" => "Coke 330ml",                "desc" => "",            "price" => 1.00 ],
            [ "name" => "Diet Coke 330ml",           "desc" => "",            "price" => 1.00 ],
            [ "name" => "Fanta 330ml",               "desc" => "",            "price" => 1.00 ],
            [ "name" => "Tango 330ml",               "desc" => "",            "price" => 1.00 ],
            [ "name" => "Pepsi 330ml",               "desc" => "",            "price" => 1.00 ],
            [ "name" => "Rio 330ml",                 "desc" => "",            "price" => 1.50 ],
            [ "name" => "Rubicon",                    "desc" => "3 flavours",  "price" => 1.50 ],
            [ "name" => "Large Coke Bottle",         "desc" => "",            "price" => 3.00 ],
            [ "name" => "Large Tango Bottle",        "desc" => "",            "price" => 3.00 ],
            [ "name" => "Large Pepsi Bottle",        "desc" => "",            "price" => 3.00 ],
            [ "name" => "Fruit-shoot",               "desc" => "",            "price" => 0.80 ],
            [ "name" => "Sprite 330ml",              "desc" => "",            "price" => 1.00 ],
            [ "name" => "Sprite Zero Sugar 330ml",   "desc" => "",            "price" => 1.00 ],
          ];

          foreach ($drinks as $item) {
            $searchText = strtolower(($item["name"] ?? '') . ' ' . ($item["desc"] ?? ''));
            echo '<div class="item-row" data-search="' . htmlspecialchars($searchText, ENT_QUOTES) . '">';
            echo '  <div style="flex:1;text-align:left;">';
            echo '    <h4 style="margin:0 0 6px;font-size:17px;">' . htmlspecialchars($item["name"]) . '</h4>';
            if (!empty($item["desc"])) {
              echo '    <p style="margin:0 0 8px;font-size:14px;color:#555;">' . htmlspecialchars($item["desc"]) . '</p>';
            }
            echo '    <span class="price" style="font-weight:bold;font-size:15px;">&pound;' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink:0;">';
            echo '    <button data-add-to-cart data-name="' . htmlspecialchars($item["name"], ENT_QUOTES) . '" data-price="' . number_format($item["price"], 2, ".", "") . '" aria-label="Add ' . htmlspecialchars($item["name"], ENT_QUOTES) . '">Add</button>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';


        } else {
          echo '<div class="category-content"></div>';
        }

        echo '</div>'; // .category-block
      }
    ?>
  </div> <!-- .menu-categories -->
</div> <!-- .container -->

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Expand/collapse categories
    document.querySelectorAll('.category-row').forEach(row => {
      row.addEventListener('click', function () {
        const next = this.nextElementSibling;
        if (next && next.classList.contains('category-content')) {
          next.style.display = (next.style.display === 'block') ? 'none' : 'block';
        }
      });
    });

    // Highlight "Order Now" by default
    const orderLink = document.querySelector('.bottom-nav .nav-item[href="#menu"]');
    if (orderLink) orderLink.classList.add('active');

    // Cleanup of legacy storage
    try { localStorage.removeItem('cart_count'); } catch (e) {}

    // ===== Search logic =====
    const input = document.getElementById('searchInput');
    const noResultsEl = document.getElementById('noResults');
    const searchWrap = document.querySelector('.menu-search-wrap');
    const infoBoxes = document.querySelectorAll('.info-box');
    const dealNote = document.getElementById('deal-note');

    // Make sticky bar non-sticky while typing (better iOS scroll with keyboard)
    if (input && searchWrap) {
      input.addEventListener('focus', () => searchWrap.classList.add('is-focused'));
      input.addEventListener('blur',  () => searchWrap.classList.remove('is-focused'));
    }

    // Cache original HTML for highlight reset & precompute searchable text
    document.querySelectorAll('.category-content .item-row').forEach(row => {
      const title = row.querySelector('h4');
      const desc = row.querySelector('p');
      if (title) title.dataset.orig = title.innerHTML;
      if (desc) desc.dataset.orig = desc.innerHTML;
      if (!row.dataset.search) {
        row.dataset.search = ((title?.textContent || '') + ' ' + (desc?.textContent || '')).toLowerCase();
      } else {
        row.dataset.search = row.dataset.search.toLowerCase();
      }
    });

    let t;
    input.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(applyFilter, 120);
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        input.value = '';
        applyFilter();
        input.blur();
      }
    });

    function normalise(str){
      return (str || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
    }

    function highlight(el, q){
      if(!el) return;
      el.innerHTML = el.dataset.orig || el.innerHTML;
      if(!q) return;
      const text = el.textContent;
      const idx = normalise(text).indexOf(normalise(q));
      if(idx === -1) return;
      const before = text.slice(0, idx);
      const hit = text.slice(idx, idx + q.length);
      const after = text.slice(idx + q.length);
      el.innerHTML = `${escapeHtml(before)}<mark class="search-hit">${escapeHtml(hit)}</mark>${escapeHtml(after)}`;
    }

    function escapeHtml(s){
      return s.replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
      }[m]));
    }

    function applyFilter(){
      const qRaw = input.value.trim();
      const q = normalise(qRaw);
      const hasQuery = q.length > 0;

      // Hide/show the info banners only while searching
      infoBoxes.forEach(box => box.style.display = hasQuery ? 'none' : '');
      if (dealNote) dealNote.style.display = hasQuery ? 'none' : '';

      let totalMatches = 0;

      document.querySelectorAll('.category-block').forEach(block => {
        const content = block.querySelector('.category-content');
        const rows = content ? Array.from(content.querySelectorAll('.item-row')) : [];
        let matchesInBlock = 0;

        rows.forEach(row => {
          const title = row.querySelector('h4');
          const desc = row.querySelector('p');
          if (title && title.dataset.orig) title.innerHTML = title.dataset.orig;
          if (desc && desc.dataset.orig) desc.innerHTML = desc.dataset.orig;

          if(!hasQuery){
            row.style.display = '';
            return;
          }

          const ok = normalise(row.dataset.search || '').includes(q);
          row.style.display = ok ? '' : 'none';

          if(ok){
            matchesInBlock++;
            totalMatches++;
            highlight(title, qRaw);
            highlight(desc, qRaw);
          }
        });

        if(hasQuery){
          block.classList.toggle('is-hidden', matchesInBlock === 0);
          if(matchesInBlock > 0){
            if(content && content.style.display !== 'block') content.style.display = 'block';
          } else if (content) {
            content.style.display = 'none';
          }
        } else {
          block.classList.remove('is-hidden');
        }
      });

      noResultsEl.style.display = (!hasQuery || totalMatches > 0) ? 'none' : 'block';
    }

    // Preload from ?q=
    try {
      const url = new URL(window.location.href);
      const qParam = url.searchParams.get('q');
      if(qParam){
        input.value = qParam;
        applyFilter();
      }
    } catch(e){}
  });

  // Open options sheet
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-open-options]');
    if(!btn) return;
    e.preventDefault();
    const name = btn.getAttribute('data-item') || '';
    const type = btn.getAttribute('data-type') || 'pizza';
    if (window.openOptions) window.openOptions({ name, type });
  });
</script>

<!-- Bottom nav + Basket + Theme toggle + Options sheet -->
<?php include __DIR__ . '/partials/bottom-nav.php'; ?>
<?php include __DIR__ . '/partials/basket.php'; ?>
<?php include __DIR__ . '/options.php'; ?>
<?php include __DIR__ . '/partials/theme-toggle.php'; ?> 
<?php include __DIR__ . '/includes/footer.php'; ?> 

</body>
</html>
