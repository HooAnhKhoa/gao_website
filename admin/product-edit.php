<?php
// admin/product-edit.php
require_once '../includes/init.php';

// Kiểm tra quyền Admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$db = Database::getInstance();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin sản phẩm hiện tại
$product = $db->selectOne("SELECT * FROM products WHERE id = ?", [$id]);

// Nếu không tìm thấy sản phẩm, quay về trang danh sách
if (!$product) {
    Functions::showMessage('error', 'Sản phẩm không tồn tại!');
    header('Location: products.php');
    exit;
}

// Xử lý khi Submit Form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    // Nếu không nhập slug thì tự tạo từ tên
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : Functions::createSlug($name);
    $category_id = $_POST['category_id'] ?? null;
    $price = $_POST['price'] ?? 0;
    $sale_price = !empty($_POST['sale_price']) ? $_POST['sale_price'] : null;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $status = $_POST['status'] ?? 'inactive';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $description = $_POST['description'] ?? '';
    
    // Validate cơ bản
    if (empty($name) || empty($price)) {
        $error = 'Vui lòng nhập Tên sản phẩm và Giá bán.';
    } else {
        try {
            // Xử lý upload ảnh nếu có chọn ảnh mới
            $imageName = $product['image']; // Giữ ảnh cũ mặc định
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                // Upload ảnh mới
                $newImage = Functions::uploadImage($_FILES['image'], '../assets/images/products/');
                
                // Nếu upload thành công, xóa ảnh cũ (trừ khi là ảnh default)
                if ($newImage) {
                    if ($product['image'] && $product['image'] != 'default.jpg') {
                        Functions::deleteImage('../assets/images/products/' . $product['image']);
                    }
                    $imageName = $newImage;
                }
            }

            // Cập nhật Database
            $data = [
                'name' => $name,
                'slug' => $slug,
                'category_id' => $category_id,
                'price' => $price,
                'sale_price' => $sale_price,
                'stock_quantity' => $stock_quantity,
                'image' => $imageName,
                'description' => $description,
                'status' => $status,
                'featured' => $featured,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->update('products', $data, "id = ?", [$id]);
            
            Functions::showMessage('success', 'Cập nhật sản phẩm thành công!');
            // Refresh lại trang để thấy dữ liệu mới
            header("Location: product-edit.php?id=$id");
            exit;

        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

// Lấy danh sách danh mục để hiển thị trong Select box
$categories = $db->select("SELECT * FROM categories ORDER BY name ASC");

$pageTitle = 'Chỉnh sửa sản phẩm: ' . $product['name'];
require_once 'includes/header.php';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa sản phẩm</h1>
    <a href="products.php" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Thông tin chung</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" required
                               onkeyup="document.getElementById('slug').value = toSlug(this.value)">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Đường dẫn (Slug)</label>
                        <input type="text" class="form-control" name="slug" id="slug"
                               value="<?php echo htmlspecialchars($product['slug']); ?>">
                        <small class="text-muted">Để trống sẽ tự động tạo theo tên sản phẩm</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả chi tiết</label>
                        <textarea class="form-control summernote" name="description" rows="10">
                            <?php echo $product['description']; ?>
                        </textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Thiết lập</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Đang bán</option>
                            <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Ngừng kinh doanh</option>
                            <option value="out_of_stock" <?php echo $product['status'] == 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="featured" id="featured" 
                                   <?php echo $product['featured'] == 1 ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="featured">Sản phẩm nổi bật</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select class="form-select select2" name="category_id">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Giá & Kho hàng</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="price" 
                               value="<?php echo $product['price']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Giá khuyến mãi (VNĐ)</label>
                        <input type="number" class="form-control" name="sale_price" 
                               value="<?php echo $product['sale_price']; ?>">
                        <small class="text-muted">Để trống nếu không giảm giá</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Số lượng tồn kho</label>
                        <input type="number" class="form-control" name="stock_quantity" 
                               value="<?php echo $product['stock_quantity']; ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Hình ảnh sản phẩm</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $product['image']; ?>" 
                                 class="img-fluid rounded mb-2" id="previewImage" 
                                 style="max-height: 200px; border: 1px solid #ddd;">
                        <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" 
                                 class="img-fluid rounded mb-2" id="previewImage" 
                                 style="max-height: 200px; border: 1px solid #ddd;">
                        <?php endif; ?>
                    </div>
                    <div class="input-group">
                        <input type="file" class="form-control" name="image" id="imageInput" accept="image/*">
                    </div>
                    <small class="text-muted d-block mt-2">Chấp nhận: jpg, jpeg, png, gif</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4 sticky-bottom" style="position: sticky; bottom: 0; z-index: 100;">
        <div class="card-body d-flex justify-content-between align-items-center">
            <a href="products.php" class="btn btn-secondary">Hủy bỏ</a>
            <button type="submit" class="btn btn-success px-4">
                <i class="fas fa-save me-2"></i> Lưu thay đổi
            </button>
        </div>
    </div>
</form>

<script>
// Hàm tạo Slug tự động (Client-side)
function toSlug(str) {
    str = str.toLowerCase();
    str = str.replace(/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/g, 'a');
    str = str.replace(/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/g, 'e');
    str = str.replace(/(ì|í|ị|ỉ|ĩ)/g, 'i');
    str = str.replace(/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/g, 'o');
    str = str.replace(/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/g, 'u');
    str = str.replace(/(ỳ|ý|ỵ|ỷ|ỹ)/g, 'y');
    str = str.replace(/(đ)/g, 'd');
    str = str.replace(/([^0-9a-z-\s])/g, '');
    str = str.replace(/(\s+)/g, '-');
    str = str.replace(/^-+/g, '');
    str = str.replace(/-+$/g, '');
    return str;
}

// Preview ảnh khi chọn
document.getElementById('imageInput').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>