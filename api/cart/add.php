<?php
// api/cart/add.php
require_once __DIR__ . '/../../includes/init.php';

header('Content-Type: application/json');

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ hỗ trợ phương thức POST'
    ]);
    exit;
}

try {
    // Lấy dữ liệu từ POST
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Validate
    if ($product_id <= 0) {
        throw new Exception('ID sản phẩm không hợp lệ');
    }
    
    if ($quantity <= 0) {
        throw new Exception('Số lượng phải lớn hơn 0');
    }
    
    $db = Database::getInstance();
    $functions = new Functions();
    
    // 1. Kiểm tra sản phẩm tồn tại và còn hàng
    $product = $db->selectOne(
        "SELECT id, name, price, sale_price, stock_quantity 
         FROM products 
         WHERE id = ? AND status = ?",
        [$product_id, PRODUCT_ACTIVE]
    );
    
    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại hoặc đã ngừng kinh doanh');
    }
    
    if ($product['stock_quantity'] < $quantity) {
        throw new Exception('Số lượng trong kho không đủ. Chỉ còn ' . $product['stock_quantity'] . ' sản phẩm');
    }
    
    // 2. Xác định user_id hoặc session_id
    $user_id = null;
    $session_id = null;
    
    if ($functions->isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
    } else {
        // Tạo session_id cho khách
        if (!isset($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = session_id();
        }
        $session_id = $_SESSION['cart_session_id'];
    }
    
    // 3. Kiểm tra sản phẩm đã có trong giỏ chưa
    if ($user_id) {
        $existingCart = $db->selectOne(
            "SELECT id, quantity FROM cart 
             WHERE user_id = ? AND product_id = ?",
            [$user_id, $product_id]
        );
    } else {
        $existingCart = $db->selectOne(
            "SELECT id, quantity FROM cart 
             WHERE session_id = ? AND product_id = ? AND user_id IS NULL",
            [$session_id, $product_id]
        );
    }
    
    // 4. Thêm hoặc cập nhật giỏ hàng
    if ($existingCart) {
        // Cập nhật số lượng
        $db->update(
            'cart',
            ['quantity' => $existingCart['quantity'] + $quantity],
            'id = ?',
            [$existingCart['id']]
        );
        
        $action = 'updated';
    } else {
        // Thêm mới vào giỏ hàng
        $db->insert('cart', [
            'user_id' => $user_id,
            'session_id' => $session_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $action = 'added';
    }
    
    // 5. Tính tổng số lượng trong giỏ
    $cartCount = $functions->getCartCount();
    
    // 6. Trả về thành công
    echo json_encode([
        'success' => true,
        'message' => 'Đã thêm "' . $product['name'] . '" vào giỏ hàng!',
        'cart_count' => $cartCount,
        'product_name' => $product['name'],
        'action' => $action
    ]);
    
} catch (Exception $e) {
    error_log("Cart add error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>