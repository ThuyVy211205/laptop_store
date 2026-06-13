<?php
/**
 * Điều Khiển Liên Hệ
 * Xử lý form liên hệ và lưu tin nhắn của khách hàng
 */

class DieuKhienLienHe {
    public function index() {
        $pageTitle = 'Liên hệ với chúng tôi';
        $sent  = false;
        $error = '';

        // Pre-fill từ tài khoản nếu đã đăng nhập
        $prefill = ['name' => '', 'email' => '', 'phone' => ''];
        if (isLoggedIn()) {
            $nguoiDung = db()->fetch(
                "SELECT ho_ten, thu_dien_tu, so_dien_thoai FROM nguoi_dung WHERE id = ?",
                [$_SESSION['id_nguoi_dung']]
            );
            if ($nguoiDung) {
                $prefill['name']  = $nguoiDung['ho_ten']        ?? '';
                $prefill['email'] = $nguoiDung['thu_dien_tu']   ?? '';
                $prefill['phone'] = $nguoiDung['so_dien_thoai'] ?? '';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten         = trim($_POST['name']     ?? '');
            $email       = trim($_POST['email']    ?? '');
            $soDienThoai = trim($_POST['phone']    ?? '');
            $idDonHang   = trim($_POST['order_id'] ?? '');
            $chuDe       = trim($_POST['subject']  ?? '');
            $noiDung     = trim($_POST['message']  ?? '');

            if (!$ten) {
                $error = 'Vui lòng nhập họ và tên.';
            } elseif (!$soDienThoai) {
                $error = 'Vui lòng nhập số điện thoại.';
            } elseif (!isValidPhone($soDienThoai)) {
                $error = 'Số điện thoại không hợp lệ — phải là số Việt Nam 10 chữ số (ví dụ: 0901234567).';
            } elseif (!$email) {
                $error = 'Vui lòng nhập địa chỉ email.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email không đúng định dạng (ví dụ: example@gmail.com).';
            } elseif (!preg_match('/^[^@]+@[^@]+\.[a-zA-Z]{2,10}$/', $email)) {
                $error = 'Email phải có đuôi hợp lệ (ví dụ: .com, .vn, .net).';
            } elseif (!$noiDung) {
                $error = 'Vui lòng nhập nội dung tin nhắn.';
            } else {
                db()->insert('lien_he', [
                    'ten'            => $ten,
                    'thu_dien_tu'    => $email,
                    'so_dien_thoai'  => $soDienThoai ?: null,
                    'id_don_hang'    => $idDonHang,
                    'chu_de'         => $chuDe ?: null,
                    'noi_dung'       => $noiDung,
                    'trang_thai'     => 'new',
                ]);
                $sent = true;
            }
        }

        require_once ROOT_PATH . '/views/lien_he/lien_he.php';
    }
}
