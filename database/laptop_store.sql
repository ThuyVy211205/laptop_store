-- ============================================================
-- VQSTORE - Laptop & VQ Accessories Store
-- Full Database Schema + Sample Data
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Create database
CREATE DATABASE IF NOT EXISTS `laptop_store` 
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `laptop_store`;

-- ============================================================
-- TABLE: users (Customers)
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `birthday` DATE DEFAULT NULL,
    `gender` ENUM('male','female','other') DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `rank` ENUM('silver','gold','diamond') DEFAULT 'silver',
    `total_spent` DECIMAL(15,2) DEFAULT 0,
    `total_orders` INT DEFAULT 0,
    `google_id` VARCHAR(100) DEFAULT NULL,
    `reset_token` VARCHAR(100) DEFAULT NULL,
    `reset_expires` DATETIME DEFAULT NULL,
    `status` ENUM('active','blocked') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_google (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: admins
-- ============================================================
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` VARCHAR(50) DEFAULT 'super_admin',
    `status` ENUM('active','blocked') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: employees
-- ============================================================
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` VARCHAR(50) DEFAULT 'Nhân viên bán hàng',
    `status` ENUM('active','blocked') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: categories
-- ============================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT 'folder',
    `image` VARCHAR(255) DEFAULT NULL,
    `parent_id` INT DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active','inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: products
-- ============================================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `sku` VARCHAR(100) DEFAULT NULL,
    `brand` VARCHAR(100) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `specs` JSON DEFAULT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `sale_price` DECIMAL(15,2) DEFAULT NULL,
    `stock` INT DEFAULT 0,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `views` INT DEFAULT 0,
    `sold_quantity` INT DEFAULT 0,
    `rating_avg` DECIMAL(3,2) DEFAULT 0,
    `rating_count` INT DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_flash_sale` TINYINT(1) DEFAULT 0,
    `is_new` TINYINT(1) DEFAULT 0,
    `flash_sale_end` DATETIME DEFAULT NULL,
    `status` ENUM('active','inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category_id),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: product_images
-- ============================================================
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: vouchers
-- ============================================================
DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('percent','fixed') DEFAULT 'percent',
    `value` DECIMAL(15,2) NOT NULL,
    `min_order` DECIMAL(15,2) DEFAULT 0,
    `max_discount` DECIMAL(15,2) DEFAULT NULL,
    `quantity` INT DEFAULT 0,
    `used_count` INT DEFAULT 0,
    `expires_at` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: orders
-- ============================================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT DEFAULT NULL,
    `shipping_name` VARCHAR(100) NOT NULL,
    `shipping_phone` VARCHAR(20) NOT NULL,
    `shipping_email` VARCHAR(100) DEFAULT NULL,
    `shipping_address` TEXT NOT NULL,
    `note` TEXT DEFAULT NULL,
    `subtotal` DECIMAL(15,2) NOT NULL,
    `discount_amount` DECIMAL(15,2) DEFAULT 0,
    `shipping_fee` DECIMAL(15,2) DEFAULT 0,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `voucher_id` INT DEFAULT NULL,
    `voucher_code` VARCHAR(50) DEFAULT NULL,
    `payment_method` ENUM('cod','bank','momo','vnpay') DEFAULT 'cod',
    `payment_status` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    `status` ENUM('pending','confirmed','shipping','delivered','completed','cancelled') DEFAULT 'pending',
    `cancel_reason` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_code (order_code),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: order_details
-- ============================================================
DROP TABLE IF EXISTS `order_details`;
CREATE TABLE `order_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `quantity` INT NOT NULL,
    `subtotal` DECIMAL(15,2) NOT NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: carts
-- ============================================================
DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: reviews
-- ============================================================
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `rating` TINYINT NOT NULL,
    `content` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: comments
-- ============================================================
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `parent_id` INT DEFAULT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    INDEX idx_parent (parent_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: payments
-- ============================================================
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `method` VARCHAR(20) NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `transaction_id` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('pending','success','failed') DEFAULT 'pending',
    `paid_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: notifications
-- ============================================================
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT,
    `type` VARCHAR(50) DEFAULT 'info',
    `link` VARCHAR(255) DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: banners
-- ============================================================
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) DEFAULT NULL,
    `subtitle` VARCHAR(255) DEFAULT NULL,
    `image` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255) DEFAULT NULL,
    `position` VARCHAR(50) DEFAULT 'hero',
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: wishlists
-- ============================================================
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Default admin (password: Admin@123)
INSERT INTO `admins` (`full_name`, `email`, `password`, `phone`, `role`) VALUES
('Super Admin', 'admin@vqstore.vn', '$2y$10$pUcwz3PSGZuTpYDz1GgKgemFdHbjf0RyN/wfSWNqRBJP9DCpmJtVe', '0901234567', 'super_admin');

-- Sample employees (password: Employee@123)
INSERT INTO `employees` (`full_name`, `email`, `password`, `phone`, `role`) VALUES
('Nguyễn Văn Sales', 'sales@vqstore.vn', '$2y$10$DJtJqAa9zXKAEWFgQfX3Y.lHsJ.zWlMxFM4tVCpvUEKxNzGhxbe1G', '0912345678', 'Nhân viên bán hàng'),
('Trần Thị Kho', 'kho@vqstore.vn', '$2y$10$DJtJqAa9zXKAEWFgQfX3Y.lHsJ.zWlMxFM4tVCpvUEKxNzGhxbe1G', '0923456789', 'Kho hàng');

-- Categories
INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `sort_order`) VALUES
('Laptop Gaming', 'laptop-gaming', 'Laptop dành cho game thủ với cấu hình mạnh mẽ', 'gamepad', 1),
('Laptop Văn Phòng', 'laptop-van-phong', 'Laptop mỏng nhẹ, pin trâu cho công việc', 'briefcase', 2),
('MacBook', 'macbook', 'Apple MacBook chính hãng', 'apple', 3),
('Bàn Phím Cơ', 'ban-phim-co', 'Bàn phím cơ gaming và làm việc', 'keyboard', 4),
('Chuột Gaming', 'chuot-gaming', 'Chuột chuyên dụng cho game thủ', 'mouse', 5),
('Tai Nghe', 'tai-nghe', 'Tai nghe gaming và âm thanh chuyên nghiệp', 'headphones', 6);

-- Sample users (password for all: User@123)
INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `rank`, `total_spent`, `total_orders`) VALUES
('Nguyễn Văn An', 'an.nguyen@gmail.com', '$2y$10$qZX5p.7Y6F5w8VTNa1L1Y.q.dKvBjmTRSt4ZxX7fS8K3yQy8L1L1q', '0987654321', 'gold', 25000000, 5),
('Trần Thị Bình', 'binh.tran@gmail.com', '$2y$10$qZX5p.7Y6F5w8VTNa1L1Y.q.dKvBjmTRSt4ZxX7fS8K3yQy8L1L1q', '0976543210', 'silver', 8500000, 2),
('Lê Hoàng Cường', 'cuong.le@gmail.com', '$2y$10$qZX5p.7Y6F5w8VTNa1L1Y.q.dKvBjmTRSt4ZxX7fS8K3yQy8L1L1q', '0965432109', 'diamond', 75000000, 12);

-- Products - Laptop Gaming
INSERT INTO `products` (`category_id`, `name`, `slug`, `sku`, `brand`, `description`, `specs`, `price`, `sale_price`, `stock`, `thumbnail`, `is_featured`, `is_flash_sale`, `is_new`, `flash_sale_end`, `sold_quantity`, `rating_avg`, `rating_count`) VALUES
(1, 'ASUS ROG Strix G15 G513 R7-6800H/16GB/512GB/RTX3050', 'asus-rog-strix-g15-g513-r7', 'ASUS-ROG-G513', 'ASUS', 
'ASUS ROG Strix G15 G513 - Laptop gaming mạnh mẽ với CPU AMD Ryzen 7 thế hệ mới, card đồ họa RTX 3050, màn hình 144Hz cho trải nghiệm gaming đỉnh cao.',
'{"CPU":"AMD Ryzen 7 6800H","RAM":"16GB DDR5","Ổ cứng":"512GB SSD NVMe","Màn hình":"15.6\\" FHD 144Hz","Card đồ họa":"NVIDIA RTX 3050 4GB","Hệ điều hành":"Windows 11"}',
32990000, 27990000, 15, 'assets/images/laptop_asus_gaming_rog_strix_g15.1.webp', 1, 1, 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 45, 4.8, 32),

(1, 'MSI Katana GF66 i7-11800H/16GB/512GB/RTX3060', 'msi-katana-gf66-i7-rtx3060', 'MSI-KATANA-GF66', 'MSI',
'MSI Katana GF66 - Thiết kế hầm hố đậm chất gaming với hiệu năng vượt trội từ Intel Core i7 và RTX 3060.',
'{"CPU":"Intel Core i7-11800H","RAM":"16GB DDR4","Ổ cứng":"512GB SSD","Màn hình":"15.6\\" FHD 144Hz","Card đồ họa":"NVIDIA RTX 3060 6GB","Hệ điều hành":"Windows 11"}',
35990000, 30990000, 8, 'assets/images/laptop-msi-katana-gf66-i7.webp', 1, 1, 0, DATE_ADD(NOW(), INTERVAL 5 DAY), 28, 4.7, 19),

(1, 'Acer Nitro 5 i5-12500H/8GB/512GB/RTX3050', 'acer-nitro-5-i5-12500h', 'ACER-NITRO-5', 'Acer',
'Acer Nitro 5 - Laptop gaming giá tốt với cấu hình ổn định, phù hợp cho game thủ mới bắt đầu.',
'{"CPU":"Intel Core i5-12500H","RAM":"8GB DDR4","Ổ cứng":"512GB SSD","Màn hình":"15.6\\" FHD 144Hz","Card đồ họa":"NVIDIA RTX 3050 4GB"}',
22990000, 19990000, 20, 'assets/images/acer_nitro_5_tiger_1.webp', 1, 0, 1, NULL, 67, 4.6, 41),

(1, 'Lenovo Legion 5 Pro R7-6800H/16GB/1TB/RTX3070', 'lenovo-legion-5-pro-r7-rtx3070', 'LENOVO-LEGION-5P', 'Lenovo',
'Lenovo Legion 5 Pro - Flagship gaming với màn hình QHD 165Hz và card đồ họa RTX 3070 cực mạnh.',
'{"CPU":"AMD Ryzen 7 6800H","RAM":"16GB DDR5","Ổ cứng":"1TB SSD NVMe","Màn hình":"16\\" QHD 165Hz","Card đồ họa":"NVIDIA RTX 3070 8GB"}',
45990000, NULL, 5, 'assets/images/lenovo-legion-pro7.webp', 1, 0, 1, NULL, 18, 4.9, 15),

-- Laptop Văn Phòng
(2, 'Dell Vostro 3520 i5-1235U/8GB/256GB SSD', 'dell-vostro-3520-i5', 'DELL-VOSTRO-3520', 'Dell',
'Dell Vostro 3520 - Laptop văn phòng bền bỉ, hiệu năng ổn định cho công việc hàng ngày.',
'{"CPU":"Intel Core i5-1235U","RAM":"8GB DDR4","Ổ cứng":"256GB SSD","Màn hình":"15.6\\" FHD","Card đồ họa":"Intel Iris Xe"}',
16990000, 14990000, 25, 'assets/images/dell-vostro.webp', 1, 1, 0, DATE_ADD(NOW(), INTERVAL 3 DAY), 89, 4.5, 56),

(2, 'HP Pavilion 15 i7-1255U/16GB/512GB', 'hp-pavilion-15-i7', 'HP-PAVILION-15', 'HP',
'HP Pavilion 15 - Thiết kế sang trọng, mỏng nhẹ, phù hợp cho doanh nhân và sinh viên.',
'{"CPU":"Intel Core i7-1255U","RAM":"16GB DDR4","Ổ cứng":"512GB SSD","Màn hình":"15.6\\" FHD IPS","Card đồ họa":"Intel Iris Xe"}',
21990000, 19490000, 12, 'assets/images/hp-pavilion.webp', 1, 0, 1, NULL, 34, 4.7, 23),

(2, 'ASUS Vivobook 15 i3-1215U/8GB/256GB', 'asus-vivobook-15-i3', 'ASUS-VIVO-15', 'ASUS',
'ASUS Vivobook 15 - Laptop học tập giá rẻ, đầy đủ tính năng cho công việc cơ bản.',
'{"CPU":"Intel Core i3-1215U","RAM":"8GB DDR4","Ổ cứng":"256GB SSD","Màn hình":"15.6\\" FHD"}',
12990000, 10990000, 30, 'assets/images/asus-vivobook.webp', 0, 1, 0, DATE_ADD(NOW(), INTERVAL 7 DAY), 112, 4.4, 78),

-- MacBook
(3, 'MacBook Air M2 2022 8GB/256GB', 'macbook-air-m2-2022-8gb-256gb', 'MAC-AIR-M2', 'Apple',
'MacBook Air M2 - Chip M2 mạnh mẽ, thiết kế mỏng nhẹ chỉ 1.24kg, pin lên đến 18 giờ.',
'{"Chip":"Apple M2 8-core CPU","RAM":"8GB Unified","Ổ cứng":"256GB SSD","Màn hình":"13.6\\" Liquid Retina","Pin":"Lên đến 18 giờ"}',
27990000, 25990000, 18, 'assets/images/macbook-air-m2.webp', 1, 1, 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 56, 4.9, 47),

(3, 'MacBook Pro M3 14 inch 2023 18GB/512GB', 'macbook-pro-m3-14-2023', 'MAC-PRO-M3-14', 'Apple',
'MacBook Pro M3 14 inch - Hiệu năng đỉnh cao với chip M3, màn hình XDR sống động.',
'{"Chip":"Apple M3 Pro","RAM":"18GB Unified","Ổ cứng":"512GB SSD","Màn hình":"14.2\\" Liquid Retina XDR","Pin":"Lên đến 18 giờ"}',
54990000, NULL, 7, 'assets/images/macbook-pro-2023-m3.webp', 1, 0, 1, NULL, 21, 5.0, 12),

-- Bàn phím cơ
(4, 'Keychron K2 Pro RGB Hot-swap Brown Switch', 'keychron-k2-pro-rgb', 'KEY-K2-PRO', 'Keychron',
'Keychron K2 Pro - Bàn phím cơ không dây cao cấp, hỗ trợ hot-swap, RGB đẹp mắt.',
'{"Layout":"75% (84 phím)","Switch":"Gateron G Pro Brown","Kết nối":"USB-C / Bluetooth 5.1","Đèn":"RGB Per-key","Pin":"4000mAh"}',
3490000, 2890000, 22, 'assets/images/ban-phim-keychron.webp', 1, 1, 0, DATE_ADD(NOW(), INTERVAL 5 DAY), 134, 4.8, 89),

(4, 'AKKO 3098B Multi-modes World Tour Tokyo', 'akko-3098b-tokyo', 'AKKO-3098B', 'AKKO',
'AKKO 3098B Tokyo - Bàn phím cơ với keycap theme độc đáo, switch êm ái.',
'{"Layout":"96% (98 phím)","Switch":"AKKO V3 Cream Yellow","Kết nối":"USB-C / Bluetooth / 2.4Ghz","Keycap":"PBT Doubleshot"}',
2790000, 2390000, 15, 'assets/images/ban-phim-co-akko.webp', 1, 0, 1, NULL, 67, 4.7, 45),

-- Chuột Gaming
(5, 'Logitech G Pro X Superlight Wireless', 'logitech-gpro-x-superlight', 'LOG-GPRO-X', 'Logitech',
'Logitech G Pro X Superlight - Chuột gaming nhẹ nhất chỉ 63g, sensor HERO 25K.',
'{"Sensor":"HERO 25,600 DPI","Trọng lượng":"63g","Pin":"70 giờ","Kết nối":"LIGHTSPEED Wireless","Switch":"Mechanical"}',
3290000, 2790000, 20, 'assets/images/chuot-khong-day-bluetooth-logitech.webp', 1, 1, 0, DATE_ADD(NOW(), INTERVAL 4 DAY), 156, 4.9, 124),

(5, 'Razer DeathAdder V3 Pro Wireless', 'razer-deathadder-v3-pro', 'RAZER-DA-V3', 'Razer',
'Razer DeathAdder V3 Pro - Form chuột huyền thoại với công nghệ mới nhất.',
'{"Sensor":"Focus Pro 30K","Trọng lượng":"63g","Pin":"90 giờ","Kết nối":"HyperSpeed Wireless"}',
3890000, 3490000, 12, 'assets/images/chuot-gaming-asus-tuf.webp', 1, 0, 1, NULL, 78, 4.8, 56),

-- Tai nghe
(6, 'SteelSeries Arctis Nova 7 Wireless', 'steelseries-arctis-nova-7', 'SS-NOVA-7', 'SteelSeries',
'SteelSeries Arctis Nova 7 - Tai nghe gaming wireless cao cấp với âm thanh 360° Spatial.',
'{"Driver":"40mm Neodymium","Tần số":"20Hz - 22kHz","Kết nối":"2.4GHz Wireless + Bluetooth","Pin":"38 giờ","Microphone":"ClearCast Gen 2"}',
4990000, 4290000, 10, 'assets/images/tai-nghe-bluetooth-arctis-nova.webp', 1, 1, 0, DATE_ADD(NOW(), INTERVAL 6 DAY), 89, 4.8, 67),

-- Laptop Văn Phòng (thêm)
(2, 'Lenovo IdeaPad Slim 5 i5-1335U/16GB/512GB', 'lenovo-ideapad-slim5-i5', 'LENOVO-SLIM5', 'Lenovo',
'Lenovo IdeaPad Slim 5 - Thiết kế siêu mỏng nhẹ, màn hình OLED sắc nét, hiệu năng tốt cho công việc và học tập.',
'{"CPU":"Intel Core i5-1335U","RAM":"16GB DDR5","Ổ cứng":"512GB SSD","Màn hình":"14\\" OLED 2.8K","Card đồ họa":"Intel Iris Xe"}',
18990000, 16990000, 18, 'assets/images/lenovo-IdeaPad-slim5.webp', 1, 0, 1, NULL, 43, 4.7, 28),

-- MacBook (thêm)
(3, 'MacBook Air M4 2025 16GB/256GB', 'macbook-air-m4-2025', 'MAC-AIR-M4', 'Apple',
'MacBook Air M4 2025 - Chip M4 thế hệ mới nhất, hiệu năng vượt trội, pin lên đến 20 giờ.',
'{"Chip":"Apple M4 10-core CPU","RAM":"16GB Unified","Ổ cứng":"256GB SSD","Màn hình":"13.6\\" Liquid Retina","Pin":"Lên đến 20 giờ"}',
32990000, 29990000, 10, 'assets/images/macbook-air-m4.webp', 1, 1, 1, DATE_ADD(NOW(), INTERVAL 5 DAY), 31, 4.9, 18),

(3, 'MacBook Pro M1 Pro 14 inch 16GB/512GB', 'macbook-pro-m1-pro-14', 'MAC-PRO-M1', 'Apple',
'MacBook Pro M1 Pro 14 inch - Hiệu năng chuyên nghiệp với chip M1 Pro, màn hình Liquid Retina XDR.',
'{"Chip":"Apple M1 Pro 10-core CPU","RAM":"16GB Unified","Ổ cứng":"512GB SSD","Màn hình":"14.2\\" Liquid Retina XDR","Pin":"Lên đến 17 giờ"}',
42990000, 38990000, 6, 'assets/images/macbook-pro-m1.webp', 1, 0, 0, NULL, 27, 4.8, 21),

-- Bàn Phím Cơ (thêm)
(4, 'Ducky One 3 TKL RGB Cherry MX Red', 'ducky-one-3-tkl-rgb', 'DUCKY-ONE3-TKL', 'Ducky',
'Ducky One 3 TKL - Bàn phím cơ cao cấp Đài Loan, build chắc chắn, RGB đẹp, switch Cherry MX chính hãng.',
'{"Layout":"TKL (87 phím)","Switch":"Cherry MX Red","Keycap":"PBT Doubleshot","Đèn":"RGB Per-key","Kết nối":"USB-C"}',
2990000, 2590000, 12, 'assets/images/ban-phim-ducky-one.webp', 1, 0, 1, NULL, 58, 4.8, 37),

(4, 'RK ROYAL KLUDGE RK84 RGB Wireless', 'rk-royal-kludge-rk84', 'RK-RK84', 'RK ROYAL',
'RK84 - Bàn phím cơ 75% không dây 3 chế độ kết nối, giá tốt cho người mới.',
'{"Layout":"75% (84 phím)","Switch":"RK Brown","Kết nối":"USB-C / Bluetooth 5.0 / 2.4GHz","Đèn":"RGB","Pin":"3800mAh"}',
1490000, 1190000, 25, 'assets/images/ban-phim-RK-ROYAL.webp', 0, 1, 0, DATE_ADD(NOW(), INTERVAL 4 DAY), 145, 4.5, 92),

-- Chuột (thêm)
(5, 'Apple Magic Mouse 3', 'apple-magic-mouse-3', 'APPLE-MAGIC-M3', 'Apple',
'Apple Magic Mouse 3 - Chuột không dây chính hãng Apple, mặt Multi-Touch, thiết kế tối giản sang trọng.',
'{"Kết nối":"Bluetooth","Mặt cảm ứng":"Multi-Touch","Pin":"Lightning sạc","Tương thích":"macOS / iPadOS"}',
1990000, 1790000, 15, 'assets/images/chuot-apple.webp', 0, 0, 1, NULL, 62, 4.6, 43),

(5, 'AULA F808 Wired RGB Gaming', 'aula-f808-wired-rgb', 'AULA-F808', 'AULA',
'AULA F808 - Chuột gaming có dây giá rẻ, sensor 6400DPI, RGB, phù hợp cho game thủ mới.',
'{"Sensor":"Optical 6400 DPI","Nút":"7 nút lập trình","Đèn":"RGB","Kết nối":"USB có dây","Trọng lượng":"98g"}',
299000, 249000, 40, 'assets/images/chuot-co-day-aula.webp', 0, 1, 0, DATE_ADD(NOW(), INTERVAL 7 DAY), 203, 4.3, 115),

-- Tai Nghe (thêm)
(6, 'JBL Tune 670NC Bluetooth ANC', 'jbl-tune-670nc-bluetooth', 'JBL-TUNE-670NC', 'JBL',
'JBL Tune 670NC - Tai nghe chụp tai Bluetooth với công nghệ chống ồn chủ động, pin 70 giờ.',
'{"Driver":"40mm","Kết nối":"Bluetooth 5.3","ANC":"Adaptive Noise Cancelling","Pin":"70 giờ","Ứng dụng":"JBL Headphones"}',
1990000, 1690000, 20, 'assets/images/tai-nghe-JBL.webp', 1, 0, 1, NULL, 76, 4.6, 54),

(6, 'Bose QuietComfort 45 Wireless ANC', 'bose-quietcomfort-45', 'BOSE-QC45', 'Bose',
'Bose QuietComfort 45 - Tai nghe cao cấp với ANC hàng đầu thế giới, âm thanh chuẩn Hi-Fi.',
'{"Driver":"40mm","Kết nối":"Bluetooth 5.1 / 3.5mm","ANC":"QuietComfort","Pin":"24 giờ","Ứng dụng":"Bose Music"}',
8490000, 7490000, 8, 'assets/images/tai-nghe-chup-tai-bose.webp', 1, 0, 0, NULL, 34, 4.9, 29),

(6, 'Sony WH-1000XM5 Wireless ANC', 'sony-wh-1000xm5', 'SONY-XM5', 'Sony',
'Sony WH-1000XM5 - Tai nghe chống ồn đỉnh cao, âm thanh LDAC Hi-Res, pin 30 giờ.',
'{"Driver":"30mm","Kết nối":"Bluetooth 5.2 / 3.5mm","ANC":"Dual Noise Sensor","Pin":"30 giờ","Codec":"LDAC / AAC / SBC"}',
8990000, 7990000, 7, 'assets/images/tai-nghe-chup-tai-sony.webp', 1, 0, 1, NULL, 41, 4.9, 33);

-- Sample product images (multiple per product)
INSERT INTO `product_images` (`product_id`, `image_path`, `sort_order`) VALUES
(1, 'assets/images/laptop_asus_gaming_rog_strix_g15.1.webp', 1),
(1, 'assets/images/laptop_asus_gaming_rog_strix_g15.2.webp', 2),
(1, 'assets/images/laptop_asus_gaming_rog_strix_g15.3.webp', 3),
(2, 'assets/images/laptop-msi-katana-gf66-i7.webp', 1),
(2, 'assets/images/laptop-msi-katana-gf66-i7.1.webp', 2),
(8, 'assets/images/macbook-air-m2.webp', 1),
(8, 'assets/images/macbook-air-m2.1.webp', 2),
(8, 'assets/images/macbook-air-m2.2.webp', 3),
-- product 3 (Acer Nitro 5)
(3, 'assets/images/acer_nitro_5_tiger_1.webp', 1),
(3, 'assets/images/acer_nitro_5_tiger_2.webp', 2),
(3, 'assets/images/acer_nitro_5_tiger_3.webp', 3),
-- product 4 (Lenovo Legion)
(4, 'assets/images/lenovo-legion-pro7.webp', 1),
(4, 'assets/images/lenovo-legion-pro7.1.webp', 2),
(4, 'assets/images/lenovo-legion-pro7.2.webp', 3),
-- product 5 (Dell Vostro)
(5, 'assets/images/dell-vostro.webp', 1),
(5, 'assets/images/dell-vostro.1.webp', 2),
(5, 'assets/images/dell-vostro.2.webp', 3),
-- product 6 (HP Pavilion)
(6, 'assets/images/hp-pavilion.webp', 1),
(6, 'assets/images/hp-pavilion.1.webp', 2),
(6, 'assets/images/hp-pavilion.2.webp', 3),
-- product 7 (ASUS Vivobook)
(7, 'assets/images/asus-vivobook.webp', 1),
(7, 'assets/images/asus-vivobook.1.webp', 2),
(7, 'assets/images/asus-vivobook.2.webp', 3),
-- product 9 (MacBook Pro M3)
(9, 'assets/images/macbook-pro-2023-m3.webp', 1),
(9, 'assets/images/macbook-pro-2023-m3.1.webp', 2),
(9, 'assets/images/macbook-pro-2023-m3.2.webp', 3),
-- product 10 (Keychron K2)
(10, 'assets/images/ban-phim-keychron.webp', 1),
(10, 'assets/images/ban-phim-keychron.1.webp', 2),
(10, 'assets/images/ban-phim-keychron.2.webp', 3),
-- product 11 (AKKO 3098B)
(11, 'assets/images/ban-phim-co-akko.webp', 1),
(11, 'assets/images/ban-phim-co-akko.1.webp', 2),
(11, 'assets/images/ban-phim-co-akko.2.webp', 3),
-- product 12 (Logitech G Pro X)
(12, 'assets/images/chuot-khong-day-bluetooth-logitech.webp', 1),
(12, 'assets/images/chuot-khong-day-bluetooth-logitech.1.webp', 2),
(12, 'assets/images/chuot-khong-day-bluetooth-logitech.2.webp', 3),
-- product 13 (Razer DeathAdder)
(13, 'assets/images/chuot-gaming-asus-tuf.webp', 1),
(13, 'assets/images/chuot-gaming-asus-tuf.1.webp', 2),
(13, 'assets/images/chuot-gaming-asus-tuf.2.webp', 3),
-- product 14 (SteelSeries Arctis Nova 7)
(14, 'assets/images/tai-nghe-bluetooth-arctis-nova.webp', 1),
(14, 'assets/images/tai-nghe-bluetooth-arctis-nova.1.webp', 2),
(14, 'assets/images/tai-nghe-bluetooth-arctis-nova.2.webp', 3),
-- product 15 (Lenovo IdeaPad Slim 5)
(15, 'assets/images/lenovo-IdeaPad-slim5.webp', 1),
(15, 'assets/images/lenovo-IdeaPad-slim5.1.webp', 2),
(15, 'assets/images/lenovo-IdeaPad-slim5.2.webp', 3),
-- product 16 (MacBook Air M4)
(16, 'assets/images/macbook-air-m4.webp', 1),
(16, 'assets/images/macbook-air-m4.1.webp', 2),
(16, 'assets/images/macbook-air-m4.2.webp', 3),
-- product 17 (MacBook Pro M1)
(17, 'assets/images/macbook-pro-m1.webp', 1),
(17, 'assets/images/macbook-pro-m1.1.webp', 2),
(17, 'assets/images/macbook-pro-m1.2.webp', 3),
-- product 18 (Ducky One 3)
(18, 'assets/images/ban-phim-ducky-one.webp', 1),
(18, 'assets/images/ban-phim-ducky-one.1.webp', 2),
(18, 'assets/images/ban-phim-ducky-one.2.webp', 3),
-- product 19 (RK Royal RK84)
(19, 'assets/images/ban-phim-RK-ROYAL.webp', 1),
(19, 'assets/images/ban-phim-RK-ROYAL.2.webp', 2),
(19, 'assets/images/ban-phim-RK-ROYAL.3.webp', 3),
-- product 20 (Apple Magic Mouse)
(20, 'assets/images/chuot-apple.webp', 1),
(20, 'assets/images/chuot-apple.1.webp', 2),
(20, 'assets/images/chuot-apple.2.webp', 3),
-- product 21 (AULA F808)
(21, 'assets/images/chuot-co-day-aula.webp', 1),
(21, 'assets/images/chuot-co-day-aula.1.webp', 2),
(21, 'assets/images/chuot-co-day-aula.2.webp', 3),
-- product 22 (JBL Tune 670NC)
(22, 'assets/images/tai-nghe-JBL.webp', 1),
(22, 'assets/images/tai-nghe-JBL.1.webp', 2),
(22, 'assets/images/tai-nghe-JBL.2.webp', 3),
-- product 23 (Bose QuietComfort 45)
(23, 'assets/images/tai-nghe-chup-tai-bose.webp', 1),
(23, 'assets/images/tai-nghe-chup-tai-bose.1.webp', 2),
(23, 'assets/images/tai-nghe-chup-tai-bose.2.webp', 3),
-- product 24 (Sony WH-1000XM5)
(24, 'assets/images/tai-nghe-chup-tai-sony.webp', 1),
(24, 'assets/images/tai-nghe-chup-tai-sony.1.webp', 2),
(24, 'assets/images/tai-nghe-chup-tai-sony.2.webp', 3);

-- Vouchers
INSERT INTO `vouchers` (`code`, `type`, `value`, `min_order`, `max_discount`, `quantity`, `expires_at`, `is_active`) VALUES
('TECH10', 'percent', 10, 500000, 500000, 100, '2026-06-30 23:59:59', 1),
('GAME20', 'percent', 20, 1000000, 2000000, 50, '2026-06-30 23:59:59', 1),
('WELCOME500', 'fixed', 500000, 5000000, NULL, 200, DATE_ADD(NOW(), INTERVAL 60 DAY), 1),
('FLASH50', 'percent', 50, 2000000, 1000000, 10, DATE_ADD(NOW(), INTERVAL 30 DAY), 1);

-- Sample banners
INSERT INTO `banners` (`title`, `subtitle`, `image`, `link`, `position`, `sort_order`) VALUES
('GAMING LAPTOP', 'Giảm đến 30% - Hiệu năng đỉnh cao', 'assets/images/banner-laptop-gm.jpg', '/category/laptop-gaming', 'hero', 1),
('MACBOOK 2024', 'Sức mạnh chip M3 mới nhất', 'assets/images/banner-macbook.jpg', '/category/macbook', 'hero', 2),
('GAMING GEAR', 'Phụ kiện gaming chính hãng', 'assets/images/banner-phukien.jpg', '/category/chuot-gaming', 'hero', 3),
('LAPTOP VĂN PHÒNG', 'Mỏng nhẹ, pin trâu - Làm việc mọi lúc mọi nơi', 'assets/images/banner-laptop-vp.jpg', '/category/laptop-van-phong', 'hero', 4),
('SIÊU SALE', 'Laptop & phụ kiện giảm đến 50% - Số lượng có hạn!', 'assets/images/banner-sale.jpg', '/promotions', 'promo', 5);

-- Sample reviews
INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `content`) VALUES
(1, 1, 5, 'Laptop chạy game rất mượt, build chắc chắn, đáng đồng tiền!'),
(1, 2, 4, 'Tản nhiệt tốt, màn hình đẹp. Hơi nặng nhưng chấp nhận được với laptop gaming.'),
(8, 3, 5, 'MacBook Air M2 quá xịn! Pin trâu, thiết kế đẹp, hiệu năng mượt mà.'),
(10, 1, 5, 'Bàn phím gõ rất sướng, switch Brown êm tay, đèn RGB đẹp.');

-- Sample comments
INSERT INTO `comments` (`product_id`, `user_id`, `content`) VALUES
(1, 1, 'Sản phẩm có hỗ trợ trả góp 0% không shop?'),
(1, 2, 'Mình mua được 2 tuần rồi, dùng rất ổn nhé bạn!'),
(8, 3, 'Cho mình hỏi MacBook này có bản 16GB không ạ?');

-- Sample notifications
INSERT INTO `notifications` (`user_id`, `title`, `content`, `type`) VALUES
(1, 'Chào mừng đến với VQSTORE!', 'Cảm ơn bạn đã đăng ký tài khoản. Hãy khám phá ngay các sản phẩm hot!', 'info'),
(1, 'Voucher TECH10 đã sẵn sàng', 'Sử dụng mã TECH10 để giảm 10% cho đơn hàng đầu tiên', 'voucher');

-- ============================================================
-- TABLE: contacts (Tin nhắn liên hệ từ khách hàng)
-- ============================================================
DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `order_id` VARCHAR(50) DEFAULT NULL,
    `subject` VARCHAR(255) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new','read','replied') DEFAULT 'new',
    `reply_content` TEXT DEFAULT NULL,
    `replied_by` VARCHAR(100) DEFAULT NULL,
    `replied_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- HOÀN TẤT
-- ============================================================
-- Tài khoản admin mặc định:
--   Email:    admin@vqstore.vn
--   Password: Admin@123
--
-- Tài khoản user mẫu (password chung: User@123):
--   an.nguyen@gmail.com   (Gold rank)
--   binh.tran@gmail.com   (Silver rank)
--   cuong.le@gmail.com    (Diamond rank)
--
-- Vouchers:
--   TECH10      - Giảm 10% (đơn từ 500K)
--   GAME20      - Giảm 20% (đơn từ 1M)
--   WELCOME500  - Giảm 500K (đơn từ 5M)
--   FLASH50     - Giảm 50% (đơn từ 2M)
-- ============================================================
