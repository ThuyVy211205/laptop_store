<?php
/**
 * Model Người Dùng — bảng: nguoi_dung
 * Xử lý đăng ký, đăng nhập, cập nhật thông tin, xếp hạng thành viên
 */

class NguoiDung {
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
        return $this->db->fetch("SELECT * FROM nguoi_dung WHERE thu_dien_tu = ?", [$email]);
    }

    /** Lấy người dùng đăng nhập bằng Google OAuth */
    public function getByGoogleId($googleId) {
        return $this->db->fetch("SELECT * FROM nguoi_dung WHERE id_google = ?", [$googleId]);
    }

    /** Kiểm tra email đã tồn tại chưa (bỏ qua ID hiện tại khi cập nhật) */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM nguoi_dung WHERE thu_dien_tu = ?";
        $params = [$email];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        return (bool) $this->db->fetch($sql, $params);
    }

    /** Tạo tài khoản người dùng mới, tự động hash mật khẩu */
    public function create($data) {
        if (isset($data['mat_khau']) && !empty($data['mat_khau'])) {
            $data['mat_khau'] = password_hash($data['mat_khau'], PASSWORD_DEFAULT);
        }
        return $this->db->insert('nguoi_dung', $data);
    }

    /** Cập nhật thông tin người dùng, hash lại mật khẩu nếu có */
    public function update($id, $data) {
        if (isset($data['mat_khau']) && !empty($data['mat_khau'])) {
            $data['mat_khau'] = password_hash($data['mat_khau'], PASSWORD_DEFAULT);
        } elseif (isset($data['mat_khau'])) {
            unset($data['mat_khau']);
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
            "UPDATE nguoi_dung SET ma_dat_lai = ?, han_dat_lai = ? WHERE thu_dien_tu = ?",
            [$token, $expiresAt, $email]
        );
    }

    /** Lấy người dùng theo token đặt lại mật khẩu (còn hiệu lực) */
    public function getByResetToken($token) {
        return $this->db->fetch(
            "SELECT * FROM nguoi_dung WHERE ma_dat_lai = ? AND han_dat_lai > NOW()",
            [$token]
        );
    }

    /** Xóa token đặt lại mật khẩu sau khi dùng xong */
    public function clearResetToken($userId) {
        return $this->db->execute(
            "UPDATE nguoi_dung SET ma_dat_lai = NULL, han_dat_lai = NULL WHERE id = ?",
            [$userId]
        );
    }

    /** Cập nhật hạng thành viên dựa vào tổng chi tiêu (silver/gold/diamond) */
    public function updateRank($userId) {
        $user = $this->getById($userId);
        if (!$user) return false;

        $spent = $user['tong_chi_tieu'];
        $hang = 'silver';
        if ($spent >= 50000000) {
            $hang = 'diamond';
        } elseif ($spent >= 15000000) {
            $hang = 'gold';
        }
        return $this->db->execute("UPDATE nguoi_dung SET `hang` = ? WHERE id = ?", [$hang, $userId]);
    }

    /** Tăng tổng tiền đã tiêu và số đơn hàng khi đặt hàng thành công */
    public function incrementStats($userId, $amount) {
        return $this->db->execute(
            "UPDATE nguoi_dung SET tong_chi_tieu = tong_chi_tieu + ?, tong_don_hang = tong_don_hang + 1 WHERE id = ?",
            [$amount, $userId]
        );
    }

    /** Giảm tổng tiền và số đơn khi đơn hàng bị hủy */
    public function decrementStats($userId, $amount) {
        return $this->db->execute(
            "UPDATE nguoi_dung SET
                tong_chi_tieu = GREATEST(0, tong_chi_tieu - ?),
                tong_don_hang = GREATEST(0, tong_don_hang - 1)
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
            $sql .= " AND (ho_ten LIKE ? OR thu_dien_tu LIKE ? OR so_dien_thoai LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($filterRank) {
            $sql .= " AND `hang` = ?";
            $params[] = $filterRank;
        }

        $sql .= " ORDER BY ngay_tao DESC";
        return $this->db->fetchAll($sql, $params);
    }

    /** Chuyển đổi trạng thái active/blocked của tài khoản */
    public function toggleStatus($userId) {
        $user = $this->getById($userId);
        if (!$user) return false;
        $newStatus = $user['trang_thai'] === 'active' ? 'blocked' : 'active';
        return $this->db->execute("UPDATE nguoi_dung SET trang_thai = ? WHERE id = ?", [$newStatus, $userId]);
    }

    /** Đếm tổng số khách hàng */
    public function countTotal() {
        $row = $this->db->fetch("SELECT COUNT(*) AS tong FROM nguoi_dung");
        return $row['tong'] ?? 0;
    }
}
