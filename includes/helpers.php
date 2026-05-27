<?php
/**
 * Auth & User helpers
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Get current logged user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    static $cached = null;
    if ($cached !== null) return $cached;

    require_once ROOT_PATH . '/models/User.php';
    $userModel = new User();
    $cached = $userModel->getById($_SESSION['user_id']);
    return $cached;
}

/**
 * Get current admin
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) return null;
    return [
        'id'        => $_SESSION['admin_id'],
        'full_name' => $_SESSION['admin_name'] ?? 'Admin',
        'email'     => $_SESSION['admin_email'] ?? '',
        'role'      => $_SESSION['admin_role'] ?? 'admin',
    ];
}

/**
 * Get cart item count
 */
function getCartCount() {
    if (isLoggedIn()) {
        require_once ROOT_PATH . '/models/Cart.php';
        $cart = new Cart();
        return $cart->count($_SESSION['user_id']);
    } else {
        return isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
    }
}

/**
 * Get wishlist count
 */
function getWishlistCount() {
    if (!isLoggedIn()) return 0;
    $row = db()->fetch("SELECT COUNT(*) AS c FROM wishlists WHERE user_id = ?", [$_SESSION['user_id']]);
    return $row['c'] ?? 0;
}

/**
 * Get user notifications
 */
function getUserNotifications($userId, $limit = 10) {
    return db()->fetchAll(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
        [$userId, $limit]
    );
}

/**
 * Get unread notification count
 */
function getUnreadCount($userId) {
    $row = db()->fetch(
        "SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0",
        [$userId]
    );
    return $row['c'] ?? 0;
}

/**
 * Require login (redirect if not)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Vui lòng đăng nhập để tiếp tục');
        redirect('/auth/login');
    }
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('/admin/auth/login');
    }
}

/**
 * Get user rank info
 */
function getUserRankInfo($rank) {
    $ranks = [
        'silver'  => ['name' => 'Silver',  'color' => '#94a3b8', 'icon' => '⭐'],
        'gold'    => ['name' => 'Gold',    'color' => '#f59e0b', 'icon' => '🥇'],
        'diamond' => ['name' => 'Diamond', 'color' => '#06b6d4', 'icon' => '💎'],
    ];
    return $ranks[$rank] ?? $ranks['silver'];
}