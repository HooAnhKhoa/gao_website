<?php
// api/orders/detail.php
header('Content-Type: application/json');

try {
    require_once '../../includes/init.php';
    
    // Kiểm tra đăng nhập
    if (!Functions::isLoggedIn()) {
        throw new Exception('Vui lòng đăng nhập để xem chi tiết đơn hàng');
    }
    
    $orderCode = $_GET['code'] ?? '';
    if (empty($orderCode)) {
        throw new Exception('Mã đơn hàng không hợp lệ');
    }
    
    // Lấy thông tin đơn hàng (chỉ của user hiện tại)
    $order = $db->selectOne(
        "SELECT * FROM orders WHERE order_code = ? AND user_id = ?",
        [$orderCode, $_SESSION['user_id']]
    );
    
    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại hoặc bạn không có quyền xem');
    }
    
    // Lấy chi tiết sản phẩm
    $orderItems = $db->select(
        "SELECT oi.*, p.image, p.slug 
         FROM order_items oi 
         LEFT JOIN products p ON oi.product_id = p.id 
         WHERE oi.order_id = ?",
        [$order['id']]
    );
    
    // Tạo HTML cho modal
    ob_start();
    ?>
    
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Thông tin đơn hàng</h6>
            <table class="table table-borderless table-sm">
                <tr>
                    <td width="40%"><strong>Mã đơn hàng:</strong></td>
                    <td><?php echo $order['order_code']; ?></td>
                </tr>
                <tr>
                    <td><strong>Ngày đặt:</strong></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                </tr>
                <tr>
                    <td><strong>Trạng thái:</strong></td>
                    <td>
                        <?php
                        $statusInfo = Functions::getStatusLabel($order['order_status'], 'order');
                        echo '<span class="' . $statusInfo['class'] . '">' . $statusInfo['label'] . '</span>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Thanh toán:</strong></td>
                    <td>
                        <?php
                        $paymentInfo = Functions::getStatusLabel($order['payment_status'], 'payment');
                        echo '<span class="' . $paymentInfo['class'] . '">' . $paymentInfo['label'] . '</span>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Phương thức:</strong></td>
                    <td>
                        <?php 
                        switch($order['payment_method']) {
                            case 'cod': echo 'Thanh toán khi nhận hàng'; break;
                            case 'bank_transfer': echo 'Chuyển khoản ngân hàng'; break;
                            case 'momo': echo 'Ví MoMo'; break;
                            default: echo 'Thanh toán khi nhận hàng';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-success mb-3"><i class="fas fa-map-marker-alt me-2"></i>Thông tin nhận hàng</h6>
            <table class="table table-borderless table-sm">
                <tr>
                    <td width="30%"><strong>Họ tên:</strong></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Điện thoại:</strong></td>
                    <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                </tr>
                <?php if ($order['customer_email']): ?>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Địa chỉ:</strong></td>
                    <td><?php echo htmlspecialchars($order['customer_address']); ?></td>
                </tr>
                <?php if ($order['note']): ?>
                <tr>
                    <td><strong>Ghi chú:</strong></td>
                    <td><?php echo htmlspecialchars($order['note']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <hr class="my-4">
    
    <h6 class="text-info mb-3"><i class="fas fa-shopping-bag me-2"></i>Chi tiết sản phẩm</h6>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="bg-light">
                <tr>
                    <th width="60px">Ảnh</th>
                    <th>Sản phẩm</th>
                    <th width="100px" class="text-center">Đơn giá</th>
                    <th width="80px" class="text-center">SL</th>
                    <th width="120px" class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td class="text-center">
                        <?php if ($item['image']): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['image']; ?>" 
                             class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else: ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/no-image.svg" 
                             class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                    </td>
                    <td class="text-center">
                        <?php echo Functions::formatPrice($item['product_price']); ?>
                    </td>
                    <td class="text-center">
                        <?php echo $item['quantity']; ?>
                    </td>
                    <td class="text-end">
                        <strong><?php echo Functions::formatPrice($item['total_price']); ?></strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td colspan="4" class="text-end"><strong>Tạm tính:</strong></td>
                    <td class="text-end"><strong><?php echo Functions::formatPrice($order['total_amount']); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end">Phí vận chuyển:</td>
                    <td class="text-end">
                        <?php echo $order['shipping_fee'] > 0 ? Functions::formatPrice($order['shipping_fee']) : 'Miễn phí'; ?>
                    </td>
                </tr>
                <?php if ($order['discount_amount'] > 0): ?>
                <tr>
                    <td colspan="4" class="text-end">Giảm giá:</td>
                    <td class="text-end text-success">-<?php echo Functions::formatPrice($order['discount_amount']); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="table-success">
                    <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                    <td class="text-end"><strong class="text-danger fs-5"><?php echo Functions::formatPrice($order['final_amount']); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <?php if ($order['payment_method'] === 'bank_transfer' && $order['payment_status'] === 'pending'): ?>
    <div class="alert alert-warning mt-3">
        <h6><i class="fas fa-credit-card me-2"></i>Thông tin chuyển khoản:</h6>
        <p class="mb-1"><strong>Ngân hàng:</strong> Vietcombank</p>
        <p class="mb-1"><strong>Số tài khoản:</strong> 1234567890</p>
        <p class="mb-1"><strong>Chủ tài khoản:</strong> CONG TY TNHH GAO NGON</p>
        <p class="mb-1"><strong>Số tiền:</strong> <span class="text-danger fw-bold"><?php echo Functions::formatPrice($order['final_amount']); ?></span></p>
        <p class="mb-0"><strong>Nội dung:</strong> <?php echo $order['order_code']; ?></p>
    </div>
    <?php endif; ?>
    
    <?php
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'order' => $order
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>