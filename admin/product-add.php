<?php
// admin/product-add.php
require_once '../includes/init.php';

// Kiểm tra quyền Admin
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$db = Database::getInstance();

// Lấy danh sách danh mục
$categories = $db->select("SELECT * FROM categories ORDER BY name ASC");

$error = '';
$success = '';

// --- XỬ LÝ SUBMIT FORM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    // Nếu không nhập slug thì tự tạo từ tên
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : Functions::createSlug($name);
    
    // Kiểm tra trùng Slug
    $checkSlug = $db->selectOne("SELECT id FROM products WHERE slug = ?", [$slug]);
    if ($checkSlug) {
        $slug .= '-' . time(); // Thêm timestamp nếu trùng
    }

    $category_id = $_POST['category_id'] ?? null;
    $price = $_POST['price'] ?? 0;
    $sale_price = !empty($_POST['sale_price']) ? $_POST['sale_price'] : null;
    $weight = $_POST['weight'] ?? 0;
    $unit = $_POST['unit'] ?? 'kg';
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $origin = $_POST['origin'] ?? '';
    $short_description = $_POST['short_description'] ?? '';
    $description = $_POST['description'] ?? '';
    $cooking_guide = $_POST['cooking_guide'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Xử lý thông tin dinh dưỡng (Text -> JSON)
    $nutritional_info_raw = $_POST['nutritional_info'] ?? '';
    $nutritional_json = '';
    if (!empty($nutritional_info_raw)) {
        $lines = explode("\n", $nutritional_info_raw);
        $nutri_arr = [];
        foreach ($lines as $line) {
            $parts = explode(':', $line);
            if (count($parts) >= 2) {
                $nutri_arr[] = [
                    'name' => trim($parts[0]),
                    'amount' => trim($parts[1]),
                    'dv' => isset($parts[2]) ? trim($parts[2]) : ''
                ];
            }
        }
        $nutritional_json = json_encode($nutri_arr, JSON_UNESCAPED_UNICODE);
    }

    // Validate
    if (empty($name) || empty($price)) {
        $error = 'Vui lòng nhập Tên sản phẩm và Giá bán.';
    } else {
        try {
            // 1. Xử lý Ảnh Chính
            $imageName = 'default.jpg'; // Ảnh mặc định nếu không up
            if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
                $uploaded = Functions::uploadImage($_FILES['main_image'], '../assets/images/products/');
                if ($uploaded) {
                    $imageName = $uploaded;
                }
            }

            // 2. Xử lý Ảnh Phụ (Lấy từ Dropzone hidden inputs)
            $additionalImages = [];
            if (isset($_POST['additional_images']) && is_array($_POST['additional_images'])) {
                foreach ($_POST['additional_images'] as $img) {
                    $additionalImages[] = $img;
                }
            }
            $imagesJson = !empty($additionalImages) ? json_encode($additionalImages) : null;

            // 3. Insert vào Database
            $data = [
                'name' => $name,
                'slug' => $slug,
                'category_id' => $category_id,
                'price' => $price,
                'sale_price' => $sale_price,
                'weight' => $weight,
                'unit' => $unit,
                'stock_quantity' => $stock_quantity,
                'origin' => $origin,
                'short_description' => $short_description,
                'description' => $description,
                'cooking_guide' => $cooking_guide,
                'nutritional_info' => $nutritional_json,
                'image' => $imageName,
                'images' => $imagesJson,
                'status' => $status,
                'featured' => $featured,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $newId = $db->insert('products', $data);
            
            if ($newId) {
                Functions::showMessage('success', 'Thêm sản phẩm mới thành công!');
                // Chuyển hướng về trang danh sách hoặc trang sửa của sản phẩm vừa tạo
                header("Location: products.php"); 
                exit;
            } else {
                $error = 'Có lỗi xảy ra khi lưu dữ liệu.';
            }

        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Thêm sản phẩm mới';
// CSS cho Dropzone và Select2
$additionalCss = [
    'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
];
$additionalScripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
];

require_once 'includes/header.php';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Thêm sản phẩm mới</h1>
    <a href="products.php" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data" id="productForm">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Thông tin cơ bản</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required
                               onkeyup="generateSlug(this.value)">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug (URL)</label>
                        <input type="text" class="form-control" name="slug" id="slug"
                               value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>">
                        <small class="text-muted">Tự động tạo nếu để trống</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                        <select class="form-select select2" name="category_id" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php 
                            // Hàm hiển thị cây danh mục
                            function showCategoryTree($categories, $parent_id = null, $char = '', $selected_id = 0) {
                                foreach ($categories as $key => $item) {
                                    if ($item['parent_id'] == $parent_id) {
                                        $selected = ($item['id'] == $selected_id) ? 'selected' : '';
                                        echo '<option value="'.$item['id'].'" '.$selected.'>';
                                            echo $char . htmlspecialchars($item['name']);
                                        echo '</option>';
                                        unset($categories[$key]);
                                        showCategoryTree($categories, $item['id'], $char . '|-- ', $selected_id);
                                    }
                                }
                            }
                            showCategoryTree($categories, null, '', $_POST['category_id'] ?? 0);
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả ngắn</label>
                        <textarea class="form-control" name="short_description" rows="3"><?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả chi tiết</label>
                        <textarea class="form-control summernote" name="description" rows="10"><?php echo $_POST['description'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Thông tin bổ sung</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Hướng dẫn nấu</label>
                            <textarea class="form-control" name="cooking_guide" rows="5"><?php echo htmlspecialchars($_POST['cooking_guide'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Thông tin dinh dưỡng</label>
                            <textarea class="form-control" name="nutritional_info" rows="5" placeholder="Ví dụ:&#10;Protein: 5g: 10%&#10;Carbs: 20g: 5%"><?php echo htmlspecialchars($_POST['nutritional_info'] ?? ''); ?></textarea>
                            <small class="text-muted">Định dạng mỗi dòng: Tên chất: Hàm lượng: %DV</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4 sticky-top" style="z-index: 99;">
                <div class="card-body">
                    <button type="submit" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-plus me-2"></i> Thêm sản phẩm
                    </button>
                    <a href="products.php" class="btn btn-outline-secondary w-100">Hủy bỏ</a>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Cấu hình</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="active" selected>Đang bán</option>
                            <option value="inactive">Ngừng bán</option>
                            <option value="out_of_stock">Hết hàng</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured">
                        <label class="form-check-label" for="featured">Sản phẩm nổi bật</label>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Giá & Kho</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="price" value="<?php echo $_POST['price'] ?? ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" class="form-control" name="sale_price" value="<?php echo $_POST['sale_price'] ?? ''; ?>">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tồn kho</label>
                            <input type="number" class="form-control" name="stock_quantity" value="<?php echo $_POST['stock_quantity'] ?? '100'; ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Đơn vị</label>
                            <select class="form-select" name="unit">
                                <option value="kg" selected>kg</option>
                                <option value="bao">Bao</option>
                                <option value="hop">Hộp</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trọng lượng (kg)</label>
                        <input type="number" step="0.1" class="form-control" name="weight" value="<?php echo $_POST['weight'] ?? '1'; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xuất xứ</label>
                        <input type="text" class="form-control" name="origin" value="<?php echo htmlspecialchars($_POST['origin'] ?? 'Việt Nam'); ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Hình ảnh</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold d-block text-start">Ảnh chính</label>
                        <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" 
                             class="img-thumbnail mb-2" id="previewMainImage" style="height: 150px;">
                        <input type="file" class="form-control" name="main_image" id="mainImageInput" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ảnh phụ</label>
                        <div id="additionalImagesDropzone" class="dropzone"></div>
                        <div id="dropzoneInputs"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Tạo Slug tự động
function generateSlug(str) {
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
    document.getElementById('slug').value = str;
}

// Preview Ảnh chính khi chọn
document.getElementById('mainImageInput').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewMainImage').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Khởi tạo các thư viện
document.addEventListener('DOMContentLoaded', function() {
    // Select2
    $('.select2').select2({
        theme: 'bootstrap-5'
    });

    // Summernote
    $('.summernote').summernote({
        height: 200,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    // Dropzone Configuration
    if (document.getElementById('additionalImagesDropzone')) {
        Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone("#additionalImagesDropzone", { 
            url: "<?php echo SITE_URL; ?>/api/admin/products/upload-image.php",
            paramName: "image",
            maxFiles: 5,
            maxFilesize: 2, // MB
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            dictDefaultMessage: "Kéo thả ảnh vào đây để thêm ảnh phụ",
            success: function(file, response) {
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                
                if (response.success) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'additional_images[]';
                    input.value = response.file_name;
                    input.id = 'img_' + file.upload.uuid;
                    document.getElementById('dropzoneInputs').appendChild(input);
                } else {
                    alert('Lỗi upload: ' + response.message);
                    this.removeFile(file);
                }
            },
            removedfile: function(file) {
                if (file.previewElement != null && file.previewElement.parentNode != null) {
                    file.previewElement.parentNode.removeChild(file.previewElement);
                }
                var input = document.getElementById('img_' + file.upload.uuid);
                if (input) input.remove();
            }
        });
    }
});
</script>

<style>
.dropzone {
    border: 2px dashed #198754;
    border-radius: 5px;
    background: #f8f9fa;
    min-height: 150px;
}
</style>

<?php require_once 'includes/footer.php'; ?>