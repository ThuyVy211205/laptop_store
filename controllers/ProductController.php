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
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Product listing — laptop or accessories typed pages
     */
    public function index() {
        $search    = trim($_GET['q'] ?? '');
        $flashSale = !empty($_GET['flash_sale']);
        $catParam  = $_GET['category'] ?? null;

        // Flat search / flash-sale mode (unchanged)
        if ($search !== '' || $flashSale || $catParam) {
            $filters = [
                'search'      => $search,
                'category_id' => $catParam,
                'min_price'   => $_GET['min_price'] ?? null,
                'max_price'   => $_GET['max_price'] ?? null,
                'sort'        => $_GET['sort'] ?? 'newest',
                'brand'       => $_GET['brand'] ?? null,
            ];
            if ($flashSale) $filters['flash_sale'] = 1;

            $page       = max(1, (int)($_GET['page'] ?? 1));
            $perPage    = ITEMS_PER_PAGE;
            $offset     = ($page - 1) * $perPage;
            $products   = $this->productModel->getAll($filters, $perPage, $offset);
            $totalCount = $this->productModel->countAll($filters);
            $totalPages = ceil($totalCount / $perPage);

            $allCats = $this->categoryModel->getAll();
            $pageTitle          = $search !== ''
                ? 'Kết quả tìm kiếm: "' . htmlspecialchars($search) . '"'
                : ($flashSale ? 'Khuyến mãi' : 'Sản phẩm');
            $pageSubtitle       = '';
            $pageType           = 'search';
            $currentSort        = $filters['sort'];
            $selectedCategories = [];
            $selectedPriceRanges = [];
            $sidebarCategories  = $this->_getLaptopCategories($allCats);

            require_once ROOT_PATH . '/views/products/list.php';
            return;
        }

        // Typed listing: laptop (default) or phu-kien
        $type    = $_GET['type'] ?? 'laptop';
        $allCats = $this->categoryModel->getAll();

        $selectedCategories  = array_map('intval', (array)($_GET['categories'] ?? []));
        $selectedPriceRanges = (array)($_GET['price_ranges'] ?? []);
        $currentSort         = $_GET['sort'] ?? 'newest';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;

        [$minPrice, $maxPrice] = $this->_parsePriceRanges($selectedPriceRanges);

        if ($type === 'phu-kien') {
            $sidebarCategories = $this->_getAccCategories($allCats);
            $filters           = $this->_buildFilters(
                $selectedCategories,
                $sidebarCategories,
                $currentSort,
                $minPrice,
                $maxPrice
            );

            $products   = $this->productModel->getAll($filters, $perPage, $offset);
            $totalCount = $this->productModel->countAll($filters);
            $totalPages = ceil($totalCount / $perPage);
            $pageTitle    = 'Phụ kiện';
            $pageSubtitle = 'Khám phá tất cả phụ kiện công nghệ tại TechStore';
            $pageType     = 'phu-kien';

            require_once ROOT_PATH . '/views/products/accessories.php';
            return;
        }

        // Default: laptop
        $sidebarCategories = $this->_getLaptopCategories($allCats);
        $filters           = $this->_buildFilters(
            $selectedCategories,
            $sidebarCategories,
            $currentSort,
            $minPrice,
            $maxPrice
        );

        $products   = $this->productModel->getAll($filters, $perPage, $offset);
        $totalCount = $this->productModel->countAll($filters);
        $totalPages = ceil($totalCount / $perPage);
        $pageTitle    = 'Laptop';
        $pageSubtitle = 'Khám phá tất cả các sản phẩm Laptop hiện có tại TechStore';
        $pageType     = 'laptop';

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

        $this->productModel->incrementView($product['id']);

        $images   = $this->productModel->getImages($product['id']);
        $reviews  = $this->productModel->getReviews($product['id']);
        $comments = $this->productModel->getComments($product['id']);
        $related  = $this->productModel->getRelated($product['id'], $product['category_id'], 4);

        $specs = [];
        if (!empty($product['specs'])) {
            $decoded = json_decode($product['specs'], true);
            if (is_array($decoded)) $specs = $decoded;
        }

        $colorVariants = $this->productModel->getColorVariants(
            $product['id'], $product['category_id'], $product['brand'] ?? ''
        );

        $inWishlist = false;
        if (isLoggedIn()) {
            require_once ROOT_PATH . '/models/Wishlist.php';
            $wishlist   = new Wishlist();
            $inWishlist = $wishlist->isInWishlist($_SESSION['user_id'], $product['id']);
        }

        $pageTitle = $product['name'];
        require_once ROOT_PATH . '/views/products/detail.php';
    }

    /**
     * AJAX: Quick view modal content
     */
    public function quickview($id) {
        $product = $this->productModel->getById($id);
        if (!$product) {
            http_response_code(404);
            echo '<p class="text-center py-4">Không tìm thấy sản phẩm</p>';
            exit;
        }
        $images = $this->productModel->getImages($product['id']);
        require_once ROOT_PATH . '/views/products/_quickview.php';
        exit;
    }

    /**
     * AJAX: Search autocomplete suggestions
     */
    public function searchSuggest() {
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode([]);
            exit;
        }
        $results = $this->productModel->search($q, 6);
        $out = array_map(function ($p) {
            return [
                'name'            => $p['name'],
                'slug'            => $p['slug'],
                'thumbnail'       => imgUrl($p['thumbnail']),
                'price_formatted' => formatPrice($p['sale_price'] ?: $p['price']),
            ];
        }, $results);
        echo json_encode($out);
        exit;
    }

    /**
     * Products by category slug
     */
    public function category($slug) {
        $category = $this->categoryModel->getBySlug($slug);
        if (!$category) {
            redirect('/404');
            return;
        }

        $currentSort         = $_GET['sort'] ?? 'newest';
        $selectedCategories  = array_map('intval', (array)($_GET['categories'] ?? [$category['id']]));
        $selectedPriceRanges = (array)($_GET['price_ranges'] ?? []);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;

        [$minPrice, $maxPrice] = $this->_parsePriceRanges($selectedPriceRanges);
        // also respect legacy min_price / max_price query params
        if ($minPrice === null && !empty($_GET['min_price'])) $minPrice = (int)$_GET['min_price'];
        if ($maxPrice === null && !empty($_GET['max_price'])) $maxPrice = (int)$_GET['max_price'];

        $filters = ['category_ids' => $selectedCategories, 'sort' => $currentSort];
        if ($minPrice !== null) $filters['min_price'] = $minPrice;
        if ($maxPrice !== null) $filters['max_price'] = $maxPrice;

        $products   = $this->productModel->getAll($filters, $perPage, $offset);
        $totalCount = $this->productModel->countAll($filters);
        $totalPages = ceil($totalCount / $perPage);

        $allCats = $this->categoryModel->getAll();
        $isAcc   = (bool) preg_match(
            '/chu[oô]t|tai[\s\-]?nghe|b[àa]n[\s\-]?ph[íi]m|ph[ụu][\s\-]?ki[eê]n/',
            strtolower($category['name'] . ' ' . $category['slug'])
        );
        $sidebarCategories = $isAcc
            ? $this->_getAccCategories($allCats)
            : $this->_getLaptopCategories($allCats);
        if (empty($sidebarCategories)) $sidebarCategories = $allCats;

        $pageTitle    = $category['name'];
        $pageSubtitle = 'Xem tất cả sản phẩm trong danh mục ' . $category['name'];
        $pageType     = $isAcc ? 'phu-kien' : 'laptop';

        require_once ROOT_PATH . '/views/products/list.php';
    }

    // ── Private helpers ────────────────────────────────────────

    private function _getLaptopCategories(array $allCats): array {
        return array_values(array_filter($allCats, fn($c) =>
            stripos($c['name'] . ' ' . $c['slug'], 'laptop') !== false ||
            stripos($c['name'] . ' ' . $c['slug'], 'macbook') !== false
        ));
    }

    private function _getAccCategories(array $allCats): array {
        return array_values(array_filter($allCats, fn($c) =>
            (bool) preg_match(
                '/chu[oô]t|tai[\s\-]?nghe|b[àa]n[\s\-]?ph[íi]m|ph[ụu][\s\-]?ki[eê]n/',
                strtolower($c['name'] . ' ' . $c['slug'])
            )
        ));
    }

    /**
     * Parse price_ranges[] → [minPrice, maxPrice]
     * e.g. ['0-10000000', '10000000-20000000', '20000000+']
     */
    private function _parsePriceRanges(array $ranges): array {
        if (empty($ranges)) return [null, null];
        $mins = [];
        $maxes = [];
        foreach ($ranges as $r) {
            if (strpos($r, '+') !== false) {
                $mins[] = (int) str_replace('+', '', $r);
            } else {
                $parts = explode('-', $r);
                $mins[] = (int)($parts[0] ?? 0);
                if (isset($parts[1]) && (int)$parts[1] > 0) {
                    $maxes[] = (int)$parts[1];
                }
            }
        }
        $minPrice = !empty($mins)  ? min($mins)  : null;
        $maxPrice = !empty($maxes) ? max($maxes) : null;
        return [$minPrice, $maxPrice];
    }

    /**
     * Build product filter array.
     * If no categories selected, fall back to all sidebar category IDs.
     */
    private function _buildFilters(
        array $selectedCats,
        array $sidebarCats,
        string $sort,
        ?int $minPrice,
        ?int $maxPrice
    ): array {
        $filters = ['sort' => $sort];

        if (!empty($selectedCats)) {
            $filters['category_ids'] = $selectedCats;
        } else {
            $ids = array_column($sidebarCats, 'id');
            if (!empty($ids)) $filters['category_ids'] = $ids;
        }
        if ($minPrice !== null) $filters['min_price'] = $minPrice;
        if ($maxPrice !== null) $filters['max_price'] = $maxPrice;

        return $filters;
    }
}
