<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    
    try {
        $db->beginTransaction();
        
        // Get cart items
        $user_id = $_SESSION['user_id'] ?? null;
        $session_id = session_id();
        
        if ($user_id) {
            $cart_items = $db->select(
                "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ? AND p.status = 'active'",
                [$user_id]
            );
        } else {
            $cart_items = $db->select(
                "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.session_id = ? AND c.user_id IS NULL AND p.status = 'active'",
                [$session_id]
            );
        }
        
        if (empty($cart_items)) {
            throw new Exception('Giỏ hàng trống');
        }
        
        // Validate stock
        foreach ($cart_items as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                throw new Exception("Sản phẩm {$item['name']} không đủ số lượng trong kho");
            }
        }
        
        // Calculate totals
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $subtotal += $price * $item['quantity'];
        }
        
        $shipping_fee = $subtotal >= 500000 ? 0 : 30000;
        $total_amount = $subtotal + $shipping_fee;
        
        // Create order
        $order_code = 'DH' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        
        $order_id = $db->insert('orders', [
            'order_code' => $order_code,
            'user_id' => $user_id,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'customer_address' => $data['customer_address'],
            'note' => $data['note'] ?? '',
            'total_amount' => $subtotal,
            'shipping_fee' => $shipping_fee,
            'final_amount' => $total_amount,
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Create order items
        foreach ($cart_items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            
            $db->insert('order_items', [
                'order_id' => $order_id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_price' => $price,
                'quantity' => $item['quantity'],
                'total_price' => $price * $item['quantity']
            ]);
            
            // Update product stock
            $db->update(
                "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
                [$item['quantity'], $item['product_id']]
            );
        }
        
        // Clear cart
        if ($user_id) {
            $db->delete('cart', 'user_id = ?', [$user_id]);
        } else {
            $db->delete('cart', 'session_id = ? AND user_id IS NULL', [$session_id]);
        }
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Đặt hàng thành công';
        $response['order_code'] = $order_code;
        $response['order_id'] = $order_id;
        
    } catch (Exception $e) {
        $db->rollback();
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
?>