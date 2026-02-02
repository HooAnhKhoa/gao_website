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
$category_id = $_GET['id'] ?? 0;

if ($action == 'delete' && $category_id > 0) {
    // Check if category has products
    $productCount = $db->selectOne(
        "SELECT COUNT(*) as total FROM products WHERE category_id = ?", 
        [$category_id]
    )['total'];
    
    if ($productCount > 0) {
        $_SESSION['flash_message'] = [
            'type' => 'error', 
            'text' => "Không thể xóa danh mục vì còn $productCount sản phẩm thuộc danh mục này"
        ];
    } else {
        // Check if category has subcategories
        $subcategoryCount = $db->selectOne(
            "SELECT COUNT(*) as total FROM categories WHERE parent_id = ?", 
            [$category_id]
        )['total'];
        
        if ($subcategoryCount > 0) {
            $_SESSION['flash_message'] = [
                'type' => 'error', 
                'text' => "Không thể xóa danh mục vì còn $subcategoryCount danh mục con"
            ];
        } else {
            // Get category info to delete image
            $category = $db->selectOne("SELECT image FROM categories WHERE id = ?", [$category_id]);
            
            // Delete category
            $deleted = $db->delete('categories', 'id = ?', [$category_id]);
            
            if ($deleted) {
                // Delete image file if exists
                if ($category && $category['image']) {
                    $imagePath = '../assets/images/categories/' . $category['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $_SESSION['flash_message'] = [
                    'type' => 'success', 
                    'text' => 'Đã xóa danh mục thành công!'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'error', 
                    'text' => 'Có lỗi xảy ra khi xóa danh mục'
                ];
            }
        }
    }
    header('Location: categories.php');
    exit;
}

if ($action == 'toggle_status' && $category_id > 0) {
    $category = $db->selectOne("SELECT status FROM categories WHERE id = ?", [$category_id]);
    if ($category) {
        $new_status = $category['status'] == 'active' ? 'inactive' : 'active';
        $db->update('categories', ['status' => $new_status], 'id = ?', [$category_id]);
        
        $status_text = $new_status == 'active' ? 'kích hoạt' : 'vô hiệu hóa';
        $_SESSION['flash_message'] = [
            'type' => 'success', 
            'text' => "Đã $status_text danh mục!"
        ];
    }
    header('Location: categories.php');
    exit;
}

// Get filter parameters
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = "1=1";
$params = [];

if ($filter_status) {
    $where .= " AND c.status = ?";
    $params[] = $filter_status;
}

if ($search) {
    $where .= " AND (c.name LIKE ? OR c.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

// Get categories with product count and parent name
$categories = $db->select("
    SELECT c.*, 
           parent.name as parent_name,
           COUNT(p.id) as product_count,
           COUNT(sub.id) as subcategory_count
    FROM categories c
    LEFT JOIN categories parent ON c.parent_id = parent.id
    LEFT JOIN products p ON c.id = p.category_id
    LEFT JOIN categories sub ON c.id = sub.parent_id
    WHERE $where
    GROUP BY c.id
    ORDER BY c.parent_id IS NULL DESC, parent.name, c.name
", $params);

// NOW include header after all logic is done
$pageTitle = 'Quản lý danh mục - Admin';
require_once 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-tags me-2"></i>Quản lý danh mục
    </h1>
    <a href="category-add.php" class="btn btn-success">
        <i class="fas fa-plus me-1"></i>Thêm danh mục mới
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
            <i class="fas fa-filter me-1"></i>Bộ lọc danh mục
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?php echo $filter_status == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                </select>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Tìm kiếm</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Tên danh mục..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-success" type="submit">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <a href="categories.php" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Làm mới
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Categories Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success">
            <i class="fas fa-list me-1"></i>Danh sách danh mục (<?php echo count($categories); ?>)
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="categoriesTable">
                <thead class="table-success">
                    <tr>
                        <th width="80">Hình ảnh</th>
                        <th>Tên danh mục</th>
                        <th width="150">Danh mục cha</th>
                        <th width="100">Sản phẩm</th>
                        <th width="100">Danh mục con</th>
                        <th width="100">Trạng thái</th>
                        <th width="150">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <?php if ($category['image']): ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/categories/<?php echo $category['image']; ?>" 
                                 class="img-thumbnail" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>"
                                 style="width: 60px; height: 60px; object-fit: cover;"
                                 onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.svg'">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px; border-radius: 8px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                            <?php if ($category['description']): ?>
                            <div class="small text-muted">
                                <?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?>
                                <?php if (strlen($category['description']) > 100) echo '...'; ?>
                            </div>
                            <?php endif; ?>
                            <div class="small text-info">
                                Slug: <?php echo $category['slug']; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($category['parent_name']): ?>
                            <span class="badge bg-info"><?php echo htmlspecialchars($category['parent_name']); ?></span>
                            <?php else: ?>
                            <span class="text-muted">Danh mục gốc</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($category['product_count'] > 0): ?>
                            <span class="badge bg-primary"><?php echo $category['product_count']; ?></span>
                            <?php else: ?>
                            <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($category['subcategory_count'] > 0): ?>
                            <span class="badge bg-secondary"><?php echo $category['subcategory_count']; ?></span>
                            <?php else: ?>
                            <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($category['status'] == 'active'): ?>
                            <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Không hoạt động</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="category-edit.php?id=<?php echo $category['id']; ?>" 
                                   class="btn btn-warning" 
                                   title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="categories.php?action=toggle_status&id=<?php echo $category['id']; ?>" 
                                   class="btn btn-<?php echo $category['status'] == 'active' ? 'secondary' : 'success'; ?>"
                                   title="<?php echo $category['status'] == 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" 
                                   class="btn btn-danger confirm-delete"
                                   onclick="return confirm('Bạn có chắc muốn xóa danh mục này? Hành động này không thể hoàn tác.')"
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
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#categoriesTable')) {
        $('#categoriesTable').DataTable().destroy();
    }
    
    $('#categoriesTable').DataTable({
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
        },
        responsive: true,
        order: [[1, 'asc']], // Sort by category name
        columnDefs: [
            { orderable: false, targets: [0, 6] } // Disable sorting for image and actions columns
        ]
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>