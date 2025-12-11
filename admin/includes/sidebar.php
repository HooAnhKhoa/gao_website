<?php
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <i class="fas fa-seedling"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Gạo Ngon Admin</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Quản lý bán hàng
    </div>

    <!-- Nav Item - Orders -->
    <li class="nav-item <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="orders.php">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Đơn hàng</span>
            <span class="badge bg-danger rounded-pill float-end" id="pendingOrderCount">0</span>
        </a>
    </li>

    <!-- Nav Item - Products -->
    <li class="nav-item <?php echo in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : ''; ?>">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProducts" 
           aria-expanded="true" aria-controls="collapseProducts">
            <i class="fas fa-fw fa-box"></i>
            <span>Sản phẩm</span>
        </a>
        <div id="collapseProducts" class="collapse <?php echo in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php']) ? 'show' : ''; ?>" 
             aria-labelledby="headingProducts" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quản lý sản phẩm:</h6>
                <a class="collapse-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" 
                   href="products.php">Tất cả sản phẩm</a>
                <a class="collapse-item <?php echo $current_page == 'product-add.php' ? 'active' : ''; ?>" 
                   href="product-add.php">Thêm sản phẩm</a>
                <a class="collapse-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>" 
                   href="categories.php">Danh mục</a>
                <a class="collapse-item <?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>" 
                   href="inventory.php">Tồn kho</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Customers -->
    <li class="nav-item <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="customers.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Khách hàng</span>
        </a>
    </li>

    <!-- Nav Item - Reviews -->
    <li class="nav-item <?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="reviews.php">
            <i class="fas fa-fw fa-star"></i>
            <span>Đánh giá</span>
            <span class="badge bg-warning rounded-pill float-end" id="pendingReviewCount">0</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Quản lý nội dung
    </div>

    <!-- Nav Item - Pages -->
    <li class="nav-item <?php echo $current_page == 'pages.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="pages.php">
            <i class="fas fa-fw fa-file"></i>
            <span>Trang tĩnh</span>
        </a>
    </li>

    <!-- Nav Item - Blog -->
    <li class="nav-item <?php echo in_array($current_page, ['blog.php', 'post-add.php', 'post-edit.php']) ? 'active' : ''; ?>">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseBlog" 
           aria-expanded="true" aria-controls="collapseBlog">
            <i class="fas fa-fw fa-newspaper"></i>
            <span>Blog</span>
        </a>
        <div id="collapseBlog" class="collapse <?php echo in_array($current_page, ['blog.php', 'post-add.php', 'post-edit.php']) ? 'show' : ''; ?>" 
             aria-labelledby="headingBlog" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quản lý blog:</h6>
                <a class="collapse-item <?php echo $current_page == 'blog.php' ? 'active' : ''; ?>" 
                   href="blog.php">Bài viết</a>
                <a class="collapse-item" href="blog-categories.php">Chuyên mục</a>
                <a class="collapse-item" href="blog-comments.php">Bình luận</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Banner -->
    <li class="nav-item <?php echo $current_page == 'banners.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="banners.php">
            <i class="fas fa-fw fa-image"></i>
            <span>Banner quảng cáo</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Thiết lập hệ thống
    </div>

    <!-- Nav Item - Settings -->
    <li class="nav-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="settings.php">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Cài đặt</span>
        </a>
    </li>

    <!-- Nav Item - Users -->
    <li class="nav-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="users.php">
            <i class="fas fa-fw fa-user-cog"></i>
            <span>Quản trị viên</span>
        </a>
    </li>

    <!-- Nav Item - Backup -->
    <li class="nav-item <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="backup.php">
            <i class="fas fa-fw fa-database"></i>
            <span>Sao lưu & Phục hồi</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

    <!-- Sidebar Message -->
    <div class="sidebar-card d-none d-lg-flex">
        <img class="sidebar-card-illustration mb-2" 
             src="../../assets/images/admin-sidebar.svg" 
             alt="...">
        <p class="text-center mb-2">
            <strong>Gạo Ngon Pro</strong><br>
            Quản lý cửa hàng chuyên nghiệp
        </p>
        <a class="btn btn-success btn-sm" href="#" target="_blank">
            Nâng cấp
        </a>
    </div>
</ul>
<!-- End of Sidebar -->

<script>
// Update notification counts
document.addEventListener('DOMContentLoaded', function() {
    fetch('../api/admin/notification-counts.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pendingOrderCount = document.getElementById('pendingOrderCount');
                const pendingReviewCount = document.getElementById('pendingReviewCount');
                
                if (pendingOrderCount && data.pending_orders > 0) {
                    pendingOrderCount.textContent = data.pending_orders;
                }
                
                if (pendingReviewCount && data.pending_reviews > 0) {
                    pendingReviewCount.textContent = data.pending_reviews;
                }
            }
        })
        .catch(error => console.error('Error loading notification counts:', error));
});
</script>