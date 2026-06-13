<?php
/**
 * Model Sản Phẩm — bảng: san_pham
 * Xử lý toàn bộ thao tác CSDL: lấy danh sách, tìm kiếm, đánh giá, bình luận, quản lý tồn kho
 */

class SanPham {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy danh sách sản phẩm với bộ lọc tùy chọn (danh mục, giá, thương hiệu, từ khóa) */
    public function getAll($filters = [], $limit = 12, $offset = 0) {
        $sql = "SELECT p.*, c.ten AS ten_danh_muc, c.duong_dan AS duong_dan_danh_muc
                FROM san_pham p
                LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
                WHERE p.trang_thai = 'active'";
        $params = [];

        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['category_ids'])));
            if (!empty($ids)) {
                $sql .= " AND p.id_danh_muc IN (" . implode(',', $ids) . ")";
            }
        } elseif (!empty($filters['id_danh_muc'])) {
            $sql .= " AND p.id_danh_muc = ?";
            $params[] = $filters['id_danh_muc'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND COALESCE(p.gia_khuyen_mai, p.gia) >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND COALESCE(p.gia_khuyen_mai, p.gia) <= ?";
            $params[] = $filters['max_price'];
        }

        if (!empty($filters['thuong_hieu'])) {
            $sql .= " AND p.thuong_hieu = ?";
            $params[] = $filters['thuong_hieu'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.ten LIKE ? OR p.mo_ta LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY COALESCE(p.gia_khuyen_mai, p.gia) ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY COALESCE(p.gia_khuyen_mai, p.gia) DESC";
                break;
            case 'best_seller':
                $sql .= " ORDER BY p.so_luong_ban DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY p.diem_danh_gia DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY p.ngay_tao DESC";
        }

        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->fetchAll($sql, $params);
    }

    /** Đếm tổng số sản phẩm theo bộ lọc — dùng cho phân trang */
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) AS tong FROM san_pham p WHERE p.trang_thai = 'active'";
        $params = [];

        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['category_ids'])));
            if (!empty($ids)) {
                $sql .= " AND p.id_danh_muc IN (" . implode(',', $ids) . ")";
            }
        } elseif (!empty($filters['id_danh_muc'])) {
            $sql .= " AND p.id_danh_muc = ?";
            $params[] = $filters['id_danh_muc'];
        }
        if (!empty($filters['min_price'])) {
            $sql .= " AND COALESCE(p.gia_khuyen_mai, p.gia) >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND COALESCE(p.gia_khuyen_mai, p.gia) <= ?";
            $params[] = $filters['max_price'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (p.ten LIKE ? OR p.mo_ta LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $row = $this->db->fetch($sql, $params);
        return $row['tong'] ?? 0;
    }

    /** Lấy sản phẩm theo ID */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT p.*, c.ten AS ten_danh_muc, c.duong_dan AS duong_dan_danh_muc
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
             WHERE p.id = ?",
            [$id]
        );
    }

    /** Lấy sản phẩm theo đường dẫn thân thiện (slug) */
    public function getBySlug($duongDan) {
        return $this->db->fetch(
            "SELECT p.*, c.ten AS ten_danh_muc, c.duong_dan AS duong_dan_danh_muc
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
             WHERE p.duong_dan = ? AND p.trang_thai = 'active'",
            [$duongDan]
        );
    }

    /** Lấy sản phẩm theo danh mục */
    public function getByCategory($categoryId, $limit = 12, $offset = 0, $sort = 'newest') {
        return $this->getAll(['id_danh_muc' => $categoryId, 'sort' => $sort], $limit, $offset);
    }

    /** Đếm số sản phẩm trong một danh mục */
    public function countByCategory($categoryId) {
        return $this->countAll(['id_danh_muc' => $categoryId]);
    }

    /** Tìm kiếm sản phẩm theo từ khóa */
    public function search($keyword, $limit = 20) {
        return $this->db->fetchAll(
            "SELECT * FROM san_pham
             WHERE trang_thai = 'active'
             AND (ten LIKE ? OR mo_ta LIKE ? OR thuong_hieu LIKE ?)
             ORDER BY so_luong_ban DESC
             LIMIT " . (int)$limit,
            ['%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%']
        );
    }

    /** Đếm số kết quả tìm kiếm */
    public function countSearch($keyword) {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS tong FROM san_pham
             WHERE trang_thai = 'active'
             AND (ten LIKE ? OR mo_ta LIKE ? OR thuong_hieu LIKE ?)",
            ['%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%']
        );
        return $row['tong'] ?? 0;
    }

    /** Lấy sản phẩm nổi bật (la_noi_bat = 1) */
    public function getFeatured($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.ten AS ten_danh_muc
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
             WHERE p.trang_thai = 'active' AND p.la_noi_bat = 1
             ORDER BY p.ngay_tao DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm đang flash sale còn hiệu lực */
    public function getFlashSale($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.ten AS ten_danh_muc
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
             WHERE p.trang_thai = 'active'
             AND p.la_flash_sale = 1
             AND p.gia_khuyen_mai IS NOT NULL
             AND (p.ket_thuc_flash_sale IS NULL OR p.ket_thuc_flash_sale > NOW())
             ORDER BY p.ngay_tao DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm mới (la_moi = 1) */
    public function getNewArrivals($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.ten AS ten_danh_muc
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
             WHERE p.trang_thai = 'active' AND p.la_moi = 1
             ORDER BY p.ngay_tao DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm bán chạy nhất */
    public function getBestSellers($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.ten AS ten_danh_muc
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
             WHERE p.trang_thai = 'active'
             ORDER BY p.so_luong_ban DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy tất cả ảnh gallery của sản phẩm từ bảng anh_san_pham */
    public function getImages($productId) {
        return $this->db->fetchAll(
            "SELECT * FROM anh_san_pham WHERE id_san_pham = ? ORDER BY thu_tu ASC",
            [$productId]
        );
    }

    /** Thêm ảnh vào gallery sản phẩm */
    public function addImage($productId, $imagePath, $sortOrder = 0) {
        return $this->db->insert('anh_san_pham', [
            'id_san_pham'  => $productId,
            'duong_dan_anh' => $imagePath,
            'thu_tu'       => $sortOrder,
        ]);
    }

    /** Xóa toàn bộ ảnh gallery của sản phẩm */
    public function deleteImages($productId) {
        return $this->db->execute("DELETE FROM anh_san_pham WHERE id_san_pham = ?", [$productId]);
    }

    /** Lấy các biến thể màu sắc — chỉ hiển thị nếu sản phẩm thuộc nhóm bien_the */
    public function getColorVariants($productId, $categoryId, $brand, $limit = 4) {
        try {
            $nhom = $this->db->fetch(
                "SELECT nhom_bien_the FROM san_pham WHERE id = ?", [$productId]
            );
            if (empty($nhom['nhom_bien_the'])) return [];
            return $this->db->fetchAll(
                "SELECT id, ten, duong_dan, hinh_thu_nho, gia, gia_khuyen_mai
                 FROM san_pham
                 WHERE trang_thai = 'active' AND nhom_bien_the = ? AND id != ?
                 ORDER BY id ASC
                 LIMIT " . (int)$limit,
                [$nhom['nhom_bien_the'], $productId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Lấy sản phẩm liên quan (cùng danh mục) */
    public function getRelated($productId, $categoryId, $limit = 4) {
        return $this->db->fetchAll(
            "SELECT p.*, c.ten AS ten_danh_muc
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
             WHERE p.trang_thai = 'active' AND p.id_danh_muc = ? AND p.id != ?
             ORDER BY p.so_luong_ban DESC
             LIMIT " . (int)$limit,
            [$categoryId, $productId]
        );
    }

    /** Tăng bộ đếm lượt xem sản phẩm */
    public function incrementView($id) {
        return $this->db->execute("UPDATE san_pham SET luot_xem = luot_xem + 1 WHERE id = ?", [$id]);
    }

    /** Lấy danh sách đánh giá của sản phẩm */
    public function getReviews($productId) {
        return $this->db->fetchAll(
            "SELECT r.*, u.ho_ten, u.anh_dai_dien
             FROM danh_gia r
             LEFT JOIN nguoi_dung u ON r.id_nguoi_dung = u.id
             WHERE r.id_san_pham = ?
             ORDER BY r.ngay_tao DESC",
            [$productId]
        );
    }

    /** Lấy bình luận sản phẩm kèm phản hồi */
    public function getComments($productId) {
        $comments = $this->db->fetchAll(
            "SELECT c.*, u.ho_ten, u.anh_dai_dien
             FROM binh_luan c
             LEFT JOIN nguoi_dung u ON c.id_nguoi_dung = u.id
             WHERE c.id_san_pham = ? AND c.id_cha IS NULL
             ORDER BY c.ngay_tao DESC",
            [$productId]
        );

        foreach ($comments as &$comment) {
            $comment['replies'] = $this->db->fetchAll(
                "SELECT c.*, u.ho_ten, u.anh_dai_dien
                 FROM binh_luan c
                 LEFT JOIN nguoi_dung u ON c.id_nguoi_dung = u.id
                 WHERE c.id_cha = ?
                 ORDER BY c.ngay_tao ASC",
                [$comment['id']]
            );
        }
        return $comments;
    }

    /** Thêm đánh giá, tự động tính lại điểm trung bình */
    public function addReview($productId, $userId, $rating, $content) {
        $id = $this->db->insert('danh_gia', [
            'id_san_pham'   => $productId,
            'id_nguoi_dung' => $userId,
            'diem_so'       => $rating,
            'noi_dung'      => $content,
        ]);
        $this->recalcRating($productId);
        return $id;
    }

    /** Tính lại điểm đánh giá trung bình của sản phẩm */
    public function recalcRating($productId) {
        $row = $this->db->fetch(
            "SELECT AVG(diem_so) AS diem_tb, COUNT(*) AS tong
             FROM danh_gia WHERE id_san_pham = ?",
            [$productId]
        );
        return $this->db->execute(
            "UPDATE san_pham SET diem_danh_gia = ?, so_danh_gia = ? WHERE id = ?",
            [round($row['diem_tb'] ?? 0, 2), $row['tong'] ?? 0, $productId]
        );
    }

    /** Thêm bình luận mới (hoặc phản hồi) */
    public function addComment($productId, $userId, $content, $parentId = null) {
        return $this->db->insert('binh_luan', [
            'id_san_pham'   => $productId,
            'id_nguoi_dung' => $userId,
            'id_cha'        => $parentId,
            'noi_dung'      => $content,
        ]);
    }

    // ============================================================
    //  ADMIN: Quản lý sản phẩm (CRUD)
    // ============================================================

    /** Tạo sản phẩm mới */
    public function create($data) {
        return $this->db->insert('san_pham', $data);
    }

    /** Cập nhật thông tin sản phẩm */
    public function update($id, $data) {
        $set = [];
        $params = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE san_pham SET " . implode(', ', $set) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }

    /** Xóa sản phẩm */
    public function delete($id) {
        return $this->db->execute("DELETE FROM san_pham WHERE id = ?", [$id]);
    }

    /** Giảm tồn kho và tăng số lượng đã bán sau khi đặt hàng thành công */
    public function decreaseStock($productId, $quantity) {
        return $this->db->execute(
            "UPDATE san_pham SET ton_kho = ton_kho - ?, so_luong_ban = so_luong_ban + ? WHERE id = ?",
            [$quantity, $quantity, $productId]
        );
    }

    /** Lấy danh sách sản phẩm sắp hết hàng */
    public function getLowStock($limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM san_pham
             WHERE trang_thai = 'active' AND ton_kho <= " . (int)LOW_STOCK_WARNING . "
             ORDER BY ton_kho ASC
             LIMIT " . (int)$limit
        );
    }

    /** Admin: lấy tất cả sản phẩm, hỗ trợ tìm kiếm và lọc */
    public function adminGetAll($search = '', $categoryId = null, $stockFilter = '') {
        $sql = "SELECT p.*, c.ten AS ten_danh_muc
                FROM san_pham p
                LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (p.ten LIKE ? OR p.ma_san_pham LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($categoryId) {
            $sql .= " AND p.id_danh_muc = ?";
            $params[] = $categoryId;
        }
        if ($stockFilter === 'low') {
            $sql .= " AND p.ton_kho <= " . (int)LOW_STOCK_WARNING . " AND p.ton_kho > 0";
        } elseif ($stockFilter === 'out') {
            $sql .= " AND p.ton_kho <= 0";
        }

        $sql .= " ORDER BY p.ngay_tao DESC";
        return $this->db->fetchAll($sql, $params);
    }
}
