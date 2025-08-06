<?php 
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Products - Ecommerce Store';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get products with optional filtering
if ($search) {
    $conn = getConnection();
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE (p.name LIKE ? OR p.description LIKE ?) AND p.stock > 0 
            ORDER BY p.created_at DESC";
    $searchTerm = "%$search%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
    $conn->close();
} else {
    $products = getAllProducts($category_id);
}

$categories = getAllCategories();
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <h1>Products</h1>
    
    <!-- Search and Filter Section -->
    <div class="form-container" style="max-width: 800px;">
      <form method="get" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; margin: 5px 0;">
        
        <select name="category" style="flex: none; min-width: 150px; margin: 5px 0;">
          <option value="">All Categories</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($category['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        
        <button type="submit" style="margin: 5px 0;">Search</button>
        
        <?php if ($search || $category_id): ?>
          <a href="products.php" class="btn" style="margin: 5px 0;">Clear</a>
        <?php endif; ?>
      </form>
    </div>
    
    <!-- Results Info -->
    <?php if ($search): ?>
      <div class="alert alert-info">
        Showing results for: "<strong><?php echo htmlspecialchars($search); ?></strong>"
        (<?php echo count($products); ?> products found)
      </div>
    <?php elseif ($category_id): ?>
      <?php
      $selectedCategory = null;
      foreach ($categories as $cat) {
          if ($cat['id'] == $category_id) {
              $selectedCategory = $cat;
              break;
          }
      }
      ?>
      <?php if ($selectedCategory): ?>
        <div class="alert alert-info">
          Category: <strong><?php echo htmlspecialchars($selectedCategory['name']); ?></strong>
          (<?php echo count($products); ?> products)
        </div>
      <?php endif; ?>
    <?php endif; ?>
    
    <!-- Products Grid -->
    <section class="featured-products">
      <div class="products-grid">
        <?php if (empty($products)): ?>
          <div class="empty-cart">
            <h3>No products found</h3>
            <?php if ($search || $category_id): ?>
              <p>Try adjusting your search criteria or <a href="products.php">view all products</a></p>
            <?php else: ?>
              <p>No products are currently available.</p>
            <?php endif; ?>
          </div>
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
              <p style="padding: 0 15px; color: #666; font-size: 0.9rem;">
                <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . (strlen($product['description']) > 80 ? '...' : ''); ?>
              </p>
              <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>