<?php
session_start();
require_once 'config.php';

header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug check: is user logged in?
if (!isset($_SESSION['user_id'])) {
    echo "ERROR: Not logged in\n";
    var_dump($_SESSION);
    exit;
}

// Debug check: is data sent?
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo "ERROR: Missing data\n";
    print_r($_POST);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

echo "User: $user_id | Product: $product_id | Qty: $quantity\n";

require_once 'config.php';

if ($quantity <= 0) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    echo "Deleted from cart";
    exit;
}

// Check if item exists
$stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    if ($stmt->execute()) {
        echo "Updated cart ✅";
    } else {
        echo "Update failed ❌";
    }
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    if ($stmt->execute()) {
        echo "Inserted into cart ✅";
    } else {
        echo "Insert failed ❌";
    }
}
