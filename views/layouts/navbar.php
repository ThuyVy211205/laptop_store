<!-- ===================== MAIN NAVBAR ===================== -->
<nav class="navbar-tech sticky-top">
    <div class="container">
        <div class="navbar-inner">
            <!-- Logo -->
            <a href="<?= SITE_URL ?>" class="navbar-logo">
                <span class="logo-tech">TECH</span><span class="logo-store">STORE</span>
                <span class="logo-tagline d-none d-md-inline">.vn</span>
            </a>

            <!-- Mobile toggle -->
            <button class="navbar-mobile-toggle d-lg-none" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Search -->
            <div class="navbar-search">
                <form action="<?= SITE_URL ?>/products" method="GET" class="search-form" id="searchForm">
                    <input type="text" name="q" class="search-input" id="searchInput"
                           placeholder="Tìm laptop, phụ kiện gaming..." autocomplete="off"
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <div class="search-dropdown" id="searchDropdown"></div>
            </div>

            <!-- Right Actions -->
            <div class="navbar-actions">
                <!-- Hotline -->
                <div class="action-item d-none d-xl-flex">
                    <i class="fas fa-headset action-icon"></i>
                    <div class="action-text">
                        <small>Hotline</small>
                        <strong>1900 9999</strong>
                    </div>
                </div>

                <!-- Order tracking -->
                <a href="<?= SITE_URL ?>/order" class="action-item d-none d-md-flex">
                    <i class="fas fa-truck action-icon"></i>
                    <div class="action-text">
                        <small>Tra cứu</small>
                        <strong>Đơn hàng</strong>
                    </div>
                </a>

                <!-- Wishlist -->
                <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/wishlist" class="action-item action-icon-only" title="Yêu thích">
                    <i class="fas fa-heart"></i>
                    <?php $wcount = getWishlistCount(); if ($wcount > 0): ?>
                    <span class="action-badge"><?= $wcount ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <!-- Cart -->
                <button class="action-item action-icon-only cart-toggle" id="cartToggleBtn" title="Giỏ hàng">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="action-badge" id="cartBadgeNav"><?= $cartCount ?></span>
                </button>

                <!-- User -->
                <?php if (isLoggedIn() && $currentUser): ?>
                <div class="action-item dropdown">
                    <button class="user-dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <?php if (!empty($currentUser['avatar'])): ?>
                                <img src="<?= imgUrl($currentUser['avatar']) ?>" alt="">
                            <?php else: ?>
                                <span><?= mb_strtoupper(mb_substr($currentUser['full_name'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="action-text d-none d-md-block">
                            <small>Xin chào</small>
                            <strong><?= htmlspecialchars(mb_substr($currentUser['full_name'], 0, 12)) ?></strong>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end user-menu">
                        <li class="user-menu-header">
                            <div class="d-flex align-items-center gap-2">
                                <?php $rank = getUserRankInfo($currentUser['rank'] ?? 'silver'); ?>
                                <span class="rank-badge" style="color: <?= $rank['color'] ?>">
                                    <?= $rank['icon'] ?> <?= $rank['name'] ?>
                                </span>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars($currentUser['email']) ?></small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/account"><i class="fas fa-user me-2"></i>Tài khoản của tôi</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/order"><i class="fas fa-box me-2"></i>Đơn hàng</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/wishlist"><i class="fas fa-heart me-2"></i>Yêu thích</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="<?= SITE_URL ?>/auth/login" class="action-item">
                    <i class="fas fa-user action-icon"></i>
                    <div class="action-text d-none d-md-block">
                        <small>Đăng nhập</small>
                        <strong>Tài khoản</strong>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mega Menu / Category Nav -->
        <?php
        require_once ROOT_PATH . '/models/Category.php';
        $cats = (new Category())->getAll();
        ?>
        <div class="navbar-mega-menu" id="megaMenu">
            <ul class="mega-menu-list">
                <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
                <?php foreach ($cats as $cat): ?>
                <li><a href="<?= SITE_URL ?>/category/<?= htmlspecialchars($cat['slug']) ?>">
                    <i class="fas fa-<?= htmlspecialchars($cat['icon'] ?? 'folder') ?>"></i>
                    <?= htmlspecialchars($cat['name']) ?>
                </a></li>
                <?php endforeach; ?>
                <li><a href="<?= SITE_URL ?>/products?sort=best_seller"><i class="fas fa-fire"></i> Bán chạy</a></li>
                <li><a href="<?= SITE_URL ?>/products?flash_sale=1"><i class="fas fa-bolt"></i> Flash Sale</a></li>
            </ul>
        </div>
    </div>
</nav>