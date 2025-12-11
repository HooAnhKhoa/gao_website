<?php
// index.php

// Load init.php (nó sẽ load constants, db, functions)
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Trang chủ - Gạo Ngon';
$pageDescription = 'Cửa hàng gạo chất lượng, uy tín, giá tốt nhất thị trường';
$showBreadcrumb = false;

require_once __DIR__ . '/includes/header.php';
?>
<!-- Hero Slider -->
<section class="hero-slider mb-5">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner rounded-3">
            <div class="carousel-item active">
                <img src="assets/images/banner/banner1.jpg" class="d-block w-100" alt="Gạo ST25 - Gạo ngon nhất thế giới">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="display-4 fw-bold">Gạo ST25</h2>
                    <p class="lead">Gạo ngon nhất thế giới 2021 - Hương vị đặc biệt từ Sóc Trăng</p>
                    <a href="pages/products.php" class="btn btn-success btn-lg mt-3">Mua ngay</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/banner/banner2.jpg" class="d-block w-100" alt="Gạo hữu cơ - An toàn sức khỏe">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="display-4 fw-bold">Gạo Hữu Cơ</h2>
                    <p class="lead">100% tự nhiên, không hóa chất - Bảo vệ sức khỏe gia đình bạn</p>
                    <a href="pages/products.php" class="btn btn-success btn-lg mt-3">Khám phá</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/banner/banner3.jpg" class="d-block w-100" alt="Ưu đãi đặc biệt">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="display-4 fw-bold">Ưu Đãi Đặc Biệt</h2>
                    <p class="lead">Giảm đến 30% cho đơn hàng đầu tiên - Miễn phí vận chuyển toàn quốc</p>
                    <a href="pages/products.php" class="btn btn-success btn-lg mt-3">Mua sắm ngay</a>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- Features Section -->
<section class="features-section mb-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="feature-item text-center p-3">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shipping-fast fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">Miễn phí vận chuyển</h5>
                    <p class="text-muted mb-0">Cho đơn từ 500K</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-item text-center p-3">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-undo-alt fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">Đổi trả 7 ngày</h5>
                    <p class="text-muted mb-0">Nếu không hài lòng</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-item text-center p-3">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">Bảo hành chất lượng</h5>
                    <p class="text-muted mb-0">100% chính hãng</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-item text-center p-3">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">Hỗ trợ 24/7</h5>
                    <p class="text-muted mb-0">Tư vấn miễn phí</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products mb-5">
    <div class="container">
        <div class="section-header mb-4">
            <h2 class="section-title fw-bold text-center">Sản Phẩm Nổi Bật</h2>
            <p class="section-subtitle text-center text-muted">Những loại gạo được yêu thích nhất</p>
        </div>
        
        <div class="row g-4">
            <?php
            $featuredProducts = $functions->getFeaturedProducts(8);
            
            foreach ($featuredProducts as $product):
                $discount = $functions->calculateDiscount($product['price'], $product['sale_price']);
                $currentPrice = $product['sale_price'] ?: $product['price'];
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="product-card card h-100 border-0 shadow-sm">
                    <?php if ($discount): ?>
                    <span class="badge bg-danger position-absolute" style="top: 10px; left: 10px; z-index: 1;">
                        -<?php echo $discount; ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($product['stock_quantity'] <= 0): ?>
                    <span class="badge bg-dark position-absolute" style="top: 10px; right: 10px; z-index: 1;">
                        Hết hàng
                    </span>
                    <?php endif; ?>
                    
                    <div class="product-image position-relative overflow-hidden" style="height: 200px;">
                        <img src="assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                             class="card-img-top h-100 object-fit-cover" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                            <div class="overlay-content opacity-0">
                                <a href="pages/product-detail.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-light btn-sm mb-2">
                                    <i class="fas fa-eye me-1"></i>Xem nhanh
                                </a>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                <button class="btn btn-success btn-sm add-to-cart-btn" 
                                        data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus me-1"></i>Thêm giỏ hàng
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <span class="product-category small text-muted d-block mb-1">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                        <h5 class="product-title card-title">
                            <a href="pages/product-detail.php?id=<?php echo $product['id']; ?>" 
                               class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h5>
                        
                        <div class="product-price mb-2">
                            <span class="current-price fw-bold text-success fs-5">
                                <?php echo $functions->formatPrice($currentPrice); ?>
                            </span>
                            <?php if ($product['sale_price']): ?>
                            <span class="original-price text-muted text-decoration-line-through ms-2">
                                <?php echo $functions->formatPrice($product['price']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-rating mb-3">
                            <?php
                            $rating = $product['rating'] ?? 0;
                            $fullStars = floor($rating);
                            $hasHalfStar = $rating - $fullStars >= 0.5;
                            
                            for ($i = 1; $i <= 5; $i++):
                                if ($i <= $fullStars):
                            ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php
                                elseif ($i == $fullStars + 1 && $hasHalfStar):
                            ?>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            <?php else: ?>
                                <i class="far fa-star text-warning"></i>
                            <?php
                                endif;
                            endfor;
                            ?>
                            <small class="text-muted ms-1">(<?php echo $product['total_reviews'] ?? 0; ?>)</small>
                        </div>
                        
                        <div class="product-meta d-flex justify-content-between">
                            <span class="stock-status small">
                                <i class="fas fa-box me-1"></i>
                                <?php if ($product['stock_quantity'] > 10): ?>
                                    <span class="text-success">Còn <?php echo $product['stock_quantity']; ?> sản phẩm</span>
                                <?php elseif ($product['stock_quantity'] > 0): ?>
                                    <span class="text-warning">Còn <?php echo $product['stock_quantity']; ?> sản phẩm</span>
                                <?php else: ?>
                                    <span class="text-danger">Hết hàng</span>
                                <?php endif; ?>
                            </span>
                            <span class="origin small text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($product['origin']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-grid">
                            <?php if ($product['stock_quantity'] > 0): ?>
                            <button class="btn btn-outline-success add-to-cart-btn" 
                                    data-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-cart-plus me-1"></i>Thêm vào giỏ hàng
                            </button>
                            <?php else: ?>
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="fas fa-bell me-1"></i>Thông báo khi có hàng
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="pages/products.php" class="btn btn-success btn-lg">
                <i class="fas fa-store me-2"></i>Xem tất cả sản phẩm
            </a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section mb-5 bg-light py-5">
    <div class="container">
        <div class="section-header mb-4">
            <h2 class="section-title fw-bold text-center">Danh Mục Sản Phẩm</h2>
            <p class="section-subtitle text-center text-muted">Chọn loại gạo phù hợp với nhu cầu của bạn</p>
        </div>
        
        <div class="row g-4">
            <?php
            $categories = $functions->getCategoriesWithCount();
            
            foreach ($categories as $category):
                $categoryImage = $category['image'] ?: 'category-default.jpg';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="category-card card border-0 shadow-sm h-100">
                    <div class="category-image" style="height: 180px; overflow: hidden;">
                        <img src="assets/images/categories/<?php echo $categoryImage; ?>" 
                             class="card-img-top h-100 object-fit-cover"
                             alt="<?php echo htmlspecialchars($category['name']); ?>">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <p class="card-text text-muted small mb-2">
                            <?php echo htmlspecialchars(substr($category['description'] ?? '', 0, 80)); ?>...
                        </p>
                        <span class="badge bg-success rounded-pill">
                            <?php echo $category['product_count']; ?> sản phẩm
                        </span>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="pages/products.php?category=<?php echo $category['id']; ?>" 
                           class="btn btn-outline-success w-100">
                            <i class="fas fa-arrow-right me-1"></i>Xem ngay
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="new-arrivals mb-5">
    <div class="container">
        <div class="section-header mb-4">
            <h2 class="section-title fw-bold text-center">Sản Phẩm Mới Về</h2>
            <p class="section-subtitle text-center text-muted">Cập nhật những sản phẩm mới nhất</p>
        </div>
        
        <div class="row g-4">
            <?php
            $newProducts = $functions->getNewProducts(4);
            
            foreach ($newProducts as $product):
                $discount = $functions->calculateDiscount($product['price'], $product['sale_price']);
                $currentPrice = $product['sale_price'] ?: $product['price'];
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="new-product-card card border-0 shadow-sm h-100">
                    <div class="position-relative">
                        <?php if (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                        <span class="badge bg-danger position-absolute" style="top: 10px; left: 10px; z-index: 1;">
                            Mới
                        </span>
                        <?php endif; ?>
                        
                        <img src="assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             style="height: 200px; object-fit: cover;">
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="pages/product-detail.php?id=<?php echo $product['id']; ?>" 
                               class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h5>
                        
                        <div class="product-price mb-2">
                            <span class="current-price fw-bold text-success">
                                <?php echo $functions->formatPrice($currentPrice); ?>
                            </span>
                            <?php if ($product['sale_price']): ?>
                            <span class="original-price text-muted text-decoration-line-through ms-2">
                                <?php echo $functions->formatPrice($product['price']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr($product['short_description'] ?? $product['description'] ?? '', 0, 60)); ?>...
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-weight me-1"></i>
                                <?php echo $product['weight']; ?>kg
                            </span>
                            <button class="btn btn-sm btn-outline-success add-to-cart-btn" 
                                    data-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-choose-us mb-5 bg-success text-white py-5">
    <div class="container">
        <div class="section-header mb-4 text-center">
            <h2 class="section-title fw-bold">Tại Sao Chọn Gạo Ngon?</h2>
            <p class="section-subtitle opacity-75">Cam kết mang đến sản phẩm tốt nhất cho khách hàng</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-award fa-3x"></i>
                    </div>
                    <h4 class="fw-bold">Chất Lượng Hàng Đầu</h4>
                    <p class="opacity-75">Chúng tôi lựa chọn những hạt gạo ngon nhất từ các vùng trồng lúa nổi tiếng</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-leaf fa-3x"></i>
                    </div>
                    <h4 class="fw-bold">An Toàn Sức Khỏe</h4>
                    <p class="opacity-75">Sản phẩm được kiểm định nghiêm ngặt, đảm bảo an toàn vệ sinh thực phẩm</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-truck fa-3x"></i>
                    </div>
                    <h4 class="fw-bold">Giao Hàng Nhanh</h4>
                    <p class="opacity-75">Miễn phí vận chuyển, giao hàng toàn quốc trong 24-48 giờ</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials mb-5">
    <div class="container">
        <div class="section-header mb-4">
            <h2 class="section-title fw-bold text-center">Khách Hàng Nói Gì</h2>
            <p class="section-subtitle text-center text-muted">Những đánh giá từ khách hàng đã sử dụng</p>
        </div>
        
        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="testimonial-item text-center p-4">
                        <div class="testimonial-avatar mb-3">
                            <img src="assets/images/avatars/avatar1.jpg" class="rounded-circle" width="80" alt="Khách hàng">
                        </div>
                        <p class="testimonial-text lead fst-italic mb-3">
                            "Gạo ST25 của shop ngon thật sự! Hạt gạo dẻo, thơm, nấu cơm ăn rất ngon. Gia đình tôi đã dùng 2 tháng nay và rất hài lòng."
                        </p>
                        <h5 class="testimonial-author fw-bold">Chị Nguyễn Thị Mai</h5>
                        <p class="testimonial-position text-muted">Nội trợ - Hà Nội</p>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <div class="testimonial-item text-center p-4">
                        <div class="testimonial-avatar mb-3">
                            <img src="assets/images/avatars/avatar2.jpg" class="rounded-circle" width="80" alt="Khách hàng">
                        </div>
                        <p class="testimonial-text lead fst-italic mb-3">
                            "Dịch vụ giao hàng rất nhanh, gói hàng đẹp. Gạo hữu cơ rất ngon, an toàn cho sức khỏe. Sẽ tiếp tục ủng hộ shop."
                        </p>
                        <h5 class="testimonial-author fw-bold">Anh Trần Văn Bình</h5>
                        <p class="testimonial-position text-muted">Doanh nhân - TP.HCM</p>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <div class="testimonial-item text-center p-4">
                        <div class="testimonial-avatar mb-3">
                            <img src="assets/images/avatars/avatar3.jpg" class="rounded-circle" width="80" alt="Khách hàng">
                        </div>
                        <p class="testimonial-text lead fst-italic mb-3">
                            "Tôi mua gạo cho công ty, số lượng lớn. Shop hỗ trợ rất tốt, giá cả hợp lý. Sản phẩm đều đạt chất lượng như cam kết."
                        </p>
                        <h5 class="testimonial-author fw-bold">Chị Lê Thị Hương</h5>
                        <p class="testimonial-position text-muted">Quản lý nhà hàng - Đà Nẵng</p>
                    </div>
                </div>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter mb-5 bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-3">Đăng Ký Nhận Tin</h3>
                <p class="text-muted mb-0">
                    Nhận thông tin về khuyến mãi, sản phẩm mới và mẹo nấu ăn từ chuyên gia.
                </p>
            </div>
            <div class="col-lg-6">
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Nhập email của bạn" required>
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-paper-plane me-1"></i>Đăng ký
                        </button>
                    </div>
                    <div class="form-text mt-2">
                        <small class="text-muted">Chúng tôi cam kết không spam email của bạn</small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
// Add to cart functionality for all buttons
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all add-to-cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            addToCart(productId);
        });
    });
    
    // Product image hover effect
    const productImages = document.querySelectorAll('.product-image');
    productImages.forEach(image => {
        const overlay = image.querySelector('.product-overlay');
        
        image.addEventListener('mouseenter', function() {
            const content = overlay.querySelector('.overlay-content');
            content.classList.remove('opacity-0');
            content.classList.add('opacity-100');
        });
        
        image.addEventListener('mouseleave', function() {
            const content = overlay.querySelector('.overlay-content');
            content.classList.remove('opacity-100');
            content.classList.add('opacity-0');
        });
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>