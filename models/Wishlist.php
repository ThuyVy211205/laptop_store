<?php
/**
 * Wishlist Model
 */

class Wishlist {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * Get user's wishlist items
     */
    public function getByUser($userId) {
        return $this->db->fetchAll(
            "SELECT w.*, p.name, p.slug, p.thumbnail, p.price, p.sale_price, p.stock,
                    p.rating_avg, p.rating_count
             FROM wishlists w
             LEFT JOIN products p ON w.product_id = p.id
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC",
            [$userId]
        );
    }

    /**
     * Check if product is in wishlist
     */
    public function isInWishlist($userId, $productId) {
        $row = $this->db->fetch(
            "SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        return (bool) $row;
    }

    /**
     * Add to wishlist
     */
    public function add($userId, $productId) {
        if ($this->isInWishlist($userId, $productId)) return false;
        return $this->db->insert('wishlists', [
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Remove from wishlist
     */
    public function remove($userId, $productId) {
        return $this->db->execute(
            "DELETE FROM wishlists WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
    }

    /**
     * Toggle wishlist (add if not exists, remove if exists)
     * Returns 'added' or 'removed'
     */
    public function toggle($userId, $productId) {
        if ($this->isInWishlist($userId, $productId)) {
            $this->remove($userId, $productId);
            return 'removed';
        }
        $this->add($userId, $productId);
        return 'added';
    }

    /**
     * Count
     */
    public function count($userId) {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM wishlists WHERE user_id = ?",
            [$userId]
        );
        return $row['c'] ?? 0;
    }
}