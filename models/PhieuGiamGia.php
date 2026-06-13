<?php
/**
 * Model Phiếu Giảm Giá — bảng: phieu_giam_gia
 * Xử lý kiểm tra, áp dụng và quản lý mã giảm giá
 */

class PhieuGiamGia {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy phiếu giảm giá theo mã (chỉ trả về phiếu đang kích hoạt) */
    public function getByCode($code) {
        return $this->db->fetch(
            "SELECT * FROM phieu_giam_gia WHERE ma_phieu = ? AND dang_hoat_dong = 1",
            [strtoupper($code)]
        );
    }

    /** Lấy tất cả phiếu giảm giá (admin) */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM phieu_giam_gia ORDER BY ngay_tao DESC");
    }

    /** Áp dụng phiếu giảm giá — trả về ['success', 'message', 'discount', 'voucher'] */
    public function apply($code, $orderTotal) {
        $voucher = $this->getByCode($code);

        if (!$voucher) {
            return ['success' => false, 'message' => 'Mã voucher không tồn tại'];
        }

        if ($voucher['ngay_het_han'] && strtotime($voucher['ngay_het_han']) < time()) {
            return ['success' => false, 'message' => 'Voucher đã hết hạn'];
        }

        if ($voucher['so_luong'] > 0 && $voucher['so_lan_dung'] >= $voucher['so_luong']) {
            return ['success' => false, 'message' => 'Voucher đã hết lượt sử dụng'];
        }

        if ($voucher['don_hang_toi_thieu'] > 0 && $orderTotal < $voucher['don_hang_toi_thieu']) {
            return [
                'success' => false,
                'message' => 'Đơn hàng tối thiểu ' . formatPrice($voucher['don_hang_toi_thieu']),
            ];
        }

        $discount = 0;
        if ($voucher['loai'] === 'percent') {
            $discount = $orderTotal * ($voucher['gia_tri'] / 100);
            if ($voucher['giam_toi_da'] && $discount > $voucher['giam_toi_da']) {
                $discount = $voucher['giam_toi_da'];
            }
        } else {
            $discount = $voucher['gia_tri'];
        }

        return [
            'success'  => true,
            'message'  => 'Áp dụng voucher thành công',
            'discount' => $discount,
            'voucher'  => $voucher,
        ];
    }

    /** Tăng biến đếm lượt sử dụng phiếu giảm giá */
    public function use($voucherId) {
        return $this->db->execute(
            "UPDATE phieu_giam_gia SET so_lan_dung = so_lan_dung + 1 WHERE id = ?",
            [$voucherId]
        );
    }

    /** Tạo phiếu giảm giá mới, tự chuyển mã thành chữ hoa */
    public function create($data) {
        $data['ma_phieu'] = strtoupper($data['ma_phieu']);
        return $this->db->insert('phieu_giam_gia', $data);
    }

    /** Cập nhật thông tin phiếu giảm giá */
    public function update($id, $data) {
        $set = [];
        $params = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = ?";
            $params[] = $val;
        }
        $params[] = $id;
        return $this->db->execute("UPDATE phieu_giam_gia SET " . implode(', ', $set) . " WHERE id = ?", $params);
    }

    /** Xóa phiếu giảm giá */
    public function delete($id) {
        return $this->db->execute("DELETE FROM phieu_giam_gia WHERE id = ?", [$id]);
    }
}
