<?php
/**
 * Model Đơn Hàng — bảng: don_hang
 * Xử lý toàn bộ thao tác CSDL liên quan đến đơn hàng và thống kê doanh thu
 */

class DonHang {
    private $db;

    private static $validTransitions = [
        'pending'   => ['confirmed', 'cancelled'],
        'confirmed' => ['shipping', 'cancelled'],
        'shipping'  => ['delivered', 'cancelled'],
        'delivered' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct() {
        $this->db = db();
    }

    private function isValidTransition($currentStatus, $newStatus) {
        if ($currentStatus === $newStatus) return false;
        return in_array($newStatus, self::$validTransitions[$currentStatus] ?? []);
    }

    /** Tạo đơn hàng mới, trả về ID vừa tạo */
    public function create($data) {
        return $this->db->insert('don_hang', $data);
    }

    /** Thêm dòng sản phẩm vào bảng chi_tiet_don */
    public function addDetail($orderId, $product, $quantity) {
        return $this->db->insert('chi_tiet_don', [
            'id_don_hang'  => $orderId,
            'id_san_pham'  => $product['id_san_pham'] ?? $product['id'],
            'ten_san_pham' => $product['ten'] ?? $product['ten_san_pham'],
            'hinh_thu_nho' => $product['hinh_thu_nho'],
            'gia'          => $product['gia_khuyen_mai'] ?: $product['gia'],
            'so_luong'     => $quantity,
            'tam_tinh'     => ($product['gia_khuyen_mai'] ?: $product['gia']) * $quantity,
        ]);
    }

    /** Lấy đơn hàng theo ID (kèm tên khách + mã voucher) */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT o.*, u.ho_ten, u.thu_dien_tu, v.ma_phieu AS ma_phieu_voucher
             FROM don_hang o
             LEFT JOIN nguoi_dung u ON o.id_nguoi_dung = u.id
             LEFT JOIN phieu_giam_gia v ON o.id_phieu_giam = v.id
             WHERE o.id = ?",
            [$id]
        );
    }

    /** Lấy đơn hàng theo mã đơn (ma_don_hang) */
    public function getByCode($code) {
        return $this->db->fetch(
            "SELECT o.*, u.ho_ten, u.thu_dien_tu
             FROM don_hang o
             LEFT JOIN nguoi_dung u ON o.id_nguoi_dung = u.id
             WHERE o.ma_don_hang = ?",
            [$code]
        );
    }

    /** Lấy danh sách đơn hàng của một khách hàng, lọc theo trạng thái */
    public function getByUser($userId, $status = null) {
        $sql = "SELECT o.*, v.ma_phieu AS ma_phieu_voucher
                FROM don_hang o
                LEFT JOIN phieu_giam_gia v ON o.id_phieu_giam = v.id
                WHERE o.id_nguoi_dung = ?";
        $params = [$userId];
        if ($status) {
            $sql .= " AND o.trang_thai = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY o.ngay_tao DESC";
        return $this->db->fetchAll($sql, $params);
    }

    /** Lấy chi tiết sản phẩm trong đơn (kèm ảnh đầu từ anh_san_pham) */
    public function getDetails($orderId) {
        return $this->db->fetchAll(
            "SELECT od.*, p.duong_dan, p.hinh_thu_nho AS hinh_san_pham,
                    (SELECT duong_dan_anh FROM anh_san_pham
                     WHERE id_san_pham = od.id_san_pham
                     ORDER BY thu_tu ASC LIMIT 1) AS anh_dau
             FROM chi_tiet_don od
             LEFT JOIN san_pham p ON od.id_san_pham = p.id
             WHERE od.id_don_hang = ?",
            [$orderId]
        );
    }

    /** Cập nhật trạng thái đơn hàng. Nếu là trạng thái hoàn tất (delivered/completed), tự động cập nhật thanh_toan */
    public function updateStatus($orderId, $status) {
        $order = $this->getById($orderId);
        if (!$order || !$this->isValidTransition($order['trang_thai'], $status)) {
            return false;
        }

        $finalStatuses = ['delivered', 'completed', 'hoan_thanh', 'da_giao'];

        if (in_array($status, $finalStatuses)) {
            $this->db->execute(
                "UPDATE don_hang SET trang_thai = ?, trang_thai_thanh_toan = 'paid' WHERE id = ?",
                [$status, $orderId]
            );
            $this->db->execute(
                "UPDATE thanh_toan SET trang_thai = 'success', ngay_thanh_toan = NOW() WHERE id_don_hang = ?",
                [$orderId]
            );
            return true;
        }

        return $this->db->execute("UPDATE don_hang SET trang_thai = ? WHERE id = ?", [$status, $orderId]);
    }

    /** Hủy đơn hàng, lưu lý do hủy */
    public function cancel($orderId, $reason = '') {
        $order = $this->getById($orderId);
        if (!$order || !$this->isValidTransition($order['trang_thai'], 'cancelled')) {
            return false;
        }
        return $this->db->execute(
            "UPDATE don_hang SET trang_thai = 'cancelled', ly_do_huy = ? WHERE id = ?",
            [$reason, $orderId]
        );
    }

    /** Admin: lấy tất cả đơn, hỗ trợ tìm kiếm / lọc trạng thái / ngày */
    public function adminGetAll($search = '', $status = '', $date = '') {
        $sql = "SELECT o.*, u.ho_ten AS ten_khach
                FROM don_hang o
                LEFT JOIN nguoi_dung u ON o.id_nguoi_dung = u.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (o.ma_don_hang LIKE ? OR o.ten_giao_hang LIKE ? OR o.sdt_giao_hang LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($status) {
            $sql .= " AND o.trang_thai = ?";
            $params[] = $status;
        }
        if ($date) {
            $sql .= " AND DATE(o.ngay_tao) = ?";
            $params[] = $date;
        }

        $sql .= " ORDER BY o.ngay_tao DESC";
        return $this->db->fetchAll($sql, $params);
    }

    /** Thống kê tổng quan cho dashboard admin */
    public function getStats() {
        $stats = [];

        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(tong_tien), 0) AS tong
             FROM don_hang
             WHERE trang_thai IN ('completed','delivered')
             AND MONTH(ngay_tao) = MONTH(NOW())
             AND YEAR(ngay_tao) = YEAR(NOW())"
        );
        $stats['doanh_thu_thang'] = $row['tong'];

        $row = $this->db->fetch(
            "SELECT COUNT(*) AS tong FROM don_hang WHERE DATE(ngay_tao) = CURDATE()"
        );
        $stats['don_hang_hom_nay'] = $row['tong'];

        $row = $this->db->fetch("SELECT COUNT(*) AS tong FROM san_pham WHERE trang_thai='active'");
        $stats['tong_san_pham'] = $row['tong'];

        $row = $this->db->fetch(
            "SELECT COUNT(*) AS tong FROM san_pham WHERE trang_thai='active' AND ton_kho <= " . (int)LOW_STOCK_WARNING
        );
        $stats['sap_het_hang'] = $row['tong'];

        $row = $this->db->fetch("SELECT COUNT(*) AS tong FROM nguoi_dung");
        $stats['tong_khach_hang'] = $row['tong'];

        $statuses = ['pending','confirmed','shipping','delivered','completed'];
        foreach ($statuses as $s) {
            $row = $this->db->fetch("SELECT COUNT(*) AS c FROM don_hang WHERE trang_thai = ?", [$s]);
            $stats[$s . '_orders'] = $row['c'];
        }

        return $stats;
    }

    /** Doanh thu theo từng tháng trong năm */
    public function getRevenueByMonth($year = null) {
        $year = $year ?: date('Y');
        return $this->db->fetchAll(
            "SELECT MONTH(ngay_tao) AS thang, COALESCE(SUM(tong_tien),0) AS tong
             FROM don_hang
             WHERE trang_thai IN ('completed','delivered')
             AND YEAR(ngay_tao) = ?
             GROUP BY MONTH(ngay_tao)
             ORDER BY MONTH(ngay_tao)",
            [$year]
        );
    }

    /** Doanh thu 7 tuần gần nhất */
    public function getRevenueByWeek() {
        return $this->db->fetchAll(
            "SELECT WEEK(ngay_tao, 1) AS tuan, COALESCE(SUM(tong_tien),0) AS tong
             FROM don_hang
             WHERE trang_thai IN ('completed','delivered')
             AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 7 WEEK)
             GROUP BY WEEK(ngay_tao, 1)
             ORDER BY WEEK(ngay_tao, 1)"
        );
    }

    /** Tổng hợp doanh thu: hôm nay / tuần / tháng / năm */
    public function getRevenueSummary() {
        $today = $this->db->fetch(
            "SELECT COALESCE(SUM(tong_tien),0) AS tong
             FROM don_hang
             WHERE trang_thai IN ('completed','delivered') AND DATE(ngay_tao) = CURDATE()"
        );
        $week = $this->db->fetch(
            "SELECT COALESCE(SUM(tong_tien),0) AS tong
             FROM don_hang
             WHERE trang_thai IN ('completed','delivered')
             AND YEARWEEK(ngay_tao, 1) = YEARWEEK(NOW(), 1)"
        );
        $month = $this->db->fetch(
            "SELECT COALESCE(SUM(tong_tien),0) AS tong
             FROM don_hang
             WHERE trang_thai IN ('completed','delivered')
             AND MONTH(ngay_tao) = MONTH(NOW())
             AND YEAR(ngay_tao) = YEAR(NOW())"
        );
        $year = $this->db->fetch(
            "SELECT COALESCE(SUM(tong_tien),0) AS tong
             FROM don_hang
             WHERE trang_thai IN ('completed','delivered')
             AND YEAR(ngay_tao) = YEAR(NOW())"
        );

        return [
            'hom_nay' => $today['tong'],
            'tuan'    => $week['tong'],
            'thang'   => $month['tong'],
            'nam'     => $year['tong'],
        ];
    }

    /** Lấy sản phẩm bán chạy nhất */
    public function getBestSellers($limit = 5) {
        return $this->db->fetchAll(
            "SELECT p.id, p.ten, p.hinh_thu_nho, p.gia, p.so_luong_ban
             FROM san_pham p
             WHERE p.trang_thai = 'active'
             ORDER BY p.so_luong_ban DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm bán chậm nhất */
    public function getWorstSellers($limit = 5) {
        return $this->db->fetchAll(
            "SELECT id, ten, hinh_thu_nho, gia, ton_kho, so_luong_ban
             FROM san_pham
             WHERE trang_thai = 'active'
             ORDER BY so_luong_ban ASC, ngay_tao DESC
             LIMIT " . (int)$limit
        );
    }

    /** Lấy sản phẩm của nhiều đơn cùng lúc, kết quả nhóm theo id_don_hang */
    public function getItemsByOrderIds(array $ids) {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->fetchAll(
            "SELECT od.*, p.duong_dan
             FROM chi_tiet_don od
             LEFT JOIN san_pham p ON od.id_san_pham = p.id
             WHERE od.id_don_hang IN ($placeholders)",
            $ids
        );
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['id_don_hang']][] = $row;
        }
        return $grouped;
    }

    /** Lấy đơn hàng gần nhất cho dashboard */
    public function getRecent($limit = 10) {
        return $this->db->fetchAll(
            "SELECT o.*, u.ho_ten
             FROM don_hang o
             LEFT JOIN nguoi_dung u ON o.id_nguoi_dung = u.id
             ORDER BY o.ngay_tao DESC
             LIMIT " . (int)$limit
        );
    }
}
