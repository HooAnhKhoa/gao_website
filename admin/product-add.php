<?php
session_start();
require_once '../includes/functions.php';

// Check admin authentication
if (!Functions::isLoggedIn() || !Functions::isAdmin()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Thêm sản phẩm mới - Admin';
$additionalCss = [
    'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css'
];
$additionalScripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js'
];

require_once 'includes/header.php';

$db = Database::getInstance();

// Get categories
$categories = $db->select("
    SELECT * FROM categories 
    WHERE status = 'active' 
    ORDER BY parent_id, name
");

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $category_id = $_POST['category_id'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $sale_price = $_POST['sale_price'] ?? null;
    $weight = $_POST['weight'] ?? 0;
    $unit = $_POST['unit'] ?? 'kg';
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $origin = $_POST['origin'] ?? '';
    $short_description = $_POST['short_description'] ?? '';
    $description = $_POST['description'] ?? '';
    $cooking_guide = $_POST['cooking_guide'] ?? '';
    $nutritional_info = $_POST['nutritional_info'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($slug) || $category_id <= 0 || $price <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        try {
            // Check if slug already exists
            $existing = $db->selectOne(
                "SELECT id FROM products WHERE slug = ?",
                [$slug]
            );
            
            if ($existing) {
                $error = 'Slug đã tồn tại. Vui lòng chọn slug khác.';
            } else {
                // Handle main image upload
                $main_image = '';
                if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                    $main_image = Functions::uploadImage($_FILES['main_image']);
                }
                
                // Handle additional images
                $additional_images = [];
                if (!empty($_FILES['additional_images'])) {
                    foreach ($_FILES['additional_images']['tmp_name'] as $index => $tmp_name) {
                        if ($_FILES['additional_images']['error'][$index] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['additional_images']['name'][$index],
                                'type' => $_FILES['additional_images']['type'][$index],
                                'tmp_name' => $tmp_name,
                                'error' => $_FILES['additional_images']['error'][$index],
                                'size' => $_FILES['additional_images']['size'][$index]
                            ];
                            
                            $image_name = Functions::uploadImage($file, '../../assets/images/products/additional/');
                            $additional_images[] = $image_name;
                        }
                    }
                }
                
                // Create nutritional info JSON
                $nutritional_json = '';
                if (!empty($nutritional_info)) {
                    $nutritional_array = [];
                    $lines = explode("\n", $nutritional_info);
                    foreach ($lines as $line) {
                        $parts = explode(':', $line);
                        if (count($parts) === 3) {
                            $nutritional_array[] = [
                                'name' => trim($parts[0]),
                                'amount' => trim($parts[1]),
                                'dv' => trim($parts[2])
                            ];
                        }
                    }
                    $nutritional_json = json_encode($nutritional_array);
                }
                
                // Insert product
                $product_id = $db->insert('products', [
                    'name' => $name,
                    'slug' => $slug,
                    'category_id' => $category_id,
                    'price' => $price,
                    'sale_price' => $sale_price ?: null,
                    'weight' => $weight,
                    'unit' => $unit,
                    'stock_quantity' => $stock_quantity,
                    'origin' => $origin,
                    'short_description' => $short_description,
                    'description' => $description,
                    'cooking_guide' => $cooking_guide,
                    'nutritional_info' => $nutritional_json,
                    'image' => $main_image,
                    'images' => !empty($additional_images) ? json_encode($additional_images) : null,
                    'status' => $status,
                    'featured' => $featured,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                if ($product_id) {
                    $success = 'Thêm sản phẩm thành công!';
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => $success];
                    header('Location: products.php');
                    exit;
                } else {
                    $error = 'Có lỗi xảy ra khi thêm sản phẩm';
                }
            }
        } catch (Exception $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-plus me-2"></i>Thêm sản phẩm mới
    </h1>
    <a href="products.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>

<!-- Flash Messages -->
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>
    <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>
    <?php echo htmlspecialchars($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Product Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success">
            <i class="fas fa-edit me-1"></i>Thông tin sản phẩm
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data" id="productForm">
            <!-- Basic Information -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                    </h5>
                    
                    <!-- Product Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               placeholder="Nhập tên sản phẩm"
                               required>
                    </div>
                    
                    <!-- Slug -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Slug (URL) <span class="text-danger">*</span>
                            <small class="text-muted ms-2">Tự động tạo từ tên sản phẩm</small>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="slug" 
                                   id="slug"
                                   value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>"
                                   placeholder="slug-san-pham"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="generateSlug">
                                <i class="fas fa-sync-alt"></i> Tạo slug
                            </button>
                        </div>
                    </div>
                    
                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Danh mục <span class="text-danger">*</span>
                        </label>
                        <select class="form-select select2" name="category_id" required>
                            <option value="">Chọn danh mục</option>
                            <?php 
                            $parentCategories = array_filter($categories, function($cat) {
                                return $cat['parent_id'] === null;
                            });
                            
                            foreach ($parentCategories as $parent): 
                            ?>
                            <optgroup label="<?php echo htmlspecialchars($parent['name']); ?>">
                                <?php 
                                $childCategories = array_filter($categories, function($cat) use ($parent) {
                                    return $cat['parent_id'] == $parent['id'];
                                });
                                
                                foreach ($childCategories as $child): 
                                ?>
                                <option value="<?php echo $child['id']; ?>"
                                    <?php echo ($_POST['category_id'] ?? '') == $child['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($child['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Short Description -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả ngắn</label>
                        <textarea class="form-control" name="short_description" 
                                  rows="3" 
                                  placeholder="Mô tả ngắn về sản phẩm (hiển thị trên danh sách)..."><?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả chi tiết</label>
                        <textarea class="form-control summernote" name="description" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="fas fa-image me-2"></i>Hình ảnh
                    </h5>
                    
                    <!-- Main Image -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Ảnh chính <span class="text-danger">*</span>
                        </label>
                        <div class="image-upload-area border rounded p-3 text-center mb-2">
                            <input type="file" class="d-none" name="main_image" id="mainImage" accept="image/*">
                            <div class="upload-placeholder" id="mainImagePlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Click để chọn ảnh chính</p>
                                <p class="small text-muted">Kích thước đề xuất: 600x600px</p>
                            </div>
                            <div class="image-preview d-none" id="mainImagePreview">
                                <img id="mainImagePreviewImg" class="img-thumbnail" 
                                     style="max-height: 200px; object-fit: contain;">
                                <button type="button" class="btn btn-sm btn-danger mt-2" 
                                        onclick="removeMainImage()">
                                    <i class="fas fa-trash me-1"></i> Xóa ảnh
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Images -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Ảnh phụ</label>
                        <div class="additional-images-upload">
                            <div class="dropzone border rounded p-3" id="additionalImagesDropzone">
                                <div class="dz-message">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                                    <p class="mb-2">Kéo thả hoặc click để chọn ảnh</p>
                                    <p class="small text-muted">Tối đa 5 ảnh, mỗi ảnh tối đa 2MB</p>
                                </div>
                            </div>
                            <div class="image-previews mt-3" id="additionalImagePreviews"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pricing & Inventory -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="fas fa-tag me-2"></i>Giá cả
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Giá bán <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="price" 
                                           min="0" step="1000"
                                           value="<?php echo $_POST['price'] ?? ''; ?>"
                                           placeholder="0"
                                           required>
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Giá khuyến mãi</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="sale_price" 
                                           min="0" step="1000"
                                           value="<?php echo $_POST['sale_price'] ?? ''; ?>"
                                           placeholder="0">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Khối lượng</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="weight" 
                                           min="0" step="0.1"
                                           value="<?php echo $_POST['weight'] ?? '5'; ?>"
                                           placeholder="5">
                                    <span class="input-group-text">kg</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Đơn vị tính</label>
                                <select class="form-select" name="unit">
                                    <option value="kg" <?php echo ($_POST['unit'] ?? 'kg') == 'kg' ? 'selected' : ''; ?>>Kilogram</option>
                                    <option value="bao" <?php echo ($_POST['unit'] ?? '') == 'bao' ? 'selected' : ''; ?>>Bao</option>
                                    <option value="hop" <?php echo ($_POST['unit'] ?? '') == 'hop' ? 'selected' : ''; ?>>Hộp</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="fas fa-warehouse me-2"></i>Tồn kho & Trạng thái
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Số lượng tồn <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="stock_quantity" 
                                       min="0"
                                       value="<?php echo $_POST['stock_quantity'] ?? '0'; ?>"
                                       placeholder="0"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Xuất xứ</label>
                                <input type="text" class="form-control" name="origin" 
                                       value="<?php echo htmlspecialchars($_POST['origin'] ?? ''); ?>"
                                       placeholder="Ví dụ: Sóc Trăng, Việt Nam">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trạng thái</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo ($_POST['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Đang bán</option>
                                    <option value="inactive" <?php echo ($_POST['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Ngừng bán</option>
                                    <option value="out_of_stock" <?php echo ($_POST['status'] ?? '') == 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nổi bật</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="featured" 
                                           id="featured"
                                           <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        Hiển thị nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="fas fa-utensils me-2"></i>Hướng dẫn nấu
                    </h5>
                    <textarea class="form-control summernote" name="cooking_guide" rows="5"><?php echo htmlspecialchars($_POST['cooking_guide'] ?? ''); ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="fas fa-apple-alt me-2"></i>Thông tin dinh dưỡng
                    </h5>
                    <div class="mb-3">
                        <textarea class="form-control" name="nutritional_info" 
                                  rows="5"
                                  placeholder="Mỗi dòng một thành phần, định dạng: Tên: Hàm lượng: %DV
Ví dụ: 
Carbohydrate: 25g: 8%
Protein: 5g: 10%
Chất béo: 1g: 2%"><?php echo htmlspecialchars($_POST['nutritional_info'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Thông tin dinh dưỡng sẽ được hiển thị dưới dạng bảng trên trang chi tiết sản phẩm.</small>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Hủy bỏ
                        </a>
                        <div>
                            <button type="submit" name="save_draft" value="1" class="btn btn-outline-success me-2">
                                <i class="fas fa-save me-1"></i> Lưu nháp
                            </button>
                            <button type="submit" name="publish" value="1" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Lưu và đăng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem trước sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Product add/edit JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Generate slug from product name
    const nameInput = document.querySelector('input[name="name"]');
    const slugInput = document.getElementById('slug');
    const generateSlugBtn = document.getElementById('generateSlug');
    
    function generateSlug(text) {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[đĐ]/g, 'd')
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
    
    if (nameInput && slugInput) {
        nameInput.addEventListener('blur', function() {
            if (!slugInput.value) {
                slugInput.value = generateSlug(this.value);
            }
        });
        
        generateSlugBtn.addEventListener('click', function() {
            slugInput.value = generateSlug(nameInput.value);
        });
    }
    
    // Main image upload
    const mainImageInput = document.getElementById('mainImage');
    const mainImagePlaceholder = document.getElementById('mainImagePlaceholder');
    const mainImagePreview = document.getElementById('mainImagePreview');
    const mainImagePreviewImg = document.getElementById('mainImagePreviewImg');
    
    if (mainImageInput) {
        mainImagePlaceholder.addEventListener('click', function() {
            mainImageInput.click();
        });
        
        mainImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    mainImagePreviewImg.src = e.target.result;
                    mainImagePlaceholder.classList.add('d-none');
                    mainImagePreview.classList.remove('d-none');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Additional images upload with Dropzone
    Dropzone.autoDiscover = false;
    
    if (document.getElementById('additionalImagesDropzone')) {
        const dropzone = new Dropzone('#additionalImagesDropzone', {
            url: '../api/admin/products/upload-image.php',
            paramName: 'image',
            maxFiles: 5,
            maxFilesize: 2, // MB
            acceptedFiles: 'image/*',
            addRemoveLinks: true,
            dictDefaultMessage: '',
            dictFallbackMessage: 'Trình duyệt của bạn không hỗ trợ kéo thả ảnh.',
            dictFileTooBig: 'Ảnh quá lớn ({{filesize}}MB). Tối đa: {{maxFilesize}}MB.',
            dictInvalidFileType: 'Không thể upload file này.',
            dictResponseError: 'Server lỗi {{statusCode}}.',
            dictCancelUpload: 'Hủy upload',
            dictCancelUploadConfirmation: 'Bạn có chắc muốn hủy upload?',
            dictRemoveFile: 'Xóa ảnh',
            dictMaxFilesExceeded: 'Chỉ có thể upload tối đa {{maxFiles}} ảnh.',
            
            init: function() {
                this.on('success', function(file, response) {
                    if (response.success) {
                        // Add hidden input with image name
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'additional_images[]';
                        input.value = response.file_name;
                        file.previewElement.appendChild(input);
                    } else {
                        this.removeFile(file);
                        showNotification(response.message, 'error');
                    }
                });
                
                this.on('removedfile', function(file) {
                    // Remove the hidden input
                    const inputs = file.previewElement.querySelectorAll('input[name="additional_images[]"]');
                    inputs.forEach(input => input.remove());
                });
                
                this.on('error', function(file, errorMessage) {
                    showNotification(errorMessage, 'error');
                });
            }
        });
    }
    
    // Initialize Summernote
    $('.summernote').summernote({
        height: 200,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                uploadSummernoteImage(files[0], this);
            }
        }
    });
    
    // Form validation
    const productForm = document.getElementById('productForm');
    productForm.addEventListener('submit', function(e) {
        // Additional validation can be added here
        const mainImageInput = document.getElementById('mainImage');
        if (!mainImageInput.files || mainImageInput.files.length === 0) {
            e.preventDefault();
            showNotification('Vui lòng chọn ảnh chính cho sản phẩm!', 'error');
            mainImagePlaceholder.scrollIntoView({ behavior: 'smooth' });
        }
    });
    
    // Price validation
    const priceInput = document.querySelector('input[name="price"]');
    const salePriceInput = document.querySelector('input[name="sale_price"]');
    
    if (priceInput && salePriceInput) {
        salePriceInput.addEventListener('change', function() {
            const price = parseFloat(priceInput.value) || 0;
            const salePrice = parseFloat(this.value) || 0;
            
            if (salePrice > price) {
                showNotification('Giá khuyến mãi không được lớn hơn giá bán!', 'error');
                this.value = '';
            }
        });
    }
    
    // Preview product
    window.previewProduct = function() {
        const formData = new FormData(productForm);
        
        fetch('../api/admin/products/preview.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.html;
                const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                modal.show();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Lỗi tạo bản xem trước!', 'error');
        });
    };
});

function removeMainImage() {
    const mainImageInput = document.getElementById('mainImage');
    const mainImagePlaceholder = document.getElementById('mainImagePlaceholder');
    const mainImagePreview = document.getElementById('mainImagePreview');
    
    mainImageInput.value = '';
    mainImagePreview.classList.add('d-none');
    mainImagePlaceholder.classList.remove('d-none');
}

function uploadSummernoteImage(file, editor) {
    const formData = new FormData();
    formData.append('image', file);
    
    fetch('../api/admin/products/upload-image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $(editor).summernote('insertImage', data.url);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi upload ảnh!', 'error');
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>

<style>
.image-upload-area {
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.image-upload-area:hover {
    border-color: var(--success-color);
    background-color: rgba(25, 135, 84, 0.05);
}

.dropzone {
    border: 2px dashed #dee2e6 !important;
    border-radius: 0.375rem !important;
    background: white !important;
}

.dropzone .dz-preview {
    margin: 10px !important;
}

.dropzone .dz-image {
    border-radius: 5px !important;
}

.form-check.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
}

.select2-container--bootstrap-5 .select2-selection {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: calc(1.5em + 0.75rem + 2px);
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    line-height: calc(1.5em + 0.75rem);
}

.note-editor.note-frame {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
</style>

<?php
require_once 'includes/footer.php';
?>