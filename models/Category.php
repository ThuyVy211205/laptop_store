<?php
/**
 * Category Model
 */

class Category {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * Get all top-level categories with product count
     */
    public function getAll() {
        $cats = $this->db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'active') AS product_count
             FROM categories c
             WHERE c.status = 'active' AND (c.parent_id IS NULL OR c.parent_id = 0)
             ORDER BY c.sort_order ASC, c.id ASC"
        );
        // Ensure every category has a usable slug (generate from name if DB value is empty)
        foreach ($cats as &$cat) {
            if (empty($cat['slug']) && !empty($cat['name'])) {
                $cat['slug'] = createSlug($cat['name']);
            }
        }
        unset($cat);
        return $cats;
    }

    /**
     * Get distinct brands grouped by category (for navbar submenu)
     * Returns: [ category_id => [ ['brand' => ..., 'count' => ...], ... ], ... ]
     */
    public function getBrandsPerCategory() {
        $rows = $this->db->fetchAll(
            "SELECT p.category_id, p.brand, COUNT(*) AS cnt
             FROM products p
             JOIN categories c ON c.id = p.category_id
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

    /**
     * Get category by ID
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
    }

    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        if (empty($slug)) return null;

        // Try exact DB slug match first
        $cat = $this->db->fetch("SELECT * FROM categories WHERE slug = ?", [$slug]);
        if ($cat) return $cat;

        // Fallback: DB slug may be empty — match by generating slug from name
        $all = $this->db->fetchAll("SELECT * FROM categories WHERE status = 'active'");
        foreach ($all as $c) {
            if (!empty($c['name']) && createSlug($c['name']) === $slug) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Create
     */
    public function create($data) {
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = createSlug($data['name']);
        }
        return $this->db->insert('categories', $data);
    }

    /**
     * Update
     */
    public function update($id, $data) {
        $set = [];
        $params = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = ?";
            $params[] = $val;
        }
        $params[] = $id;
        return $this->db->execute("UPDATE categories SET " . implode(', ', $set) . " WHERE id = ?", $params);
    }

    /**
     * Delete
     */
    public function delete($id) {
        return $this->db->execute("DELETE FROM categories WHERE id = ?", [$id]);
    }
}