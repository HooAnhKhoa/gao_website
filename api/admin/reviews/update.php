<?php
require_once '../../../includes/init.php';
header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $reviewId = $input['review_id'] ?? 0;
    $status = $input['status'] ?? '';
    
    if (!$reviewId || !in_array($status, ['approved', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }
    
    $updated = $db->update('reviews', 
        ['status' => $status], 
        'id = ?', 
        [$reviewId]
    );
    
    if ($updated) {
        $message = $status === 'approved' ? 'Đã duyệt đánh giá thành công!' : 'Đã từ chối đánh giá!';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>