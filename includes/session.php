<?php
/**
 * Session management
 */

// Set secure session params
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Regenerate ID periodically to prevent fixation
if (!isset($_SESSION['_init'])) {
    session_regenerate_id(true);
    $_SESSION['_init'] = time();
} elseif (time() - $_SESSION['_init'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['_init'] = time();
}

// CSRF Token
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

// Initialize cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}