<?php
/**
 * Quản lý phiên đăng nhập (Session)
 * Bảo mật: cookie httponly, CSRF token, tái tạo session ID định kỳ
 */

// Cấu hình bảo mật cho session (httponly, samesite)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Tái tạo session ID định kỳ — tránh session fixation
// Dùng false (không xóa session cũ ngay) để tránh race condition khi mở nhiều tab
if (!isset($_SESSION['_init'])) {
    session_regenerate_id(false);
    $_SESSION['_init'] = time();
} elseif (time() - $_SESSION['_init'] > 1800) {
    session_regenerate_id(false);
    $_SESSION['_init'] = time();
}

// Khởi tạo CSRF token nếu chưa có
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfToken() {
    return $_SESSION['csrf_token'] ?? '';
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Khởi tạo session giỏ hàng cho khách
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}