-- ============================================================
-- VQSTORE - Cửa Hàng Laptop & Phụ Kiện
-- Cấu trúc CSDL + Dữ liệu mẫu (Phiên bản tiếng Việt)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Tạo cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS `laptop_store`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `laptop_store`;

-- ============================================================
-- BẢNG: nguoi_dung (Khách hàng)
-- ============================================================
DROP TABLE IF EXISTS `nguoi_dung`;
CREATE TABLE `nguoi_dung` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ho_ten` VARCHAR(100) NOT NULL,
    `thu_dien_tu` VARCHAR(100) NOT NULL UNIQUE,
    `mat_khau` VARCHAR(255) DEFAULT NULL,
    `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
    `anh_dai_dien` VARCHAR(255) DEFAULT NULL,
    `ngay_sinh` DATE DEFAULT NULL,
    `gioi_tinh` ENUM('male','female','other') DEFAULT NULL,
    `dia_chi` TEXT DEFAULT NULL,
    `hang` ENUM('silver','gold','diamond') DEFAULT 'silver',
    `tong_chi_tieu` DECIMAL(15,2) DEFAULT 0,
    `tong_don_hang` INT DEFAULT 0,
    `id_google` VARCHAR(100) DEFAULT NULL,
    `ma_dat_lai` VARCHAR(100) DEFAULT NULL,
    `han_dat_lai` DATETIME DEFAULT NULL,
    `trang_thai` ENUM('active','blocked') DEFAULT 'active',
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ngay_cap_nhat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_thu_dien_tu (thu_dien_tu),
    INDEX idx_google (id_google)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: quan_tri_vien (Quản trị viên)
-- ============================================================
DROP TABLE IF EXISTS `quan_tri_vien`;
CREATE TABLE `quan_tri_vien` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ho_ten` VARCHAR(100) NOT NULL,
    `thu_dien_tu` VARCHAR(100) NOT NULL UNIQUE,
    `mat_khau` VARCHAR(255) NOT NULL,
    `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
    `vai_tro` VARCHAR(50) DEFAULT 'super_admin',
    `trang_thai` ENUM('active','blocked') DEFAULT 'active',
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: nhan_vien (Nhân viên)
-- ============================================================
DROP TABLE IF EXISTS `nhan_vien`;
CREATE TABLE `nhan_vien` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ho_ten` VARCHAR(100) NOT NULL,
    `thu_dien_tu` VARCHAR(100) NOT NULL UNIQUE,
    `mat_khau` VARCHAR(255) NOT NULL,
    `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
    `vai_tro` VARCHAR(50) DEFAULT 'Nhân viên bán hàng',
    `trang_thai` ENUM('active','blocked') DEFAULT 'active',
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: danh_muc (Danh mục sản phẩm)
-- ============================================================
DROP TABLE IF EXISTS `danh_muc`;
CREATE TABLE `danh_muc` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ten` VARCHAR(100) NOT NULL,
    `duong_dan` VARCHAR(150) NOT NULL UNIQUE,
    `mo_ta` TEXT DEFAULT NULL,
    `bieu_tuong` VARCHAR(50) DEFAULT 'folder',
    `hinh_anh` VARCHAR(255) DEFAULT NULL,
    `id_cha` INT DEFAULT NULL,
    `thu_tu` INT DEFAULT 0,
    `trang_thai` ENUM('active','inactive') DEFAULT 'active',
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: san_pham (Sản phẩm)
-- ============================================================
DROP TABLE IF EXISTS `san_pham`;
CREATE TABLE `san_pham` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_danh_muc` INT NOT NULL,
    `ten` VARCHAR(255) NOT NULL,
    `duong_dan` VARCHAR(255) NOT NULL UNIQUE,
    `ma_san_pham` VARCHAR(100) DEFAULT NULL,
    `thuong_hieu` VARCHAR(100) DEFAULT NULL,
    `mo_ta` TEXT DEFAULT NULL,
    `thong_so` JSON DEFAULT NULL,
    `gia` DECIMAL(15,2) NOT NULL,
    `gia_khuyen_mai` DECIMAL(15,2) DEFAULT NULL,
    `ton_kho` INT DEFAULT 0,
    `hinh_thu_nho` VARCHAR(255) DEFAULT NULL,
    `luot_xem` INT DEFAULT 0,
    `so_luong_ban` INT DEFAULT 0,
    `diem_danh_gia` DECIMAL(3,2) DEFAULT 0,
    `so_danh_gia` INT DEFAULT 0,
    `la_noi_bat` TINYINT(1) DEFAULT 0,
    `la_flash_sale` TINYINT(1) DEFAULT 0,
    `la_moi` TINYINT(1) DEFAULT 0,
    `ket_thuc_flash_sale` DATETIME DEFAULT NULL,
    `trang_thai` ENUM('active','inactive') DEFAULT 'active',
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ngay_cap_nhat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_danh_muc (id_danh_muc),
    INDEX idx_duong_dan (duong_dan),
    INDEX idx_trang_thai (trang_thai),
    FOREIGN KEY (id_danh_muc) REFERENCES danh_muc(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: anh_san_pham (Hình ảnh sản phẩm)
-- ============================================================
DROP TABLE IF EXISTS `anh_san_pham`;
CREATE TABLE `anh_san_pham` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_san_pham` INT NOT NULL,
    `duong_dan_anh` VARCHAR(255) NOT NULL,
    `thu_tu` INT DEFAULT 0,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_san_pham (id_san_pham),
    FOREIGN KEY (id_san_pham) REFERENCES san_pham(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: phieu_giam_gia (Phiếu giảm giá / Voucher)
-- ============================================================
DROP TABLE IF EXISTS `phieu_giam_gia`;
CREATE TABLE `phieu_giam_gia` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_phieu` VARCHAR(50) NOT NULL UNIQUE,
    `loai` ENUM('percent','fixed') DEFAULT 'percent',
    `gia_tri` DECIMAL(15,2) NOT NULL,
    `don_hang_toi_thieu` DECIMAL(15,2) DEFAULT 0,
    `giam_toi_da` DECIMAL(15,2) DEFAULT NULL,
    `so_luong` INT DEFAULT 0,
    `so_lan_dung` INT DEFAULT 0,
    `ngay_het_han` DATETIME DEFAULT NULL,
    `dang_hoat_dong` TINYINT(1) DEFAULT 1,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: don_hang (Đơn hàng)
-- ============================================================
DROP TABLE IF EXISTS `don_hang`;
CREATE TABLE `don_hang` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_don_hang` VARCHAR(50) NOT NULL UNIQUE,
    `id_nguoi_dung` INT DEFAULT NULL,
    `ten_giao_hang` VARCHAR(100) NOT NULL,
    `sdt_giao_hang` VARCHAR(20) NOT NULL,
    `thu_dien_tu_giao_hang` VARCHAR(100) DEFAULT NULL,
    `dia_chi_giao_hang` TEXT NOT NULL,
    `ghi_chu` TEXT DEFAULT NULL,
    `tam_tinh` DECIMAL(15,2) NOT NULL,
    `so_tien_giam` DECIMAL(15,2) DEFAULT 0,
    `phi_van_chuyen` DECIMAL(15,2) DEFAULT 0,
    `tong_tien` DECIMAL(15,2) NOT NULL,
    `id_phieu_giam` INT DEFAULT NULL,
    `ma_phieu_giam` VARCHAR(50) DEFAULT NULL,
    `phuong_thuc_thanh_toan` ENUM('cod','bank','momo','vnpay') DEFAULT 'cod',
    `trang_thai_thanh_toan` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    `trang_thai` ENUM('pending','confirmed','shipping','delivered','completed','cancelled') DEFAULT 'pending',
    `ly_do_huy` TEXT DEFAULT NULL,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ngay_cap_nhat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nguoi_dung (id_nguoi_dung),
    INDEX idx_trang_thai (trang_thai),
    INDEX idx_ma_don (ma_don_hang),
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: chi_tiet_don (Chi tiết đơn hàng)
-- ============================================================
DROP TABLE IF EXISTS `chi_tiet_don`;
CREATE TABLE `chi_tiet_don` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_don_hang` INT NOT NULL,
    `id_san_pham` INT NOT NULL,
    `ten_san_pham` VARCHAR(255) NOT NULL,
    `hinh_thu_nho` VARCHAR(255) DEFAULT NULL,
    `gia` DECIMAL(15,2) NOT NULL,
    `so_luong` INT NOT NULL,
    `tam_tinh` DECIMAL(15,2) NOT NULL,
    INDEX idx_don_hang (id_don_hang),
    INDEX idx_san_pham (id_san_pham),
    FOREIGN KEY (id_don_hang) REFERENCES don_hang(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: gio_hang (Giỏ hàng)
-- ============================================================
DROP TABLE IF EXISTS `gio_hang`;
CREATE TABLE `gio_hang` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_nguoi_dung` INT NOT NULL,
    `id_san_pham` INT NOT NULL,
    `so_luong` INT DEFAULT 1,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_product (id_nguoi_dung, id_san_pham),
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_san_pham) REFERENCES san_pham(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: danh_gia (Đánh giá sản phẩm)
-- ============================================================
DROP TABLE IF EXISTS `danh_gia`;
CREATE TABLE `danh_gia` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_san_pham` INT NOT NULL,
    `id_nguoi_dung` INT NOT NULL,
    `diem_so` TINYINT NOT NULL,
    `noi_dung` TEXT,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_san_pham (id_san_pham),
    FOREIGN KEY (id_san_pham) REFERENCES san_pham(id) ON DELETE CASCADE,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: binh_luan (Bình luận sản phẩm)
-- ============================================================
DROP TABLE IF EXISTS `binh_luan`;
CREATE TABLE `binh_luan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_san_pham` INT NOT NULL,
    `id_nguoi_dung` INT NOT NULL,
    `id_cha` INT DEFAULT NULL,
    `noi_dung` TEXT NOT NULL,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_san_pham (id_san_pham),
    INDEX idx_cha (id_cha),
    FOREIGN KEY (id_san_pham) REFERENCES san_pham(id) ON DELETE CASCADE,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: thanh_toan (Thanh toán)
-- ============================================================
DROP TABLE IF EXISTS `thanh_toan`;
CREATE TABLE `thanh_toan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_don_hang` INT NOT NULL,
    `phuong_thuc` VARCHAR(20) NOT NULL,
    `so_tien` DECIMAL(15,2) NOT NULL,
    `ma_giao_dich` VARCHAR(100) DEFAULT NULL,
    `trang_thai` ENUM('pending','success','failed') DEFAULT 'pending',
    `ngay_thanh_toan` DATETIME DEFAULT NULL,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_don_hang) REFERENCES don_hang(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: thong_bao (Thông báo người dùng)
-- ============================================================
DROP TABLE IF EXISTS `thong_bao`;
CREATE TABLE `thong_bao` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_nguoi_dung` INT NOT NULL,
    `tieu_de` VARCHAR(255) NOT NULL,
    `noi_dung` TEXT,
    `loai` VARCHAR(50) DEFAULT 'info',
    `lien_ket` VARCHAR(255) DEFAULT NULL,
    `da_doc` TINYINT(1) DEFAULT 0,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nguoi_dung (id_nguoi_dung),
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: bang_quang_cao (Banner quảng cáo)
-- ============================================================
DROP TABLE IF EXISTS `bang_quang_cao`;
CREATE TABLE `bang_quang_cao` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tieu_de` VARCHAR(255) DEFAULT NULL,
    `tieu_de_phu` VARCHAR(255) DEFAULT NULL,
    `hinh_anh` VARCHAR(255) NOT NULL,
    `lien_ket` VARCHAR(255) DEFAULT NULL,
    `vi_tri` VARCHAR(50) DEFAULT 'hero',
    `thu_tu` INT DEFAULT 0,
    `trang_thai` ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: yeu_thich (Sản phẩm yêu thích)
-- ============================================================
DROP TABLE IF EXISTS `yeu_thich`;
CREATE TABLE `yeu_thich` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_nguoi_dung` INT NOT NULL,
    `id_san_pham` INT NOT NULL,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_product (id_nguoi_dung, id_san_pham),
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_san_pham) REFERENCES san_pham(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BẢNG: lien_he (Liên hệ từ khách hàng)
-- ============================================================
DROP TABLE IF EXISTS `lien_he`;
CREATE TABLE `lien_he` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ten` VARCHAR(100) NOT NULL,
    `thu_dien_tu` VARCHAR(100) NOT NULL,
    `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
    `id_don_hang` VARCHAR(50) DEFAULT NULL,
    `chu_de` VARCHAR(255) DEFAULT NULL,
    `noi_dung` TEXT NOT NULL,
    `trang_thai` ENUM('new','read','replied') DEFAULT 'new',
    `noi_dung_tra_loi` TEXT DEFAULT NULL,
    `nguoi_tra_loi` VARCHAR(100) DEFAULT NULL,
    `ngay_tra_loi` DATETIME DEFAULT NULL,
    `ngay_tao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_trang_thai (trang_thai),
    INDEX idx_thu_dien_tu (thu_dien_tu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DỮ LIỆU MẪU
-- ============================================================

-- Quản trị viên mặc định (mật khẩu: Admin@123)
INSERT INTO `quan_tri_vien` (`ho_ten`, `thu_dien_tu`, `mat_khau`, `so_dien_thoai`, `vai_tro`) VALUES
('Super Admin', 'admin@vqstore.vn', '$2y$10$pUcwz3PSGZuTpYDz1GgKgemFdHbjf0RyN/wfSWNqRBJP9DCpmJtVe', '0901234567', 'super_admin');

-- Nhân viên mẫu (mật khẩu: Employee@123)
INSERT INTO `nhan_vien` (`ho_ten`, `thu_dien_tu`, `mat_khau`, `so_dien_thoai`, `vai_tro`) VALUES
('Nguyễn Văn Sales', 'sales@vqstore.vn', '$2y$10$DJtJqAa9zXKAEWFgQfX3Y.lHsJ.zWlMxFM4tVCpvUEKxNzGhxbe1G', '0912345678', 'Nhân viên bán hàng'),
('Trần Thị Kho', 'kho@vqstore.vn', '$2y$10$DJtJqAa9zXKAEWFgQfX3Y.lHsJ.zWlMxFM4tVCpvUEKxNzGhxbe1G', '0923456789', 'Kho hàng');

-- Danh mục
INSERT INTO `danh_muc` (`ten`, `duong_dan`, `mo_ta`, `bieu_tuong`, `thu_tu`) VALUES
('Laptop Gaming', 'laptop-gaming', 'Laptop dành cho game thủ với cấu hình mạnh mẽ', 'gamepad', 1),
('Laptop Văn Phòng', 'laptop-van-phong', 'Laptop mỏng nhẹ, pin trâu cho công việc', 'briefcase', 2),
('MacBook', 'macbook', 'Apple MacBook chính hãng', 'apple', 3),
('Bàn Phím Cơ', 'ban-phim-co', 'Bàn phím cơ gaming và làm việc', 'keyboard', 4),
('Chuột Gaming', 'chuot-gaming', 'Chuột chuyên dụng cho game thủ', 'mouse', 5),
('Tai Nghe', 'tai-nghe', 'Tai nghe gaming và âm thanh chuyên nghiệp', 'headphones', 6);

-- Khách hàng mẫu (mật khẩu: User@123)
INSERT INTO `nguoi_dung` (`ho_ten`, `thu_dien_tu`, `mat_khau`, `so_dien_thoai`, `hang`, `tong_chi_tieu`, `tong_don_hang`) VALUES
('Nguyễn Văn An', 'an.nguyen@gmail.com', '$2y$10$qZX5p.7Y6F5w8VTNa1L1Y.q.dKvBjmTRSt4ZxX7fS8K3yQy8L1L1q', '0987654321', 'gold', 25000000, 5),
('Trần Thị Bình', 'binh.tran@gmail.com', '$2y$10$qZX5p.7Y6F5w8VTNa1L1Y.q.dKvBjmTRSt4ZxX7fS8K3yQy8L1L1q', '0976543210', 'silver', 8500000, 2),
('Lê Hoàng Cường', 'cuong.le@gmail.com', '$2y$10$qZX5p.7Y6F5w8VTNa1L1Y.q.dKvBjmTRSt4ZxX7fS8K3yQy8L1L1q', '0965432109', 'diamond', 75000000, 12);

-- Sản phẩm - Laptop Gaming
INSERT INTO `san_pham` (`id_danh_muc`, `ten`, `duong_dan`, `ma_san_pham`, `thuong_hieu`, `mo_ta`, `thong_so`, `gia`, `gia_khuyen_mai`, `ton_kho`, `hinh_thu_nho`, `la_noi_bat`, `la_flash_sale`, `la_moi`, `ket_thuc_flash_sale`, `so_luong_ban`, `diem_danh_gia`, `so_danh_gia`) VALUES
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

-- Hình ảnh sản phẩm mẫu
INSERT INTO `anh_san_pham` (`id_san_pham`, `duong_dan_anh`, `thu_tu`) VALUES
(1, 'assets/images/laptop_asus_gaming_rog_strix_g15.1.webp', 1),
(1, 'assets/images/laptop_asus_gaming_rog_strix_g15.2.webp', 2),
(1, 'assets/images/laptop_asus_gaming_rog_strix_g15.3.webp', 3),
(2, 'assets/images/laptop-msi-katana-gf66-i7.webp', 1),
(2, 'assets/images/laptop-msi-katana-gf66-i7.1.webp', 2),
(8, 'assets/images/macbook-air-m2.webp', 1),
(8, 'assets/images/macbook-air-m2.1.webp', 2),
(8, 'assets/images/macbook-air-m2.2.webp', 3),
(3, 'assets/images/acer_nitro_5_tiger_1.webp', 1),
(3, 'assets/images/acer_nitro_5_tiger_2.webp', 2),
(3, 'assets/images/acer_nitro_5_tiger_3.webp', 3),
(4, 'assets/images/lenovo-legion-pro7.webp', 1),
(4, 'assets/images/lenovo-legion-pro7.1.webp', 2),
(4, 'assets/images/lenovo-legion-pro7.2.webp', 3),
(5, 'assets/images/dell-vostro.webp', 1),
(5, 'assets/images/dell-vostro.1.webp', 2),
(5, 'assets/images/dell-vostro.2.webp', 3),
(6, 'assets/images/hp-pavilion.webp', 1),
(6, 'assets/images/hp-pavilion.1.webp', 2),
(6, 'assets/images/hp-pavilion.2.webp', 3),
(7, 'assets/images/asus-vivobook.webp', 1),
(7, 'assets/images/asus-vivobook.1.webp', 2),
(7, 'assets/images/asus-vivobook.2.webp', 3),
(9, 'assets/images/macbook-pro-2023-m3.webp', 1),
(9, 'assets/images/macbook-pro-2023-m3.1.webp', 2),
(9, 'assets/images/macbook-pro-2023-m3.2.webp', 3),
(10, 'assets/images/ban-phim-keychron.webp', 1),
(10, 'assets/images/ban-phim-keychron.1.webp', 2),
(10, 'assets/images/ban-phim-keychron.2.webp', 3),
(11, 'assets/images/ban-phim-co-akko.webp', 1),
(11, 'assets/images/ban-phim-co-akko.1.webp', 2),
(11, 'assets/images/ban-phim-co-akko.2.webp', 3),
(12, 'assets/images/chuot-khong-day-bluetooth-logitech.webp', 1),
(12, 'assets/images/chuot-khong-day-bluetooth-logitech.1.webp', 2),
(12, 'assets/images/chuot-khong-day-bluetooth-logitech.2.webp', 3),
(13, 'assets/images/chuot-gaming-asus-tuf.webp', 1),
(13, 'assets/images/chuot-gaming-asus-tuf.1.webp', 2),
(13, 'assets/images/chuot-gaming-asus-tuf.2.webp', 3),
(14, 'assets/images/tai-nghe-bluetooth-arctis-nova.webp', 1),
(14, 'assets/images/tai-nghe-bluetooth-arctis-nova.1.webp', 2),
(14, 'assets/images/tai-nghe-bluetooth-arctis-nova.2.webp', 3),
(15, 'assets/images/lenovo-IdeaPad-slim5.webp', 1),
(15, 'assets/images/lenovo-IdeaPad-slim5.1.webp', 2),
(15, 'assets/images/lenovo-IdeaPad-slim5.2.webp', 3),
(16, 'assets/images/macbook-air-m4.webp', 1),
(16, 'assets/images/macbook-air-m4.1.webp', 2),
(16, 'assets/images/macbook-air-m4.2.webp', 3),
(17, 'assets/images/macbook-pro-m1.webp', 1),
(17, 'assets/images/macbook-pro-m1.1.webp', 2),
(17, 'assets/images/macbook-pro-m1.2.webp', 3),
(18, 'assets/images/ban-phim-ducky-one.webp', 1),
(18, 'assets/images/ban-phim-ducky-one.1.webp', 2),
(18, 'assets/images/ban-phim-ducky-one.2.webp', 3),
(19, 'assets/images/ban-phim-RK-ROYAL.webp', 1),
(19, 'assets/images/ban-phim-RK-ROYAL.2.webp', 2),
(19, 'assets/images/ban-phim-RK-ROYAL.3.webp', 3),
(20, 'assets/images/chuot-apple.webp', 1),
(20, 'assets/images/chuot-apple.1.webp', 2),
(20, 'assets/images/chuot-apple.2.webp', 3),
(21, 'assets/images/chuot-co-day-aula.webp', 1),
(21, 'assets/images/chuot-co-day-aula.1.webp', 2),
(21, 'assets/images/chuot-co-day-aula.2.webp', 3),
(22, 'assets/images/tai-nghe-JBL.webp', 1),
(22, 'assets/images/tai-nghe-JBL.1.webp', 2),
(22, 'assets/images/tai-nghe-JBL.2.webp', 3),
(23, 'assets/images/tai-nghe-chup-tai-bose.webp', 1),
(23, 'assets/images/tai-nghe-chup-tai-bose.1.webp', 2),
(23, 'assets/images/tai-nghe-chup-tai-bose.2.webp', 3),
(24, 'assets/images/tai-nghe-chup-tai-sony.webp', 1),
(24, 'assets/images/tai-nghe-chup-tai-sony.1.webp', 2),
(24, 'assets/images/tai-nghe-chup-tai-sony.2.webp', 3);

-- Phiếu giảm giá
INSERT INTO `phieu_giam_gia` (`ma_phieu`, `loai`, `gia_tri`, `don_hang_toi_thieu`, `giam_toi_da`, `so_luong`, `ngay_het_han`, `dang_hoat_dong`) VALUES
('TECH10', 'percent', 10, 500000, 500000, 100, '2026-06-30 23:59:59', 1),
('GAME20', 'percent', 20, 1000000, 2000000, 50, '2026-06-30 23:59:59', 1),
('WELCOME500', 'fixed', 500000, 5000000, NULL, 200, DATE_ADD(NOW(), INTERVAL 60 DAY), 1),
('FLASH50', 'percent', 50, 2000000, 1000000, 10, DATE_ADD(NOW(), INTERVAL 30 DAY), 1);

-- Banner quảng cáo mẫu
INSERT INTO `bang_quang_cao` (`tieu_de`, `tieu_de_phu`, `hinh_anh`, `lien_ket`, `vi_tri`, `thu_tu`) VALUES
('GAMING LAPTOP', 'Giảm đến 30% - Hiệu năng đỉnh cao', 'assets/images/banner-laptop-gm.jpg', '/category/laptop-gaming', 'hero', 1),
('MACBOOK 2024', 'Sức mạnh chip M3 mới nhất', 'assets/images/banner-macbook.jpg', '/category/macbook', 'hero', 2),
('GAMING GEAR', 'Phụ kiện gaming chính hãng', 'assets/images/banner-phukien.jpg', '/category/chuot-gaming', 'hero', 3),
('LAPTOP VĂN PHÒNG', 'Mỏng nhẹ, pin trâu - Làm việc mọi lúc mọi nơi', 'assets/images/banner-laptop-vp.jpg', '/category/laptop-van-phong', 'hero', 4),
('SIÊU SALE', 'Laptop & phụ kiện giảm đến 50% - Số lượng có hạn!', 'assets/images/banner-sale.jpg', '/promotions', 'promo', 5);

-- Đánh giá mẫu
INSERT INTO `danh_gia` (`id_san_pham`, `id_nguoi_dung`, `diem_so`, `noi_dung`) VALUES
(1, 1, 5, 'Laptop chạy game rất mượt, build chắc chắn, đáng đồng tiền!'),
(1, 2, 4, 'Tản nhiệt tốt, màn hình đẹp. Hơi nặng nhưng chấp nhận được với laptop gaming.'),
(8, 3, 5, 'MacBook Air M2 quá xịn! Pin trâu, thiết kế đẹp, hiệu năng mượt mà.'),
(10, 1, 5, 'Bàn phím gõ rất sướng, switch Brown êm tay, đèn RGB đẹp.');

-- Bình luận mẫu
INSERT INTO `binh_luan` (`id_san_pham`, `id_nguoi_dung`, `noi_dung`) VALUES
(1, 1, 'Sản phẩm có hỗ trợ trả góp 0% không shop?'),
(1, 2, 'Mình mua được 2 tuần rồi, dùng rất ổn nhé bạn!'),
(8, 3, 'Cho mình hỏi MacBook này có bản 16GB không ạ?');

-- Thông báo mẫu
INSERT INTO `thong_bao` (`id_nguoi_dung`, `tieu_de`, `noi_dung`, `loai`) VALUES
(1, 'Chào mừng đến với VQSTORE!', 'Cảm ơn bạn đã đăng ký tài khoản. Hãy khám phá ngay các sản phẩm hot!', 'info'),
(1, 'Voucher TECH10 đã sẵn sàng', 'Sử dụng mã TECH10 để giảm 10% cho đơn hàng đầu tiên', 'voucher');

-- ============================================================
-- HOÀN TẤT
-- ============================================================
