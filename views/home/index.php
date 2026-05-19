<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<!-- ===================== HERO SLIDER ===================== -->
<section class="hero-section">
    <div class="container-fluid p-0">
        <div class="hero-slider" id="heroSlider">
            <?php if (!empty($banners)): foreach ($banners as $i => $banner): ?>
            <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>"
                 style="background-image: linear-gradient(135deg, rgba(10,14,26,.8), rgba(15,23,42,.6)), url('<?= imgUrl($banner['image']) ?>')">
                <div class="container">
                    <div class="hero-content">
                        <span class="hero-badge">
                            <i class="fas fa-bolt"></i> HOT DEAL
                        </span>
                        <h1 class="hero-title"><?= htmlspecialchars($banner['title']) ?></h1>
                        <p class="hero-subtitle"><?= htmlspecialchars($banner['subtitle']) ?></p>
                        <a href="<?= SITE_URL . htmlspecialchars($banner['link'] ?? '/products') ?>" class="btn btn-tech btn-lg hero-cta">
                            <i class="fas fa-rocket me-2"></i>Khám phá ngay
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <!-- Fallback hero -->
            <div class="hero-slide active hero-fallback">
                <div class="container">
                    <div class="hero-content">
                        <span class="hero-badge"><i class="fas fa-bolt"></i> WELCOME</span>
                        <h1 class="hero-title">CÔNG NGHỆ ĐỈNH CAO<br>GIÁ TỐT NHẤT</h1>
                        <p class="hero-subtitle">Laptop Gaming, MacBook, Phụ kiện chính hãng — Giảm giá đến 50%</p>
                        <a href="<?= SITE_URL ?>/products" class="btn btn-tech btn-lg hero-cta">
                            <i class="fas fa-rocket me-2"></i>Mua sắm ngay
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Slider nav -->
        <button class="hero-nav hero-prev" id="heroPrev"><i class="fas fa-chevron-left"></i></button>
        <button class="hero-nav hero-next" id="heroNext"><i class="fas fa-chevron-right"></i></button>

        <!-- Dots -->
        <?php if (!empty($banners) && count($banners) > 1): ?>
        <div class="hero-dots">
            <?php foreach ($banners as $i => $b): ?>
            <span class="hero-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== BRAND STRIP ===================== -->
<section class="brand-strip">
    <div class="container">
        <div class="brand-marquee">
            <div class="brand-track">
                <span class="brand-item"><i class="fab fa-apple"></i> Apple</span>
                <span class="brand-item">ASUS ROG</span>
                <span class="brand-item">MSI Gaming</span>
                <span class="brand-item">Acer</span>
                <span class="brand-item">Dell</span>
                <span class="brand-item">HP</span>
                <span class="brand-item">Lenovo</span>
                <span class="brand-item">Razer</span>
                <span class="brand-item">Logitech</span>
                <span class="brand-item">SteelSeries</span>
                <!-- Duplicate for infinite scroll -->
                <span class="brand-item"><i class="fab fa-apple"></i> Apple</span>
                <span class="brand-item">ASUS ROG</span>
                <span class="brand-item">MSI Gaming</span>
                <span class="brand-item">Acer</span>
                <span class="brand-item">Dell</span>
            </div>
        </div>
    </div>
</section>

<!-- ===================== CATEGORIES GRID ===================== -->
<section class="section section-categories">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-th-large"></i> Danh mục nổi bật
            </h2>
            <a href="<?= SITE_URL ?>/products" class="section-link">
                Xem tất cả <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="row g-3">
            <?php foreach ($categories as $cat): ?>
            <div class="col-md-4 col-lg-2 col-6">
                <a href="<?= SITE_URL ?>/category/<?= htmlspecialchars($cat['slug']) ?>" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-<?= htmlspecialchars($cat['icon'] ?? 'folder') ?>"></i>
                    </div>
                    <h6 class="category-name"><?= htmlspecialchars($cat['name']) ?></h6>
                    <span class="category-count"><?= $cat['product_count'] ?? 0 ?> sản phẩm</span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== FLASH SALE ===================== -->
<?php if (!empty($flashSale)): ?>
<section class="section section-flash-sale">
    <div class="container">
        <div class="flash-sale-header">
            <div class="flash-sale-title">
                <i class="fas fa-bolt"></i>
                <h2>FLASH SALE</h2>
                <span class="flash-sale-tag">GIẢM SỐC</span>
            </div>
            <div class="flash-sale-countdown" id="flashCountdown" data-end="<?= $flashSale[0]['flash_sale_end'] ?? '' ?>">
                <span class="countdown-label">Kết thúc sau:</span>
                <div class="countdown-boxes">
                    <span class="countdown-box"><span id="cd-hours">00</span><small>Giờ</small></span>
                    <span class="countdown-sep">:</span>
                    <span class="countdown-box"><span id="cd-minutes">00</span><small>Phút</small></span>
                    <span class="countdown-sep">:</span>
                    <span class="countdown-box"><span id="cd-seconds">00</span><small>Giây</small></span>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <?php foreach ($flashSale as $product): ?>
            <div class="col-lg-3 col-md-4 col-6">
                <?php include __DIR__ . '/../products/_product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== FEATURED PRODUCTS WITH TABS ===================== -->
<section class="section section-featured">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-star"></i> Sản phẩm nổi bật
            </h2>
            <div class="section-tabs" id="featuredTabs">
                <button class="tab-btn active" data-tab="all">Tất cả</button>
                <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
                <button class="tab-btn" data-tab="cat-<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-3" id="featuredGrid">
            <?php foreach ($featured as $product): ?>
            <div class="col-lg-3 col-md-4 col-6 featured-item" data-cat="cat-<?= $product['category_id'] ?>">
                <?php include __DIR__ . '/../products/_product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/products" class="btn btn-outline-tech">
                Xem tất cả sản phẩm <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===================== GAMING BANNER ===================== -->
<section class="section">
    <div class="container">
        <div class="gaming-banner">
            <div class="gaming-banner-content">
                <span class="banner-tag"><i class="fas fa-gamepad"></i> GAMING ZONE</span>
                <h2 class="banner-title">PHỤ KIỆN GAMING CHẤT LƯỢNG</h2>
                <p class="banner-desc">Bàn phím cơ, chuột gaming, tai nghe — Trang bị "vũ khí" để thống trị mọi trận đấu</p>
                <a href="<?= SITE_URL ?>/category/chuot-gaming" class="btn btn-tech">
                    <i class="fas fa-arrow-right me-2"></i>Khám phá ngay
                </a>
            </div>
            <div class="gaming-banner-decor">
                <i class="fas fa-gamepad floating-icon"></i>
                <i class="fas fa-headphones floating-icon"></i>
                <i class="fas fa-keyboard floating-icon"></i>
                <i class="fas fa-mouse floating-icon"></i>
            </div>
        </div>
    </div>
</section>

<!-- ===================== NEW ARRIVALS ===================== -->
<?php if (!empty($newArrivals)): ?>
<section class="section section-new">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-sparkles"></i> Hàng mới về
            </h2>
            <a href="<?= SITE_URL ?>/products?sort=newest" class="section-link">
                Xem tất cả <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="row g-3">
            <?php foreach ($newArrivals as $product): ?>
            <div class="col-lg-3 col-md-4 col-6">
                <?php include __DIR__ . '/../products/_product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== BEST SELLERS ===================== -->
<?php if (!empty($bestSellers)): ?>
<section class="section section-bestsellers">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-fire"></i> Bán chạy nhất
            </h2>
            <a href="<?= SITE_URL ?>/products?sort=best_seller" class="section-link">
                Xem tất cả <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="row g-3">
            <?php foreach ($bestSellers as $product): ?>
            <div class="col-lg-3 col-md-4 col-6">
                <?php include __DIR__ . '/../products/_product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== WHY US ===================== -->
<section class="section section-why-us">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="why-us-item">
                    <i class="fas fa-shield-alt"></i>
                    <h6>Chính hãng 100%</h6>
                    <small>Cam kết nguồn gốc</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="why-us-item">
                    <i class="fas fa-shipping-fast"></i>
                    <h6>Giao hàng nhanh</h6>
                    <small>Free ship đơn từ 500K</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="why-us-item">
                    <i class="fas fa-headset"></i>
                    <h6>Hỗ trợ 24/7</h6>
                    <small>Hotline: 1900 9999</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="why-us-item">
                    <i class="fas fa-medal"></i>
                    <h6>Bảo hành dài hạn</h6>
                    <small>Bảo hành toàn quốc</small>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$extraJs = ['slider.js'];
include ROOT_PATH . '/views/layouts/footer.php';
?>