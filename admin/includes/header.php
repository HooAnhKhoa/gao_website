<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load cấu hình
require_once __DIR__ . '/../../config/constants.php';

// Check Auth
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$adminName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin' : 'Quản trị Gạo Ngon'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>const SITE_URL = "<?php echo SITE_URL; ?>";</script>

    <style>
        :root {
            --primary: #198754; /* Màu xanh Gạo Ngon */
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fc;
            color: #5a5c69;
            overflow-x: hidden;
        }

        /* Layout Structure */
        #wrapper { display: flex; }
        #content-wrapper {
            background-color: #f8f9fc;
            width: 100%;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        #content { flex: 1 0 auto; }

        /* Topbar */
        .topbar {
            height: 4.375rem;
            margin-bottom: 1.5rem !important;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important;
            z-index: 10; 
        }
        .topbar .nav-item .nav-link {
            height: 4.375rem;
            display: flex;
            align-items: center;
            padding: 0 .75rem;
            color: #d1d3e2;
        }
        .topbar .nav-item .nav-link:hover { color: #b7b9cc; }
        .topbar .nav-item .nav-link .img-profile {
            height: 2rem;
            width: 2rem;
        }
        
        /* Cards */
        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid #e3e6f0;
            border-radius: .35rem;
        }
        .card-header {
            padding: .75rem 1.25rem;
            margin-bottom: 0;
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .border-left-primary { border-left: .25rem solid #4e73df!important; }
        .border-left-success { border-left: .25rem solid #1cc88a!important; }
        .border-left-info { border-left: .25rem solid #36b9cc!important; }
        .border-left-warning { border-left: .25rem solid #f6c23e!important; }
        
        .text-gray-300 { color: #dddfeb!important; }
        .text-gray-400 { color: #d1d3e2!important; }
        .text-gray-500 { color: #b7b9cc!important; }
        .text-gray-600 { color: #858796!important; }
        .text-gray-800 { color: #5a5c69!important; }
        
        .shadow { box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important; }
        
        /* Utilities */
        .badge-counter {
            position: absolute;
            transform: scale(.7);
            transform-origin: top right;
            right: .25rem;
            margin-top: -.25rem;
        }
        
        /* Tables */
        table.dataTable { width: 100% !important; }
        
        /* Footer */
        .sticky-footer {
            padding: 2rem 0;
            flex-shrink: 0;
            background-color: #fff !important;
        }
    </style>

    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3 text-success">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small me-2">
                                    <?php echo htmlspecialchars($adminName); ?>
                                </span>
                                <img class="img-profile rounded-circle"
                                     src="<?php echo SITE_URL; ?>/assets/images/avatars/admin.jpg" 
                                     onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=198754&color=fff'">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in">
                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Hồ sơ
                                </a>
                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>/index.php" target="_blank">
                                    <i class="fas fa-store fa-sm fa-fw mr-2 text-gray-400"></i> Xem website
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Đăng xuất
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid">