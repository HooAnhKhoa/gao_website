<?php
require_once '../../includes/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

try {
    // Kiểm tra đăng nhập
    if (!Functions::isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá']);
        exit;
    }
    
    $productId = $_POST['product_id'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    $comment = trim($_POST['comment'] ?? '');
    
    // Validate
    if (!$productId || !$rating || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }
    
    // Kiểm tra sản phẩm tồn tại
    $product = $db->selectOne("SELECT id FROM products WHERE id = ? AND status = 'active'", [$productId]);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }
    
    // Kiểm tra đã đánh giá chưa
    $existingReview = $db->selectOne(
        "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?",
        [$productId, $_SESSION['user_id']]
    );
    
    if ($existingReview) {
        echo json_encode(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này rồi']);
        exit;
    }
    
    // Tạo đánh giá mới
    $reviewData = [
        'product_id' => $productId,
        'user_id' => $_SESSION['user_id'],
        'rating' => $rating,
        'comment' => $comment,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $reviewId = $db->insert('reviews', $reviewData);
    
    if ($reviewId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Cảm ơn bạn đã đánh giá! Đánh giá sẽ được hiển thị sau khi được duyệt.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu đánh giá']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>