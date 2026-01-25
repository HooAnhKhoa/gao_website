<?php
// api/cart/get.php
require_once __DIR__ . '/../../includes/init.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $functions = new Functions();
    
    $user_id = null;
    $session_id = null;
    
    if ($functions->isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
    } else if (isset($_SESSION['cart_session_id'])) {
        $session_id = $_SESSION['cart_session_id'];
    }
    
    $cart_items = [];
    $total_items = 0;
    $total_price = 0;
    
    if ($user_id) {
        // Get cart items for logged in user
        $cart_items = $db->select(
            "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ? AND p.status = ?",
            [$user_id, PRODUCT_ACTIVE]
        );
    } else if ($session_id) {
        // Get cart items for guest
        $cart_items = $db->select(
            "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.session_id = ? AND c.user_id IS NULL AND p.status = ?",
            [$session_id, PRODUCT_ACTIVE]
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
    
    echo json_encode([
        'success' => true,
        'items' => $cart_items,
        'total_items' => $total_items,
        'total_price' => $total_price
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'items' => [],
        'total_items' => 0,
        'total_price' => 0
    ]);
}
?>