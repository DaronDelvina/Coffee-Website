<?php
session_start();
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Get user's orders
$orders = [];
$stmt = $conn->prepare("
    SELECT o.id, o.total_amount, o.status, o.created_at, 
           COUNT(oi.id) as item_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders | Coffee Website</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css" />
</head>
<body class="logged-in">
  <!-- header/navbar -->
  <header>
    <nav class="navbar section-content">
      <a href="index.php" class="nav-logo"><h2 class="logo-text">Coffee</h2></a>
      <ul class="nav-menu">
        <button id="menu-close-button" class="fas fa-times"></button>
        <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
        <li class="nav-item"><a href="index.php#menu" class="nav-link">Menu</a></li>
        <li class="nav-item"><a href="orders.php" class="nav-link">My Orders</a></li>
      </ul>
      <div class="auth-buttons">
        <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="logout.php" class="button">Logout</a>
      </div>
      <button id="menu-open-button" class="fas fa-bars"></button>
    </nav>
  </header>

  <main class="orders-main">
    <section class="orders-section">
      <div class="orders-container">
        <h2 class="orders-title">My Orders</h2>
        
        <?php if (empty($orders)): ?>
          <div class="alert info">You haven't placed any orders yet.</div>
          <a href="index.php" class="button">Browse Menu</a>
        <?php else: ?>
          <div class="orders-list">
            <?php foreach ($orders as $order): ?>
              <div class="order-card">
                <div class="order-header">
                  <h3>Order #<?php echo $order['id']; ?></h3>
                  <span class="order-status <?php echo $order['status']; ?>">
                    <?php echo ucfirst($order['status']); ?>
                  </span>
                </div>
                <div class="order-details">
                  <div class="order-meta">
                    <span>Placed on: <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></span>
                    <span>Items: <?php echo $order['item_count']; ?></span>
                  </div>
                  <div class="order-total">
                    Total: $<?php echo number_format($order['total_amount'], 2); ?>
                  </div>
                </div>
                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="button view-order">View Details</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>