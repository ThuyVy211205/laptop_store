<?php
/**
 * Model Yêu Thích — bảng: yeu_thich
 * Quản lý danh sách sản phẩm yêu thích của người dùng
 */

class Wishlist {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy danh sách sản phẩm yêu thích của người dùng */
    public function getByUser($userId) {
        return $this->db->fetchAll(
            "SELECT p.id, p.name, p.slug, p.thumbnail, p.price, p.sale_price, p.stock,
                    p.rating_avg, p.rating_count, w.created_at
             FROM yeu_thich w
             INNER JOIN san_pham p ON w.product_id = p.id AND p.status = 'active'
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC",
            [$userId]
        );
    }

    /** Kiểm tra sản phẩm có trong danh sách yêu thích không */
    public function isInWishlist($userId, $productId) {
        $row = $this->db->fetch(
            "SELECT id FROM yeu_thich WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        return (bool) $row;
    }

    /** Thêm sản phẩm vào yêu thích (bỏ qua nếu đã có) */
    public function add($userId, $productId) {
        if ($this->isInWishlist($userId, $productId)) return false;
        return $this->db->insert('yeu_thich', [
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);
    }

    /** Xóa sản phẩm khỏi danh sách yêu thích */
    public function remove($userId, $productId) {
        return $this->db->execute(
            "DELETE FROM yeu_thich WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
    }

    /** Chuyển đổi trạng thái yêu thích — trả về 'added' hoặc 'removed' */
    public function toggle($userId, $productId) {
        if ($this->isInWishlist($userId, $productId)) {
            $this->remove($userId, $productId);
            return 'removed';
        }
        $this->add($userId, $productId);
        return 'added';
    }

    /** Đếm số sản phẩm yêu thích của người dùng */
    public function count($userId) {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM yeu_thich WHERE user_id = ?",
            [$userId]
        );
        return $row['c'] ?? 0;
    }
}