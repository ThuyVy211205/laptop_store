<?php
/**
 * Điều Khiển Trang Chủ
 * Tổng hợp dữ liệu: nổi bật, flash sale, hàng mới, bán chạy, banner
 */

require_once ROOT_PATH . '/models/SanPham.php';
require_once ROOT_PATH . '/models/DanhMuc.php';

class DieuKhienTrangChu {
    private $sanPhamModel;
    private $danhMucModel;

    public function __construct() {
        $this->sanPhamModel = new SanPham();
        $this->danhMucModel = new DanhMuc();
    }

    /** Hiển thị trang chủ với sản phẩm nổi bật, flash sale và banner */
    public function index() {
        $categories  = $this->danhMucModel->getAll();
        $featured    = $this->sanPhamModel->getFeatured(8);
        $flashSale   = $this->sanPhamModel->getFlashSale(8);
        $bestSellers = $this->sanPhamModel->getBestSellers(8);

        $banners = db()->fetchAll(
            "SELECT * FROM bang_quang_cao WHERE trang_thai = 'active' AND vi_tri = 'hero' ORDER BY thu_tu ASC"
        );

        require_once ROOT_PATH . '/views/trang_chu/trang_chu.php';
    }

    /** 404 Not Found */
    public function notFound() {
        http_response_code(404);
        require_once ROOT_PATH . '/views/loi_404.php';
    }
}
