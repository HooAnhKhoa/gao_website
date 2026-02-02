# 🌾 Website Bán Gạo - Nền Tảng Thương Mại Điện Tử

Nền tảng thương mại điện tử hiện đại, responsive chuyên bán các loại gạo cao cấp từ Việt Nam. Được xây dựng bằng PHP, MySQL và Bootstrap để mang lại trải nghiệm mua sắm tuyệt vời.

## ✨ Tính Năng

### 🛍️ Tính Năng Khách Hàng
- **Danh Mục Sản Phẩm**: Duyệt các loại gạo đa dạng với mô tả chi tiết
- **Tìm Kiếm & Lọc Nâng Cao**: Tìm sản phẩm theo danh mục, giá cả và độ phổ biến
- **Giỏ Hàng**: Thêm/xóa sản phẩm với cập nhật thời gian thực
- **Xác Thực Người Dùng**: Hệ thống đăng ký và đăng nhập bảo mật
- **Quản Lý Đơn Hàng**: Đặt hàng với nhiều phương thức thanh toán
- **Đánh Giá Sản Phẩm**: Đánh giá và nhận xét sản phẩm đã mua
- **Thiết Kế Responsive**: Giao diện thân thiện với mobile

### 👨‍💼 Tính Năng Admin
- **Bảng Điều Khiển**: Phân tích và thống kê toàn diện
- **Quản Lý Sản Phẩm**: Thêm, sửa và quản lý sản phẩm gạo
- **Quản Lý Danh Mục**: Tổ chức sản phẩm theo danh mục
- **Quản Lý Đơn Hàng**: Theo dõi và cập nhật trạng thái đơn hàng
- **Quản Lý Khách Hàng**: Xem thông tin khách hàng và đơn hàng
- **Quản Lý Đánh Giá**: Kiểm duyệt đánh giá sản phẩm
- **Theo Dõi Kho Hàng**: Giám sát mức tồn kho

## 🚀 Công Nghệ Sử Dụng

- **Backend**: PHP 8.0+
- **Cơ Sở Dữ Liệu**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6
- **Server**: Apache/Nginx

## 📋 Yêu Cầu Hệ Thống

- PHP 8.0 trở lên
- MySQL 8.0 trở lên
- Web server Apache/Nginx
- mod_rewrite được bật (cho Apache)

## 🛠️ Hướng Dẫn Cài Đặt

1. **Clone repository**
   ```bash
   git clone https://github.com/yourusername/gao-website.git
   cd gao-website
   ```

2. **Thiết Lập Cơ Sở Dữ Liệu**
   - Tạo database MySQL
   - Import schema database (kiểm tra file SQL trong dự án)
   - Cập nhật cấu hình database trong `config/database.php`

3. **Cấu Hình**
   ```php
   // config/database.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ten_database_cua_ban');
   define('DB_USER', 'ten_nguoi_dung');
   define('DB_PASS', 'mat_khau');
   ```

4. **Phân Quyền File**
   ```bash
   chmod 755 assets/images/
   chmod 755 assets/images/products/
   chmod 755 assets/images/categories/
   ```

5. **Cấu Hình Web Server**
   - Trỏ web server đến thư mục gốc của dự án
   - Đảm bảo mod_rewrite được bật cho URL thân thiện

## 📁 Cấu Trúc Dự Án

```
gao-website/
├── admin/                  # Panel quản trị
│   ├── includes/          # Component dùng chung admin
│   ├── dashboard.php      # Bảng điều khiển admin
│   ├── products.php       # Quản lý sản phẩm
│   ├── categories.php     # Quản lý danh mục
│   ├── orders.php         # Quản lý đơn hàng
│   └── reviews.php        # Quản lý đánh giá
├── api/                   # API endpoints
│   ├── auth/             # API xác thực
│   ├── cart/             # API giỏ hàng
│   ├── orders/           # API quản lý đơn hàng
│   └── reviews/          # API hệ thống đánh giá
├── assets/               # Tài nguyên tĩnh
│   ├── css/             # File CSS
│   ├── js/              # File JavaScript
│   └── images/          # Hình ảnh upload
├── config/              # File cấu hình
├── includes/            # Component PHP dùng chung
├── pages/               # Trang khách hàng
└── index.php           # Trang chủ
```

## 🎯 Giải Thích Tính Năng Chính

### Quản Lý Sản Phẩm
- Danh mục sản phẩm động với phân loại
- Upload và quản lý hình ảnh
- Theo dõi tồn kho và cảnh báo hết hàng
- Định giá với hỗ trợ giảm giá

### Hệ Thống Giỏ Hàng
- Giỏ hàng dựa trên session cho khách vãng lai
- Giỏ hàng lưu database cho người dùng đã đăng nhập
- Cập nhật giỏ hàng thời gian thực qua AJAX
- Hiển thị số lượng giỏ hàng ở header

### Xử Lý Đơn Hàng
- Quy trình thanh toán nhiều bước
- Nhiều phương thức thanh toán (COD, Chuyển khoản, MoMo)
- Theo dõi trạng thái đơn hàng
- Thông báo email

### Hệ Thống Đánh Giá
- Hệ thống đánh giá 5 sao
- Kiểm duyệt đánh giá (chờ duyệt/đã duyệt/từ chối)
- Tính toán điểm đánh giá trung bình
- Hiển thị đánh giá trên trang sản phẩm

## 🚀 Hướng Dẫn Sử Dụng

### Dành Cho Khách Hàng
1. Duyệt sản phẩm trên trang chủ
2. Sử dụng tìm kiếm và bộ lọc để tìm loại gạo cụ thể
3. Thêm sản phẩm vào giỏ hàng
4. Đăng ký/đăng nhập để thanh toán
5. Hoàn tất mua hàng với phương thức thanh toán ưa thích
6. Theo dõi trạng thái đơn hàng trong profile
7. Để lại đánh giá cho sản phẩm đã mua

### Dành Cho Quản Trị Viên
1. Truy cập panel admin tại `/admin/`
2. Đăng nhập với tài khoản admin
3. Quản lý sản phẩm, danh mục và đơn hàng
4. Theo dõi doanh số qua bảng điều khiển
5. Kiểm duyệt đánh giá khách hàng

## 🤝 Đóng Góp

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/TinhNangMoi`)
3. Commit thay đổi (`git commit -m 'Thêm tính năng mới'`)
4. Push lên branch (`git push origin feature/TinhNangMoi`)
5. Mở Pull Request

## 📝 Giấy Phép

Dự án này được cấp phép theo giấy phép MIT - xem file [LICENSE](LICENSE) để biết chi tiết.

## 👥 Tác Giả

- **Hồ Anh Khoa** - [GitHub của bạn](https://github.com/HooAnhKhoa)

## 🙏 Lời Cảm Ơn

- Đội ngũ Bootstrap cho CSS framework tuyệt vời
- Font Awesome cho các icon đẹp
- Cộng đồng PHP cho sự hỗ trợ liên tục
- Tất cả contributors đã giúp cải thiện dự án này

## 📞 Hỗ Trợ

Nếu bạn gặp vấn đề hoặc có câu hỏi:
- Tạo issue trên GitHub
- Liên hệ: email.cua.ban@example.com


**Tài khoản demo:**
- Admin: admin@gao.com / 123456
- Khách hàng: customer@example.com / 123456
---

Được tạo với ❤️ dành cho những người yêu gạo Việt Nam
