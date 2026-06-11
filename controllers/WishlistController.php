<?php
/**
 * Controller Danh Sách Yêu Thích
 * Xem và xóa sản phẩm trong danh sách yêu thích
 */

require_once ROOT_PATH . '/models/Wishlist.php';

class WishlistController {
    private $wishlistModel;

    public function __construct() {
        $this->wishlistModel = new Wishlist();
    }

    /** Hiển thị trang sản phẩm yêu thích */
    public function index() {
        requireLogin();
        $items = $this->wishlistModel->getByUser($_SESSION['user_id']);
        $pageTitle = 'Sản phẩm yêu thích';
        require_once ROOT_PATH . '/views/account/wishlist.php';
    }

    /** Xóa sản phẩm khỏi danh sách yêu thích, chuyển về trang wishlist */
    public function remove($productId) {
        requireLogin();
        $this->wishlistModel->remove($_SESSION['user_id'], $productId);
        setFlash('success', 'Đã xóa khỏi danh sách yêu thích');
        redirect('/wishlist');
    }
}