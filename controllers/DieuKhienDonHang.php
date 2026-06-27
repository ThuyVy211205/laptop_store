<?php
/**
 * Điều Khiển Đơn Hàng
 * Cho phép người dùng xem danh sách, chi tiết đơn hàng và hủy đơn
 */

require_once ROOT_PATH . '/models/DonHang.php';
require_once ROOT_PATH . '/models/NguoiDung.php';
require_once ROOT_PATH . '/models/SanPham.php';

class DieuKhienDonHang {
    private $donHangModel;
    private $nguoiDungModel;

    public function __construct() {
        $this->donHangModel   = new DonHang();
        $this->nguoiDungModel = new NguoiDung();
    }

    /** Danh sách đơn hàng của người dùng đang đăng nhập */
    public function index() {
        requireLogin();

        $status = $_GET['status'] ?? null;
        $orders = $this->donHangModel->getByUser($_SESSION['id_nguoi_dung'], $status);

        $orderIds     = array_column($orders, 'id');
        $itemsByOrder = $this->donHangModel->getItemsByOrderIds($orderIds);
        foreach ($orders as &$order) {
            $order['items'] = $itemsByOrder[$order['id']] ?? [];
        }
        unset($order);

        $pageTitle = 'Đơn hàng của tôi';
        require_once ROOT_PATH . '/views/tai_khoan/don_hang.php';
    }

    /** Hiển thị chi tiết một đơn hàng */
    public function detail($id) {
        requireLogin();

        $order = $this->donHangModel->getById($id);
        if (!$order || $order['id_nguoi_dung'] != $_SESSION['id_nguoi_dung']) {
            redirect('/order');
            return;
        }

        $details   = $this->donHangModel->getDetails($id);
        $pageTitle = 'Chi tiết đơn hàng #' . $order['ma_don_hang'];
        require_once ROOT_PATH . '/views/tai_khoan/chi_tiet_don.php';
    }

    /** Trang xác nhận đặt hàng thành công */
    public function success($id) {
        requireLogin();

        $order = $this->donHangModel->getById($id);
        if (!$order || $order['id_nguoi_dung'] != $_SESSION['id_nguoi_dung']) {
            redirect('/order');
            return;
        }

        $details   = $this->donHangModel->getDetails($id);
        $pageTitle = 'Đặt hàng thành công';
        require_once ROOT_PATH . '/views/thanh_toan/thanh_cong.php';
    }

    /** Tra cứu đơn hàng công khai theo mã đơn — không yêu cầu đăng nhập */
    public function lookup($param = null) {
        $code    = trim($_GET['code'] ?? '');
        $order   = null;
        $details = null;
        $error   = null;

        if ($code !== '') {
            $order = $this->donHangModel->getByCode($code);
            if ($order) {
                $details = $this->donHangModel->getDetails($order['id']);
            } else {
                $error = 'Không tìm thấy đơn hàng với mã <strong>' . htmlspecialchars($code) . '</strong>. Vui lòng kiểm tra lại.';
            }
        }

        $pageTitle = 'Tra cứu đơn hàng';
        require_once ROOT_PATH . '/views/don_hang/tra_cuu.php';
    }

    /** Hủy đơn hàng kèm lý do (kiểm tra quyền trước khi hủy) */
    public function cancel($id) {
        requireLogin();

        $order = $this->donHangModel->getById($id);
        if (!$order || $order['id_nguoi_dung'] != $_SESSION['id_nguoi_dung']) {
            redirect('/order');
            return;
        }

        if ($order['trang_thai'] !== 'pending') {
            setFlash('error', 'Chỉ có thể hủy đơn hàng đang chờ xác nhận');
            redirect('/order/detail/' . $id);
            return;
        }

        $reason = $_POST['reason'] ?? 'Khách hàng tự hủy';

        $details = $this->donHangModel->getDetails($id);
        $db = db();
        $db->beginTransaction();
        try {
            $this->donHangModel->cancel($id, $reason);

            $sanPhamModel = new SanPham();
            foreach ($details as $item) {
                $sanPhamModel->increaseStock($item['id_san_pham'], $item['so_luong']);
            }

            $this->nguoiDungModel->decrementStats($order['id_nguoi_dung'], $order['tong_tien']);
            $this->nguoiDungModel->updateRank($order['id_nguoi_dung']);

            if (!empty($order['id_phieu_giam'])) {
                $db->execute(
                    "UPDATE phieu_giam_gia SET so_lan_dung = GREATEST(0, so_lan_dung - 1) WHERE id = ?",
                    [$order['id_phieu_giam']]
                );
            }

            db()->insert('thong_bao', [
                'id_nguoi_dung' => $order['id_nguoi_dung'],
                'tieu_de'       => 'Đơn hàng đã bị hủy',
                'noi_dung'      => 'Đơn hàng #' . $order['ma_don_hang'] . ' đã bị hủy. Lý do: ' . $reason,
                'loai'          => 'order',
                'lien_ket'      => SITE_URL . '/order/detail/' . $id,
            ]);

            $db->commit();

            $orderRow = $this->donHangModel->getById($id);
            try {
                $mailResult = buildOrderCancelEmail($orderRow, $details);
                $mailErr = '';
                $toEmail = $orderRow['thu_dien_tu_giao_hang']
                    ?? (db()->fetch("SELECT thu_dien_tu FROM nguoi_dung WHERE id=?", [$order['id_nguoi_dung']])['thu_dien_tu'] ?? '');
                if ($toEmail) {
                    sendOrderMail($toEmail, $orderRow['ten_giao_hang'],
                        'Đơn hàng #' . $orderRow['ma_don_hang'] . ' đã bị hủy',
                        $mailResult['html'], $mailErr, $mailResult['images']);
                }
            } catch (Exception $mailEx) {
                error_log('Cancel mail error: ' . $mailEx->getMessage());
            }

            setFlash('success', 'Đã hủy đơn hàng thành công');
        } catch (Exception $e) {
            $db->rollback();
            setFlash('error', 'Hủy đơn hàng thất bại. Vui lòng thử lại.');
        }
        redirect('/order/detail/' . $id);
    }
}
