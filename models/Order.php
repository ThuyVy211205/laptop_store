<?php
/**
 * Order Model
 */

class Order {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * Create new order
     */
    public function create($data) {
        return $this->db->insert('orders', $data);
    }

    /**
     * Add order detail (line item)
     */
    public function addDetail($orderId, $product, $quantity) {
        return $this->db->insert('order_details', [
            'order_id'     => $orderId,
            'product_id'   => $product['id'],
            'product_name' => $product['name'],
            'thumbnail'    => $product['thumbnail'],
            'price'        => $product['sale_price'] ?: $product['price'],
            'quantity'     => $quantity,
            'subtotal'     => ($product['sale_price'] ?: $product['price']) * $quantity,
        ]);
    }

    /**
     * Get order by ID
     */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT o.*, u.full_name, u.email
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE o.id = ?",
            [$id]
        );
    }

    /**
     * Get order by code
     */
    public function getByCode($code) {
        return $this->db->fetch(
            "SELECT o.*, u.full_name, u.email
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE o.order_code = ?",
            [$code]
        );
    }

    /**
     * Get orders by user
     */
    public function getByUser($userId, $status = null) {
        $sql = "SELECT * FROM orders WHERE user_id = ?";
        $params = [$userId];
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get order details
     */
    public function getDetails($orderId) {
        return $this->db->fetchAll(
            "SELECT od.*, p.slug
             FROM order_details od
             LEFT JOIN products p ON od.product_id = p.id
             WHERE od.order_id = ?",
            [$orderId]
        );
    }

    /**
     * Update order status
     */
    public function updateStatus($orderId, $status) {
        return $this->db->execute("UPDATE orders SET status = ? WHERE id = ?", [$status, $orderId]);
    }

    /**
     * Cancel order
     */
    public function cancel($orderId, $reason = '') {
        return $this->db->execute(
            "UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE id = ?",
            [$reason, $orderId]
        );
    }

    /**
     * Admin: get all orders with filters
     */
    public function adminGetAll($search = '', $status = '', $date = '') {
        $sql = "SELECT o.*, u.full_name AS user_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (o.order_code LIKE ? OR o.shipping_name LIKE ? OR o.shipping_phone LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }
        if ($date) {
            $sql .= " AND DATE(o.created_at) = ?";
            $params[] = $date;
        }

        $sql .= " ORDER BY o.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get dashboard statistics
     */
    public function getStats() {
        $stats = [];

        // Revenue this month
        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount), 0) AS total
             FROM orders
             WHERE status IN ('completed','delivered')
             AND MONTH(created_at) = MONTH(NOW())
             AND YEAR(created_at) = YEAR(NOW())"
        );
        $stats['revenue_month'] = $row['total'];

        // Orders today
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS total FROM orders WHERE DATE(created_at) = CURDATE()"
        );
        $stats['orders_today'] = $row['total'];

        // Total products & low stock
        $row = $this->db->fetch("SELECT COUNT(*) AS total FROM products WHERE status='active'");
        $stats['total_products'] = $row['total'];

        $row = $this->db->fetch(
            "SELECT COUNT(*) AS total FROM products WHERE status='active' AND stock <= " . (int)LOW_STOCK_WARNING
        );
        $stats['low_stock'] = $row['total'];

        // Total customers
        $row = $this->db->fetch("SELECT COUNT(*) AS total FROM users");
        $stats['total_customers'] = $row['total'];

        // Order status counts
        $statuses = ['pending','confirmed','shipping','delivered','completed'];
        foreach ($statuses as $s) {
            $row = $this->db->fetch("SELECT COUNT(*) AS c FROM orders WHERE status = ?", [$s]);
            $stats[$s . '_orders'] = $row['c'];
        }

        return $stats;
    }

    /**
     * Revenue by month (current year)
     */
    public function getRevenueByMonth($year = null) {
        $year = $year ?: date('Y');
        return $this->db->fetchAll(
            "SELECT MONTH(created_at) AS month, COALESCE(SUM(total_amount),0) AS total
             FROM orders
             WHERE status IN ('completed','delivered')
             AND YEAR(created_at) = ?
             GROUP BY MONTH(created_at)
             ORDER BY MONTH(created_at)",
            [$year]
        );
    }

    /**
     * Revenue by week (last 7 weeks)
     */
    public function getRevenueByWeek() {
        return $this->db->fetchAll(
            "SELECT WEEK(created_at, 1) AS week, COALESCE(SUM(total_amount),0) AS total
             FROM orders
             WHERE status IN ('completed','delivered')
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 WEEK)
             GROUP BY WEEK(created_at, 1)
             ORDER BY WEEK(created_at, 1)"
        );
    }

    /**
     * Get revenue summary for dashboard
     */
    public function getRevenueSummary() {
        $today = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) AS total
             FROM orders
             WHERE status IN ('completed','delivered') AND DATE(created_at) = CURDATE()"
        );
        $week = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) AS total
             FROM orders
             WHERE status IN ('completed','delivered')
             AND YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)"
        );
        $month = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) AS total
             FROM orders
             WHERE status IN ('completed','delivered')
             AND MONTH(created_at) = MONTH(NOW())
             AND YEAR(created_at) = YEAR(NOW())"
        );
        $year = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) AS total
             FROM orders
             WHERE status IN ('completed','delivered')
             AND YEAR(created_at) = YEAR(NOW())"
        );

        return [
            'today' => $today['total'],
            'week'  => $week['total'],
            'month' => $month['total'],
            'year'  => $year['total'],
        ];
    }

    /**
     * Get best selling products
     */
    public function getBestSellers($limit = 5) {
        return $this->db->fetchAll(
            "SELECT p.id, p.name, p.thumbnail, p.price, p.sold_quantity
             FROM products p
             WHERE p.status = 'active'
             ORDER BY p.sold_quantity DESC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Get worst sellers (for revenue page)
     */
    public function getWorstSellers($limit = 5) {
        return $this->db->fetchAll(
            "SELECT id, name, thumbnail, price, stock, sold_quantity
             FROM products
             WHERE status = 'active'
             ORDER BY sold_quantity ASC, created_at DESC
             LIMIT " . (int)$limit
        );
    }

    /**
     * Get recent orders for dashboard
     */
    public function getRecent($limit = 10) {
        return $this->db->fetchAll(
            "SELECT o.*, u.full_name
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC
             LIMIT " . (int)$limit
        );
    }
}