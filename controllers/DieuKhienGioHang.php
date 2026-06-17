<?php
/**
 * Điều Khiển Giỏ Hàng
 * Xử lý xem giỏ, thêm/xóa/cập nhật sản phẩm, mua ngay và mini cart sidebar
 */

require_once ROOT_PATH . '/models/GioHang.php';

class DieuKhienGioHang {
    private $gioHangModel;

    public function __construct() {
        $this->gioHangModel = new GioHang();
    }

    /** Hiển thị trang giỏ hàng */
    public function index() {
        unset($_SESSION['buy_now']);

        $userId = isLoggedIn() ? $_SESSION['id_nguoi_dung'] : null;
        $items  = $this->gioHangModel->getItems($userId);
        $total  = $this->gioHangModel->getTotal($userId);

        $pageTitle = 'Giỏ hàng';
        require_once ROOT_PATH . '/views/gio_hang/gio_hang.php';
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

        $userId = isLoggedIn() ? $_SESSION['id_nguoi_dung'] : null;
        $result = $this->gioHangModel->add($productId, $quantity, $userId);
        if (!$result) {
            echo json_encode([
                'success'    => false,
                'message'    => 'Số lượng sản phẩm trong giỏ vượt quá tồn kho hiện có',
            ]);
            exit;
        }

        $count  = $this->gioHangModel->count($userId);
        echo json_encode([
            'success'    => true,
            'cart_count' => (int)$count,
            'message'    => 'Đã thêm vào giỏ hàng',
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

        $userId = isLoggedIn() ? $_SESSION['id_nguoi_dung'] : null;
        $this->gioHangModel->remove($productId, $userId);
        $count = $this->gioHangModel->count($userId);
        $total = $this->gioHangModel->getTotal($userId);

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

        $product = db()->fetch(
            "SELECT ton_kho, duong_dan FROM san_pham WHERE id = ? AND trang_thai = 'active'",
            [$productId]
        );
        if (!$product) {
            redirect('/products');
            return;
        }

        // Kiểm tra số lượng hiện có trong giỏ hàng của cùng một sản phẩm.
        $gioHang = new GioHang();
        $userId  = $_SESSION['id_nguoi_dung'];
        $items   = $gioHang->getItems($userId);
        $trongGio = 0;
        foreach ($items as $item) {
            if (($item['id_san_pham'] ?? $item['id']) == $productId) {
                $trongGio = (int)($item['so_luong'] ?? 0);
                break;
            }
        }

        $totalQty = $trongGio + $quantity;
        if ($totalQty > $product['ton_kho']) {
            setFlash('error', 'Bạn đã có 50 sản phẩm này trong giỏ hàng.
                               Tổng số lượng yêu cầu (51) vượt quá tồn kho hiện có  (' . $product['ton_kho'] . ')');
            redirect('/product/' . $product['duong_dan']);
            return;
        }

        $_SESSION['buy_now'] = [
            'id_san_pham' => $productId,
            'quantity'   => $quantity,
        ];

        redirect('/checkout');
    }

    /** AJAX: Lấy HTML mini cart để hiển thị trên sidebar giỏ hàng */
    public function sidebar($param = null) {
        header('Content-Type: application/json');
        $userId = isLoggedIn() ? $_SESSION['id_nguoi_dung'] : null;
        $items  = $this->gioHangModel->getItems($userId);
        $total  = $this->gioHangModel->getTotal($userId);

        ob_start();
        if (empty($items)) {
            echo '<div class="text-center py-5" style="color:var(--color-text-3)">'
               . '<i class="fas fa-shopping-bag fa-3x mb-3 d-block" style="color:var(--color-border)"></i>'
               . '<p class="mb-0">Giỏ hàng của bạn đang trống</p>'
               . '<a href="' . SITE_URL . '/products" style="font-size:13px;color:var(--color-primary)">Xem sản phẩm</a>'
               . '</div>';
        } else {
            foreach ($items as $item) {
                $price    = $item['gia_khuyen_mai'] ?: $item['gia'];
                $imgSrc   = imgUrl($item['hinh_thu_nho']);
                $name     = htmlspecialchars($item['ten'] ?? '');
                $qty      = (int)($item['so_luong'] ?? 1);
                $priceStr = formatPrice($price * $qty);
                $pid      = (int)($item['id_san_pham'] ?? $item['id'] ?? 0);
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
