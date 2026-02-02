<?php
require_once '../../../includes/init.php';
header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$reviewId = $_GET['id'] ?? 0;

if (!$reviewId) {
    echo json_encode(['success' => false, 'message' => 'ID đánh giá không hợp lệ']);
    exit;
}

try {
    $review = $db->selectOne("
        SELECT r.*, u.full_name, u.email, u.phone, p.name as product_name, p.image as product_image, p.price
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN products p ON r.product_id = p.id
        WHERE r.id = ?
    ", [$reviewId]);
    
    if (!$review) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đánh giá']);
        exit;
    }
    
    ob_start();
    ?>
    <div class="row">
        <div class="col-md-4">
            <div class="text-center">
                <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $review['product_image'] ?? 'no-image.svg'; ?>" 
                     class="img-fluid rounded mb-3" style="max-height: 200px;">
                <h6 class="fw-bold"><?php echo htmlspecialchars($review['product_name']); ?></h6>
                <p class="text-success fw-bold"><?php echo Functions::formatPrice($review['price']); ?></p>
            </div>
        </div>
        <div class="col-md-8">
            <h6 class="fw-bold mb-3">Thông tin đánh giá</h6>
            
            <div class="mb-3">
                <label class="fw-bold">Khách hàng:</label>
                <div><?php echo htmlspecialchars($review['full_name'] ?? 'Khách vãng lai'); ?></div>
                <?php if ($review['email']): ?>
                <small class="text-muted"><?php echo htmlspecialchars($review['email']); ?></small>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Đánh giá:</label>
                <div class="d-flex align-items-center">
                    <div class="stars me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= $review['rating']): ?>
                            <i class="fas fa-star text-warning"></i>
                        <?php else: ?>
                            <i class="far fa-star text-warning"></i>
                        <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="badge bg-primary"><?php echo $review['rating']; ?>/5 sao</span>
                </div>
            </div>
            
            <?php if ($review['comment']): ?>
            <div class="mb-3">
                <label class="fw-bold">Bình luận:</label>
                <div class="border rounded p-3 bg-light">
                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="fw-bold">Trạng thái:</label>
                <div>
                    <?php
                    $statusInfo = Functions::getStatusLabel($review['status'], 'review');
                    ?>
                    <span class="<?php echo $statusInfo['class']; ?>">
                        <?php echo $statusInfo['label']; ?>
                    </span>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Ngày đăng:</label>
                <div><?php echo date('d/m/Y H:i:s', strtotime($review['created_at'])); ?></div>
            </div>
            
            <?php if ($review['status'] === 'pending'): ?>
            <div class="mt-4">
                <button class="btn btn-success me-2" onclick="updateReviewStatus(<?php echo $review['id']; ?>, 'approved')">
                    <i class="fas fa-check me-1"></i>Duyệt đánh giá
                </button>
                <button class="btn btn-danger" onclick="updateReviewStatus(<?php echo $review['id']; ?>, 'rejected')">
                    <i class="fas fa-times me-1"></i>Từ chối
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function updateReviewStatus(reviewId, status) {
        if (!confirm('Bạn có chắc muốn ' + (status === 'approved' ? 'duyệt' : 'từ chối') + ' đánh giá này?')) {
            return;
        }
        
        fetch('<?php echo SITE_URL; ?>/api/admin/reviews/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                review_id: reviewId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
                location.reload();
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
            }
        })
        .catch(error => {
            alert('Có lỗi xảy ra khi cập nhật');
        });
    }
    </script>
    <?php
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>