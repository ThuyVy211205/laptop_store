<?php
/**
 * Model Giỏ Hàng — bảng: gio_hang
 * Quản lý giỏ hàng cho người dùng đã đăng nhập (CSDL) và khách (session)
 */

class GioHang {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy danh sách sản phẩm trong giỏ (CSDL nếu đăng nhập, session nếu khách) */
    public function getItems($userId = null) {
        if ($userId) {
            return $this->db->fetchAll(
                "SELECT c.*, p.ten, p.hinh_thu_nho, p.duong_dan, p.gia, p.gia_khuyen_mai, p.ton_kho
                 FROM gio_hang c
                 LEFT JOIN san_pham p ON c.id_san_pham = p.id
                 WHERE c.id_nguoi_dung = ?
                 ORDER BY c.ngay_tao DESC",
                [$userId]
            );
        }

        $items = [];
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $item) {
                $product = $this->db->fetch(
                    "SELECT id, ten, hinh_thu_nho, duong_dan, gia, gia_khuyen_mai, ton_kho FROM san_pham WHERE id = ?",
                    [$productId]
                );
                if ($product) {
                    $product['so_luong'] = $item['so_luong'];
                    $product['id_san_pham'] = $productId;
                    $items[] = $product;
                }
            }
        }
        return $items;
    }

    /** Thêm sản phẩm vào giỏ hàng (tự tăng số lượng nếu đã tồn tại) */
    public function add($productId, $quantity = 1, $userId = null) {
        if ($userId) {
            $existing = $this->db->fetch(
                "SELECT * FROM gio_hang WHERE id_nguoi_dung = ? AND id_san_pham = ?",
                [$userId, $productId]
            );
            if ($existing) {
                return $this->db->execute(
                    "UPDATE gio_hang SET so_luong = so_luong + ? WHERE id_nguoi_dung = ? AND id_san_pham = ?",
                    [$quantity, $userId, $productId]
                );
            }
            return $this->db->insert('gio_hang', [
                'id_nguoi_dung' => $userId,
                'id_san_pham'   => $productId,
                'so_luong'      => $quantity,
            ]);
        }

        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['so_luong'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'id_san_pham' => $productId,
                'so_luong'    => $quantity,
            ];
        }
        return true;
    }

    /** Cập nhật số lượng sản phẩm trong giỏ */
    public function update($productId, $quantity, $userId = null) {
        if ($quantity <= 0) {
            return $this->remove($productId, $userId);
        }

        if ($userId) {
            return $this->db->execute(
                "UPDATE gio_hang SET so_luong = ? WHERE id_nguoi_dung = ? AND id_san_pham = ?",
                [$quantity, $userId, $productId]
            );
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['so_luong'] = $quantity;
        }
        return true;
    }

    /** Xóa một sản phẩm khỏi giỏ hàng */
    public function remove($productId, $userId = null) {
        if ($userId) {
            return $this->db->execute(
                "DELETE FROM gio_hang WHERE id_nguoi_dung = ? AND id_san_pham = ?",
                [$userId, $productId]
            );
        }
        unset($_SESSION['cart'][$productId]);
        return true;
    }

    /** Xóa toàn bộ giỏ hàng */
    public function clear($userId = null) {
        if ($userId) {
            return $this->db->execute("DELETE FROM gio_hang WHERE id_nguoi_dung = ?", [$userId]);
        }
        $_SESSION['cart'] = [];
        return true;
    }

    /** Đếm tổng số lượng sản phẩm trong giỏ */
    public function count($userId = null) {
        if ($userId) {
            $row = $this->db->fetch(
                "SELECT COALESCE(SUM(so_luong),0) AS tong FROM gio_hang WHERE id_nguoi_dung = ?",
                [$userId]
            );
            return $row['tong'] ?? 0;
        }
        if (empty($_SESSION['cart'])) return 0;
        return array_sum(array_column($_SESSION['cart'], 'so_luong'));
    }

    /** Tính tổng giá trị giỏ hàng */
    public function getTotal($userId = null) {
        $items = $this->getItems($userId);
        $total = 0;
        foreach ($items as $item) {
            $price = $item['gia_khuyen_mai'] ?: $item['gia'];
            $soLuong = $item['so_luong'] ?? $item['quantity'] ?? 0;
            $total += $price * $soLuong;
        }
        return $total;
    }

    /** Đồng bộ giỏ hàng từ session vào CSDL sau khi khách đăng nhập */
    public function mergeSessionToDb($userId) {
        if (empty($_SESSION['cart'])) return;
        foreach ($_SESSION['cart'] as $productId => $item) {
            $this->add($productId, $item['so_luong'] ?? $item['quantity'] ?? 1, $userId);
        }
        $_SESSION['cart'] = [];
    }
}
