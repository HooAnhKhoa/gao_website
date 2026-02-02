# 🌾 Gạo Website - Rice E-commerce Platform

A modern, responsive e-commerce platform specialized in selling premium rice varieties from Vietnam. Built with PHP, MySQL, and Bootstrap for a seamless shopping experience.

## ✨ Features

### 🛍️ Customer Features
- **Product Catalog**: Browse diverse rice varieties with detailed descriptions
- **Advanced Search & Filter**: Find products by category, price, and popularity
- **Shopping Cart**: Add/remove items with real-time cart updates
- **User Authentication**: Secure registration and login system
- **Order Management**: Place orders with multiple payment methods
- **Product Reviews**: Rate and review purchased products
- **Responsive Design**: Mobile-friendly interface

### 👨‍💼 Admin Features
- **Dashboard**: Comprehensive analytics and statistics
- **Product Management**: Add, edit, and manage rice products
- **Category Management**: Organize products into categories
- **Order Management**: Track and update order status
- **Customer Management**: View customer information and orders
- **Review Management**: Moderate product reviews
- **Inventory Tracking**: Monitor stock levels

## 🚀 Tech Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6
- **Server**: Apache/Nginx

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/gao-website.git
   cd gao-website
   ```

2. **Database Setup**
   - Create a MySQL database
   - Import the database schema (check for SQL files in the project)
   - Update database configuration in `config/database.php`

3. **Configuration**
   ```php
   // config/database.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **File Permissions**
   ```bash
   chmod 755 assets/images/
   chmod 755 assets/images/products/
   chmod 755 assets/images/categories/
   ```

5. **Web Server Configuration**
   - Point your web server to the project root directory
   - Ensure mod_rewrite is enabled for clean URLs

## 📁 Project Structure

```
gao-website/
├── admin/                  # Admin panel
│   ├── includes/          # Admin shared components
│   ├── dashboard.php      # Admin dashboard
│   ├── products.php       # Product management
│   ├── categories.php     # Category management
│   ├── orders.php         # Order management
│   └── reviews.php        # Review management
├── api/                   # API endpoints
│   ├── auth/             # Authentication APIs
│   ├── cart/             # Shopping cart APIs
│   ├── orders/           # Order management APIs
│   └── reviews/          # Review system APIs
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Image uploads
├── config/              # Configuration files
├── includes/            # Shared PHP components
├── pages/               # Customer-facing pages
└── index.php           # Homepage
```

## 🎯 Key Features Explained

### Product Management
- Dynamic product catalog with categories
- Image upload and management
- Stock tracking and inventory alerts
- Pricing with discount support

### Shopping Cart System
- Session-based cart for guests
- Database-persistent cart for logged-in users
- Real-time cart updates via AJAX
- Cart count display in header

### Order Processing
- Multi-step checkout process
- Multiple payment methods (COD, Bank Transfer, MoMo)
- Order status tracking
- Email notifications

### Review System
- 5-star rating system
- Review moderation (pending/approved/rejected)
- Average rating calculation
- Review display on product pages

## 🔧 Configuration

### Constants (config/constants.php)
```php
// Product status
define('PRODUCT_ACTIVE', 'active');
define('PRODUCT_INACTIVE', 'inactive');

// Review status
define('REVIEW_PENDING', 'pending');
define('REVIEW_APPROVED', 'approved');
define('REVIEW_REJECTED', 'rejected');

// Pagination
define('ITEMS_PER_PAGE', 12);
```

### Database Configuration
Update `config/database.php` with your database credentials.

## 🚀 Usage

### For Customers
1. Browse products on the homepage
2. Use search and filters to find specific rice varieties
3. Add products to cart
4. Register/login for checkout
5. Complete purchase with preferred payment method
6. Track order status in profile
7. Leave reviews for purchased products

### For Administrators
1. Access admin panel at `/admin/`
2. Login with admin credentials
3. Manage products, categories, and orders
4. Monitor sales through dashboard
5. Moderate customer reviews

## 🎨 Customization

### Styling
- Main styles: `assets/css/style.css`
- Admin styles: `assets/css/admin.css`
- Bootstrap variables can be customized

### JavaScript
- Main functionality: `assets/js/main.js`
- Cart operations: `assets/js/cart.js`
- Admin features: `assets/js/admin.js`

## 🔒 Security Features

- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection for forms
- Secure password hashing
- Session management
- File upload validation

## 📱 Mobile Responsiveness

The platform is fully responsive and optimized for:
- Desktop computers
- Tablets
- Mobile phones
- Various screen sizes

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **Your Name** - *Initial work* - [YourGitHub](https://github.com/yourusername)

## 🙏 Acknowledgments

- Bootstrap team for the excellent CSS framework
- Font Awesome for the beautiful icons
- PHP community for continuous support
- All contributors who helped improve this project

## 📞 Support

If you encounter any issues or have questions:
- Create an issue on GitHub
- Contact: your.email@example.com

## 🔄 Version History

- **v1.0.0** - Initial release with core e-commerce functionality
- **v1.1.0** - Added review system and improved admin dashboard
- **v1.2.0** - Enhanced mobile responsiveness and cart functionality

---

Made with ❤️ for Vietnamese rice lovers
