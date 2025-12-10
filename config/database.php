<?php
// Cấu hình cơ sở dữ liệu
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gaodb');
define('DB_CHARSET', 'utf8mb4');

// Cấu hình website
define('SITE_NAME', 'Gạo Ngon - Cửa hàng gạo chất lượng');
define('SITE_URL', 'http://localhost/gao_website');
define('ADMIN_EMAIL', 'admin@gao-ngon.com');

// Cấu hình session
define('SESSION_LIFETIME', 86400); // 24 giờ

// Cấu hình upload
define('UPLOAD_DIR', 'assets/images/products/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Cấu hình pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Cấu hình khác
define('CURRENCY', 'VND');
define('SHIPPING_FEE', 30000);
define('TAX_RATE', 0.1); // 10%
?>