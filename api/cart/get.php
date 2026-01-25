<?php
// api/cart/get.php
session_start();
header('Content-Type: application/json');

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_items = 0;
$total_price = 0;

// Tính tổng số lượng
foreach ($cart_items as $item) {
    $total_items += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'items' => $cart_items,
    'total_items' => $total_items,
    'total_price' => $total_price
]);
?>