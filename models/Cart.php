<?php
/**
 * Model Giỏ Hàng — bảng: gio_hang
 * Quản lý giỏ hàng cho người dùng đã đăng nhập (CSDL) và khách (session)
 */

class Cart {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Lấy danh sách sản phẩm trong giỏ (CSDL nếu đăng nhập, session nếu khách) */
    public function getItems($userId = null) {
        if ($userId) {
            return $this->db->fetchAll(
                "SELECT c.*, p.name, p.thumbnail, p.slug, p.price, p.sale_price, p.stock
                 FROM gio_hang c
                 LEFT JOIN san_pham p ON c.product_id = p.id
                 WHERE c.user_id = ?
                 ORDER BY c.created_at DESC",
                [$userId]
            );
        }

        // Giỏ hàng khách — lấy từ session
        $items = [];
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $item) {
                $product = $this->db->fetch(
                    "SELECT id, name, thumbnail, slug, price, sale_price, stock FROM san_pham WHERE id = ?",
                    [$productId]
                );
                if ($product) {
                    $product['quantity'] = $item['quantity'];
                    $product['product_id'] = $productId;
                    $items[] = $product;
                }
            }
        }
        return $items;
    }

    /** Thêm sản phẩm vào giỏ hàng (tự tăng số lượng nếu đã tồn tại) */
    public function add($productId, $quantity = 1, $userId = null) {
        if ($userId) {
            // Kiểm tra sản phẩm đã có trong giỏ chưa
            $existing = $this->db->fetch(
                "SELECT * FROM gio_hang WHERE user_id = ? AND product_id = ?",
                [$userId, $productId]
            );
            if ($existing) {
                return $this->db->execute(
                    "UPDATE gio_hang SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?",
                    [$quantity, $userId, $productId]
                );
            }
            return $this->db->insert('gio_hang', [
                'user_id'    => $userId,
                'product_id' => $productId,
                'quantity'   => $quantity,
            ]);
        }

        // Giỏ hàng khách — lưu vào session
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'product_id' => $productId,
                'quantity'   => $quantity,
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
                "UPDATE gio_hang SET quantity = ? WHERE user_id = ? AND product_id = ?",
                [$quantity, $userId, $productId]
            );
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        }
        return true;
    }

    /** Xóa một sản phẩm khỏi giỏ hàng */
    public function remove($productId, $userId = null) {
        if ($userId) {
            return $this->db->execute(
                "DELETE FROM gio_hang WHERE user_id = ? AND product_id = ?",
                [$userId, $productId]
            );
        }
        unset($_SESSION['cart'][$productId]);
        return true;
    }

    /** Xóa toàn bộ giỏ hàng */
    public function clear($userId = null) {
        if ($userId) {
            return $this->db->execute("DELETE FROM gio_hang WHERE user_id = ?", [$userId]);
        }
        $_SESSION['cart'] = [];
        return true;
    }

    /** Đếm tổng số lượng sản phẩm trong giỏ */
    public function count($userId = null) {
        if ($userId) {
            $row = $this->db->fetch(
                "SELECT COALESCE(SUM(quantity),0) AS c FROM gio_hang WHERE user_id = ?",
                [$userId]
            );
            return $row['c'] ?? 0;
        }
        if (empty($_SESSION['cart'])) return 0;
        return array_sum(array_column($_SESSION['cart'], 'quantity'));
    }

    /** Tính tổng giá trị giỏ hàng */
    public function getTotal($userId = null) {
        $items = $this->getItems($userId);
        $total = 0;
        foreach ($items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $total += $price * $item['quantity'];
        }
        return $total;
    }

    /** Đồng bộ giỏ hàng từ session vào CSDL sau khi khách đăng nhập */
    public function mergeSessionToDb($userId) {
        if (empty($_SESSION['cart'])) return;
        foreach ($_SESSION['cart'] as $productId => $item) {
            $this->add($productId, $item['quantity'], $userId);
        }
        $_SESSION['cart'] = [];
    }
}