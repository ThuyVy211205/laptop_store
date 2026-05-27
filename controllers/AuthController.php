<?php
/**
 * Authentication Controller
 */

require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Cart.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Login form & POST
     */
    public function login()
    {
        if (isLoggedIn()) {
            redirect('/account');
            return;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                $error = 'Vui lòng nhập đầy đủ thông tin';
            } else {
                $db = db();
                $user = $this->userModel->getByEmail($email);

                /* --- Check admins table --- */
                if (!$user) {
                    $admin = $db->fetch(
                        "SELECT * FROM admins WHERE email = ? AND status = 'active'",
                        [$email]
                    );
                    if ($admin && password_verify($password, $admin['password'])) {
                        $_SESSION['admin'] = [
                            'id' => $admin['id'],
                            'full_name' => $admin['full_name'],
                            'email' => $admin['email'],
                            'role' => $admin['role'] ?? 'super_admin',
                        ];
                        redirect('/admin');
                        return;
                    }

                    /* --- Check employees table --- */
                    $emp = $db->fetch(
                        "SELECT * FROM employees WHERE email = ? AND status = 'active'",
                        [$email]
                    );
                    if ($emp && password_verify($password, $emp['password'])) {
                        $_SESSION['admin'] = [
                            'id' => $emp['id'],
                            'full_name' => $emp['full_name'],
                            'email' => $emp['email'],
                            'role' => $emp['role'] ?? 'Nhân viên bán hàng',
                        ];
                        redirect('/admin');
                        return;
                    }

                    $error = 'Email không tồn tại';
                } elseif ($user['status'] === 'blocked') {
                    $error = 'Tài khoản đã bị khóa. Vui lòng liên hệ admin.';
                } elseif (!$this->userModel->verifyPassword($password, $user['password'])) {
                    $error = 'Mật khẩu không đúng';
                } else {
                    // Login success
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_rank'] = $user['rank'];

                    // Merge guest cart
                    $cartModel = new Cart();
                    $cartModel->mergeSessionToDb($user['id']);

                    setFlash('success', 'Đăng nhập thành công! Chào mừng ' . $user['full_name']);
                    redirect('/account');
                    return;
                }
            }
        }

        $pageTitle = 'Đăng nhập';
        require_once ROOT_PATH . '/views/auth/login.php';
    }

    /**
     * Register form & POST
     */
    public function register()
    {
        if (isLoggedIn()) {
            redirect('/account');
            return;
        }

        $error = null;
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = $_POST;
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $agreeTerms = isset($_POST['agree_terms']);

            if (!$fullName || !$email || !$password) {
                $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
            } elseif (!isValidEmail($email)) {
                $error = 'Email không hợp lệ';
            } elseif (strlen($password) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự';
            } elseif ($password !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp';
            } elseif ($phone && !isValidPhone($phone)) {
                $error = 'Số điện thoại không hợp lệ';
            } elseif (!$agreeTerms) {
                $error = 'Vui lòng đồng ý với điều khoản sử dụng';
            } elseif ($this->userModel->emailExists($email)) {
                $error = 'Email đã được sử dụng';
            } else {
                // Create user
                $userId = $this->userModel->create([
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $password,
                ]);

                if ($userId) {
                    // Create welcome notification
                    db()->insert('notifications', [
                        'user_id' => $userId,
                        'title' => 'Chào mừng đến với VQSTORE!',
                        'content' => 'Cảm ơn bạn đã đăng ký tài khoản. Hãy khám phá ngay các sản phẩm hot!',
                        'type' => 'info',
                    ]);

                    setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                    redirect('/auth/login');
                    return;
                }
                $error = 'Đăng ký thất bại, vui lòng thử lại';
            }
        }

        $pageTitle = 'Đăng ký';
        require_once ROOT_PATH . '/views/auth/register.php';
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_destroy();
        redirect('/');
    }

    /**
     * Forgot password form
     */
    public function forgotPassword()
    {
        $message = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (!isValidEmail($email)) {
                $error = 'Email không hợp lệ';
            } else {
                $user = $this->userModel->getByEmail($email);
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $this->userModel->saveResetToken($email, $token, $expires);

                    // In production, send via email. Here, just show the link.
                    $resetLink = SITE_URL . '/auth/resetPassword?token=' . $token;
                    $message = 'Liên kết đặt lại mật khẩu (demo): <a href="' . $resetLink . '" class="text-warning">Bấm vào đây</a>';
                } else {
                    $message = 'Nếu email tồn tại, liên kết đặt lại đã được gửi.';
                }
            }
        }

        $pageTitle = 'Quên mật khẩu';
        require_once ROOT_PATH . '/views/auth/forgot-password.php';
    }

    /**
     * Reset password
     */
    public function resetPassword()
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        if (!$token) {
            redirect('/auth/login');
            return;
        }

        $user = $this->userModel->getByResetToken($token);
        if (!$user) {
            setFlash('error', 'Liên kết không hợp lệ hoặc đã hết hạn');
            redirect('/auth/forgotPassword');
            return;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($newPassword) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp';
            } else {
                $this->userModel->update($user['id'], ['password' => $newPassword]);
                $this->userModel->clearResetToken($user['id']);
                setFlash('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập.');
                redirect('/auth/login');
                return;
            }
        }

        $pageTitle = 'Đặt lại mật khẩu';
        require_once ROOT_PATH . '/views/auth/reset-password.php';
    }
}