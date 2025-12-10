<?php
// Thêm dòng này trước khi sử dụng class Functions
require_once __DIR__ . '/../includes/init.php';

// Các constant cần thiết (thêm nếu chưa có trong functions.php)
if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 'admin');
}

// Handle registration TRƯỚC KHI có output
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);
    $role = 'user'; // Default role for new users
    $status = 'active'; // Default status for new users
    
    // Validate
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error = 'Số điện thoại không hợp lệ';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif (!$agree_terms) {
        $error = 'Vui lòng đồng ý với điều khoản dịch vụ';
    } else {
        $db = Database::getInstance();
        
        // Check if email already exists
        $existingUser = $db->selectOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if ($existingUser) {
            $error = 'Email đã được đăng ký';
        } else {
            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $username = explode('@', $email)[0] . '_' . time();
            
            $userId = $db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'full_name' => $full_name,
                'phone' => $phone,
                'role' => $role,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($userId) {
                // Auto login after registration
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                
                // Gửi thông báo flash message và redirect
                Functions::showMessage('success', 'Đăng ký thành công!');
                
                // Redirect về trang chủ ngay lập tức
                header('Location: ../index.php');
                exit;
            } else {
                $error = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
            }
        }
    }
}

// If user is already logged in, redirect to home
if (Functions::isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$pageTitle = 'Đăng ký - Gạo Ngon';
$pageDescription = 'Đăng ký tài khoản Gạo Ngon để nhận ưu đãi đặc biệt và mua sắm dễ dàng hơn.';
$showBreadcrumb = true;

$breadcrumbItems = [
    ['text' => 'Trang chủ', 'url' => '../index.php'],
    ['text' => 'Đăng ký']
];

require_once '../includes/header.php';
?>

<!-- Register Section -->
<section class="register-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="register-card card border-0 shadow-sm">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h2 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản
                        </h2>
                        <p class="mb-0 mt-2">Tạo tài khoản để nhận ưu đãi đặc biệt</p>
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
                            <p class="mb-0 mt-2 small">Bạn sẽ được chuyển hướng về trang chủ sau 3 giây...</p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$success): ?>
                        <form method="POST" action="" id="registerForm">
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <!-- Full Name -->
                                    <div class="form-group mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-user me-2"></i>Họ và tên *
                                        </label>
                                        <input type="text" class="form-control" 
                                               name="full_name" 
                                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                                               placeholder="Nhập họ và tên của bạn"
                                               required>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div class="form-group mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-envelope me-2"></i>Email *
                                        </label>
                                        <input type="email" class="form-control" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                               placeholder="Nhập email của bạn"
                                               required>
                                    </div>
                                    
                                    <!-- Phone -->
                                    <div class="form-group mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-phone me-2"></i>Số điện thoại *
                                        </label>
                                        <input type="tel" class="form-control" 
                                               name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                               placeholder="Nhập số điện thoại"
                                               required>
                                    </div>
                                </div>
                                
                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <!-- Password -->
                                    <div class="form-group mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-lock me-2"></i>Mật khẩu *
                                        </label>
                                        <div class="password-input">
                                            <input type="password" class="form-control" 
                                                   name="password" 
                                                   placeholder="Nhập mật khẩu (ít nhất 6 ký tự)"
                                                   id="registerPassword"
                                                   required>
                                            <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                    data-target="registerPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Confirm Password -->
                                    <div class="form-group mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu *
                                        </label>
                                        <div class="password-input">
                                            <input type="password" class="form-control" 
                                                   name="confirm_password" 
                                                   placeholder="Nhập lại mật khẩu"
                                                   id="confirmPassword"
                                                   required>
                                            <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                    data-target="confirmPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Password Strength -->
                                    <div class="password-strength mb-4">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="passwordStrengthBar" 
                                                 role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted" id="passwordStrengthText">
                                            Độ mạnh mật khẩu: Chưa đánh giá
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Terms and Conditions -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" 
                                       name="agree_terms" 
                                       id="agree_terms"
                                       required>
                                <label class="form-check-label" for="agree_terms">
                                    Tôi đồng ý với 
                                    <a href="terms.php" class="text-success text-decoration-none" target="_blank">
                                        Điều khoản dịch vụ
                                    </a> 
                                    và 
                                    <a href="privacy.php" class="text-success text-decoration-none" target="_blank">
                                        Chính sách bảo mật
                                    </a>
                                </label>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-success btn-lg py-3">
                                    <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản
                                </button>
                            </div>
                            
                            <!-- Login Link -->
                            <div class="text-center">
                                <p class="mb-0">
                                    Đã có tài khoản?
                                    <a href="login.php" class="text-success fw-bold text-decoration-none">
                                        Đăng nhập ngay
                                    </a>
                                </p>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Benefits Section -->
                <div class="benefits-section mt-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="benefit-card text-center p-4 border rounded">
                                <div class="benefit-icon mb-3">
                                    <i class="fas fa-gift fa-3x text-success"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Ưu đãi đặc biệt</h5>
                                <p class="text-muted mb-0 small">
                                    Nhận ngay 10% giảm giá cho đơn hàng đầu tiên
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="benefit-card text-center p-4 border rounded">
                                <div class="benefit-icon mb-3">
                                    <i class="fas fa-shipping-fast fa-3x text-success"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Miễn phí vận chuyển</h5>
                                <p class="text-muted mb-0 small">
                                    Miễn phí vận chuyển cho thành viên từ đơn thứ 2
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="benefit-card text-center p-4 border rounded">
                                <div class="benefit-icon mb-3">
                                    <i class="fas fa-star fa-3x text-success"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Tích điểm đổi quà</h5>
                                <p class="text-muted mb-0 small">
                                    Tích điểm cho mỗi đơn hàng và đổi quà hấp dẫn
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Register page JavaScript
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
    
    // Password strength checker
    const passwordInput = document.getElementById('registerPassword');
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            
            // Update progress bar
            strengthBar.style.width = strength.score * 25 + '%';
            
            // Update color and text
            switch (strength.score) {
                case 0:
                case 1:
                    strengthBar.className = 'progress-bar bg-danger';
                    strengthText.textContent = 'Độ mạnh mật khẩu: Rất yếu';
                    break;
                case 2:
                    strengthBar.className = 'progress-bar bg-warning';
                    strengthText.textContent = 'Độ mạnh mật khẩu: Yếu';
                    break;
                case 3:
                    strengthBar.className = 'progress-bar bg-info';
                    strengthText.textContent = 'Độ mạnh mật khẩu: Khá';
                    break;
                case 4:
                    strengthBar.className = 'progress-bar bg-success';
                    strengthText.textContent = 'Độ mạnh mật khẩu: Mạnh';
                    break;
            }
        });
    }
    
    // Form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const phone = document.querySelector('input[name="phone"]').value;
            
            // Check password match
            if (password !== confirmPassword) {
                e.preventDefault();
                showNotification('Mật khẩu xác nhận không khớp!', 'error');
                return;
            }
            
            // Check password length
            if (password.length < 6) {
                e.preventDefault();
                showNotification('Mật khẩu phải có ít nhất 6 ký tự!', 'error');
                return;
            }
            
            // Check phone format
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                showNotification('Số điện thoại không hợp lệ!', 'error');
                return;
            }
        });
    }
});

// Password strength checker function
function checkPasswordStrength(password) {
    let score = 0;
    
    // Length check
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    
    // Character variety checks
    if (/[a-z]/.test(password)) score++; // lowercase
    if (/[A-Z]/.test(password)) score++; // uppercase
    if (/[0-9]/.test(password)) score++; // numbers
    if (/[^a-zA-Z0-9]/.test(password)) score++; // special characters
    
    // Cap at 4
    score = Math.min(score, 4);
    
    return { score };
}
</script>

<style>
/* Register page styles */
.register-card {
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

.password-strength .progress {
    background-color: #e9ecef;
}

.benefit-card {
    transition: all 0.3s ease;
}

.benefit-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: var(--success-color) !important;
}

.benefit-icon {
    transition: all 0.3s ease;
}

.benefit-card:hover .benefit-icon {
    transform: scale(1.1);
}
</style>

<?php
require_once '../includes/footer.php';
?>