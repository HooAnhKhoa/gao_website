<?php
require_once __DIR__ . '/../includes/init.php';
$pageTitle = 'Sản phẩm - Danh sách gạo chất lượng';
$pageDescription = 'Khám phá đa dạng các loại gạo: gạo ST25, gạo nếp, gạo lứt, gạo đặc sản vùng miền. Chất lượng cao, giá tốt, giao hàng toàn quốc.';
$showBreadcrumb = true;

// Breadcrumb
$breadcrumbItems = [
    ['text' => 'Trang chủ', 'url' => '../index.php'],
    ['text' => 'Sản phẩm']
];

require_once '../includes/header.php';

$db = Database::getInstance();
$functions = new Functions();

// Get parameters
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;

// Build filter conditions
$where = "p.status = ?";
$params = [PRODUCT_ACTIVE];
$joins = "LEFT JOIN categories c ON p.category_id = c.id";

if (!empty($search)) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($category_id)) {
    $where .= " AND (p.category_id = ? OR c.parent_id = ?)";
    $params[] = $category_id;
    $params[] = $category_id;
    
    // Get category info for breadcrumb
    $category = $db->selectOne("SELECT * FROM categories WHERE id = ?", [$category_id]);
    if ($category) {
        $breadcrumbItems[] = ['text' => $category['name']];
        $pageTitle = $category['name'] . ' - Gạo Ngon';
    }
}

// Build sort conditions
$orderBy = "ORDER BY ";
switch ($sort) {
    case 'price_asc':
        $orderBy .= "COALESCE(p.sale_price, p.price) ASC";
        break;
    case 'price_desc':
        $orderBy .= "COALESCE(p.sale_price, p.price) DESC";
        break;
    case 'name':
        $orderBy .= "p.name ASC";
        break;
    case 'popular':
        $orderBy .= "p.total_reviews DESC, p.rating DESC";
        break;
    case 'featured':
        $orderBy .= "p.featured DESC, p.created_at DESC";
        break;
    default: // newest
        $orderBy .= "p.created_at DESC";
        break;
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM products p {$joins} WHERE {$where}";
$totalResult = $db->selectOne($countQuery, $params);
$totalProducts = $totalResult['total'];

// Calculate pagination
$pagination = $functions->paginate($totalProducts, $page, $limit);
$offset = $pagination['offset'];

// Get products
$productsQuery = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  {$joins} 
                  WHERE {$where} 
                  {$orderBy} 
                  LIMIT ? OFFSET ?";

$paramsWithLimit = array_merge($params, [$limit, $offset]);
$products = $db->select($productsQuery, $paramsWithLimit);

// Get all categories for filter
$categories = $db->select(
    "SELECT * FROM categories WHERE status = 'active' ORDER BY name"
);

// Get featured categories
$featuredCategories = $db->select(
    "SELECT * FROM categories WHERE status = 'active' AND parent_id IS NULL LIMIT 6"
);
?>

<!-- Page Header -->
<div class="page-header bg-light py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">
                    <?php echo !empty($category) ? $category['name'] : 'Tất cả sản phẩm'; ?>
                </h1>
                <p class="lead mb-0">
                    <?php echo !empty($category) ? 
                        htmlspecialchars($category['description'] ?? 'Khám phá các sản phẩm gạo chất lượng') : 
                        'Khám phá đa dạng các loại gạo chất lượng cao từ khắp mọi miền đất nước'; ?>
                </p>
            </div>
            <div class="col-lg-4">
                <div class="text-lg-end">
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-box me-1"></i>
                        <?php echo $totalProducts; ?> sản phẩm
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h5>
                </div>
                
                <!-- Search Filter -->
                <div class="card-body border-bottom">
                    <form method="get" action="" class="filter-form">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_id); ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tìm kiếm</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Tên sản phẩm...">
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Category Filter -->
                <div class="card-body border-bottom">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-list me-2"></i>Danh mục
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="?category=" 
                               class="d-flex justify-content-between align-items-center text-decoration-none 
                                      <?php echo empty($category_id) ? 'text-success fw-bold' : 'text-dark'; ?>">
                                <span>Tất cả sản phẩm</span>
                                <span class="badge bg-light text-dark"><?php echo $totalProducts; ?></span>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): 
                            $productCount = $db->selectOne(
                                "SELECT COUNT(*) as count FROM products WHERE category_id = ? AND status = ?",
                                [$cat['id'], PRODUCT_ACTIVE]
                            )['count'];
                        ?>
                        <li class="mb-2">
                            <a href="?category=<?php echo $cat['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="d-flex justify-content-between align-items-center text-decoration-none 
                                      <?php echo $category_id == $cat['id'] ? 'text-success fw-bold' : 'text-dark'; ?>">
                                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                <span class="badge bg-light text-dark"><?php echo $productCount; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Price Range Filter -->
                <div class="card-body border-bottom">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-tag me-2"></i>Khoảng giá
                    </h6>
                    <div class="price-filter">
                        <div class="mb-3">
                            <label class="form-label">Từ: <span id="priceMinValue">0</span>đ</label>
                            <input type="range" class="form-range" min="0" max="500000" step="10000" 
                                   id="priceMin" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Đến: <span id="priceMaxValue">500.000</span>đ</label>
                            <input type="range" class="form-range" min="10000" max="1000000" step="10000" 
                                   id="priceMax" value="500000">
                        </div>
                        <button class="btn btn-outline-success w-100" id="applyPriceFilter">
                            <i class="fas fa-check me-1"></i>Áp dụng
                        </button>
                    </div>
                </div>
                
                <!-- Featured Categories -->
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-star me-2"></i>Danh mục nổi bật
                    </h6>
                    <div class="row g-2">
                        <?php foreach ($featuredCategories as $cat): ?>
                        <div class="col-6">
                            <a href="?category=<?php echo $cat['id']; ?>" 
                               class="text-decoration-none">
                                <div class="featured-category p-2 text-center border rounded hover-shadow">
                                    <i class="fas fa-seedling text-success mb-2"></i>
                                    <div class="small fw-bold"><?php echo htmlspecialchars($cat['name']); ?></div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Section -->
        <div class="col-lg-9">
            <!-- Products Header -->
            <div class="products-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="sort-by">
                            <form method="get" class="d-inline">
                                <?php if ($search): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                                <?php if ($category_id): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_id); ?>">
                                <?php endif; ?>
                                <div class="input-group">
                                    <label class="input-group-text" for="sortSelect">
                                        <i class="fas fa-sort-amount-down me-1"></i>Sắp xếp
                                    </label>
                                    <select class="form-select" id="sortSelect" name="sort" onchange="this.form.submit()">
                                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                        <option value="featured" <?php echo $sort == 'featured' ? 'selected' : ''; ?>>Nổi bật</option>
                                        <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Phổ biến</option>
                                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá: Thấp đến cao</option>
                                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá: Cao đến thấp</option>
                                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Tên: A-Z</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="view-options text-md-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary active" id="gridView">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="listView">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid/List -->
            <div id="productsContainer" class="products-container <?php echo $totalProducts > 0 ? 'grid-view' : ''; ?>">
                <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                        <h3 class="mb-3">Không tìm thấy sản phẩm</h3>
                        <p class="text-muted mb-4">Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                        <a href="products.php" class="btn btn-success">
                            <i class="fas fa-redo me-1"></i>Xem tất cả sản phẩm
                        </a>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach ($products as $product): 
                        $discount = $functions->calculateDiscount($product['price'], $product['sale_price']);
                        $currentPrice = $product['sale_price'] ?: $product['price'];
                    ?>
                    <div class="product-item">
                        <div class="card product-card border-0 shadow-sm h-100">
                            <!-- Product Image -->
                            <div class="product-image position-relative overflow-hidden">
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
                                
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                    <img src="../assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                </a>
                                
                                <div class="product-actions position-absolute bottom-0 start-0 w-100 p-3 bg-gradient">
                                    <div class="d-flex justify-content-center">
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                        <button class="btn btn-success btn-sm add-to-cart-btn me-2" 
                                                data-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-secondary btn-sm quick-view-btn"
                                                data-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Product Info -->
                            <div class="card-body">
                                <div class="product-category small text-muted mb-1">
                                    <a href="?category=<?php echo $product['category_id']; ?>" 
                                       class="text-decoration-none">
                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                    </a>
                                </div>
                                
                                <h5 class="product-title">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
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
                                
                                <!-- Rating -->
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
                                
                                <!-- Description -->
                                <p class="product-description small text-muted mb-3 d-none d-md-block">
                                    <?php echo htmlspecialchars(substr($product['short_description'] ?? $product['description'] ?? '', 0, 80)); ?>...
                                </p>
                                
                                <!-- Meta -->
                                <div class="product-meta d-flex justify-content-between">
                                    <span class="stock-status small">
                                        <?php if ($product['stock_quantity'] > 10): ?>
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            <span class="text-success">Còn hàng</span>
                                        <?php elseif ($product['stock_quantity'] > 0): ?>
                                            <i class="fas fa-exclamation-circle text-warning me-1"></i>
                                            <span class="text-warning">Sắp hết</span>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle text-danger me-1"></i>
                                            <span class="text-danger">Hết hàng</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="origin small text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($product['origin']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="card-footer bg-transparent border-top-0 pt-0">
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
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <li class="page-item <?php echo !$pagination['has_prev'] ? 'disabled' : ''; ?>">
                        <a class="page-link" 
                           href="?page=<?php echo $pagination['prev_page']; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php 
                    $startPage = max(1, $pagination['current_page'] - 2);
                    $endPage = min($pagination['total_pages'], $startPage + 4);
                    
                    if ($endPage - $startPage < 4) {
                        $startPage = max(1, $endPage - 4);
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                    <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                        <a class="page-link" 
                           href="?page=<?php echo $i; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <!-- Next Page -->
                    <li class="page-item <?php echo !$pagination['has_next'] ? 'disabled' : ''; ?>">
                        <a class="page-link" 
                           href="?page=<?php echo $pagination['next_page']; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
                
                <!-- Page Info -->
                <div class="text-center text-muted mt-2">
                    Hiển thị <?php echo min($pagination['items_per_page'], count($products)); ?> 
                    trên <?php echo $pagination['total_items']; ?> sản phẩm
                </div>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem nhanh sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="quickViewContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// View toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const gridViewBtn = document.getElementById('gridView');
    const listViewBtn = document.getElementById('listView');
    const productsContainer = document.getElementById('productsContainer');
    
    // Set initial state
    updateViewButtons();
    
    // Grid view
    gridViewBtn.addEventListener('click', function() {
        productsContainer.classList.remove('list-view');
        productsContainer.classList.add('grid-view');
        updateViewButtons();
        saveViewPreference('grid');
    });
    
    // List view
    listViewBtn.addEventListener('click', function() {
        productsContainer.classList.remove('grid-view');
        productsContainer.classList.add('list-view');
        updateViewButtons();
        saveViewPreference('list');
    });
    
    function updateViewButtons() {
        if (productsContainer.classList.contains('grid-view')) {
            gridViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
        } else {
            gridViewBtn.classList.remove('active');
            listViewBtn.classList.add('active');
        }
    }
    
    function saveViewPreference(view) {
        localStorage.setItem('productView', view);
    }
    
    // Load saved preference
    const savedView = localStorage.getItem('productView') || 'grid';
    if (savedView === 'list') {
        productsContainer.classList.remove('grid-view');
        productsContainer.classList.add('list-view');
    }
    updateViewButtons();
    
    // Price range filter
    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    const priceMinValue = document.getElementById('priceMinValue');
    const priceMaxValue = document.getElementById('priceMaxValue');
    const applyPriceFilter = document.getElementById('applyPriceFilter');
    
    if (priceMin && priceMax) {
        // Format price display
        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(price);
        }
        
        priceMinValue.textContent = formatPrice(priceMin.value);
        priceMaxValue.textContent = formatPrice(priceMax.value);
        
        priceMin.addEventListener('input', function() {
            priceMinValue.textContent = formatPrice(this.value);
            if (parseInt(this.value) > parseInt(priceMax.value)) {
                this.value = priceMax.value;
            }
        });
        
        priceMax.addEventListener('input', function() {
            priceMaxValue.textContent = formatPrice(this.value);
            if (parseInt(this.value) < parseInt(priceMin.value)) {
                this.value = priceMin.value;
            }
        });
        
        applyPriceFilter.addEventListener('click', function() {
            const minPrice = priceMin.value;
            const maxPrice = priceMax.value;
            
            // Build URL with price filter
            let url = new URL(window.location.href);
            url.searchParams.set('min_price', minPrice);
            url.searchParams.set('max_price', maxPrice);
            
            window.location.href = url.toString();
        });
    }
    
    // Quick view functionality
    document.querySelectorAll('.quick-view-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            loadQuickView(productId);
        });
    });
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            addToCart(productId);
        });
    });
});

function loadQuickView(productId) {
    fetch(`../api/products/detail.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                const discount = calculateDiscount(product.price, product.sale_price);
                const currentPrice = product.sale_price || product.price;
                
                const modalContent = `
                    <div class="row">
                        <div class="col-md-6">
                            <img src="../assets/images/products/${product.image || 'default.jpg'}" 
                                 class="img-fluid rounded" 
                                 alt="${product.name}">
                        </div>
                        <div class="col-md-6">
                            <h4 class="fw-bold">${product.name}</h4>
                            <div class="mb-3">
                                <span class="text-success fw-bold fs-4">${formatPrice(currentPrice)}</span>
                                ${product.sale_price ? 
                                    `<span class="text-muted text-decoration-line-through ms-2">${formatPrice(product.price)}</span>` : 
                                    ''
                                }
                                ${discount ? 
                                    `<span class="badge bg-danger ms-2">-${discount}</span>` : 
                                    ''
                                }
                            </div>
                            
                            <div class="mb-3">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-weight me-1"></i>${product.weight}kg
                                </span>
                                <span class="badge bg-light text-dark ms-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>${product.origin}
                                </span>
                            </div>
                            
                            <p class="text-muted mb-4">${product.short_description || product.description || ''}</p>
                            
                            <div class="mb-4">
                                <h6>Thông tin dinh dưỡng:</h6>
                                ${product.nutritional_info ? 
                                    `<p class="small">${product.nutritional_info}</p>` : 
                                    '<p class="small text-muted">Đang cập nhật...</p>'
                                }
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="quantity-selector me-3">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="decreaseQuantity()">-</button>
                                    <input type="number" id="quickViewQuantity" value="1" min="1" max="${product.stock_quantity}" 
                                           class="form-control text-center d-inline-block" style="width: 60px;">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="increaseQuantity()">+</button>
                                </div>
                                <span class="text-muted small">
                                    Còn ${product.stock_quantity} sản phẩm
                                </span>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-success" onclick="addToCartFromQuickView(${product.id})">
                                    <i class="fas fa-cart-plus me-1"></i>Thêm vào giỏ hàng
                                </button>
                                <a href="product-detail.php?id=${product.id}" class="btn btn-outline-success">
                                    <i class="fas fa-info-circle me-1"></i>Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('quickViewContent').innerHTML = modalContent;
                const quickViewModal = new bootstrap.Modal(document.getElementById('quickViewModal'));
                quickViewModal.show();
            }
        })
        .catch(error => {
            console.error('Error loading quick view:', error);
            showNotification('Lỗi tải thông tin sản phẩm!', 'error');
        });
}

function calculateDiscount(price, salePrice) {
    if (salePrice && salePrice < price) {
        const discount = Math.round(((price - salePrice) / price) * 100);
        return discount + '%';
    }
    return null;
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
}

function increaseQuantity() {
    const input = document.getElementById('quickViewQuantity');
    const max = parseInt(input.max);
    if (input.value < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quickViewQuantity');
    if (input.value > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function addToCartFromQuickView(productId) {
    const quantity = document.getElementById('quickViewQuantity').value;
    addToCart(productId, parseInt(quantity));
    
    // Close modal after adding to cart
    const modal = bootstrap.Modal.getInstance(document.getElementById('quickViewModal'));
    modal.hide();
}
</script>

<style>
/* Grid/List view styles */
.products-container {
    display: grid;
    gap: 1.5rem;
}

.products-container.grid-view {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}

.products-container.list-view .product-item {
    width: 100%;
}

.products-container.list-view .product-card {
    display: flex;
    flex-direction: row;
}

.products-container.list-view .product-image {
    flex: 0 0 250px;
    height: 250px;
}

.products-container.list-view .card-body {
    flex: 1;
}

.products-container.list-view .product-description {
    display: block !important;
}

/* Price range slider */
.form-range::-webkit-slider-thumb {
    background-color: var(--primary-color);
}

.form-range::-moz-range-thumb {
    background-color: var(--primary-color);
}

/* Featured categories hover effect */
.hover-shadow {
    transition: var(--transition);
}

.hover-shadow:hover {
    box-shadow: var(--box-shadow);
    transform: translateY(-2px);
}

/* Gradient overlay for product actions */
.bg-gradient {
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
}

/* Empty state */
.empty-state {
    max-width: 500px;
    margin: 0 auto;
}

/* Pagination active state */
.page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .products-container.grid-view {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .products-container.list-view .product-card {
        flex-direction: column;
    }
    
    .products-container.list-view .product-image {
        flex: 0 0 auto;
        height: 200px;
    }
}

@media (max-width: 576px) {
    .products-container.grid-view {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once '../includes/footer.php';
?>