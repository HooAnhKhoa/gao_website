<?php
session_start();
require_once '../includes/init.php';

// Check admin authentication
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Quản lý sản phẩm - Admin';
$additionalCss = [
    'https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css',
    'https://cdn.datatables.net/select/1.6.0/css/select.dataTables.min.css'
];
$additionalScripts = [
    'https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js',
    'https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js',
    'https://cdn.datatables.net/select/1.6.0/js/dataTables.select.min.js'
];

require_once 'includes/header.php';

$db = Database::getInstance();

// Handle actions
$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? 0;

if ($action == 'delete' && $product_id > 0) {
    // Delete product
    $db->delete('products', 'id = ?', [$product_id]);
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Đã xóa sản phẩm thành công!'];
    header('Location: products.php');
    exit;
}

if ($action == 'toggle_status' && $product_id > 0) {
    // Toggle product status
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

if ($action == 'toggle_featured' && $product_id > 0) {
    // Toggle featured status
    $product = $db->selectOne("SELECT featured FROM products WHERE id = ?", [$product_id]);
    if ($product) {
        $new_featured = $product['featured'] ? 0 : 1;
        $db->update('products', ['featured' => $new_featured], 'id = ?', [$product_id]);
        
        $featured_text = $new_featured ? 'nổi bật' : 'bỏ nổi bật';
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Đã $featured_text sản phẩm!"];
    }
    header('Location: products.php');
    exit;
}

// Get filter parameters
$filter_category = $_GET['category'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_featured = $_GET['featured'] ?? '';
$filter_stock = $_GET['stock'] ?? '';
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

if ($filter_featured !== '') {
    $where .= " AND p.featured = ?";
    $params[] = $filter_featured;
}

if ($filter_stock == 'low') {
    $where .= " AND p.stock_quantity <= 10";
} elseif ($filter_stock == 'out') {
    $where .= " AND p.stock_quantity = 0";
}

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.slug LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Get total count
$total_count = $db->selectOne(
    "SELECT COUNT(*) as total FROM products p WHERE $where",
    $params
)['total'];

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
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-boxes me-2"></i>Quản lý sản phẩm
    </h1>
    <a href="product-add.php" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
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
            <div class="col-md-3">
                <label class="form-label small fw-bold">Danh mục</label>
                <select class="form-select form-select-sm" name="category">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" 
                        <?php echo $filter_category == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label small fw-bold">Trạng thái</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Đang bán</option>
                    <option value="inactive" <?php echo $filter_status == 'inactive' ? 'selected' : ''; ?>>Ngừng bán</option>
                    <option value="out_of_stock" <?php echo $filter_status == 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label small fw-bold">Nổi bật</label>
                <select class="form-select form-select-sm" name="featured">
                    <option value="">Tất cả</option>
                    <option value="1" <?php echo $filter_featured === '1' ? 'selected' : ''; ?>>Nổi bật</option>
                    <option value="0" <?php echo $filter_featured === '0' ? 'selected' : ''; ?>>Không nổi bật</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label small fw-bold">Tồn kho</label>
                <select class="form-select form-select-sm" name="stock">
                    <option value="">Tất cả</option>
                    <option value="low" <?php echo $filter_stock == 'low' ? 'selected' : ''; ?>>Sắp hết</option>
                    <option value="out" <?php echo $filter_stock == 'out' ? 'selected' : ''; ?>>Hết hàng</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label small fw-bold">Tìm kiếm</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Tên, mô tả..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-success" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="d-flex justify-content-between">
                    <span class="small text-muted">
                        Tìm thấy <strong><?php echo $total_count; ?></strong> sản phẩm
                    </span>
                    <div>
                        <a href="products.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-redo"></i> Làm mới
                        </a>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-filter"></i> Áp dụng bộ lọc
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Products Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-success">
            <i class="fas fa-box me-1"></i>Danh sách sản phẩm
        </h6>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" 
                    data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-download me-1"></i> Xuất dữ liệu
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" id="exportExcel">Excel</a></li>
                <li><a class="dropdown-item" href="#" id="exportPDF">PDF</a></li>
                <li><a class="dropdown-item" href="#" id="exportCSV">CSV</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover data-table" id="productsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th width="80">Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th width="120">Danh mục</th>
                        <th width="100">Giá</th>
                        <th width="80">Tồn kho</th>
                        <th width="100">Trạng thái</th>
                        <th width="100">Nổi bật</th>
                        <th width="120">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): 
                        $currentPrice = $product['sale_price'] ?: $product['price'];
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="select-item" value="<?php echo $product['id']; ?>">
                        </td>
                        <td>
                            <img src="../../assets/images/products/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                                 class="img-thumbnail" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 60px; height: 60px; object-fit: cover;">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <div class="small text-muted">
                                Mã: SP<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?>
                            </div>
                            <div class="small">
                                Lượt xem: <?php echo $product['views'] ?? 0; ?>
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
                            <div class="form-check form-switch">
                                <input class="form-check-input featured-toggle" 
                                       type="checkbox" 
                                       data-id="<?php echo $product['id']; ?>"
                                       <?php echo $product['featured'] ? 'checked' : ''; ?>>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="../pages/product-detail.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-info" 
                                   target="_blank"
                                   title="Xem trên website">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-warning" 
                                   title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="products.php?action=toggle_status&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-<?php echo $product['status'] == 'active' ? 'secondary' : 'success'; ?>"
                                   title="<?php echo $product['status'] == 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-danger confirm-delete"
                                   data-confirm="Bạn có chắc muốn xóa sản phẩm này? Hành động này không thể hoàn tác."
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
        
        <!-- Bulk Actions -->
        <div class="row mt-3">
            <div class="col-md-8">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        Hành động hàng loạt
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="bulkActivate">Kích hoạt</a></li>
                        <li><a class="dropdown-item" href="#" id="bulkDeactivate">Vô hiệu hóa</a></li>
                        <li><a class="dropdown-item" href="#" id="bulkFeatured">Đánh dấu nổi bật</a></li>
                        <li><a class="dropdown-item" href="#" id="bulkUnfeatured">Bỏ nổi bật</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="bulkDelete">Xóa sản phẩm</a></li>
                    </ul>
                </div>
                <span class="ms-3 small text-muted">
                    Đã chọn <span id="selectedCount">0</span> sản phẩm
                </span>
            </div>
            <div class="col-md-4 text-end">
                <a href="product-add.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Thêm sản phẩm mới
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div class="modal fade" id="quickEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa nhanh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickEditForm">
                <div class="modal-body">
                    <input type="hidden" id="editProductId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên sản phẩm</label>
                                <input type="text" class="form-control" id="editName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Danh mục</label>
                                <select class="form-select" id="editCategory" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Giá bán</label>
                                <input type="number" class="form-control" id="editPrice" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Giá khuyến mãi</label>
                                <input type="number" class="form-control" id="editSalePrice" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Số lượng tồn</label>
                                <input type="number" class="form-control" id="editStock" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" id="editStatus">
                                    <option value="active">Đang bán</option>
                                    <option value="inactive">Ngừng bán</option>
                                    <option value="out_of_stock">Hết hàng</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nổi bật</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editFeatured">
                                    <label class="form-check-label">Hiển thị nổi bật</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Products management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with export buttons
    const table = $('#productsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-sm btn-outline-secondary'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-outline-success'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-outline-danger'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-sm btn-outline-info'
            }
        ],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
        },
        columnDefs: [
            {
                orderable: false,
                targets: [0, 1, 7, 8]
            }
        ],
        select: {
            style: 'multi',
            selector: 'td:first-child'
        }
    });
    
    // Select all checkbox
    $('#selectAll').on('click', function() {
        $('.select-item').prop('checked', this.checked);
        updateSelectedCount();
    });
    
    // Update selected count
    $('.select-item').on('change', updateSelectedCount);
    
    function updateSelectedCount() {
        const selectedCount = $('.select-item:checked').length;
        $('#selectedCount').text(selectedCount);
    }
    
    // Featured toggle
    $('.featured-toggle').on('change', function() {
        const productId = $(this).data('id');
        const featured = this.checked ? 1 : 0;
        
        $.ajax({
            url: '../api/admin/products/update-featured.php',
            method: 'POST',
            data: {
                product_id: productId,
                featured: featured
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                } else {
                    showNotification(response.message, 'error');
                    // Revert checkbox state
                    $(this).prop('checked', !featured);
                }
            },
            error: function() {
                showNotification('Lỗi kết nối!', 'error');
                // Revert checkbox state
                $(this).prop('checked', !featured);
            }
        });
    });
    
    // Bulk actions
    $('#bulkActivate').on('click', function(e) {
        e.preventDefault();
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            bulkUpdateStatus(selectedIds, 'active');
        }
    });
    
    $('#bulkDeactivate').on('click', function(e) {
        e.preventDefault();
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            bulkUpdateStatus(selectedIds, 'inactive');
        }
    });
    
    $('#bulkFeatured').on('click', function(e) {
        e.preventDefault();
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            bulkUpdateFeatured(selectedIds, 1);
        }
    });
    
    $('#bulkUnfeatured').on('click', function(e) {
        e.preventDefault();
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            bulkUpdateFeatured(selectedIds, 0);
        }
    });
    
    $('#bulkDelete').on('click', function(e) {
        e.preventDefault();
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            if (confirm(`Bạn có chắc muốn xóa ${selectedIds.length} sản phẩm đã chọn?`)) {
                bulkDeleteProducts(selectedIds);
            }
        }
    });
    
    function getSelectedIds() {
        const selectedIds = [];
        $('.select-item:checked').each(function() {
            selectedIds.push($(this).val());
        });
        return selectedIds;
    }
    
    function bulkUpdateStatus(productIds, status) {
        $.ajax({
            url: '../api/admin/products/bulk-update.php',
            method: 'POST',
            data: {
                product_ids: productIds,
                action: 'status',
                value: status
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Lỗi kết nối!', 'error');
            }
        });
    }
    
    function bulkUpdateFeatured(productIds, featured) {
        $.ajax({
            url: '../api/admin/products/bulk-update.php',
            method: 'POST',
            data: {
                product_ids: productIds,
                action: 'featured',
                value: featured
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Lỗi kết nối!', 'error');
            }
        });
    }
    
    function bulkDeleteProducts(productIds) {
        $.ajax({
            url: '../api/admin/products/bulk-delete.php',
            method: 'POST',
            data: {
                product_ids: productIds
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Lỗi kết nối!', 'error');
            }
        });
    }
    
    // Quick edit via double click
    $('#productsTable tbody').on('dblclick', 'tr', function() {
        const rowData = table.row(this).data();
        if (rowData) {
            openQuickEdit(rowData);
        }
    });
    
    function openQuickEdit(rowData) {
        // Extract data from row
        const productId = $(rowData[0]).find('.select-item').val();
        const productName = $(rowData[2]).find('strong').text().trim();
        const categoryName = $(rowData[3]).text().trim();
        
        // Set form values
        $('#editProductId').val(productId);
        $('#editName').val(productName);
        
        // Set category (you'll need to map category name to ID)
        const categoryId = findCategoryIdByName(categoryName);
        $('#editCategory').val(categoryId);
        
        // Open modal
        const modal = new bootstrap.Modal(document.getElementById('quickEditModal'));
        modal.show();
    }
    
    function findCategoryIdByName(name) {
        // You would need to implement this based on your categories
        return '';
    }
    
    // Quick edit form submission
    $('#quickEditForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            product_id: $('#editProductId').val(),
            name: $('#editName').val(),
            category_id: $('#editCategory').val(),
            price: $('#editPrice').val(),
            sale_price: $('#editSalePrice').val(),
            stock_quantity: $('#editStock').val(),
            status: $('#editStatus').val(),
            featured: $('#editFeatured').is(':checked') ? 1 : 0
        };
        
        $.ajax({
            url: '../api/admin/products/quick-edit.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    $('#quickEditModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Lỗi kết nối!', 'error');
            }
        });
    });
    
    // Export buttons
    $('#exportExcel').on('click', function(e) {
        e.preventDefault();
        table.button('.buttons-excel').trigger();
    });
    
    $('#exportPDF').on('click', function(e) {
        e.preventDefault();
        table.button('.buttons-pdf').trigger();
    });
    
    $('#exportCSV').on('click', function(e) {
        e.preventDefault();
        table.button('.buttons-csv').trigger();
    });
    
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => {
            notification.alert('close');
        }, 5000);
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>