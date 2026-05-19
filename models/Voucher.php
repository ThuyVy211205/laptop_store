<?php
/**
 * Voucher Model
 */

class Voucher {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * Get voucher by code
     */
    public function getByCode($code) {
        return $this->db->fetch(
            "SELECT * FROM vouchers WHERE code = ? AND is_active = 1",
            [strtoupper($code)]
        );
    }

    /**
     * Get all vouchers
     */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM vouchers ORDER BY created_at DESC");
    }

    /**
     * Apply voucher - returns ['success' => bool, 'message' => str, 'discount' => float]
     */
    public function apply($code, $orderTotal) {
        $voucher = $this->getByCode($code);

        if (!$voucher) {
            return ['success' => false, 'message' => 'Mã voucher không tồn tại'];
        }

        if ($voucher['expires_at'] && strtotime($voucher['expires_at']) < time()) {
            return ['success' => false, 'message' => 'Voucher đã hết hạn'];
        }

        if ($voucher['quantity'] > 0 && $voucher['used_count'] >= $voucher['quantity']) {
            return ['success' => false, 'message' => 'Voucher đã hết lượt sử dụng'];
        }

        if ($voucher['min_order'] > 0 && $orderTotal < $voucher['min_order']) {
            return [
                'success' => false,
                'message' => 'Đơn hàng tối thiểu ' . formatPrice($voucher['min_order']),
            ];
        }

        $discount = 0;
        if ($voucher['type'] === 'percent') {
            $discount = $orderTotal * ($voucher['value'] / 100);
            if ($voucher['max_discount'] && $discount > $voucher['max_discount']) {
                $discount = $voucher['max_discount'];
            }
        } else {
            $discount = $voucher['value'];
        }

        return [
            'success'  => true,
            'message'  => 'Áp dụng voucher thành công',
            'discount' => $discount,
            'voucher'  => $voucher,
        ];
    }

    /**
     * Mark voucher as used
     */
    public function use($voucherId) {
        return $this->db->execute(
            "UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?",
            [$voucherId]
        );
    }

    /**
     * Create voucher
     */
    public function create($data) {
        $data['code'] = strtoupper($data['code']);
        return $this->db->insert('vouchers', $data);
    }

    /**
     * Update voucher
     */
    public function update($id, $data) {
        $set = [];
        $params = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = ?";
            $params[] = $val;
        }
        $params[] = $id;
        return $this->db->execute("UPDATE vouchers SET " . implode(', ', $set) . " WHERE id = ?", $params);
    }

    /**
     * Delete voucher
     */
    public function delete($id) {
        return $this->db->execute("DELETE FROM vouchers WHERE id = ?", [$id]);
    }
}