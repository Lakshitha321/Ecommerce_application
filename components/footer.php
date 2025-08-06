<footer>
    <div class="footer-content">
        <p>Copyright &copy; <?php echo date("Y"); ?> - Ecommerce Store</p>
        <p>Built with PHP & MySQL</p>
    </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>
```

---

## pages/index.php
```php
<?php 
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Home - Ecommerce Store';
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <h1>Welcome to Our Store</h1>
    
    <?php if (!isLoggedIn()): ?>
      <div class="welcome-message">
        <p>Please <a href="login.php">login</a> or <a href="signup.php">sign up</a> to start shopping!</p>
      </div>
    <?php endif; ?>
    
    <section class="featured-products">
      <h2>Featured Products</h2>
      <div class="products-grid">
        <?php 
        $products = array_slice(getAllProducts(), 0, 6);
        if (empty($products)): ?>
          <p>No products available at the moment.</p>
        <?php else: ?>
          <?php foreach ($products as $product): ?>
            <div class="product-card">
              <?php if ($product['image']): ?>
                <img src="/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
              <?php else: ?>
                <div class="no-image">No Image</div>
              <?php endif; ?>
              <h3><?php echo htmlspecialchars($product['name']); ?></h3>
              <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
              <p class="category"><?php echo htmlspecialchars($product['category_name']); ?></p>
              <p class="stock">Stock: <?php echo $product['stock']; ?></p>
              <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="text-center">
        <a href="products.php" class="btn btn-lg">View All Products</a>
      </div>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>