<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $name, $email, $hashed_password);
            
            if ($insert->execute()) {
                $success = 'Registration successful! You can now login.';
                $name = $email = $password = $confirm_password = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up | Coffee Website</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <!-- header/navbar -->
  <header>
    <nav class="navbar section-content">
      <a href="index.php" class="nav-logo"><h2 class="logo-text">Coffee</h2></a>
      <ul class="nav-menu">
        <button id="menu-close-button" class="fas fa-times"></button>
        <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
        <li class="nav-item"><a href="index.php#menu" class="nav-link">Menu</a></li>
      </ul>
      <div class="auth-buttons">
        <a href="login.php" class="button">Login</a>
        <a href="signup.php" class="button active">Sign Up</a>
      </div>
      <button id="menu-open-button" class="fas fa-bars"></button>
    </nav>
  </header>

  <main class="auth-main">
    <section class="auth-section">
      <div class="auth-container">
        <h2 class="auth-title">Create Account</h2>
        <p class="auth-subtitle">Join our coffee community</p>
        
        <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form id="signup-form" class="auth-form" method="POST" action="signup.php">
          <div class="form-group">
            <label for="signup-name">Full Name</label>
            <input type="text" id="signup-name" name="name" required placeholder="Enter your name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
          </div>
          <div class="form-group">
            <label for="signup-email">Email</label>
            <input type="email" id="signup-email" name="email" required placeholder="Enter your email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
          </div>
          <div class="form-group">
            <label for="signup-password">Password (min 6 characters)</label>
            <input type="password" id="signup-password" name="password" required placeholder="Enter your password">
          </div>
          <div class="form-group">
            <label for="signup-confirm-password">Confirm Password</label>
            <input type="password" id="signup-confirm-password" name="confirm_password" required placeholder="Confirm your password">
          </div>
          <button type="submit" class="button auth-button">Sign Up</button>
        </form>

        <div class="auth-footer">
          <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
      </div>
    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>