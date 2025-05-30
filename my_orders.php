<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

$stmt = $conn->prepare("SELECT o.id, o.total_amount, o.created_at, GROUP_CONCAT(CONCAT(oi.product_name, ' × ', oi.quantity) SEPARATOR '\n') as products
                        FROM orders o
                        JOIN order_items oi ON o.id = oi.order_id
                        WHERE o.user_id = ?
                        GROUP BY o.id
                        ORDER BY o.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="admin-dashboard">
  <main class="admin-container">
    <section class="dashboard-stats">
      <h2 style="text-align:center;">My Orders</h2>
    </section>

    <section class="tab-content active">
      <?php if (empty($orders)): ?>
        <p>You haven't placed any orders yet.</p>
      <?php else: ?>
        <div class="table-container">
          <table class="admin-table" style="border-collapse: collapse; width: 100%;">
            <thead>
              <tr style="border-bottom: 1px solid #e9ecef;">
                <th style="border-right: 1px solid #e9ecef; padding: 1rem;">Products</th>
                <th style="border-right: 1px solid #e9ecef; padding: 1rem;">Total</th>
                <th style="border-right: 1px solid #e9ecef; padding: 1rem;">Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
              <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="border-right: 1px solid #e9ecef; padding: 1rem;">
                  <?php foreach (explode("\n", $order['products']) as $line): ?>
                    <div><?= htmlspecialchars($line) ?></div>
                  <?php endforeach; ?>
                </td>
                <td style="border-right: 1px solid #e9ecef; padding: 1rem;">$<?= number_format($order['total_amount'], 2) ?></td>
                <td style="border-right: 1px solid #e9ecef; padding: 1rem;"><?= $order['created_at'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <div style="text-align: center; margin-top: 2rem;">
        <a href="index.php" class="button">Back to Home</a>
      </div>
    </section>
  </main>
</body>
</html>