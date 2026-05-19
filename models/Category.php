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
     * Get all categories with product count
     */
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'active') AS product_count
             FROM categories c
             WHERE c.status = 'active'
             ORDER BY c.sort_order ASC, c.id ASC"
        );
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
        return $this->db->fetch("SELECT * FROM categories WHERE slug = ?", [$slug]);
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