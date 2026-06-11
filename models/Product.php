<?php
/**
 * Model Sản Phẩm — bảng: san_pham
 * Xử lý toàn bộ thao tác CSDL: lấy danh sách, tìm kiếm, đánh giá, bình luận, quản lý tồn kho
 */

class Product {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy danh sách sản phẩm với bộ lọc tùy chọn (danh mục, giá, thương hiệu, từ khóa) */
    public function getAll($filters = [], $limit = 12, $offset = 0) {
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM san_pham p
                LEFT JOIN danh_muc c ON p.category_id = c.id
                WHERE p.status = 'active'";
        $params = [];

        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['category_ids'])));
            if (!empty($ids)) {
                $sql .= " AND p.category_id IN (" . implode(',', $ids) . ")";
            }
        } elseif (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND COALESCE(p.sale_price, p.price) >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND COALESCE(p.sale_price, p.price) <= ?";
            $params[] = $filters['max_price'];
        }

        if (!empty($filters['brand'])) {
            $sql .= " AND p.brand = ?";
            $params[] = $filters['brand'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        // Xử lý sắp xếp kết quả sản phẩm
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY COALESCE(p.sale_price, p.price) ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY COALESCE(p.sale_price, p.price) DESC";
                break;
            case 'best_seller':
                $sql .= " ORDER BY p.sold_quantity DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY p.rating_avg DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY p.created_at DESC";
        }

        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->fetchAll($sql, $params);
    }

    /** Đếm tổng số sản phẩm theo bộ lọc — dùng cho phân trang */
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) AS total FROM san_pham p WHERE p.status = 'active'";
        $params = [];

        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['category_ids'])));
            if (!empty($ids)) {
                $sql .= " AND p.category_id IN (" . implode(',', $ids) . ")";
            }
        } elseif (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['min_price'])) {
            $sql .= " AND COALESCE(p.sale_price, p.price) >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND COALESCE(p.sale_price, p.price) <= ?";
            $params[] = $filters['max_price'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $row = $this->db->fetch($sql, $params);
        return $row['total'] ?? 0;
    }

    /** Lấy sản phẩm theo ID */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.category_id = c.id
             WHERE p.id = ?",
            [$id]
        );
    }

    /** Lấy sản phẩm theo đường dẫn thân thiện (slug) */
    public function getBySlug($slug) {
        return $this->db->fetch(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.category_id = c.id
             WHERE p.slug = ? AND p.status = 'active'",
            [$slug]
        );
    }

    /** Lấy sản phẩm theo danh mục */
    public function getByCategory($categoryId, $limit = 12, $offset = 0, $sort = 'newest') {
        return $this->getAll(['category_id' => $categoryId, 'sort' => $sort], $limit, $offset);
    }

    /** Đếm số sản phẩm trong một danh mục */
    public function countByCategory($categoryId) {
        return $this->countAll(['category_id' => $categoryId]);
    }

    /** Tìm kiếm sản phẩm theo từ khóa */
    public function search($keyword, $limit = 20) {
        return $this->db->fetchAll(
            "SELECT * FROM san_pham
             WHERE status = 'active'
             AND (name LIKE ? OR description LIKE ? OR brand LIKE ?)
             ORDER BY sold_quantity DESC
             LIMIT " . (int)$limit,
            ['%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%']
        );
    }

    /** Đếm số kết quả tìm kiếm */
    public function countSearch($keyword) {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS total FROM san_pham
             WHERE status = 'active'
             AND (name LIKE ? OR description LIKE ? OR brand LIKE ?)",
            ['%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%']
        );
        return $row['total'] ?? 0;
    }

    /** Lấy sản phẩm nổi bật (is_featured = 1) */
    public function getFeatured($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.category_id = c.id
             WHERE p.status = 'active' AND p.is_featured = 1
             ORDER BY p.created_at DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm đang flash sale còn hiệu lực */
    public function getFlashSale($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.category_id = c.id
             WHERE p.status = 'active'
             AND p.is_flash_sale = 1
             AND p.sale_price IS NOT NULL
             AND (p.flash_sale_end IS NULL OR p.flash_sale_end > NOW())
             ORDER BY p.created_at DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm mới (is_new = 1) */
    public function getNewArrivals($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.category_id = c.id
             WHERE p.status = 'active' AND p.is_new = 1
             ORDER BY p.created_at DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm bán chạy nhất */
    public function getBestSellers($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.category_id = c.id
             WHERE p.status = 'active'
             ORDER BY p.sold_quantity DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy tất cả ảnh gallery của sản phẩm từ bảng anh_san_pham */
    public function getImages($productId) {
        return $this->db->fetchAll(
            "SELECT * FROM anh_san_pham WHERE product_id = ? ORDER BY sort_order ASC",
            [$productId]
        );
    }

    /** Thêm ảnh vào gallery sản phẩm */
    public function addImage($productId, $imagePath, $sortOrder = 0) {
        return $this->db->insert('anh_san_pham', [
            'product_id' => $productId,
            'image_path' => $imagePath,
            'sort_order' => $sortOrder,
        ]);
    }

    /** Xóa toàn bộ ảnh gallery của sản phẩm */
    public function deleteImages($productId) {
        return $this->db->execute("DELETE FROM anh_san_pham WHERE product_id = ?", [$productId]);
    }

    /** Lấy các biến thể màu sắc cùng thương hiệu & danh mục */
    public function getColorVariants($productId, $categoryId, $brand, $limit = 4) {
        if (empty($brand)) return [];
        try {
            return $this->db->fetchAll(
                "SELECT id, name, slug, thumbnail, color, price, sale_price
                 FROM san_pham
                 WHERE status = 'active' AND category_id = ? AND brand = ? AND id != ?
                 ORDER BY id ASC
                 LIMIT " . (int)$limit,
                [$categoryId, $brand, $productId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Lấy sản phẩm liên quan (cùng danh mục) */
    public function getRelated($productId, $categoryId, $limit = 4) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.category_id = c.id
             WHERE p.status = 'active' AND p.category_id = ? AND p.id != ?
             ORDER BY p.sold_quantity DESC
             LIMIT " . (int)$limit,
            [$categoryId, $productId]
        );
    }

    /** Tăng bộ đếm lượt xem sản phẩm */
    public function incrementView($id) {
        return $this->db->execute("UPDATE san_pham SET views = views + 1 WHERE id = ?", [$id]);
    }

    /** Lấy danh sách đánh giá của sản phẩm */
    public function getReviews($productId) {
        return $this->db->fetchAll(
            "SELECT r.*, u.full_name, u.avatar
             FROM danh_gia r
             LEFT JOIN nguoi_dung u ON r.user_id = u.id
             WHERE r.product_id = ?
             ORDER BY r.created_at DESC",
            [$productId]
        );
    }

    /** Lấy bình luận sản phẩm kèm phản hồi */
    public function getComments($productId) {
        $comments = $this->db->fetchAll(
            "SELECT c.*, u.full_name, u.avatar
             FROM binh_luan c
             LEFT JOIN nguoi_dung u ON c.user_id = u.id
             WHERE c.product_id = ? AND c.parent_id IS NULL
             ORDER BY c.created_at DESC",
            [$productId]
        );

        // Lấy phản hồi (replies) cho từng bình luận
        foreach ($comments as &$comment) {
            $comment['replies'] = $this->db->fetchAll(
                "SELECT c.*, u.full_name, u.avatar
                 FROM binh_luan c
                 LEFT JOIN nguoi_dung u ON c.user_id = u.id
                 WHERE c.parent_id = ?
                 ORDER BY c.created_at ASC",
                [$comment['id']]
            );
        }
        return $comments;
    }

    /** Thêm đánh giá, tự động tính lại điểm trung bình */
    public function addReview($productId, $userId, $rating, $content) {
        $id = $this->db->insert('danh_gia', [
            'product_id' => $productId,
            'user_id'    => $userId,
            'rating'     => $rating,
            'content'    => $content,
        ]);
        $this->recalcRating($productId);
        return $id;
    }

    /** Tính lại điểm đánh giá trung bình của sản phẩm */
    public function recalcRating($productId) {
        $row = $this->db->fetch(
            "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
             FROM danh_gia WHERE product_id = ?",
            [$productId]
        );
        return $this->db->execute(
            "UPDATE san_pham SET rating_avg = ?, rating_count = ? WHERE id = ?",
            [round($row['avg_rating'] ?? 0, 2), $row['total'] ?? 0, $productId]
        );
    }

    /** Thêm bình luận mới (hoặc phản hồi) */
    public function addComment($productId, $userId, $content, $parentId = null) {
        return $this->db->insert('binh_luan', [
            'product_id' => $productId,
            'user_id'    => $userId,
            'parent_id'  => $parentId,
            'content'    => $content,
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
            "UPDATE san_pham SET stock = stock - ?, sold_quantity = sold_quantity + ? WHERE id = ?",
            [$quantity, $quantity, $productId]
        );
    }

    /** Lấy danh sách sản phẩm sắp hết hàng */
    public function getLowStock($limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM san_pham
             WHERE status = 'active' AND stock <= " . (int)LOW_STOCK_WARNING . "
             ORDER BY stock ASC
             LIMIT " . (int)$limit
        );
    }

    /** Admin: lấy tất cả sản phẩm, hỗ trợ tìm kiếm và lọc */
    public function adminGetAll($search = '', $categoryId = null, $stockFilter = '') {
        $sql = "SELECT p.*, c.name AS category_name
                FROM san_pham p
                LEFT JOIN danh_muc c ON p.category_id = c.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        if ($stockFilter === 'low') {
            $sql .= " AND p.stock <= " . (int)LOW_STOCK_WARNING . " AND p.stock > 0";
        } elseif ($stockFilter === 'out') {
            $sql .= " AND p.stock <= 0";
        }

        $sql .= " ORDER BY p.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }
}