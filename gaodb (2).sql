-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 01, 2026 at 09:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gaodb`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL COMMENT 'Dành cho khách vãng lai',
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `session_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 1, 8, '2026-01-25 11:22:18', '2026-01-25 17:55:13'),
(2, 3, NULL, 2, 4, '2026-01-25 11:23:45', '2026-01-25 17:28:36'),
(4, NULL, 'qdgk4tdpia23drtm6t9mnpft50', 7, 2, '2026-01-25 17:18:54', '2026-01-25 23:18:54');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `image`, `status`, `created_at`) VALUES
(1, 'Gạo thơm', 'gao-thom', 'Các loại gạo thơm ngon, hạt dài', NULL, NULL, 'active', '2026-01-07 15:08:15'),
(2, 'Gạo nếp', 'gao-nep', 'Gạo nếp các loại', NULL, NULL, 'active', '2026-01-07 15:08:15'),
(3, 'Gạo lứt', 'gao-lut', 'Gạo lứt giàu dinh dưỡng', NULL, NULL, 'active', '2026-01-07 15:08:15'),
(4, 'Gạo đặc sản', 'gao-dac-san', 'Gạo đặc sản vùng miền', NULL, NULL, 'active', '2026-01-07 15:08:15'),
(5, 'Gạo hữu cơ', 'gao-huu-co', 'Gạo trồng theo phương pháp hữu cơ', NULL, NULL, 'active', '2026-01-07 15:08:15'),
(6, 'Gạo dinh dưỡng', 'gao-dinh-duong', 'Gạo bổ sung dinh dưỡng', NULL, NULL, 'active', '2026-01-07 15:08:15');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `note` text DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cod','bank_transfer','momo') DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `note`, `total_amount`, `shipping_fee`, `discount_amount`, `final_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`, `updated_at`) VALUES
(1, 'DH202601260CDF2E', 1, 'Quản trị viên', 'admin@gao.com', '0123456789', 'Hà Nội', '', 360000.00, 30000.00, 0.00, 390000.00, '', 'pending', 'pending', '2026-01-25 17:02:08', '2026-01-25 17:02:08'),
(2, 'DH20260126494AFC', 1, 'Quản trị viên', 'admin@gao.com', '0123456789', 'Hà Nội', '', 360000.00, 30000.00, 0.00, 390000.00, 'cod', 'pending', 'pending', '2026-01-25 17:02:12', '2026-01-25 17:02:12'),
(3, 'DH202601262CCC09', 1, 'Quản trị viên', 'admin@gao.com', '0123456789', 'Hà Nội', '', 360000.00, 30000.00, 0.00, 390000.00, 'cod', 'pending', 'processing', '2026-01-25 17:03:46', '2026-02-01 02:09:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `total_price`) VALUES
(1, 3, 7, 'gao ngon', 90000.00, 4, 360000.00);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL COMMENT 'Khối lượng tính bằng kg',
  `unit` varchar(20) DEFAULT 'kg',
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `images` text DEFAULT NULL COMMENT 'JSON array of additional images',
  `stock_quantity` int(11) DEFAULT 0,
  `origin` varchar(100) DEFAULT NULL COMMENT 'Xuất xứ',
  `cooking_guide` text DEFAULT NULL COMMENT 'Hướng dẫn nấu',
  `nutritional_info` text DEFAULT NULL COMMENT 'JSON: Thông tin dinh dưỡng',
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `weight`, `unit`, `category_id`, `image`, `images`, `stock_quantity`, `origin`, `cooking_guide`, `nutritional_info`, `rating`, `total_reviews`, `status`, `featured`, `created_at`, `updated_at`, `views`) VALUES
(1, 'Gạo ST25', 'gao-st25', 'Gạo thơm ngon nhất thế giới 2021', NULL, 45000.00, 42000.00, 5.00, 'kg', 1, 'st25.jpg', NULL, 100, 'Sóc Trăng', NULL, NULL, 0.00, 0, 'inactive', 0, '2026-01-07 15:08:15', '2026-01-25 22:44:42', 12),
(2, 'Gạo nếp cái hoa vàng', 'gao-nep-cai-hoa-vang', 'Gạo nếp thơm ngon', NULL, 35000.00, NULL, 5.00, 'kg', 2, 'nep-cai-hoa-vang.jpg', NULL, 150, 'Hưng Yên', NULL, NULL, 0.00, 0, 'active', 0, '2026-01-07 15:08:15', '2026-01-25 23:19:35', 1),
(3, 'Gạo lứt huyết rồng', 'gao-lut-huyet-rong', 'Gạo lứt giàu dinh dưỡng', NULL, 50000.00, 45000.00, 5.00, 'kg', 3, 'lut-huyet-rong.jpg', NULL, 80, 'Đồng Tháp', NULL, NULL, 0.00, 0, 'active', 0, '2026-01-07 15:08:15', '2026-01-07 15:08:15', 0),
(4, 'Gạo Tám xoan Hải Hậu', 'gao-tam-xoan-hai-hau', 'Gạo đặc sản Nam Định', NULL, 55000.00, NULL, 5.00, 'kg', 4, 'tam-xoan.jpg', NULL, 60, 'Nam Định', NULL, NULL, 0.00, 0, 'active', 0, '2026-01-07 15:08:15', '2026-01-07 15:08:15', 0),
(5, 'Gạo hữu cơ Japonica', 'gao-huu-co-japonica', 'Gạo hữu cơ nhật bản', NULL, 75000.00, 70000.00, 5.00, 'kg', 5, 'japonica.jpg', NULL, 40, 'Đà Lạt', NULL, NULL, 0.00, 0, 'active', 0, '2026-01-07 15:08:15', '2026-01-25 17:06:39', 51),
(6, 'Gạo lứt tím than', 'gao-lut-tim-than', 'Gạo dinh dưỡng cao', NULL, 48000.00, NULL, 5.00, 'kg', 6, 'lut-tim-than.jpg', NULL, 120, 'Cần Thơ', NULL, NULL, 0.00, 0, 'active', 0, '2026-01-07 15:08:15', '2026-01-07 15:08:15', 0),
(7, 'gao ngon', 'gao-ngon', 'ngon', 'ko co', 100000.00, 90000.00, 1.00, 'kg', 1, '69769f2451753_1769381668.jpg', NULL, 100, 'Việt Nam', 'asssssssssssss', '', 0.00, 0, 'active', 0, '2026-01-25 16:54:28', '2026-01-25 23:19:32', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `images` text DEFAULT NULL COMMENT 'JSON array of review images',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `avatar`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gao.com', '$2y$10$lELhZgoLiT/rqM.gbzrrRO4KSCa1lQ4RGeKOOm0YtslvDFf1n1Umq', 'Quản trị viên', '0123456789', 'Hà Nội', NULL, 'admin', 'active', '2026-01-07 15:08:15', '2026-01-25 17:57:54'),
(2, 'user1', 'user1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0987654321', 'TP Hồ Chí Minh', NULL, 'user', 'active', '2026-01-07 15:08:15', '2026-01-07 15:08:15'),
(3, 'anhkhoa12_1769356354', 'anhkhoa12@gmail.com', '$2y$10$lELhZgoLiT/rqM.gbzrrRO4KSCa1lQ4RGeKOOm0YtslvDFf1n1Umq', 'Here first post', '0123123122', NULL, NULL, 'user', 'active', '2026-01-25 09:52:34', '2026-01-25 15:52:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
