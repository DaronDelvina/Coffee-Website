<?php
session_start();
require_once 'config.php';

$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$user_name = $logged_in ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Coffee Website</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css" />
</head>
<body class="<?php echo $logged_in ? 'logged-in' : ''; ?>">
<header>
  <nav class="navbar section-content">
    <a href="#" class="nav-logo"><h2 class="logo-text">Coffee</h2></a>
    <ul class="nav-menu">
      <button id="menu-close-button" class="fas fa-times"></button>
      <li class="nav-item"><a href="#" class="nav-link">Home</a></li>
      <li class="nav-item"><a href="#menu" class="nav-link">Menu</a></li>
    </ul>
    <div class="auth-buttons">
      <?php if ($logged_in): ?>
        <span class="welcome-text">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <a href="admin_dashboard.php" class="button">Admin Panel</a>
        <?php endif; ?>
        <a href="my_orders.php" class="button">My Orders</a>
        <a href="logout.php" class="button">Logout</a>
      <?php else: ?>
        <a href="login.php" class="button">Login</a>
        <a href="signup.php" class="button">Sign Up</a>
      <?php endif; ?>
    </div>
    <button id="menu-open-button" class="fas fa-bars"></button>
  </nav>
</header>

<main>
  <section class="hero-section">
    <div class="section-content">
      <div class="hero-details">
        <h2 class="title">Best Coffee</h2>
        <h3 class="subtitle">Awaken Your Senses with Every Golden Drop</h3>
        <p class="description">Step into our cozy heaven where the aroma of roasted beans meets the warmth of genuine hospitality. Your perfect cup awaits.</p>
        <div class="buttons">
          <a href="#menu" class="button order-now">Order Now</a>
        </div>
      </div>
      <div class="hero-image-wrapper">
        <img src="images/coffee-hero-section.png" alt="Hero" class="hero-image" />
      </div>
    </div>
  </section>

  <section class="menu-section" id="menu">
    <h2 class="section-title">Our Menu</h2>
    <div class="section-content">
      <ul class="menu-list">
        <li class="menu-item"><a href="#hot-beverages">
          <img src="images/hot-beverages.png" alt="Hot Beverages" class="menu-image" />
          <h3 class="name">Hot Beverages</h3>
          <p class="text">Wide range of Steaming hot coffee to make you fresh and light.</p>
        </a></li>
        <li class="menu-item"><a href="#cold-beverages">
          <img src="images/cold-beverages.png" alt="Cold Beverages" class="menu-image" />
          <h3 class="name">Cold Beverages</h3>
          <p class="text">Creamy and frothy cold coffee to make you cool.</p>
        </a></li>
        <li class="menu-item"><a href="#desserts">
          <img src="images/1747948730871-b35cba8a-ef5c-43f0-a8f7-dd05576c4a32-Photoroom-Photoroom.png" alt="Desserts" class="menu-image" />
          <h3 class="name">Desserts</h3>
          <p class="text">Satiate your plate and take you on a culinary treat.</p>
        </a></li>
      </ul>
    </div>
  </section>

  <?php
  $categories = ['hot-beverages' => 'Hot Beverages', 'cold-beverages' => 'Cold Beverages', 'desserts' => 'Desserts'];
  foreach ($categories as $key => $title):
    $query = $conn->prepare("SELECT * FROM products WHERE category = ? AND status = 'active'");
    $query->bind_param("s", $key);
    $query->execute();
    $products = $query->get_result();
  ?>
  <section id="<?= $key ?>" class="product-category">
    <h2 class="section-title"><?= $title ?></h2>
    <div class="product-list">
      <?php while ($product = $products->fetch_assoc()): ?>
      <div class="product-card" data-id="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>" data-price="<?= $product['price'] ?>">
        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"/>
        <p class="product-name"><?= htmlspecialchars($product['name']) ?></p>
        <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
        <?php if ($logged_in): ?>
          <input type="number" value="1" min="1" class="quantity-input" />
          <button class="add-to-cart">Add to Cart</button>
        <?php else: ?>
          <a href="login.php" class="button login-to-purchase">Log In to Purchase</a>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
    </div>
  </section>
  <?php endforeach; ?>
</main>

<?php if ($logged_in): ?>
  <div id="cart" class="cart">
    <h3>Your Cart</h3>
    <ul id="cart-items"></ul>
    <p id="cart-total">Total: $0.00</p>
    <button id="checkout-button">Checkout</button>
  </div>
<?php endif; ?>

<script src="script.js"></script>
</body>
</html>