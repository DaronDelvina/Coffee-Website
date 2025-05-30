<?php
session_start();
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$order = [];
$items = [];

$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Details | Coffee Website</title>
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

  <main class="order-details-main">
    <section class="order-details-section">
      <div class="order-details-container">
        <h2 class="order-details-title">Order #<?php echo $order['id']; ?></h2>
        
        <div class="order-summary">
          <div class="order-meta">
            <div>
              <strong>Status:</strong>
              <span class="order-status <?php echo $order['status']; ?>">
                <?php echo ucfirst($order['status']); ?>
              </span>
            </div>
            <div>
              <strong>Date:</strong>
              <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
            </div>
            <div>
              <strong>Customer:</strong>
              <?php echo htmlspecialchars($order['user_name']); ?>
            </div>
          </div>
          
          <div class="order-items">
            <h3>Items</h3>
            <table>
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                  <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                  <td><?php echo $item['quantity']; ?></td>
                  <td>$<?php echo number_format($item['price'], 2); ?></td>
                  <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="text-right"><strong>Total:</strong></td>
                  <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        
        <a href="orders.php" class="button button">Back to Orders</a>
      </div>
    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>