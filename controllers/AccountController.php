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

        $user = $this->userModel->getById($_SESSION['user_id']);
        if (!$user) {
            session_destroy();
            redirect('/auth/login');
            return;
        }

        $recentOrders = $this->orderModel->getByUser($_SESSION['user_id']);
        $recentOrders = array_slice($recentOrders, 0, 5);
        $recentIds    = array_column($recentOrders, 'id');
        $itemsByOrder = $this->orderModel->getItemsByOrderIds($recentIds);
        foreach ($recentOrders as &$ord) {
            $ord['items'] = $itemsByOrder[$ord['id']] ?? [];
        }
        unset($ord);

        // Stats
        $totalOrders = count($this->orderModel->getByUser($_SESSION['user_id']));
        $successOrders = count($this->orderModel->getByUser($_SESSION['user_id'], 'completed'));

        $pageTitle = 'Tài khoản của tôi';
        require_once ROOT_PATH . '/views/account/profile.php';
    }

    /**
     * Danh sách đơn hàng — delegate sang OrderController
     */
    public function orders() {
        require_once ROOT_PATH . '/controllers/OrderController.php';
        (new OrderController())->index();
    }

    /**
     * Notifications / Inbox — xem thông báo và email từ admin
     */
    public function notifications() {
        requireLogin();
        $userId = $_SESSION['user_id'];

        // Đánh dấu đã đọc toàn bộ nếu có param ?read=all
        if (isset($_GET['read']) && $_GET['read'] === 'all') {
            db()->execute("UPDATE notifications SET is_read=1 WHERE user_id=?", [$userId]);
            redirect('/account/notifications');
            return;
        }

        $notifications = db()->fetchAll(
            "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100",
            [$userId]
        );

        $pageTitle = 'Thông báo & Email';
        require_once ROOT_PATH . '/views/account/notifications.php';
    }

    /**
     * Mark a single notification as read (AJAX)
     */
    public function markRead() {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                db()->execute(
                    "UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?",
                    [$id, $_SESSION['user_id']]
                );
            }
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }
        redirect('/account/notifications');
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

            if ($data['phone'] && !isValidPhone($data['phone'])) {
                setFlash('error', 'Số điện thoại không hợp lệ (ví dụ: 0901234567)');
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