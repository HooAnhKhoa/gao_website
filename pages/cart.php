<?php
require_once __DIR__ . '/../includes/init.php';

$pageTitle = 'Giỏ hàng - Gạo Ngon';
$pageDescription = 'Xem và quản lý giỏ hàng của bạn. Thêm, xóa, sửa số lượng sản phẩm trước khi thanh toán.';
$showBreadcrumb = true;

$breadcrumbItems = [
    ['text' => 'Trang chủ', 'url' => '../index.php'],
    ['text' => 'Giỏ hàng']
];

require_once '../includes/header.php';

$db = Database::getInstance();
$functions = new Functions();

// Get cart items
$cartData = $functions->getCartItems();
$cartItems = $cartData['items'];
$cartTotal = $cartData['total'];
$totalItems = $cartData['total_items'];

// Calculate shipping fee
$shippingFee = $cartTotal >= 500000 ? 0 : SHIPPING_FEE;
$finalTotal = $cartTotal + $shippingFee;
?>

<!-- Cart Section -->
<section class="cart-section py-5">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header mb-5">
            <h1 class="fw-bold mb-3">Giỏ hàng của bạn</h1>
            <p class="lead text-muted mb-0">
                <?php if ($totalItems > 0): ?>
                Bạn có <span class="text-success fw-bold"><?php echo $totalItems; ?></span> sản phẩm trong giỏ hàng
                <?php else: ?>
                Giỏ hàng của bạn đang trống
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($cartItems)): ?>
        <!-- Empty Cart -->
        <div class="empty-cart text-center py-5">
            <div class="empty-state mb-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                <h3 class="mb-3">Giỏ hàng trống</h3>
                <p class="text-muted mb-4">Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
                <a href="products.php" class="btn btn-success btn-lg">
                    <i class="fas fa-store me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
            
            <!-- Recommended Products -->
            <div class="recommended-products mt-5">
                <h4 class="fw-bold mb-4">Có thể bạn sẽ thích</h4>
                <div class="row g-4">
                    <?php
                    $recommendedProducts = $functions->getFeaturedProducts(4);
                    foreach ($recommendedProducts as $product):
                        $discount = $functions->calculateDiscount($product['price'], $product['sale_price']);
                        $currentPrice = $product['sale_price'] ?: $product['price'];
                    ?>
                    <div class="col-md-3">
                        <div class="product-card card border-0 shadow-sm h-100">
                            <div class="product-image position-relative" style="height: 200px;">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                    <img src="../assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                         class="card-img-top h-100 object-fit-cover" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </a>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h5>
                                <div class="product-price mb-3">
                                    <span class="current-price fw-bold text-success">
                                        <?php echo $functions->formatPrice($currentPrice); ?>
                                    </span>
                                    <?php if ($product['sale_price']): ?>
                                    <span class="original-price text-muted text-decoration-line-through ms-2 small">
                                        <?php echo $functions->formatPrice($product['price']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-outline-success w-100 add-to-cart-btn" 
                                        data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus me-1"></i>Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Cart with Items -->
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="cart-items card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">Sản phẩm</h5>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0">Đơn giá</h5>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0">Số lượng</h5>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0">Thành tiền</h5>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php foreach ($cartItems as $item): 
                            $itemTotal = $item['total_price'];
                        ?>
                        <div class="cart-item border-bottom p-3" data-id="<?php echo $item['product_id']; ?>">
                            <div class="row align-items-center">
                                <!-- Product Image & Info -->
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="product-image me-3">
                                            <a href="product-detail.php?id=<?php echo $item['product_id']; ?>">
                                                <img src="../assets/images/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" 
                                                     class="rounded" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            </a>
                                        </div>
                                        <div class="product-info">
                                            <h6 class="mb-1">
                                                <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                            </h6>
                                            <?php if ($item['stock_warning'] ?? false): ?>
                                            <div class="stock-warning small text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Chỉ còn <?php echo $item['stock_quantity']; ?> sản phẩm
                                            </div>
                                            <?php endif; ?>
                                            <div class="actions mt-2">
                                                <button class="btn btn-sm btn-outline-danger remove-item" 
                                                        data-id="<?php echo $item['product_id']; ?>">
                                                    <i class="fas fa-trash me-1"></i>Xóa
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary ms-2 save-for-later" 
                                                        data-id="<?php echo $item['product_id']; ?>">
                                                    <i class="fas fa-heart me-1"></i>Để dành
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Price -->
                                <div class="col-md-2 text-center">
                                    <div class="product-price">
                                        <span class="fw-bold text-success">
                                            <?php echo $functions->formatPrice($item['current_price']); ?>
                                        </span>
                                        <?php if ($item['sale_price']): ?>
                                        <div class="original-price small text-muted text-decoration-line-through">
                                            <?php echo $functions->formatPrice($item['price']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Quantity -->
                                <div class="col-md-2 text-center">
                                    <div class="quantity-selector">
                                        <div class="input-group input-group-sm" style="width: 120px; margin: 0 auto;">
                                            <button class="btn btn-outline-secondary decrease-qty" type="button">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center quantity-input" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" 
                                                   max="<?php echo $item['stock_quantity']; ?>"
                                                   data-id="<?php echo $item['product_id']; ?>">
                                            <button class="btn btn-outline-secondary increase-qty" type="button">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <?php if ($item['quantity'] > $item['stock_quantity']): ?>
                                        <div class="text-danger small mt-1">
                                            Vượt quá số lượng tồn kho
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Total -->
                                <div class="col-md-2 text-center">
                                    <div class="item-total">
                                        <span class="fw-bold text-success">
                                            <?php echo $functions->formatPrice($itemTotal); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Cart Actions -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="products.php" class="btn btn-outline-success">
                                <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                            </a>
                            <button class="btn btn-outline-danger" id="clearCartBtn">
                                <i class="fas fa-trash me-2"></i>Xóa giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Coupon Code -->
                <div class="coupon-section card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-ticket-alt text-success me-2"></i>Mã giảm giá
                        </h5>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="couponCode" 
                                       placeholder="Nhập mã giảm giá">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-success w-100" id="applyCouponBtn">
                                    Áp dụng mã
                                </button>
                            </div>
                        </div>
                        <div class="available-coupons mt-3">
                            <h6 class="fw-bold mb-2">Mã giảm giá có sẵn:</h6>
                            <div class="coupon-list">
                                <span class="badge bg-success me-2 mb-2 cursor-pointer" 
                                      onclick="applyCoupon('WELCOME10')">
                                    WELCOME10 - Giảm 10% đơn đầu
                                </span>
                                <span class="badge bg-success me-2 mb-2 cursor-pointer" 
                                      onclick="applyCoupon('FREESHIP')">
                                    FREESHIP - Miễn phí vận chuyển
                                </span>
                                <span class="badge bg-success me-2 mb-2 cursor-pointer" 
                                      onclick="applyCoupon('GAO50K')">
                                    GAO50K - Giảm 50.000đ
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Info -->
                <div class="shipping-info card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-truck text-success me-2"></i>Thông tin giao hàng
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Tỉnh/Thành phố</label>
                                    <select class="form-select" id="province">
                                        <option value="">Chọn tỉnh/thành phố</option>
                                        <option value="hanoi">Hà Nội</option>
                                        <option value="hcm">TP. Hồ Chí Minh</option>
                                        <option value="danang">Đà Nẵng</option>
                                        <option value="hue">Huế</option>
                                        <option value="cantho">Cần Thơ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Quận/Huyện</label>
                                    <select class="form-select" id="district" disabled>
                                        <option value="">Chọn quận/huyện</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Địa chỉ cụ thể</label>
                                    <textarea class="form-control" id="address" rows="2" 
                                              placeholder="Số nhà, tên đường, phường/xã..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Phương thức vận chuyển</label>
                                    <div class="shipping-methods">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" 
                                                   name="shippingMethod" id="standardShipping" 
                                                   value="standard" checked>
                                            <label class="form-check-label" for="standardShipping">
                                                Giao hàng tiêu chuẩn (3-5 ngày) - 
                                                <span class="text-success fw-bold">
                                                    <?php echo $functions->formatPrice(SHIPPING_FEE); ?>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   name="shippingMethod" id="expressShipping" 
                                                   value="express">
                                            <label class="form-check-label" for="expressShipping">
                                                Giao hàng nhanh (1-2 ngày) - 
                                                <span class="text-success fw-bold">
                                                    <?php echo $functions->formatPrice(SHIPPING_FEE + 20000); ?>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Ghi chú đơn hàng</label>
                                    <textarea class="form-control" id="orderNote" rows="2" 
                                              placeholder="Ghi chú cho người bán..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt me-2"></i>Tóm tắt đơn hàng
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <!-- Order Details -->
                        <div class="order-details mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính:</span>
                                <span class="fw-bold" id="subtotalAmount">
                                    <?php echo $functions->formatPrice($cartTotal); ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phí vận chuyển:</span>
                                <span class="fw-bold" id="shippingAmount">
                                    <?php echo $functions->formatPrice($shippingFee); ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="couponDiscountContainer" style="display: none;">
                                <span class="text-muted">Giảm giá:</span>
                                <span class="fw-bold text-success" id="couponDiscountAmount">0đ</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold">Tổng cộng:</span>
                                <span class="fw-bold fs-5 text-success" id="totalAmount">
                                    <?php echo $functions->formatPrice($finalTotal); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Promo Banner -->
                        <div class="promo-banner mb-4">
                            <div class="alert alert-success">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-gift fa-2x me-3"></i>
                                    <div>
                                        <strong>Đơn từ 500K</strong>
                                        <p class="mb-0 small">Được miễn phí vận chuyển</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Checkout Button -->
                        <div class="checkout-btn mb-3">
                            <?php if (Functions::isLoggedIn()): ?>
                            <a href="checkout.php" class="btn btn-success btn-lg w-100 py-3">
                                <i class="fas fa-lock me-2"></i>Tiến hành thanh toán
                            </a>
                            <?php else: ?>
                            <div class="d-grid gap-2">
                                <a href="checkout.php" class="btn btn-success btn-lg py-3">
                                    <i class="fas fa-lock me-2"></i>Thanh toán nhanh
                                </a>
                                <a href="login.php?redirect=checkout" class="btn btn-outline-success">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để thanh toán
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Security Info -->
                        <div class="security-info text-center">
                            <p class="small text-muted mb-2">
                                <i class="fas fa-shield-alt me-1 text-success"></i>
                                Thanh toán an toàn & bảo mật
                            </p>
                            <div class="payment-icons">
                                <img src="../assets/images/payment/visa.png" alt="Visa" height="30" class="me-2">
                                <img src="../assets/images/payment/mastercard.png" alt="MasterCard" height="30" class="me-2">
                                <img src="../assets/images/payment/momo.png" alt="Momo" height="30" class="me-2">
                                <img src="../assets/images/payment/cod.png" alt="COD" height="30">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Support -->
                <div class="customer-support card border-0 shadow-sm mt-4">
                    <div class="card-body text-center">
                        <i class="fas fa-headset fa-3x text-success mb-3"></i>
                        <h5 class="fw-bold mb-2">Hỗ trợ khách hàng</h5>
                        <p class="text-muted small mb-3">
                            Cần hỗ trợ? Liên hệ với chúng tôi
                        </p>
                        <a href="tel:19001000" class="btn btn-outline-success w-100">
                            <i class="fas fa-phone-alt me-2"></i>1900 1000
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Cart page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.querySelectorAll('.decrease-qty').forEach(button => {
        button.addEventListener('click', function() {
            const item = this.closest('.cart-item');
            const input = item.querySelector('.quantity-input');
            const productId = input.dataset.id;
            
            let quantity = parseInt(input.value);
            if (quantity > 1) {
                quantity--;
                updateCartItem(productId, quantity);
            }
        });
    });
    
    document.querySelectorAll('.increase-qty').forEach(button => {
        button.addEventListener('click', function() {
            const item = this.closest('.cart-item');
            const input = item.querySelector('.quantity-input');
            const productId = input.dataset.id;
            
            let quantity = parseInt(input.value);
            const max = parseInt(input.max);
            
            if (quantity < max) {
                quantity++;
                updateCartItem(productId, quantity);
            } else {
                showNotification('Đã đạt số lượng tối đa trong kho!', 'warning');
            }
        });
    });
    
    // Quantity input change
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.id;
            let quantity = parseInt(this.value);
            const max = parseInt(this.max);
            
            if (quantity < 1) quantity = 1;
            if (quantity > max) {
                quantity = max;
                showNotification('Số lượng không được vượt quá tồn kho!', 'warning');
            }
            
            this.value = quantity;
            updateCartItem(productId, quantity);
        });
    });
    
    // Remove item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                removeCartItem(productId);
            }
        });
    });
    
    // Save for later
    document.querySelectorAll('.save-for-later').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            saveForLater(productId);
        });
    });
    
    // Clear cart
    const clearCartBtn = document.getElementById('clearCartBtn');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) {
                clearCart();
            }
        });
    }
    
    // Apply coupon
    const applyCouponBtn = document.getElementById('applyCouponBtn');
    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', function() {
            const couponCode = document.getElementById('couponCode').value.trim();
            if (couponCode) {
                applyCoupon(couponCode);
            } else {
                showNotification('Vui lòng nhập mã giảm giá!', 'warning');
            }
        });
    }
    
    // Shipping method change
    document.querySelectorAll('input[name="shippingMethod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateShippingFee(this.value);
        });
    });
});

// Update cart item quantity
function updateCartItem(productId, quantity) {
    fetch('../api/cart/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã cập nhật giỏ hàng!', 'success');
            updateCartSummary(data.cart_data);
            updateCartDisplay(data.cart_data);
            updateCartCount();
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối!', 'error');
    });
}

// Remove cart item
function removeCartItem(productId) {
    fetch('../api/cart/remove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã xóa sản phẩm khỏi giỏ hàng!', 'success');
            updateCartSummary(data.cart_data);
            updateCartDisplay(data.cart_data);
            updateCartCount();
            
            // Remove item from DOM
            const itemElement = document.querySelector(`.cart-item[data-id="${productId}"]`);
            if (itemElement) {
                itemElement.remove();
                
                // Check if cart is empty
                const cartItems = document.querySelectorAll('.cart-item');
                if (cartItems.length === 0) {
                    location.reload(); // Reload to show empty cart
                }
            }
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối!', 'error');
    });
}

// Save for later
function saveForLater(productId) {
    fetch('../api/wishlist/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã thêm vào danh sách để dành!', 'success');
            removeCartItem(productId); // Remove from cart after saving
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối!', 'error');
    });
}

// Clear cart
function clearCart() {
    fetch('../api/cart/clear.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã xóa toàn bộ giỏ hàng!', 'success');
            location.reload(); // Reload page to show empty cart
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối!', 'error');
    });
}

// Apply coupon
function applyCoupon(code) {
    fetch('../api/cart/coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            coupon_code: code
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Áp dụng mã giảm giá thành công!', 'success');
            updateCartSummary(data.cart_data);
            updateCouponDisplay(data.coupon);
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối!', 'error');
    });
}

// Update shipping fee
function updateShippingFee(method) {
    fetch('../api/cart/shipping.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            shipping_method: method
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartSummary(data.cart_data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Update cart summary
function updateCartSummary(cartData) {
    if (!cartData) return;
    
    const subtotal = cartData.total || 0;
    const shippingFee = cartData.shipping_fee || 0;
    const discount = cartData.discount || 0;
    const total = cartData.final_total || 0;
    
    // Update summary display
    const subtotalElement = document.getElementById('subtotalAmount');
    const shippingElement = document.getElementById('shippingAmount');
    const discountElement = document.getElementById('couponDiscountAmount');
    const discountContainer = document.getElementById('couponDiscountContainer');
    const totalElement = document.getElementById('totalAmount');
    
    if (subtotalElement) subtotalElement.textContent = formatPrice(subtotal);
    if (shippingElement) shippingElement.textContent = formatPrice(shippingFee);
    
    if (discount > 0) {
        if (discountContainer) discountContainer.style.display = 'flex';
        if (discountElement) discountElement.textContent = '-' + formatPrice(discount);
    } else {
        if (discountContainer) discountContainer.style.display = 'none';
    }
    
    if (totalElement) totalElement.textContent = formatPrice(total);
}

// Update cart display
function updateCartDisplay(cartData) {
    if (!cartData || !cartData.items) return;
    
    // Update each item's total
    cartData.items.forEach(item => {
        const itemElement = document.querySelector(`.cart-item[data-id="${item.product_id}"]`);
        if (itemElement) {
            const totalElement = itemElement.querySelector('.item-total span');
            if (totalElement) {
                totalElement.textContent = formatPrice(item.total_price);
            }
            
            // Update quantity input
            const quantityInput = itemElement.querySelector('.quantity-input');
            if (quantityInput) {
                quantityInput.value = item.quantity;
            }
            
            // Update stock warning
            const warningElement = itemElement.querySelector('.stock-warning');
            if (item.quantity > item.stock_quantity) {
                if (!warningElement) {
                    const productInfo = itemElement.querySelector('.product-info');
                    if (productInfo) {
                        const warningDiv = document.createElement('div');
                        warningDiv.className = 'stock-warning small text-danger mt-1';
                        warningDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>
                                               Chỉ còn ${item.stock_quantity} sản phẩm`;
                        productInfo.appendChild(warningDiv);
                    }
                }
            } else if (warningElement) {
                warningElement.remove();
            }
        }
    });
}

// Update coupon display
function updateCouponDisplay(coupon) {
    if (!coupon) return;
    
    const couponCodeInput = document.getElementById('couponCode');
    if (couponCodeInput) {
        couponCodeInput.value = coupon.code;
    }
    
    // Show applied coupon
    const couponList = document.querySelector('.coupon-list');
    if (couponList) {
        const appliedBadge = document.createElement('span');
        appliedBadge.className = 'badge bg-warning text-dark me-2 mb-2';
        appliedBadge.innerHTML = `${coupon.code} - Đã áp dụng`;
        couponList.insertBefore(appliedBadge, couponList.firstChild);
    }
}

// Format price helper
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
}

// Update cart count in header
function updateCartCount() {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        const currentCount = parseInt(element.textContent) || 0;
        // We'll update this with actual count from API if needed
    });
}

// Predefined coupon function for click
window.applyCoupon = function(code) {
    document.getElementById('couponCode').value = code;
    applyCoupon(code);
};
</script>

<style>
/* Cart page styles */
.cart-item {
    transition: all 0.3s ease;
}

.cart-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.quantity-selector .input-group {
    max-width: 120px;
}

.quantity-selector input {
    width: 50px;
}

.stock-warning {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.coupon-list .badge {
    cursor: pointer;
    transition: all 0.3s ease;
}

.coupon-list .badge:hover {
    transform: scale(1.05);
}

.order-summary {
    border: 2px solid var(--success-color) !important;
}

.order-summary .promo-banner .alert {
    border-left: 4px solid var(--warning-color);
}

.customer-support {
    border: 1px dashed var(--success-color);
}

.customer-support:hover {
    border-color: var(--primary-color);
    background-color: rgba(25, 135, 84, 0.05);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .cart-items .card-header {
        display: none;
    }
    
    .cart-item .row {
        flex-direction: column;
    }
    
    .cart-item .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .cart-item .col-md-2 {
        text-align: left !important;
        margin-bottom: 0.5rem;
    }
    
    .cart-item .text-center {
        text-align: left !important;
    }
    
    .quantity-selector .input-group {
        margin: 0 !important;
    }
}

@media (max-width: 576px) {
    .coupon-section .row {
        flex-direction: column;
    }
    
    .coupon-section .col-md-4 {
        margin-top: 1rem;
    }
    
    .shipping-info .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>

<?php
require_once '../includes/footer.php';
?>