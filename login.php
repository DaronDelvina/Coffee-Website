<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                header("Location: index.php");
                exit();
            } else {
                $error = 'Incorrect password';
            }
        } else {
            $error = 'Email not found';
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
  <title>Login | Coffee Website</title>
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
        <a href="login.php" class="button active">Login</a>
        <a href="signup.php" class="button">Sign Up</a>
      </div>
      <button id="menu-open-button" class="fas fa-bars"></button>
    </nav>
  </header>

  <main class="auth-main">
    <section class="auth-section">
      <div class="auth-container">
        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-subtitle">Login to your account</p>

        <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form id="login-form" class="auth-form" method="POST" action="login.php">
          <div class="form-group">
            <label for="login-email">Email</label>
            <input type="email" id="login-email" name="email" required placeholder="Enter your email">
          </div>
          <div class="form-group">
            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="password" required placeholder="Enter your password">
          </div>
          <button type="submit" class="button auth-button">Login</button>
        </form>

        <div class="auth-footer">
          <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
      </div>
    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>
