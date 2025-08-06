<?php 
require_once __DIR__ . 'functions.php';
$pageTitle = 'Welcome - Ecommerce Store';
?>

<?php require_once __DIR__ . '/components/header.php'; ?>

<main>
  <div class="container">
    <!-- Hero Section -->
    <div class="welcome-message">
      <h1>🛒 Welcome to Our Store</h1>
      <p style="font-size: 1.2rem; margin: 1rem 0;">Discover amazing products at great prices!</p>
      
      <?php if (!isLoggedIn()): ?>
        <div style="margin: 2rem 0;">
          <p style="margin-bottom: 1rem;">Join thousands of satisfied customers today!</p>
          <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="pages/login.php" class="btn btn-lg">Login</a>
            <a href="pages/signup.php" class="btn btn-lg btn-success">Sign Up Now</a>
          </div>
        </div>
      <?php else: ?>
        <div style="margin: 2rem 0;">
          <h3>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
          <p>Ready to discover new products?</p>
          <a href="pages/products.php" class="btn btn-lg">Browse Products</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Stats -->
    <?php if (isLoggedIn()): ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 2rem 0;">
        <div style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
          <h3 style="margin: 0; color: white;">Cart Items</h3>
          <p style="font-size: 2rem; margin: 0.5rem 0; font-weight: bold;"><?php echo getCartCount($_SESSION['user_id']); ?></p>
          <a href="pages/cart.php" style="color: #fff; text-decoration: underline;">View Cart</a>
        </div>
        
        <?php 
        // Get user's order count
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $orderCount = $stmt->get_result()->fetch_assoc()['count'];
        $conn->close();
        ?>
        <div style="background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
          <h3 style="margin: 0; color: white;">My Orders</h3>
          <p style="font-size: 2rem; margin: 0.5rem 0; font-weight: bold;"><?php echo $orderCount; ?></p>
          <a href="pages/orders.php" style="color: #fff; text-decoration: underline;">View Orders</a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Categories Section -->
    <section style="margin: 3rem 0;">
      <h2 style="text-align: center; color: #343a40; margin-bottom: 2rem;">Shop by Category</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <?php 
        $categories = getAllCategories();
        $categoryIcons = [
          'Electronics',
          'Clothing',
          'Books',
          'Home & Garden'
        ];
        
        foreach ($categories as $category): 
          $icon = $categoryIcons[$category['name']] ?? '🛍️';
        ?>
          <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s; cursor: pointer;" 
               onmouseover="this.style.transform='translateY(-5px)'" 
               onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 3rem; margin-bottom: 1rem;"><?php echo $icon; ?></div>
            <h3 style="color: #007bff; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($category['name']); ?></h3>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 1.5rem;">
              <?php echo htmlspecialchars($category['description']); ?>
            </p>
            <a href="pages/products.php?category=<?php echo $category['id']; ?>" class="btn">Browse <?php echo htmlspecialchars($category['name']); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    
    <!-- Featured Products Section -->
    <section class="featured-products" style="margin: 3rem 0;">
      <h2 style="text-align: center; color: #343a40; margin-bottom: 2rem;">Featured Products</h2>
      
      <div class="products-grid">
        <?php 
        $products = array_slice(getAllProducts(), 0, 6);
        if (empty($products)): ?>
          <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
            <h3>Coming Soon!</h3>
            <p>We're adding amazing products to our store. Check back soon!</p>
            <?php if (isAdmin()): ?>
              <div style="margin-top: 2rem;">
                <a href="pages/admin_add_product.php" class="btn btn-success">Add First Product</a>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <?php foreach ($products as $product): ?>
            <div class="product-card">
              <?php if ($product['image']): ?>
                <img src="/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     loading="lazy">
              <?php else: ?>
                <div class="no-image"><br>No Image</div>
              <?php endif; ?>
              
              <div style="padding: 15px;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.2rem;">
                  <?php echo htmlspecialchars($product['name']); ?>
                </h3>
                
                <p class="price" style="margin: 0.5rem 0; font-size: 1.3rem;">
                  $<?php echo number_format($product['price'], 2); ?>
                </p>
                
                <p class="category" style="margin: 0.25rem 0;">
                  <?php echo htmlspecialchars($product['category_name']); ?>
                </p>
                
                <p class="stock" style="margin: 0.25rem 0; font-size: 0.9rem;">
                  <?php if ($product['stock'] > 10): ?>
                     In Stock
                  <?php elseif ($product['stock'] > 0): ?>
                     Only <?php echo $product['stock']; ?> left
                  <?php else: ?>
                     Out of Stock
                  <?php endif; ?>
                </p>
                
                <p style="color: #666; font-size: 0.9rem; margin: 1rem 0;">
                  <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . (strlen($product['description']) > 80 ? '...' : ''); ?>
                </p>
              </div>
              
              <a href="pages/product.php?id=<?php echo $product['id']; ?>" class="btn">
                View Details
              </a>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      
      <?php if (!empty($products)): ?>
        <div class="text-center" style="margin-top: 2rem;">
          <a href="pages/products.php" class="btn btn-lg">🛍️ View All Products</a>
        </div>
      <?php endif; ?>
    </section>

    <!-- Features Section -->
    <section style="margin: 4rem 0; padding: 3rem 0; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 15px;">
      <h2 style="text-align: center; color: #343a40; margin-bottom: 3rem;">Why Choose Our Store?</h2>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; padding: 0 2rem;">
        <div style="text-align: center;">
          <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
          <h3 style="color: #007bff;">Fast Delivery</h3>
          <p style="color: #666;">Quick and reliable shipping to your doorstep</p>
        </div>
        
        <div style="text-align: center;">
          <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
          <h3 style="color: #007bff;">Secure Payment</h3>
          <p style="color: #666;">Safe and secure payment processing</p>
        </div>
        
        <div style="text-align: center;">
          <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
          <h3 style="color: #007bff;">Quality Guarantee</h3>
          <p style="color: #666;">High-quality products with warranty</p>
        </div>
        
        <div style="text-align: center;">
          <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
          <h3 style="color: #007bff;">24/7 Support</h3>
          <p style="color: #666;">Round-the-clock customer service</p>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <?php if (!isLoggedIn()): ?>
      <section style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 3rem; border-radius: 15px; text-align: center; margin: 3rem 0;">
        <h2 style="color: white; margin-bottom: 1rem;">Ready to Start Shopping?</h2>
        <p style="font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9;">
          Join our community of satisfied customers and discover amazing deals!
        </p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
          <a href="pages/signup.php" class="btn" style="background: white; color: #007bff;">Create Account</a>
          <a href="pages/products.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">Browse Products</a>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>

<?php require_once __DIR__ . '/components/footer.php'; ?>