<style>
    /* === CSS TÍCH HỢP CHO SIDEBAR === */
    .bg-gradient-success {
        background-color: #198754;
        background-image: linear-gradient(180deg, #198754 10%, #157347 100%);
        background-size: cover;
    }
    
    .sidebar {
        width: 6.5rem;
        min-height: 100vh;
        transition: all .3s;
    }
    
    .sidebar .nav-item {
        position: relative;
    }
    
    .sidebar .nav-item .nav-link {
        display: block;
        width: 100%;
        text-align: left;
        padding: 1rem;
        width: 14rem;
        color: rgba(255, 255, 255, .8);
        text-decoration: none;
    }
    
    .sidebar .nav-item .nav-link:hover {
        color: #fff;
    }
    
    .sidebar .nav-item .nav-link i {
        font-size: .85rem;
        margin-right: .25rem;
        width: 1.5rem;
        text-align: center;
    }
    
    .sidebar .nav-item.active .nav-link {
        font-weight: 700;
        color: #fff;
    }
    
    .sidebar-brand {
        height: 4.375rem;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 800;
        padding: 1.5rem 1rem;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: .05rem;
        z-index: 1;
        color: #fff;
    }
    
    .sidebar-brand:hover {
        color: #fff;
        text-decoration: none;
    }
    
    .sidebar-brand-icon i {
        font-size: 2rem;
    }
    
    .sidebar-heading {
        text-align: left;
        padding: 0 1rem;
        font-weight: 800;
        font-size: .65rem;
        color: rgba(255, 255, 255, .4);
        text-transform: uppercase;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .sidebar-divider {
        margin: 0 1rem 1rem;
        border-top: 1px solid rgba(255, 255, 255, .15);
    }
    
    /* Repsonsive Sidebar */
    @media (min-width: 768px) {
        .sidebar {
            width: 14rem !important;
        }
        .sidebar .nav-item .nav-link {
            display: block;
            width: 100%;
        }
        .sidebar .nav-item .nav-link span {
            font-size: 0.85rem;
            display: inline;
        }
    }
    
    /* Sub-menu Styles */
    .sidebar .collapse {
        position: relative;
        left: 0;
        z-index: 1;
        top: 0;
    }
    
    .sidebar .collapse-inner {
        background: #fff;
        border-radius: .35rem;
        margin: 0 1rem;
        padding: .5rem 0;
    }
    
    .sidebar .collapse-item {
        color: #3a3b45;
        display: block;
        padding: .5rem 1rem;
        text-decoration: none;
        font-size: 0.85rem;
    }
    
    .sidebar .collapse-item:hover {
        background-color: #eaecf4;
        color: #198754;
    }
    
    .sidebar .collapse-item.active {
        color: #198754;
        font-weight: 700;
        background-color: #f0f1f5;
    }
    
    .collapse-header {
        margin: 0;
        white-space: nowrap;
        padding: .5rem 1.5rem;
        text-transform: uppercase;
        font-weight: 800;
        font-size: .65rem;
        color: #b7b9cc;
    }
    
    /* Sidebar Card */
    .sidebar-card {
        background-color: rgba(255, 255, 255, .1);
        padding: 1rem;
        margin: 0 1rem 1rem;
        border-radius: .35rem;
        text-align: center;
        color: rgba(255, 255, 255, .8);
        font-size: 0.8rem;
    }
    .sidebar-card img {
        width: 100%;
        max-width: 60px;
        margin-bottom: 0.5rem;
    }
</style>

<?php
// Lấy tên trang hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
    
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo SITE_URL; ?>/admin/dashboard.php">
        <div class="sidebar-brand-icon">
            <i class="fas fa-seedling"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Gạo Ngon Admin</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Quản lý bán hàng
    </div>

    <li class="nav-item <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/orders.php">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Đơn hàng</span>
            <span class="badge bg-danger rounded-pill float-end me-2" id="pendingOrderCount">0</span>
        </a>
    </li>

    <li class="nav-item <?php echo in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php', 'categories.php', 'inventory.php']) ? 'active' : ''; ?>">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProducts" 
           aria-expanded="true" aria-controls="collapseProducts">
            <i class="fas fa-fw fa-box"></i>
            <span>Sản phẩm</span>
        </a>
        <div id="collapseProducts" class="collapse <?php echo in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php', 'categories.php', 'inventory.php']) ? 'show' : ''; ?>" 
             aria-labelledby="headingProducts" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quản lý sản phẩm:</h6>
                <a class="collapse-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/admin/products.php">Tất cả sản phẩm</a>
                <a class="collapse-item <?php echo $current_page == 'product-add.php' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/admin/product-add.php">Thêm sản phẩm</a>
                <a class="collapse-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>" 
                   href="<?php echo SITE_URL; ?>/admin/categories.php">Danh mục</a>
            </div>
        </div>
    </li>

    <li class="nav-item <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/customers.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Khách hàng</span>
        </a>
    </li>

    <li class="nav-item <?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/reviews.php">
            <i class="fas fa-fw fa-star"></i>
            <span>Đánh giá</span>
            <span class="badge bg-warning rounded-pill float-end me-2" id="pendingReviewCount">0</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    

    <hr class="sidebar-divider d-none d-md-block">

    <div class="sidebar-card d-none d-lg-flex">
        <i class="fas fa-rocket fa-2x text-white mb-2"></i>
        <p class="text-center mb-2">
            <strong>Gạo Ngon Pro</strong><br>
            Hệ thống quản lý
        </p>
        <a class="btn btn-light btn-sm text-success fw-bold" href="<?php echo SITE_URL; ?>" target="_blank">
            Xem Website
        </a>
    </div>
</ul>
<script>
// Script cập nhật số lượng thông báo (Đơn hàng/Đánh giá)
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra SITE_URL để tránh lỗi JS
    if (typeof SITE_URL !== 'undefined') {
        fetch(SITE_URL + '/api/admin/notification-counts.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const pendingOrderCount = document.getElementById('pendingOrderCount');
                    const pendingReviewCount = document.getElementById('pendingReviewCount');
                    
                    // Cập nhật số đơn hàng chờ
                    if (pendingOrderCount) {
                        pendingOrderCount.textContent = data.pending_orders;
                        pendingOrderCount.style.display = data.pending_orders > 0 ? 'inline-block' : 'none';
                    }
                    
                    // Cập nhật số đánh giá chờ
                    if (pendingReviewCount) {
                        pendingReviewCount.textContent = data.pending_reviews;
                        pendingReviewCount.style.display = data.pending_reviews > 0 ? 'inline-block' : 'none';
                    }
                }
            })
            .catch(error => {
                // Fail silently để không làm phiền admin
                console.log('Notification update skipped'); 
            });
    }
});
</script>