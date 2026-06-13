<?php
/**
 * Điều Khiển Tài Khoản
 * Xử lý hồ sơ cá nhân, đổi mật khẩu, thông báo
 */

require_once ROOT_PATH . '/models/NguoiDung.php';
require_once ROOT_PATH . '/models/DonHang.php';

class DieuKhienTaiKhoan {

    private function requireLogin() {
        if (empty($_SESSION['id_nguoi_dung'])) {
            header('Location: ' . SITE_URL . '/auth/login');
            exit;
        }
    }

    /** Trang hồ sơ cá nhân */
    public function index() {
        $this->requireLogin();
        $userId = $_SESSION['id_nguoi_dung'];

        $nguoiDung = new NguoiDung();
        $donHang   = new DonHang();

        $user          = $nguoiDung->getById($userId);
        $allOrders     = $donHang->getByUser($userId);
        $totalOrders   = count($allOrders);
        $successOrders = count(array_filter($allOrders, fn($o) => $o['trang_thai'] === 'completed'));
        $recentOrders  = array_slice($allOrders, 0, 5);

        $pageTitle = 'Tài khoản của tôi';
        require_once ROOT_PATH . '/views/tai_khoan/ho_so.php';
    }

    /** Cập nhật hồ sơ hoặc đổi mật khẩu (POST) */
    public function update() {
        $this->requireLogin();
        $userId = $_SESSION['id_nguoi_dung'];
        $type   = $_POST['type'] ?? '';

        $nguoiDung = new NguoiDung();
        $user      = $nguoiDung->getById($userId);

        if ($type === 'profile') {
            $data = [
                'ho_ten'        => trim($_POST['full_name'] ?? ''),
                'so_dien_thoai' => trim($_POST['phone']     ?? ''),
                'ngay_sinh'     => $_POST['birthday']       ?? null,
                'gioi_tinh'     => $_POST['gender']         ?? null,
                'dia_chi'       => trim($_POST['address']   ?? ''),
            ];

            if (!empty($_FILES['avatar']['name'])) {
                $avatar = uploadImage($_FILES['avatar'], 'anh_dai_dien');
                if ($avatar) {
                    if (!empty($user['anh_dai_dien'])) {
                        $old = UPLOAD_PATH . '/' . $user['anh_dai_dien'];
                        if (file_exists($old)) unlink($old);
                    }
                    $data['anh_dai_dien'] = $avatar;
                }
            }

            $nguoiDung->update($userId, $data);
            setFlash('success', 'Cập nhật hồ sơ thành công.');

        } elseif ($type === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password']     ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!$nguoiDung->verifyPassword($current, $user['mat_khau'])) {
                setFlash('danger', 'Mật khẩu hiện tại không đúng.');
            } elseif (strlen($new) < 6) {
                setFlash('danger', 'Mật khẩu mới phải có ít nhất 6 ký tự.');
            } elseif ($new !== $confirm) {
                setFlash('danger', 'Xác nhận mật khẩu không khớp.');
            } else {
                $nguoiDung->update($userId, ['mat_khau' => $new]);
                setFlash('success', 'Đổi mật khẩu thành công.');
            }
        }

        header('Location: ' . SITE_URL . '/account');
        exit;
    }

    /** Trang thông báo */
    public function notifications() {
        $this->requireLogin();
        $userId = $_SESSION['id_nguoi_dung'];

        if (isset($_GET['read'])) {
            if ($_GET['read'] === 'all') {
                db()->execute(
                    "UPDATE thong_bao SET da_doc = 1 WHERE id_nguoi_dung = ?",
                    [$userId]
                );
            } elseif (is_numeric($_GET['read'])) {
                db()->execute(
                    "UPDATE thong_bao SET da_doc = 1 WHERE id = ? AND id_nguoi_dung = ?",
                    [(int)$_GET['read'], $userId]
                );
            }
            header('Location: ' . SITE_URL . '/account/notifications');
            exit;
        }

        $notifications = db()->fetchAll(
            "SELECT * FROM thong_bao WHERE id_nguoi_dung = ? ORDER BY ngay_tao DESC LIMIT 50",
            [$userId]
        );

        $pageTitle = 'Thông báo';
        require_once ROOT_PATH . '/views/tai_khoan/thong_bao.php';
    }
}