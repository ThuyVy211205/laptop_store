<?php
/**
 * Controller Trang Chủ
 * Tổng hợp dữ liệu: nổi bật, flash sale, hàng mới, bán chạy, banner
 */

require_once ROOT_PATH . '/models/Product.php';
require_once ROOT_PATH . '/models/Category.php';

class HomeController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /** Hiển thị trang chủ với sản phẩm nổi bật, flash sale và banner */
    public function index() {
        $categories     = $this->categoryModel->getAll();
        $featured       = $this->productModel->getFeatured(8);
        $flashSale      = $this->productModel->getFlashSale(8);
        $newArrivals    = $this->productModel->getNewArrivals(8);
        $bestSellers    = $this->productModel->getBestSellers(8);

        // Lấy banner hero đang active từ DB
        $banners = db()->fetchAll(
            "SELECT * FROM bang_quang_cao WHERE status = 'active' AND position = 'hero' ORDER BY sort_order ASC"
        );

        require_once ROOT_PATH . '/views/home/index.php';
    }

    /**
     * 404 Not Found
     */
    public function notFound() {
        http_response_code(404);
        require_once ROOT_PATH . '/views/404.php';
    }
}