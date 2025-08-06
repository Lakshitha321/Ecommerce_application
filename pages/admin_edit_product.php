<?php 
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$pageTitle = 'Edit Product - Admin';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    header('Location: admin.php');
    exit();
}

$product = getProduct($product_id);
if (!$product) {
    header('Location: admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    
    if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
        $error = 'Please fill in all fields with valid values';
    } else {
        $imageName = $product['image']; // Keep existing image by default
        
        // Handle image upload if new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadProductImage($_FILES['image']);
            if ($uploadResult['success']) {
                // Delete old image if it exists
                if ($product['image']) {
                    deleteProductImage($product['image']);
                }
                $imageName = $uploadResult['filename'];
            } else {
                $error = $uploadResult['message'];
            }
        }
        
        if (!$error) {
            $conn = getConnection();
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssdissi", $name, $description, $price, $stock, $category_id, $imageName, $product_id);
            
            if ($stmt->execute()) {
                $success = 'Product updated successfully!';
                // Refresh product data
                $product = getProduct($product_id);
            } else {
                $error = 'Failed to update product';
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}

$categories = getAllCategories();
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <div class="form-container">
      <h2>Edit Product</h2>
      
      <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
      <?php endif; ?>
      
      <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        
        <textarea name="description" placeholder="Product Description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        
        <input type="number" name="price" step="0.01" placeholder="Price" value="<?php echo $product['price']; ?>" required>
        
        <input type="number" name="stock" placeholder="Stock Quantity" value="<?php echo $product['stock']; ?>" required>
        
        <select name="category_id" required>
          <option value="">Select Category</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($category['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        
        <div class="file-input">
          <label for="image">Product Image:</label>
          <?php if ($product['image']): ?>
            <div style="margin: 10px 0;">
              <p>Current image:</p>
              <img src="/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="Current product image" style="max-width: 200px; height: auto; border: 1px solid #ddd; border-radius: 4px;">
            </div>
          <?php endif; ?>
          <input type="file" id="image" name="image" accept="image/*">
          <small>Max size: 5MB. Formats: JPG, PNG, GIF. Leave empty to keep current image.</small>
        </div>
        
        <button type="submit">Update Product</button>
      </form>
      
      <p><a href="admin.php">← Back to Admin Panel</a></p>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>