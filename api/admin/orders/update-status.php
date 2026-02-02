<?php
// api/admin/orders/update-status.php
header('Content-Type: application/json');
require_once '../../../includes/init.php';

// Check admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Lấy dữ liệu JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = $input['order_id'] ?? 0;
    $newStatus = $input['status'] ?? '';
    
    if (!$orderId || !$newStatus) {
        throw new Exception('Thiếu thông tin đơn hàng hoặc trạng thái');
    }
    
    // Validate trạng thái
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($newStatus, $validStatuses)) {
        throw new Exception('Trạng thái không hợp lệ');
    }
    
    // Kiểm tra đơn hàng tồn tại
    $order = $db->selectOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại');
    }
    
    // Kiểm tra logic chuyển trạng thái
    $currentStatus = $order['order_status'];
    $statusFlow = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [], // Không thể chuyển từ delivered
        'cancelled' => [] // Không thể chuyển từ cancelled
    ];
    
    if (!in_array($newStatus, $statusFlow[$currentStatus])) {
        throw new Exception('Không thể chuyển từ trạng thái "' . $currentStatus . '" sang "' . $newStatus . '"');
    }
    
    // Cập nhật trạng thái
    $updated = $db->update('orders', [
        'order_status' => $newStatus,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$orderId]);
    
    if (!$updated) {
        throw new Exception('Không thể cập nhật trạng thái đơn hàng');
    }
    
    // Tự động cập nhật trạng thái thanh toán cho COD khi giao hàng thành công
    if ($newStatus === 'delivered' && $order['payment_method'] === 'cod') {
        $db->update('orders', [
            'payment_status' => 'paid'
        ], 'id = ?', [$orderId]);
    }
    
    // Thông báo thành công
    $statusLabels = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý', 
        'shipped' => 'Đang giao hàng',
        'delivered' => 'Đã giao hàng',
        'cancelled' => 'Đã hủy'
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật trạng thái đơn hàng thành "' . $statusLabels[$newStatus] . '"',
        'new_status' => $newStatus,
        'order_code' => $order['order_code']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>