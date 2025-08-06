<?php 
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$pageTitle = 'Checkout - Ecommerce Store';

$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

// Get user info
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    
    if (empty($shipping_address)) {
        $error = 'Please provide a shipping address';
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $_SESSION['user_id'], $cartTotal, $shipping_address);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order items and update product stock
            foreach ($cartItems as $item) {
                // Add to order_items
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
                
                // Update product stock
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt->execute();
            }
            
            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            
            $conn->commit();
            
            // Redirect to orders page
            header('Location: orders.php?success=order_placed');
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Failed to place order. Please try again.';
        }
    }
}

$conn->close();
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <h1>Checkout</h1>
    
    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
      <!-- Order Summary -->
      <div class="cart-container">
        <div class="cart-items">
          <h3>Order Summary</h3>
          <?php foreach ($cartItems as $item): ?>
            <div class="cart-item" style="grid-template-columns: 60px 1fr auto;">
              <?php if ($item['image']): ?>
                <img src="/uploads/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px;">
              <?php else: ?>
                <div style="width: 60px; height: 60px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 0.7rem;">No Image</div>
              <?php endif; ?>
              
              <div class="cart-item-info">
                <h4 style="margin: 0 0 5px 0; font-size: 1rem;"><?php echo htmlspecialchars($item['name']); ?></h4>
                <p style="margin: 0; font-size: 0.9rem; color: #666;">
                  $<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                </p>
              </div>
              
              <div class="cart-item-total">
                $<?php echo number_format($item['total'], 2); ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
          <div class="cart-total">
            <h3>Total: $<?php echo number_format($cartTotal, 2); ?></h3>
          </div>
        </div>
      </div>
      
      <!-- Shipping Information -->
      <div class="form-container" style="max-width: none;">
        <h3>Shipping Information</h3>
        
        <form method="post">
          <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Customer Details:</label>
            <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 0.9rem;">
              <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
              <?php echo htmlspecialchars($user['email']); ?><br>
              <?php if ($user['phone']): ?>
                Phone: <?php echo htmlspecialchars($user['phone']); ?>
              <?php endif; ?>
            </div>
          </div>
          
          <label for="shipping_address" style="display: block; margin-bottom: 5px; font-weight: 500;">Shipping Address:</label>
          <textarea name="shipping_address" id="shipping_address" placeholder="Enter your complete shipping address" rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
          
          <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 15px 0; font-size: 0.9rem;">
            <strong>Payment:</strong><br>
            Cash on Delivery (COD) - Pay when you receive your order
          </div>
          
          <button type="submit" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 1.1rem;">
            Place Order
          </button>
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
          <a href="cart.php">← Back to Cart</a>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>