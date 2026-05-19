<?php
/**
 * TechStore - Front Controller
 * Routes all requests to appropriate controllers
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/session.php';

// Parse URL
$url = $_GET['url'] ?? '';
$url = trim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$segments = $url ? explode('/', $url) : [];

$route = $segments[0] ?? 'home';
$action = $segments[1] ?? 'index';
$param  = $segments[2] ?? null;
$param2 = $segments[3] ?? null;

// ============================================================
//  ROUTING
// ============================================================
try {
    switch ($route) {
        case '':
        case 'home':
            require_once 'controllers/HomeController.php';
            (new HomeController())->index();
            break;

        case 'products':
            require_once 'controllers/ProductController.php';
            $ctrl = new ProductController();
            if ($action === 'index' || $action === '') {
                $ctrl->index();
            } else {
                // /products/{slug}
                $ctrl->detail($action);
            }
            break;

        case 'product':
            require_once 'controllers/ProductController.php';
            (new ProductController())->detail($action);
            break;

        case 'category':
            require_once 'controllers/ProductController.php';
            (new ProductController())->category($action);
            break;

        case 'search':
            require_once 'controllers/ProductController.php';
            (new ProductController())->index();
            break;

        case 'cart':
            require_once 'controllers/CartController.php';
            (new CartController())->$action($param);
            break;

        case 'checkout':
            require_once 'controllers/CheckoutController.php';
            (new CheckoutController())->$action();
            break;

        case 'order':
            require_once 'controllers/OrderController.php';
            $ctrl = new OrderController();
            $ctrl->$action($param);
            break;

        case 'account':
            require_once 'controllers/AccountController.php';
            (new AccountController())->$action();
            break;

        case 'auth':
            require_once 'controllers/AuthController.php';
            (new AuthController())->$action();
            break;

        case 'wishlist':
            require_once 'controllers/WishlistController.php';
            (new WishlistController())->$action($param);
            break;

        case 'api':
            require_once 'controllers/ApiController.php';
            $ctrl = new ApiController();
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
            require_once 'controllers/HomeController.php';
            (new HomeController())->notFound();
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