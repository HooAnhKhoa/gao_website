<?php
// Xác định đường dẫn assets dựa trên vị trí file hiện tại
$current_dir = dirname($_SERVER['SCRIPT_NAME']);
$is_in_pages = strpos($current_dir, '/pages') !== false;
$is_in_admin = strpos($current_dir, '/admin') !== false;

// Xác định base path cho assets
if ($is_in_pages || $is_in_admin) {
    $assets_base = '../assets/';
    $site_base = '../';
} else {
    $assets_base = 'assets/';
    $site_base = './';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $assets_base; ?>css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $assets_base; ?>images/favicon.ico">
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Cửa hàng gạo chất lượng, uy tín, giá tốt nhất thị trường. Gạo ST25, gạo nếp, gạo lứt, gạo đặc sản các vùng miền.'; ?>">
    <meta name="keywords" content="gạo, gạo ST25, gạo nếp, gạo lứt, gạo đặc sản, mua gạo online, gạo chất lượng">
    <meta name="author" content="Gạo Ngon">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle : SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Cửa hàng gạo chất lượng, uy tín, giá tốt nhất thị trường'; ?>">
    <meta property="og:image" content="<?php echo $assets_base; ?>images/logo.png">
    <meta property="og:url" content="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="website">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Preload important resources -->
    <link rel="preload" href="<?php echo $assets_base; ?>css/style.css" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" as="style">
    
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar bg-dark text-white py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="top-bar-left">
                        <i class="fas fa-phone-alt me-2"></i>
                        <span>Hotline: <a href="tel:19001000" class="text-white text-decoration-none">1900 1000</a></span>
                        <span class="ms-3">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:<?php echo ADMIN_EMAIL; ?>" class="text-white text-decoration-none"><?php echo ADMIN_EMAIL; ?></a>
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="top-bar-right text-md-end">
                        <?php if (Functions::isLoggedIn()): ?>
                            <?php $currentUser = Functions::getCurrentUser(); ?>
                            <span class="me-3">
                                <i class="fas fa-user me-1"></i>
                                Xin chào, <a href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>profile.php" class="text-white text-decoration-none"><?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['username']); ?></a>
                            </span>
                            <?php if (Functions::isAdmin()): ?>
                                <a href="<?php echo ($is_in_admin) ? './' : (($is_in_pages) ? '../admin/' : 'admin/'); ?>dashboard.php" class="text-white text-decoration-none me-3">
                                    <i class="fas fa-cog me-1"></i>Quản trị
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo ($is_in_pages || $is_in_admin) ? '../api/' : 'api/'; ?>auth/logout.php" class="text-white text-decoration-none">
                                <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất
                            </a>
                        <?php else: ?>
                            <a href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>login.php" class="text-white text-decoration-none me-3">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                            <a href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>register.php" class="text-white text-decoration-none">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header py-3 shadow-sm">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="logo">
                        <a href="<?php echo $site_base; ?>index.php" class="text-decoration-none">
                            <h1 class="h3 mb-0 text-success">
                                <i class="fas fa-seedling text-warning"></i>
                                Gạo Ngon
                            </h1>
                            <small class="text-muted">Hương vị quê hương</small>
                        </a>
                    </div>
                </div>

                <!-- Search -->
                <div class="col-lg-5 col-md-8 col-6 order-md-2 order-lg-1">
                    <form class="search-form" action="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>products.php" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Tìm kiếm sản phẩm..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Cart & Actions -->
                <div class="col-lg-4 col-md-12 order-md-1 order-lg-2 mt-3 mt-lg-0">
                    <div class="header-actions d-flex justify-content-end align-items-center">
                        <!-- Wishlist  -->
                        
                        <!-- Cart -->
                        <a href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>cart.php" class="btn btn-outline-primary position-relative me-3">
                            <i class="fas fa-shopping-cart"></i>
                            <?php $cartCount = Functions::getCartCount(); ?>
                            <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                <?php echo $cartCount; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Mobile Menu Toggle -->
                        <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo $site_base; ?>index.php">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    
                    <?php
                    // Lấy danh mục cho menu
                    $db = Database::getInstance();
                    $categories = $db->select(
                        "SELECT * FROM categories WHERE status = 'active' AND parent_id IS NULL LIMIT 6"
                    );
                    
                    foreach ($categories as $category):
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="category<?php echo $category['id']; ?>" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="category<?php echo $category['id']; ?>">
                            <?php
                            $subCategories = $db->select(
                                "SELECT * FROM categories WHERE parent_id = ? AND status = 'active'",
                                [$category['id']]
                            );
                            
                            if ($subCategories):
                                foreach ($subCategories as $sub):
                            ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>products.php?category=<?php echo $sub['id']; ?>">
                                        <?php echo htmlspecialchars($sub['name']); ?>
                                    </a>
                                </li>
                            <?php
                                endforeach;
                            endif;
                            ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-success" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>products.php?category=<?php echo $category['id']; ?>">
                                    <i class="fas fa-arrow-right me-1"></i>Xem tất cả
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endforeach; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>products.php">
                            <i class="fas fa-store me-1"></i>Tất cả sản phẩm
                        </a>
                    </li>
                    
                   
                </ul>
                
                <!-- Promo Banner -->
                <div class="promo-banner d-none d-lg-block">
                    <span class="badge bg-danger">Miễn phí vận chuyển đơn từ 500K</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu (Offcanvas) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Gạo Ngon</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo $site_base; ?>index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                </li>
                
                <?php foreach ($categories as $category): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>products.php?category=<?php echo $category['id']; ?>">
                        <i class="fas fa-chevron-right me-2"></i><?php echo htmlspecialchars($category['name']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>products.php">
                        <i class="fas fa-store me-2"></i>Tất cả sản phẩm
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>cart.php">
                        <i class="fas fa-shopping-cart me-2"></i>Giỏ hàng
                        <?php $mobileCartCount = Functions::getCartCount(); ?>
                        <?php if ($mobileCartCount > 0): ?>
                        <span class="badge bg-danger float-end cart-count"><?php echo $mobileCartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <?php if (Functions::isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>profile.php">
                            <i class="fas fa-user me-2"></i>Tài khoản
                        </a>
                    </li>
                    <?php if (Functions::isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-success" href="<?php echo ($is_in_admin) ? './' : (($is_in_pages) ? '../admin/' : 'admin/'); ?>dashboard.php">
                            <i class="fas fa-cog me-2"></i>Quản trị
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?php echo ($is_in_pages || $is_in_admin) ? '../api/' : 'api/'; ?>auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>login.php">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($is_in_pages || $is_in_admin) ? './' : 'pages/'; ?>register.php">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Breadcrumb (optional) -->
    <?php if (isset($showBreadcrumb) && $showBreadcrumb): ?>
    <div class="breadcrumb-container py-3 bg-light">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo $site_base; ?>index.php"><i class="fas fa-home"></i></a></li>
                    <?php if (isset($breadcrumbItems)): ?>
                        <?php foreach ($breadcrumbItems as $item): ?>
                            <?php if (isset($item['url'])): ?>
                                <li class="breadcrumb-item"><a href="<?php echo $item['url']; ?>"><?php echo htmlspecialchars($item['text']); ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($item['text']); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
    </div>
    <?php endif; ?>

    <!-- Flash Messages -->
    <div class="container mt-3">
        <?php Functions::displayFlashMessage(); ?>
    </div>

    <!-- Main Content -->
    <main class="main-content">