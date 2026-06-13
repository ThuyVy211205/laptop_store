<?php
/**
 * Điều Khiển Danh Sách Yêu Thích
 * Xem và xóa sản phẩm trong danh sách yêu thích
 */

require_once ROOT_PATH . '/models/YeuThich.php';

class DieuKhienYeuThich {
    private $yeuThichModel;

    public function __construct() {
        $this->yeuThichModel = new YeuThich();
    }

    /** Hiển thị trang sản phẩm yêu thích */
    public function index() {
        requireLogin();
        $items     = $this->yeuThichModel->getByUser($_SESSION['id_nguoi_dung']);
        $pageTitle = 'Sản phẩm yêu thích';
        require_once ROOT_PATH . '/views/tai_khoan/yeu_thich.php';
    }

    /** Xóa sản phẩm khỏi danh sách yêu thích, chuyển về trang wishlist */
    public function remove($productId) {
        requireLogin();
        $this->yeuThichModel->remove($_SESSION['id_nguoi_dung'], $productId);
        setFlash('success', 'Đã xóa khỏi danh sách yêu thích');
        redirect('/wishlist');
    }
}
