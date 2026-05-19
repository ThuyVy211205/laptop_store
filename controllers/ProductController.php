<?php
/**
 * Product Controller
 */

require_once ROOT_PATH . '/models/Product.php';
require_once ROOT_PATH . '/models/Category.php';

class ProductController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Product listing with filters
     */
    public function index() {
        $filters = [
            'search'      => $_GET['q']     ?? '',
            'category_id' => $_GET['category'] ?? null,
            'min_price'   => $_GET['min_price'] ?? null,
            'max_price'   => $_GET['max_price'] ?? null,
            'sort'        => $_GET['sort']  ?? 'newest',
            'brand'       => $_GET['brand'] ?? null,
        ];

        // Pagination
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;

        $products    = $this->productModel->getAll($filters, $perPage, $offset);
        $totalCount  = $this->productModel->countAll($filters);
        $totalPages  = ceil($totalCount / $perPage);
        $categories  = $this->categoryModel->getAll();

        $pageTitle = !empty($filters['search'])
            ? 'Kết quả tìm kiếm: "' . htmlspecialchars($filters['search']) . '"'
            : 'Tất cả sản phẩm';

        require_once ROOT_PATH . '/views/products/list.php';
    }

    /**
     * Product detail
     */
    public function detail($slug) {
        $product = $this->productModel->getBySlug($slug);
        if (!$product) {
            redirect('/404');
            return;
        }

        // Increment view counter
        $this->productModel->incrementView($product['id']);

        $images   = $this->productModel->getImages($product['id']);
        $reviews  = $this->productModel->getReviews($product['id']);
        $comments = $this->productModel->getComments($product['id']);
        $related  = $this->productModel->getRelated($product['id'], $product['category_id'], 4);

        // Parse specs JSON
        $specs = [];
        if (!empty($product['specs'])) {
            $decoded = json_decode($product['specs'], true);
            if (is_array($decoded)) $specs = $decoded;
        }

        // Check wishlist
        $inWishlist = false;
        if (isLoggedIn()) {
            require_once ROOT_PATH . '/models/Wishlist.php';
            $wishlist = new Wishlist();
            $inWishlist = $wishlist->isInWishlist($_SESSION['user_id'], $product['id']);
        }

        $pageTitle = $product['name'];
        require_once ROOT_PATH . '/views/products/detail.php';
    }

    /**
     * Products by category
     */
    public function category($slug) {
        $category = $this->categoryModel->getBySlug($slug);
        if (!$category) {
            redirect('/404');
            return;
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;
        $sort    = $_GET['sort'] ?? 'newest';

        $products   = $this->productModel->getByCategory($category['id'], $perPage, $offset, $sort);
        $totalCount = $this->productModel->countByCategory($category['id']);
        $totalPages = ceil($totalCount / $perPage);
        $categories = $this->categoryModel->getAll();

        $pageTitle = $category['name'];
        require_once ROOT_PATH . '/views/products/list.php';
    }
}