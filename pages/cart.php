<?php 
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$pageTitle = 'Shopping Cart - Ecommerce Store';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        $conn = getConnection();
        foreach ($_POST['quantities'] as $cart_id => $quantity) {
            $cart_id = intval($cart_id);
            $quantity = intval($quantity);
            
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or less
                $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
                $stmt->execute();
            } else {
                // Update quantity
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("iii", $quantity, $cart_id, $_SESSION['user_id']);
                $stmt->execute();
            }
        }
        $conn->close();
        $success = 'Cart updated successfully!';
    } elseif (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = 'Item removed from cart!';
        } else {
            $error = 'Failed to remove item from cart';
        }
        $stmt->close();
        $conn->close();
    }
}

$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <h1>Shopping Cart</h1>
    
    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (empty($cartItems)): ?>
      <div class="empty-cart">
        <h3>Your cart is empty</h3>
        <p>Start shopping to add items to your cart!</p>
        <a href="products.php" class="btn btn-lg">Shop Now</a>
      </div>
    <?php else: ?>
      <div class="cart-container">
        <div class="cart-items">
          <form method="post">
            <?php foreach ($cartItems as $item): ?>
              <div class="cart-item">
                <?php if ($item['image']): ?>
                  <img src="/uploads/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <?php else: ?>
                  <div style="width: 80px; height: 80px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 0.8rem; color: #666;">No Image</div>
                <?php endif; ?>
                
                <div class="cart-item-info">
                  <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                  <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                </div>
                
                <div class="cart-item-price">
                  $<?php echo number_format($item['price'], 2); ?>
                </div>
                
                <div class="cart-item-quantity">
                  <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0" max="99">
                </div>
                
                <div class="cart-item-total">
                  $<?php echo number_format($item['total'], 2); ?>
                </div>
              </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 20px; display: flex; gap: 10px;">
              <button type="submit" name="update_cart" class="btn">Update Cart</button>
            </div>
          </form>
        </div>
        
        <div class="cart-summary">
          <div class="cart-total">
            <h3>Total: $<?php echo number_format($cartTotal, 2); ?></h3>
          </div>
          
          <div class="cart-actions">
            <a href="products.php" class="btn">Continue Shopping</a>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
          </div>
        </div>
      </div>
      
      <!-- Individual Remove Buttons -->
      <script>
      function removeItem(cartId) {
        if (confirm('Are you sure you want to remove this item from your cart?')) {
          const form = document.createElement('form');
          form.method = 'post';
          form.innerHTML = '<input type="hidden" name="cart_id" value="' + cartId + '">' +
                          '<input type="hidden" name="remove_item" value="1">';
          document.body.appendChild(form);
          form.submit();
        }
      }
      
      // Add remove buttons to each cart item
      document.querySelectorAll('.cart-item').forEach((item, index) => {
        const cartId = <?php echo json_encode(array_column($cartItems, 'id')); ?>[index];
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger';
        removeBtn.style.padding = '5px 10px';
        removeBtn.style.fontSize = '0.8rem';
        removeBtn.innerHTML = '×';
        removeBtn.onclick = () => removeItem(cartId);
        item.appendChild(removeBtn);
      });
      </script>
    <?php endif; ?>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>