<?php
session_start();
require_once '../includes/functions.php';

// Check admin authentication
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Dashboard - Quản trị Gạo Ngon';
require_once 'includes/header.php';

$db = Database::getInstance();
$functions = new Functions();

// Get statistics
$today = date('Y-m-d');
$firstDayMonth = date('Y-m-01');
$lastDayMonth = date('Y-m-t');

$stats = $db->selectOne("
    SELECT 
        (SELECT COUNT(*) FROM products) as total_products,
        (SELECT COUNT(*) FROM products WHERE status = 'active') as active_products,
        (SELECT COUNT(*) FROM products WHERE stock_quantity <= 10) as low_stock,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?) as today_orders,
        (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') as pending_orders,
        (SELECT COUNT(*) FROM users WHERE role = 'user') as total_customers,
        (SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?) as today_customers,
        (SELECT SUM(final_amount) FROM orders WHERE order_status = 'delivered') as total_revenue,
        (SELECT SUM(final_amount) FROM orders WHERE DATE(created_at) = ?) as today_revenue,
        (SELECT SUM(final_amount) FROM orders WHERE created_at BETWEEN ? AND ?) as month_revenue
", [$today, $today, $today, $firstDayMonth, $lastDayMonth]);

// Get recent orders
$recent_orders = $db->select("
    SELECT o.*, u.full_name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Get low stock products
$low_stock = $db->select("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.stock_quantity <= 10 AND p.status = 'active' 
    ORDER BY p.stock_quantity ASC 
    LIMIT 5
");

// Get recent reviews
$recent_reviews = $db->select("
    SELECT r.*, u.full_name, p.name as product_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN products p ON r.product_id = p.id 
    WHERE r.status = 'pending' 
    ORDER BY r.created_at DESC 
    LIMIT 5
");

// Get monthly revenue data for chart
$monthly_revenue = $db->select("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(final_amount) as revenue,
        COUNT(*) as orders
    FROM orders 
    WHERE order_status = 'delivered' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
?>

<!-- Dashboard Content -->
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <h1 class="h2 fw-bold text-success mb-3">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Tổng quan</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Doanh thu (Tháng)
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php echo $functions->formatPrice($stats['month_revenue'] ?? 0); ?>
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success me-2">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <?php 
                                    $todayRevenue = $stats['today_revenue'] ?? 0;
                                    echo $functions->formatPrice($todayRevenue); 
                                    ?> hôm nay
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Tổng đơn hàng
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php echo $stats['total_orders'] ?? 0; ?>
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="<?php echo ($stats['pending_orders'] ?? 0) > 0 ? 'text-danger' : 'text-success'; ?> me-2">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo $stats['pending_orders'] ?? 0; ?> đang chờ
                                </span>
                                <span class="text-info">
                                    <i class="fas fa-sun me-1"></i>
                                    <?php echo $stats['today_orders'] ?? 0; ?> hôm nay
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Khách hàng
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php echo $stats['total_customers'] ?? 0; ?>
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success">
                                    <i class="fas fa-user-plus me-1"></i>
                                    <?php echo $stats['today_customers'] ?? 0; ?> mới hôm nay
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Sản phẩm
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php echo $stats['total_products'] ?? 0; ?>
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-success me-2">
                                    <i class="fas fa-check-circle me-1"></i>
                                    <?php echo $stats['active_products'] ?? 0; ?> đang bán
                                </span>
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <?php echo $stats['low_stock'] ?? 0; ?> sắp hết
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Revenue Chart -->
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

        <!-- Pie Chart -->
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

    <!-- Recent Orders & Low Stock -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-success">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Đơn hàng gần đây
                    </h6>
                    <a href="orders.php" class="btn btn-sm btn-outline-success">
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
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
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
                                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="orders.php?action=edit&id=<?php echo $order['id']; ?>" 
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

        <!-- Low Stock Products -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Sản phẩm sắp hết hàng
                    </h6>
                    <a href="products.php?filter=low_stock" class="btn btn-sm btn-outline-danger">
                        Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($low_stock as $product): ?>
                        <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="../../assets/images/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
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

    <!-- Pending Reviews -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-warning">
                        <i class="fas fa-star me-1"></i>
                        Đánh giá chờ duyệt
                    </h6>
                    <a href="reviews.php?status=pending" class="btn btn-sm btn-outline-warning">
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
                                            <img src="../../assets/images/avatars/<?php echo $review['avatar'] ?? 'default.jpg'; ?>" 
                                                 class="rounded-circle me-2" 
                                                 alt="<?php echo htmlspecialchars($review['full_name']); ?>"
                                                 style="width: 30px; height: 30px;">
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
                                            <a href="review-detail.php?id=<?php echo $review['id']; ?>" 
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

<!-- Chart.js -->
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
    
    // Get order status counts
    fetch('../api/admin/order-stats.php')
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
        fetch('../api/admin/reviews/update.php', {
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