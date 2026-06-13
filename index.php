<?php
/**
 * Front Controller — Điều phối tất cả request đến controller tương ứng
 * Phân tích URL → gọi đúng controller::action()
 */

require_once __DIR__ . '/config/co_so_du_lieu.php';
require_once __DIR__ . '/thu_vien/ham.php';
require_once __DIR__ . '/thu_vien/tro_giup.php';
require_once __DIR__ . '/thu_vien/phien.php';

// Phân tích URL thành các segment
$url = $_GET['url'] ?? '';
$url = trim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$segments = $url ? explode('/', $url) : [];

$route  = $segments[0] ?? 'home';
$action = $segments[1] ?? 'index';
$param  = $segments[2] ?? null;
$param2 = $segments[3] ?? null;

// ============================================================
//  ĐỊNH TUYẾN (ROUTING)
// ============================================================
try {
    switch ($route) {
        case '':
        case 'home':
            require_once 'controllers/DieuKhienTrangChu.php';
            (new DieuKhienTrangChu())->index();
            break;

        case 'products':
            require_once 'controllers/DieuKhienSanPham.php';
            $ctrl = new DieuKhienSanPham();
            if ($action === '' || $action === 'index') {
                $ctrl->index();
            } elseif ($action === 'search-suggest') {
                $ctrl->searchSuggest();
            } elseif ($action === 'quickview' && $param) {
                $ctrl->quickview($param);
            } else {
                $ctrl->detail($action);
            }
            break;

        case 'product':
            require_once 'controllers/DieuKhienSanPham.php';
            (new DieuKhienSanPham())->detail($action);
            break;

        case 'category':
            require_once 'controllers/DieuKhienSanPham.php';
            (new DieuKhienSanPham())->category($action);
            break;

        case 'search':
            require_once 'controllers/DieuKhienSanPham.php';
            (new DieuKhienSanPham())->index();
            break;

        case 'cart':
            require_once 'controllers/DieuKhienGioHang.php';
            (new DieuKhienGioHang())->$action($param);
            break;

        case 'checkout':
            require_once 'controllers/DieuKhienThanhToan.php';
            (new DieuKhienThanhToan())->$action();
            break;

        case 'order':
            require_once 'controllers/DieuKhienDonHang.php';
            $ctrl = new DieuKhienDonHang();
            $ctrl->$action($param);
            break;

        case 'account':
            require_once 'controllers/DieuKhienTaiKhoan.php';
            (new DieuKhienTaiKhoan())->$action();
            break;

        case 'auth':
            require_once 'controllers/DieuKhienXacThuc.php';
            (new DieuKhienXacThuc())->$action();
            break;

        case 'admin':
            require_once 'controllers/DieuKhienQuanTri.php';
            $ctrl   = new DieuKhienQuanTri();
            $method = ($action === '' || $action === 'index') ? 'dashboard' : $action;
            if (method_exists($ctrl, $method)) {
                $ctrl->$method($param);
            } else {
                $ctrl->dashboard();
            }
            break;

        case 'wishlist':
            require_once 'controllers/DieuKhienYeuThich.php';
            (new DieuKhienYeuThich())->$action($param);
            break;

        case 'promotions':
        case 'khuyen-mai':
            require_once 'controllers/DieuKhienKhuyenMai.php';
            (new DieuKhienKhuyenMai())->index();
            break;

        case 'contact':
            require_once 'controllers/DieuKhienLienHe.php';
            (new DieuKhienLienHe())->index();
            break;

        case 'api':
            require_once 'controllers/DieuKhienAPI.php';
            $ctrl   = new DieuKhienAPI();
            $method = $action;
            if ($param) $method .= ucfirst($param);
            if (method_exists($ctrl, $method)) {
                $ctrl->$method();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;

        case '404':
        default:
            http_response_code(404);
            require_once 'controllers/DieuKhienTrangChu.php';
            (new DieuKhienTrangChu())->notFound();
            break;
    }
} catch (Exception $e) {
    if (DEBUG) {
        echo '<pre style="color:red">';
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
        echo '</pre>';
    } else {
        header('Location: ' . SITE_URL . '/404');
        exit;
    }
}
