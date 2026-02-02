// assets/js/cart.js - Cart functionality

// Update cart count in header
function updateCartCount() {
    fetch(SITE_URL + '/api/cart/count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartBadges = document.querySelectorAll('.cart-count');
                cartBadges.forEach(badge => {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-flex';
                        badge.classList.add('updated');
                        setTimeout(() => badge.classList.remove('updated'), 600);
                    } else {
                        badge.style.display = 'none';
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

// Add to cart function
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    return fetch(SITE_URL + '/api/cart/add.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Có lỗi xảy ra', 'error');
        }
        return data;
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showNotification('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
        return { success: false };
    });
}

// Remove from cart function
function removeFromCart(productId) {
    return fetch(SITE_URL + '/api/cart/remove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Có lỗi xảy ra', 'error');
        }
        return data;
    })
    .catch(error => {
        console.error('Error removing from cart:', error);
        showNotification('Có lỗi xảy ra khi xóa khỏi giỏ hàng', 'error');
        return { success: false };
    });
}

// Update cart item quantity
function updateCartQuantity(productId, quantity) {
    return fetch(SITE_URL + '/api/cart/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            product_id: productId,
            quantity: quantity 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
        } else {
            showNotification(data.message || 'Có lỗi xảy ra', 'error');
        }
        return data;
    })
    .catch(error => {
        console.error('Error updating cart:', error);
        showNotification('Có lỗi xảy ra khi cập nhật giỏ hàng', 'error');
        return { success: false };
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.cart-notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create new notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed cart-notification`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Initialize cart functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    updateCartCount();
    
    // Add event listeners for add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const quantity = this.getAttribute('data-quantity') || 1;
            
            // Disable button temporarily
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
            
            addToCart(productId, quantity).finally(() => {
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
});

// Export functions for global use
window.cartFunctions = {
    updateCartCount,
    addToCart,
    removeFromCart,
    updateCartQuantity,
    showNotification
};