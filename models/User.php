<?php
/**
 * Model Người Dùng — bảng: nguoi_dung
 * Xử lý đăng ký, đăng nhập, cập nhật thông tin, xếp hạng thành viên
 */

class User {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy thông tin người dùng theo ID */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM nguoi_dung WHERE id = ?", [$id]);
    }

    /** Lấy người dùng theo địa chỉ email */
    public function getByEmail($email) {
        return $this->db->fetch("SELECT * FROM nguoi_dung WHERE email = ?", [$email]);
    }

    /** Lấy người dùng đăng nhập bằng Google OAuth */
    public function getByGoogleId($googleId) {
        return $this->db->fetch("SELECT * FROM nguoi_dung WHERE google_id = ?", [$googleId]);
    }

    /** Kiểm tra email đã tồn tại chưa (bỏ qua ID hiện tại khi cập nhật) */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM nguoi_dung WHERE email = ?";
        $params = [$email];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        return (bool) $this->db->fetch($sql, $params);
    }

    /** Tạo tài khoản người dùng mới, tự động hash mật khẩu */
    public function create($data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->db->insert('nguoi_dung', $data);
    }

    /** Cập nhật thông tin người dùng, hash lại mật khẩu nếu có */
    public function update($id, $data) {
        // Hash lại mật khẩu nếu có cập nhật
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
        $sql = "UPDATE nguoi_dung SET " . implode(', ', $set) . " WHERE id = ?";
        return $this->db->execute($sql, $params);
    }

    /** Xác thực mật khẩu người dùng (so sánh với hash) */
    public function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }

    /** Lưu token đặt lại mật khẩu và thời hạn hết hạn */
    public function saveResetToken($email, $token, $expiresAt) {
        return $this->db->execute(
            "UPDATE nguoi_dung SET reset_token = ?, reset_expires = ? WHERE email = ?",
            [$token, $expiresAt, $email]
        );
    }

    /** Lấy người dùng theo token đặt lại mật khẩu (còn hiệu lực) */
    public function getByResetToken($token) {
        return $this->db->fetch(
            "SELECT * FROM nguoi_dung WHERE reset_token = ? AND reset_expires > NOW()",
            [$token]
        );
    }

    /** Xóa token đặt lại mật khẩu sau khi dùng xong */
    public function clearResetToken($userId) {
        return $this->db->execute(
            "UPDATE nguoi_dung SET reset_token = NULL, reset_expires = NULL WHERE id = ?",
            [$userId]
        );
    }

    /** Cập nhật hạng thành viên dựa vào tổng chi tiêu (silver/gold/diamond) */
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
        return $this->db->execute("UPDATE nguoi_dung SET `rank` = ? WHERE id = ?", [$rank, $userId]);
    }

    /** Tăng tổng tiền đã tiêu và số đơn hàng khi đặt hàng thành công */
    public function incrementStats($userId, $amount) {
        return $this->db->execute(
            "UPDATE nguoi_dung SET total_spent = total_spent + ?, total_orders = total_orders + 1 WHERE id = ?",
            [$amount, $userId]
        );
    }

    /** Giảm tổng tiền và số đơn khi đơn hàng bị hủy */
    public function decrementStats($userId, $amount) {
        return $this->db->execute(
            "UPDATE nguoi_dung SET
                total_spent = GREATEST(0, total_spent - ?),
                total_orders = GREATEST(0, total_orders - 1)
             WHERE id = ?",
            [$amount, $userId]
        );
    }

    // ============================================================
    //  ADMIN: Quản lý người dùng
    // ============================================================

    /** Admin: lấy tất cả người dùng, hỗ trợ tìm kiếm và lọc theo hạng */
    public function adminGetAll($search = '', $filterRank = '') {
        $sql = "SELECT * FROM nguoi_dung WHERE 1=1";
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

    /** Chuyển đổi trạng thái active/blocked của tài khoản */
    public function toggleStatus($userId) {
        $user = $this->getById($userId);
        if (!$user) return false;
        $newStatus = $user['status'] === 'active' ? 'blocked' : 'active';
        return $this->db->execute("UPDATE nguoi_dung SET status = ? WHERE id = ?", [$newStatus, $userId]);
    }

    /** Đếm tổng số khách hàng */
    public function countTotal() {
        $row = $this->db->fetch("SELECT COUNT(*) AS total FROM nguoi_dung");
        return $row['total'] ?? 0;
    }
}