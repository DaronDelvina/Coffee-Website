<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    echo "User ID is missing.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $email, $id);
    }

    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="admin_style.css">
  <style>
    .edit-container {
      max-width: 500px;
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

    .edit-container input {
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
    <h2>Edit User</h2>
    <form method="POST">
      <label>Name:</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

      <label>Email:</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

      <label>New Password (optional):</label>
      <input type="password" name="password" placeholder="Leave blank to keep current">

      <button type="submit">Save Changes</button>
    </form>
  </div>
</body>
</html>
