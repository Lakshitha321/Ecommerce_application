<?php 
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Admin Panel - Ecommerce Store';

// Get statistics
$conn = getConnection();

// Count products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$productCount = $result->fetch_assoc()['count'];

// Count users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
$userCount = $result->fetch_assoc()['count'];

// Count orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$orderCount = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('delivered', 'shipped', 'processing')");
$totalRevenue = $result->fetch_assoc()['total'] ?? 0;

// Recent orders
$stmt = $conn->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$stmt->execute();
$recentOrders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get all products for management
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$stmt->execute();
$allProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <h1>Admin Panel</h1>
    
    <!-- Statistics Dashboard -->
    <div class="admin-actions">
      <div class="admin-card">
        <h3><?php echo $productCount; ?></h3>
        <p>Total Products</p>
      </div>
      <div class="admin-card">
        <h3><?php echo $userCount; ?></h3>
        <p>Total Users</p>
      </div>
      <div class="admin-card">
        <h3><?php echo $orderCount; ?></h3>
        <p>Total Orders</p>
      </div>
      <div class="admin-card">
        <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
        <p>Total Revenue</p>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="admin-panel">
      <h2>Quick Actions</h2>
      <div class="admin-actions">
        <div class="admin-card">
          <h3>Products</h3>
          <p>Manage your product catalog</p>
          <a href="admin_add_product.php" class="btn">Add New Product</a>
        </div>
        <div class="admin-card">
          <h3>Orders</h3>
          <p>View and manage customer orders</p>
          <a href="#orders-section" class="btn">View Orders</a>
        </div>
        <div class="admin-card">
          <h3>Users</h3>
          <p>Manage user accounts</p>
          <a href="#users-section" class="btn">View Users</a>
        </div>
      </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="admin-panel" id="orders-section">
      <h2>Recent Orders</h2>
      <?php if (empty($recentOrders)): ?>
        <p>No orders found.</p>
      <?php else: ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['username']); ?></td>
                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                  <span class="order-status <?php echo $order['status']; ?>">
                    <?php echo ucfirst($order['status']); ?>
                  </span>
                </td>
                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                <td>
                  <select onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)" style="padding: 5px;">
                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                  </select>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    
    <!-- Products Management -->
    <div class="admin-panel">
      <h2>Products Management</h2>
      <div style="margin-bottom: 20px;">
        <a href="admin_add_product.php" class="btn btn-success">Add New Product</a>
      </div>
      
      <?php if (empty($allProducts)): ?>
        <p>No products found.</p>
      <?php else: ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allProducts as $product): ?>
              <tr>
                <td>
                  <?php if ($product['image']): ?>
                    <img src="/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                  <?php else: ?>
                    <div style="width: 50px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">No Image</div>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td><?php echo $product['stock']; ?></td>
                <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                <td>
                  <a href="admin_edit_product.php?id=<?php echo $product['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Edit</a>
                  <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
function updateOrderStatus(orderId, status) {
  if (confirm('Are you sure you want to update this order status?')) {
    fetch('', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'action=update_order_status&order_id=' + orderId + '&status=' + status
    })
    .then(response => response.text())
    .then(data => {
      alert('Order status updated successfully!');
      location.reload();
    })
    .catch(error => {
      alert('Failed to update order status');
    });
  }
}

function deleteProduct(productId) {
  if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
    fetch('', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'action=delete_product&product_id=' + productId
    })
    .then(response => response.text())
    .then(data => {
      alert('Product deleted successfully!');
      location.reload();
    })
    .catch(error => {
      alert('Failed to delete product');
    });
  }
}
</script>

<?php
// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = getConnection();
    
    if ($_POST['action'] === 'update_order_status') {
        $order_id = intval($_POST['order_id']);
        $status = $_POST['status'];
        
        $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (in_array($status, $allowedStatuses)) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $order_id);
            $stmt->execute();
        }
    } elseif ($_POST['action'] === 'delete_product') {
        $product_id = intval($_POST['product_id']);
        
        // Get product image to delete
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            if ($product['image']) {
                deleteProductImage($product['image']);
            }
        }
        
        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
    }
    
    $conn->close();
    exit();
}
?>

<?php require_once __DIR__ . '/../components/footer.php'; ?>