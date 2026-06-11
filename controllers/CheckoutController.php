<?php
/**
 * Controller Thanh Toán
 * Xử lý toàn bộ luồng đặt hàng: hiển thị trang, áp voucher, tạo đơn, gửi email xác nhận
 */

require_once ROOT_PATH . '/models/Cart.php';
require_once ROOT_PATH . '/models/Order.php';
require_once ROOT_PATH . '/models/Product.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Voucher.php';

class CheckoutController {
    private $cartModel;
    private $orderModel;
    private $productModel;
    private $userModel;
    private $voucherModel;

    public function __construct() {
        $this->cartModel    = new Cart();
        $this->orderModel   = new Order();
        $this->productModel = new Product();
        $this->userModel    = new User();
        $this->voucherModel = new Voucher();
    }

    /** Hiển thị trang thanh toán (yêu cầu đăng nhập) */
    public function index() {
        requireLogin();

        $userId = $_SESSION['user_id'];

        if (!empty($_SESSION['buy_now'])) {
            $buyNow  = $_SESSION['buy_now'];
            $product = $this->productModel->getById($buyNow['product_id']);
            if (!$product) {
                unset($_SESSION['buy_now']);
                setFlash('error', 'Sản phẩm không tồn tại');
                redirect('/products');
                return;
            }
            $qty      = $buyNow['quantity'];
            $price    = $product['sale_price'] ?: $product['price'];
            $items    = [[
                'product_id' => $product['id'],
                'id'         => $product['id'],
                'name'       => $product['name'],
                'thumbnail'  => $product['thumbnail'],
                'price'      => $product['price'],
                'sale_price' => $product['sale_price'],
                'quantity'   => $qty,
                'stock'      => $product['stock'],
            ]];
            $subtotal = $price * $qty;
        } else {
            $items = $this->cartModel->getItems($userId);
            if (empty($items)) {
                setFlash('warning', 'Giỏ hàng của bạn đang trống');
                redirect('/cart');
                return;
            }
            $subtotal = $this->cartModel->getTotal($userId);
        }

        $user      = $this->userModel->getById($userId);
        $pageTitle = 'Thanh toán';

        require_once ROOT_PATH . '/views/checkout/index.php';
    }

    /** Xử lý đặt hàng: tạo đơn, lưu chi tiết, giảm tồn kho, gửi email xác nhận */
    public function place() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/checkout');
            return;
        }

        $userId   = $_SESSION['user_id'];
        $isBuyNow = !empty($_SESSION['buy_now']);

        if ($isBuyNow) {
            $buyNow  = $_SESSION['buy_now'];
            $product = $this->productModel->getById($buyNow['product_id']);
            if (!$product) {
                unset($_SESSION['buy_now']);
                setFlash('error', 'Sản phẩm không tồn tại');
                redirect('/products');
                return;
            }
            $qty      = $buyNow['quantity'];
            $price    = $product['sale_price'] ?: $product['price'];
            $items    = [[
                'product_id' => $product['id'],
                'id'         => $product['id'],
                'name'       => $product['name'],
                'thumbnail'  => $product['thumbnail'],
                'price'      => $product['price'],
                'sale_price' => $product['sale_price'],
                'quantity'   => $qty,
                'stock'      => $product['stock'],
            ]];
            $subtotal = $price * $qty;
        } else {
            $items = $this->cartModel->getItems($userId);
            if (empty($items)) {
                setFlash('warning', 'Giỏ hàng trống');
                redirect('/cart');
                return;
            }
            $subtotal = $this->cartModel->getTotal($userId);
        }

        // Kiểm tra dữ liệu giao hàng đầu vào
        $name    = trim($_POST['shipping_name']    ?? '');
        $phone   = trim($_POST['shipping_phone']   ?? '');
        $email   = trim($_POST['shipping_email']   ?? '');
        $address = trim($_POST['shipping_address'] ?? '');
        $note    = trim($_POST['note']             ?? '');
        $payMethod    = $_POST['payment_method'] ?? 'cod';
        $voucherCode  = trim($_POST['voucher_code'] ?? '');

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

        // Tính tổng tiền và áp dụng voucher giảm giá
        $discount = 0;
        $voucherId = null;

        if ($voucherCode) {
            $apply = $this->voucherModel->apply($voucherCode, $subtotal);
            if ($apply['success']) {
                $discount  = $apply['discount'];
                $voucherId = $apply['voucher']['id'];
            }
        }

        $total = $subtotal - $discount;
        if ($total < 0) $total = 0;

        // Kiểm tra tồn kho từng sản phẩm trước khi tạo đơn
        foreach ($items as $item) {
            if ($item['stock'] < $item['quantity']) {
                setFlash('error', 'Sản phẩm "' . $item['name'] . '" chỉ còn ' . $item['stock'] . ' sản phẩm');
                redirect('/cart');
                return;
            }
        }

        // Bắt đầu transaction — đảm bảo toàn vẹn dữ liệu
        $db = db();
        $db->beginTransaction();

        try {
            // Tạo bản ghi đơn hàng
            $orderId = $this->orderModel->create([
                'order_code'       => generateOrderCode(),
                'user_id'          => $userId,
                'shipping_name'    => $name,
                'shipping_phone'   => $phone,
                'shipping_email'   => $email,
                'shipping_address' => $address,
                'note'             => $note,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discount,
                'total_amount'     => $total,
                'voucher_id'       => $voucherId,
                'payment_method'   => $payMethod,
                'payment_status'   => 'pending',
                'status'           => 'pending',
            ]);

            // Thêm chi tiết sản phẩm vào đơn và giảm tồn kho
            foreach ($items as $item) {
                $this->orderModel->addDetail($orderId, $item, $item['quantity']);
                $this->productModel->decreaseStock($item['product_id'] ?? $item['id'], $item['quantity']);
            }

            // Đánh dấu voucher đã được sử dụng
            if ($voucherId) {
                $this->voucherModel->use($voucherId);
            }

            // Cập nhật thống kê mua hàng và hạng thành viên
            $this->userModel->incrementStats($userId, $total);
            $this->userModel->updateRank($userId);

            // Tạo thông báo đặt hàng thành công cho người dùng
            $orderCode = $this->orderModel->getById($orderId)['order_code'];
            $db->insert('thong_bao', [
                'user_id' => $userId,
                'title'   => 'Đặt hàng thành công',
                'content' => 'Đơn hàng #' . $orderCode . ' đã được tạo thành công. Cảm ơn bạn đã mua hàng!',
                'type'    => 'order',
                'link'    => SITE_URL . '/order/detail/' . $orderId,
            ]);

            // Xóa giỏ hàng hoặc session mua ngay
            if ($isBuyNow) {
                unset($_SESSION['buy_now']);
            } else {
                $this->cartModel->clear($userId);
            }

            $db->commit();

            // Gửi email xác nhận đơn hàng (ngoài transaction — lỗi mail không hủy đơn)
            try {
                $orderRow = $this->orderModel->getById($orderId);
                // Địa chỉ email nhận: ưu tiên email nhập khi checkout, fallback về email tài khoản
                $toEmail = $email ?: ($db->fetch("SELECT email FROM nguoi_dung WHERE id=?", [$userId])['email'] ?? '');

                if ($toEmail) {
                    $mailResult = buildOrderConfirmEmail(
                        array_merge($orderRow, ['id' => $orderId]),
                        $items
                    );
                    sendOrderMail(
                        $toEmail, $name,
                        'Xác nhận đơn hàng #' . $orderCode,
                        $mailResult['html'],
                        $err,
                        $mailResult['images']
                    );
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