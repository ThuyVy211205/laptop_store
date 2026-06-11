<?php
/**
 * Controller Trang Khuyến Mãi
 * Hiển thị danh sách voucher và banner khuyến mãi đang active
 */

class PromotionsController {

    public function index() {
        $pageTitle = 'Khuyến Mãi';
        $extraCss  = ['product.css', 'promotions.css'];

        // Banner promo từ DB
        $promoBanner = db()->fetch(
            "SELECT * FROM bang_quang_cao WHERE position = 'promo' AND status = 'active' ORDER BY sort_order ASC LIMIT 1"
        );

        // Vouchers đang active từ database
        $vouchers = db()->fetchAll(
            "SELECT * FROM phieu_giam_gia
             WHERE is_active = 1
               AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY value DESC"
        );

        // 10 sản phẩm giảm giá cao nhất, đủ các loại danh mục
        $allDiscounted = db()->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    ROUND((p.price - p.sale_price) / p.price * 100) AS discount_pct
             FROM san_pham p
             LEFT JOIN danh_muc c ON p.category_id = c.id
             WHERE p.sale_price IS NOT NULL
               AND p.sale_price < p.price
               AND p.status = 'active'
               AND p.stock > 0
             ORDER BY discount_pct DESC"
        );

        $categoryGroups = [
            'laptop-gaming', 'laptop-van-phong', 'macbook',
            'ban-phim-co', 'chuot-gaming', 'tai-nghe',
        ];

        $selected = $selectedIds = [];

        foreach ($categoryGroups as $slug) {
            foreach ($allDiscounted as $p) {
                if ($p['category_slug'] === $slug && !in_array($p['id'], $selectedIds)) {
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

        require_once ROOT_PATH . '/views/promotions/index.php';
    }
}
