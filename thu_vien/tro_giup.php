<?php
/**
 * Các hàm hỗ trợ xác thực và người dùng
 * Kiểm tra đăng nhập, lấy dữ liệu phiên, bảo vệ route
 */

/** Kiểm tra người dùng đã đăng nhập chưa */
function isLoggedIn() {
    return isset($_SESSION['id_nguoi_dung']) && !empty($_SESSION['id_nguoi_dung']);
}

/** Kiểm tra admin đã đăng nhập chưa */
function isAdminLoggedIn() {
    return !empty($_SESSION['admin']['id']);
}

/** Lấy thông tin người dùng đang đăng nhập (có cache static) */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    static $cached = null;
    if ($cached !== null) return $cached;

    require_once ROOT_PATH . '/models/NguoiDung.php';
    $nguoiDungModel = new NguoiDung();
    $cached = $nguoiDungModel->getById($_SESSION['id_nguoi_dung']);
    return $cached;
}

/** Lấy thông tin admin đang đăng nhập từ session */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) return null;
    return $_SESSION['admin'];
}

/** Lấy tổng số lượng sản phẩm trong giỏ hàng (DB hoặc session) */
function getCartCount() {
    if (isLoggedIn()) {
        require_once ROOT_PATH . '/models/GioHang.php';
        $gioHang = new GioHang();
        return $gioHang->count($_SESSION['id_nguoi_dung']);
    } else {
        return isset($_SESSION['cart'])
            ? array_sum(array_column($_SESSION['cart'], 'so_luong'))
            : 0;
    }
}

/** Lấy số sản phẩm trong danh sách yêu thích của người dùng */
function getWishlistCount() {
    if (!isLoggedIn()) return 0;
    $row = db()->fetch(
        "SELECT COUNT(*) AS c FROM yeu_thich WHERE id_nguoi_dung = ?",
        [$_SESSION['id_nguoi_dung']]
    );
    return $row['c'] ?? 0;
}

/** Lấy danh sách thông báo của người dùng */
function getUserNotifications($userId, $limit = 10) {
    return db()->fetchAll(
        "SELECT * FROM thong_bao WHERE id_nguoi_dung = ? ORDER BY ngay_tao DESC LIMIT ?",
        [$userId, $limit]
    );
}

/** Đếm số thông báo chưa đọc của người dùng */
function getUnreadCount($userId) {
    $row = db()->fetch(
        "SELECT COUNT(*) AS c FROM thong_bao WHERE id_nguoi_dung = ? AND da_doc = 0",
        [$userId]
    );
    return $row['c'] ?? 0;
}

/** Yêu cầu đăng nhập — chuyển hướng đến trang login nếu chưa đăng nhập */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Vui lòng đăng nhập để tiếp tục');
        redirect('/auth/login');
    }
}

/** Yêu cầu đăng nhập admin — chuyển hướng nếu không có quyền */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('/admin/login');
    }
}

/** Lấy thông tin hạng thành viên: tên, màu sắc, biểu tượng (silver/gold/diamond) */
function getUserRankInfo($rank) {
    $ranks = [
        'silver'  => ['name' => 'Silver',  'color' => '#94a3b8', 'icon' => '⭐'],
        'gold'    => ['name' => 'Gold',    'color' => '#f59e0b', 'icon' => '🥇'],
        'diamond' => ['name' => 'Diamond', 'color' => '#06b6d4', 'icon' => '💎'],
    ];
    return $ranks[$rank] ?? $ranks['silver'];
}

/** Lấy danh sách ID sản phẩm được gắn cờ Mới (la_moi = 1, cache static) */
function getNewProductIds() {
    static $ids = null;
    if ($ids === null) {
        $rows = db()->fetchAll(
            "SELECT id FROM san_pham WHERE trang_thai = 'active' AND la_moi = 1 ORDER BY id DESC"
        );
        $ids = array_column($rows, 'id');
    }
    return $ids;
}