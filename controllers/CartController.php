<?php
/**
 * Controller Giỏ Hàng
 * Xử lý xem giỏ, thêm/xóa/cập nhật sản phẩm, mua ngay và mini cart sidebar
 */

require_once ROOT_PATH . '/models/Cart.php';

class CartController {
    private $cartModel;

    public function __construct() {
        $this->cartModel = new Cart();
    }

    /** Hiển thị trang giỏ hàng */
    public function index() {
        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $items  = $this->cartModel->getItems($userId);
        $total  = $this->cartModel->getTotal($userId);

        $pageTitle = 'Giỏ hàng';
        require_once ROOT_PATH . '/views/cart/index.php';
    }

    /** AJAX: Thêm sản phẩm vào giỏ hàng */
    public function add($param = null) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
            exit;
        }

        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $result = $this->cartModel->add($productId, $quantity, $userId);
        $count  = $this->cartModel->count($userId);

        echo json_encode([
            'success'    => (bool)$result,
            'cart_count' => (int)$count,
            'message'    => $result ? 'Đã thêm vào giỏ hàng' : 'Có lỗi xảy ra',
        ]);
        exit;
    }

    /** AJAX: Xóa sản phẩm khỏi giỏ hàng */
    public function remove($param = null) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        if (!$productId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $this->cartModel->remove($productId, $userId);
        $count = $this->cartModel->count($userId);
        $total = $this->cartModel->getTotal($userId);

        echo json_encode([
            'success'    => true,
            'cart_count' => (int)$count,
            'total'      => formatPrice($total),
        ]);
        exit;
    }

    /** Mua ngay — lưu sản phẩm vào session và chuyển thẳng đến trang thanh toán */
    public function buynow($param = null) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/products');
            return;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

        if (!$productId) {
            redirect('/products');
            return;
        }

        $_SESSION['buy_now'] = [
            'product_id' => $productId,
            'quantity'   => $quantity,
        ];

        redirect('/checkout');
    }

    /** AJAX: Lấy HTML mini cart để hiển thị trên sidebar giỏ hàng */
    public function sidebar($param = null) {
        header('Content-Type: application/json');
        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        $items  = $this->cartModel->getItems($userId);
        $total  = $this->cartModel->getTotal($userId);

        ob_start();
        if (empty($items)) {
            echo '<div class="text-center py-5" style="color:var(--color-text-3)">'
               . '<i class="fas fa-shopping-bag fa-3x mb-3 d-block" style="color:var(--color-border)"></i>'
               . '<p class="mb-0">Giỏ hàng của bạn đang trống</p>'
               . '<a href="' . SITE_URL . '/products" style="font-size:13px;color:var(--color-primary)">Xem sản phẩm</a>'
               . '</div>';
        } else {
            foreach ($items as $item) {
                $price    = $item['sale_price'] ?: $item['price'];
                $imgSrc   = imgUrl($item['thumbnail']);
                $name     = htmlspecialchars($item['name'] ?? '');
                $qty      = (int)($item['quantity'] ?? 1);
                $priceStr = formatPrice($price * $qty);
                $pid      = (int)$item['product_id'];
                echo '<div class="cart-mini-item">'
                   . '<img src="' . $imgSrc . '" alt="' . $name . '" onerror="this.style.display=\'none\'">'
                   . '<div class="cart-mini-info">'
                   . '<div class="name">' . $name . '</div>'
                   . '<div class="price">' . $priceStr . ' &times; ' . $qty . '</div>'
                   . '</div>'
                   . '<button class="btn-remove-mini" data-id="' . $pid . '" title="Xóa">'
                   . '<i class="fas fa-times"></i>'
                   . '</button>'
                   . '</div>';
            }
        }
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html'    => $html,
            'total'   => formatPrice($total),
            'count'   => count($items),
        ]);
        exit;
    }
}