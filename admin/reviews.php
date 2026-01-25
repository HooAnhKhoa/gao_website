<?php
// admin/reviews.php
require_once '../includes/init.php';

// Kiểm tra quyền Admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$db = Database::getInstance();

// --- XỬ LÝ HÀNH ĐỘNG (Duyệt / Từ chối / Xóa) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        $db->update('reviews', ['status' => 'approved'], "id = ?", [$id]);
        Functions::showMessage('success', 'Đã duyệt đánh giá thành công!');
    } elseif ($action == 'reject') {
        $db->update('reviews', ['status' => 'rejected'], "id = ?", [$id]);
        Functions::showMessage('warning', 'Đã từ chối đánh giá!');
    } elseif ($action == 'delete') {
        $db->delete('reviews', "id = ?", [$id]);
        Functions::showMessage('success', 'Đã xóa đánh giá!');
    }
    
    // Quay lại trang hiện tại (giữ nguyên bộ lọc)
    $redirectUrl = 'reviews.php';
    if (isset($_GET['status'])) $redirectUrl .= '?status=' . $_GET['status'];
    header("Location: $redirectUrl");
    exit;
}

$pageTitle = 'Quản lý đánh giá';
require_once 'includes/header.php';

// --- LỌC VÀ PHÂN TRANG ---
$status_filter = $_GET['status'] ?? '';
$where = "1=1";
$params = [];

if ($status_filter) {
    $where .= " AND r.status = ?";
    $params[] = $status_filter;
}

// Phân trang
$page = $_GET['page'] ?? 1;
$limit = 10;

// Đếm tổng số bản ghi
$total_reviews = $db->selectOne("SELECT COUNT(*) as total FROM reviews r WHERE $where", $params)['total'];
$pagination = Functions::paginate($total_reviews, $page, $limit);

// Lấy danh sách đánh giá (Join với User và Product)
$reviews = $db->select("
    SELECT r.*, u.full_name, u.avatar, p.name as product_name, p.image as product_image
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN products p ON r.product_id = p.id 
    WHERE $where 
    ORDER BY r.created_at DESC 
    LIMIT $limit OFFSET {$pagination['offset']}", 
    $params
);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Đánh giá sản phẩm</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success">Trạng thái</h6>
    </div>
    <div class="card-body">
        <div class="btn-group" role="group">
            <a href="reviews.php" class="btn btn-outline-secondary <?php echo $status_filter == '' ? 'active' : ''; ?>">
                Tất cả
            </a>
            <a href="reviews.php?status=pending" class="btn btn-outline-warning <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
                Chờ duyệt
            </a>
            <a href="reviews.php?status=approved" class="btn btn-outline-success <?php echo $status_filter == 'approved' ? 'active' : ''; ?>">
                Đã duyệt
            </a>
            <a href="reviews.php?status=rejected" class="btn btn-outline-danger <?php echo $status_filter == 'rejected' ? 'active' : ''; ?>">
                Đã từ chối
            </a>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="table-success">
                    <tr>
                        <th width="50">ID</th>
                        <th width="200">Sản phẩm</th>
                        <th width="180">Người đánh giá</th>
                        <th width="120">Điểm số</th>
                        <th>Nội dung</th>
                        <th width="120">Ngày đăng</th>
                        <th width="100">Trạng thái</th>
                        <th width="120">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr><td colspan="8" class="text-center py-4">Không tìm thấy đánh giá nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?php echo $review['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $review['product_image'] ?? 'default.jpg'; ?>" 
                                         class="rounded me-2" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                    <small class="fw-bold text-truncate" style="max-width: 150px;">
                                        <?php echo htmlspecialchars($review['product_name']); ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                         style="width: 30px; height: 30px; font-size: 12px;">
                                        <?php echo strtoupper(substr($review['full_name'], 0, 1)); ?>
                                    </div>
                                    <span class="small"><?php echo htmlspecialchars($review['full_name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="text-warning">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <div style="max-height: 80px; overflow-y: auto;">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </div>
                            </td>
                            <td class="small"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></td>
                            <td>
                                <?php if ($review['status'] == 'approved'): ?>
                                    <span class="badge bg-success">Đã hiện</span>
                                <?php elseif ($review['status'] == 'rejected'): ?>
                                    <span class="badge bg-danger">Từ chối</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group-vertical w-100">
                                    <?php if ($review['status'] == 'pending' || $review['status'] == 'rejected'): ?>
                                        <a href="reviews.php?action=approve&id=<?php echo $review['id']; ?>&status=<?php echo $status_filter; ?>" 
                                           class="btn btn-sm btn-success mb-1" title="Duyệt">
                                            <i class="fas fa-check"></i> Duyệt
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($review['status'] == 'pending' || $review['status'] == 'approved'): ?>
                                        <a href="reviews.php?action=reject&id=<?php echo $review['id']; ?>&status=<?php echo $status_filter; ?>" 
                                           class="btn btn-sm btn-warning mb-1" title="Từ chối">
                                            <i class="fas fa-times"></i> Ẩn
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="reviews.php?action=delete&id=<?php echo $review['id']; ?>&status=<?php echo $status_filter; ?>" 
                                       class="btn btn-sm btn-danger confirm-delete" 
                                       data-confirm="Bạn có chắc chắn muốn xóa đánh giá này không?">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo !$pagination['has_prev'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&status=<?php echo $status_filter; ?>">Trước</a>
                </li>
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo !$pagination['has_next'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&status=<?php echo $status_filter; ?>">Sau</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>