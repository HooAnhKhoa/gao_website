<?php
// admin/includes/sidebar.php
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
        </a>
    </li>

    <li class="nav-item <?php echo in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/products.php">
            <i class="fas fa-fw fa-boxes"></i>
            <span>Sản phẩm</span>
        </a>
    </li>

    <li class="nav-item <?php echo in_array($current_page, ['categories.php', 'category-add.php', 'category-edit.php']) ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/categories.php">
            <i class="fas fa-fw fa-tags"></i>
            <span>Danh mục</span>
        </a>
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
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Hệ thống
    </div>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo SITE_URL; ?>/index.php" target="_blank">
            <i class="fas fa-fw fa-external-link-alt"></i>
            <span>Xem website</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>