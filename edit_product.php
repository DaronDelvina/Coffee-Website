<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    echo "Product ID is missing.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $image_path = $product['image_url']; // default to existing

    if (!empty($_FILES['image_file']['name'])) {
        $target_dir = "images/";
        $image_path = $target_dir . basename($_FILES["image_file"]["name"]);
        move_uploaded_file($_FILES["image_file"]["tmp_name"], $image_path);
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=?, image_url=? WHERE id=?");
    $stmt->bind_param("sdssi", $name, $price, $category, $image_path, $id);
    $stmt->execute();

    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="admin_style.css">
  <style>
    .edit-container {
      max-width: 600px;
      margin: 50px auto;
      background-color: var(--light-bg);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .edit-container h2 {
      text-align: center;
      color: var(--text-dark);
      margin-bottom: 20px;
    }

    .edit-container label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: var(--text-light);
    }

    .edit-container input,
    .edit-container select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .edit-container button {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      background-color: var(--secondary);
      color: var(--white);
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 1rem;
    }

    .edit-container button:hover {
      background-color: var(--gold);
      color: var(--text-dark);
    }

    .edit-container img {
      display: block;
      margin: 10px auto;
      max-width: 200px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .button {
        flex: 1;
    padding: 1rem;
    font-weight: 600;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: background 0.5s;;
    background: #ffd95a;
    color: #3e2723;
    }

    .button:hover {
        background: #6d3410;    
    }

    .button.back-dashboard {
  border-radius: 25px;
  padding: 12px 25px;
  font-weight: bold;
  display: inline-block;
}

  </style>
</head>
<body class="admin-dashboard">
<div style="margin-top: 30px; margin-left: 20px;">
  <a href="admin_dashboard.php" class="button back-dashboard">← BACK TO DASHBOARD</a>
</div>


  <div class="edit-container">
    <h2>Edit Product</h2>
    <form method="POST" enctype="multipart/form-data">
      <label>Name:</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

      <label>Price:</label>
      <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>

      <label>Category:</label>
      <select name="category" required>
        <option value="hot-beverages" <?php if ($product['category'] === 'hot-beverages') echo 'selected'; ?>>Hot Beverages</option>
        <option value="cold-beverages" <?php if ($product['category'] === 'cold-beverages') echo 'selected'; ?>>Cold Beverages</option>
        <option value="desserts" <?php if ($product['category'] === 'desserts') echo 'selected'; ?>>Desserts</option>
      </select>

      <label>Current Image:</label>
      <img src="<?php echo $product['image_url']; ?>" alt="Product Image">

      <label>Upload New Image:</label>
      <input type="file" name="image_file">

      <button type="submit">Save Changes</button>
    </form>
  </div>
</body>
</html>
