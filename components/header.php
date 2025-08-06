<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'Ecommerce Store'; ?></title>
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<header>
  <div class="header-container">
    <div class="logo"><a href="/pages/index.php">🛒 Store</a></div>
    <nav class="nav-menu">
      <div><a href="/pages/index.php">Home</a></div>
      <div><a href="/pages/products.php">Products</a></div>
      <?php if (isLoggedIn()): ?>
        <div><a href="/pages/cart.php">Cart (<?php echo getCartCount($_SESSION['user_id']); ?>)</a></div>
        <div><a href="/pages/orders.php">Orders</a></div>
        <div><a href="/pages/profile.php">Profile</a></div>
        <?php if (isAdmin()): ?>
          <div><a href="/pages/admin.php">Admin</a></div>
        <?php endif; ?>
        <div><a href="/pages/logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a></div>
      <?php else: ?>
        <div><a href="/pages/login.php">Login</a></div>
        <div><a href="/pages/signup.php">Sign Up</a></div>
      <?php endif; ?>
    </nav>
  </div>
</header>