<?php
/* Load categories for nav dropdowns */
require_once ROOT_PATH . '/models/Category.php';
$_navCatModel = new Category();
$_navCats     = $_navCatModel->getAll();

/* Group: laptop vs accessories */
$_laptopCats = array_values(array_filter($_navCats, function($c) {
    return stripos($c['name'] . ' ' . $c['slug'], 'laptop') !== false
        || stripos($c['name'] . ' ' . $c['slug'], 'macbook') !== false;
}));
$_accCats = array_values(array_filter($_navCats, function($c) {
    $n = strtolower($c['name']); $s = strtolower($c['slug'] ?? '');
    return preg_match('/chu[oô]t|tai[\s-]?nghe|b[àa]n[\s-]?ph[íi]m|ph[ụu][\s-]?ki[eê]n/', $n . ' ' . $s);
}));
?>

<!-- =========================================================
     MAIN NAVBAR
     ========================================================= -->
<nav class="navbar-main">
    <div class="container">
        <div class="navbar-row">

            <!-- Logo -->
            <a href="<?= SITE_URL ?>" class="nav-logo">
                <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.webp" alt="Laptop Store" style="height:65px;width:auto;display:block;">
            </a>

            <!-- Search -->
            <div class="nav-search-wrap">
                <form action="<?= SITE_URL ?>/products" method="GET" class="nav-search-form" id="searchForm">
                    <input type="text" name="q" id="searchInput"
                           placeholder="Tìm kiếm laptop, phụ kiện..."
                           autocomplete="off"
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" aria-label="Tìm kiếm">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <div class="nav-search-dropdown" id="searchDropdown"></div>
            </div>

            <!-- Right side -->
            <div class="nav-right">

                <!-- Plain nav links (desktop) -->
                <a href="<?= SITE_URL ?>/products"
                   class="nav-link-plain" style="display:none" id="navSP">Sản phẩm</a>
                <a href="<?= SITE_URL ?>/products?type=phu-kien"
                   class="nav-link-plain" style="display:none" id="navPK">Phụ kiện</a>
                <a href="<?= SITE_URL ?>/products?flash_sale=1"
                   class="nav-link-plain" style="display:none" id="navKM">Khuyến mãi</a>
                <a href="<?= SITE_URL ?>/contact"
                   class="nav-link-plain" style="display:none" id="navLH">Liên hệ</a>

                <!-- Cart -->
                <button class="nav-icon-btn cart-toggle" id="cartToggleBtn" title="Giỏ hàng">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="nav-badge" id="cartBadgeNav"><?= $cartCount ?></span>
                </button>

                <!-- Account -->
                <?php if (isLoggedIn() && $currentUser): ?>
                <div class="nav-item">
                    <button class="nav-link-btn nav-user-btn">
                        <div class="nav-user-avatar">
                            <?php if (!empty($currentUser['avatar'])): ?>
                                <img src="<?= imgUrl($currentUser['avatar']) ?>" alt="">
                            <?php else: ?>
                                <span><?= mb_strtoupper(mb_substr($currentUser['full_name'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="d-none d-xl-inline" style="font-size:13px;max-width:88px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-left:4px">
                            <?= htmlspecialchars(mb_substr($currentUser['full_name'], 0, 10)) ?>
                        </span>
                        <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px"></i>
                    </button>
                    <div class="nav-dropdown nav-user-dropdown">
                        <div class="nav-dd-header">
                            <?php if (function_exists('getUserRankInfo')): ?>
                            <?php $rank = getUserRankInfo($currentUser['rank'] ?? 'silver'); ?>
                            <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong>
                            <small style="color:<?= $rank['color'] ?>"><?= $rank['icon'] ?> <?= $rank['name'] ?></small>
                            <?php else: ?>
                            <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong>
                            <?php endif; ?>
                            <small><?= htmlspecialchars($currentUser['email']) ?></small>
                        </div>
                        <a href="<?= SITE_URL ?>/account"><i class="fas fa-user"></i> Tài khoản</a>
                        <a href="<?= SITE_URL ?>/order"><i class="fas fa-box"></i> Đơn hàng</a>
                        <a href="<?= SITE_URL ?>/wishlist"><i class="fas fa-heart"></i> Yêu thích</a>
                        <hr>
                        <a href="<?= SITE_URL ?>/auth/logout" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?= SITE_URL ?>/auth/login"
                   class="nav-account-link nav-account-link--sm d-none d-sm-flex">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Đăng nhập</span>
                </a>
                <a href="<?= SITE_URL ?>/auth/register"
                   class="nav-account-link nav-account-link--reg d-none d-md-flex">
                    <i class="fas fa-user-plus"></i>
                    <span>Đăng ký</span>
                </a>
                <?php endif; ?>

                <!-- Theme toggle -->
                <button class="nav-theme-btn" id="themeToggle" title="Chuyển sáng/tối">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>

                <!-- Mobile menu -->
                <button class="nav-mobile-btn d-lg-none" id="mobileToggle" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>

            </div><!-- /nav-right -->
        </div><!-- /navbar-row -->
    </div>
</nav>

<!-- Mobile Drawer -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="drawer-header">
        <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.webp" alt="Laptop Store" style="height:40px;width:auto;display:block;">
        <button class="drawer-close" id="drawerClose"><i class="fas fa-times"></i></button>
    </div>
    <nav class="drawer-nav">
        <a href="<?= SITE_URL ?>/products">
            <i class="fas fa-laptop"></i> Sản phẩm Laptop
        </a>
        <a href="<?= SITE_URL ?>/products?type=phu-kien">
            <i class="fas fa-plug"></i> Phụ kiện
        </a>
        <a href="<?= SITE_URL ?>/products?flash_sale=1">
            <i class="fas fa-bolt"></i> Khuyến mãi
        </a>
        <a href="<?= SITE_URL ?>/contact">
            <i class="fas fa-phone-alt"></i> Liên hệ
        </a>
        <?php if (!isLoggedIn()): ?>
        <a href="<?= SITE_URL ?>/auth/login" style="color:var(--color-primary);font-weight:700">
            <i class="fas fa-user-circle"></i> Đăng nhập / Đăng ký
        </a>
        <?php endif; ?>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<script>
/* ── Theme toggle ── */
(function(){
    var html  = document.documentElement;
    var btn   = document.getElementById('themeToggle');
    var icon  = document.getElementById('themeIcon');
    var saved = localStorage.getItem('ts_theme');

    function setTheme(dark){
        html.classList.toggle('dark', dark);
        if(icon) icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
    }
    setTheme(saved === 'dark');

    if(btn) btn.addEventListener('click', function(){
        var isDark = html.classList.contains('dark');
        localStorage.setItem('ts_theme', isDark ? 'light' : 'dark');
        setTheme(!isDark);
    });

    /* Show desktop nav items */
    function showDesktop(){
        var isLg = window.innerWidth >= 992;
        ['navSP','navPK','navKM','navLH'].forEach(function(id){
            var el = document.getElementById(id);
            if(el) el.style.display = isLg ? '' : 'none';
        });
    }
    showDesktop();
    window.addEventListener('resize', showDesktop);

    /* Mobile drawer */
    var drawer  = document.getElementById('mobileDrawer');
    var overlay = document.getElementById('mobileOverlay');
    var toggleB = document.getElementById('mobileToggle');
    var closeB  = document.getElementById('drawerClose');

    function openDrawer(){ drawer.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow='hidden'; }
    function closeDrawer(){ drawer.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow=''; }

    if(toggleB) toggleB.addEventListener('click', openDrawer);
    if(closeB)  closeB.addEventListener('click', closeDrawer);
    if(overlay) overlay.addEventListener('click', closeDrawer);
})();
</script>
