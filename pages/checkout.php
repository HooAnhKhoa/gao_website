<?php
// Check if cart has items
require_once __DIR__ . '/../includes/init.php';

$cartData = $functions->getCartItems();
if (empty($cartData['items'])) {
    header('Location: cart.php');
    exit;
}

$pageTitle = 'Thanh toán - Gạo Ngon';
$pageDescription = 'Hoàn tất đơn hàng của bạn. Chọn phương thức thanh toán và điền thông tin giao hàng.';
$showBreadcrumb = true;

$breadcrumbItems = [
    ['text' => 'Trang chủ', 'url' => '../index.php'],
    ['text' => 'Giỏ hàng', 'url' => 'cart.php'],
    ['text' => 'Thanh toán']
];

require_once '../includes/header.php';

$db = Database::getInstance();
$user = Functions::getCurrentUser();
$cartTotal = $cartData['total'];
$shippingFee = $cartTotal >= 500000 ? 0 : SHIPPING_FEE;
$finalTotal = $cartTotal + $shippingFee;

// Get user addresses if logged in
$userAddresses = [];
if ($user) {
    $userAddresses = $db->select(
        "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC",
        [$user['id']]
    );
}
?>

<!-- Checkout Section -->
<section class="checkout-section py-5">
    <div class="container">
        <!-- Progress Steps -->
        <div class="checkout-progress mb-5">
            <div class="progress-steps">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <div class="step-label">Giỏ hàng</div>
                </div>
                <div class="step active">
                    <div class="step-circle">2</div>
                    <div class="step-label">Thanh toán</div>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <div class="step-label">Hoàn tất</div>
                </div>
            </div>
        </div>

        <form id="checkoutForm" method="POST" action="../api/orders/create.php">
            <div class="row">
                <!-- Left Column - Customer Info -->
                <div class="col-lg-8">
                    <!-- Customer Information -->
                    <div class="customer-info card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Thông tin khách hàng
                            </h5>
                        </div>
                        
                        <div class="card-body">
                            <?php if ($user): ?>
                            <!-- Logged in user -->
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Đang đăng nhập với tài khoản: 
                                <strong><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></strong>
                                (<a href="../api/auth/logout.php?redirect=checkout" class="text-decoration-none">Đăng xuất</a>)
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Họ và tên *</label>
                                        <input type="text" class="form-control" 
                                               name="customer_name" 
                                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Email *</label>
                                        <input type="email" class="form-control" 
                                               name="customer_email" 
                                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Số điện thoại *</label>
                                        <input type="tel" class="form-control" 
                                               name="customer_phone" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                               required>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Guest checkout -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Họ và tên *</label>
                                        <input type="text" class="form-control" 
                                               name="customer_name" 
                                               required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Email *</label>
                                        <input type="email" class="form-control" 
                                               name="customer_email" 
                                               required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Số điện thoại *</label>
                                        <input type="tel" class="form-control" 
                                               name="customer_phone" 
                                               required>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="createAccount">
                                        <label class="form-check-label" for="createAccount">
                                            Tạo tài khoản để tích điểm và theo dõi đơn hàng dễ dàng hơn
                                        </label>
                                    </div>
                                    
                                    <div class="account-fields mt-3" style="display: none;">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label fw-bold">Mật khẩu *</label>
                                                    <input type="password" class="form-control" 
                                                           name="password" 
                                                           id="password">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label fw-bold">Xác nhận mật khẩu *</label>
                                                    <input type="password" class="form-control" 
                                                           name="confirm_password" 
                                                           id="confirm_password">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="shipping-address card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ giao hàng
                            </h5>
                        </div>
                        
                        <div class="card-body">
                            <?php if (!empty($userAddresses)): ?>
                            <!-- Saved addresses -->
                            <div class="saved-addresses mb-4">
                                <h6 class="fw-bold mb-3">Chọn địa chỉ đã lưu:</h6>
                                <div class="row g-3">
                                    <?php foreach ($userAddresses as $address): ?>
                                    <div class="col-md-6">
                                        <div class="address-card border rounded p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="address_id" 
                                                       value="<?php echo $address['id']; ?>"
                                                       id="address<?php echo $address['id']; ?>"
                                                       <?php echo $address['is_default'] ? 'checked' : ''; ?>
                                                       data-address='<?php echo json_encode($address); ?>'>
                                                <label class="form-check-label w-100" for="address<?php echo $address['id']; ?>">
                                                    <div class="address-info">
                                                        <strong><?php echo htmlspecialchars($address['full_name']); ?></strong>
                                                        <p class="mb-1 small"><?php echo htmlspecialchars($address['phone']); ?></p>
                                                        <p class="mb-0 small text-muted">
                                                            <?php echo htmlspecialchars($address['address']); ?>, 
                                                            <?php echo htmlspecialchars($address['district']); ?>, 
                                                            <?php echo htmlspecialchars($address['city']); ?>
                                                        </p>
                                                        <?php if ($address['is_default']): ?>
                                                        <span class="badge bg-success small">Mặc định</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-success btn-sm" 
                                            id="addNewAddressBtn">
                                        <i class="fas fa-plus me-1"></i>Thêm địa chỉ mới
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- New address form -->
                            <div class="new-address-form <?php echo empty($userAddresses) ? '' : 'd-none'; ?>" 
                                 id="newAddressForm">
                                <h6 class="fw-bold mb-3">
                                    <?php echo empty($userAddresses) ? 'Địa chỉ giao hàng' : 'Địa chỉ mới'; ?>
                                </h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label fw-bold">Họ và tên *</label>
                                            <input type="text" class="form-control" 
                                                   name="shipping_name" 
                                                   value="<?php echo $user ? htmlspecialchars($user['full_name'] ?? '') : ''; ?>"
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label fw-bold">Số điện thoại *</label>
                                            <input type="tel" class="form-control" 
                                                   name="shipping_phone" 
                                                   value="<?php echo $user ? htmlspecialchars($user['phone'] ?? '') : ''; ?>"
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label fw-bold">Tỉnh/Thành phố *</label>
                                            <select class="form-select" name="shipping_city" required>
                                                <option value="">Chọn tỉnh/thành phố</option>
                                                <option value="hanoi">Hà Nội</option>
                                                <option value="hcm">TP. Hồ Chí Minh</option>
                                                <option value="danang">Đà Nẵng</option>
                                                <option value="hue">Huế</option>
                                                <option value="cantho">Cần Thơ</option>
                                                <!-- Add more provinces -->
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label fw-bold">Quận/Huyện *</label>
                                            <select class="form-select" name="shipping_district" required>
                                                <option value="">Chọn quận/huyện</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label fw-bold">Địa chỉ cụ thể *</label>
                                            <textarea class="form-control" name="shipping_address" 
                                                      rows="2" 
                                                      placeholder="Số nhà, tên đường, phường/xã..."
                                                      required></textarea>
                                        </div>
                                    </div>
                                    
                                    <?php if ($user): ?>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="save_address" 
                                                   id="saveAddress">
                                            <label class="form-check-label" for="saveAddress">
                                                Lưu địa chỉ này cho lần mua sau
                                            </label>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Method -->
                    <div class="shipping-method card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-truck me-2"></i>Phương thức vận chuyển
                            </h5>
                        </div>
                        
                        <div class="card-body">
                            <div class="shipping-options">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" 
                                           name="shipping_method" 
                                           id="standardShipping" 
                                           value="standard" 
                                           checked 
                                           data-price="<?php echo SHIPPING_FEE; ?>">
                                    <label class="form-check-label" for="standardShipping">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Giao hàng tiêu chuẩn</strong>
                                                <p class="mb-0 small text-muted">Giao hàng trong 3-5 ngày làm việc</p>
                                            </div>
                                            <span class="fw-bold text-success">
                                                <?php echo $functions->formatPrice(SHIPPING_FEE); ?>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="shipping_method" 
                                           id="expressShipping" 
                                           value="express" 
                                           data-price="<?php echo SHIPPING_FEE + 20000; ?>">
                                    <label class="form-check-label" for="expressShipping">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Giao hàng nhanh</strong>
                                                <p class="mb-0 small text-muted">Giao hàng trong 1-2 ngày làm việc</p>
                                            </div>
                                            <span class="fw-bold text-success">
                                                <?php echo $functions->formatPrice(SHIPPING_FEE + 20000); ?>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="payment-method card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>Phương thức thanh toán
                            </h5>
                        </div>
                        
                        <div class="card-body">
                            <div class="payment-options">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" 
                                           name="payment_method" 
                                           id="codPayment" 
                                           value="cod" 
                                           checked>
                                    <label class="form-check-label w-100" for="codPayment">
                                        <div class="d-flex align-items-center">
                                            <div class="payment-icon me-3">
                                                <img src="../assets/images/payment/cod.png" alt="COD" height="40">
                                            </div>
                                            <div>
                                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                                <p class="mb-0 small text-muted">Thanh toán bằng tiền mặt khi nhận hàng</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" 
                                           name="payment_method" 
                                           id="bankTransfer" 
                                           value="bank_transfer">
                                    <label class="form-check-label w-100" for="bankTransfer">
                                        <div class="d-flex align-items-center">
                                            <div class="payment-icon me-3">
                                                <img src="../assets/images/payment/bank-transfer.png" alt="Bank Transfer" height="40">
                                            </div>
                                            <div>
                                                <strong>Chuyển khoản ngân hàng</strong>
                                                <p class="mb-0 small text-muted">Chuyển khoản trước khi giao hàng</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="payment_method" 
                                           id="momoPayment" 
                                           value="momo">
                                    <label class="form-check-label w-100" for="momoPayment">
                                        <div class="d-flex align-items-center">
                                            <div class="payment-icon me-3">
                                                <img src="../assets/images/payment/momo.png" alt="Momo" height="40">
                                            </div>
                                            <div>
                                                <strong>Ví điện tử Momo</strong>
                                                <p class="mb-0 small text-muted">Thanh toán qua ứng dụng Momo</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Bank transfer details (hidden by default) -->
                            <div class="bank-details mt-3 p-3 border rounded" style="display: none;" id="bankDetails">
                                <h6 class="fw-bold mb-3">Thông tin chuyển khoản:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>Ngân hàng:</strong> Vietcombank
                                            </li>
                                            <li class="mb-2">
                                                <strong>Số tài khoản:</strong> 123456789012
                                            </li>
                                            <li class="mb-2">
                                                <strong>Chủ tài khoản:</strong> CÔNG TY TNHH GẠO NGON
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="small text-muted mb-2">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            Vui lòng ghi nội dung: 
                                            <strong>SDH<?php echo date('Ymd'); ?> - Tên của bạn</strong>
                                        </p>
                                        <p class="small text-muted mb-0">
                                            Đơn hàng sẽ được xử lý sau khi nhận được thanh toán.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Note -->
                    <div class="order-note card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-sticky-note me-2"></i>Ghi chú đơn hàng
                                </label>
                                <textarea class="form-control" name="order_note" 
                                          rows="3" 
                                          placeholder="Ghi chú cho người bán (không bắt buộc)..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary card border-0 shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>Đơn hàng của bạn
                            </h5>
                        </div>
                        
                        <div class="card-body">
                            <!-- Order Items -->
                            <div class="order-items mb-4">
                                <h6 class="fw-bold mb-3">Sản phẩm</h6>
                                <div class="order-items-list" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($cartData['items'] as $item): ?>
                                    <div class="order-item d-flex justify-content-between mb-2">
                                        <div class="item-info">
                                            <div class="item-name small">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                                <span class="text-muted">× <?php echo $item['quantity']; ?></span>
                                            </div>
                                        </div>
                                        <div class="item-price">
                                            <span class="fw-bold">
                                                <?php echo $functions->formatPrice($item['total_price']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Order Summary -->
                            <div class="order-summary-details mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tạm tính:</span>
                                    <span class="fw-bold" id="summarySubtotal">
                                        <?php echo $functions->formatPrice($cartTotal); ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span class="fw-bold" id="summaryShipping">
                                        <?php echo $functions->formatPrice($shippingFee); ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2" id="couponSummary" style="display: none;">
                                    <span class="text-muted">Giảm giá:</span>
                                    <span class="fw-bold text-success" id="summaryDiscount">0đ</span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="fw-bold">Tổng cộng:</span>
                                    <span class="fw-bold fs-5 text-success" id="summaryTotal">
                                        <?php echo $functions->formatPrice($finalTotal); ?>
                                    </span>
                                </div>
                                
                                <div class="promo-banner alert alert-success mb-0">
                                    <i class="fas fa-truck me-2"></i>
                                    <small>Miễn phí vận chuyển cho đơn từ 500.000đ</small>
                                </div>
                            </div>
                            
                            <!-- Terms and Conditions -->
                            <div class="terms mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="agreeTerms" 
                                           required>
                                    <label class="form-check-label small" for="agreeTerms">
                                        Tôi đồng ý với 
                                        <a href="#" class="text-decoration-none">Điều khoản dịch vụ</a> 
                                        và 
                                        <a href="#" class="text-decoration-none">Chính sách bảo mật</a>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Place Order Button -->
                            <div class="place-order">
                                <button type="submit" class="btn btn-success btn-lg w-100 py-3" 
                                        id="placeOrderBtn">
                                    <i class="fas fa-lock me-2"></i>Đặt hàng
                                </button>
                                
                                <p class="text-center small text-muted mt-2 mb-0">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Thanh toán an toàn & bảo mật
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Info -->
                    <div class="security-info card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-shield-alt text-success me-2"></i>Bảo mật thông tin
                            </h6>
                            <ul class="list-unstyled small text-muted">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Thông tin được mã hóa SSL 256-bit
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Không lưu trữ thông tin thẻ tín dụng
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Tuân thủ quy định bảo mật PCI DSS
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Need Help -->
                    <div class="need-help card border-0 shadow-sm mt-4">
                        <div class="card-body text-center">
                            <i class="fas fa-question-circle fa-3x text-success mb-3"></i>
                            <h6 class="fw-bold mb-2">Cần hỗ trợ?</h6>
                            <p class="text-muted small mb-3">
                                Liên hệ với chúng tôi nếu bạn gặp khó khăn
                            </p>
                            <a href="tel:19001000" class="btn btn-outline-success w-100">
                                <i class="fas fa-phone-alt me-2"></i>1900 1000
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-success mb-4" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mb-3">Đang xử lý đơn hàng...</h5>
                <p class="text-muted mb-0">Vui lòng không đóng trình duyệt</p>
            </div>
        </div>
    </div>
</div>

<script>
// Checkout page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Toggle create account fields
    const createAccountCheckbox = document.getElementById('createAccount');
    const accountFields = document.querySelector('.account-fields');
    
    if (createAccountCheckbox) {
        createAccountCheckbox.addEventListener('change', function() {
            if (this.checked) {
                accountFields.style.display = 'block';
                // Make password fields required
                document.getElementById('password').required = true;
                document.getElementById('confirm_password').required = true;
            } else {
                accountFields.style.display = 'none';
                // Remove required from password fields
                document.getElementById('password').required = false;
                document.getElementById('confirm_password').required = false;
            }
        });
    }
    
    // Toggle new address form
    const addNewAddressBtn = document.getElementById('addNewAddressBtn');
    const newAddressForm = document.getElementById('newAddressForm');
    const addressRadios = document.querySelectorAll('input[name="address_id"]');
    
    if (addNewAddressBtn) {
        addNewAddressBtn.addEventListener('click', function() {
            newAddressForm.classList.remove('d-none');
            // Uncheck all saved addresses
            addressRadios.forEach(radio => {
                radio.checked = false;
            });
        });
    }
    
    // When selecting saved address, hide new address form
    addressRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                newAddressForm.classList.add('d-none');
                
                // Auto-fill shipping info from saved address
                const addressData = JSON.parse(this.dataset.address);
                document.querySelector('input[name="shipping_name"]').value = addressData.full_name || '';
                document.querySelector('input[name="shipping_phone"]').value = addressData.phone || '';
                document.querySelector('select[name="shipping_city"]').value = addressData.city || '';
                document.querySelector('select[name="shipping_district"]').value = addressData.district || '';
                document.querySelector('textarea[name="shipping_address"]').value = addressData.address || '';
            }
        });
    });
    
    // Show bank transfer details when selected
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const bankDetails = document.getElementById('bankDetails');
    
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'bank_transfer') {
                bankDetails.style.display = 'block';
            } else {
                bankDetails.style.display = 'none';
            }
        });
    });
    
    // Update shipping fee when shipping method changes
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    const shippingFeeElement = document.getElementById('summaryShipping');
    const totalElement = document.getElementById('summaryTotal');
    
    shippingRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const shippingFee = parseInt(this.dataset.price);
            const subtotal = <?php echo $cartTotal; ?>;
            const discount = 0; // Add discount calculation if needed
            
            const total = subtotal + shippingFee - discount;
            
            shippingFeeElement.textContent = formatPrice(shippingFee);
            totalElement.textContent = formatPrice(total);
        });
    });
    
    // Form validation and submission
    const checkoutForm = document.getElementById('checkoutForm');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    
    checkoutForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();
        
        // Disable submit button
        placeOrderBtn.disabled = true;
        placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
        
        // Submit form via AJAX
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loadingModal.hide();
            
            if (data.success) {
                // Redirect to success page
                window.location.href = `order-success.php?order_code=${data.order_code}`;
            } else {
                // Show error message
                showNotification(data.message || 'Có lỗi xảy ra khi đặt hàng!', 'error');
                
                // Re-enable submit button
                placeOrderBtn.disabled = false;
                placeOrderBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Đặt hàng';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingModal.hide();
            showNotification('Lỗi kết nối! Vui lòng thử lại.', 'error');
            
            // Re-enable submit button
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Đặt hàng';
        });
    });
    
    // City-District dependency
    const citySelect = document.querySelector('select[name="shipping_city"]');
    const districtSelect = document.querySelector('select[name="shipping_district"]');
    
    const districts = {
        'hanoi': ['Ba Đình', 'Hoàn Kiếm', 'Hai Bà Trưng', 'Đống Đa', 'Cầu Giấy', 'Thanh Xuân', 'Hoàng Mai', 'Long Biên'],
        'hcm': ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 9', 'Quận 10'],
        'danang': ['Hải Châu', 'Thanh Khê', 'Sơn Trà', 'Ngũ Hành Sơn', 'Liên Chiểu', 'Cẩm Lệ'],
        'hue': ['Huế', 'Phong Điền', 'Quảng Điền', 'Phú Vang', 'Hương Thủy', 'Hương Trà'],
        'cantho': ['Ninh Kiều', 'Bình Thủy', 'Cái Răng', 'Ô Môn', 'Thốt Nốt']
    };
    
    if (citySelect) {
        citySelect.addEventListener('change', function() {
            const selectedCity = this.value;
            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            
            if (districts[selectedCity]) {
                districtSelect.disabled = false;
                districts[selectedCity].forEach(district => {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    districtSelect.appendChild(option);
                });
            } else {
                districtSelect.disabled = true;
            }
        });
    }
});

// Form validation
function validateForm() {
    // Check required fields
    const requiredFields = document.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            
            // Add error message
            if (!field.nextElementSibling?.classList.contains('invalid-feedback')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Trường này là bắt buộc';
                field.parentNode.appendChild(errorDiv);
            }
        } else {
            field.classList.remove('is-invalid');
            const errorDiv = field.nextElementSibling;
            if (errorDiv?.classList.contains('invalid-feedback')) {
                errorDiv.remove();
            }
        }
    });
    
    // Validate email
    const emailField = document.querySelector('input[name="customer_email"]');
    if (emailField && emailField.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value.trim())) {
            isValid = false;
            emailField.classList.add('is-invalid');
            
            if (!emailField.nextElementSibling?.classList.contains('invalid-feedback')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Email không hợp lệ';
                emailField.parentNode.appendChild(errorDiv);
            }
        }
    }
    
    // Validate phone
    const phoneField = document.querySelector('input[name="customer_phone"]');
    if (phoneField && phoneField.value.trim()) {
        const phoneRegex = /^[0-9]{10,11}$/;
        if (!phoneRegex.test(phoneField.value.trim())) {
            isValid = false;
            phoneField.classList.add('is-invalid');
            
            if (!phoneField.nextElementSibling?.classList.contains('invalid-feedback')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Số điện thoại không hợp lệ';
                phoneField.parentNode.appendChild(errorDiv);
            }
        }
    }
    
    // Check terms agreement
    const termsCheckbox = document.getElementById('agreeTerms');
    if (termsCheckbox && !termsCheckbox.checked) {
        isValid = false;
        termsCheckbox.classList.add('is-invalid');
        showNotification('Vui lòng đồng ý với điều khoản dịch vụ!', 'warning');
    }
    
    // Check create account password match
    const createAccountCheckbox = document.getElementById('createAccount');
    if (createAccountCheckbox && createAccountCheckbox.checked) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            isValid = false;
            showNotification('Mật khẩu xác nhận không khớp!', 'error');
        }
    }
    
    return isValid;
}

// Format price helper
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
}
</script>

<style>
/* Checkout page styles */
.checkout-progress .progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 800px;
    margin: 0 auto;
    position: relative;
}

.checkout-progress .progress-steps::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 10%;
    right: 10%;
    height: 2px;
    background-color: #dee2e6;
    z-index: 1;
}

.checkout-progress .step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.checkout-progress .step-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #dee2e6;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.checkout-progress .step.active .step-circle {
    background-color: var(--success-color);
    color: white;
}

.checkout-progress .step-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-align: center;
}

.checkout-progress .step.active .step-label {
    color: var(--success-color);
    font-weight: 500;
}

/* Address card */
.address-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.address-card:hover {
    border-color: var(--success-color) !important;
    background-color: rgba(25, 135, 84, 0.05);
}

.address-card .form-check-label {
    cursor: pointer;
}

/* Payment options */
.payment-options .form-check-label {
    cursor: pointer;
    padding: 1rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.payment-options .form-check-input:checked + .form-check-label {
    background-color: rgba(25, 135, 84, 0.1);
    border: 1px solid var(--success-color);
}

/* Order summary sticky */
.order-summary {
    border: 2px solid var(--success-color) !important;
}

.order-items-list::-webkit-scrollbar {
    width: 6px;
}

.order-items-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.order-items-list::-webkit-scrollbar-thumb {
    background: var(--success-color);
    border-radius: 3px;
}

/* Security info */
.security-info {
    border-left: 4px solid var(--success-color);
}

/* Need help */
.need-help {
    border: 1px dashed var(--success-color);
    transition: all 0.3s ease;
}

.need-help:hover {
    border-color: var(--primary-color);
    background-color: rgba(25, 135, 84, 0.05);
}

/* Loading modal */
#loadingModal .modal-content {
    background-color: rgba(255, 255, 255, 0.95);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .checkout-progress .progress-steps::before {
        left: 5%;
        right: 5%;
    }
    
    .checkout-progress .step-label {
        font-size: 0.75rem;
    }
    
    .payment-options .payment-icon {
        display: none;
    }
    
    .bank-details .row {
        flex-direction: column;
    }
}
</style>

<?php
require_once '../includes/footer.php';
?>