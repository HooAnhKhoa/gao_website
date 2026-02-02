<?php
// pages/checkout.php
require_once '../includes/init.php';

// Kiểm tra giỏ hàng
$cartInfo = $functions->getCartItems();
if ($cartInfo['total_items'] == 0) {
    Functions::showMessage('warning', 'Giỏ hàng trống!');
    header('Location: ' . SITE_URL . '/pages/products.php');
    exit;
}

$pageTitle = 'Thanh toán';
require_once '../includes/header.php';

$currentUser = Functions::getCurrentUser();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-success"><i class="fas fa-map-marker-alt me-2"></i>Thông tin nhận hàng</h5>
                </div>
                <div class="card-body p-4">
                    <form id="checkoutForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và tên</label>
                            <input type="text" class="form-control" name="full_name" 
                                   value="<?php echo $currentUser['full_name'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo $currentUser['phone'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email (Tùy chọn)</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo $currentUser['email'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ giao hàng</label>
                            <textarea class="form-control" name="address" rows="3" required
                                      placeholder="Số nhà, tên đường, phường/xã, quận/huyện..."><?php echo $currentUser['address'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú đơn hàng</label>
                            <textarea class="form-control" name="note" rows="2" placeholder="Ví dụ: Giao giờ hành chính..."></textarea>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold text-success mb-3"><i class="fas fa-credit-card me-2"></i>Phương thức thanh toán</h5>
                        
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                            <label class="form-check-label w-100 stretched-link" for="cod">
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                <p class="text-muted small mb-0">Bạn sẽ thanh toán tiền mặt cho shipper khi nhận được hàng.</p>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="banking" value="banking">
                            <label class="form-check-label w-100 stretched-link" for="banking">
                                <strong>Chuyển khoản ngân hàng</strong>
                                <p class="text-muted small mb-0">Quét mã QR hoặc chuyển khoản theo thông tin đơn hàng.</p>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 sticky-top" style="top: 90px; z-index: 1;">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-receipt me-2"></i>Đơn hàng của bạn</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <?php foreach ($cartInfo['items'] as $item): ?>
                                <tr class="border-bottom">
                                    <td style="width: 70px;" class="ps-3 py-3">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['image']; ?>" 
                                             class="img-fluid rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-truncate" style="max-width: 180px;">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                        <div class="small text-muted">x <?php echo $item['quantity']; ?></div>
                                    </td>
                                    <td class="text-end pe-3 py-3 fw-bold">
                                        <?php echo Functions::formatPrice($item['total_price']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white p-4">
                    <?php 
                    $shippingFee = ($cartInfo['total'] >= 500000) ? 0 : 30000;
                    $finalTotal = $cartInfo['total'] + $shippingFee;
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tạm tính:</span>
                        <span class="fw-bold"><?php echo Functions::formatPrice($cartInfo['total']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Phí vận chuyển:</span>
                        <span class="fw-bold text-success">
                            <?php echo $shippingFee == 0 ? 'Miễn phí' : Functions::formatPrice($shippingFee); ?>
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="h5 mb-0 fw-bold">Tổng cộng:</span>
                        <span class="h4 mb-0 fw-bold text-danger"><?php echo Functions::formatPrice($finalTotal); ?></span>
                    </div>
                    
                    <button type="button" onclick="processCheckout()" class="btn btn-success w-100 py-3 fw-bold text-uppercase fs-5 shadow-sm">
                        Đặt hàng ngay
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function để hiển thị thông báo
function showNotification(message, type = 'info') {
    // Tạo element thông báo
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Thêm vào body
    document.body.appendChild(notification);
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function processCheckout() {
    // 1. Thu thập dữ liệu form
    const form = document.getElementById('checkoutForm');
    const formData = new FormData(form);
    
    const data = {};
    formData.forEach((value, key) => (data[key] = value));

    // Validate cơ bản
    if (!data.full_name || !data.phone || !data.address) {
        showNotification('Vui lòng điền đầy đủ thông tin nhận hàng!', 'error');
        return;
    }

    // Hiển thị trạng thái đang xử lý
    const btn = document.querySelector('button[onclick="processCheckout()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
    btn.disabled = true;

    // 2. Gửi API (Dùng đường dẫn tương đối để tránh lỗi SITE_URL)
    // Từ pages/checkout.php ra api/checkout/process.php thì dùng ../api/...
    const apiUrl = '../api/checkout/process.php';

    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        // Kiểm tra xem server có trả về lỗi HTTP không (404, 500...)
        if (!response.ok) {
            const text = await response.text();
            console.error("HTTP Error:", response.status, text);
            throw new Error(`Server lỗi (${response.status}): ${text}`);
        }

        // Kiểm tra xem dữ liệu trả về có phải JSON không
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            // Nếu server trả về HTML (lỗi PHP Fatal error...)
            const text = await response.text();
            console.error("Server trả về HTML thay vì JSON:", text);
            throw new Error("Server bị lỗi nội bộ. Vui lòng kiểm tra Console (F12) để biết chi tiết.");
        }
    })
    .then(result => {
        if (result.success) {
            showNotification(result.message, 'success');
            // Chuyển hướng
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1500);
        } else {
            showNotification(result.message || 'Có lỗi xảy ra', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Chi tiết lỗi:', error);
        alert('Lỗi đặt hàng: ' + error.message); // Hiện popup để bạn dễ thấy lỗi
        
        showNotification(error.message, 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>