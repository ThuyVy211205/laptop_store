<?php
/**
 * User Model
 */

class User {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        return $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }

    /**
     * Get user by Google ID
     */
    public function getByGoogleId($googleId) {
        return $this->db->fetch("SELECT * FROM users WHERE google_id = ?", [$googleId]);
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        return (bool) $this->db->fetch($sql, $params);
    }

    /**
     * Create new user
     */
    public function create($data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->db->insert('users', $data);
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        // If password is set, hash it
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } elseif (isset($data['password'])) {
            unset($data['password']);
        }

        $set = [];
        $params = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $set) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }

    /**
     * Verify password
     */
    public function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Save reset password token
     */
    public function saveResetToken($email, $token, $expiresAt) {
        return $this->db->execute(
            "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?",
            [$token, $expiresAt, $email]
        );
    }

    /**
     * Get user by reset token
     */
    public function getByResetToken($token) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()",
            [$token]
        );
    }

    /**
     * Clear reset token
     */
    public function clearResetToken($userId) {
        return $this->db->execute(
            "UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = ?",
            [$userId]
        );
    }

    /**
     * Update user rank based on total spent
     */
    public function updateRank($userId) {
        $user = $this->getById($userId);
        if (!$user) return false;

        $spent = $user['total_spent'];
        $rank = 'silver';
        if ($spent >= 50000000) {
            $rank = 'diamond';
        } elseif ($spent >= 15000000) {
            $rank = 'gold';
        }
        return $this->db->execute("UPDATE users SET `rank` = ? WHERE id = ?", [$rank, $userId]);
    }

    /**
     * Increment total_spent + total_orders
     */
    public function incrementStats($userId, $amount) {
        return $this->db->execute(
            "UPDATE users SET total_spent = total_spent + ?, total_orders = total_orders + 1 WHERE id = ?",
            [$amount, $userId]
        );
    }

    // ============================================================
    //  ADMIN
    // ============================================================

    /**
     * Admin: get all users
     */
    public function adminGetAll($search = '', $filterRank = '') {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($filterRank) {
            $sql .= " AND `rank` = ?";
            $params[] = $filterRank;
        }

        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Toggle status (active/blocked)
     */
    public function toggleStatus($userId) {
        $user = $this->getById($userId);
        if (!$user) return false;
        $newStatus = $user['status'] === 'active' ? 'blocked' : 'active';
        return $this->db->execute("UPDATE users SET status = ? WHERE id = ?", [$newStatus, $userId]);
    }

    /**
     * Count total customers
     */
    public function countTotal() {
        $row = $this->db->fetch("SELECT COUNT(*) AS total FROM users");
        return $row['total'] ?? 0;
    }
}