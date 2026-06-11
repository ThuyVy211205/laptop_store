<?php
/**
 * Model Danh Mục — bảng: danh_muc
 * Xử lý danh mục sản phẩm và thương hiệu cho menu điều hướng
 */

class Category {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy tất cả danh mục gốc kèm số lượng sản phẩm */
    public function getAll() {
        $cats = $this->db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM san_pham WHERE category_id = c.id AND status = 'active') AS product_count
             FROM danh_muc c
             WHERE c.status = 'active' AND (c.parent_id IS NULL OR c.parent_id = 0)
             ORDER BY c.sort_order ASC, c.id ASC"
        );
        // Đảm bảo mỗi danh mục có slug hợp lệ — sinh từ tên nếu cột slug rỗng
        foreach ($cats as &$cat) {
            if (empty($cat['slug']) && !empty($cat['name'])) {
                $cat['slug'] = createSlug($cat['name']);
            }
        }
        unset($cat);
        return $cats;
    }

    /** Lấy thương hiệu nhóm theo danh mục — dùng cho menu thả xuống navbar */
    public function getBrandsPerCategory() {
        $rows = $this->db->fetchAll(
            "SELECT p.category_id, p.brand, COUNT(*) AS cnt
             FROM san_pham p
             JOIN danh_muc c ON c.id = p.category_id
             WHERE p.status = 'active' AND p.brand IS NOT NULL AND p.brand != ''
               AND c.status = 'active'
             GROUP BY p.category_id, p.brand
             ORDER BY p.category_id ASC, cnt DESC"
        );
        $map = [];
        foreach ($rows as $row) {
            $map[$row['category_id']][] = ['brand' => $row['brand'], 'count' => $row['cnt']];
        }
        return $map;
    }

    /** Lấy danh mục theo ID */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM danh_muc WHERE id = ?", [$id]);
    }

    /** Lấy danh mục theo slug, fallback sang sinh slug từ tên nếu cột rỗng */
    public function getBySlug($slug) {
        if (empty($slug)) return null;

        // Ưu tiên tìm khớp chính xác theo cột slug trong DB
        $cat = $this->db->fetch("SELECT * FROM danh_muc WHERE slug = ?", [$slug]);
        if ($cat) return $cat;

        // Fallback: sinh slug từ tên và so sánh nếu cột slug rỗng
        $all = $this->db->fetchAll("SELECT * FROM danh_muc WHERE status = 'active'");
        foreach ($all as $c) {
            if (!empty($c['name']) && createSlug($c['name']) === $slug) {
                return $c;
            }
        }
        return null;
    }

    /** Tạo danh mục mới, tự sinh slug từ tên nếu thiếu */
    public function create($data) {
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = createSlug($data['name']);
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