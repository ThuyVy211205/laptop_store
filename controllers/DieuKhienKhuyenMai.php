<?php
/**
 * Điều Khiển Khuyến Mãi
 * Hiển thị danh sách voucher và banner khuyến mãi đang kích hoạt
 */

class DieuKhienKhuyenMai {

    public function index() {
        $pageTitle = 'Khuyến Mãi';
        $extraCss  = ['product.css', 'promotions.css'];

        $promoBanner = db()->fetch(
            "SELECT * FROM bang_quang_cao WHERE vi_tri = 'promo' AND trang_thai = 'active' ORDER BY thu_tu ASC LIMIT 1"
        );

        $vouchers = db()->fetchAll(
            "SELECT * FROM phieu_giam_gia
             WHERE dang_hoat_dong = 1
               AND (ngay_het_han IS NULL OR ngay_het_han > NOW())
             ORDER BY gia_tri DESC"
        );

        $allDiscounted = db()->fetchAll(
            "SELECT p.*, c.ten AS ten_danh_muc, c.duong_dan AS duong_dan_danh_muc,
                    ROUND((p.gia - p.gia_khuyen_mai) / p.gia * 100) AS pct_giam
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
             WHERE p.gia_khuyen_mai IS NOT NULL
               AND p.gia_khuyen_mai < p.gia
               AND p.trang_thai = 'active'
               AND p.ton_kho > 0
             ORDER BY pct_giam DESC"
        );

        $categoryGroups = [
            'laptop-gaming', 'laptop-van-phong', 'macbook',
            'ban-phim-co', 'chuot-gaming', 'tai-nghe',
        ];

        $selected = $selectedIds = [];

        foreach ($categoryGroups as $slug) {
            foreach ($allDiscounted as $p) {
                if ($p['duong_dan_danh_muc'] === $slug && !in_array($p['id'], $selectedIds)) {
                    $selected[]    = $p;
                    $selectedIds[] = $p['id'];
                    break;
                }
            }
        }

        foreach ($allDiscounted as $p) {
            if (count($selected) >= 12) break;
            if (!in_array($p['id'], $selectedIds)) {
                $selected[]    = $p;
                $selectedIds[] = $p['id'];
            }
        }

        shuffle($selected);
        $products = $selected;

        require_once ROOT_PATH . '/views/khuyen_mai/khuyen_mai.php';
    }
}
