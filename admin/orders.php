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
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success">Bộ lọc & Tìm kiếm</h6>
    </div>
    <div class="card-body">
        <div class="btn-group" role="group">
            <a href="orders.php" class="btn btn-outline-secondary <?php echo $status_filter == '' ? 'active' : ''; ?>">Tất cả</a>
            <a href="orders.php?status=pending" class="btn btn-outline-warning <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">Chờ xử lý</a>
            <a href="orders.php?status=shipping" class="btn btn-outline-primary <?php echo $status_filter == 'shipping' ? 'active' : ''; ?>">Đang giao</a>
            <a href="orders.php?status=delivered" class="btn btn-outline-success <?php echo $status_filter == 'delivered' ? 'active' : ''; ?>">Hoàn thành</a>
            <a href="orders.php?status=cancelled" class="btn btn-outline-danger <?php echo $status_filter == 'cancelled' ? 'active' : ''; ?>">Đã hủy</a>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="table-success">
                    <tr>
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
                        <tr><td colspan="7" class="text-center">Không tìm thấy đơn hàng nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="fw-bold text-primary">
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="text-decoration-none">
                                    #<?php echo $order['order_code']; ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                <small class="text-muted"><i class="fas fa-phone-alt me-1"></i><?php echo $order['customer_phone']; ?></small>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
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
                                echo '<span class="badge ' . $status['class'] . '">' . $status['label'] . '</span>';
                                ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($order['order_status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success update-status" 
                                            data-id="<?php echo $order['id']; ?>" 
                                            data-status="processing"
                                            title="Xác nhận & Giao hàng">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger update-status" 
                                            data-id="<?php echo $order['id']; ?>" 
                                            data-status="cancelled"
                                            title="Hủy đơn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($order['order_status'] == 'processing' || $order['order_status'] == 'shipped'): ?>
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
    
    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.id;
            const status = this.dataset.status;
            let confirmMsg = 'Bạn có chắc muốn cập nhật trạng thái đơn hàng này?';
            
            if (status === 'cancelled') confirmMsg = 'Bạn có chắc chắn muốn HỦY đơn hàng này?';
            
            if (confirm(confirmMsg)) {
                // Gọi API cập nhật (cần tạo file api/admin/orders/update-status.php)
                // Hoặc gửi request POST về chính trang này nếu xử lý logic ở đây (tuy nhiên dùng API clean hơn)
                
                // Demo fetch API:
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
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Đã có lỗi xảy ra khi kết nối server.');
                });
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>