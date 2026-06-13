<?php
/**
 * Model Danh Mục — bảng: danh_muc
 * Xử lý danh mục sản phẩm và thương hiệu cho menu điều hướng
 */

class DanhMuc {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy tất cả danh mục gốc kèm số lượng sản phẩm */
    public function getAll() {
        $cats = $this->db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM san_pham WHERE id_danh_muc = c.id AND trang_thai = 'active') AS so_san_pham
             FROM danh_muc c
             WHERE c.trang_thai = 'active' AND (c.id_cha IS NULL OR c.id_cha = 0)
             ORDER BY c.thu_tu ASC, c.id ASC"
        );
        foreach ($cats as &$cat) {
            if (empty($cat['duong_dan']) && !empty($cat['ten'])) {
                $cat['duong_dan'] = createSlug($cat['ten']);
            }
        }
        unset($cat);
        return $cats;
    }

    /** Lấy thương hiệu nhóm theo danh mục — dùng cho menu thả xuống navbar */
    public function getBrandsPerCategory() {
        $rows = $this->db->fetchAll(
            "SELECT p.id_danh_muc, p.thuong_hieu, COUNT(*) AS so_luong
             FROM san_pham p
             JOIN danh_muc c ON c.id = p.id_danh_muc
             WHERE p.trang_thai = 'active' AND p.thuong_hieu IS NOT NULL AND p.thuong_hieu != ''
               AND c.trang_thai = 'active'
             GROUP BY p.id_danh_muc, p.thuong_hieu
             ORDER BY p.id_danh_muc ASC, so_luong DESC"
        );
        $map = [];
        foreach ($rows as $row) {
            $map[$row['id_danh_muc']][] = ['thuong_hieu' => $row['thuong_hieu'], 'count' => $row['so_luong']];
        }
        return $map;
    }

    /** Lấy danh mục theo ID */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM danh_muc WHERE id = ?", [$id]);
    }

    /** Lấy danh mục theo đường dẫn, fallback sinh từ tên nếu cột rỗng */
    public function getBySlug($duongDan) {
        if (empty($duongDan)) return null;

        $cat = $this->db->fetch("SELECT * FROM danh_muc WHERE duong_dan = ?", [$duongDan]);
        if ($cat) return $cat;

        $all = $this->db->fetchAll("SELECT * FROM danh_muc WHERE trang_thai = 'active'");
        foreach ($all as $c) {
            if (!empty($c['ten']) && createSlug($c['ten']) === $duongDan) {
                return $c;
            }
        }
        return null;
    }

    /** Tạo danh mục mới, tự sinh đường dẫn từ tên nếu thiếu */
    public function create($data) {
        if (empty($data['duong_dan']) && !empty($data['ten'])) {
            $data['duong_dan'] = createSlug($data['ten']);
        }
        return $this->db->insert('danh_muc', $data);
    }

    /** Cập nhật danh mục */
    public function update($id, $data) {
        $set = [];
        $params = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = ?";
            $params[] = $val;
        }
        $params[] = $id;
        return $this->db->execute("UPDATE danh_muc SET " . implode(', ', $set) . " WHERE id = ?", $params);
    }

    /** Xóa danh mục */
    public function delete($id) {
        return $this->db->execute("DELETE FROM danh_muc WHERE id = ?", [$id]);
    }
}
