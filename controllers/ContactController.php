<?php
class ContactController {
    public function index() {
        $pageTitle = 'Liên hệ với chúng tôi';
        $sent  = false;
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name']     ?? '');
            $email    = trim($_POST['email']    ?? '');
            $phone    = trim($_POST['phone']    ?? '');
            $order_id = trim($_POST['order_id'] ?? '');
            $subject  = trim($_POST['subject']  ?? '');
            $message  = trim($_POST['message']  ?? '');

            if (!$name || !$email || !$order_id || !$message) {
                $error = 'Vui lòng điền đầy đủ các trường bắt buộc.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.[a-zA-Z]{2,10}$/', $email)) {
                $error = 'Email không hợp lệ (ví dụ: example@gmail.com).';
            } elseif ($phone && !isValidPhone($phone)) {
                $error = 'Số điện thoại không hợp lệ (ví dụ: 0901234567).';
            } else {
                db()->insert('lien_he', [
                    'name'     => $name,
                    'email'    => $email,
                    'phone'    => $phone    ?: null,
                    'order_id' => $order_id,
                    'subject'  => $subject  ?: null,
                    'message'  => $message,
                    'status'   => 'new',
                ]);
                $sent = true;
            }
        }

        require_once ROOT_PATH . '/views/contact/index.php';
    }
}
