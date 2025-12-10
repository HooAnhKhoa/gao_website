<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $data['product_id'] ?? 0;
    $quantity = $data['quantity'] ?? 1;
    
    if ($product_id <= 0) {
        $response['message'] = 'Sản phẩm không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    $db = Database::getInstance();
    
    // Check if product exists and is active
    $product = $db->selectOne(
        "SELECT * FROM products WHERE id = ? AND status = 'active'",
        [$product_id]
    );
    
    if (!$product) {
        $response['message'] = 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh';
        echo json_encode($response);
        exit;
    }
    
    // Check stock
    if ($product['stock_quantity'] < $quantity) {
        $response['message'] = 'Số lượng sản phẩm không đủ trong kho';
        echo json_encode($response);
        exit;
    }
    
    $user_id = $_SESSION['user_id'] ?? null;
    $session_id = session_id();
    
    try {
        if ($user_id) {
            // User is logged in
            $existing = $db->selectOne(
                "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
                [$user_id, $product_id]
            );
            
            if ($existing) {
                $db->update(
                    "UPDATE cart SET quantity = quantity + ? WHERE id = ?",
                    [$quantity, $existing['id']]
                );
            } else {
                $db->insert('cart', [
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        } else {
            // Guest user
            $existing = $db->selectOne(
                "SELECT * FROM cart WHERE session_id = ? AND product_id = ? AND user_id IS NULL",
                [$session_id, $product_id]
            );
            
            if ($existing) {
                $db->update(
                    "UPDATE cart SET quantity = quantity + ? WHERE id = ?",
                    [$quantity, $existing['id']]
                );
            } else {
                $db->insert('cart', [
                    'session_id' => $session_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Get updated cart count
        if ($user_id) {
            $cartCount = $db->selectOne(
                "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?",
                [$user_id]
            )['total'] ?? 0;
        } else {
            $cartCount = $db->selectOne(
                "SELECT SUM(quantity) as total FROM cart WHERE session_id = ? AND user_id IS NULL",
                [$session_id]
            )['total'] ?? 0;
        }
        
        $response['success'] = true;
        $response['message'] = 'Đã thêm vào giỏ hàng';
        $response['cart_count'] = $cartCount;
        
    } catch (Exception $e) {
        $response['message'] = 'Lỗi hệ thống: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>