<?php
/**
 * Các hàm tiện ích dùng chung cho toàn dự án
 * Định dạng, validate, upload, email, phân trang, slug...
 */

/** Định dạng số tiền sang VND (ví dụ: 1.500.000đ) */
function formatPrice($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

/** Tính phần trăm giảm giá giữa giá gốc và giá sale */
function calcDiscount($original, $sale) {
    if ($original <= 0 || $sale <= 0 || $sale >= $original) return 0;
    return round((($original - $sale) / $original) * 100);
}

/** Định dạng ngày: dd/mm/yyyy */
function formatDate($date) {
    if (!$date) return '';
    return date('d/m/Y', strtotime($date));
}

/** Định dạng ngày giờ: dd/mm/yyyy H:i */
function formatDateTime($datetime) {
    if (!$datetime) return '';
    return date('d/m/Y H:i', strtotime($datetime));
}

/** Tạo slug thân thiện URL từ văn bản tiếng Việt (bỏ dấu, thay khoảng trắng bằng '-') */
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

/** Cắt ngắn chuỗi văn bản theo số ký tự, thêm '...' nếu bị cắt */
function truncate($text, $length = 100) {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

/** Tạo mã đơn hàng ngẫu nhiên dạng TSyymmddhh + 5 ký tự */
function generateOrderCode() {
    return 'TS' . date('ymd') . strtoupper(substr(uniqid(), -5));
}

/** Chuyển hướng trang — hỗ trợ cả URL tương đối và tuyệt đối */
function redirect($url) {
    if (strpos($url, 'http') !== 0) {
        $url = SITE_URL . '/' . ltrim($url, '/');
    }
    header('Location: ' . $url);
    exit;
}

/** Thông báo tạm thời (flash message) — lưu/đọc từ session */
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

/** Làm sạch dữ liệu đầu vào (htmlspecialchars + trim), hỗ trợ cả mảng */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/** Kiểm tra email hợp lệ (chỉ chấp nhận @gmail.com) */
function isValidEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    return (bool) preg_match('/^[^@\s]+@gmail\.com$/i', $email);
}

/** Kiểm tra số điện thoại Việt Nam hợp lệ — đầu số 03x/05x/07x/08x/09x, đủ 10 số */
function isValidPhone($phone) {
    return preg_match('/^(0[35789]\d{8}|\+84[35789]\d{8})$/', $phone);
}

/** Tải ảnh lên server — kiểm tra loại file, kích thước và lưu vào tai_len/ */
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

/** Lấy URL ảnh đầy đủ — fallback về SVG placeholder nếu đường dẫn trống */
function imgUrl($path) {
    if (empty($path)) return noImageUrl();
    if (strpos($path, 'http') === 0) return $path;
    if (strpos($path, 'data:') === 0) return $path;
    if (strpos($path, 'assets/') === 0) return SITE_URL . '/' . ltrim($path, '/');
    return UPLOAD_URL . '/' . ltrim($path, '/');
}

/** Tạo HTML hiển thị sao đánh giá (★) từ điểm số thập phân */
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

/** Hiển thị thời gian tương đối: 'Vừa xong', 'X phút trước', 'X giờ trước'... */
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    if ($diff < 2592000) return floor($diff / 86400) . ' ngày trước';
    return formatDate($datetime);
}

/** Tạo HTML phân trang Bootstrap — trả về chuỗi rỗng nếu chỉ có 1 trang */
function paginate($total, $perPage, $currentPage, $baseUrl) {
    $totalPages = ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<ul class="pagination justify-content-center">';

    // Nút trang trước
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

/**
 * Gửi email marketing (admin → khách) qua tài khoản SMTP_USER (vqstore296).
 */
function sendMail($toEmail, $toName, $subject, $htmlBody, &$error = '')
{
    require_once ROOT_PATH . '/ho_tro/GuiThu.php';
    return Mailer::send($toEmail, $toName, $subject, $htmlBody, $error, SMTP_USER, SMTP_PASS);
}

/**
 * Gửi email tự động (xác nhận đơn hàng, trạng thái...) qua tài khoản SMTP_NOREPLY_USER.
 */
function sendOrderMail($toEmail, $toName, $subject, $htmlBody, &$error = '', array $inlineImages = [])
{
    require_once ROOT_PATH . '/ho_tro/GuiThu.php';
    return Mailer::send($toEmail, $toName, $subject, $htmlBody, $error, SMTP_NOREPLY_USER, SMTP_NOREPLY_PASS, $inlineImages);
}

/**
 * Xây dựng HTML email xác nhận đơn hàng (format giống BKshop).
 */
function buildOrderConfirmEmail(array $order, array $items)
{
    $payLabels = [
        'cod'           => 'Thanh toán khi nhận hàng (COD)',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo'          => 'Ví MoMo',
        'vnpay'         => 'VNPay',
    ];
    $payLabel  = $payLabels[$order['phuong_thuc_thanh_toan']] ?? $order['phuong_thuc_thanh_toan'];
    $orderLink = SITE_URL . '/order/detail/' . $order['id'];

    // --- Rows sản phẩm (layout mobile-friendly: ảnh trái, thông tin phải) ---
    $dbInst   = db();
    $rows     = '';
    $cidImages = [];   // danh sách ảnh CID để gửi kèm email

    foreach ($items as $idx => $item) {
        $unitPrice = ($item['gia_khuyen_mai'] ?? 0) ?: ($item['gia'] ?? 0);
        $lineTotal = $unitPrice * ($item['so_luong'] ?? 1);

        // Lấy ảnh: ưu tiên Product Thumbnails 1 → thumbnail sản phẩm
        $thumb = $item['hinh_thu_nho'] ?? '';
        $pid   = $item['id_san_pham'] ?? ($item['id'] ?? null);
        if ($pid) {
            $firstImg = $dbInst->fetch(
                "SELECT duong_dan_anh FROM anh_san_pham WHERE id_san_pham = ? ORDER BY thu_tu ASC LIMIT 1",
                [$pid]
            );
            if ($firstImg) $thumb = $firstImg['duong_dan_anh'];
        }

        // Xác định đường dẫn file thật
        $filePath = '';
        if ($thumb) {
            $filePath = strpos($thumb, 'assets/') === 0
                ? ROOT_PATH . '/' . $thumb
                : UPLOAD_PATH . '/' . ltrim($thumb, '/');
        }

        // Dùng CID inline (Gmail hỗ trợ, không bị chặn như URL localhost hay data:URI)
        if ($filePath && file_exists($filePath)) {
            $cid      = 'product_img_' . $idx . '_' . ($pid ?: 0) . '@vqstore';
            $mime     = mime_content_type($filePath) ?: 'image/jpeg';
            $cidImages[] = ['cid' => $cid, 'path' => $filePath, 'mime' => $mime];
            $imgTag   = '<img src="cid:' . $cid . '" width="70" height="70"
                             style="object-fit:cover;border-radius:8px;display:block;border:1px solid #e5e7eb;"
                             alt="' . htmlspecialchars($item['ten'] ?? $item['ten_san_pham'] ?? '') . '">';
        } else {
            $imgTag = '<div style="width:70px;height:70px;background:#f3f4f6;border-radius:8px;
                           display:flex;align-items:center;justify-content:center;font-size:28px;
                           border:1px solid #e5e7eb;">🖥️</div>';
        }

        $itemName = $item['ten'] ?? $item['ten_san_pham'] ?? '';
        $rows .= '
        <!-- Sản phẩm: ' . htmlspecialchars($itemName) . ' -->
        <tr>
          <td style="padding:14px 12px;border-bottom:1px solid #f1f5f9;vertical-align:top;width:82px;">
            ' . $imgTag . '
          </td>
          <td style="padding:14px 12px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
            <div style="font-size:13.5px;font-weight:600;color:#1e2a3b;margin-bottom:6px;line-height:1.4;">
              ' . htmlspecialchars($itemName) . '
            </div>
            <div style="font-size:12.5px;color:#9ca3af;">
              Số lượng: <strong style="color:#374151;">' . ($item['so_luong'] ?? 1) . '</strong>
              &nbsp;|&nbsp;
              Đơn giá: <strong style="color:#374151;">' . number_format($unitPrice, 0, ',', '.') . 'đ</strong>
            </div>
            <div style="font-size:14px;font-weight:700;color:#1d4ed8;margin-top:6px;">
              Thành tiền: ' . number_format($lineTotal, 0, ',', '.') . 'đ
            </div>
          </td>
        </tr>';
    }

    // --- Tổng tiền ---
    $subtotal      = $order['tam_tinh']   ?? $order['tong_tien'];
    $discount      = $order['so_tien_giam'] ?? 0;
    $total         = $order['tong_tien'];

    $discountRow = $discount > 0 ? '
        <tr>
            <td style="padding:7px 12px;text-align:right;font-size:13px;color:#6b7280;">Giảm giá (voucher):</td>
            <td style="padding:7px 12px;text-align:right;font-size:13px;color:#16a34a;font-weight:600;white-space:nowrap;">
                -' . number_format($discount, 0, ',', '.') . 'đ</td>
        </tr>' : '';

    $noteHtml = !empty($order['ghi_chu'])
        ? '<p style="margin:6px 0 0;font-size:13px;color:#6b7280;"><strong>Ghi chú:</strong> ' . htmlspecialchars($order['ghi_chu']) . '</p>'
        : '';

    $html = '<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Xác nhận đơn hàng #' . $order['ma_don_hang'] . '</title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Inter,Arial,sans-serif;">
<div style="max-width:620px;margin:28px auto;background:#fff;border-radius:14px;
            overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.10);">

  <!-- Header -->
  <div style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);padding:32px;text-align:center;">
    <div style="font-size:30px;font-weight:900;color:#fff;letter-spacing:2px;">VQSTORE</div>
    <div style="color:#bfdbfe;font-size:13px;margin-top:6px;">Laptop &amp; Phụ kiện Công nghệ</div>
    <div style="margin-top:18px;display:inline-block;background:rgba(255,255,255,.15);
                border-radius:30px;padding:8px 22px;">
      <span style="color:#fff;font-size:15px;font-weight:600;">✅ Đặt hàng thành công!</span>
    </div>
  </div>

  <!-- Body -->
  <div style="padding:28px 32px;">

    <p style="font-size:16px;font-weight:700;margin:0 0 6px;color:#1e2a3b;">
      Xin chào ' . htmlspecialchars($order['ten_giao_hang']) . ',
    </p>
    <p style="font-size:14px;color:#6b7280;margin:0 0 24px;line-height:1.7;">
      Cảm ơn bạn đã mua hàng tại <strong style="color:#1d4ed8;">VQSTORE</strong>!
      Đơn hàng <strong style="color:#1d4ed8;">#' . $order['ma_don_hang'] . '</strong>
      của bạn đã được ghi nhận thành công và đang chờ xác nhận.
    </p>

    <!-- Tiêu đề bảng -->
    <h3 style="font-size:14px;font-weight:700;color:#1d4ed8;margin:0 0 12px;
               padding-bottom:8px;border-bottom:2px solid #1d4ed8;">Chi tiết đơn hàng</h3>

    <!-- Bảng sản phẩm: 2 cột (ảnh | thông tin) — hiển thị tốt trên mobile -->
    <table width="100%" cellpadding="0" cellspacing="0"
           style="border-collapse:collapse;border:1px solid #e5e7eb;border-radius:10px;
                  overflow:hidden;margin-bottom:16px;font-size:13px;">
      <tbody>' . $rows . '</tbody>
    </table>

    <!-- Tổng tiền: bảng riêng, căn phải -->
    <table width="100%" cellpadding="0" cellspacing="0"
           style="border-collapse:collapse;margin-bottom:20px;">
      <tr>
        <td style="padding:7px 0;font-size:13px;color:#6b7280;">Tạm tính:</td>
        <td style="padding:7px 0;font-size:13px;color:#374151;font-weight:600;text-align:right;white-space:nowrap;">
          ' . number_format($subtotal, 0, ',', '.') . 'đ</td>
      </tr>
      ' . $discountRow . '
      <tr style="border-top:2px solid #e5e7eb;">
        <td style="padding:10px 0 4px;font-size:14px;font-weight:700;color:#1e2a3b;">Tổng cộng:</td>
        <td style="padding:10px 0 4px;font-size:17px;font-weight:800;color:#1d4ed8;text-align:right;white-space:nowrap;">
          ' . number_format($total, 0, ',', '.') . 'đ</td>
      </tr>
    </table>

    <!-- Thông tin giao hàng -->
    <div style="background:#f8fafd;border-radius:10px;padding:18px 20px;margin-bottom:24px;">
      <h4 style="font-size:13.5px;font-weight:700;margin:0 0 12px;color:#374151;">
        📦 Thông tin giao hàng
      </h4>
      <table cellpadding="0" cellspacing="0" style="font-size:13px;color:#374151;width:100%;">
        <tr><td style="padding:3px 0;width:130px;color:#9ca3af;">Người nhận:</td>
            <td style="padding:3px 0;font-weight:600;">' . htmlspecialchars($order['ten_giao_hang']) . '</td></tr>
        <tr><td style="padding:3px 0;color:#9ca3af;">Điện thoại:</td>
            <td style="padding:3px 0;">' . htmlspecialchars($order['sdt_giao_hang']) . '</td></tr>
        <tr><td style="padding:3px 0;color:#9ca3af;">Địa chỉ:</td>
            <td style="padding:3px 0;">' . htmlspecialchars($order['dia_chi_giao_hang']) . '</td></tr>
        <tr><td style="padding:3px 0;color:#9ca3af;">Thanh toán:</td>
            <td style="padding:3px 0;">' . $payLabel . '</td></tr>
      </table>
      ' . $noteHtml . '
    </div>

    <!-- Nút theo dõi -->
    <div style="text-align:center;">
      <a href="' . $orderLink . '"
         style="display:inline-block;background:linear-gradient(135deg,#1d4ed8,#3b82f6);
                color:#fff;padding:14px 36px;border-radius:30px;font-weight:700;
                font-size:15px;text-decoration:none;letter-spacing:.3px;">
        Theo dõi đơn hàng →
      </a>
    </div>

  </div><!-- /body -->

  <!-- Footer -->
  <div style="background:#f8fafd;padding:20px 32px;text-align:center;
              font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;">
    © ' . date('Y') . ' <strong style="color:#6b7280;">VQSTORE</strong> — Laptop &amp; Phụ kiện Công nghệ<br>
    Email này được gửi tự động, vui lòng không phản hồi trực tiếp.
  </div>

</div>
</body></html>';

    return ['html' => $html, 'images' => $cidImages];
}

/** Tạo khung HTML email có thương hiệu (wrapper chung) */
function buildMailHtml($subject, $bodyHtml)
{
    return '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>' . htmlspecialchars($subject) . '</title>
<style>
  body{margin:0;padding:0;background:#f4f6fb;font-family:Inter,sans-serif;color:#1e2a3b}
  .wrap{max-width:600px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;
        box-shadow:0 2px 12px rgba(0,0,0,.08)}
  .header{background:linear-gradient(135deg,#1d4ed8,#2563eb);padding:28px 32px;text-align:center}
  .header img{height:50px;width:auto}
  .header h1{color:#fff;margin:12px 0 0;font-size:18px;font-weight:600}
  .body{padding:32px}
  .body h2{font-size:17px;color:#1d4ed8;margin:0 0 16px}
  .body p{line-height:1.7;color:#374151;margin:0 0 12px}
  .footer{background:#f8fafd;padding:20px 32px;text-align:center;
          font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div style="font-size:24px;font-weight:800;color:#fff;letter-spacing:1px;">VQSTORE</div>
    <h1>' . htmlspecialchars($subject) . '</h1>
  </div>
  <div class="body">
    ' . $bodyHtml . '
  </div>
  <div class="footer">
    © ' . date('Y') . ' VQSTORE — Cửa hàng Laptop &amp; Phụ kiện Công nghệ<br>
    Đây là email tự động, vui lòng không reply trực tiếp.
  </div>
</div>
</body></html>';
}
