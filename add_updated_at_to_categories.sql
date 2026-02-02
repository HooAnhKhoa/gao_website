-- Thêm cột updated_at vào bảng categories
ALTER TABLE `categories` 
ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() 
AFTER `created_at`;

-- Cập nhật giá trị updated_at cho các bản ghi hiện có
UPDATE `categories` SET `updated_at` = `created_at` WHERE `updated_at` IS NULL;