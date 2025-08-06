<?php 
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$success = '';
$pageTitle = 'Sign Up - Ecommerce Store';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        $result = registerUser($username, $email, $password, $address, $phone);
        if ($result === 'success') {
            $success = 'Registration successful! You can now login.';
            $username = $email = $address = $phone = '';
        } else {
            $error = $result;
        }
    }
}
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <div class="form-container">
      <h2>Sign Up</h2>
      
      <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
      <?php endif; ?>
      
      <form method="post">
        <input type="text" name="username" placeholder="Username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
        <input type="password" name="password" placeholder="Password (min 6 characters)" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <textarea name="address" placeholder="Address (optional)" rows="3"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
        <input type="tel" name="phone" placeholder="Phone (optional)" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
        <button type="submit">Sign Up</button>
      </form>
      
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>