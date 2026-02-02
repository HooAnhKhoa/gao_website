<?php
// pages/profile.php
require_once '../includes/init.php';

// Kiểm tra đăng nhập
if (!Functions::isLoggedIn()) {
    Functions::showMessage('warning', 'Vui lòng đăng nhập để xem trang cá nhân!');
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$currentUser = Functions::getCurrentUser();
$pageTitle = 'Trang cá nhân';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($fullName) || empty($phone)) {
        Functions::showMessage('error', 'Vui lòng điền đầy đủ họ tên và số điện thoại!');
    } else {
        $updateData = [
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $updated = $db->update('users', $updateData, 'id = ?', [$currentUser['id']]);
        if ($updated) {
            Functions::showMessage('success', 'Cập nhật thông tin thành công!');
            // Refresh user data
            $currentUser = Functions::getCurrentUser();
        } else {
            Functions::showMessage('error', 'Có lỗi xảy ra khi cập nhật!');
        }
    }
}

// Lấy danh sách đơn hàng của user
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalOrders = $db->selectOne(
    "SELECT COUNT(*) as total FROM orders WHERE user_id = ?",
    [$currentUser['id']]
)['total'] ?? 0;

$orders = $db->select(
    "SELECT * FROM orders 
     WHERE user_id = ? 
     ORDER BY created_at DESC 
     LIMIT ? OFFSET ?",
    [$currentUser['id'], $limit, $offset]
);

$pagination = Functions::paginate($totalOrders, $page, $limit);

require_once '../includes/header.php';
?>

<div class="container py-5 profile-page">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="avatar mb-3">
                        <i class="fas fa-user-circle text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($currentUser['full_name'] ?: $currentUser['username']); ?></h5>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                    <span class="badge bg-success">Thành viên</span>
                </div>
            </div>
            
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#profile-info" class="list-group-item list-group-item-action active" data-tab="profile-info">
                            <i class="fas fa-user me-2"></i>Thông tin cá nhân
                        </a>
                        <a href="#order-history" class="list-group-item list-group-item-action" data-tab="order-history">
                            <i class="fas fa-shopping-bag me-2"></i>Lịch sử đơn hàng
                            <span class="badge bg-primary ms-auto"><?php echo $totalOrders; ?></span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/api/auth/logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Profile Info Tab -->
            <div id="profile-info" class="tab-content active">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin cá nhân</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php Functions::displayFlashMessage(); ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tên đăng nhập</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($currentUser['username']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" 
                                           value="<?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Địa chỉ</label>
                                <textarea class="form-control" name="address" rows="3" 
                                          placeholder="Nhập địa chỉ của bạn..."><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày tham gia</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo date('d/m/Y', strtotime($currentUser['created_at'])); ?>" readonly>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Cập nhật thông tin
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order History Tab -->
            <div id="order-history" class="tab-content">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Lịch sử đơn hàng</h5>
                        <span class="badge bg-primary"><?php echo $totalOrders; ?> đơn hàng</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">Chưa có đơn hàng nào</h5>
                            <p class="text-muted">Hãy bắt đầu mua sắm để tạo đơn hàng đầu tiên!</p>
                            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-2"></i>Mua sắm ngay
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Mã đơn hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thanh toán</th>
                                        <th class="pe-4">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <strong class="text-primary"><?php echo $order['order_code']; ?></strong>
                                        </td>
                                        <td>
                                            <div><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <strong class="text-danger"><?php echo Functions::formatPrice($order['final_amount']); ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            $statusInfo = Functions::getStatusLabel($order['order_status'], 'order');
                                            echo '<span class="' . $statusInfo['class'] . '">' . $statusInfo['label'] . '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $paymentInfo = Functions::getStatusLabel($order['payment_status'], 'payment');
                                            echo '<span class="' . $paymentInfo['class'] . '">' . $paymentInfo['label'] . '</span>';
                                            ?>
                                        </td>
                                        <td class="pe-4">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetail('<?php echo $order['order_code']; ?>')">
                                                <i class="fas fa-eye"></i> Xem
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                        <div class="card-footer bg-white">
                            <nav aria-label="Order pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($pagination['has_prev']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>#order-history">Trước</a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>#order-history"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>#order-history">Sau</a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}

.list-group-item-action.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

.avatar {
    position: relative;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.75em;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}
</style>

<script>
// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Check URL hash on load
    const hash = window.location.hash.substring(1);
    if (hash) {
        switchTab(hash);
    }
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
            
            // Update URL hash
            window.history.pushState(null, null, '#' + tabId);
        });
    });
    
    function switchTab(tabId) {
        // Hide all tabs
        tabContents.forEach(content => {
            content.classList.remove('active');
        });
        
        // Remove active class from all links
        tabLinks.forEach(link => {
            link.classList.remove('active');
        });
        
        // Show selected tab
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }
        
        // Add active class to selected link
        const selectedLink = document.querySelector(`[data-tab="${tabId}"]`);
        if (selectedLink) {
            selectedLink.classList.add('active');
        }
    }
});

// View order detail
function viewOrderDetail(orderCode) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    const content = document.getElementById('orderDetailContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch order detail
    fetch('../api/orders/detail.php?code=' + orderCode)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message || 'Không thể tải thông tin đơn hàng'}
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Có lỗi xảy ra khi tải thông tin đơn hàng
                </div>
            `;
        });
}
</script>

<?php require_once '../includes/footer.php'; ?>