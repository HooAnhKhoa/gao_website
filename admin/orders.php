<?php
// admin/orders.php
require_once '../includes/init.php';

// Check admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$pageTitle = 'Quản lý đơn hàng';
require_once 'includes/header.php';

$db = Database::getInstance();
$functions = new Functions();

// Xử lý bộ lọc
$status_filter = $_GET['status'] ?? '';
$where = "1=1";
$params = [];

if ($status_filter) {
    $where .= " AND order_status = ?";
    $params[] = $status_filter;
}

// Phân trang
$page = $_GET['page'] ?? 1;
$limit = 10;
$total_orders = $db->selectOne("SELECT COUNT(*) as total FROM orders WHERE $where", $params)['total'];
$pagination = Functions::paginate($total_orders, $page, $limit);

// Lấy danh sách đơn hàng
// SỬA: Lấy trực tiếp từ bảng orders, không cần JOIN users vì thông tin đã lưu cứng trong orders
$orders = $db->select("
    SELECT * FROM orders 
    WHERE $where 
    ORDER BY created_at DESC 
    LIMIT $limit OFFSET {$pagination['offset']}", 
    $params
);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Danh sách đơn hàng</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-success">Bộ lọc & Tìm kiếm</h6>
        <div class="btn-group">
            <button class="btn btn-sm btn-success" onclick="bulkApprove()" title="Duyệt tất cả đơn chờ xử lý">
                <i class="fas fa-check-double me-1"></i>Duyệt hàng loạt
            </button>
            <button class="btn btn-sm btn-info" onclick="refreshOrders()" title="Làm mới danh sách">
                <i class="fas fa-sync-alt me-1"></i>Làm mới
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="btn-group" role="group">
            <a href="orders.php" class="btn btn-outline-secondary <?php echo $status_filter == '' ? 'active' : ''; ?>">
                Tất cả 
                <span class="badge bg-secondary ms-1"><?php echo $db->selectOne("SELECT COUNT(*) as total FROM orders")['total']; ?></span>
            </a>
            <a href="orders.php?status=pending" class="btn btn-outline-warning <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
                Chờ xử lý
                <span class="badge bg-warning ms-1"><?php echo $db->selectOne("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'")['total']; ?></span>
            </a>
            <a href="orders.php?status=processing" class="btn btn-outline-info <?php echo $status_filter == 'processing' ? 'active' : ''; ?>">
                Đang xử lý
                <span class="badge bg-info ms-1"><?php echo $db->selectOne("SELECT COUNT(*) as total FROM orders WHERE order_status = 'processing'")['total']; ?></span>
            </a>
            <a href="orders.php?status=shipped" class="btn btn-outline-primary <?php echo $status_filter == 'shipped' ? 'active' : ''; ?>">
                Đang giao
                <span class="badge bg-primary ms-1"><?php echo $db->selectOne("SELECT COUNT(*) as total FROM orders WHERE order_status = 'shipped'")['total']; ?></span>
            </a>
            <a href="orders.php?status=delivered" class="btn btn-outline-success <?php echo $status_filter == 'delivered' ? 'active' : ''; ?>">
                Hoàn thành
                <span class="badge bg-success ms-1"><?php echo $db->selectOne("SELECT COUNT(*) as total FROM orders WHERE order_status = 'delivered'")['total']; ?></span>
            </a>
            <a href="orders.php?status=cancelled" class="btn btn-outline-danger <?php echo $status_filter == 'cancelled' ? 'active' : ''; ?>">
                Đã hủy
                <span class="badge bg-danger ms-1"><?php echo $db->selectOne("SELECT COUNT(*) as total FROM orders WHERE order_status = 'cancelled'")['total']; ?></span>
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
                        <th width="30">
                            <input type="checkbox" id="selectAll" title="Chọn tất cả">
                        </th>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="8" class="text-center">Không tìm thấy đơn hàng nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr data-order-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['order_status']; ?>">
                            <td>
                                <input type="checkbox" class="order-checkbox" value="<?php echo $order['id']; ?>" 
                                       <?php echo $order['order_status'] !== 'pending' ? 'disabled' : ''; ?>>
                            </td>
                            <td class="fw-bold text-primary">
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="text-decoration-none">
                                    #<?php echo $order['order_code']; ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                <small class="text-muted"><i class="fas fa-phone-alt me-1"></i><?php echo $order['customer_phone']; ?></small>
                            </td>
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                <br><small class="text-muted"><?php echo Functions::timeAgo($order['created_at']); ?></small>
                            </td>
                            <td class="fw-bold text-danger">
                                <?php echo $functions->formatPrice($order['final_amount']); ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php 
                                    $methods = [
                                        'cod' => 'COD', 
                                        'bank_transfer' => 'Chuyển khoản', 
                                        'momo' => 'MoMo'
                                    ];
                                    echo $methods[$order['payment_method']] ?? $order['payment_method']; 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $status = Functions::getStatusLabel($order['order_status'], 'order');
                                echo '<span class="badge ' . $status['class'] . ' status-badge" data-order-id="' . $order['id'] . '">' . $status['label'] . '</span>';
                                ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($order['order_status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success update-status quick-approve" 
                                            data-id="<?php echo $order['id']; ?>" 
                                            data-status="processing"
                                            title="Duyệt đơn hàng">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger update-status" 
                                            data-id="<?php echo $order['id']; ?>" 
                                            data-status="cancelled"
                                            title="Hủy đơn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($order['order_status'] == 'processing'): ?>
                                    <button class="btn btn-sm btn-primary update-status" 
                                            data-id="<?php echo $order['id']; ?>" 
                                            data-status="shipped"
                                            title="Chuyển sang giao hàng">
                                        <i class="fas fa-shipping-fast"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($order['order_status'] == 'shipped'): ?>
                                    <button class="btn btn-sm btn-success update-status" 
                                            data-id="<?php echo $order['id']; ?>" 
                                            data-status="delivered"
                                            title="Đã giao hàng">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                    <?php endif; ?>
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

<script>
// Xử lý cập nhật trạng thái đơn hàng bằng Ajax
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.update-status');
    const selectAllCheckbox = document.getElementById('selectAll');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    
    // Xử lý chọn tất cả
    selectAllCheckbox?.addEventListener('change', function() {
        orderCheckboxes.forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = this.checked;
            }
        });
    });
    
    // Xử lý cập nhật trạng thái đơn lẻ
    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.id;
            const status = this.dataset.status;
            let confirmMsg = 'Bạn có chắc muốn cập nhật trạng thái đơn hàng này?';
            
            if (status === 'cancelled') confirmMsg = 'Bạn có chắc chắn muốn HỦY đơn hàng này?';
            if (status === 'processing') confirmMsg = 'Duyệt đơn hàng này?';
            if (status === 'shipped') confirmMsg = 'Chuyển đơn hàng sang trạng thái "Đang giao hàng"?';
            if (status === 'delivered') confirmMsg = 'Xác nhận đã giao hàng thành công?';
            
            if (confirm(confirmMsg)) {
                updateOrderStatus(orderId, status, this);
            }
        });
    });
});

// Hàm cập nhật trạng thái đơn hàng
function updateOrderStatus(orderId, status, buttonElement) {
    // Disable button để tránh click nhiều lần
    if (buttonElement) {
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    fetch('<?php echo SITE_URL; ?>/api/admin/orders/update-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            showNotification(data.message, 'success');
            
            // Cập nhật giao diện
            updateOrderRow(orderId, status);
            
            // Reload trang sau 1 giây để cập nhật số liệu
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('Lỗi: ' + data.message, 'error');
            // Khôi phục button
            if (buttonElement) {
                buttonElement.disabled = false;
                restoreButtonIcon(buttonElement, status);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Đã có lỗi xảy ra khi kết nối server.', 'error');
        // Khôi phục button
        if (buttonElement) {
            buttonElement.disabled = false;
            restoreButtonIcon(buttonElement, status);
        }
    });
}

// Duyệt hàng loạt
function bulkApprove() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Vui lòng chọn ít nhất một đơn hàng để duyệt.');
        return;
    }
    
    if (confirm(`Duyệt ${checkedBoxes.length} đơn hàng đã chọn?`)) {
        let completed = 0;
        const total = checkedBoxes.length;
        
        checkedBoxes.forEach(checkbox => {
            const orderId = checkbox.value;
            updateOrderStatus(orderId, 'processing', null);
            completed++;
        });
        
        showNotification(`Đang xử lý ${total} đơn hàng...`, 'info');
    }
}

// Làm mới danh sách
function refreshOrders() {
    location.reload();
}

// Hiển thị thông báo
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Cập nhật giao diện hàng đơn hàng
function updateOrderRow(orderId, newStatus) {
    const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
    if (row) {
        const statusBadge = row.querySelector('.status-badge');
        const statusLabels = {
            'pending': { label: 'Chờ xử lý', class: 'badge bg-warning' },
            'processing': { label: 'Đang xử lý', class: 'badge bg-info' },
            'shipped': { label: 'Đang giao hàng', class: 'badge bg-primary' },
            'delivered': { label: 'Đã giao hàng', class: 'badge bg-success' },
            'cancelled': { label: 'Đã hủy', class: 'badge bg-danger' }
        };
        
        if (statusBadge && statusLabels[newStatus]) {
            statusBadge.className = statusLabels[newStatus].class + ' status-badge';
            statusBadge.textContent = statusLabels[newStatus].label;
        }
        
        // Cập nhật data-status
        row.setAttribute('data-status', newStatus);
        
        // Disable checkbox nếu không còn pending
        const checkbox = row.querySelector('.order-checkbox');
        if (checkbox && newStatus !== 'pending') {
            checkbox.disabled = true;
            checkbox.checked = false;
        }
    }
}

// Khôi phục icon button
function restoreButtonIcon(button, status) {
    const icons = {
        'processing': '<i class="fas fa-check"></i>',
        'shipped': '<i class="fas fa-shipping-fast"></i>',
        'delivered': '<i class="fas fa-check-double"></i>',
        'cancelled': '<i class="fas fa-times"></i>'
    };
    
    button.innerHTML = icons[status] || '<i class="fas fa-check"></i>';
}
</script>

<?php require_once 'includes/footer.php'; ?>