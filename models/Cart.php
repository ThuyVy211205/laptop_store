<?php
/**
 * Cart Model
 * Handles cart for both logged-in users (DB) and guests (session)
 */

class Cart {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * Get cart items for user (DB) or session (guest)
     */
    public function getItems($userId = null) {
        if ($userId) {
            return $this->db->fetchAll(
                "SELECT c.*, p.name, p.thumbnail, p.slug, p.price, p.sale_price, p.stock
                 FROM carts c
                 LEFT JOIN products p ON c.product_id = p.id
                 WHERE c.user_id = ?
                 ORDER BY c.created_at DESC",
                [$userId]
            );
        }

        // Guest cart from session
        $items = [];
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $item) {
                $product = $this->db->fetch(
                    "SELECT id, name, thumbnail, slug, price, sale_price, stock FROM products WHERE id = ?",
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

    /**
     * Add to cart
     */
    public function add($productId, $quantity = 1, $userId = null) {
        if ($userId) {
            // Check if exists
            $existing = $this->db->fetch(
                "SELECT * FROM carts WHERE user_id = ? AND product_id = ?",
                [$userId, $productId]
            );
            if ($existing) {
                return $this->db->execute(
                    "UPDATE carts SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?",
                    [$quantity, $userId, $productId]
                );
            }
            return $this->db->insert('carts', [
                'user_id'    => $userId,
                'product_id' => $productId,
                'quantity'   => $quantity,
            ]);
        }

        // Guest cart
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

    /**
     * Update quantity
     */
    public function update($productId, $quantity, $userId = null) {
        if ($quantity <= 0) {
            return $this->remove($productId, $userId);
        }

        if ($userId) {
            return $this->db->execute(
                "UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?",
                [$quantity, $userId, $productId]
            );
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        }
        return true;
    }

    /**
     * Remove item
     */
    public function remove($productId, $userId = null) {
        if ($userId) {
            return $this->db->execute(
                "DELETE FROM carts WHERE user_id = ? AND product_id = ?",
                [$userId, $productId]
            );
        }
        unset($_SESSION['cart'][$productId]);
        return true;
    }

    /**
     * Clear all
     */
    public function clear($userId = null) {
        if ($userId) {
            return $this->db->execute("DELETE FROM carts WHERE user_id = ?", [$userId]);
        }
        $_SESSION['cart'] = [];
        return true;
    }

    /**
     * Count items
     */
    public function count($userId = null) {
        if ($userId) {
            $row = $this->db->fetch(
                "SELECT COALESCE(SUM(quantity),0) AS c FROM carts WHERE user_id = ?",
                [$userId]
            );
            return $row['c'] ?? 0;
        }
        if (empty($_SESSION['cart'])) return 0;
        return array_sum(array_column($_SESSION['cart'], 'quantity'));
    }

    /**
     * Get total amount
     */
    public function getTotal($userId = null) {
        $items = $this->getItems($userId);
        $total = 0;
        foreach ($items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $total += $price * $item['quantity'];
        }
        return $total;
    }

    /**
     * Merge session cart to DB after login
     */
    public function mergeSessionToDb($userId) {
        if (empty($_SESSION['cart'])) return;
        foreach ($_SESSION['cart'] as $productId => $item) {
            $this->add($productId, $item['quantity'], $userId);
        }
        $_SESSION['cart'] = [];
    }
}