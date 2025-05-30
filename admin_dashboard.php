<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $id = intval($_POST['user_id']);
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $id);
        }
        $stmt->execute();
    }

    if (isset($_POST['delete_user'])) {
        $id = intval($_POST['user_id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='customer'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    if (isset($_POST['save_product'])) {
        $id = $_POST['product_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $image_path = '';

        if (!empty($_FILES['image_file']['name'])) {
            $target_dir = "images/";
            $image_path = $target_dir . basename($_FILES["image_file"]["name"]);
            move_uploaded_file($_FILES["image_file"]["tmp_name"], $image_path);
        }

        if ($id) {
            if ($image_path) {
                $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=?, image_url=? WHERE id=?");
                $stmt->bind_param("sdssi", $name, $price, $category, $image_path, $id);
            } else {
                $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=? WHERE id=?");
                $stmt->bind_param("sdsi", $name, $price, $category, $id);
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, price, category, image_url) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $name, $price, $category, $image_path);
        }
        $stmt->execute();
    }

    if (isset($_POST['delete_product'])) {
        $id = intval($_POST['product_id']);
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

$users = $conn->query("SELECT id, name, email, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$orders = $conn->query("SELECT o.id, o.total_amount, o.created_at, u.name AS customer
  FROM orders o
  JOIN users u ON o.user_id = u.id
  ORDER BY o.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

$order_items_map = [];
$item_results = $conn->query("SELECT order_id, product_name, quantity FROM order_items ORDER BY order_id");
while ($row = $item_results->fetch_assoc()) {
    $order_items_map[$row['order_id']][] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin_style.css">
  <link rel="stylesheet" href="style.css">
</head>
<body class="admin-dashboard">
  <div class="admin-container">
    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
    <div style="position: relative; top: -5px; margin-left: 1px;">
  <a href="index.php" class="button back-dashboard">← BACK TO HOME</a>
</div>


    <div class="tab-navigation">
      <button class="tab-button active" data-target="tab-orders">Orders</button>
      <button class="tab-button" data-target="tab-users">Users</button>
      <button class="tab-button" data-target="tab-products">Products</button>
    </div>

    <div id="tab-orders" class="tab-content active">
      <h3>Recent Orders</h3>
      <table class="admin-table">
        <thead><tr><th>Customer</th><th>Amount</th><th>Products</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td><?php echo htmlspecialchars($order['customer']); ?></td>
            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
            

<td>
  <?php foreach ($order_items_map[$order['id']] ?? [] as $item): ?>
    <div><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo $item['quantity']; ?></div>
  <?php endforeach; ?>
</td>
<td><?php echo $order['created_at']; ?></td>

          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div id="tab-users" class="tab-content">
      <h3>Manage Users</h3>
      <table class="admin-table">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo $user['created_at']; ?></td>
            <td>
              <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="button">Edit</a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <button class="button" name="delete_user">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div id="tab-products" class="tab-content">
      <h3>Manage Products</h3>
      <a href="add_product.php" class="button">Add Product</a>
      <table class="admin-table">
        <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Image</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($products as $product): ?>
          <tr>
            <td><?php echo $product['id']; ?></td>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td>$<?php echo number_format($product['price'], 2); ?></td>
            <td><?php echo $product['category']; ?></td>
            <td><img src="<?php echo $product['image_url']; ?>" class="admin-product-img">
            <td>
              <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="button">Edit</a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button class="button" name="delete_product">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="admin_script.js"></script>
</body>
</html>

<style>
  .admin-product-img {
    width: 40px;
    height: auto;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: block;
    margin: 0 auto;
  }
</style>