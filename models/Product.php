<?php
/**
 * Product Model
 * Handles all product-related database operations
 */

class Product {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * Get all products with optional filters
     */
    public function getAll($filters = [], $limit = 12, $offset = 0) {
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
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

        // Sorting
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

    /**
     * Count products with filters (for pagination)
     */
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) AS total FROM products p WHERE p.status = 'active'";
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

    /**
     * Get product by ID
     */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.id = ?",
            [$id]
        );
    }

    /**
     * Get product by slug
     */
    public function getBySlug($slug) {
        return $this->db->fetch(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ? AND p.status = 'active'",
            [$slug]
        );
    }

    /**
     * Get products by category
     */
    public function getByCategory($categoryId, $limit = 12, $offset = 0, $sort = 'newest') {
        return $this->getAll(['category_id' => $categoryId, 'sort' => $sort], $limit, $offset);
    }

    /**
     * Count products in a category
     */
    public function countByCategory($categoryId) {
        return $this->countAll(['category_id' => $categoryId]);
    }

    /**
     * Search products
     */
    public function search($keyword, $limit = 20) {
        return $this->db->fetchAll(
            "SELECT * FROM products
             WHERE status = 'active'
             AND (name LIKE ? OR description LIKE ? OR brand LIKE ?)
             ORDER BY sold_quantity DESC
             LIMIT " . (int)$limit,
            ['%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%']
        );
    }

    /**
     * Count search results
     */
    public function countSearch($keyword) {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS total FROM products
             WHERE status = 'active'
             AND (name LIKE ? OR description LIKE ? OR brand LIKE ?)",
            ['%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%']
        );
        return $row['total'] ?? 0;
    }

    /**
     * Get featured products
     */
    public function getFeatured($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'active' AND p.is_featured = 1
             ORDER BY p.created_at DESC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Get flash sale products
     */
    public function getFlashSale($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'active'
             AND p.is_flash_sale = 1
             AND p.sale_price IS NOT NULL
             AND (p.flash_sale_end IS NULL OR p.flash_sale_end > NOW())
             ORDER BY p.created_at DESC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Get new arrivals
     */
    public function getNewArrivals($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'active' AND p.is_new = 1
             ORDER BY p.created_at DESC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Get best sellers
     */
    public function getBestSellers($limit = 8) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'active'
             ORDER BY p.sold_quantity DESC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Get product images
     */
    public function getImages($productId) {
        return $this->db->fetchAll(
            "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC",
            [$productId]
        );
    }

    /**
     * Add product image
     */
    public function addImage($productId, $imagePath, $sortOrder = 0) {
        return $this->db->insert('product_images', [
            'product_id' => $productId,
            'image_path' => $imagePath,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Delete all images of a product
     */
    public function deleteImages($productId) {
        return $this->db->execute("DELETE FROM product_images WHERE product_id = ?", [$productId]);
    }

    /**
     * Get color/variant alternatives — same brand + category, different product IDs
     */
    public function getColorVariants($productId, $categoryId, $brand, $limit = 4) {
        if (empty($brand)) return [];
        try {
            return $this->db->fetchAll(
                "SELECT id, name, slug, thumbnail, color, price, sale_price
                 FROM products
                 WHERE status = 'active' AND category_id = ? AND brand = ? AND id != ?
                 ORDER BY id ASC
                 LIMIT " . (int)$limit,
                [$categoryId, $brand, $productId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get related products (same category)
     */
    public function getRelated($productId, $categoryId, $limit = 4) {
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'active' AND p.category_id = ? AND p.id != ?
             ORDER BY p.sold_quantity DESC
             LIMIT " . (int)$limit,
            [$categoryId, $productId]
        );
    }

    /**
     * Increment view counter
     */
    public function incrementView($id) {
        return $this->db->execute("UPDATE products SET views = views + 1 WHERE id = ?", [$id]);
    }

    /**
     * Get reviews of a product
     */
    public function getReviews($productId) {
        return $this->db->fetchAll(
            "SELECT r.*, u.full_name, u.avatar
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.product_id = ?
             ORDER BY r.created_at DESC",
            [$productId]
        );
    }

    /**
     * Get comments of a product (with replies)
     */
    public function getComments($productId) {
        $comments = $this->db->fetchAll(
            "SELECT c.*, u.full_name, u.avatar
             FROM comments c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.product_id = ? AND c.parent_id IS NULL
             ORDER BY c.created_at DESC",
            [$productId]
        );

        // Get replies
        foreach ($comments as &$comment) {
            $comment['replies'] = $this->db->fetchAll(
                "SELECT c.*, u.full_name, u.avatar
                 FROM comments c
                 LEFT JOIN users u ON c.user_id = u.id
                 WHERE c.parent_id = ?
                 ORDER BY c.created_at ASC",
                [$comment['id']]
            );
        }
        return $comments;
    }

    /**
     * Add review
     */
    public function addReview($productId, $userId, $rating, $content) {
        $id = $this->db->insert('reviews', [
            'product_id' => $productId,
            'user_id'    => $userId,
            'rating'     => $rating,
            'content'    => $content,
        ]);
        $this->recalcRating($productId);
        return $id;
    }

    /**
     * Recalculate average rating
     */
    public function recalcRating($productId) {
        $row = $this->db->fetch(
            "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
             FROM reviews WHERE product_id = ?",
            [$productId]
        );
        return $this->db->execute(
            "UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?",
            [round($row['avg_rating'] ?? 0, 2), $row['total'] ?? 0, $productId]
        );
    }

    /**
     * Add comment
     */
    public function addComment($productId, $userId, $content, $parentId = null) {
        return $this->db->insert('comments', [
            'product_id' => $productId,
            'user_id'    => $userId,
            'parent_id'  => $parentId,
            'content'    => $content,
        ]);
    }

    // ============================================================
    //  ADMIN CRUD
    // ============================================================

    /**
     * Create new product
     */
    public function create($data) {
        return $this->db->insert('products', $data);
    }

    /**
     * Update product
     */
    public function update($id, $data) {
        $set = [];
        $params = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $set) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }

    /**
     * Delete product
     */
    public function delete($id) {
        return $this->db->execute("DELETE FROM products WHERE id = ?", [$id]);
    }

    /**
     * Decrease stock after order
     */
    public function decreaseStock($productId, $quantity) {
        return $this->db->execute(
            "UPDATE products SET stock = stock - ?, sold_quantity = sold_quantity + ? WHERE id = ?",
            [$quantity, $quantity, $productId]
        );
    }

    /**
     * Get low stock products
     */
    public function getLowStock($limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM products
             WHERE status = 'active' AND stock <= " . (int)LOW_STOCK_WARNING . "
             ORDER BY stock ASC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Admin: list with search/filter
     */
    public function adminGetAll($search = '', $categoryId = null, $stockFilter = '') {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
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