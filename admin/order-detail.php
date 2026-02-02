<?php
// admin/order-detail.php
require_once '../includes/init.php';

// Check admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$db = Database::getInstance();
$functions = new Functions();

// Lấy ID đơn hàng
$orderId = $_GET['id'] ?? 0;

if (!$orderId) {
    Functions::showMessage('error', 'Không tìm thấy đơn hàng');
    Functions::redirect('orders.php');
}

// Lấy thông tin đơn hàng
$order = $db->selectOne("SELECT * FROM orders WHERE id = ?", [$orderId]);

if (!$order) {
    Functions::showMessage('error', 'Đơn hàng không tồn tại');
    Functions::redirect('orders.php');
}

// Lấy chi tiết sản phẩm trong đơn hàng
$orderItems = $db->select("
    SELECT oi.*, p.image as product_image, p.slug as product_slug
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id
", [$orderId]);

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['order_status'] ?? '';
    $note = $_POST['note'] ?? '';
    
    if ($newStatus && in_array($newStatus, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        try {
            $db->update('orders', [
                'order_status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$orderId]);
            
            // Thêm ghi chú lịch sử nếu có
            if ($note) {
                $db->insert('order_history', [
                    'order_id' => $orderId,
                    'status' => $newStatus,
                    'note' => $note,
                    'created_by' => $_SESSION['user_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            Functions::showMessage('success', 'Cập nhật trạng thái đơn hàng thành công');
            Functions::redirect("order-detail.php?id=$orderId");
        } catch (Exception $e) {
            Functions::showMessage('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Chi tiết đơn hàng #' . $order['order_code'];
require_once 'includes/header.php';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-receipt me-2"></i>Chi tiết đơn hàng #<?php echo $order['order_code']; ?>
    </h1>
    <div>
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-1"></i>In đơn hàng
        </button>
    </div>
</div>

<?php Functions::displayFlashMessage(); ?>

<div class="row">
    <!-- Thông tin đơn hàng -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-success">Thông tin đơn hàng</h6>
                <?php 
                $status = Functions::getStatusLabel($order['order_status'], 'order');
                echo '<span class="' . $status['class'] . '">' . $status['label'] . '</span>';
                ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Mã đơn hàng:</td>
                                <td>#<?php echo $order['order_code']; ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Ngày đặt:</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phương thức thanh toán:</td>
                                <td>
                                    <?php 
                                    $methods = [
                                        'cod' => 'Thanh toán khi nhận hàng (COD)', 
                                        'bank_transfer' => 'Chuyển khoản ngân hàng', 
                                        'momo' => 'Ví MoMo'
                                    ];
                                    echo $methods[$order['payment_method']] ?? $order['payment_method']; 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Trạng thái thanh toán:</td>
                                <td>
                                    <?php 
                                    $paymentStatus = Functions::getStatusLabel($order['payment_status'], 'payment');
                                    echo '<span class="' . $paymentStatus['class'] . '">' . $paymentStatus['label'] . '</span>';
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Tổng tiền hàng:</td>
                                <td><?php echo $functions->formatPrice($order['total_amount']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phí vận chuyển:</td>
                                <td><?php echo $functions->formatPrice($order['shipping_fee']); ?></td>
                            </tr>
                            <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                            <tr>
                                <td class="fw-bold">Giảm giá:</td>
                                <td class="text-success">-<?php echo $functions->formatPrice($order['discount_amount']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="border-top">
                                <td class="fw-bold text-danger">Tổng thanh toán:</td>
                                <td class="fw-bold text-danger fs-5"><?php echo $functions->formatPrice($order['final_amount']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Sản phẩm đã đặt</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="100">Đơn giá</th>
                                <th width="80">Số lượng</th>
                                <th width="120">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['product_image']): ?>
                                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['product_image']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                             class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($item['product_name'] ?: 'Sản phẩm đã xóa'); ?></div>
                                            <?php if ($item['product_slug']): ?>
                                            <small class="text-muted">
                                                <a href="<?php echo SITE_URL; ?>/pages/product-detail.php?slug=<?php echo $item['product_slug']; ?>" 
                                                   target="_blank" class="text-decoration-none">
                                                    <i class="fas fa-external-link-alt me-1"></i>Xem sản phẩm
                                                </a>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end"><?php echo $functions->formatPrice($item['product_price']); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end fw-bold"><?php echo $functions->formatPrice($item['total_price']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Thông tin khách hàng -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Thông tin khách hàng</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Họ tên:</strong><br>
                    <?php echo htmlspecialchars($order['customer_name']); ?>
                </div>
                <div class="mb-3">
                    <strong>Số điện thoại:</strong><br>
                    <a href="tel:<?php echo $order['customer_phone']; ?>" class="text-decoration-none">
                        <i class="fas fa-phone me-1"></i><?php echo $order['customer_phone']; ?>
                    </a>
                </div>
                <div class="mb-3">
                    <strong>Email:</strong><br>
                    <?php if ($order['customer_email']): ?>
                    <a href="mailto:<?php echo $order['customer_email']; ?>" class="text-decoration-none">
                        <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($order['customer_email']); ?>
                    </a>
                    <?php else: ?>
                    <span class="text-muted">Không có</span>
                    <?php endif; ?>
                </div>
                <div class="mb-0">
                    <strong>Địa chỉ giao hàng:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?>
                </div>
            </div>
        </div>

        <!-- Cập nhật trạng thái -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Cập nhật trạng thái</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Trạng thái đơn hàng</label>
                        <select name="order_status" class="form-select" required>
                            <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                            <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                            <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>Đang giao hàng</option>
                            <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                            <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Nhập ghi chú về việc cập nhật trạng thái..."></textarea>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-success w-100">
                        <i class="fas fa-save me-1"></i>Cập nhật trạng thái
                    </button>
                </form>
            </div>
        </div>

        <!-- Ghi chú đơn hàng -->
        <?php if ($order['note']): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Ghi chú từ khách hàng</h6>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['note'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .btn, .card-header, .sidebar, .topbar, .footer {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .card-body {
        padding: 0 !important;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>