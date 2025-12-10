<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
session_start();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

$cart_items = [];
$total_items = 0;
$total_price = 0;

if ($user_id) {
    // Get cart items for logged in user
    $cart_items = $db->select(
        "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
         FROM cart c 
         JOIN products p ON c.product_id = p.id 
         WHERE c.user_id = ? AND p.status = 'active'",
        [$user_id]
    );
} else {
    // Get cart items for guest
    $cart_items = $db->select(
        "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
         FROM cart c 
         JOIN products p ON c.product_id = p.id 
         WHERE c.session_id = ? AND c.user_id IS NULL AND p.status = 'active'",
        [$session_id]
    );
}

// Calculate totals
foreach ($cart_items as &$item) {
    $price = $item['sale_price'] ?: $item['price'];
    $item['total_price'] = $price * $item['quantity'];
    $item['current_price'] = $price;
    
    $total_items += $item['quantity'];
    $total_price += $item['total_price'];
}

// Check for stock warnings
foreach ($cart_items as &$item) {
    if ($item['quantity'] > $item['stock_quantity']) {
        $item['stock_warning'] = true;
    }
}

echo json_encode([
    'success' => true,
    'items' => $cart_items,
    'total_items' => $total_items,
    'total_price' => $total_price
]);
?>