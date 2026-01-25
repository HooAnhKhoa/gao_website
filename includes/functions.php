<?php
class Functions {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Chuyển hướng
    public static function redirect($url) {
        header("Location: " . $url);
        exit();
    }
    
    // Hiển thị thông báo
    public static function showMessage($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'text' => $message
        ];
    }
    
    // Hiển thị flash message
    public static function displayFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $alertClass = '';
            
            switch ($message['type']) {
                case 'success':
                    $alertClass = 'alert-success';
                    break;
                case 'error':
                    $alertClass = 'alert-danger';
                    break;
                case 'warning':
                    $alertClass = 'alert-warning';
                    break;
                case 'info':
                    $alertClass = 'alert-info';
                    break;
            }
            
            echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($message['text']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            
            unset($_SESSION['flash_message']);
        }
    }
    
    // Format tiền
    public static function formatPrice($price) {
        return number_format($price, 0, ',', '.') . 'đ';
    }
    
    // Tạo slug từ chuỗi
    public static function createSlug($string) {
        $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
        $slug = strtolower(trim($slug, '-'));
        return $slug;
    }
    
    // Kiểm tra đăng nhập
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Kiểm tra admin
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
    }
    
    // Lấy thông tin user hiện tại
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            $db = Database::getInstance();
            return $db->selectOne(
                "SELECT * FROM users WHERE id = ?",
                [$_SESSION['user_id']]
            );
        }
        return null;
    }
    
    // === [ĐOẠN MÃ ĐÃ SỬA LỖI] ===
    // Lấy số lượng giỏ hàng
    public static function getCartCount() {
        $db = Database::getInstance();
        
        if (self::isLoggedIn()) {
            // Đã đăng nhập: Lấy theo user_id
            return $db->selectOne(
                "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?",
                [$_SESSION['user_id']]
            )['total'] ?? 0;
        } else {
            // Khách vãng lai: Lấy trực tiếp từ session_id() của hệ thống
            // SỬA: Bỏ kiểm tra isset($_SESSION['session_id']) vì sai logic
            $sid = session_id();
            if ($sid) {
                return $db->selectOne(
                    "SELECT SUM(quantity) as total FROM cart WHERE session_id = ?",
                    [$sid]
                )['total'] ?? 0;
            }
        }
        
        return 0;
    }
    // =============================
    
    // Tạo mã đơn hàng
    public static function generateOrderCode() {
        return 'DH' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }
    
    // Upload ảnh
    public static function uploadImage($file, $targetDir = UPLOAD_DIR) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Lỗi upload file: " . $file['error']);
        }
        
        // Kiểm tra kích thước
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception("File quá lớn. Kích thước tối đa: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB");
        }
        
        // Kiểm tra loại file
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ALLOWED_TYPES)) {
            throw new Exception("Loại file không hợp lệ. Chỉ chấp nhận: " . implode(', ', ALLOWED_TYPES));
        }
        
        // Tạo tên file mới
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $targetPath = $targetDir . $fileName;
        
        // Tạo thư mục nếu chưa có
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Di chuyển file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Không thể di chuyển file đã upload");
        }
        
        return $fileName;
    }
    
    // Xóa ảnh
    public static function deleteImage($filename) {
        if ($filename && file_exists(UPLOAD_DIR . $filename)) {
            unlink(UPLOAD_DIR . $filename);
        }
    }
    
    // Pagination
    public static function paginate($totalItems, $currentPage, $itemsPerPage = ITEMS_PER_PAGE, $url = '') {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $currentPage = max(1, min($currentPage, $totalPages));
        
        $pagination = [
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'items_per_page' => $itemsPerPage,
            'offset' => ($currentPage - 1) * $itemsPerPage,
            'has_prev' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'prev_page' => $currentPage - 1,
            'next_page' => $currentPage + 1
        ];
        
        return $pagination;
    }
    
    // Tính giá giảm
    public static function calculateDiscount($price, $salePrice) {
        if ($salePrice && $salePrice < $price) {
            $discount = round((($price - $salePrice) / $price) * 100);
            return $discount . '%';
        }
        return null;
    }
    
    // Xử lý input
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    // Validate email
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    // Validate phone
    public static function validatePhone($phone) {
        return preg_match('/^[0-9]{10,11}$/', $phone);
    }
    
    // Get status label
    public static function getStatusLabel($status, $type = 'order') {
        $labels = [
            'order' => [
                ORDER_PENDING => ['label' => 'Chờ xử lý', 'class' => 'badge bg-warning'],
                ORDER_PROCESSING => ['label' => 'Đang xử lý', 'class' => 'badge bg-info'],
                ORDER_SHIPPED => ['label' => 'Đang giao hàng', 'class' => 'badge bg-primary'],
                ORDER_DELIVERED => ['label' => 'Đã giao', 'class' => 'badge bg-success'],
                ORDER_CANCELLED => ['label' => 'Đã hủy', 'class' => 'badge bg-danger']
            ],
            'payment' => [
                PAYMENT_PENDING => ['label' => 'Chờ thanh toán', 'class' => 'badge bg-warning'],
                PAYMENT_PAID => ['label' => 'Đã thanh toán', 'class' => 'badge bg-success'],
                PAYMENT_FAILED => ['label' => 'Thanh toán thất bại', 'class' => 'badge bg-danger']
            ],
            'product' => [
                PRODUCT_ACTIVE => ['label' => 'Còn hàng', 'class' => 'badge bg-success'],
                PRODUCT_INACTIVE => ['label' => 'Ngừng kinh doanh', 'class' => 'badge bg-secondary'],
                PRODUCT_OUT_OF_STOCK => ['label' => 'Hết hàng', 'class' => 'badge bg-danger']
            ],
            'review' => [
                REVIEW_PENDING => ['label' => 'Chờ duyệt', 'class' => 'badge bg-warning'],
                REVIEW_APPROVED => ['label' => 'Đã duyệt', 'class' => 'badge bg-success'],
                REVIEW_REJECTED => ['label' => 'Từ chối', 'class' => 'badge bg-danger']
            ]
        ];
        
        return $labels[$type][$status] ?? ['label' => 'Không xác định', 'class' => 'badge bg-secondary'];
    }
    
    // Get featured products
    public function getFeaturedProducts($limit = 6) {
        return $this->db->select(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.status = ? AND p.featured = 1 
             ORDER BY RAND() 
             LIMIT ?",
            [PRODUCT_ACTIVE, $limit]
        );
    }
    
    // Get new products
    public function getNewProducts($limit = 8) {
        return $this->db->select(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.status = ? 
             ORDER BY p.created_at DESC 
             LIMIT ?",
            [PRODUCT_ACTIVE, $limit]
        );
    }
    
    // Get categories with product count
    public function getCategoriesWithCount() {
        return $this->db->select(
            "SELECT c.*, COUNT(p.id) as product_count 
             FROM categories c 
             LEFT JOIN products p ON c.id = p.category_id AND p.status = ? 
             WHERE c.status = ? 
             GROUP BY c.id 
             ORDER BY c.name",
            [PRODUCT_ACTIVE, 'active']
        );
    }
    
    // Search products
    public function searchProducts($keyword, $categoryId = null, $page = 1, $limit = ITEMS_PER_PAGE) {
        $where = "p.status = ?";
        $params = [PRODUCT_ACTIVE];
        
        if (!empty($keyword)) {
            $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$keyword}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($categoryId)) {
            $where .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        $count = $this->db->selectOne(
            "SELECT COUNT(*) as total FROM products p WHERE {$where}",
            $params
        )['total'];
        
        $pagination = self::paginate($count, $page, $limit);
        
        $products = $this->db->select(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE {$where} 
             ORDER BY p.created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $pagination['offset']])
        );
        
        return [
            'products' => $products,
            'pagination' => $pagination
        ];
    }
    
    // Get cart items
    public function getCartItems() {
        $db = Database::getInstance();
        
        if (self::isLoggedIn()) {
            $cartItems = $db->select(
                "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ? AND p.status = ?",
                [$_SESSION['user_id'], PRODUCT_ACTIVE]
            );
        } elseif (session_id()) { // SỬA: Kiểm tra session_id trực tiếp
            $cartItems = $db->select(
                "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.session_id = ? AND p.status = ?",
                [session_id(), PRODUCT_ACTIVE]
            );
        } else {
            $cartItems = [];
        }
        
        // Tính tổng
        $total = 0;
        $totalItems = 0;
        
        foreach ($cartItems as &$item) {
            $price = $item['sale_price'] ?: $item['price'];
            $item['total_price'] = $price * $item['quantity'];
            $item['current_price'] = $price;
            $total += $item['total_price'];
            $totalItems += $item['quantity'];
            
            // Kiểm tra số lượng tồn kho
            if ($item['quantity'] > $item['stock_quantity']) {
                $item['stock_warning'] = true;
            }
        }
        
        return [
            'items' => $cartItems,
            'total' => $total,
            'total_items' => $totalItems
        ];
    }
}

// Khởi tạo functions
$functions = new Functions();
?>