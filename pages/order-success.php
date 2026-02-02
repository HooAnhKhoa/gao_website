<?php
// pages/order-success.php
require_once '../includes/init.php';

$orderCode = $_GET['code'] ?? '';
if (empty($orderCode)) {
    Functions::showMessage('error', 'Mã đơn hàng không hợp lệ!');
    header('Location: ' . SITE_URL . '/pages/products.php');
    exit;
}

// Lấy thông tin đơn hàng
$order = $db->selectOne(
    "SELECT * FROM orders WHERE order_code = ?",
    [$orderCode]
);

if (!$order) {
    Functions::showMessage('error', 'Đơn hàng không tồn tại!');
    header('Location: ' . SITE_URL . '/pages/products.php');
    exit;
}

// Lấy chi tiết đơn hàng
$orderItems = $db->select(
    "SELECT * FROM order_items WHERE order_id = ?",
    [$order['id']]
);

$pageTitle = 'Đặt hàng thành công';
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h1 class="text-success mb-3">Đặt hàng thành công!</h1>
                <p class="lead text-muted">Cảm ơn bạn đã tin tưởng và đặt hàng tại cửa hàng chúng tôi.</p>
            </div>

            <!-- Order Info -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Thông tin đơn hàng</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mã đơn hàng:</strong> <span class="text-primary"><?php echo $order['order_code']; ?></span></p>
                            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                            <p><strong>Trạng thái:</strong> 
                                <span class="badge bg-warning">Chờ xử lý</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phương thức thanh toán:</strong> 
                                <?php 
                                switch($order['payment_method']) {
                                    case 'cod': echo 'Thanh toán khi nhận hàng'; break;
                                    case 'banking': echo 'Chuyển khoản ngân hàng'; break;
                                    default: echo 'Thanh toán khi nhận hàng';
                                }
                                ?>
                            </p>
                            <p><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?php echo Functions::formatPrice($order['final_amount']); ?></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin nhận hàng</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($order['customer_email']): ?>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                    <?php if ($order['note']): ?>
                    <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['note']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Chi tiết đơn hàng</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end pe-4">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    </td>
                                    <td class="text-center py-3">
                                        <?php echo Functions::formatPrice($item['product_price']); ?>
                                    </td>
                                    <td class="text-center py-3">
                                        <?php echo $item['quantity']; ?>
                                    </td>
                                    <td class="text-end pe-4 py-3 fw-bold">
                                        <?php echo Functions::formatPrice($item['total_price']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="ps-4 py-3 fw-bold">Tạm tính:</td>
                                    <td class="text-end pe-4 py-3 fw-bold"><?php echo Functions::formatPrice($order['total_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="ps-4 py-2">Phí vận chuyển:</td>
                                    <td class="text-end pe-4 py-2">
                                        <?php echo $order['shipping_fee'] > 0 ? Functions::formatPrice($order['shipping_fee']) : 'Miễn phí'; ?>
                                    </td>
                                </tr>
                                <tr class="border-top">
                                    <td colspan="3" class="ps-4 py-3 fw-bold text-success fs-5">Tổng cộng:</td>
                                    <td class="text-end pe-4 py-3 fw-bold text-danger fs-5"><?php echo Functions::formatPrice($order['final_amount']); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <?php if ($order['payment_method'] === 'banking'): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Hướng dẫn thanh toán</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Thông tin chuyển khoản:</h6>
                        <p class="mb-2"><strong>Ngân hàng:</strong> Vietcombank</p>
                        <p class="mb-2"><strong>Số tài khoản:</strong> 1234567890</p>
                        <p class="mb-2"><strong>Chủ tài khoản:</strong> CONG TY TNHH GAO NGON</p>
                        <p class="mb-2"><strong>Số tiền:</strong> <span class="text-danger fw-bold"><?php echo Functions::formatPrice($order['final_amount']); ?></span></p>
                        <p class="mb-0"><strong>Nội dung:</strong> <?php echo $order['order_code']; ?></p>
                    </div>
                    <p class="text-muted small">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Vui lòng chuyển khoản đúng số tiền và ghi đúng nội dung để đơn hàng được xử lý nhanh chóng.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Next Steps -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-secondary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Các bước tiếp theo</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-primary rounded-pill">1</span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Xác nhận đơn hàng</h6>
                                    <p class="text-muted small mb-0">Chúng tôi sẽ gọi điện xác nhận trong vòng 2 giờ</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-info rounded-pill">2</span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Chuẩn bị hàng</h6>
                                    <p class="text-muted small mb-0">Đóng gói và chuẩn bị giao hàng</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-warning rounded-pill">3</span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Giao hàng</h6>
                                    <p class="text-muted small mb-0">Giao hàng trong vòng 1-3 ngày làm việc</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-success rounded-pill">4</span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Hoàn thành</h6>
                                    <p class="text-muted small mb-0">Nhận hàng và thanh toán (nếu COD)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center">
                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                </a>
                <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>Về trang chủ
                </a>
            </div>

            <!-- Contact Info -->
            <div class="text-center mt-5 pt-4 border-top">
                <p class="text-muted">
                    <i class="fas fa-phone me-2"></i>Hotline: <strong>1900 1234</strong> |
                    <i class="fas fa-envelope me-2"></i>Email: <strong>support@gao-ngon.com</strong>
                </p>
                <p class="text-muted small">
                    Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi để được hỗ trợ.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    animation: bounceIn 1s ease-in-out;
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}
</style>

<?php require_once '../includes/footer.php'; ?>