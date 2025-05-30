<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo "error|Please log in to place an order";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cart_data'])) {
    echo "error|Invalid request";
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_data = $_POST['cart_data'];
$total_amount = 0;
$items = [];
$cart_items = explode('|', $cart_data);
$order_summary = [];

foreach ($cart_items as $item_data) {
    $parts = explode(',', $item_data);
    if (count($parts) !== 4) continue;
    $item = [
        'id' => intval($parts[0]),
        'name' => $parts[1],
        'price' => floatval($parts[2]),
        'quantity' => intval($parts[3])
    ];
    $items[] = $item;
    $total_amount += $item['price'] * $item['quantity'];
    $order_summary[] = $item['name'] . ':' . $item['quantity'];
}

// Save current cart state
try {
    $conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

    $stmt_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    foreach ($items as $item) {
        $stmt_cart->bind_param("iii", $user_id, $item['id'], $item['quantity']);
        $stmt_cart->execute();
    }
} catch (Exception $e) {
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total_amount);
    $stmt->execute();
    $order_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt->bind_param("iisid", $order_id, $item['id'], $item['name'], $item['quantity'], $item['price']);
        $stmt->execute();
    }

    $conn->commit();

    // Set simplified cookie format
    setcookie("past_orders", implode(';', $order_summary), time() + 86400, "/");

    echo "success|Order placed successfully!|" . $order_id;
} catch (Exception $e) {
    $conn->rollback();
    echo "error|Failed to place order: " . $e->getMessage();
}
$conn->close();
