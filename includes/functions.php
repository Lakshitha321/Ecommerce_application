<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Initialize database and tables
function initDatabase() {
    global $dbname;
    
    $conn = getConnection(false);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully or already exists<br>";
    } else {
        die("Error creating database: " . $conn->error);
    }
    
    $conn->select_db($dbname);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        email VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        address TEXT,
        phone VARCHAR(20),
        is_admin TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Create categories table
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock INT(6) DEFAULT 0,
        category_id INT(6) UNSIGNED,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )";
    $conn->query($sql);
    
    // Create cart table
    $sql = "CREATE TABLE IF NOT EXISTS cart (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) UNSIGNED,
        product_id INT(6) UNSIGNED,
        quantity INT(6) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $conn->query($sql);
    
    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) UNSIGNED,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        shipping_address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $conn->query($sql);
    
    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT(6) UNSIGNED,
        product_id INT(6) UNSIGNED,
        quantity INT(6) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $conn->query($sql);
    
    // Insert sample data
    insertSampleData($conn);
    $conn->close();
}

// Insert sample data
function insertSampleData($conn) {
    // Insert sample categories
    $categories = [
        ['Electronics', 'Electronic devices and gadgets'],
        ['Clothing', 'Fashion and apparel'],
        ['Books', 'Books and magazines'],
        ['Home & Garden', 'Home improvement and garden supplies']
    ];
    
    foreach ($categories as $category) {
        $name = $category[0];
        $description = $category[1];
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insert sample products
    $products = [
        ['Smartphone', 'Latest model smartphone with great features', 599.99, 50, 1, 'smartphone.jpg'],
        ['Laptop', 'High performance laptop for work and gaming', 999.99, 25, 1, 'laptop.jpg'],
        ['T-Shirt', 'Comfortable cotton t-shirt', 19.99, 100, 2, 'tshirt.jpg'],
        ['Jeans', 'Classic blue jeans', 49.99, 75, 2, 'jeans.jpg'],
        ['Programming Book', 'Learn programming from basics', 29.99, 200, 3, 'book.jpg']
    ];
    
    foreach ($products as $product) {
        $name = $product[0];
        $description = $product[1];
        $price = $product[2];
        $stock = $product[3];
        $category_id = $product[4];
        $image = $product[5];
        
        $stmt = $conn->prepare("INSERT IGNORE INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $category_id, $image);
        $stmt->execute();
        $stmt->close();
    }
    
    // Create admin user if not exists
    $adminUsername = "admin";
    $adminEmail = "admin@ecommerce.com";
    $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
    $isAdmin = 1;
    
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $adminUsername, $adminEmail, $adminPassword, $isAdmin);
    $stmt->execute();
    $stmt->close();
}

// Image upload function
function uploadProductImage($file) {
    $uploadDir = __DIR__ . "/../uploads/products/";
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG and GIF allowed.'];
    }
    
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

// Delete product image
function deleteProductImage($filename) {
    $filepath = __DIR__ . "/../uploads/products/" . $filename;
    if ($filename && file_exists($filepath)) {
        unlink($filepath);
    }
}

// User registration
function registerUser($username, $email, $password, $address = '', $phone = '') {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return "Email already exists";
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, address, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $hashedPassword, $address, $phone);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return "success";
    } else {
        $stmt->close();
        $conn->close();
        return "Registration failed";
    }
}

// User login
function loginUser($email, $password) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $email;
            $_SESSION['is_admin'] = $user['is_admin'];
            $stmt->close();
            $conn->close();
            return "success";
        }
    }
    
    $stmt->close();
    $conn->close();
    return "Invalid credentials";
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Product functions
function getAllProducts($category_id = null) {
    $conn = getConnection();
    
    if ($category_id) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? AND p.stock > 0 
                ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
    } else {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.stock > 0 
                ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($sql);
    }
    
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
    return $products;
}

function getAllCategories() {
    $conn = getConnection();
    
    $sql = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    $conn->close();
    return $categories;
}

function getProduct($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $product = null;
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
    
    $stmt->close();
    $conn->close();
    return $product;
}

function addProduct($name, $description, $price, $stock, $category_id, $image = null) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $category_id, $image);
    
    if ($stmt->execute()) {
        $product_id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return ['success' => true, 'product_id' => $product_id];
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Failed to add product'];
    }
}

// Cart functions
function addToCart($user_id, $product_id, $quantity = 1) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $newQuantity = $row['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $newQuantity, $row['id']);
        $success = $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $success = $stmt->execute();
    }
    
    $stmt->close();
    $conn->close();
    return $success;
}

function getCartItems($user_id) {
    $conn = getConnection();
    
    $sql = "SELECT c.*, p.name, p.price, p.image, (c.quantity * p.price) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    return $items;
}

function getCartCount($user_id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $count = $row['count'] ?? 0;
    }
    
    $stmt->close();
    $conn->close();
    return $count;
}

function getCartTotal($user_id) {
    $conn = getConnection();
    
    $sql = "SELECT SUM(c.quantity * p.price) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total = $row['total'] ?? 0;
    }
    
    $stmt->close();
    $conn->close();
    return $total;
}

// Initialize database on first load
initDatabase();
?>