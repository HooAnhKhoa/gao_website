<?php
// api/checkout/process.php

// Tắt hiển thị lỗi HTML để tránh hỏng JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    require_once '../../includes/init.php';
    $db = Database::getInstance();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không hợp lệ.');
    }

    // 1. Lấy dữ liệu
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if ($contentType === "application/json") {
        $content = file_get_contents("php://input");
        $data = json_decode($content, true);
    } else {
        $data = $_POST;
    }

    // 2. Validate dữ liệu input
    $fullName = $data['full_name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $address = $data['address'] ?? '';
    $paymentMethod = $data['payment_method'] ?? 'cod';
    $note = $data['note'] ?? '';

    if (empty($fullName) || empty($phone) || empty($address)) {
        throw new Exception('Vui lòng điền đầy đủ thông tin nhận hàng.');
    }

    // 3. Lấy giỏ hàng & Tính toán
    $cartData = $functions->getCartItems();
    $cartItems = $cartData['items'];
    $totalAmount = $cartData['total'];

    if (empty($cartItems)) {
        throw new Exception('Giỏ hàng trống.');
    }

    // Phí ship: > 500k free ship, ngược lại 30k
    $shippingFee = ($totalAmount >= 500000) ? 0 : 30000;
    $finalAmount = $totalAmount + $shippingFee;

    // 4. Tạo đơn hàng (Insert bảng orders)
    $userId = Functions::isLoggedIn() ? $_SESSION['user_id'] : null;
    
    $orderData = [
        'order_code' => Functions::generateOrderCode(),
        'user_id' => $userId,
        // Khớp tên cột với gaodb.sql
        'customer_name' => $fullName,
        'customer_phone' => $phone,
        'customer_email' => $email,
        'customer_address' => $address,
        'note' => $note,
        'total_amount' => $totalAmount,
        'shipping_fee' => $shippingFee,
        'discount_amount' => 0,
        'final_amount' => $finalAmount,
        'payment_method' => $paymentMethod,
        'payment_status' => 'pending',
        'order_status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $orderId = $db->insert('orders', $orderData);

    if (!$orderId) {
        throw new Exception('Lỗi lưu đơn hàng (orders).');
    }

    // 5. Lưu chi tiết đơn hàng (Insert bảng order_items)
    foreach ($cartItems as $item) {
        // Xác định giá bán thực tế (ưu tiên giá sale)
        $actualPrice = ($item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
        
        $itemData = [
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            // Khớp tên cột với gaodb.sql
            'product_name' => $item['name'],           // Thêm tên sản phẩm
            'product_price' => $actualPrice,           // Sửa 'price' -> 'product_price'
            'quantity' => $item['quantity'],
            'total_price' => $actualPrice * $item['quantity'] // Thêm tổng tiền item
        ];
        
        $itemInsertId = $db->insert('order_items', $itemData);
        if (!$itemInsertId) {
             // Nếu lỗi insert chi tiết, nên log lại hoặc rollback (ở mức nâng cao)
             // Ở đây ta tạm thời throw exception để báo lỗi
             throw new Exception('Lỗi lưu chi tiết đơn hàng (order_items).');
        }
    }

    // 6. Xóa giỏ hàng sau khi thành công
    if ($userId) {
        $db->delete('cart', "user_id = ?", [$userId]);
    } else {
        $sid = session_id();
        $db->delete('cart', "session_id = ?", [$sid]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Đặt hàng thành công!',
        'redirect' => SITE_URL . '/pages/order-success.php?code=' . $orderData['order_code']
    ]);

} catch (Exception $e) {
    // Log lỗi để debug
    error_log("Checkout Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} catch (Error $e) {
    // Log lỗi để debug
    error_log("Checkout Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>