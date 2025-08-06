<?php 
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$success = '';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

$product = getProduct($product_id);
if (!$product) {
    header('Location: products.php');
    exit();
}

$pageTitle = $product['name'] . ' - Ecommerce Store';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $error = 'Please login to add items to cart';
    } else {
        $quantity = intval($_POST['quantity']);
        if ($quantity <= 0) {
            $error = 'Please enter a valid quantity';
        } elseif ($quantity > $product['stock']) {
            $error = 'Not enough stock available';
        } else {
            if (addToCart($_SESSION['user_id'], $product_id, $quantity)) {
                $success = 'Product added to cart successfully!';
            } else {
                $error = 'Failed to add product to cart';
            }
        }
    }
}
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <div class="product-detail">
      <div class="product-detail-content">
        <div class="product-image">
          <?php if ($product['image']): ?>
            <img src="/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
          <?php else: ?>
            <div class="no-image" style="height: 400px; font-size: 1.5rem;">No Image Available</div>
          <?php endif; ?>
        </div>
        
        <div class="product-info">
          <h1><?php echo htmlspecialchars($product['name']); ?></h1>
          <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
          <div class="category"><?php echo htmlspecialchars($product['category_name']); ?></div>
          <div class="stock">Available: <?php echo $product['stock']; ?> units</div>
          
          <div class="description" style="margin: 1.5rem 0; line-height: 1.8;">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
          </div>
          
          <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
          <?php endif; ?>
          
          <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
          <?php endif; ?>
          
          <?php if ($product['stock'] > 0): ?>
            <form method="post" style="margin-top: 2rem;">
              <div class="quantity-selector">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
              </div>
              
              <?php if (isLoggedIn()): ?>
                <button type="submit" name="add_to_cart" class="btn btn-lg" style="width: 100%;">
                  Add to Cart
                </button>
              <?php else: ?>
                <div style="text-align: center; margin: 1rem 0;">
                  <p>Please <a href="login.php">login</a> to add items to your cart</p>
                </div>
              <?php endif; ?>
            </form>
          <?php else: ?>
            <div class="alert alert-info">
              <strong>Out of Stock</strong> - This product is currently unavailable
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div style="text-align: center; margin: 2rem 0;">
      <a href="products.php" class="btn">← Back to Products</a>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>