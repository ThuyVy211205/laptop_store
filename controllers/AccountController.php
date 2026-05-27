<?php
/**
 * Account Controller
 */

require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Order.php';

class AccountController {
    private $userModel;
    private $orderModel;

    public function __construct() {
        $this->userModel  = new User();
        $this->orderModel = new Order();
    }

    /**
     * Account dashboard
     */
    public function index() {
        requireLogin();

        $user         = $this->userModel->getById($_SESSION['user_id']);
        $recentOrders = $this->orderModel->getByUser($_SESSION['user_id']);
        $recentOrders = array_slice($recentOrders, 0, 5);

        // Stats
        $totalOrders = count($this->orderModel->getByUser($_SESSION['user_id']));
        $successOrders = count($this->orderModel->getByUser($_SESSION['user_id'], 'completed'));

        $pageTitle = 'Tài khoản của tôi';
        require_once ROOT_PATH . '/views/account/profile.php';
    }

    /**
     * Update profile
     */
    public function update() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/account');
            return;
        }

        $userId = $_SESSION['user_id'];
        $data   = [];

        $type = $_POST['type'] ?? 'profile';

        if ($type === 'profile') {
            $data['full_name'] = trim($_POST['full_name'] ?? '');
            $data['phone']     = trim($_POST['phone']     ?? '');
            $data['birthday']  = $_POST['birthday']       ?? null;
            $data['gender']    = $_POST['gender']         ?? null;
            $data['address']   = trim($_POST['address']   ?? '');

            if (!$data['full_name']) {
                setFlash('error', 'Vui lòng nhập họ tên');
                redirect('/account');
                return;
            }

            // Handle avatar upload
            if (!empty($_FILES['avatar']['tmp_name'])) {
                $avatar = uploadImage($_FILES['avatar'], 'avatars');
                if ($avatar) $data['avatar'] = $avatar;
            }

            $this->userModel->update($userId, $data);
            $_SESSION['user_name'] = $data['full_name'];
            setFlash('success', 'Cập nhật thông tin thành công');
        } elseif ($type === 'password') {
            $current     = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password']     ?? '';
            $confirm     = $_POST['confirm_password'] ?? '';

            $user = $this->userModel->getById($userId);

            if (!$this->userModel->verifyPassword($current, $user['password'])) {
                setFlash('error', 'Mật khẩu hiện tại không đúng');
            } elseif (strlen($newPassword) < 6) {
                setFlash('error', 'Mật khẩu mới phải có ít nhất 6 ký tự');
            } elseif ($newPassword !== $confirm) {
                setFlash('error', 'Mật khẩu xác nhận không khớp');
            } else {
                $this->userModel->update($userId, ['password' => $newPassword]);
                setFlash('success', 'Đổi mật khẩu thành công');
            }
        }

        redirect('/account');
    }
}