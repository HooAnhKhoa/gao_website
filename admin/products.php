<?php
require_once '../includes/init.php';

// Check admin authentication
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Handle actions BEFORE any output
$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? 0;

if ($action == 'delete' && $product_id > 0) {
    $db->delete('products', 'id = ?', [$product_id]);
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Đã xóa sản phẩm thành công!'];
    header('Location: products.php');
    exit;
}

if ($action == 'toggle_status' && $product_id > 0) {
    $product = $db->selectOne("SELECT status FROM products WHERE id = ?", [$product_id]);
    if ($product) {
        $new_status = $product['status'] == 'active' ? 'inactive' : 'active';
        $db->update('products', ['status' => $new_status], 'id = ?', [$product_id]);
        
        $status_text = $new_status == 'active' ? 'kích hoạt' : 'vô hiệu hóa';
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Đã $status_text sản phẩm!"];
    }
    header('Location: products.php');
    exit;
}

// Get filter parameters
$filter_category = $_GET['category'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = "1=1";
$params = [];

if ($filter_category) {
    $where .= " AND p.category_id = ?";
    $params[] = $filter_category;
}

if ($filter_status) {
    $where .= " AND p.status = ?";
    $params[] = $filter_status;
}

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

// Get categories for filter
$categories = $db->select("SELECT * FROM categories WHERE status = 'active' ORDER BY name");

// Get products
$products = $db->select(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE $where 
     ORDER BY p.created_at DESC",
    $params
);

// NOW include header after all logic is done
$pageTitle = 'Quản lý sản phẩm - Admin';
require_once 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-boxes me-2"></i>Quản lý sản phẩm
    </h1>
    <a href="product-add.php" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Thêm sản phẩm mới
    </a>
</div>

<!-- Flash Message -->
<?php if (isset($_SESSION['flash_message'])): ?>
<div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show">
    <?php echo $_SESSION['flash_message']['text']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_message']); endif; ?>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success">
            <i class="fas fa-filter me-1"></i>Bộ lọc sản phẩm
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Danh mục</label>
                <select class="form-select" name="category">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" 
                        <?php echo $filter_category == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Đang bán</option>
                    <option value="inactive" <?php echo $filter_status == 'inactive' ? 'selected' : ''; ?>>Ngừng bán</option>
                </select>
            </div>
            
            <div class="col-md-5">
                <label class="form-label">Tìm kiếm</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Tên sản phẩm..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-success" type="submit">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Products Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success">
            <i class="fas fa-box me-1"></i>Danh sách sản phẩm (<?php echo count($products); ?>)
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="productsTable">
                <thead class="table-success">
                    <tr>
                        <th width="80">Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th width="150">Danh mục</th>
                        <th width="120">Giá</th>
                        <th width="80">Tồn kho</th>
                        <th width="100">Trạng thái</th>
                        <th width="150">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): 
                        $currentPrice = $product['sale_price'] ?: $product['price'];
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                                 class="img-thumbnail" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 60px; height: 60px; object-fit: cover;"
                                 onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.svg'">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <div class="small text-muted">
                                Mã: SP<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?>
                            </div>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Chưa phân loại'); ?>
                        </td>
                        <td>
                            <div class="fw-bold text-success">
                                <?php echo Functions::formatPrice($currentPrice); ?>
                            </div>
                            <?php if ($product['sale_price']): ?>
                            <div class="small text-muted text-decoration-line-through">
                                <?php echo Functions::formatPrice($product['price']); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($product['stock_quantity'] > 10): ?>
                            <span class="badge bg-success">
                                <?php echo $product['stock_quantity']; ?>
                            </span>
                            <?php elseif ($product['stock_quantity'] > 0): ?>
                            <span class="badge bg-warning">
                                <?php echo $product['stock_quantity']; ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-danger">Hết hàng</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusInfo = Functions::getStatusLabel($product['status'], 'product');
                            ?>
                            <span class="badge <?php echo $statusInfo['class']; ?>">
                                <?php echo $statusInfo['label']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-warning" 
                                   title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="products.php?action=toggle_status&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-<?php echo $product['status'] == 'active' ? 'secondary' : 'success'; ?>"
                                   title="<?php echo $product['status'] == 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-danger confirm-delete"
                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')"
                                   title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Wait for jQuery to be loaded
$(document).ready(function() {
    // Initialize DataTable - simple version to avoid conflicts
    if ($.fn.DataTable.isDataTable('#productsTable')) {
        $('#productsTable').DataTable().destroy();
    }
    
    $('#productsTable').DataTable({
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
        },
        responsive: true,
        order: [[1, 'asc']] // Sort by product name
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>