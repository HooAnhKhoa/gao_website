// assets/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    // ====================
    // CẤU HÌNH ĐƯỜNG DẪN
    // ====================
    const getApiPath = () => {
        // Sử dụng biến global từ footer.php nếu có
        if (window.API_PATH) {
            return window.API_PATH;
        }
        
        // Tự động xác định
        const currentPath = window.location.pathname;
        if (currentPath.includes('/pages/')) {
            return '../api/';
        }
        return 'api/';
    };
    
    const API_PATH = getApiPath();
    console.log('API Path:', API_PATH);
    
    // ====================
    // HÀM CHÍNH THÊM VÀO GIỎ HÀNG
    // ====================
    window.addToCart = function(productId, quantity = 1, button = null) {
        console.log('addToCart called:', { productId, quantity, api: API_PATH + 'cart/add.php' });
        
        // Validate
        if (!productId || productId <= 0) {
            showNotification('ID sản phẩm không hợp lệ!', 'error');
            return Promise.reject('Invalid product ID');
        }
        
        if (quantity <= 0) {
            showNotification('Số lượng phải lớn hơn 0!', 'error');
            return Promise.reject('Invalid quantity');
        }
        
        // Hiển thị loading trên button
        let originalHTML = '';
        if (button) {
            originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang thêm...';
            button.disabled = true;
        }
        
        // Tạo FormData (tương thích với PHP POST)
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        
        // Gọi API
        return fetch(API_PATH + 'cart/add.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            // Kiểm tra content type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text.substring(0, 200));
                    throw new Error('Server trả về HTML thay vì JSON');
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            
            if (data.success) {
                showNotification(data.message || 'Đã thêm vào giỏ hàng thành công!', 'success');
                updateCartCount();
                return data;
            } else {
                showNotification(data.message || 'Thêm vào giỏ hàng thất bại!', 'error');
                throw new Error(data.message || 'API error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showNotification('Lỗi: ' + error.message, 'error');
            throw error;
        })
        .finally(() => {
            // Khôi phục button sau 1.5 giây
            if (button) {
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }, 1500);
            }
        });
    };
    
    // ====================
    // XỬ LÝ SỰ KIỆN CLICK
    // ====================
    document.addEventListener('click', function(e) {
        // Nút thêm vào giỏ hàng
        const addToCartBtn = e.target.closest('.add-to-cart-btn');
        if (addToCartBtn && !addToCartBtn.disabled) {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = addToCartBtn.dataset.id;
            let quantity = 1;
            
            // Tìm input số lượng
            const productItem = addToCartBtn.closest('.product-item, .card, .product-info, .add-to-cart');
            if (productItem) {
                const quantityInput = productItem.querySelector('.quantity-input, input[type="number"], #quantity');
                if (quantityInput) {
                    quantity = parseInt(quantityInput.value) || 1;
                    // Validate số lượng
                    const max = parseInt(quantityInput.max) || 999;
                    const min = parseInt(quantityInput.min) || 1;
                    quantity = Math.max(min, Math.min(quantity, max));
                }
            }
            
            // Gọi hàm thêm vào giỏ
            window.addToCart(productId, quantity, addToCartBtn);
        }
        
        // Nút tăng/giảm số lượng
        if (e.target.closest('#decreaseQty')) {
            e.preventDefault();
            const input = document.getElementById('quantity');
            if (input) {
                let value = parseInt(input.value) || 1;
                if (value > 1) {
                    input.value = value - 1;
                }
            }
        }
        
        if (e.target.closest('#increaseQty')) {
            e.preventDefault();
            const input = document.getElementById('quantity');
            if (input) {
                let value = parseInt(input.value) || 1;
                const max = parseInt(input.max) || 999;
                if (value < max) {
                    input.value = value + 1;
                }
            }
        }
    });
    
    // ====================
    // CẬP NHẬT SỐ LƯỢNG GIỎ
    // ====================
    function updateCartCount() {
        fetch(API_PATH + 'cart/count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCountDisplay(data.count);
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
    }
    
    function updateCartCountDisplay(count) {
        document.querySelectorAll('.cart-count').forEach(element => {
            element.textContent = count || 0;
            element.style.display = count > 0 ? 'inline-block' : 'none';
        });
    }
    
    // ====================
    // HÀM HIỂN THỊ THÔNG BÁO
    // ====================
    function showNotification(message, type = 'success') {
        // Xóa thông báo cũ
        const existing = document.querySelectorAll('.custom-notification');
        existing.forEach(n => n.remove());
        
        const notification = document.createElement('div');
        notification.className = `custom-notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            animation: slideInRight 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        document.body.appendChild(notification);
        
        // Tự động ẩn
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
    
    // ====================
    // THÊM CSS ANIMATIONS
    // ====================
    if (!document.querySelector('#notif-animations')) {
        const style = document.createElement('style');
        style.id = 'notif-animations';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .add-to-cart-btn:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }
        `;
        document.head.appendChild(style);
    }
    
    // ====================
    // KHỞI TẠO
    // ====================
    
    // Khởi tạo số lượng giỏ hàng
    updateCartCount();
    
    // Cập nhật mỗi 30 giây
    setInterval(updateCartCount, 30000);
    
    // Debug info
    console.log('Cart system initialized with API path:', API_PATH);
});