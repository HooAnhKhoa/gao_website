<?php
require_once '../includes/init.php';

// Check admin authentication
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Get category ID
$category_id = $_GET['id'] ?? 0;

if (!$category_id) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Không tìm thấy danh mục'];
    header('Location: categories.php');
    exit;
}

// Get category data
$category = $db->selectOne("SELECT * FROM categories WHERE id = ?", [$category_id]);

if (!$category) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Danh mục không tồn tại'];
    header('Location: categories.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $status = $_POST['status'] ?? 'active';
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Tên danh mục là bắt buộc';
    }
    
    if (strlen($name) > 100) {
        $errors[] = 'Tên danh mục không được vượt quá 100 ký tự';
    }
    
    // Check if trying to set parent as itself or its child
    if ($parent_id == $category_id) {
        $errors[] = 'Không thể đặt danh mục làm cha của chính nó';
    }
    
    if ($parent_id) {
        // Check if parent_id is a child of current category
        $childCategories = $db->select("SELECT id FROM categories WHERE parent_id = ?", [$category_id]);
        $childIds = array_column($childCategories, 'id');
        if (in_array($parent_id, $childIds)) {
            $errors[] = 'Không thể đặt danh mục con làm danh mục cha';
        }
    }
    
    // Check duplicate name (exclude current category)
    $existingCategory = $db->selectOne(
        "SELECT id FROM categories WHERE name = ? AND id != ?", 
        [$name, $category_id]
    );
    if ($existingCategory) {
        $errors[] = 'Tên danh mục đã tồn tại';
    }
    
    // Generate slug if name changed
    $slug = $category['slug'];
    if ($name !== $category['name']) {
        $slug = Functions::createSlug($name);
        
        // Check duplicate slug (exclude current category)
        $existingSlug = $db->selectOne(
            "SELECT id FROM categories WHERE slug = ? AND id != ?", 
            [$slug, $category_id]
        );
        if ($existingSlug) {
            $slug = $slug . '-' . time();
        }
    }
    
    // Handle image upload
    $imageName = $category['image']; // Keep current image by default
    $deleteOldImage = false;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $newImageName = Functions::uploadImage($_FILES['image'], '../assets/images/categories/');
            $imageName = $newImageName;
            $deleteOldImage = true; // Mark old image for deletion
        } catch (Exception $e) {
            $errors[] = 'Lỗi upload hình ảnh: ' . $e->getMessage();
        }
    }
    
    // Handle remove image
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        $deleteOldImage = true;
        $imageName = null;
    }
    
    // If no errors, update category
    if (empty($errors)) {
        try {
            $categoryData = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'parent_id' => $parent_id,
                'image' => $imageName,
                'status' => $status
            ];
            
            $updated = $db->update('categories', $categoryData, 'id = ?', [$category_id]);
            
            if ($updated) {
                // Delete old image if needed
                if ($deleteOldImage && $category['image'] && $category['image'] !== $imageName) {
                    $oldImagePath = '../assets/images/categories/' . $category['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                
                $_SESSION['flash_message'] = [
                    'type' => 'success', 
                    'text' => 'Cập nhật danh mục thành công!'
                ];
                header('Location: categories.php');
                exit;
            } else {
                $errors[] = 'Có lỗi xảy ra khi cập nhật danh mục';
            }
        } catch (Exception $e) {
            $errors[] = 'Lỗi database: ' . $e->getMessage();
        }
    }
}

// Get parent categories for dropdown (exclude current category and its children)
$excludeIds = [$category_id];

// Get all children of current category
$children = $db->select("SELECT id FROM categories WHERE parent_id = ?", [$category_id]);
foreach ($children as $child) {
    $excludeIds[] = $child['id'];
}

$excludeIdsStr = implode(',', $excludeIds);
$parentCategories = $db->select(
    "SELECT id, name FROM categories 
     WHERE parent_id IS NULL 
     AND status = 'active' 
     AND id NOT IN ($excludeIdsStr)
     ORDER BY name"
);

// NOW include header after all logic is done
$pageTitle = 'Chỉnh sửa danh mục - Admin';
require_once 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-edit me-2"></i>Chỉnh sửa danh mục: <?php echo htmlspecialchars($category['name']); ?>
    </h1>
    <div>
        <a href="categories.php" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
        <a href="category-add.php" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Thêm mới
        </a>
    </div>
</div>

<!-- Display errors -->
<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Category Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Thông tin danh mục</h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? $category['name']); ?>" 
                                       required maxlength="100">
                                <div class="form-text">Tên danh mục sẽ hiển thị trên website</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo ($_POST['status'] ?? $category['status']) == 'active' ? 'selected' : ''; ?>>
                                        Hoạt động
                                    </option>
                                    <option value="inactive" <?php echo ($_POST['status'] ?? $category['status']) == 'inactive' ? 'selected' : ''; ?>>
                                        Không hoạt động
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Danh mục cha</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">-- Danh mục gốc --</option>
                            <?php foreach ($parentCategories as $parent): ?>
                            <option value="<?php echo $parent['id']; ?>" 
                                    <?php echo ($_POST['parent_id'] ?? $category['parent_id']) == $parent['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($parent['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Để trống nếu đây là danh mục gốc</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? $category['description']); ?></textarea>
                        <div class="form-text">Mô tả ngắn về danh mục này</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hình ảnh danh mục</label>
                        
                        <?php if ($category['image']): ?>
                        <div class="current-image mb-3">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo SITE_URL; ?>/assets/images/categories/<?php echo $category['image']; ?>" 
                                     alt="Current image" class="img-thumbnail me-3" style="max-width: 150px;">
                                <div>
                                    <p class="mb-2"><strong>Hình ảnh hiện tại:</strong> <?php echo $category['image']; ?></p>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                        <label class="form-check-label text-danger" for="remove_image">
                                            Xóa hình ảnh hiện tại
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <input type="file" class="form-control" id="image" name="image" 
                               accept="image/jpeg,image/jpg,image/png,image/gif">
                        <div class="form-text">
                            <?php if ($category['image']): ?>
                            Chọn hình ảnh mới để thay thế hình ảnh hiện tại.
                            <?php else: ?>
                            Chọn hình ảnh đại diện cho danh mục.
                            <?php endif; ?>
                            Định dạng: JPG, PNG, GIF. Kích thước tối đa: 5MB
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Cập nhật danh mục
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Card -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Xem trước</h6>
            </div>
            <div class="card-body">
                <div id="preview-card" class="text-center">
                    <div id="preview-image" class="mb-3">
                        <?php if ($category['image']): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/categories/<?php echo $category['image']; ?>" 
                             alt="Preview" class="img-fluid rounded" 
                             style="max-height: 150px; width: 100%; object-fit: cover;">
                        <?php else: ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/no-image.svg" 
                             alt="Preview" class="img-fluid rounded" 
                             style="max-height: 150px; width: 100%; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                    <h5 id="preview-name" class="card-title text-success"><?php echo htmlspecialchars($category['name']); ?></h5>
                    <p id="preview-description" class="card-text text-muted">
                        <?php echo htmlspecialchars($category['description'] ?: 'Mô tả danh mục sẽ hiển thị ở đây'); ?>
                    </p>
                    <span id="preview-status" class="badge bg-<?php echo $category['status'] == 'active' ? 'success' : 'secondary'; ?>">
                        <?php echo $category['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Thông tin danh mục</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <strong>ID:</strong> <?php echo $category['id']; ?>
                    </li>
                    <li class="mb-2">
                        <strong>Slug:</strong> <code><?php echo $category['slug']; ?></code>
                    </li>
                    <li class="mb-2">
                        <strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($category['created_at'])); ?>
                    </li>
                    <?php
                    // Get product count
                    $productCount = $db->selectOne("SELECT COUNT(*) as total FROM products WHERE category_id = ?", [$category_id])['total'];
                    $subcategoryCount = $db->selectOne("SELECT COUNT(*) as total FROM categories WHERE parent_id = ?", [$category_id])['total'];
                    ?>
                    <li class="mb-2">
                        <strong>Số sản phẩm:</strong> 
                        <span class="badge bg-primary"><?php echo $productCount; ?></span>
                    </li>
                    <li class="mb-0">
                        <strong>Danh mục con:</strong> 
                        <span class="badge bg-secondary"><?php echo $subcategoryCount; ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Actions Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Hành động khác</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="products.php?category=<?php echo $category_id; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-boxes me-1"></i>Xem sản phẩm (<?php echo $productCount; ?>)
                    </a>
                    <?php if ($subcategoryCount > 0): ?>
                    <a href="categories.php?parent=<?php echo $category_id; ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sitemap me-1"></i>Xem danh mục con (<?php echo $subcategoryCount; ?>)
                    </a>
                    <?php endif; ?>
                    <a href="category-add.php?parent=<?php echo $category_id; ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Thêm danh mục con
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for jQuery to be loaded
$(document).ready(function() {
    // Real-time preview
    $('#name').on('input', function() {
        const name = $(this).val() || '<?php echo htmlspecialchars($category['name']); ?>';
        $('#preview-name').text(name);
    });

    $('#description').on('input', function() {
        const description = $(this).val() || 'Mô tả danh mục sẽ hiển thị ở đây';
        $('#preview-description').text(description);
    });

    $('#status').on('change', function() {
        const status = $(this).val();
        const statusBadge = $('#preview-status');
        
        if (status === 'active') {
            statusBadge.removeClass('bg-secondary').addClass('bg-success').text('Hoạt động');
        } else {
            statusBadge.removeClass('bg-success').addClass('bg-secondary').text('Không hoạt động');
        }
    });

    // Image preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-image img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove image checkbox
    $('#remove_image').on('change', function() {
        if (this.checked) {
            $('#preview-image img').attr('src', '<?php echo SITE_URL; ?>/assets/images/no-image.svg');
        } else {
            $('#preview-image img').attr('src', '<?php echo SITE_URL; ?>/assets/images/categories/<?php echo $category['image']; ?>');
        }
    });

    // Form validation
    $('form').on('submit', function(e) {
        const name = $('#name').val().trim();
        
        if (!name) {
            e.preventDefault();
            showNotification('Vui lòng nhập tên danh mục', 'error');
            $('#name').focus();
            return false;
        }

        if (name.length > 100) {
            e.preventDefault();
            showNotification('Tên danh mục không được vượt quá 100 ký tự', 'error');
            $('#name').focus();
            return false;
        }

        // Show loading
        $(this).find('button[type="submit"]').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin me-1"></i>Đang cập nhật...'
        );
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>