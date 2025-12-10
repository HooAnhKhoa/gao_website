<?php
require_once __DIR__ . '/../config/database.php';
?>

    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row">
                <!-- About -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h4 class="text-uppercase mb-4">
                        <i class="fas fa-seedling me-2 text-warning"></i>Gạo Ngon
                    </h4>
                    <p class="text-light">
                        Chuyên cung cấp các loại gạo chất lượng cao, đảm bảo an toàn vệ sinh thực phẩm, 
                        mang hương vị quê hương đến mọi nhà.
                    </p>
                    <div class="social-icons mt-4">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-uppercase mb-4">Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="index.php" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-1 text-warning"></i>Trang chủ
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="pages/products.php" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-1 text-warning"></i>Sản phẩm
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-1 text-warning"></i>Tin tức
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-1 text-warning"></i>Giới thiệu
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-1 text-warning"></i>Liên hệ
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-uppercase mb-4">Danh mục</h5>
                    <ul class="list-unstyled">
                        <?php
                        $db = Database::getInstance();
                        $footerCategories = $db->select(
                            "SELECT * FROM categories WHERE status = 'active' LIMIT 5"
                        );
                        
                        foreach ($footerCategories as $category):
                        ?>
                        <li class="mb-2">
                            <a href="pages/products.php?category=<?php echo $category['id']; ?>" 
                               class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-1 text-warning"></i>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-uppercase mb-4">Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt me-3 text-warning"></i>
                            <span class="text-light">123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh</span>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone-alt me-3 text-warning"></i>
                            <a href="tel:19001000" class="text-light text-decoration-none">1900 1000</a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope me-3 text-warning"></i>
                            <a href="mailto:<?php echo ADMIN_EMAIL; ?>" class="text-light text-decoration-none">
                                <?php echo ADMIN_EMAIL; ?>
                            </a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-clock me-3 text-warning"></i>
                            <span class="text-light">8:00 - 22:00 (T2 - CN)</span>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="bg-white my-4">

            <!-- Payment Methods -->
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-uppercase mb-3">Phương thức thanh toán</h6>
                    <div class="payment-methods">
                        <img src="assets/images/payment/cod.png" alt="COD" class="me-2 mb-2" height="30">
                        <img src="assets/images/payment/bank-transfer.png" alt="Chuyển khoản" class="me-2 mb-2" height="30">
                        <img src="assets/images/payment/momo.png" alt="Momo" class="me-2 mb-2" height="30">
                        <img src="assets/images/payment/visa.png" alt="Visa" class="me-2 mb-2" height="30">
                        <img src="assets/images/payment/mastercard.png" alt="MasterCard" class="me-2 mb-2" height="30">
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-uppercase mb-3">Đảm bảo</h6>
                    <div class="guarantees">
                        <span class="badge bg-success me-2 mb-2">
                            <i class="fas fa-check-circle me-1"></i>Chính hãng
                        </span>
                        <span class="badge bg-success me-2 mb-2">
                            <i class="fas fa-shipping-fast me-1"></i>Miễn phí ship
                        </span>
                        <span class="badge bg-success me-2 mb-2">
                            <i class="fas fa-undo-alt me-1"></i>Đổi trả 7 ngày
                        </span>
                        <span class="badge bg-success mb-2">
                            <i class="fas fa-shield-alt me-1"></i>Bảo hành chất lượng
                        </span>
                    </div>
                </div>
            </div>

            <hr class="bg-white my-4">

            <!-- Copyright -->
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-light">
                        &copy; <?php echo date('Y'); ?> Gạo Ngon. Tất cả các quyền được bảo lưu.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-light">
                        Được phát triển bởi <a href="#" class="text-warning text-decoration-none">Gạo Ngon Team</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button type="button" class="btn btn-success btn-floating btn-lg" id="btn-back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <!-- Additional Scripts -->
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
    // Back to top button
    const backToTopButton = document.getElementById("btn-back-to-top");

    window.addEventListener("scroll", () => {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = "block";
        } else {
            backToTopButton.style.display = "none";
        }
    });

    backToTopButton.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });

    // Update cart count
    function updateCartCount() {
        fetch('api/cart/get.php')
            .then(response => response.json())
            .then(data => {
                const cartElements = document.querySelectorAll('.cart-count');
                cartElements.forEach(element => {
                    element.textContent = data.total_items || 0;
                });
            })
            .catch(error => console.error('Error updating cart count:', error));
    }

    // Initialize cart count on page load
    document.addEventListener('DOMContentLoaded', updateCartCount);

    // Add to cart function
    function addToCart(productId, quantity = 1) {
        fetch('api/cart/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification(data.message || 'Đã thêm vào giỏ hàng!', 'success');
                // Update cart count
                updateCartCount();
            } else {
                showNotification(data.message || 'Có lỗi xảy ra!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Lỗi kết nối!', 'error');
        });
    }

    // Notification function
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `custom-notification alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        `;
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .custom-notification {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        #btn-back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            z-index: 999;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>