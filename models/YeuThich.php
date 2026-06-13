<?php
/**
 * Model Yêu Thích — bảng: yeu_thich
 * Quản lý danh sách sản phẩm yêu thích của người dùng
 */

class YeuThich {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy danh sách sản phẩm yêu thích của người dùng */
    public function getByUser($userId) {
        return $this->db->fetchAll(
            "SELECT p.id, p.ten, p.duong_dan, p.hinh_thu_nho, p.gia, p.gia_khuyen_mai, p.ton_kho,
                    p.diem_danh_gia, p.so_danh_gia, w.ngay_tao
             FROM yeu_thich w
             INNER JOIN san_pham p ON w.id_san_pham = p.id AND p.trang_thai = 'active'
             WHERE w.id_nguoi_dung = ?
             ORDER BY w.ngay_tao DESC",
            [$userId]
        );
    }

    /** Kiểm tra sản phẩm có trong danh sách yêu thích không */
    public function isInWishlist($userId, $productId) {
        $row = $this->db->fetch(
            "SELECT id FROM yeu_thich WHERE id_nguoi_dung = ? AND id_san_pham = ?",
            [$userId, $productId]
        );
        return (bool) $row;
    }

    /** Thêm sản phẩm vào yêu thích (bỏ qua nếu đã có) */
    public function add($userId, $productId) {
        if ($this->isInWishlist($userId, $productId)) return false;
        return $this->db->insert('yeu_thich', [
            'id_nguoi_dung' => $userId,
            'id_san_pham'   => $productId,
        ]);
    }

    /** Xóa sản phẩm khỏi danh sách yêu thích */
    public function remove($userId, $productId) {
        return $this->db->execute(
            "DELETE FROM yeu_thich WHERE id_nguoi_dung = ? AND id_san_pham = ?",
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
            "SELECT COUNT(*) AS tong FROM yeu_thich WHERE id_nguoi_dung = ?",
            [$userId]
        );
        return $row['tong'] ?? 0;
    }
}
