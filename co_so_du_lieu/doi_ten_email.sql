-- Migration: Đổi tên cột email → thu_dien_tu
USE laptop_store;

-- nguoi_dung
ALTER TABLE `nguoi_dung`
    CHANGE `email` `thu_dien_tu` VARCHAR(100) NOT NULL,
    DROP INDEX idx_email,
    ADD INDEX idx_thu_dien_tu (thu_dien_tu);

-- quan_tri_vien
ALTER TABLE `quan_tri_vien`
    CHANGE `email` `thu_dien_tu` VARCHAR(100) NOT NULL;

-- nhan_vien
ALTER TABLE `nhan_vien`
    CHANGE `email` `thu_dien_tu` VARCHAR(100) NOT NULL;

-- don_hang
ALTER TABLE `don_hang`
    CHANGE `email_giao_hang` `thu_dien_tu_giao_hang` VARCHAR(100) DEFAULT NULL;

-- lien_he
ALTER TABLE `lien_he`
    CHANGE `email` `thu_dien_tu` VARCHAR(100) NOT NULL,
    DROP INDEX idx_email,
    ADD INDEX idx_thu_dien_tu (thu_dien_tu);
