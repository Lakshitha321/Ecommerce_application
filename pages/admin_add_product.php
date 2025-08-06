<?php 
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$pageTitle = 'Add Product - Admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    
    if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
        $error = 'Please fill in all fields with valid values';
    } else {
        $imageName = null;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadProductImage($_FILES['image']);
            if ($uploadResult['success']) {
                $imageName = $uploadResult['filename'];
            } else {
                $error = $uploadResult['message'];
            }
        }
        
        if (!$error) {
            $result = addProduct($name, $description, $price, $stock, $category_id, $imageName);
            if ($result['success']) {
                $success = 'Product added successfully!';
                $name = $description = '';
                $price = $stock = $category_id = 0;
            } else {
                $error = $result['message'];
                if ($imageName) {
                    deleteProductImage($imageName);
                }
            }
        }
    }
}

$categories = getAllCategories();
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <div class="form-container">
      <h2>Add New Product</h2>
      
      <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
      <?php endif; ?>
      
      <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
        
        <textarea name="description" placeholder="Product Description" rows="4" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
        
        <input type="number" name="price" step="0.01" placeholder="Price" value="<?php echo isset($price) ? $price : ''; ?>" required>
        
        <input type="number" name="stock" placeholder="Stock Quantity" value="<?php echo isset($stock) ? $stock : ''; ?>" required>
        
        <select name="category_id" required>
          <option value="">Select Category</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>" <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($category['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        
        <div class="file-input">
          <label for="image">Product Image:</label>
          <input type="file" id="image" name="image" accept="image/*">
          <small>Max size: 5MB. Formats: JPG, PNG, GIF</small>
        </div>
        
        <button type="submit">Add Product</button>
      </form>
      
      <p><a href="admin.php">← Back to Admin Panel</a></p>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>