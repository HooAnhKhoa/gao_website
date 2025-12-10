<?php
require_once __DIR__ . '/../includes/init.php';

// Xử lý login TRƯỚC TIÊN
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $db = Database::getInstance();
        $user = $db->selectOne(
            "SELECT * FROM users WHERE email = ? AND status = ?",
            [$email, 'active'] // Sửa 'active' thay vì USER_ACTIVE nếu chưa có constant
        );
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect NGAY LẬP TỨC - trước khi có output
            $redirect = $_POST['redirect'] ?? '../index.php';
            header("Location: $redirect");
            exit; // QUAN TRỌNG: phải có exit
        } else {
            $error = 'Email hoặc mật khẩu không đúng';
        }
    }
}

// Sau khi xử lý POST, kiểm tra nếu đã login thì redirect
if (Functions::isLoggedIn()) {
    $redirect = $_GET['redirect'] ?? 'index.php';
    header("Location: ../$redirect");
    exit;
}

// CHỈ KHI KHÔNG redirect, mới set biến và include header
$pageTitle = 'Đăng nhập - Gạo Ngon';
$pageDescription = 'Đăng nhập vào tài khoản Gạo Ngon để mua sắm và quản lý đơn hàng.';
$showBreadcrumb = true;

$breadcrumbItems = [
    ['text' => 'Trang chủ', 'url' => '../index.php'],
    ['text' => 'Đăng nhập']
];

require_once '../includes/header.php';
?>

<!-- Login Section -->
<section class="login-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="login-card card border-0 shadow-sm">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h2 class="mb-0">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </h2>
                        <p class="mb-0 mt-2">Đăng nhập để tiếp tục mua sắm</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="loginForm">
                            <input type="hidden" name="redirect" 
                                   value="<?php echo $_GET['redirect'] ?? '../index.php'; ?>">
                            
                            <!-- Email -->
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       placeholder="Nhập email của bạn"
                                       required>
                            </div>
                            
                            <!-- Password -->
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu
                                </label>
                                <div class="password-input">
                                    <input type="password" class="form-control" 
                                           name="password" 
                                           placeholder="Nhập mật khẩu"
                                           id="password"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password" 
                                            data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="text-end mt-2">
                                    <a href="forgot-password.php" class="text-decoration-none small">
                                        Quên mật khẩu?
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Remember me -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" 
                                       name="remember" 
                                       id="remember">
                                <label class="form-check-label" for="remember">
                                    Ghi nhớ đăng nhập
                                </label>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-success btn-lg py-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                </button>
                            </div>
                            
                            <!-- Divider -->
                            <div class="divider my-4">
                                <span class="divider-text">hoặc đăng nhập với</span>
                            </div>
                            
                            <!-- Social Login -->
                            <div class="social-login mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-outline-primary w-100">
                                            <i class="fab fa-facebook-f me-2"></i>Facebook
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-outline-danger w-100">
                                            <i class="fab fa-google me-2"></i>Google
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Register Link -->
                            <div class="text-center">
                                <p class="mb-0">
                                    Chưa có tài khoản?
                                    <a href="register.php" class="text-success fw-bold text-decoration-none">
                                        Đăng ký ngay
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Benefits Card -->
                <div class="benefits-card card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-star text-success me-2"></i>Lợi ích khi đăng nhập
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Theo dõi đơn hàng dễ dàng
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Lưu địa chỉ giao hàng
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Tích điểm và nhận ưu đãi
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                Xem lịch sử mua hàng
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Login page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('title', 'Ẩn mật khẩu');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('title', 'Hiện mật khẩu');
            }
        });
    });
    
    // Form validation
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', function(e) {
        const email = this.querySelector('input[name="email"]').value.trim();
        const password = this.querySelector('input[name="password"]').value.trim();
        
        if (!email || !password) {
            e.preventDefault();
            showNotification('Vui lòng nhập đầy đủ thông tin!', 'error');
        }
    });
});
</script>

<style>
/* Login page styles */
.login-card {
    border-radius: 15px;
    overflow: hidden;
}

.password-input {
    position: relative;
}

.password-input .form-control {
    padding-right: 45px;
}

.password-input .toggle-password {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    border: none;
    background: transparent;
    padding: 0 15px;
    color: #6c757d;
}

.password-input .toggle-password:hover {
    color: var(--success-color);
}

.divider {
    position: relative;
    text-align: center;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background-color: #dee2e6;
}

.divider-text {
    position: relative;
    background-color: white;
    padding: 0 15px;
    color: #6c757d;
    font-size: 0.875rem;
}

.social-login .btn {
    padding: 10px;
    font-weight: 500;
}

.benefits-card {
    border-left: 4px solid var(--success-color);
}

.benefits-card li {
    padding: 5px 0;
}
</style>

<?php
require_once '../includes/footer.php';
?>