<?php
/**
 * Model Đơn Hàng — bảng: don_hang
 * Xử lý toàn bộ thao tác CSDL liên quan đến đơn hàng và thống kê doanh thu
 */

class Order {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /** Tạo đơn hàng mới, trả về ID vừa tạo */
    public function create($data) {
        return $this->db->insert('don_hang', $data);
    }

    /** Thêm dòng sản phẩm vào bảng chi_tiet_don */
    public function addDetail($orderId, $product, $quantity) {
        return $this->db->insert('chi_tiet_don', [
            'order_id'     => $orderId,
            'product_id'   => $product['product_id'] ?? $product['id'],
            'product_name' => $product['name'],
            'thumbnail'    => $product['thumbnail'],
            'price'        => $product['sale_price'] ?: $product['price'],
            'quantity'     => $quantity,
            'subtotal'     => ($product['sale_price'] ?: $product['price']) * $quantity,
        ]);
    }

    /** Lấy đơn hàng theo ID (kèm tên khách + mã voucher) */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT o.*, u.full_name, u.email, v.code AS voucher_code
             FROM don_hang o
             LEFT JOIN nguoi_dung u ON o.user_id = u.id
             LEFT JOIN phieu_giam_gia v ON o.voucher_id = v.id
             WHERE o.id = ?",
            [$id]
        );
    }

    /** Lấy đơn hàng theo mã đơn (order_code) */
    public function getByCode($code) {
        return $this->db->fetch(
            "SELECT o.*, u.full_name, u.email
             FROM don_hang o
             LEFT JOIN nguoi_dung u ON o.user_id = u.id
             WHERE o.order_code = ?",
            [$code]
        );
    }

    /** Lấy danh sách đơn hàng của một khách hàng, lọc theo trạng thái */
    public function getByUser($userId, $status = null) {
        $sql = "SELECT o.*, v.code AS voucher_code
                FROM don_hang o
                LEFT JOIN phieu_giam_gia v ON o.voucher_id = v.id
                WHERE o.user_id = ?";
        $params = [$userId];
        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY o.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    /** Lấy chi tiết sản phẩm trong đơn (kèm ảnh đầu từ anh_san_pham) */
    public function getDetails($orderId) {
        return $this->db->fetchAll(
            "SELECT od.*, p.slug, p.thumbnail AS product_thumbnail,
                    (SELECT image_path FROM anh_san_pham
                     WHERE product_id = od.product_id
                     ORDER BY sort_order ASC LIMIT 1) AS first_image
             FROM chi_tiet_don od
             LEFT JOIN san_pham p ON od.product_id = p.id
             WHERE od.order_id = ?",
            [$orderId]
        );
    }

    /** Cập nhật trạng thái đơn hàng */
    public function updateStatus($orderId, $status) {
        return $this->db->execute("UPDATE don_hang SET status = ? WHERE id = ?", [$status, $orderId]);
    }

    /** Hủy đơn hàng, lưu lý do hủy */
    public function cancel($orderId, $reason = '') {
        return $this->db->execute(
            "UPDATE don_hang SET status = 'cancelled', cancel_reason = ? WHERE id = ?",
            [$reason, $orderId]
        );
    }

    /** Admin: lấy tất cả đơn, hỗ trợ tìm kiếm / lọc trạng thái / ngày */
    public function adminGetAll($search = '', $status = '', $date = '') {
        $sql = "SELECT o.*, u.full_name AS user_name
                FROM don_hang o
                LEFT JOIN nguoi_dung u ON o.user_id = u.id
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

    /** Thống kê tổng quan cho dashboard admin */
    public function getStats() {
        $stats = [];

        // Doanh thu tháng này
        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount), 0) AS total
             FROM don_hang
             WHERE status IN ('completed','delivered')
             AND MONTH(created_at) = MONTH(NOW())
             AND YEAR(created_at) = YEAR(NOW())"
        );
        $stats['revenue_month'] = $row['total'];

        // Số đơn hàng hôm nay
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS total FROM don_hang WHERE DATE(created_at) = CURDATE()"
        );
        $stats['orders_today'] = $row['total'];

        // Tổng sản phẩm và số sản phẩm sắp hết hàng
        $row = $this->db->fetch("SELECT COUNT(*) AS total FROM san_pham WHERE status='active'");
        $stats['total_products'] = $row['total'];

        $row = $this->db->fetch(
            "SELECT COUNT(*) AS total FROM san_pham WHERE status='active' AND stock <= " . (int)LOW_STOCK_WARNING
        );
        $stats['low_stock'] = $row['total'];

        // Tổng số khách hàng đã đăng ký
        $row = $this->db->fetch("SELECT COUNT(*) AS total FROM nguoi_dung");
        $stats['total_customers'] = $row['total'];

        // Số đơn theo từng trạng thái
        $statuses = ['pending','confirmed','shipping','delivered','completed'];
        foreach ($statuses as $s) {
            $row = $this->db->fetch("SELECT COUNT(*) AS c FROM don_hang WHERE status = ?", [$s]);
            $stats[$s . '_orders'] = $row['c'];
        }

        return $stats;
    }

    /** Doanh thu theo từng tháng trong năm */
    public function getRevenueByMonth($year = null) {
        $year = $year ?: date('Y');
        return $this->db->fetchAll(
            "SELECT MONTH(created_at) AS month, COALESCE(SUM(total_amount),0) AS total
             FROM don_hang
             WHERE status IN ('completed','delivered')
             AND YEAR(created_at) = ?
             GROUP BY MONTH(created_at)
             ORDER BY MONTH(created_at)",
            [$year]
        );
    }

    /** Doanh thu 7 tuần gần nhất */
    public function getRevenueByWeek() {
        return $this->db->fetchAll(
            "SELECT WEEK(created_at, 1) AS week, COALESCE(SUM(total_amount),0) AS total
             FROM don_hang
             WHERE status IN ('completed','delivered')
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 WEEK)
             GROUP BY WEEK(created_at, 1)
             ORDER BY WEEK(created_at, 1)"
        );
    }

    /** Tổng hợp doanh thu: hôm nay / tuần / tháng / năm */
    public function getRevenueSummary() {
        $today = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) AS total
             FROM don_hang
             WHERE status IN ('completed','delivered') AND DATE(created_at) = CURDATE()"
        );
        $week = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) AS total
             FROM don_hang
             WHERE status IN ('completed','delivered')
             AND YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)"
        );
        $month = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) AS total
             FROM don_hang
             WHERE status IN ('completed','delivered')
             AND MONTH(created_at) = MONTH(NOW())
             AND YEAR(created_at) = YEAR(NOW())"
        );
        $year = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) AS total
             FROM don_hang
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

    /** Lấy sản phẩm bán chạy nhất */
    public function getBestSellers($limit = 5) {
        return $this->db->fetchAll(
            "SELECT p.id, p.name, p.thumbnail, p.price, p.sold_quantity
             FROM san_pham p
             WHERE p.status = 'active'
             ORDER BY p.sold_quantity DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm bán chậm nhất */
    public function getWorstSellers($limit = 5) {
        return $this->db->fetchAll(
            "SELECT id, name, thumbnail, price, stock, sold_quantity
             FROM san_pham
             WHERE status = 'active'
             ORDER BY sold_quantity ASC, created_at DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm của nhiều đơn cùng lúc, kết quả nhóm theo order_id */
    public function getItemsByOrderIds(array $ids) {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->fetchAll(
            "SELECT od.*, p.slug
             FROM chi_tiet_don od
             LEFT JOIN san_pham p ON od.product_id = p.id
             WHERE od.order_id IN ($placeholders)",
            $ids
        );
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['order_id']][] = $row;
        }
        return $grouped;
    }

    /** Lấy đơn hàng gần nhất cho dashboard */
    public function getRecent($limit = 10) {
        return $this->db->fetchAll(
            "SELECT o.*, u.full_name
             FROM don_hang o
             LEFT JOIN nguoi_dung u ON o.user_id = u.id
             ORDER BY o.created_at DESC
             LIMIT " . (int)$limit
        );
    }
}