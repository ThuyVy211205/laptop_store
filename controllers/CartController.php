<?php
/**
 * Cart Controller
 */

require_once ROOT_PATH . '/models/Cart.php';

class CartController {
    private $cartModel;

    public function __construct() {
        $this->cartModel = new Cart();
    }

    /**
     * Cart page
     */
    public function index() {
        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $items  = $this->cartModel->getItems($userId);
        $total  = $this->cartModel->getTotal($userId);

        $pageTitle = 'Giỏ hàng';
        require_once ROOT_PATH . '/views/cart/index.php';
    }
}