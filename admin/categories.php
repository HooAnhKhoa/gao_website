<?php
// admin/categories.php
require_once '../includes/init.php';

// Check admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$db = Database::getInstance();

// Xử lý Xóa danh mục
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Kiểm tra xem danh mục có sản phẩm con không
    $check = $db->selectOne("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$id]);
    if ($check['count'] > 0) {
        Functions::showMessage('error', 'Không thể xóa danh mục đang chứa sản phẩm!');
    } else {
        $db->delete('categories', "id = ?", [$id]);
        Functions::showMessage('success', 'Đã xóa danh mục thành công!');
    }
    header('Location: categories.php');
    exit;
}

$pageTitle = 'Quản lý danh mục';
require_once 'includes/header.php';

// Lấy danh sách danh mục (kèm tên danh mục cha)
$categories = $db->select("
    SELECT c.*, p.name as parent_name 
    FROM categories c 
    LEFT JOIN categories p ON c.parent_id = p.id 
    ORDER BY c.parent_id ASC, c.name ASC
");
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Danh mục sản phẩm</h1>
    <a href="category-add.php" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm danh mục
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover data-table" width="100%" cellspacing="0">
                <thead class="table-success">
                    <tr>
                        <th width="50">ID</th>
                        <th width="100">Hình ảnh</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Danh mục cha</th>
                        <th>Trạng thái</th>
                        <th width="120">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td class="text-center">
                            <?php if ($cat['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/categories/<?php echo $cat['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($cat['name']); ?>" 
                                     style="height: 40px; object-fit: cover;">
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-image"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?php echo htmlspecialchars($cat['name']); ?></td>
                        <td><?php echo $cat['slug']; ?></td>
                        <td>
                            <?php echo $cat['parent_name'] ? '<span class="badge bg-secondary">'.$cat['parent_name'].'</span>' : '<span class="badge bg-info">Gốc</span>'; ?>
                        </td>
                        <td>
                            <?php if ($cat['status'] == 'active'): ?>
                                <span class="badge bg-success">Hiển thị</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="category-edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" 
                               class="btn btn-sm btn-danger confirm-delete"
                               data-confirm="Bạn có chắc muốn xóa danh mục này?">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>