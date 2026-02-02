<?php
// Load init trước để có kết nối DB, Functions và hằng số SITE_URL
require_once '../includes/init.php';

// Kiểm tra quyền Admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$pageTitle = 'Dashboard - Quản trị Gạo Ngon';
require_once 'includes/header.php';

$db = Database::getInstance();
$functions = new Functions();

// Get statistics
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$firstDayMonth = date('Y-m-01');
$lastDayMonth = date('Y-m-t');
$firstDayLastMonth = date('Y-m-01', strtotime('-1 month'));
$lastDayLastMonth = date('Y-m-t', strtotime('-1 month'));

$stats = $db->selectOne("
    SELECT 
        (SELECT COUNT(*) FROM products) as total_products,
        (SELECT COUNT(*) FROM products WHERE status = 'active') as active_products,
        (SELECT COUNT(*) FROM products WHERE stock_quantity <= 10) as low_stock,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?) as today_orders,
        (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?) as yesterday_orders,
        (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') as pending_orders,
        (SELECT COUNT(*) FROM users WHERE role = 'user') as total_customers,
        (SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?) as today_customers,
        (SELECT SUM(final_amount) FROM orders WHERE order_status IN ('delivered', 'processing', 'shipped')) as total_revenue,
        (SELECT SUM(final_amount) FROM orders WHERE DATE(created_at) = ?) as today_revenue,
        (SELECT SUM(final_amount) FROM orders WHERE DATE(created_at) = ?) as yesterday_revenue,
        (SELECT SUM(final_amount) FROM orders WHERE created_at BETWEEN ? AND ?) as month_revenue,
        (SELECT SUM(final_amount) FROM orders WHERE created_at BETWEEN ? AND ?) as last_month_revenue,
        (SELECT COUNT(*) FROM reviews WHERE status = 'pending') as pending_reviews
", [$today, $yesterday, $today, $today, $yesterday, $firstDayMonth, $lastDayMonth, $firstDayLastMonth, $lastDayLastMonth]);

// Calculate growth percentages
$todayOrdersGrowth = $stats['yesterday_orders'] > 0 ? 
    round((($stats['today_orders'] - $stats['yesterday_orders']) / $stats['yesterday_orders']) * 100, 1) : 0;

$todayRevenueGrowth = $stats['yesterday_revenue'] > 0 ? 
    round((($stats['today_revenue'] - $stats['yesterday_revenue']) / $stats['yesterday_revenue']) * 100, 1) : 0;

$monthRevenueGrowth = $stats['last_month_revenue'] > 0 ? 
    round((($stats['month_revenue'] - $stats['last_month_revenue']) / $stats['last_month_revenue']) * 100, 1) : 0;

// Get recent orders
$recent_orders = $db->select("
    SELECT o.*, u.full_name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 8
");

// Get low stock products
$low_stock = $db->select("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.stock_quantity <= 10 AND p.status = 'active' 
    ORDER BY p.stock_quantity ASC 
    LIMIT 6
");

// Get top selling products this month
$top_products = $db->select("
    SELECT p.name, p.image, SUM(oi.quantity) as total_sold, SUM(oi.total_price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at BETWEEN ? AND ? AND o.order_status IN ('delivered', 'processing', 'shipped')
    GROUP BY p.id, p.name, p.image
    ORDER BY total_sold DESC
    LIMIT 5
", [$firstDayMonth, $lastDayMonth]);

// Get recent activities
$recent_activities = $db->select("
    SELECT 'order' as type, order_code as title, customer_name as description, created_at, 'success' as status
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    UNION ALL
    SELECT 'user' as type, CONCAT('Khách hàng mới: ', full_name) as title, email as description, created_at, 'info' as status
    FROM users 
    WHERE role = 'user' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    UNION ALL
    SELECT 'review' as type, CONCAT('Đánh giá mới') as title, CONCAT('Sản phẩm ID: ', product_id) as description, created_at, 'warning' as status
    FROM reviews 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ORDER BY created_at DESC
    LIMIT 10
");

// Get monthly revenue data for chart (last 12 months)
$monthly_revenue = $db->select("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(final_amount) as revenue,
        COUNT(*) as orders
    FROM orders 
    WHERE order_status IN ('delivered', 'processing', 'shipped')
    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");

// Get order status distribution
$order_status_stats = $db->select("
    SELECT 
        order_status,
        COUNT(*) as count,
        SUM(final_amount) as total_amount
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY order_status
    ORDER BY count DESC
");
?>
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800 fw-bold">
                <i class="fas fa-tachometer-alt text-success me-2"></i>Dashboard
            </h1>
            <p class="mb-0 text-muted">Chào mừng trở lại! Đây là tổng quan về cửa hàng của bạn.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-1"></i>Làm mới
            </button>
            <div class="dropdown">
                <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i>Xuất báo cáo
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Xuất Excel</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>Xuất PDF</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-3 p-3">
                                <i class="fas fa-dollar-sign fa-lg text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small text-uppercase fw-bold">Doanh thu tháng</div>
                            <div class="h4 mb-1 fw-bold text-dark">
                                <?php echo Functions::formatPrice($stats['month_revenue'] ?? 0); ?>
                            </div>
                            <div class="d-flex align-items-center">
                                <?php if ($monthRevenueGrowth >= 0): ?>
                                <span class="badge bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-arrow-up me-1"></i><?php echo abs($monthRevenueGrowth); ?>%
                                </span>
                                <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-arrow-down me-1"></i><?php echo abs($monthRevenueGrowth); ?>%
                                </span>
                                <?php endif; ?>
                                <small class="text-muted">so với tháng trước</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-3 p-3">
                                <i class="fas fa-shopping-cart fa-lg text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small text-uppercase fw-bold">Đơn hàng hôm nay</div>
                            <div class="h4 mb-1 fw-bold text-dark">
                                <?php echo $stats['today_orders'] ?? 0; ?>
                            </div>
                            <div class="d-flex align-items-center">
                                <?php if ($todayOrdersGrowth >= 0): ?>
                                <span class="badge bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-arrow-up me-1"></i><?php echo abs($todayOrdersGrowth); ?>%
                                </span>
                                <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-arrow-down me-1"></i><?php echo abs($todayOrdersGrowth); ?>%
                                </span>
                                <?php endif; ?>
                                <small class="text-muted">so với hôm qua</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-3 p-3">
                                <i class="fas fa-users fa-lg text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small text-uppercase fw-bold">Tổng khách hàng</div>
                            <div class="h4 mb-1 fw-bold text-dark">
                                <?php echo $stats['total_customers'] ?? 0; ?>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info bg-opacity-10 text-info me-2">
                                    <i class="fas fa-user-plus me-1"></i><?php echo $stats['today_customers'] ?? 0; ?>
                                </span>
                                <small class="text-muted">mới hôm nay</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-3 p-3">
                                <i class="fas fa-boxes fa-lg text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small text-uppercase fw-bold">Sản phẩm</div>
                            <div class="h4 mb-1 fw-bold text-dark">
                                <?php echo $stats['active_products'] ?? 0; ?>/<?php echo $stats['total_products'] ?? 0; ?>
                            </div>
                            <div class="d-flex align-items-center">
                                <?php if (($stats['low_stock'] ?? 0) > 0): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i><?php echo $stats['low_stock']; ?>
                                </span>
                                <small class="text-muted">sắp hết hàng</small>
                                <?php else: ?>
                                <span class="badge bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-check me-1"></i>Đủ hàng
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title fw-bold mb-3">
                        <i class="fas fa-bolt text-warning me-2"></i>Thao tác nhanh
                    </h6>
                    <div class="row g-2">
                        <div class="col-md-2 col-6">
                            <a href="<?php echo SITE_URL; ?>/admin/product-add.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-plus-circle mb-1"></i><br>
                                <small>Thêm sản phẩm</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="<?php echo SITE_URL; ?>/admin/orders.php?status=pending" class="btn btn-outline-warning w-100">
                                <i class="fas fa-clock mb-1"></i><br>
                                <small>Đơn chờ duyệt</small>
                                <?php if (($stats['pending_orders'] ?? 0) > 0): ?>
                                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                                    <?php echo $stats['pending_orders']; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="<?php echo SITE_URL; ?>/admin/products.php?filter=low_stock" class="btn btn-outline-danger w-100">
                                <i class="fas fa-exclamation-triangle mb-1"></i><br>
                                <small>Hàng sắp hết</small>
                                <?php if (($stats['low_stock'] ?? 0) > 0): ?>
                                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                                    <?php echo $stats['low_stock']; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="<?php echo SITE_URL; ?>/admin/reviews.php?status=pending" class="btn btn-outline-info w-100">
                                <i class="fas fa-star mb-1"></i><br>
                                <small>Đánh giá mới</small>
                                <?php if (($stats['pending_reviews'] ?? 0) > 0): ?>
                                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                                    <?php echo $stats['pending_reviews']; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="<?php echo SITE_URL; ?>/admin/customers.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-users mb-1"></i><br>
                                <small>Khách hàng</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-tags mb-1"></i><br>
                                <small>Danh mục</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-success">
                        <i class="fas fa-chart-line me-1"></i>
                        Doanh thu 6 tháng gần nhất
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                            aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="#">Xuất báo cáo</a></li>
                            <li><a class="dropdown-item" href="#">Xem chi tiết</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-success">
                        <i class="fas fa-chart-pie me-1"></i>
                        Trạng thái đơn hàng
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="orderStatusChart" height="200"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="me-3">
                            <i class="fas fa-circle text-primary"></i> Đang chờ
                        </span>
                        <span class="me-3">
                            <i class="fas fa-circle text-success"></i> Đã giao
                        </span>
                        <span>
                            <i class="fas fa-circle text-info"></i> Đang giao
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-success">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Đơn hàng gần đây
                    </h6>
                    <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-sm btn-outline-success">
                        Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>/admin/order-detail.php?id=<?php echo $order['id']; ?>" 
                                           class="text-decoration-none fw-bold">
                                            <?php echo $order['order_code']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                    <td class="fw-bold text-success">
                                        <?php echo $functions->formatPrice($order['final_amount']); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusInfo = Functions::getStatusLabel($order['order_status'], 'order');
                                        ?>
                                        <span class="badge <?php echo $statusInfo['class']; ?>">
                                            <?php echo $statusInfo['label']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo SITE_URL; ?>/admin/order-detail.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>/admin/orders.php?action=edit&id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Cập nhật">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Sản phẩm sắp hết hàng
                    </h6>
                    <a href="<?php echo SITE_URL; ?>/admin/products.php?filter=low_stock" class="btn btn-sm btn-outline-danger">
                        Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($low_stock as $product): ?>
                        <a href="<?php echo SITE_URL; ?>/admin/products.php?action=edit&id=<?php echo $product['id']; ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                                         class="rounded me-3" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <small class="text-muted"><?php echo $product['category_name']; ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger">
                                        <?php echo $product['stock_quantity']; ?> cái
                                    </span>
                                    <div class="text-success fw-bold mt-1">
                                        <?php echo $functions->formatPrice($product['price']); ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        
                        <?php if (empty($low_stock)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                            <p class="mb-0">Tất cả sản phẩm đều có đủ hàng</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-warning">
                        <i class="fas fa-star me-1"></i>
                        Đánh giá chờ duyệt
                    </h6>
                    <a href="<?php echo SITE_URL; ?>/admin/reviews.php?status=pending" class="btn btn-sm btn-outline-warning">
                        Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_reviews)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Người đánh giá</th>
                                    <th>Sản phẩm</th>
                                    <th>Đánh giá</th>
                                    <th>Ngày đăng</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_reviews as $review): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo SITE_URL; ?>/assets/images/avatars/<?php echo $review['avatar'] ?? 'default.jpg'; ?>" 
                                                 class="rounded-circle me-2" 
                                                 alt="<?php echo htmlspecialchars($review['full_name']); ?>"
                                                 style="width: 30px; height: 30px;"
                                                 onerror="this.src='https://ui-avatars.com/api/?name=User'">
                                            <?php echo htmlspecialchars($review['full_name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                                    <td>
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-warning"></i>
                                            <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <?php echo substr($review['comment'] ?? '', 0, 50); ?>...
                                        </small>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-success approve-review" 
                                                    data-id="<?php echo $review['id']; ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger reject-review" 
                                                    data-id="<?php echo $review['id']; ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <a href="<?php echo SITE_URL; ?>/admin/review-detail.php?id=<?php echo $review['id']; ?>" 
                                               class="btn btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <p class="mb-0">Không có đánh giá nào chờ duyệt</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    
    // Prepare data for chart
    const monthlyData = <?php echo json_encode($monthly_revenue); ?>;
    const months = monthlyData.map(item => {
        const [year, month] = item.month.split('-');
        return `${month}/${year}`;
    });
    const revenues = monthlyData.map(item => item.revenue);
    const orders = monthlyData.map(item => item.orders);
    
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Doanh thu',
                data: revenues,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }, {
                label: 'Số đơn hàng',
                data: orders,
                borderColor: '#0dcaf0',
                backgroundColor: 'rgba(13, 202, 240, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Doanh thu (VNĐ)'
                    },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Số đơn hàng'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label === 'Doanh thu') {
                                label += ': ' + new Intl.NumberFormat('vi-VN').format(context.raw) + 'đ';
                            } else {
                                label += ': ' + context.raw + ' đơn';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    
    // SỬA: Đường dẫn API tuyệt đối
    fetch(SITE_URL + '/api/admin/order-stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.data,
                            backgroundColor: [
                                '#0d6efd', // Pending
                                '#198754', // Delivered
                                '#0dcaf0', // Processing
                                '#ffc107', // Shipped
                                '#dc3545'  // Cancelled
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} đơn (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    
    // Approve review
    document.querySelectorAll('.approve-review').forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.dataset.id;
            if (confirm('Bạn có chắc muốn duyệt đánh giá này?')) {
                updateReviewStatus(reviewId, 'approved');
            }
        });
    });
    
    // Reject review
    document.querySelectorAll('.reject-review').forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.dataset.id;
            if (confirm('Bạn có chắc muốn từ chối đánh giá này?')) {
                updateReviewStatus(reviewId, 'rejected');
            }
        });
    });
    
    function updateReviewStatus(reviewId, status) {
        // SỬA: Đường dẫn API tuyệt đối
        fetch(SITE_URL + '/api/admin/reviews/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                review_id: reviewId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Lỗi kết nối!', 'error');
        });
    }
    
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>