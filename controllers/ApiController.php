<?php
/**
 * API Controller - Returns JSON
 */

require_once ROOT_PATH . '/models/Product.php';
require_once ROOT_PATH . '/models/Cart.php';
require_once ROOT_PATH . '/models/Voucher.php';
require_once ROOT_PATH . '/models/Wishlist.php';

class ApiController {
    private $productModel;
    private $cartModel;

    public function __construct() {
        header('Content-Type: application/json; charset=utf-8');
        $this->productModel = new Product();
        $this->cartModel    = new Cart();
    }

    private function json($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function getPostData() {
        $input = file_get_contents('php://input');
        $data  = json_decode($input, true);
        if (!$data) $data = $_POST;
        return $data;
    }

    /**
     * GET /api/search?q=...
     */
    public function search() {
        $keyword = trim($_GET['q'] ?? '');
        if (!$keyword) {
            $this->json(['products' => []]);
            return;
        }
        $products = $this->productModel->search($keyword, 10);
        $result = array_map(function($p) {
            return [
                'id'         => $p['id'],
                'name'       => $p['name'],
                'slug'       => $p['slug'],
                'price'      => $p['price'],
                'sale_price' => $p['sale_price'],
                'thumbnail'  => imgUrl($p['thumbnail']),
                'url'        => SITE_URL . '/product/' . $p['slug'],
            ];
        }, $products);
        $this->json(['products' => $result]);
    }

    /**
     * GET /api/cart
     */
    public function cart() {
        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $items  = $this->cartModel->getItems($userId);
        $total  = $this->cartModel->getTotal($userId);
        $count  = $this->cartModel->count($userId);

        $itemsData = array_map(function($item) {
            return [
                'id'         => $item['product_id'] ?? $item['id'],
                'name'       => $item['name'],
                'thumbnail'  => imgUrl($item['thumbnail'] ?? ''),
                'price'      => $item['sale_price'] ?: $item['price'],
                'quantity'   => $item['quantity'],
                'subtotal'   => ($item['sale_price'] ?: $item['price']) * $item['quantity'],
            ];
        }, $items);

        $this->json([
            'success' => true,
            'items'   => $itemsData,
            'total'   => $total,
            'total_formatted' => formatPrice($total),
            'count'   => (int)$count,
        ]);
    }

    /**
     * POST /api/cart/add - alias name: cartAdd
     */
    public function cartAdd() {
        $data       = $this->getPostData();
        $productId  = (int)($data['product_id'] ?? 0);
        $quantity   = (int)($data['quantity']   ?? 1);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 400);
        }

        $product = $this->productModel->getById($productId);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        if ($product['stock'] < $quantity) {
            $this->json(['success' => false, 'message' => 'Sản phẩm đã hết hàng']);
        }

        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $this->cartModel->add($productId, $quantity, $userId);

        $this->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'count'   => (int)$this->cartModel->count($userId),
        ]);
    }

    /**
     * POST /api/cart/update
     */
    public function cartUpdate() {
        $data       = $this->getPostData();
        $productId  = (int)($data['product_id'] ?? 0);
        $quantity   = (int)($data['quantity']   ?? 1);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin'], 400);
        }

        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $this->cartModel->update($productId, $quantity, $userId);

        $total = $this->cartModel->getTotal($userId);
        $this->json([
            'success' => true,
            'count'   => (int)$this->cartModel->count($userId),
            'total'   => $total,
            'total_formatted' => formatPrice($total),
        ]);
    }

    /**
     * POST /api/cart/remove
     */
    public function cartRemove() {
        $data      = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin'], 400);
        }

        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $this->cartModel->remove($productId, $userId);

        $total = $this->cartModel->getTotal($userId);
        $this->json([
            'success' => true,
            'count'   => (int)$this->cartModel->count($userId),
            'total'   => $total,
            'total_formatted' => formatPrice($total),
        ]);
    }

    /**
     * GET /api/product/{id}
     */
    public function product() {
        $segments = explode('/', trim($_GET['url'] ?? '', '/'));
        $id = (int)($segments[2] ?? 0);

        $product = $this->productModel->getById($id);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }

        $images = $this->productModel->getImages($id);
        $this->json([
            'success' => true,
            'product' => [
                'id'         => $product['id'],
                'name'       => $product['name'],
                'price'      => $product['price'],
                'sale_price' => $product['sale_price'],
                'price_formatted' => formatPrice($product['sale_price'] ?: $product['price']),
                'original_price'  => formatPrice($product['price']),
                'thumbnail'  => imgUrl($product['thumbnail']),
                'stock'      => $product['stock'],
                'rating_avg' => $product['rating_avg'],
                'slug'       => $product['slug'],
                'description'=> $product['description'],
                'url'        => SITE_URL . '/product/' . $product['slug'],
                'images'     => array_map(fn($i) => imgUrl($i['image_path']), $images),
            ],
        ]);
    }

    /**
     * POST /api/voucher/check
     */
    public function voucherCheck() {
        $data  = $this->getPostData();
        $code  = trim($data['code']  ?? '');
        $total = (float)($data['total'] ?? 0);

        if (!$code) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập mã voucher']);
        }

        $voucherModel = new Voucher();
        $result = $voucherModel->apply($code, $total);

        if ($result['success']) {
            $newTotal = $total - $result['discount'];
            $this->json([
                'success'  => true,
                'message'  => $result['message'],
                'discount' => $result['discount'],
                'discount_formatted' => formatPrice($result['discount']),
                'new_total' => $newTotal,
                'new_total_formatted' => formatPrice($newTotal),
            ]);
        } else {
            $this->json(['success' => false, 'message' => $result['message']]);
        }
    }

    /**
     * POST /api/wishlist/toggle
     */
    public function wishlistToggle() {
        if (!isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập', 'require_login' => true], 401);
        }

        $data      = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin'], 400);
        }

        $wishlistModel = new Wishlist();
        $result = $wishlistModel->toggle($_SESSION['user_id'], $productId);

        $this->json([
            'success' => true,
            'action'  => $result,
            'message' => $result === 'added' ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích',
        ]);
    }

    /**
     * POST /api/review/add
     */
    public function reviewAdd() {
        if (!isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập', 'require_login' => true], 401);
        }

        $data = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);
        $rating    = (int)($data['rating']     ?? 0);
        $content   = trim($data['content']     ?? '');

        if (!$productId || $rating < 1 || $rating > 5) {
            $this->json(['success' => false, 'message' => 'Thông tin không hợp lệ'], 400);
        }

        $this->productModel->addReview($productId, $_SESSION['user_id'], $rating, $content);
        $this->json(['success' => true, 'message' => 'Đã thêm đánh giá']);
    }

    /**
     * POST /api/comment/add
     */
    public function commentAdd() {
        if (!isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập', 'require_login' => true], 401);
        }

        $data      = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);
        $content   = trim($data['content']     ?? '');
        $parentId  = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

        if (!$productId || !$content) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập nội dung'], 400);
        }

        $commentId = $this->productModel->addComment($productId, $_SESSION['user_id'], $content, $parentId);
        $this->json(['success' => true, 'message' => 'Đã thêm bình luận', 'id' => $commentId]);
    }
}