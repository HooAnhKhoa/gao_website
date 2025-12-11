<?php
require_once __DIR__ . '/../includes/init.php';
require_once '../includes/header.php';

$db = Database::getInstance();
$functions = new Functions();

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// Get product details
$product = $db->selectOne(
    "SELECT p.*, c.name as category_name, c.slug as category_slug 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.id = ? AND p.status = ?",
    [$product_id, PRODUCT_ACTIVE]
);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Update view count
// Sử dụng expression
$db->update(
    'products',                     // $table
    ['views' => ['raw' => 'views + 1']],  // Expression
    'id = ?',                       // $where
    [$product_id]                   // $whereParams
);

// Get related products
$relatedProducts = $db->select(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.category_id = ? AND p.id != ? AND p.status = ? 
     ORDER BY RAND() 
     LIMIT 4",
    [$product['category_id'], $product_id, PRODUCT_ACTIVE]
);

// Get reviews
$reviews = $db->select(
    "SELECT r.*, u.full_name, u.avatar 
     FROM reviews r 
     JOIN users u ON r.user_id = u.id 
     WHERE r.product_id = ? AND r.status = ? 
     ORDER BY r.created_at DESC 
     LIMIT 10",
    [$product_id, REVIEW_APPROVED]
);

// Calculate rating statistics
$ratingStats = $db->selectOne(
    "SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
     FROM reviews 
     WHERE product_id = ? AND status = ?",
    [$product_id, REVIEW_APPROVED]
);

$pageTitle = $product['name'] . ' - Gạo Ngon';
$pageDescription = $product['short_description'] ?? substr($product['description'] ?? '', 0, 150);
$showBreadcrumb = true;

$breadcrumbItems = [
    ['text' => 'Trang chủ', 'url' => '../index.php'],
    ['text' => 'Sản phẩm', 'url' => 'products.php'],
    ['text' => $product['category_name'], 'url' => 'products.php?category=' . $product['category_id']],
    ['text' => $product['name']]
];
?>

<!-- Product Detail Section -->
<section class="product-detail-section py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Product Images -->
            <div class="col-lg-6">
                <div class="product-images">
                    <!-- Main Image -->
                    <div class="main-image mb-3">
                        <img id="mainProductImage" 
                             src="../assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                             class="img-fluid rounded shadow" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             style="max-height: 500px; object-fit: contain;">
                    </div>
                    
                    <!-- Thumbnails -->
                    <div class="thumbnails d-flex flex-wrap gap-2">
                        <?php
                        // Main image thumbnail
                        $images = [$product['image']];
                        
                        // Additional images from JSON
                        if ($product['images']) {
                            $additionalImages = json_decode($product['images'], true);
                            if (is_array($additionalImages)) {
                                $images = array_merge($images, $additionalImages);
                            }
                        }
                        
                        foreach ($images as $index => $image):
                            if ($image):
                        ?>
                        <a href="javascript:void(0);" 
                           class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>"
                           data-image="../assets/images/products/<?php echo $image; ?>">
                            <img src="../assets/images/products/<?php echo $image; ?>" 
                                 class="img-thumbnail" 
                                 alt="Thumbnail <?php echo $index + 1; ?>"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        </a>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-info">
                    <!-- Product Header -->
                    <div class="product-header mb-4">
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): 
                            $discount = $functions->calculateDiscount($product['price'], $product['sale_price']);
                        ?>
                        <span class="badge bg-danger fs-6 mb-2">
                            -<?php echo $discount; ?>
                        </span>
                        <?php endif; ?>
                        
                        <h1 class="product-title fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-meta mb-3">
                            <span class="product-code me-3">
                                <i class="fas fa-hashtag text-muted me-1"></i>
                                Mã: <span class="fw-bold">SP<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></span>
                            </span>
                            <span class="product-category">
                                <i class="fas fa-folder text-muted me-1"></i>
                                Danh mục: 
                                <a href="products.php?category=<?php echo $product['category_id']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </a>
                            </span>
                        </div>
                        
                        <!-- Rating -->
                        <div class="product-rating mb-4">
                            <div class="d-flex align-items-center">
                                <div class="stars me-2">
                                    <?php
                                    $rating = $product['rating'] ?? 0;
                                    $fullStars = floor($rating);
                                    $hasHalfStar = $rating - $fullStars >= 0.5;
                                    
                                    for ($i = 1; $i <= 5; $i++):
                                        if ($i <= $fullStars):
                                    ?>
                                        <i class="fas fa-star text-warning fa-lg"></i>
                                    <?php
                                        elseif ($i == $fullStars + 1 && $hasHalfStar):
                                    ?>
                                        <i class="fas fa-star-half-alt text-warning fa-lg"></i>
                                    <?php else: ?>
                                        <i class="far fa-star text-warning fa-lg"></i>
                                    <?php
                                        endif;
                                    endfor;
                                    ?>
                                </div>
                                <span class="me-3">
                                    <span class="fw-bold"><?php echo number_format($rating, 1); ?></span>
                                    <span class="text-muted">/5</span>
                                </span>
                                <span class="text-muted">
                                    (<a href="#reviews" class="text-decoration-none"><?php echo $product['total_reviews']; ?> đánh giá</a>)
                                </span>
                                <span class="ms-3 text-muted">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo ($product['views'] ?? 0) + 1; ?> lượt xem
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="product-price mb-4">
                        <?php if ($product['sale_price']): ?>
                        <div class="d-flex align-items-center">
                            <span class="current-price fw-bold text-danger display-5 me-3">
                                <?php echo $functions->formatPrice($product['sale_price']); ?>
                            </span>
                            <span class="original-price text-muted text-decoration-line-through fs-4">
                                <?php echo $functions->formatPrice($product['price']); ?>
                            </span>
                        </div>
                        <?php else: ?>
                        <div class="current-price fw-bold text-success display-5">
                            <?php echo $functions->formatPrice($product['price']); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Short Description -->
                    <div class="product-short-description mb-4">
                        <p class="lead"><?php echo htmlspecialchars($product['short_description'] ?? ''); ?></p>
                    </div>

                    <!-- Product Details -->
                    <div class="product-details mb-4">
                        <ul class="list-unstyled">
                            <?php if ($product['weight']): ?>
                            <li class="mb-2">
                                <i class="fas fa-weight text-success me-2"></i>
                                <strong>Khối lượng:</strong> <?php echo $product['weight']; ?>kg
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($product['unit']): ?>
                            <li class="mb-2">
                                <i class="fas fa-box text-success me-2"></i>
                                <strong>Đơn vị:</strong> <?php echo $product['unit']; ?>
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($product['origin']): ?>
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt text-success me-2"></i>
                                <strong>Xuất xứ:</strong> <?php echo htmlspecialchars($product['origin']); ?>
                            </li>
                            <?php endif; ?>
                            
                            <li class="mb-2">
                                <i class="fas fa-warehouse text-success me-2"></i>
                                <strong>Tình trạng:</strong>
                                <?php if ($product['stock_quantity'] > 10): ?>
                                    <span class="text-success">Còn hàng (<?php echo $product['stock_quantity']; ?> sản phẩm)</span>
                                <?php elseif ($product['stock_quantity'] > 0): ?>
                                    <span class="text-warning">Sắp hết (<?php echo $product['stock_quantity']; ?> sản phẩm)</span>
                                <?php else: ?>
                                    <span class="text-danger">Hết hàng</span>
                                <?php endif; ?>
                            </li>
                            
                            <?php if ($product['cooking_guide']): ?>
                            <li class="mb-2">
                                <i class="fas fa-utensils text-success me-2"></i>
                                <strong>Hướng dẫn nấu:</strong> <?php echo htmlspecialchars(substr($product['cooking_guide'], 0, 100)); ?>...
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Add to Cart -->
                    <div class="add-to-cart mb-5">
                        <?php if ($product['stock_quantity'] > 0): ?>
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="form-label fw-bold">Số lượng:</label>
                            </div>
                            <div class="col-auto">
                                <div class="input-group" style="width: 150px;">
                                    <button class="btn btn-outline-secondary" type="button" id="decreaseQty">-</button>
                                    <input type="number" class="form-control text-center" id="quantity" 
                                           value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                    <button class="btn btn-outline-secondary" type="button" id="increaseQty">+</button>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-success btn-lg px-5" id="addToCartBtn" data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng
                                </button>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Sản phẩm tạm thời hết hàng. Vui lòng quay lại sau!
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <button class="btn btn-outline-secondary btn-lg" disabled>
                                    <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng
                                </button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-outline-success btn-lg" id="notifyMeBtn" data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-bell me-2"></i>Thông báo khi có hàng
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Share & Wishlist -->
                    <div class="product-actions mb-5">
                        <div class="d-flex align-items-center">
                            <span class="me-3 fw-bold">Chia sẻ:</span>
                            <div class="social-share">
                                <a href="#" class="btn btn-outline-primary btn-sm me-2" title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-outline-info btn-sm me-2" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="btn btn-outline-danger btn-sm me-2" title="Pinterest">
                                    <i class="fab fa-pinterest-p"></i>
                                </a>
                                <a href="#" class="btn btn-outline-dark btn-sm" title="Sao chép link">
                                    <i class="fas fa-link"></i>
                                </a>
                            </div>
                            <div class="ms-auto">
                                <button class="btn btn-outline-danger" id="addToWishlist" data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-heart me-1"></i>Thêm vào yêu thích
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs mt-5">
            <ul class="nav nav-tabs" id="productTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                            data-bs-target="#description" type="button" role="tab">
                        <i class="fas fa-file-alt me-2"></i>Mô tả chi tiết
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="nutrition-tab" data-bs-toggle="tab" 
                            data-bs-target="#nutrition" type="button" role="tab">
                        <i class="fas fa-apple-alt me-2"></i>Thông tin dinh dưỡng
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cooking-tab" data-bs-toggle="tab" 
                            data-bs-target="#cooking" type="button" role="tab">
                        <i class="fas fa-utensils me-2"></i>Hướng dẫn nấu
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                            data-bs-target="#reviews" type="button" role="tab">
                        <i class="fas fa-star me-2"></i>Đánh giá
                        <span class="badge bg-success ms-1"><?php echo $ratingStats['total_reviews'] ?? 0; ?></span>
                    </button>
                </li>
            </ul>
            
            <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabContent">
                <!-- Description Tab -->
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'] ?? 'Đang cập nhật...')); ?>
                    </div>
                </div>
                
                <!-- Nutrition Tab -->
                <div class="tab-pane fade" id="nutrition" role="tabpanel">
                    <div class="nutrition-info">
                        <?php if ($product['nutritional_info']): 
                            $nutrition = json_decode($product['nutritional_info'], true);
                            if (is_array($nutrition)):
                        ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-success">
                                    <tr>
                                        <th>Thành phần</th>
                                        <th>Hàm lượng</th>
                                        <th>% DV*</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($nutrition as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['amount'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['dv'] ?? ''); ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <p class="text-muted small">*DV: Giá trị dinh dưỡng hàng ngày</p>
                        </div>
                        <?php else: ?>
                        <p><?php echo htmlspecialchars($product['nutritional_info']); ?></p>
                        <?php endif; ?>
                        <?php else: ?>
                        <p class="text-muted">Đang cập nhật thông tin dinh dưỡng...</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Cooking Guide Tab -->
                <div class="tab-pane fade" id="cooking" role="tabpanel">
                    <div class="cooking-guide">
                        <?php if ($product['cooking_guide']): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-3">Hướng dẫn nấu cơm:</h5>
                                <?php echo nl2br(htmlspecialchars($product['cooking_guide'])); ?>
                            </div>
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-3">Mẹo nấu cơm ngon:</h5>
                                <ul>
                                    <li>Vo gạo nhẹ nhàng, không vo quá kỹ</li>
                                    <li>Ngâm gạo 30 phút trước khi nấu</li>
                                    <li>Tỷ lệ nước: 1 gạo : 1.2 nước</li>
                                    <li>Để cơm nghỉ 10 phút sau khi nấu</li>
                                </ul>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">Đang cập nhật hướng dẫn nấu...</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="reviews-section">
                        <div class="row">
                            <!-- Rating Summary -->
                            <div class="col-lg-4 mb-4">
                                <div class="rating-summary p-4 border rounded">
                                    <h4 class="fw-bold mb-4">Đánh giá sản phẩm</h4>
                                    
                                    <div class="overall-rating text-center mb-4">
                                        <div class="display-4 fw-bold text-success mb-2">
                                            <?php echo number_format($ratingStats['avg_rating'] ?? 0, 1); ?>
                                        </div>
                                        <div class="stars mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): 
                                                $avgRating = $ratingStats['avg_rating'] ?? 0;
                                            ?>
                                            <?php if ($i <= floor($avgRating)): ?>
                                                <i class="fas fa-star text-warning fa-lg"></i>
                                            <?php elseif ($i == ceil($avgRating) && fmod($avgRating, 1) > 0): ?>
                                                <i class="fas fa-star-half-alt text-warning fa-lg"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-warning fa-lg"></i>
                                            <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-muted">
                                            Dựa trên <?php echo $ratingStats['total_reviews'] ?? 0; ?> đánh giá
                                        </p>
                                    </div>
                                    
                                    <!-- Rating Breakdown -->
                                    <div class="rating-breakdown">
                                        <?php for ($i = 5; $i >= 1; $i--): 
                                            $starCount = $ratingStats[$i . '_star'] ?? 0;
                                            $percentage = $ratingStats['total_reviews'] > 0 ? 
                                                ($starCount / $ratingStats['total_reviews']) * 100 : 0;
                                        ?>
                                        <div class="rating-bar mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="small">
                                                    <?php echo $i; ?> <i class="fas fa-star text-warning"></i>
                                                </span>
                                                <span class="small"><?php echo $starCount; ?></span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-warning" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $percentage; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                    
                                    <!-- Add Review Button -->
                                    <div class="mt-4">
                                        <button class="btn btn-success w-100" id="writeReviewBtn">
                                            <i class="fas fa-pen me-2"></i>Viết đánh giá
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reviews List -->
                            <div class="col-lg-8">
                                <?php if (!empty($reviews)): ?>
                                <div class="reviews-list">
                                    <h4 class="fw-bold mb-4">Đánh giá từ khách hàng</h4>
                                    
                                    <?php foreach ($reviews as $review): ?>
                                    <div class="review-item mb-4 pb-4 border-bottom">
                                        <div class="review-header d-flex justify-content-between align-items-start mb-3">
                                            <div class="reviewer-info d-flex align-items-center">
                                                <div class="reviewer-avatar me-3">
                                                    <img src="../assets/images/avatars/<?php echo $review['avatar'] ?: 'default.jpg'; ?>" 
                                                         class="rounded-circle" 
                                                         width="50" 
                                                         alt="<?php echo htmlspecialchars($review['full_name']); ?>">
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($review['full_name']); ?></h6>
                                                    <div class="stars small">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $review['rating']): ?>
                                                            <i class="fas fa-star text-warning"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star text-warning"></i>
                                                        <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="review-date text-muted small">
                                                <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="review-content">
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                            
                                            <?php if ($review['images']): 
                                                $reviewImages = json_decode($review['images'], true);
                                                if (is_array($reviewImages) && !empty($reviewImages)):
                                            ?>
                                            <div class="review-images mt-3">
                                                <div class="row g-2">
                                                    <?php foreach ($reviewImages as $image): ?>
                                                    <div class="col-4 col-md-3">
                                                        <a href="../assets/images/reviews/<?php echo $image; ?>" 
                                                           data-lightbox="review-<?php echo $review['id']; ?>">
                                                            <img src="../assets/images/reviews/<?php echo $image; ?>" 
                                                                 class="img-thumbnail" 
                                                                 alt="Review image"
                                                                 style="width: 100%; height: 100px; object-fit: cover;">
                                                        </a>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; endif; ?>
                                        </div>
                                        
                                        <?php if ($review['reply']): ?>
                                        <div class="review-reply mt-3 p-3 bg-light rounded">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-reply text-success me-2"></i>
                                                <strong>Phản hồi từ cửa hàng:</strong>
                                            </div>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['reply'])); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- View More Reviews -->
                                    <div class="text-center mt-4">
                                        <a href="#" class="btn btn-outline-success">
                                            <i class="fas fa-eye me-2"></i>Xem thêm đánh giá
                                        </a>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="no-reviews text-center py-5">
                                    <i class="fas fa-comments fa-4x text-muted mb-4"></i>
                                    <h4 class="mb-3">Chưa có đánh giá nào</h4>
                                    <p class="text-muted mb-4">Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                                    <button class="btn btn-success" id="writeFirstReviewBtn">
                                        <i class="fas fa-pen me-2"></i>Viết đánh giá đầu tiên
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="related-products mt-5">
            <div class="section-header mb-4">
                <h3 class="fw-bold">Sản phẩm liên quan</h3>
                <p class="text-muted">Các sản phẩm cùng danh mục</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($relatedProducts as $related): 
                    $discount = $functions->calculateDiscount($related['price'], $related['sale_price']);
                    $currentPrice = $related['sale_price'] ?: $related['price'];
                ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="related-product-card card border-0 shadow-sm h-100">
                        <div class="product-image position-relative overflow-hidden" style="height: 200px;">
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>">
                                <img src="../assets/images/products/<?php echo $related['image'] ?: 'default.jpg'; ?>" 
                                     class="card-img-top h-100 object-fit-cover" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>">
                            </a>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="product-detail.php?id=<?php echo $related['id']; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($related['name']); ?>
                                </a>
                            </h5>
                            
                            <div class="product-price mb-3">
                                <span class="current-price fw-bold text-success">
                                    <?php echo $functions->formatPrice($currentPrice); ?>
                                </span>
                                <?php if ($related['sale_price']): ?>
                                <span class="original-price text-muted text-decoration-line-through ms-2 small">
                                    <?php echo $functions->formatPrice($related['price']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid">
                                <button class="btn btn-outline-success add-to-cart-btn" 
                                        data-id="<?php echo $related['id']; ?>">
                                    <i class="fas fa-cart-plus me-1"></i>Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Write Review Modal -->
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Viết đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <input type="hidden" id="productId" value="<?php echo $product['id']; ?>">
                    
                    <!-- Rating -->
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">Đánh giá của bạn:</label>
                        <div class="rating-input text-center">
                            <div class="stars mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star fa-2x rating-star" data-value="<?php echo $i; ?>" 
                                   style="cursor: pointer; color: #ffc107; margin: 0 5px;"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" id="rating" name="rating" value="5">
                            <div class="rating-labels">
                                <span id="ratingLabel" class="text-success fw-bold">Rất tốt</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comment -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nhận xét của bạn:</label>
                        <textarea class="form-control" id="comment" name="comment" 
                                  rows="4" 
                                  placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                    </div>
                    
                    <!-- Upload Images -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Hình ảnh (tối đa 3 ảnh):</label>
                        <div class="image-upload-area border rounded p-3 text-center">
                            <input type="file" id="reviewImages" name="review_images[]" 
                                   accept="image/*" multiple class="d-none">
                            <div class="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Kéo thả ảnh vào đây hoặc click để chọn</p>
                                <p class="small text-muted">Hỗ trợ: JPG, PNG, GIF. Tối đa 5MB mỗi ảnh</p>
                            </div>
                            <div class="image-preview mt-3 d-none" id="imagePreview">
                                <div class="row g-2" id="previewContainer"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Gửi đánh giá
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

// Product detail page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Image thumbnail switching
    document.querySelectorAll('.thumbnail-item').forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Update main image
            const mainImage = document.getElementById('mainProductImage');
            const newImage = this.dataset.image;
            
            // Add fade effect
            mainImage.style.opacity = '0.5';
            setTimeout(() => {
                mainImage.src = newImage;
                mainImage.style.opacity = '1';
            }, 200);
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail-item').forEach(t => {
                t.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
    
    // Quantity controls
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');
    
    if (decreaseBtn && increaseBtn) {
        decreaseBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });
        
        increaseBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            const max = parseInt(quantityInput.max);
            if (value < max) {
                quantityInput.value = value + 1;
            }
        });
    }
    // Add to cart
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const quantity = parseInt(document.getElementById('quantity').value);
            addToCart(productId, quantity);
        });
    }
    
    // Write review button
    const writeReviewBtn = document.getElementById('writeReviewBtn');
    const writeFirstReviewBtn = document.getElementById('writeFirstReviewBtn');
    
    if (writeReviewBtn) {
        writeReviewBtn.addEventListener('click', function() {
            const reviewModal = new bootstrap.Modal(document.getElementById('writeReviewModal'));
            reviewModal.show();
        });
    }
    
    if (writeFirstReviewBtn) {
        writeFirstReviewBtn.addEventListener('click', function() {
            const reviewModal = new bootstrap.Modal(document.getElementById('writeReviewModal'));
            reviewModal.show();
        });
    }
    
    // Rating stars
    const ratingStars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('rating');
    const ratingLabel = document.getElementById('ratingLabel');
    
    const ratingLabels = {
        1: 'Rất tệ',
        2: 'Tệ',
        3: 'Bình thường',
        4: 'Tốt',
        5: 'Rất tốt'
    };
    
    ratingStars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const value = this.dataset.value;
            updateRatingStars(value);
            ratingLabel.textContent = ratingLabels[value] || '';
        });
        
        star.addEventListener('click', function() {
            const value = this.dataset.value;
            ratingInput.value = value;
            ratingLabel.textContent = ratingLabels[value] || '';
        });
    });
    
    function updateRatingStars(value) {
        ratingStars.forEach(star => {
            const starValue = star.dataset.value;
            if (starValue <= value) {
                star.classList.remove('far');
                star.classList.add('fas');
            } else {
                star.classList.remove('fas');
                star.classList.add('far');
            }
        });
    }
    
    // Initialize rating
    updateRatingStars(ratingInput.value);
    ratingLabel.textContent = ratingLabels[ratingInput.value] || '';
    
    // Image upload preview
    const fileInput = document.getElementById('reviewImages');
    const uploadArea = document.querySelector('.image-upload-area');
    const uploadPlaceholder = uploadArea.querySelector('.upload-placeholder');
    const imagePreview = document.getElementById('imagePreview');
    const previewContainer = document.getElementById('previewContainer');
    
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.backgroundColor = '#f8f9fa';
    });
    
    uploadArea.addEventListener('dragleave', function() {
        this.style.backgroundColor = '';
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.backgroundColor = '';
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    });
    
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
    
    function handleFiles(files) {
        const maxFiles = 3;
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        // Filter files
        const validFiles = Array.from(files)
            .filter(file => allowedTypes.includes(file.type))
            .filter(file => file.size <= maxSize)
            .slice(0, maxFiles);
        
        if (validFiles.length === 0) {
            showNotification('Vui lòng chọn ảnh hợp lệ (JPG, PNG, GIF, tối đa 5MB)', 'error');
            return;
        }
        
        // Clear previous previews
        previewContainer.innerHTML = '';
        
        // Show preview for each file
        validFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-4';
                
                col.innerHTML = `
                    <div class="image-preview-item position-relative">
                        <img src="${e.target.result}" 
                             class="img-thumbnail" 
                             alt="Preview ${index + 1}"
                             style="width: 100%; height: 100px; object-fit: cover;">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                                onclick="removeImagePreview(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
        
        // Show preview area
        uploadPlaceholder.classList.add('d-none');
        imagePreview.classList.remove('d-none');
    }
    
    // Add to wishlist
    const addToWishlistBtn = document.getElementById('addToWishlist');
    if (addToWishlistBtn) {
        addToWishlistBtn.addEventListener('click', function() {
            const productId = this.dataset.id;
            addToWishlist(productId);
        });
    }
    
    // Notify when in stock
    const notifyMeBtn = document.getElementById('notifyMeBtn');
    if (notifyMeBtn) {
        notifyMeBtn.addEventListener('click', function() {
            const productId = this.dataset.id;
            notifyWhenInStock(productId);
        });
    }
});

// Remove image preview
function removeImagePreview(button) {
    const previewItem = button.closest('.image-preview-item');
    const col = previewItem.closest('.col-4');
    col.remove();
    
    // Check if any previews left
    const previewContainer = document.getElementById('previewContainer');
    if (previewContainer.children.length === 0) {
        const uploadPlaceholder = document.querySelector('.upload-placeholder');
        const imagePreview = document.getElementById('imagePreview');
        
        uploadPlaceholder.classList.remove('d-none');
        imagePreview.classList.add('d-none');
    }
}

// Add to wishlist function
function addToWishlist(productId) {
    if (!isLoggedIn()) {
        showNotification('Vui lòng đăng nhập để sử dụng tính năng này!', 'warning');
        return;
    }
    
    fetch('../api/wishlist/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã thêm vào danh sách yêu thích!', 'success');
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối!', 'error');
    });
}

// Notify when in stock function
function notifyWhenInStock(productId) {
    const email = prompt('Vui lòng nhập email của bạn để nhận thông báo khi có hàng:');
    
    if (!email) return;
    
    if (!validateEmail(email)) {
        showNotification('Email không hợp lệ!', 'error');
        return;
    }
    
    fetch('../api/products/notify.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã đăng ký nhận thông báo thành công!', 'success');
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối!', 'error');
    });
}

// Submit review form
document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!isLoggedIn()) {
        showNotification('Vui lòng đăng nhập để viết đánh giá!', 'warning');
        return;
    }
    
    const productId = document.getElementById('productId').value;
    const rating = document.getElementById('rating').value;
    const comment = document.getElementById('comment').value;
    const files = document.getElementById('reviewImages').files;
    
    if (!comment.trim()) {
        showNotification('Vui lòng nhập nội dung đánh giá!', 'error');
        return;
    }
    
    // Create form data for file upload
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('rating', rating);
    formData.append('comment', comment);
    
    // Add image files
    for (let i = 0; i < files.length; i++) {
        formData.append('review_images[]', files[i]);
    }
    
    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi...';
    submitBtn.disabled = true;
    
    fetch('../api/reviews/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đánh giá của bạn đã được gửi thành công!', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('writeReviewModal'));
            modal.hide();
            
            // Reset form
            document.getElementById('reviewForm').reset();
            document.getElementById('rating').value = '5';
            updateRatingStars(5);
            
            // Reload page after 2 seconds to show new review
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối!', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Helper function to check if user is logged in
function isLoggedIn() {
    return <?php echo Functions::isLoggedIn() ? 'true' : 'false'; ?>;
}

// Helper function to validate email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Lightbox for review images
if (typeof lightbox !== 'undefined') {
    lightbox.option({
        'resizeDuration': 200,
        'wrapAround': true,
        'albumLabel': "Hình %1 của %2"
    });
}
</script>

<style>
/* Product detail page styles */
.product-images .thumbnail-item {
    transition: all 0.3s ease;
}

.product-images .thumbnail-item:hover,
.product-images .thumbnail-item.active {
    border-color: var(--primary-color) !important;
    transform: scale(1.05);
}

.product-info .stars {
    color: #ffc107;
}

.rating-input .rating-star {
    transition: all 0.3s ease;
}

.rating-input .rating-star:hover {
    transform: scale(1.2);
}

.image-upload-area {
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.image-upload-area:hover {
    border-color: var(--primary-color);
    background-color: rgba(25, 135, 84, 0.05);
}

.image-preview-item {
    transition: all 0.3s ease;
}

.image-preview-item:hover {
    transform: scale(1.05);
}

.image-preview-item button {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-preview-item:hover button {
    opacity: 1;
}

.review-item {
    transition: all 0.3s ease;
}

.review-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.review-reply {
    border-left: 4px solid var(--primary-color);
}

.rating-bar .progress {
    background-color: #e9ecef;
}

.rating-bar .progress-bar {
    transition: width 0.6s ease;
}

/* Tab styles */
.nav-tabs .nav-link {
    color: var(--secondary-color);
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border: none;
    border-bottom: 3px solid transparent;
}

.nav-tabs .nav-link:hover {
    color: var(--primary-color);
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    background-color: transparent;
    border-color: var(--primary-color);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-header {
        text-align: center;
    }
    
    .add-to-cart .row {
        justify-content: center;
    }
    
    .product-actions .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
    
    .product-actions .social-share {
        order: 2;
    }
    
    .product-actions .ms-auto {
        order: 1;
        width: 100%;
    }
    
    .product-actions .btn {
        width: 100%;
    }
}
</style>

<?php
require_once '../includes/footer.php';
?>