<?php
/**
 * Điều Khiển Xác Thực
 * Xử lý đăng nhập, đăng ký, đặt lại mật khẩu và đăng nhập Google OAuth
 */

require_once ROOT_PATH . '/models/NguoiDung.php';
require_once ROOT_PATH . '/models/GioHang.php';

class DieuKhienXacThuc {
    private $nguoiDungModel;

    public function __construct() {
        $this->nguoiDungModel = new NguoiDung();
    }

    /** Hiển thị form đăng nhập và xử lý POST đăng nhập */
    public function login() {
        if (isLoggedIn()) {
            redirect('/account');
            return;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                $error = 'Vui lòng nhập đầy đủ thông tin';
            } else {
                $db   = db();
                $user = $this->nguoiDungModel->getByEmail($email);

                if (!$user) {
                    $admin = $db->fetch(
                        "SELECT * FROM quan_tri_vien WHERE thu_dien_tu = ? AND trang_thai = 'active'",
                        [$email]
                    );
                    if ($admin) {
                        if (password_verify($password, $admin['mat_khau'])) {
                            $_SESSION['admin'] = [
                                'id'      => $admin['id'],
                                'ho_ten'  => $admin['ho_ten'],
                                'email'   => $admin['thu_dien_tu'],
                                'vai_tro' => $admin['vai_tro'] ?? 'super_admin',
                            ];
                            redirect('/admin');
                            return;
                        }
                        $error = 'Mật khẩu không đúng';
                    } else {
                        $emp = $db->fetch(
                            "SELECT * FROM nhan_vien WHERE thu_dien_tu = ? AND trang_thai = 'active'",
                            [$email]
                        );
                        if ($emp) {
                            if (password_verify($password, $emp['mat_khau'])) {
                                $_SESSION['admin'] = [
                                    'id'      => $emp['id'],
                                    'ho_ten'  => $emp['ho_ten'],
                                    'email'   => $emp['thu_dien_tu'],
                                    'vai_tro' => $emp['vai_tro'] ?? 'Nhân viên bán hàng',
                                ];
                                redirect('/admin');
                                return;
                            }
                            $error = 'Mật khẩu không đúng';
                        } else {
                            $error = 'Email không tồn tại';
                        }
                    }
                } elseif ($user['trang_thai'] === 'blocked') {
                    $error = 'Tài khoản đã bị khóa. Vui lòng liên hệ admin.';
                } elseif (!$this->nguoiDungModel->verifyPassword($password, $user['mat_khau'])) {
                    $error = 'Mật khẩu không đúng';
                } else {
                    $_SESSION['id_nguoi_dung']    = $user['id'];
                    $_SESSION['user_name']  = $user['ho_ten'];
                    $_SESSION['user_email'] = $user['thu_dien_tu'];
                    $_SESSION['user_rank']  = $user['hang'];

                    $gioHangModel = new GioHang();
                    $gioHangModel->mergeSessionToDb($user['id']);

                    setFlash('success', 'Đăng nhập thành công! Chào mừng ' . $user['ho_ten']);
                    redirect('/account');
                    return;
                }
            }
        }

        $pageTitle = 'Đăng nhập';
        require_once ROOT_PATH . '/views/xac_thuc/dang_nhap.php';
    }

    /** Hiển thị form đăng ký và xử lý POST tạo tài khoản */
    public function register() {
        if (isLoggedIn()) {
            redirect('/account');
            return;
        }

        $error = null;
        $old   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old            = $_POST;
            $hoTen          = trim($_POST['ho_ten']       ?? '');
            $email          = trim($_POST['email']           ?? '');
            $soDienThoai    = trim($_POST['phone']           ?? '');
            $password       = $_POST['password']             ?? '';
            $confirmPassword = $_POST['confirm_password']   ?? '';
            $agreeTerms     = isset($_POST['agree_terms']);

            if (!$hoTen || !$email || !$password) {
                $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
            } elseif (!isValidEmail($email)) {
                $error = 'Email không hợp lệ';
            } elseif (strlen($password) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự';
            } elseif ($password !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp';
            } elseif ($soDienThoai && !isValidPhone($soDienThoai)) {
                $error = 'Số điện thoại không hợp lệ';
            } elseif (!$agreeTerms) {
                $error = 'Vui lòng đồng ý với điều khoản sử dụng';
            } elseif ($this->nguoiDungModel->emailExists($email)) {
                $error = 'Email đã được sử dụng';
            } else {
                $userId = $this->nguoiDungModel->create([
                    'ho_ten'        => $hoTen,
                    'thu_dien_tu'   => $email,
                    'so_dien_thoai' => $soDienThoai,
                    'mat_khau'      => $password,
                ]);

                if ($userId) {
                    db()->insert('thong_bao', [
                        'id_nguoi_dung'    => $userId,
                        'tieu_de'          => 'Chào mừng đến với VQSTORE!',
                        'noi_dung'         => 'Cảm ơn bạn đã đăng ký tài khoản. Hãy khám phá ngay các sản phẩm hot!',
                        'loai'             => 'info',
                    ]);

                    setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                    redirect('/auth/login');
                    return;
                }
                $error = 'Đăng ký thất bại, vui lòng thử lại';
            }
        }

        $pageTitle = 'Đăng ký';
        require_once ROOT_PATH . '/views/xac_thuc/dang_ky.php';
    }

    /** Đăng xuất — xóa toàn bộ session và về trang chủ */
    public function logout() {
        session_destroy();
        redirect('/');
    }

    /** Hiển thị form quên mật khẩu và gửi email đặt lại */
    public function forgotPassword() {
        $message = null;
        $error   = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (!isValidEmail($email)) {
                $error = 'Email không hợp lệ';
            } else {
                $user = $this->nguoiDungModel->getByEmail($email);
                if ($user) {
                    $token   = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $this->nguoiDungModel->saveResetToken($email, $token, $expires);

                    $resetLink = SITE_URL . '/auth/resetPassword?token=' . $token;
                    $message = 'Liên kết đặt lại mật khẩu (demo): <a href="' . $resetLink . '" class="text-warning">Bấm vào đây</a>';
                } else {
                    $message = 'Nếu email tồn tại, liên kết đặt lại đã được gửi.';
                }
            }
        }

        $pageTitle = 'Quên mật khẩu';
        require_once ROOT_PATH . '/views/xac_thuc/quen_mat_khau.php';
    }

    /** Google OAuth — khởi tạo đăng nhập, chuyển hướng đến trang Google */
    public function googleLogin() {
        if (isLoggedIn()) {
            redirect('/account');
            return;
        }

        if (!GOOGLE_CLIENT_ID || !GOOGLE_CLIENT_SECRET) {
            setFlash('error', 'Chức năng đăng nhập Google chưa được cấu hình.');
            redirect('/auth/login');
            return;
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
        ]);

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        exit;
    }

    /** Google OAuth — nhận callback, xử lý đăng nhập hoặc tạo tài khoản mới */
    public function googleCallback() {
        if (!empty($_GET['error'])) {
            setFlash('error', 'Đăng nhập Google bị từ chối.');
            redirect('/auth/login');
            return;
        }

        $state = $_GET['state'] ?? '';
        if (!$state || $state !== ($_SESSION['oauth_state'] ?? '')) {
            setFlash('error', 'Xác thực thất bại. Vui lòng thử lại.');
            redirect('/auth/login');
            return;
        }
        unset($_SESSION['oauth_state']);

        $code = $_GET['code'] ?? '';
        if (!$code) {
            setFlash('error', 'Đăng nhập Google thất bại.');
            redirect('/auth/login');
            return;
        }

        $tokenData = $this->googleExchangeCode($code);
        if (!$tokenData || empty($tokenData['access_token'])) {
            setFlash('error', 'Không thể lấy token từ Google. Vui lòng thử lại.');
            redirect('/auth/login');
            return;
        }

        $googleUser = $this->googleGetUserInfo($tokenData['access_token']);
        if (!$googleUser || empty($googleUser['email'])) {
            setFlash('error', 'Không thể lấy thông tin tài khoản Google.');
            redirect('/auth/login');
            return;
        }

        $googleId = $googleUser['sub'] ?? '';
        $email    = $googleUser['email'];
        $name     = $googleUser['name'] ?? $email;
        $avatar   = $googleUser['picture'] ?? null;

        $user = $googleId ? $this->nguoiDungModel->getByGoogleId($googleId) : null;
        if (!$user) {
            $user = $this->nguoiDungModel->getByEmail($email);
        }

        if ($user) {
            if ($user['trang_thai'] === 'blocked') {
                setFlash('error', 'Tài khoản đã bị khóa. Vui lòng liên hệ admin.');
                redirect('/auth/login');
                return;
            }
            if (empty($user['id_google']) && $googleId) {
                db()->execute("UPDATE nguoi_dung SET id_google=? WHERE id=?", [$googleId, $user['id']]);
            }
        } else {
            $userId = db()->insert('nguoi_dung', [
                'ho_ten'       => $name,
                'thu_dien_tu'  => $email,
                'id_google'    => $googleId,
                'anh_dai_dien' => $avatar,
            ]);
            $user = $this->nguoiDungModel->getById($userId);

            db()->insert('thong_bao', [
                'id_nguoi_dung' => $userId,
                'tieu_de'       => 'Chào mừng đến với VQSTORE!',
                'noi_dung'      => 'Cảm ơn bạn đã đăng ký tài khoản bằng Google. Hãy khám phá ngay các sản phẩm hot!',
                'loai'          => 'info',
            ]);
        }

        $_SESSION['id_nguoi_dung']    = $user['id'];
        $_SESSION['user_name']  = $user['ho_ten'];
        $_SESSION['user_email'] = $user['thu_dien_tu'];
        $_SESSION['user_rank']  = $user['hang'];

        $gioHangModel = new GioHang();
        $gioHangModel->mergeSessionToDb($user['id']);

        setFlash('success', 'Đăng nhập thành công! Chào mừng ' . $user['ho_ten']);
        redirect('/account');
    }

    private function googleExchangeCode($code) {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => GOOGLE_CLIENT_ID,
                'client_secret' => GOOGLE_CLIENT_SECRET,
                'redirect_uri'  => GOOGLE_REDIRECT_URI,
                'grant_type'    => 'authorization_code',
            ]),
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ? json_decode($response, true) : null;
    }

    private function googleGetUserInfo($accessToken) {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ? json_decode($response, true) : null;
    }

    /** Hiển thị form và xử lý đặt lại mật khẩu mới từ token email */
    public function resetPassword() {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        if (!$token) {
            redirect('/auth/login');
            return;
        }

        $user = $this->nguoiDungModel->getByResetToken($token);
        if (!$user) {
            setFlash('error', 'Liên kết không hợp lệ hoặc đã hết hạn');
            redirect('/auth/forgotPassword');
            return;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword     = $_POST['password']         ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($newPassword) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp';
            } else {
                $this->nguoiDungModel->update($user['id'], ['mat_khau' => $newPassword]);
                $this->nguoiDungModel->clearResetToken($user['id']);
                setFlash('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập.');
                redirect('/auth/login');
                return;
            }
        }

        $pageTitle = 'Đặt lại mật khẩu';
        require_once ROOT_PATH . '/views/xac_thuc/dat_lai_mat_khau.php';
    }
}
