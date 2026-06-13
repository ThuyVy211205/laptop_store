<?php
/**
 * Điều Khiển Thanh Toán
 * Xử lý toàn bộ luồng đặt hàng: hiển thị trang, áp voucher, tạo đơn, gửi email xác nhận
 */

require_once ROOT_PATH . '/models/GioHang.php';
require_once ROOT_PATH . '/models/DonHang.php';
require_once ROOT_PATH . '/models/SanPham.php';
require_once ROOT_PATH . '/models/NguoiDung.php';
require_once ROOT_PATH . '/models/PhieuGiamGia.php';

class DieuKhienThanhToan {
    private $gioHangModel;
    private $donHangModel;
    private $sanPhamModel;
    private $nguoiDungModel;
    private $phieuGiamGiaModel;

    public function __construct() {
        $this->gioHangModel      = new GioHang();
        $this->donHangModel      = new DonHang();
        $this->sanPhamModel      = new SanPham();
        $this->nguoiDungModel    = new NguoiDung();
        $this->phieuGiamGiaModel = new PhieuGiamGia();
    }

    /** Hiển thị trang thanh toán (yêu cầu đăng nhập) */
    public function index() {
        requireLogin();

        $userId = $_SESSION['id_nguoi_dung'];

        if (!empty($_SESSION['buy_now'])) {
            $buyNow  = $_SESSION['buy_now'];
            $product = $this->sanPhamModel->getById($buyNow['id_san_pham']);
            if (!$product) {
                unset($_SESSION['buy_now']);
                setFlash('error', 'Sản phẩm không tồn tại');
                redirect('/products');
                return;
            }
            $qty      = $buyNow['quantity'];
            $price    = $product['gia_khuyen_mai'] ?: $product['gia'];
            $items    = [[
                'id_san_pham'   => $product['id'],
                'id'            => $product['id'],
                'ten'           => $product['ten'],
                'hinh_thu_nho'  => $product['hinh_thu_nho'],
                'gia'           => $product['gia'],
                'gia_khuyen_mai'=> $product['gia_khuyen_mai'],
                'so_luong'      => $qty,
                'ton_kho'       => $product['ton_kho'],
            ]];
            $subtotal = $price * $qty;
        } else {
            $items = $this->gioHangModel->getItems($userId);
            if (empty($items)) {
                setFlash('warning', 'Giỏ hàng của bạn đang trống');
                redirect('/cart');
                return;
            }
            $subtotal = $this->gioHangModel->getTotal($userId);
        }

        $user      = $this->nguoiDungModel->getById($userId);
        $pageTitle = 'Thanh toán';

        require_once ROOT_PATH . '/views/thanh_toan/thanh_toan.php';
    }

    /** Xử lý đặt hàng: tạo đơn, lưu chi tiết, giảm tồn kho, gửi email xác nhận */
    public function place() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/checkout');
            return;
        }

        $userId   = $_SESSION['id_nguoi_dung'];
        $isBuyNow = !empty($_SESSION['buy_now']);

        if ($isBuyNow) {
            $buyNow  = $_SESSION['buy_now'];
            $product = $this->sanPhamModel->getById($buyNow['id_san_pham']);
            if (!$product) {
                unset($_SESSION['buy_now']);
                setFlash('error', 'Sản phẩm không tồn tại');
                redirect('/products');
                return;
            }
            $qty      = $buyNow['quantity'];
            $price    = $product['gia_khuyen_mai'] ?: $product['gia'];
            $items    = [[
                'id_san_pham'   => $product['id'],
                'id'            => $product['id'],
                'ten'           => $product['ten'],
                'hinh_thu_nho'  => $product['hinh_thu_nho'],
                'gia'           => $product['gia'],
                'gia_khuyen_mai'=> $product['gia_khuyen_mai'],
                'so_luong'      => $qty,
                'ton_kho'       => $product['ton_kho'],
            ]];
            $subtotal = $price * $qty;
        } else {
            $items = $this->gioHangModel->getItems($userId);
            if (empty($items)) {
                setFlash('warning', 'Giỏ hàng trống');
                redirect('/cart');
                return;
            }
            $subtotal = $this->gioHangModel->getTotal($userId);
        }

        $name       = trim($_POST['shipping_name']    ?? '');
        $phone      = trim($_POST['shipping_phone']   ?? '');
        $email      = trim($_POST['shipping_email']   ?? '');
        $address    = trim($_POST['shipping_address'] ?? '');
        $note       = trim($_POST['note']             ?? '');
        $payMethod  = $_POST['payment_method']        ?? 'cod';
        $voucherCode = trim($_POST['voucher_code'] ?? '');

        if (!$name || !$phone || !$address) {
            setFlash('error', 'Vui lòng nhập đầy đủ thông tin giao hàng');
            redirect('/checkout');
            return;
        }

        if (!isValidPhone($phone)) {
            setFlash('error', 'Số điện thoại không hợp lệ (ví dụ: 0901234567)');
            redirect('/checkout');
            return;
        }

        if ($email && !isValidEmail($email)) {
            setFlash('error', 'Email không hợp lệ');
            redirect('/checkout');
            return;
        }

        $discount  = 0;
        $voucherId = null;

        if ($voucherCode) {
            $apply = $this->phieuGiamGiaModel->apply($voucherCode, $subtotal);
            if ($apply['success']) {
                $discount  = $apply['discount'];
                $voucherId = $apply['voucher']['id'];
            }
        }

        $total = $subtotal - $discount;
        if ($total < 0) $total = 0;

        foreach ($items as $item) {
            if ($item['ton_kho'] < $item['so_luong']) {
                setFlash('error', 'Sản phẩm "' . $item['ten'] . '" chỉ còn ' . $item['ton_kho'] . ' sản phẩm');
                redirect('/cart');
                return;
            }
        }

        $db = db();
        $db->beginTransaction();

        try {
            $orderId = $this->donHangModel->create([
                'ma_don_hang'            => generateOrderCode(),
                'id_nguoi_dung'          => $userId,
                'ten_giao_hang'          => $name,
                'sdt_giao_hang'          => $phone,
                'thu_dien_tu_giao_hang'  => $email,
                'dia_chi_giao_hang'      => $address,
                'ghi_chu'                => $note,
                'tam_tinh'               => $subtotal,
                'so_tien_giam'           => $discount,
                'tong_tien'              => $total,
                'id_phieu_giam'          => $voucherId,
                'phuong_thuc_thanh_toan' => $payMethod,
                'trang_thai_thanh_toan'  => 'pending',
                'trang_thai'             => 'pending',
            ]);

            foreach ($items as $item) {
                $this->donHangModel->addDetail($orderId, $item, $item['so_luong']);
                $this->sanPhamModel->decreaseStock($item['id_san_pham'] ?? $item['id'], $item['so_luong']);
            }

            if ($voucherId) {
                $this->phieuGiamGiaModel->use($voucherId);
            }

            $this->nguoiDungModel->incrementStats($userId, $total);
            $this->nguoiDungModel->updateRank($userId);

            $orderCode = $this->donHangModel->getById($orderId)['ma_don_hang'];
            $db->insert('thong_bao', [
                'id_nguoi_dung' => $userId,
                'tieu_de'       => 'Đặt hàng thành công',
                'noi_dung'      => 'Đơn hàng #' . $orderCode . ' đã được tạo thành công. Cảm ơn bạn đã mua hàng!',
                'loai'          => 'order',
                'lien_ket'      => SITE_URL . '/order/detail/' . $orderId,
            ]);

            if ($isBuyNow) {
                unset($_SESSION['buy_now']);
            } else {
                $this->gioHangModel->clear($userId);
            }

            $db->commit();

            try {
                $orderRow = $this->donHangModel->getById($orderId);
                $toEmail  = $email ?: ($db->fetch("SELECT thu_dien_tu FROM nguoi_dung WHERE id=?", [$userId])['thu_dien_tu'] ?? '');

                if ($toEmail) {
                    $mailResult = buildOrderConfirmEmail(
                        array_merge($orderRow, ['id' => $orderId]),
                        $items
                    );
                    $mailErr = '';
                    sendOrderMail(
                        $toEmail, $name,
                        'Xác nhận đơn hàng #' . $orderCode,
                        $mailResult['html'],
                        $mailErr,
                        $mailResult['images']
                    );
                    if ($mailErr) error_log('Order mail error: ' . $mailErr);
                }
            } catch (Exception $mailEx) {
                error_log('Order confirm mail error: ' . $mailEx->getMessage());
            }

            redirect('/order/success/' . $orderId);
        } catch (Exception $e) {
            $db->rollback();
            setFlash('error', 'Đặt hàng thất bại: ' . $e->getMessage());
            redirect('/checkout');
        }
    }
}
