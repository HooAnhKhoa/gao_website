<?php
// Trạng thái sản phẩm
define('PRODUCT_ACTIVE', 'active');
define('PRODUCT_INACTIVE', 'inactive');
define('PRODUCT_OUT_OF_STOCK', 'out_of_stock');

// Trạng thái đơn hàng
define('ORDER_PENDING', 'pending');
define('ORDER_PROCESSING', 'processing');
define('ORDER_SHIPPED', 'shipped');
define('ORDER_DELIVERED', 'delivered');
define('ORDER_CANCELLED', 'cancelled');

// Trạng thái thanh toán
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PAID', 'paid');
define('PAYMENT_FAILED', 'failed');

// Phương thức thanh toán
define('PAYMENT_COD', 'cod');
define('PAYMENT_BANK_TRANSFER', 'bank_transfer');
define('PAYMENT_MOMO', 'momo');

// Vai trò người dùng
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');

// Trạng thái người dùng
define('USER_ACTIVE', 'active');
define('USER_INACTIVE', 'inactive');

// Trạng thái đánh giá
define('REVIEW_PENDING', 'pending');
define('REVIEW_APPROVED', 'approved');
define('REVIEW_REJECTED', 'rejected');

// Thông báo lỗi
define('ERROR_REQUIRED', 'Trường này là bắt buộc');
define('ERROR_EMAIL_INVALID', 'Email không hợp lệ');
define('ERROR_PASSWORD_MISMATCH', 'Mật khẩu không khớp');
define('ERROR_LOGIN_FAILED', 'Email hoặc mật khẩu không đúng');
define('ERROR_EMAIL_EXISTS', 'Email đã được sử dụng');
define('ERROR_USERNAME_EXISTS', 'Tên đăng nhập đã được sử dụng');
define('ERROR_PRODUCT_NOT_FOUND', 'Sản phẩm không tồn tại');
define('ERROR_CART_EMPTY', 'Giỏ hàng trống');
define('ERROR_ORDER_NOT_FOUND', 'Đơn hàng không tồn tại');
define('ERROR_UNAUTHORIZED', 'Bạn không có quyền truy cập');
define('ERROR_INVALID_REQUEST', 'Yêu cầu không hợp lệ');

// Thông báo thành công
define('SUCCESS_REGISTER', 'Đăng ký thành công! Vui lòng đăng nhập');
define('SUCCESS_LOGIN', 'Đăng nhập thành công');
define('SUCCESS_LOGOUT', 'Đăng xuất thành công');
define('SUCCESS_PRODUCT_ADDED', 'Sản phẩm đã được thêm');
define('SUCCESS_PRODUCT_UPDATED', 'Sản phẩm đã được cập nhật');
define('SUCCESS_PRODUCT_DELETED', 'Sản phẩm đã được xóa');
define('SUCCESS_CART_ADDED', 'Đã thêm vào giỏ hàng');
define('SUCCESS_CART_UPDATED', 'Giỏ hàng đã được cập nhật');
define('SUCCESS_CART_REMOVED', 'Đã xóa khỏi giỏ hàng');
define('SUCCESS_ORDER_CREATED', 'Đặt hàng thành công');
define('SUCCESS_PROFILE_UPDATED', 'Hồ sơ đã được cập nhật');

// Màu sắc cho trạng thái
$statusColors = [
    'pending' => 'warning',
    'processing' => 'info',
    'shipped' => 'primary',
    'delivered' => 'success',
    'cancelled' => 'danger',
    'active' => 'success',
    'inactive' => 'secondary',
    'out_of_stock' => 'danger'
];

// Loại sản phẩm
$riceTypes = [
    'thom' => 'Gạo thơm',
    'nep' => 'Gạo nếp',
    'lut' => 'Gạo lứt',
    'dac_san' => 'Gạo đặc sản',
    'huu_co' => 'Gạo hữu cơ',
    'dinh_duong' => 'Gạo dinh dưỡng'
];

// Đơn vị tính
$units = [
    'kg' => 'Kilogram',
    'bao' => 'Bao',
    'hop' => 'Hộp'
];
?>