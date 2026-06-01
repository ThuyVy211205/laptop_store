<?php
/**
 * Common utility functions
 */

/**
 * Format price to VND
 */
function formatPrice($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

/**
 * Calculate discount percentage
 */
function calcDiscount($original, $sale) {
    if ($original <= 0 || $sale <= 0 || $sale >= $original) return 0;
    return round((($original - $sale) / $original) * 100);
}

/**
 * Format date dd/mm/yyyy
 */
function formatDate($date) {
    if (!$date) return '';
    return date('d/m/Y', strtotime($date));
}

/**
 * Format datetime dd/mm/yyyy H:i
 */
function formatDateTime($datetime) {
    if (!$datetime) return '';
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Create URL-friendly slug from Vietnamese text
 */
function createSlug($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $accents = [
        'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
        'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y',
        'đ'=>'d',
    ];
    $text = strtr($text, $accents);
    $text = preg_replace('/[^a-z0-9-]+/i', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Truncate text
 */
function truncate($text, $length = 100) {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Generate order code
 */
function generateOrderCode() {
    return 'TS' . date('ymd') . strtoupper(substr(uniqid(), -5));
}

/**
 * Redirect helper
 */
function redirect($url) {
    if (strpos($url, 'http') !== 0) {
        $url = SITE_URL . '/' . ltrim($url, '/');
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Flash messages
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Vietnamese phone number
 */
function isValidPhone($phone) {
    return preg_match('/^(0|\+84)[3-9]\d{8}$/', $phone);
}

/**
 * Upload image
 */
function uploadImage($file, $folder = 'products') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) return null;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) return null;

    if ($file['size'] > MAX_UPLOAD_SIZE) return null;

    $uploadDir = UPLOAD_PATH . '/' . $folder;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($ext);
    $filepath = $uploadDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $folder . '/' . $filename;
    }
    return null;
}

/**
 * Placeholder image (SVG data URI — không cần file vật lý)
 */
function noImageUrl() {
    return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E"
         . "%3Crect fill='%23f3f4f6' width='400' height='300'/%3E"
         . "%3Ctext fill='%23adb5bd' font-family='sans-serif' font-size='16' text-anchor='middle' x='200' y='158'%3ENo Image%3C/text%3E"
         . "%3C/svg%3E";
}

/**
 * Get image URL (with fallback)
 */
function imgUrl($path) {
    if (empty($path)) return noImageUrl();
    if (strpos($path, 'http') === 0) return $path;
    if (strpos($path, 'data:') === 0) return $path;
    if (strpos($path, 'assets/') === 0) return SITE_URL . '/' . ltrim($path, '/');
    return UPLOAD_URL . '/' . ltrim($path, '/');
}

/**
 * Star rating HTML
 */
function starRating($rating, $size = '') {
    $html = '<div class="star-rating ' . $size . '">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="fas fa-star"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * Time ago
 */
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    if ($diff < 2592000) return floor($diff / 86400) . ' ngày trước';
    return formatDate($datetime);
}

/**
 * Simple paginate helper
 */
function paginate($total, $perPage, $currentPage, $baseUrl) {
    $totalPages = ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<ul class="pagination justify-content-center">';

    // Previous
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '"><i class="fas fa-chevron-left"></i></a></li>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }

    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '"><i class="fas fa-chevron-right"></i></a></li>';
    }

    $html .= '</ul>';
    return $html;
}
