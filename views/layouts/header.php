<?php
$currentUser = isLoggedIn() ? getCurrentUser() : null;
$cartCount   = getCartCount();
$flash       = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Theme init — must run before any CSS to prevent flash of light mode -->
    <script>if(localStorage.getItem('ts_theme')==='dark')document.documentElement.classList.add('dark');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VQStore - Cửa hàng Laptop & Phụ kiện công nghệ chính hãng">
    <meta name="keywords" content="laptop, gaming, macbook, bàn phím cơ, chuột gaming, tai nghe">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>VQStore</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/images/favicon.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Exo+2:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css?v=<?= @filemtime(ROOT_PATH . '/assets/css/style.css') ?: 1 ?>">
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= $css ?>?v=<?= @filemtime(ROOT_PATH . '/assets/css/' . $css) ?: 2 ?>">
    <?php endforeach; endif; ?>

    <!-- Pass PHP vars to JS -->
    <script>
        window.SITE_URL = '<?= SITE_URL ?>';
        window.IS_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;
    </script>
</head>
<body>

<?php if (empty($hideNav)): ?>
<!-- ===================== TOP BAR ===================== -->
<div class="top-bar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="top-bar-left d-none d-md-flex gap-3">
                <span><i class="fas fa-phone-alt me-1"></i> Hotline: 1900 9999</span>
                <span><i class="fas fa-envelope me-1"></i> support@techstore.vn</span>
            </div>
            <div class="top-bar-right d-flex gap-3 align-items-center">
                <span class="d-none d-md-inline"><i class="fas fa-shipping-fast me-1"></i> Miễn phí giao hàng đơn từ 500K</span>
                <a href="<?= SITE_URL ?>/order" class="top-link"><i class="fas fa-search me-1"></i>Tra cứu đơn hàng</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/navbar.php'; ?>
<?php endif; ?>

<!-- ===================== FLASH MESSAGE ===================== -->
<?php if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' || $flash['type'] === 'danger' ? 'exclamation-circle' : 'info-circle') ?> me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- ===================== CART SIDEBAR ===================== -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-sidebar-header">
        <h5 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Giỏ hàng
            <span class="cart-badge" id="cartBadgeSidebar"><?= $cartCount ?></span>
        </h5>
        <button class="btn-close-cart" id="closeCartBtn">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="cart-sidebar-body" id="cartSidebarBody">
        <div class="cart-loading text-center py-5">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        </div>
    </div>
    <div class="cart-sidebar-footer" id="cartSidebarFooter">
        <div class="cart-total mb-3">
            <span>Tổng cộng:</span>
            <strong class="cart-total-amount" id="cartTotalAmount">0đ</strong>
        </div>
        <div class="d-grid gap-2">
            <a href="<?= SITE_URL ?>/cart" class="btn btn-outline-tech">Xem giỏ hàng</a>
            <a href="<?= SITE_URL ?>/checkout" class="btn btn-tech">Thanh toán ngay</a>
        </div>
    </div>
</div>
<div class="cart-overlay" id="cartOverlay"></div>

<!-- ===================== TOAST CONTAINER ===================== -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;" id="toastContainer"></div>

<!-- ===================== QUICK VIEW MODAL ===================== -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickViewBody">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================== MAIN CONTENT WRAPPER ===================== -->
<main class="main-content">