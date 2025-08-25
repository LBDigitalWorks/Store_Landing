<?php
require_once __DIR__ . '/config.php';   // add this


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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Premi Spice</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0; padding: 0; overflow-x: hidden; background-color: #f9f9f9;
    }
    .banner { position: relative; width: 100%; height: 200px;
      background: url('assets/images/burgers2.jfif') no-repeat center center; background-size: cover;
      margin: 0; padding: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .banner::after { content: ''; position: absolute; inset: 0; background-color: rgba(0, 0, 0, 0.4); }
    .icons { position: absolute; top: 15px; inset-inline: 15px; display: flex; justify-content: space-between; z-index: 2; }
    .icons i { color: white; font-size: 18px; background: rgba(0,0,0,0.4); padding: 8px; border-radius: 50%; }
    .logo-container { position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%);
      width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid white; z-index: 3;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .logo-container img { width: 100%; height: 100%; object-fit: cover; }
    .container { padding: 20px 20px 80px; text-align: center; margin-top: 10px; }
    .restaurant-name { font-size: 24px; font-weight: 600; margin: 10px 0 2px; }
    .address { color: #555; font-size: 14px; margin: 0; display: flex; justify-content: center; align-items: center; gap: 5px; }
    .rating { color: #f6b100; font-weight: 600; font-size: 15px; margin: 8px 0 20px; }
    .buttons { display: flex; justify-content: center; gap: 10px; flex-wrap: nowrap; margin-bottom: 20px; }
    .btn { display: flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 25px; background: #f1f1f1; border: 1px solid #ccc; font-size: 14px; font-weight: 500; color: #333; }
    .btn i { font-size: 14px; }

    .closed-banner, .open-banner, .alert-note {
      padding: 14px 16px; margin: 20px auto; border-radius: 10px; max-width: 90%;
      font-size: 14px; text-align: center; font-weight: 500; display: flex; justify-content: center; align-items: center; gap: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .closed-banner { background: #ffe6e6; color: #b30000; }
    .open-banner   { background: #e6ffe6; color: #006600; }
    .alert-note    { background: #ffe6e6; color: #b30000; }

    .recommended-section { text-align: left; padding: 10px 15px; }
    .recommended-items { position: relative; display: flex; overflow-x: auto; gap: 10px; padding-bottom: 10px; width: 100%;
      box-sizing: border-box; scroll-snap-type: x mandatory; }
    .recommended-items::after { content: ""; position: absolute; top: 0; right: 0; width: 40px; height: 100%;
      background: linear-gradient(to right, rgba(255,255,255,0), #f9f9f9); pointer-events: none; }
    .item-card { min-width: 130px; background: white; padding: 12px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      text-align: center; flex-shrink: 0; scroll-snap-align: start; }
    .scroll-hint { font-size: 13px; color: #aaa; text-align: right; padding-right: 15px; margin-top: 4px; }

    .menu-categories { margin-top: 30px; background: white; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
    .category-block { border-bottom: 1px solid #eee; }
    .category-row { padding: 16px; display: flex; justify-content: space-between; align-items: center;
      font-size: 16px; font-weight: 500; color: #333; cursor: pointer; background-color: #fdfdfd; }
    .category-row:hover { background: #f9f9f9; }
    .category-content { display: none; padding: 15px; background-color: #fff; }

    .bottom-nav {
      position: fixed; bottom: 0; left: 0; width: 100%; background: white; border-top: 1px solid #ccc;
      display: flex; justify-content: space-around; align-items: center; padding: 10px 0; z-index: 9999; height: 40px;
      box-shadow: 0 -1px 4px rgba(0,0,0,0.05);
    }
    .bottom-nav .nav-item { text-decoration: none; color: #333; display: block; text-align: center; font-size: 12px; }
    .bottom-nav .nav-item i { display: block; font-size: 18px; margin-bottom: 4px; }
    .bottom-nav .nav-item.active { color: #f04f32; }
  </style>
</head>
<body>
<div class="banner">
  <div class="icons"><i class="fas fa-arrow-left"></i><i class="fas fa-search"></i></div>
  <div class="logo-container"><img src="assets/images/logo.jpg" alt="Logo"></div>
</div>

<div class="container"> 
  <div class="restaurant-name">Your Restaurant Name</div>
  <div class="address"><i class="fas fa-map-marker-alt"></i> 12 Park Lane, Northside</div>
  <div class="rating"><i class="fas fa-star"></i> 4.6 (44 reviews)</div>

  <?php if (!$restaurant_open): ?>
    <div class="buttons">
      <div class="btn" style="background: #fff;">
        <i class="fas fa-truck"></i> Delivery 
        <span class="status" style="margin-left: 6px; color: #fff; background: #6c757d; padding: 2px 10px; border-radius: 12px; font-size: 13px; font-weight: 500;">Closed</span>
      </div>
      <div class="btn" style="background: #fff;">
        <i class="fas fa-shopping-bag"></i> Pickup 
        <span class="status" style="margin-left: 6px; color: #fff; background: #6c757d; padding: 2px 10px; border-radius: 12px; font-size: 13px; font-weight: 500;">Closed</span>
      </div>
    </div>
    <div class="closed-banner">
      <i class="fas fa-door-closed"></i> We're closed at the moment. Please check back during our opening hours.
    </div>
  <?php else: ?>
    <div class="buttons" style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-top: 10px;">
      <div style="display: flex; align-items: center; background: #fff; border: 1px solid #ccc; border-radius: 12px; padding: 8px 12px; font-size: 14px; font-weight: 500; white-space: nowrap;">
        <i class="fas fa-truck" style="margin-right: 6px;"></i> Delivery
        <span style="margin-left: 8px; background: #eee; color: #333; padding: 2px 8px; border-radius: 10px; font-size: 13px; font-weight: 600;">30–45 mins</span>
      </div>
      <div style="display: flex; align-items: center; background: #fff; border: 1px solid #ccc; border-radius: 12px; padding: 8px 12px; font-size: 14px; font-weight: 500; white-space: nowrap;">
        <i class="fas fa-shopping-bag" style="margin-right: 6px;"></i> Collection
        <span style="margin-left: 8px; background: #eee; color: #333; padding: 2px 8px; border-radius: 10px; font-size: 13px; font-weight: 600;">15 mins</span>
      </div>
    </div>
    <div class="open-banner">
      <i class="fas fa-door-open"></i> We're open now – place your order!
    </div>
  <?php endif; ?>

  <!-- About anchor -->
  <div id="about" style="height:1px;"></div>

  <div class="recommended-section">
    <h3><i class="fas fa-thumbs-up"></i> RECOMMENDED FOR YOU</h3>
    <div class="recommended-items">
      <?php foreach ($recommended_items as $item): ?>
        <div class="item-card">
          <p><strong><?= htmlspecialchars($item['name']) ?></strong></p>
          <p>£<?= number_format($item['price'], 2) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="scroll-hint"><i class="fas fa-angle-right"></i> Swipe</div>
  </div>

  <!-- Deal Note Box -->
  <div style="background: #e8f5e9; border-left: 4px solid #43a047; padding: 14px 16px; margin: 20px 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); font-size: 14px; color: #256029;">
    <i class="fas fa-tag" style="margin-right: 6px;"></i>
    Special offers available under <strong>Meal Deals</strong> — scroll down to save big!
  </div>

  <!-- Menu -->
  <div class="menu-categories" id="menu">
    <?php
      $categories = [
        'Meal Deals', 'Pizzas', 'Special Pizzas', 'Loaded Fries', 'Shawarma', 'Garlic Bread',
        'Calzone', 'Appetisers', 'House Special', 'Donner Kebabs', 'Burger Bar', 'Special Burgers',
        'New Grilled', 'Wraps', 'Fried Chicken', 'Sheesh Mixed Special', 'Tandoori Main Dishes',
        'Paninis', 'Bbq Platter', 'English Dishes', 'Salad Bar', 'Extras', 'Kids Meals',
        'Desserts', 'Drinks'
      ];

      foreach ($categories as $category) {
        echo '<div class="category-block">';
        echo '<div class="category-row"><span>' . $category . '</span><i class="fas fa-chevron-down"></i></div>';

        if ($category === 'Meal Deals') {
          echo '<div class="category-content">';
          echo '<div style="background: #fff8e1; color: #8a6d3b; border-left: 4px solid #f0ad4e; padding: 14px 16px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-size: 14px;">
            <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
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
            ["name" => "Deal 10", "desc" => 'Any 2x 1/4lb Burgers with Chips, Any 10" Pizza, Tray of Donner Meat & a Bottle of Drink. (Excluding Special Burgers)', "price" => 24.00],
            ["name" => "Deal 11", "desc" => '2x Any Wrap, Chips & 2x Cans of Drinks, No Sheesh Wraps in this Deal', "price" => 15.00],
            ["name" => "Deal 12", "desc" => '2x Large Donner Meat, Chips & 2 Cans of Drinks', "price" => 15.00],
            ["name" => "Deal 13", "desc" => '2x Mix Donner Special, Large Chips & 2 Cans of Pepsi', "price" => 20.00],
            ["name" => "Deal 14", "desc" => '2x Large Donner, 1x Large Chips & 2x Cans of Pepsi', "price" => 18.00],
          ];

          foreach ($meal_deals as $deal) {
            echo '<div style="margin-bottom: 15px; padding: 16px; background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; gap: 12px;">';
            echo '  <div style="flex: 1; text-align: left;">';
            echo '    <h4 style="margin: 0 0 6px; font-size: 17px;">' . $deal["name"] . '</h4>';
            echo '    <p style="margin: 0 0 8px; font-size: 14px; color: #555;">' . $deal["desc"] . '</p>';
            echo '    <span style="font-weight: bold; font-size: 15px;">£' . number_format($deal["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink: 0;">';
            echo '    <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">';
            echo '    <button style="padding: 6px 10px; background: #f04f32; border: none; color: white; border-radius: 5px; font-size: 13px; cursor: pointer;"><i class="fas fa-plus"></i></button>';
            echo '  </div>';
            echo '</div>';
          }
          echo '</div>';

        } elseif ($category === 'Pizzas') {
          echo '<div class="category-content">';
          echo '<div style="background: #fff8e1; color: #8a6d3b; border-left: 4px solid #f0ad4e; padding: 14px 16px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-size: 14px;">
            <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
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
            echo '<div style="margin-bottom: 20px;">';
            echo '  <h4 style="margin: 6px 0;">' . $pizza["name"] . (!empty($pizza["veg"]) ? ' <span style="font-size: 14px;">🌱</span>' : '') . '</h4>';
            echo '  <p style="margin: 0 0 8px; font-size: 14px; color: #666;">' . $pizza["desc"] . '</p>';
            foreach ($pizza["sizes"] as $size) {
              echo '<div style="display:flex; justify-content:space-between; align-items:center; padding: 4px 0;">';
              echo '  <span>' . $size[0] . ' £' . number_format($size[1], 2) . '</span>';
              echo '  <button style="padding: 6px 10px; background: #f04f32; border: none; color: white; border-radius: 5px; font-size: 13px; cursor: pointer;"><i class="fas fa-plus"></i></button>';
              echo '</div>';
            }
            echo '</div>';
          }
          echo '</div>';

        } elseif ($category === 'Special Pizzas') {
          echo '<div class="category-content">';
          echo '<div style="background: #fff8e1; color: #8a6d3b; border-left: 4px solid #f0ad4e; padding: 14px 16px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-size: 14px;">
            <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
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
            echo '<div style="margin-bottom: 15px; padding: 15px; background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.07); text-align: left;">';
            echo '  <h4 style="margin: 0 0 5px;">' . $pizza["name"] . '</h4>';
            echo '  <p style="margin: 0 0 8px; font-size: 14px; color: #555;">' . $pizza["desc"] . '</p>';
            foreach ($pizza["sizes"] as $size) {
              echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">';
              echo '  <span>' . $size[0] . '</span>';
              echo '  <div style="display: flex; align-items: center; gap: 10px;">';
              echo '    <span style="font-weight: bold;">£' . number_format($size[1], 2) . '</span>';
              echo '    <button style="padding: 6px 10px; background: #f04f32; border: none; color: white; border-radius: 5px; font-size: 13px; cursor: pointer;"><i class="fas fa-plus"></i></button>';
              echo '  </div>';
              echo '</div>';
            }
            echo '</div>';
          }
          echo '</div>';

        } elseif ($category === 'Loaded Fries') {
          echo '<div class="category-content">';
          echo '<div style="background: #fff8e1; color: #8a6d3b; border-left: 4px solid #f0ad4e; padding: 14px 16px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-size: 14px;">
            <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
            <strong>All loaded fries are topped generously and served hot.</strong>
          </div>';

          $Loaded_Fries = [
            [ "name" => "Loaded Fries Original", "desc" => "Chips, cheese, crispy chicken, cheese sauce & Jalapenos", "price" => 8.00 ],
            [ "name" => "Loaded Fries Peri Peri", "desc" => "Chips, cheese, peri peri chicken, fried onions, cheese sauce", "price" => 8.50 ],
            [ "name" => "Chiraag Special Loaded Fries", "desc" => "Chips, cheese, red donner, fried onions, cheese sauce", "price" => 8.50 ]
          ];

          foreach ($Loaded_Fries as $item) {
            echo '<div style="margin-bottom: 15px; padding: 16px; background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; gap: 12px;">';
            echo '  <div style="flex: 1; text-align: left;">';
            echo '    <h4 style="margin: 0 0 6px; font-size: 17px;">' . $item["name"] . '</h4>';
            echo '    <p style="margin: 0 0 8px; font-size: 14px; color: #555;">' . $item["desc"] . '</p>';
            echo '    <span style="font-weight: bold; font-size: 15px;">£' . number_format($item["price"], 2) . '</span>';
            echo '  </div>';
            echo '  <div style="flex-shrink: 0;">';
            echo '    <button style="padding: 6px 10px; background: #f04f32; border: none; color: white; border-radius: 5px; font-size: 13px; cursor: pointer;"><i class="fas fa-plus"></i></button>';
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
  });
</script>

<!-- Bottom nav using ABSOLUTE URLs built from BASE_URL -->

<?php include __DIR__ . '/partials/bottom-nav.php'; ?>

</body>
</html>
