<?php 
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'My Orders - Ecommerce Store';
$success = isset($_GET['success']) && $_GET['success'] == 'order_placed' ? 'Order placed successfully!' : '';

// Get user orders
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Function to get order items
function getOrderItems($order_id) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $items;
}
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <h1>My Orders</h1>
    
    <?php if ($success): ?>
      <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
      <div class="empty-cart">
        <h3>No orders found</h3>
        <p>You haven't placed any orders yet.</p>
        <a href="products.php" class="btn btn-lg">Start Shopping</a>
      </div>
    <?php else: ?>
      <div class="orders-container">
        <?php foreach ($orders as $order): ?>
          <div class="order-item">
            <div class="order-header">
              <div>
                <h3>Order #<?php echo $order['id']; ?></h3>
                <p>Placed on: <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
              </div>
              <div>
                <div class="order-status <?php echo $order['status']; ?>">
                  <?php echo ucfirst($order['status']); ?>
                </div>
                <div style="text-align: right; margin-top: 5px;">
                  <strong>Total: $<?php echo number_format($order['total_amount'], 2); ?></strong>
                </div>
              </div>
            </div>
            
            <div>
              <h4>Items:</h4>
              <ul class="order-items">
                <?php 
                $orderItems = getOrderItems($order['id']);
                foreach ($orderItems as $item): 
                ?>
                  <li>
                    <?php echo htmlspecialchars($item['name']); ?> 
                    (Qty: <?php echo $item['quantity']; ?>) - 
                    $<?php echo number_format($item['price'], 2); ?> each
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
            
            <div style="margin-top: 15px;">
              <h4>Shipping Address:</h4>
              <p style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 5px 0;">
                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
              </p>
            </div>
            
            <?php if ($order['status'] == 'pending'): ?>
              <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; font-size: 0.9rem;">
                <strong>Order Status:</strong> Your order is being processed. You will receive a confirmation shortly.
              </div>
            <?php elseif ($order['status'] == 'processing'): ?>
              <div style="margin-top: 15px; padding: 10px; background: #d1ecf1; border: 1px solid #b8daff; border-radius: 4px; font-size: 0.9rem;">
                <strong>Order Status:</strong> Your order is being prepared for shipment.
              </div>
            <?php elseif ($order['status'] == 'shipped'): ?>
              <div style="margin-top: 15px; padding: 10px; background: #cce5ff; border: 1px solid #99d6ff; border-radius: 4px; font-size: 0.9rem;">
                <strong>Order Status:</strong> Your order has been shipped and is on its way to you!
              </div>
            <?php elseif ($order['status'] == 'delivered'): ?>
              <div style="margin-top: 15px; padding: 10px; background: #d4edda; border: 1px solid #b7d4c0; border-radius: 4px; font-size: 0.9rem;">
                <strong>Order Status:</strong> Your order has been delivered successfully!
              </div>
            <?php elseif ($order['status'] == 'cancelled'): ?>
              <div style="margin-top: 15px; padding: 10px; background: #f8d7da; border: 1px solid #f1aeb5; border-radius: 4px; font-size: 0.9rem;">
                <strong>Order Status:</strong> This order has been cancelled.
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>