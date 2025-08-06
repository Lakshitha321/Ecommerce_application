<?php 
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$pageTitle = 'My Profile - Ecommerce Store';

// Get user info
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required';
    } else {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email is already taken by another user';
        } else {
            $updatePassword = false;
            $hashedPassword = '';
            
            // Check password update
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Current password is required to change password';
                } elseif (!password_verify($current_password, $user['password'])) {
                    $error = 'Current password is incorrect';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters long';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match';
                } else {
                    $updatePassword = true;
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }
            
            if (!$error) {
                if ($updatePassword) {
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, address = ?, phone = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $username, $email, $address, $phone, $hashedPassword, $_SESSION['user_id']);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, address = ?, phone = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $username, $email, $address, $phone, $_SESSION['user_id']);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $success = 'Profile updated successfully!';
                    
                    // Refresh user data
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                } else {
                    $error = 'Failed to update profile';
                }
            }
        }
    }
}

$conn->close();
?>

<?php require_once __DIR__ . '/../components/header.php'; ?>

<main>
  <div class="container">
    <h1>My Profile</h1>
    
    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="profile-container">
      <div class="profile-info">
        <div class="profile-section">
          <h3>Account Information</h3>
          <form method="post">
            <div class="info-item">
              <label for="username">Username:</label>
              <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="info-item">
              <label for="email">Email:</label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="info-item">
              <label for="phone">Phone:</label>
              <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            
            <div class="info-item">
              <label for="address">Address:</label>
              <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div class="profile-section">
          <h3>Change Password</h3>
          <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Leave blank if you don't want to change your password</p>
          
          <div class="info-item">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password">
          </div>
          
          <div class="info-item">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" minlength="6">
          </div>
          
          <div class="info-item">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="6">
          </div>
          
          <div class="info-item">
            <label>Account Created:</label>
            <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
          </div>
          
          <?php if ($user['is_admin']): ?>
            <div class="info-item">
              <label>Account Type:</label>
              <span style="color: #007bff; font-weight: 500;">Administrator</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div style="text-align: center; margin-top: 2rem;">
        <button type="submit" class="btn btn-lg">Update Profile</button>
      </div>
      </form>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>