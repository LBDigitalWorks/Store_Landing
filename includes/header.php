<?php
if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Restaurant</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">
  <style>
    body { background-color:#0b0b0c; color:#d4d4d8; }
    .brand { font-family:'Cinzel', serif; }
  </style>
</head>
<body>
  <header class="sticky top-0 bg-zinc-950/90 backdrop-blur border-b border-zinc-800">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
      <a href="/store/index.php" class="flex items-center gap-2">
        <img src="/assets/logo-nexaria.svg" class="h-8" alt="Nexaria">
        <span class="brand text-xl">Nexaria</span>
      </a>
      <nav class="hidden md:flex gap-6 text-sm">
        <a href="/store/index.php" class="hover:text-zinc-100 text-zinc-300">Home</a>
        <a href="/store/categories.php" class="hover:text-zinc-100 text-zinc-300">Categories</a>
        <a href="/store/products.php" class="hover:text-zinc-100 text-zinc-300">All Items</a>
      </nav>
      <a href="/store/cart.php" class="relative px-3 py-2 rounded-xl border border-zinc-800 bg-zinc-900 hover:bg-zinc-800">
        Cart <span class="absolute -top-2 -right-2 text-xs px-2 rounded-full bg-red-700"><?= (int)$cartCount ?></span>
      </a>
    </div>
  </header>
