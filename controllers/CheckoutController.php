<?php
/**
 * Checkout Controller
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

    /**
     * Checkout page
     */
    public function index() {
        requireLogin();

        $userId = $_SESSION['user_id'];
        $items  = $this->cartModel->getItems($userId);

        if (empty($items)) {
            setFlash('warning', 'Giỏ hàng của bạn đang trống');
            redirect('/cart');
            return;
        }

        $user      = $this->userModel->getById($userId);
        $subtotal  = $this->cartModel->getTotal($userId);
        $pageTitle = 'Thanh toán';

        require_once ROOT_PATH . '/views/checkout/index.php';
    }

    /**
     * Place order (POST)
     */
    public function place() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/checkout');
            return;
        }

        $userId = $_SESSION['user_id'];
        $items  = $this->cartModel->getItems($userId);

        if (empty($items)) {
            setFlash('warning', 'Giỏ hàng trống');
            redirect('/cart');
            return;
        }

        // Validate input
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
            setFlash('error', 'Số điện thoại không hợp lệ');
            redirect('/checkout');
            return;
        }

        // Calculate totals
        $subtotal = $this->cartModel->getTotal($userId);
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

        // Check stock availability
        foreach ($items as $item) {
            if ($item['stock'] < $item['quantity']) {
                setFlash('error', 'Sản phẩm "' . $item['name'] . '" chỉ còn ' . $item['stock'] . ' sản phẩm');
                redirect('/cart');
                return;
            }
        }

        // Begin transaction
        $db = db();
        $db->beginTransaction();

        try {
            // Create order
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
                'voucher_code'     => $voucherCode ?: null,
                'payment_method'   => $payMethod,
                'payment_status'   => 'pending',
                'status'           => 'pending',
            ]);

            // Add order details + decrease stock
            foreach ($items as $item) {
                $this->orderModel->addDetail($orderId, $item, $item['quantity']);
                $this->productModel->decreaseStock($item['id'], $item['quantity']);
            }

            // Use voucher
            if ($voucherId) {
                $this->voucherModel->use($voucherId);
            }

            // Update user stats & rank
            $this->userModel->incrementStats($userId, $total);
            $this->userModel->updateRank($userId);

            // Notification
            $db->insert('notifications', [
                'user_id' => $userId,
                'title'   => 'Đặt hàng thành công',
                'content' => 'Đơn hàng của bạn đã được tạo. Mã đơn: ' . $this->orderModel->getById($orderId)['order_code'],
                'type'    => 'order',
                'link'    => '/order/detail/' . $orderId,
            ]);

            // Clear cart
            $this->cartModel->clear($userId);

            $db->commit();

            redirect('/order/success/' . $orderId);
        } catch (Exception $e) {
            $db->rollback();
            setFlash('error', 'Đặt hàng thất bại: ' . $e->getMessage());
            redirect('/checkout');
        }
    }
}