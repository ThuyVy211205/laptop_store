<?php
/**
 * Order Controller
 */

require_once ROOT_PATH . '/models/Order.php';
require_once ROOT_PATH . '/models/User.php';

class OrderController {
    private $orderModel;
    private $userModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->userModel  = new User();
    }

    /**
     * User's orders list
     */
    public function index() {
        requireLogin();

        $status = $_GET['status'] ?? null;
        $orders = $this->orderModel->getByUser($_SESSION['user_id'], $status);

        $orderIds    = array_column($orders, 'id');
        $itemsByOrder = $this->orderModel->getItemsByOrderIds($orderIds);
        foreach ($orders as &$order) {
            $order['items'] = $itemsByOrder[$order['id']] ?? [];
        }
        unset($order);

        $pageTitle = 'Đơn hàng của tôi';
        require_once ROOT_PATH . '/views/account/orders.php';
    }

    /**
     * Order detail
     */
    public function detail($id) {
        requireLogin();

        $order = $this->orderModel->getById($id);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            redirect('/account/orders');
            return;
        }

        $details   = $this->orderModel->getDetails($id);
        $pageTitle = 'Chi tiết đơn hàng #' . $order['order_code'];
        require_once ROOT_PATH . '/views/account/order-detail.php';
    }

    /**
     * Order success page
     */
    public function success($id) {
        requireLogin();

        $order = $this->orderModel->getById($id);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            redirect('/account/orders');
            return;
        }

        $details   = $this->orderModel->getDetails($id);
        $pageTitle = 'Đặt hàng thành công';
        require_once ROOT_PATH . '/views/checkout/success.php';
    }

    /**
     * Cancel order
     */
    public function cancel($id) {
        requireLogin();

        $order = $this->orderModel->getById($id);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            redirect('/account/orders');
            return;
        }

        if ($order['status'] !== 'pending') {
            setFlash('error', 'Chỉ có thể hủy đơn hàng đang chờ xác nhận');
            redirect('/order/detail/' . $id);
            return;
        }

        $reason = $_POST['reason'] ?? 'Khách hàng tự hủy';
        $this->orderModel->cancel($id, $reason);

        // Hoàn lại chi tiêu và số đơn, cập nhật lại rank
        $this->userModel->decrementStats($order['user_id'], $order['total_amount']);
        $this->userModel->updateRank($order['user_id']);

        db()->insert('notifications', [
            'user_id' => $order['user_id'],
            'title'   => 'Đơn hàng đã bị hủy',
            'content' => 'Đơn hàng #' . $order['order_code'] . ' đã bị hủy. Lý do: ' . $reason,
            'type'    => 'order',
            'link'    => SITE_URL . '/order/detail/' . $id,
        ]);

        setFlash('success', 'Đã hủy đơn hàng thành công');
        redirect('/order/detail/' . $id);
    }
}