<?php
require_once '../includes/init.php';

// Kiểm tra quyền admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$pageTitle = 'Quản lý đánh giá';
require_once 'includes/header.php';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $reviewId = $_POST['review_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if ($reviewId && in_array($status, ['approved', 'rejected'])) {
        $updated = $db->update('reviews', 
            ['status' => $status], 
            'id = ?', 
            [$reviewId]
        );
        
        if ($updated) {
            Functions::showMessage('success', 'Cập nhật trạng thái đánh giá thành công!');
        } else {
            Functions::showMessage('error', 'Có lỗi xảy ra khi cập nhật!');
        }
    }
}

// Lọc theo trạng thái
$statusFilter = $_GET['status'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$whereClause = '';
$params = [];

if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
    $whereClause = 'WHERE r.status = ?';
    $params[] = $statusFilter;
}

// Đếm tổng số
$totalQuery = "SELECT COUNT(*) as total FROM reviews r $whereClause";
$total = $db->selectOne($totalQuery, $params)['total'] ?? 0;

// Lấy danh sách đánh giá
$reviews = $db->select("
    SELECT r.*, u.full_name, u.email, p.name as product_name, p.image as product_image
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    $whereClause
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
", array_merge($params, [$limit, $offset]));

$pagination = Functions::paginate($total, $page, $limit);
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-star text-warning me-2"></i>Quản lý đánh giá
        </h1>
    </div>

    <?php Functions::displayFlashMessage(); ?>

    <!-- Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="btn-group" role="group">
                        <a href="?status=" class="btn <?php echo $statusFilter === '' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            Tất cả (<?php echo $db->selectOne("SELECT COUNT(*) as total FROM reviews")['total']; ?>)
                        </a>
                        <a href="?status=pending" class="btn <?php echo $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                            Chờ duyệt (<?php echo $db->selectOne("SELECT COUNT(*) as total FROM reviews WHERE status = 'pending'")['total']; ?>)
                        </a>
                        <a href="?status=approved" class="btn <?php echo $statusFilter === 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">
                            Đã duyệt (<?php echo $db->selectOne("SELECT COUNT(*) as total FROM reviews WHERE status = 'approved'")['total']; ?>)
                        </a>
                        <a href="?status=rejected" class="btn <?php echo $statusFilter === 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                            Từ chối (<?php echo $db->selectOne("SELECT COUNT(*) as total FROM reviews WHERE status = 'rejected'")['total']; ?>)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="card shadow">
        <div class="card-body">
            <?php if (empty($reviews)): ?>
            <div class="text-center py-5">
                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Không có đánh giá nào</h5>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Khách hàng</th>
                            <th>Đánh giá</th>
                            <th>Nội dung</th>
                            <th>Ngày đăng</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $review['product_image'] ?? 'no-image.svg'; ?>" 
                                         class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($review['product_name']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($review['full_name'] ?? 'Khách vãng lai'); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($review['email'] ?? ''); ?></small>
                            </td>
                            <td>
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
                                    <span class="badge bg-secondary"><?php echo $review['rating']; ?>/5</span>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 200px;">
                                    <?php if ($review['comment']): ?>
                                    <p class="mb-0 text-truncate"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <?php else: ?>
                                    <em class="text-muted">Không có bình luận</em>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></div>
                                <small class="text-muted"><?php echo date('H:i', strtotime($review['created_at'])); ?></small>
                            </td>
                            <td>
                                <?php
                                $statusInfo = Functions::getStatusLabel($review['status'], 'review');
                                ?>
                                <span class="<?php echo $statusInfo['class']; ?>">
                                    <?php echo $statusInfo['label']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($review['status'] === 'pending'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <input type="hidden" name="action" value="update">
                                        <button type="submit" class="btn btn-success" title="Duyệt">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <input type="hidden" name="action" value="update">
                                        <button type="submit" class="btn btn-danger" title="Từ chối" 
                                                onclick="return confirm('Bạn có chắc muốn từ chối đánh giá này?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <button class="btn btn-info" onclick="viewReview(<?php echo $review['id']; ?>)" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['has_prev']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&status=<?php echo $statusFilter; ?>">Trước</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['has_next']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&status=<?php echo $statusFilter; ?>">Sau</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Review Detail Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewContent">
                <div class="text-center py-3">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewReview(reviewId) {
    const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    const content = document.getElementById('reviewContent');
    
    content.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>';
    modal.show();
    
    fetch(`<?php echo SITE_URL; ?>/api/admin/reviews/detail.php?id=${reviewId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = '<div class="alert alert-danger">Không thể tải thông tin đánh giá</div>';
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra</div>';
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>