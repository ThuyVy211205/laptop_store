<?php
/**
 * Điều Khiển API — trả về JSON
 */

require_once ROOT_PATH . '/models/SanPham.php';
require_once ROOT_PATH . '/models/GioHang.php';
require_once ROOT_PATH . '/models/PhieuGiamGia.php';
require_once ROOT_PATH . '/models/YeuThich.php';

class DieuKhienAPI {
    private $sanPhamModel;
    private $gioHangModel;

    public function __construct() {
        header('Content-Type: application/json; charset=utf-8');
        $this->sanPhamModel = new SanPham();
        $this->gioHangModel = new GioHang();
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

    /** GET /api/search?q=... */
    public function search() {
        $keyword = trim($_GET['q'] ?? '');
        if (!$keyword) {
            $this->json(['products' => []]);
            return;
        }
        $products = $this->sanPhamModel->search($keyword, 10);
        $result = array_map(function($p) {
            return [
                'id'             => $p['id'],
                'ten'            => $p['ten'],
                'duong_dan'      => $p['duong_dan'],
                'gia'            => $p['gia'],
                'gia_khuyen_mai' => $p['gia_khuyen_mai'],
                'hinh_thu_nho'   => imgUrl($p['hinh_thu_nho']),
                'url'            => SITE_URL . '/product/' . $p['duong_dan'],
            ];
        }, $products);
        $this->json(['products' => $result]);
    }

    /** GET /api/cart */
    public function cart() {
        $userId = isLoggedIn() ? $_SESSION['id_nguoi_dung'] : null;
        $items  = $this->gioHangModel->getItems($userId);
        $total  = $this->gioHangModel->getTotal($userId);
        $count  = $this->gioHangModel->count($userId);

        $itemsData = array_map(function($item) {
            return [
                'id'         => $item['id_san_pham'] ?? $item['id'],
                'ten'        => $item['ten'],
                'hinh_thu_nho' => imgUrl($item['hinh_thu_nho'] ?? ''),
                'gia'        => $item['gia_khuyen_mai'] ?: $item['gia'],
                'so_luong'   => $item['so_luong'],
                'tam_tinh'   => ($item['gia_khuyen_mai'] ?: $item['gia']) * $item['so_luong'],
            ];
        }, $items);

        $this->json([
            'success'         => true,
            'items'           => $itemsData,
            'tong'            => $total,
            'tong_dinh_dang'  => formatPrice($total),
            'so_luong'        => (int)$count,
        ]);
    }

    /** POST /api/cart/add */
    public function cartAdd() {
        $data      = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);
        $quantity  = (int)($data['quantity']  ?? 1);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 400);
        }

        $product = $this->sanPhamModel->getById($productId);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $userId = isLoggedIn() ? $_SESSION['id_nguoi_dung'] : null;
        $result = $this->gioHangModel->add($productId, $quantity, $userId);

        if (!$result) {
            $this->json(['success' => false, 'message' => 'Số lượng sản phẩm trong giỏ vượt quá tồn kho (' . $product['ton_kho'] . ')']);
        }

        $this->json([
            'success'  => true,
            'message'  => 'Đã thêm vào giỏ hàng',
            'so_luong' => (int)$this->gioHangModel->count($userId),
        ]);
    }

    /** POST /api/cart/update */
    public function cartUpdate() {
        $data      = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);
        $quantity  = (int)($data['quantity']  ?? 1);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin'], 400);
        }

        $userId = isLoggedIn() ? $_SESSION['id_nguoi_dung'] : null;
        $result = $this->gioHangModel->update($productId, $quantity, $userId);

        if (!$result) {
            $this->json(['success' => false, 'message' => 'Số lượng vượt quá tồn kho hiện có']);
        }

        $total = $this->gioHangModel->getTotal($userId);
        $this->json([
            'success'        => true,
            'so_luong'       => (int)$this->gioHangModel->count($userId),
            'tong'           => $total,
            'tong_dinh_dang' => formatPrice($total),
        ]);
    }

    /** POST /api/cart/remove */
    public function cartRemove() {
        $data      = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin'], 400);
        }

        $userId = isLoggedIn() ? $_SESSION['id_nguoi_dung'] : null;
        $this->gioHangModel->remove($productId, $userId);

        $total = $this->gioHangModel->getTotal($userId);
        $this->json([
            'success'        => true,
            'so_luong'       => (int)$this->gioHangModel->count($userId),
            'tong'           => $total,
            'tong_dinh_dang' => formatPrice($total),
        ]);
    }

    /** GET /api/product/{id} */
    public function product() {
        $segments = explode('/', trim($_GET['url'] ?? '', '/'));
        $id = (int)($segments[2] ?? 0);

        $product = $this->sanPhamModel->getById($id);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }

        $images = $this->sanPhamModel->getImages($id);
        $this->json([
            'success'  => true,
            'san_pham' => [
                'id'             => $product['id'],
                'ten'            => $product['ten'],
                'gia'            => $product['gia'],
                'gia_khuyen_mai' => $product['gia_khuyen_mai'],
                'gia_dinh_dang'  => formatPrice($product['gia_khuyen_mai'] ?: $product['gia']),
                'gia_goc'        => formatPrice($product['gia']),
                'hinh_thu_nho'   => imgUrl($product['hinh_thu_nho']),
                'ton_kho'        => $product['ton_kho'],
                'diem_danh_gia'  => $product['diem_danh_gia'],
                'duong_dan'      => $product['duong_dan'],
                'mo_ta'          => $product['mo_ta'],
                'url'            => SITE_URL . '/product/' . $product['duong_dan'],
                'hinh_anh'       => array_map(fn($i) => imgUrl($i['duong_dan_anh']), $images),
            ],
        ]);
    }

    /** POST /api/voucher/check */
    public function voucherCheck() {
        $data  = $this->getPostData();
        $code  = trim($data['code']  ?? '');
        $total = (float)($data['total'] ?? 0);

        if (!$code) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập mã voucher']);
        }

        $phieuModel = new PhieuGiamGia();
        $result     = $phieuModel->apply($code, $total);

        if ($result['success']) {
            $newTotal = $total - $result['discount'];
            $this->json([
                'success'            => true,
                'message'            => $result['message'],
                'giam_gia'           => $result['discount'],
                'giam_gia_dinh_dang' => formatPrice($result['discount']),
                'tong_moi'           => $newTotal,
                'tong_moi_dinh_dang' => formatPrice($newTotal),
            ]);
        } else {
            $this->json(['success' => false, 'message' => $result['message']]);
        }
    }

    /** POST /api/wishlist/toggle */
    public function wishlistToggle() {
        if (!isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập', 'require_login' => true], 401);
        }

        $data      = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin'], 400);
        }

        $yeuThichModel = new YeuThich();
        $result = $yeuThichModel->toggle($_SESSION['id_nguoi_dung'], $productId);

        $this->json([
            'success' => true,
            'action'  => $result,
            'message' => $result === 'added' ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích',
        ]);
    }

    /** POST /api/review/add */
    public function reviewAdd() {
        if (!isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập', 'require_login' => true], 401);
        }

        $data      = $this->getPostData();
        $productId = (int)($data['product_id'] ?? 0);
        $rating    = (int)($data['rating']     ?? 0);
        $content   = trim($data['content']     ?? '');

        if (!$productId || $rating < 1 || $rating > 5) {
            $this->json(['success' => false, 'message' => 'Thông tin không hợp lệ'], 400);
        }

        $this->sanPhamModel->addReview($productId, $_SESSION['id_nguoi_dung'], $rating, $content);
        $this->json(['success' => true, 'message' => 'Đã thêm đánh giá']);
    }

    /** POST /api/comment/add */
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

        $commentId = $this->sanPhamModel->addComment($productId, $_SESSION['id_nguoi_dung'], $content, $parentId);
        $this->json(['success' => true, 'message' => 'Đã thêm bình luận', 'id' => $commentId]);
    }

    /** GET /api/notifications */
    public function notifications() {
        if (!isLoggedIn()) {
            $this->json(['success' => false, 'chua_doc' => 0, 'items' => []]);
        }
        $db     = db();
        $userId = $_SESSION['id_nguoi_dung'];

        $items = $db->fetchAll(
            "SELECT id, tieu_de, noi_dung, loai, lien_ket, da_doc, ngay_tao
             FROM thong_bao WHERE id_nguoi_dung = ?
             ORDER BY ngay_tao DESC LIMIT 20",
            [$userId]
        );

        $chuaDoc = (int)($db->fetch(
            "SELECT COUNT(*) AS c FROM thong_bao WHERE id_nguoi_dung = ? AND da_doc = 0",
            [$userId]
        )['c'] ?? 0);

        $this->json(['success' => true, 'chua_doc' => $chuaDoc, 'items' => $items]);
    }

    /** POST /api/notifications/read — đánh dấu đã đọc (id=0 → tất cả) */
    public function notificationsRead() {
        if (!isLoggedIn()) {
            $this->json(['success' => false], 401);
        }
        $db     = db();
        $userId = $_SESSION['id_nguoi_dung'];
        $data   = $this->getPostData();
        $id     = (int)($data['id'] ?? 0);

        if ($id) {
            $db->execute(
                "UPDATE thong_bao SET da_doc = 1 WHERE id = ? AND id_nguoi_dung = ?",
                [$id, $userId]
            );
        } else {
            $db->execute(
                "UPDATE thong_bao SET da_doc = 1 WHERE id_nguoi_dung = ?",
                [$userId]
            );
        }

        $chuaDoc = (int)($db->fetch(
            "SELECT COUNT(*) AS c FROM thong_bao WHERE id_nguoi_dung = ? AND da_doc = 0",
            [$userId]
        )['c'] ?? 0);

        $this->json(['success' => true, 'chua_doc' => $chuaDoc]);
    }
}
