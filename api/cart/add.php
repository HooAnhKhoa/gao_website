<?php
require_once __DIR__ . '/../../includes/init.php';

header('Content-Type: application/json');

try {
    // Kiểm tra method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Lấy dữ liệu JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    $product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;
    
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    if ($quantity <= 0) {
        throw new Exception('Invalid quantity');
    }
    
    $db = Database::getInstance();
    $functions = new Functions();
    
    // Kiểm tra sản phẩm tồn tại
    $product = $db->selectOne(
        "SELECT id, name, price, sale_price, stock_quantity FROM products 
         WHERE id = ? AND status = ?",
        [$product_id, PRODUCT_ACTIVE]
    );
    
    if (!$product) {
        throw new Exception('Product not found or not available');
    }
    
    // Kiểm tra số lượng tồn kho
    if ($product['stock_quantity'] < $quantity) {
        throw new Exception('Insufficient stock');
    }
    
    $user_id = null;
    $session_id = null;
    
    // Kiểm tra người dùng đã đăng nhập chưa
    if ($functions->isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
    } else {
        // Tạo session_id cho khách
        if (!isset($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = session_id();
        }
        $session_id = $_SESSION['cart_session_id'];
    }
    
    // Kiểm tra sản phẩm đã có trong giỏ chưa
    if ($user_id) {
        $existingCart = $db->selectOne(
            "SELECT id, quantity FROM cart 
             WHERE user_id = ? AND product_id = ?",
            [$user_id, $product_id]
        );
    } else {
        $existingCart = $db->selectOne(
            "SELECT id, quantity FROM cart 
             WHERE session_id = ? AND product_id = ?",
            [$session_id, $product_id]
        );
    }
    
    if ($existingCart) {
        // Cập nhật số lượng
        $db->update(
            'cart',
            ['quantity' => $existingCart['quantity'] + $quantity],
            'id = ?',
            [$existingCart['id']]
        );
    } else {
        // Thêm mới
        $db->insert('cart', [
            'user_id' => $user_id,
            'session_id' => $session_id,
            'product_id' => $product_id,
            'quantity' => $quantity
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => SUCCESS_CART_ADDED,
        'cart_count' => $functions->getCartCount()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}