<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy thống kê
$stats = $db->selectOne("
    SELECT 
        (SELECT COUNT(*) FROM products) as total_products,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT COUNT(*) FROM users WHERE role = 'user') as total_customers,
        (SELECT SUM(final_amount) FROM orders WHERE order_status = 'delivered') as total_revenue
");

// Đơn hàng mới
$recent_orders = $db->select("
    SELECT o.*, u.full_name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Sản phẩm sắp hết hàng
$low_stock = $db->select("
    SELECT * FROM products 
    WHERE stock_quantity <= 10 AND status = 'active' 
    ORDER BY stock_quantity ASC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="content">
                <h1>Dashboard</h1>
                
                <!-- Thống kê -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_products']; ?></h3>
                            <p>Sản phẩm</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_orders']; ?></h3>
                            <p>Đơn hàng</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_customers']; ?></h3>
                            <p>Khách hàng</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_revenue']); ?>đ</h3>
                            <p>Doanh thu</p>
                        </div>
                    </div>
                </div>
                
                <!-- Đơn hàng gần đây -->
                <div class="section">
                    <h2>Đơn hàng gần đây</h2>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_code']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo number_format($order['final_amount']); ?>đ</td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => 'Chờ xử lý',
                                                'processing' => 'Đang xử lý',
                                                'shipped' => 'Đang giao',
                                                'delivered' => 'Đã giao',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                            echo $status_labels[$order['order_status']];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn-sm">Xem</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Sản phẩm sắp hết hàng -->
                <div class="section">
                    <h2>Sản phẩm sắp hết hàng</h2>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock as $product): ?>
                                <tr>
                                    <td>
                                        <img src="../assets/images/products/<?php echo $product['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-thumb">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>
                                        <span class="stock-warning"><?php echo $product['stock_quantity']; ?></span>
                                    </td>
                                    <td><?php echo number_format($product['price']); ?>đ</td>
                                    <td>
                                        <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn-sm">Cập nhật</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>
