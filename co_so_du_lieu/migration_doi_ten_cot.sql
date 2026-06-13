-- ============================================================
-- MIGRATION: Đổi tên tất cả cột từ tiếng Anh → tiếng Việt
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
USE laptop_store;

-- ============================================================
-- BẢNG: nguoi_dung
-- ============================================================
ALTER TABLE `nguoi_dung`
    CHANGE `full_name`    `ho_ten`        VARCHAR(100) NOT NULL,
    CHANGE `password`     `mat_khau`      VARCHAR(255) DEFAULT NULL,
    CHANGE `phone`        `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
    CHANGE `avatar`       `anh_dai_dien`  VARCHAR(255) DEFAULT NULL,
    CHANGE `birthday`     `ngay_sinh`     DATE DEFAULT NULL,
    CHANGE `gender`       `gioi_tinh`     ENUM('male','female','other') DEFAULT NULL,
    CHANGE `address`      `dia_chi`       TEXT DEFAULT NULL,
    CHANGE `rank`         `hang`          ENUM('silver','gold','diamond') DEFAULT 'silver',
    CHANGE `total_spent`  `tong_chi_tieu` DECIMAL(15,2) DEFAULT 0,
    CHANGE `total_orders` `tong_don_hang` INT DEFAULT 0,
    CHANGE `google_id`    `id_google`     VARCHAR(100) DEFAULT NULL,
    CHANGE `reset_token`  `ma_dat_lai`    VARCHAR(100) DEFAULT NULL,
    CHANGE `reset_expires` `han_dat_lai`  DATETIME DEFAULT NULL,
    CHANGE `status`       `trang_thai`    ENUM('active','blocked') DEFAULT 'active',
    CHANGE `created_at`   `ngay_tao`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHANGE `updated_at`   `ngay_cap_nhat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: quan_tri_vien
-- ============================================================
ALTER TABLE `quan_tri_vien`
    CHANGE `full_name`  `ho_ten`        VARCHAR(100) NOT NULL,
    CHANGE `password`   `mat_khau`      VARCHAR(255) NOT NULL,
    CHANGE `phone`      `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
    CHANGE `role`       `vai_tro`       VARCHAR(50) DEFAULT 'super_admin',
    CHANGE `status`     `trang_thai`    ENUM('active','blocked') DEFAULT 'active',
    CHANGE `created_at` `ngay_tao`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: nhan_vien
-- ============================================================
ALTER TABLE `nhan_vien`
    CHANGE `full_name`  `ho_ten`        VARCHAR(100) NOT NULL,
    CHANGE `password`   `mat_khau`      VARCHAR(255) NOT NULL,
    CHANGE `phone`      `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
    CHANGE `role`       `vai_tro`       VARCHAR(50) DEFAULT 'Nhân viên bán hàng',
    CHANGE `status`     `trang_thai`    ENUM('active','blocked') DEFAULT 'active',
    CHANGE `created_at` `ngay_tao`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: danh_muc
-- ============================================================
ALTER TABLE `danh_muc`
    CHANGE `name`       `ten`        VARCHAR(100) NOT NULL,
    CHANGE `slug`       `duong_dan`  VARCHAR(150) NOT NULL,
    CHANGE `description` `mo_ta`    TEXT DEFAULT NULL,
    CHANGE `icon`       `bieu_tuong` VARCHAR(50) DEFAULT 'folder',
    CHANGE `image`      `hinh_anh`   VARCHAR(255) DEFAULT NULL,
    CHANGE `parent_id`  `id_cha`     INT DEFAULT NULL,
    CHANGE `sort_order` `thu_tu`     INT DEFAULT 0,
    CHANGE `status`     `trang_thai` ENUM('active','inactive') DEFAULT 'active',
    CHANGE `created_at` `ngay_tao`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: san_pham
-- ============================================================
ALTER TABLE `san_pham`
    CHANGE `category_id`   `id_danh_muc`       INT NOT NULL,
    CHANGE `name`          `ten`               VARCHAR(255) NOT NULL,
    CHANGE `slug`          `duong_dan`         VARCHAR(255) NOT NULL,
    CHANGE `sku`           `ma_san_pham`       VARCHAR(100) DEFAULT NULL,
    CHANGE `brand`         `thuong_hieu`       VARCHAR(100) DEFAULT NULL,
    CHANGE `description`   `mo_ta`             TEXT DEFAULT NULL,
    CHANGE `specs`         `thong_so`          LONGTEXT DEFAULT NULL,
    CHANGE `price`         `gia`               DECIMAL(15,2) NOT NULL,
    CHANGE `sale_price`    `gia_khuyen_mai`    DECIMAL(15,2) DEFAULT NULL,
    CHANGE `stock`         `ton_kho`           INT DEFAULT 0,
    CHANGE `thumbnail`     `hinh_thu_nho`      VARCHAR(255) DEFAULT NULL,
    CHANGE `views`         `luot_xem`          INT DEFAULT 0,
    CHANGE `sold_quantity` `so_luong_ban`      INT DEFAULT 0,
    CHANGE `rating_avg`    `diem_danh_gia`     DECIMAL(3,2) DEFAULT 0,
    CHANGE `rating_count`  `so_danh_gia`       INT DEFAULT 0,
    CHANGE `is_featured`   `la_noi_bat`        TINYINT(1) DEFAULT 0,
    CHANGE `is_flash_sale` `la_flash_sale`     TINYINT(1) DEFAULT 0,
    CHANGE `is_new`        `la_moi`            TINYINT(1) DEFAULT 0,
    CHANGE `flash_sale_end` `ket_thuc_flash_sale` DATETIME DEFAULT NULL,
    CHANGE `status`        `trang_thai`        ENUM('active','inactive') DEFAULT 'active',
    CHANGE `created_at`    `ngay_tao`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHANGE `updated_at`    `ngay_cap_nhat`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: anh_san_pham
-- ============================================================
ALTER TABLE `anh_san_pham`
    CHANGE `product_id`  `id_san_pham`   INT NOT NULL,
    CHANGE `image_path`  `duong_dan_anh` VARCHAR(255) NOT NULL,
    CHANGE `sort_order`  `thu_tu`        INT DEFAULT 0,
    CHANGE `created_at`  `ngay_tao`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: phieu_giam_gia
-- ============================================================
ALTER TABLE `phieu_giam_gia`
    CHANGE `code`        `ma_phieu`          VARCHAR(50) NOT NULL,
    CHANGE `type`        `loai`              ENUM('percent','fixed') DEFAULT 'percent',
    CHANGE `value`       `gia_tri`           DECIMAL(15,2) NOT NULL,
    CHANGE `min_order`   `don_hang_toi_thieu` DECIMAL(15,2) DEFAULT 0,
    CHANGE `max_discount` `giam_toi_da`      DECIMAL(15,2) DEFAULT NULL,
    CHANGE `quantity`    `so_luong`          INT DEFAULT 0,
    CHANGE `used_count`  `so_lan_dung`       INT DEFAULT 0,
    CHANGE `expires_at`  `ngay_het_han`      DATETIME DEFAULT NULL,
    CHANGE `is_active`   `dang_hoat_dong`    TINYINT(1) DEFAULT 1,
    CHANGE `created_at`  `ngay_tao`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: don_hang
-- ============================================================
ALTER TABLE `don_hang`
    CHANGE `order_code`       `ma_don_hang`           VARCHAR(50) NOT NULL,
    CHANGE `user_id`          `id_nguoi_dung`         INT DEFAULT NULL,
    CHANGE `shipping_name`    `ten_giao_hang`         VARCHAR(100) NOT NULL,
    CHANGE `shipping_phone`   `sdt_giao_hang`         VARCHAR(20) NOT NULL,
    CHANGE `shipping_email`   `thu_dien_tu_giao_hang` VARCHAR(100) DEFAULT NULL,
    CHANGE `shipping_address` `dia_chi_giao_hang`     TEXT NOT NULL,
    CHANGE `note`             `ghi_chu`               TEXT DEFAULT NULL,
    CHANGE `subtotal`         `tam_tinh`              DECIMAL(15,2) NOT NULL,
    CHANGE `discount_amount`  `so_tien_giam`          DECIMAL(15,2) DEFAULT 0,
    CHANGE `total_amount`     `tong_tien`             DECIMAL(15,2) NOT NULL,
    CHANGE `voucher_id`       `id_phieu_giam`         INT DEFAULT NULL,
    CHANGE `payment_method`   `phuong_thuc_thanh_toan` ENUM('cod','bank','momo','vnpay') DEFAULT 'cod',
    CHANGE `payment_status`   `trang_thai_thanh_toan` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    CHANGE `status`           `trang_thai`            ENUM('pending','confirmed','shipping','delivered','completed','cancelled') DEFAULT 'pending',
    CHANGE `cancel_reason`    `ly_do_huy`             TEXT DEFAULT NULL,
    CHANGE `created_at`       `ngay_tao`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHANGE `updated_at`       `ngay_cap_nhat`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: chi_tiet_don
-- ============================================================
ALTER TABLE `chi_tiet_don`
    CHANGE `order_id`     `id_don_hang`  INT NOT NULL,
    CHANGE `product_id`   `id_san_pham`  INT NOT NULL,
    CHANGE `product_name` `ten_san_pham` VARCHAR(255) NOT NULL,
    CHANGE `thumbnail`    `hinh_thu_nho` VARCHAR(255) DEFAULT NULL,
    CHANGE `price`        `gia`          DECIMAL(15,2) NOT NULL,
    CHANGE `quantity`     `so_luong`     INT NOT NULL,
    CHANGE `subtotal`     `tam_tinh`     DECIMAL(15,2) NOT NULL;

-- ============================================================
-- BẢNG: gio_hang
-- ============================================================
ALTER TABLE `gio_hang`
    CHANGE `user_id`    `id_nguoi_dung` INT NOT NULL,
    CHANGE `product_id` `id_san_pham`   INT NOT NULL,
    CHANGE `quantity`   `so_luong`      INT DEFAULT 1,
    CHANGE `created_at` `ngay_tao`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: danh_gia
-- ============================================================
ALTER TABLE `danh_gia`
    CHANGE `product_id` `id_san_pham`  INT NOT NULL,
    CHANGE `user_id`    `id_nguoi_dung` INT NOT NULL,
    CHANGE `rating`     `diem_so`      TINYINT NOT NULL,
    CHANGE `content`    `noi_dung`     TEXT DEFAULT NULL,
    CHANGE `created_at` `ngay_tao`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: binh_luan
-- ============================================================
ALTER TABLE `binh_luan`
    CHANGE `product_id` `id_san_pham`  INT NOT NULL,
    CHANGE `user_id`    `id_nguoi_dung` INT NOT NULL,
    CHANGE `parent_id`  `id_cha`       INT DEFAULT NULL,
    CHANGE `content`    `noi_dung`     TEXT NOT NULL,
    CHANGE `created_at` `ngay_tao`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: thong_bao
-- ============================================================
ALTER TABLE `thong_bao`
    CHANGE `user_id`    `id_nguoi_dung` INT NOT NULL,
    CHANGE `title`      `tieu_de`      VARCHAR(255) NOT NULL,
    CHANGE `content`    `noi_dung`     TEXT DEFAULT NULL,
    CHANGE `type`       `loai`         VARCHAR(50) DEFAULT 'info',
    CHANGE `link`       `lien_ket`     VARCHAR(255) DEFAULT NULL,
    CHANGE `is_read`    `da_doc`       TINYINT(1) DEFAULT 0,
    CHANGE `created_at` `ngay_tao`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: yeu_thich
-- ============================================================
ALTER TABLE `yeu_thich`
    CHANGE `user_id`    `id_nguoi_dung` INT NOT NULL,
    CHANGE `product_id` `id_san_pham`   INT NOT NULL,
    CHANGE `created_at` `ngay_tao`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: bang_quang_cao
-- ============================================================
ALTER TABLE `bang_quang_cao`
    CHANGE `title`      `tieu_de`     VARCHAR(255) DEFAULT NULL,
    CHANGE `subtitle`   `tieu_de_phu` VARCHAR(255) DEFAULT NULL,
    CHANGE `image`      `hinh_anh`    VARCHAR(255) NOT NULL,
    CHANGE `link`       `lien_ket`    VARCHAR(255) DEFAULT NULL,
    CHANGE `position`   `vi_tri`      VARCHAR(50) DEFAULT 'hero',
    CHANGE `sort_order` `thu_tu`      INT DEFAULT 0,
    CHANGE `status`     `trang_thai`  ENUM('active','inactive') DEFAULT 'active';

-- ============================================================
-- BẢNG: thanh_toan
-- ============================================================
ALTER TABLE `thanh_toan`
    CHANGE `order_id`       `id_don_hang`    INT NOT NULL,
    CHANGE `method`         `phuong_thuc`    VARCHAR(20) NOT NULL,
    CHANGE `amount`         `so_tien`        DECIMAL(15,2) NOT NULL,
    CHANGE `transaction_id` `ma_giao_dich`   VARCHAR(100) DEFAULT NULL,
    CHANGE `status`         `trang_thai`     ENUM('pending','success','failed') DEFAULT 'pending',
    CHANGE `paid_at`        `ngay_thanh_toan` DATETIME DEFAULT NULL,
    CHANGE `created_at`     `ngay_tao`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- BẢNG: lien_he
-- ============================================================
ALTER TABLE `lien_he`
    CHANGE `name`          `ten`              VARCHAR(100) NOT NULL,
    CHANGE `phone`         `so_dien_thoai`    VARCHAR(20) DEFAULT NULL,
    CHANGE `order_id`      `id_don_hang`      VARCHAR(50) DEFAULT NULL,
    CHANGE `subject`       `chu_de`           VARCHAR(255) DEFAULT NULL,
    CHANGE `message`       `noi_dung`         TEXT NOT NULL,
    CHANGE `status`        `trang_thai`       ENUM('new','read','replied') DEFAULT 'new',
    CHANGE `reply_content` `noi_dung_tra_loi` TEXT DEFAULT NULL,
    CHANGE `replied_by`    `nguoi_tra_loi`    VARCHAR(100) DEFAULT NULL,
    CHANGE `replied_at`    `ngay_tra_loi`     DATETIME DEFAULT NULL,
    CHANGE `created_at`    `ngay_tao`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

SET FOREIGN_KEY_CHECKS = 1;
