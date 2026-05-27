<?php
/**
 * Promotions Controller
 */

require_once ROOT_PATH . '/models/Product.php';

class PromotionsController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    public function index() {
        $pageTitle = 'Khuyến Mãi';
        $extraCss  = ['home.css', 'promotions.css'];

        $flashProducts = $this->productModel->getFlashSale(4);

        $gamingProducts = db()->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE (c.slug LIKE '%gaming%' OR c.name LIKE '%Gaming%')
               AND p.stock > 0
             ORDER BY p.created_at DESC LIMIT 4"
        );

        $officeProducts = db()->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE (c.slug LIKE '%laptop%' AND c.slug NOT LIKE '%gaming%')
               AND p.stock > 0
             ORDER BY p.created_at DESC LIMIT 4"
        );

        $accessoryProducts = db()->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE (c.slug LIKE '%phu-kien%'
                    OR c.name LIKE '%chuột%'
                    OR c.name LIKE '%tai nghe%'
                    OR c.name LIKE '%bàn phím%')
               AND p.stock > 0
             ORDER BY p.created_at DESC LIMIT 4"
        );

        // Fallback: if any tab is empty, fill with featured products
        if (empty($gamingProducts))    $gamingProducts    = $this->productModel->getFeatured(4);
        if (empty($officeProducts))    $officeProducts    = $this->productModel->getNewArrivals(4);
        if (empty($accessoryProducts)) $accessoryProducts = $this->productModel->getBestSellers(4);

        require_once ROOT_PATH . '/views/promotions/index.php';
    }
}
