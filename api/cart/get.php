<?php
// api/cart/get.php
require_once '../../includes/init.php';
header('Content-Type: application/json');

try {
    // Lấy thông tin giỏ hàng từ database
    $cartInfo = $functions->getCartItems();
    
    echo json_encode([
        'success' => true,
        'items' => $cartInfo['items'],
        'total_items' => $cartInfo['total_items'],
        'total_price' => $cartInfo['total']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy thông tin giỏ hàng: ' . $e->getMessage(),
        'total_items' => 0,
        'total_price' => 0
    ]);
}
?>