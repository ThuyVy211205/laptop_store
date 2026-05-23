<?php
/* ─── Fallback slides dùng ảnh local ─── */
$localSlides = [
    [
        'bg'       => ASSETS_URL . '/images/slider1.jpg',
        'badge'    => 'HOT DEAL',
        'title'    => "Laptop Gaming<br>Chính Hãng 2025",
        'subtitle' => 'RTX 4060 — Màn hình 144Hz — Giảm đến 30%',
        'link'     => SITE_URL . '/products',
    ],
    [
        'bg'       => ASSETS_URL . '/images/slider_2.jpg',
        'badge'    => 'MỚI VỀ',
        'title'    => "MacBook Air M3<br>Siêu Mỏng Nhẹ",
        'subtitle' => 'Chip Apple M3 — Pin 18 giờ — Nhập khẩu chính hãng',
        'link'     => SITE_URL . '/products',
    ],
    [
        'bg'       => ASSETS_URL . '/images/slider_3.jpg',
        'badge'    => 'GIÁ TỐT',
        'title'    => "Laptop Văn Phòng<br>Từ 9.9 Triệu",
        'subtitle' => 'Intel Core i5 — RAM 16GB — SSD 512GB NVMe',
        'link'     => SITE_URL . '/products',
    ],
];

/* Dùng banners từ DB nếu có, không thì dùng local */
$slides = !empty($banners) ? array_map(fn($b) => [
    'bg'       => imgUrl($b['image']),
    'badge'    => 'HOT DEAL',
    'title'    => $b['title'],
    'subtitle' => $b['subtitle'] ?? '',
    'link'     => SITE_URL . ($b['link'] ?? '/products'),
], $banners) : $localSlides;

/* ─── Danh mục — dùng ảnh thật từ assets/images ─── */
$homeCategories = [
    ['name' => 'Laptop Gaming',    'slug' => 'laptop-gaming',   'img' => 'dell-16-1.webp'],
    ['name' => 'Laptop Văn phòng', 'slug' => 'laptop-van-phong', 'img' => 'lenovo-thinkbook-1.webp'],
    ['name' => 'MacBook',          'slug' => 'macbook',          'img' => 'macbook_air_m2.webp'],
    ['name' => 'Chuột',            'slug' => 'chuot-gaming',     'img' => 'chuot_gm.webp'],
    ['name' => 'Tai nghe',         'slug' => 'tai-nghe',         'img' => 'banner4.jpg'],
    ['name' => 'Bàn phím',         'slug' => 'ban-phim-co',      'img' => 'banner5.jpg'],
];

$extraCss = ['home.css'];
include ROOT_PATH . '/views/layouts/header.php';
?>

<!-- =========================================================
     HERO SECTION — Slider 65% + Sub-banners 35%
     ========================================================= -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">

            <!-- ── Main Slider (65%) — chỉ dùng dots ── -->
            <div class="hero-main">
                <div class="hero-slides-wrap" id="heroSlider">
                    <?php foreach ($slides as $i => $s): ?>
                    <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>"
                         style="background-image:url('<?= $s['bg'] ?>')">
                        <div class="hero-slide-overlay">
                            <div class="hero-content">
                                <span class="hero-badge">
                                    <i class="fas fa-bolt"></i> <?= htmlspecialchars($s['badge']) ?>
                                </span>
                                <h1 class="hero-title"><?= $s['title'] ?></h1>
                                <p class="hero-subtitle"><?= htmlspecialchars($s['subtitle']) ?></p>
                                <div class="hero-btns">
                                    <a href="<?= $s['link'] ?>" class="btn btn-primary">
                                        <i class="fas fa-shopping-bag"></i> Mua ngay
                                    </a>
                                    <a href="<?= SITE_URL ?>/products" class="btn btn-white">
                                        <i class="fas fa-search"></i> Tìm hiểu thêm
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Dots điều hướng — không dùng arrows -->
                <div class="hero-dots" id="heroDots">
                    <?php foreach ($slides as $i => $_): ?>
                    <button class="hero-dot <?= $i === 0 ? 'active' : '' ?>"
                            data-idx="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- /hero-main -->

            <!-- ── Sub-banners phải (35%) — 2 card ảnh thật ── -->
            <div class="hero-sub">

                <!-- Card 1: MacBook Air M4 -->
                <div class="hero-sub-card" style="
                    background: linear-gradient(135deg, rgba(5,12,40,.62) 0%, rgba(15,40,110,.45) 100%),
                                url('<?= ASSETS_URL ?>/images/macbook_air_m4.webp');
                    background-size: cover;
                    background-position: center;">
                    <div class="sub-card-overlay">
                        <div>
                            <span class="sub-card-label">Mới về 2025</span>
                            <h3 class="sub-card-title">MacBook Air M4<br>Siêu mỏng nhẹ</h3>
                            <p class="sub-card-desc">Chip M4 — Pin 20 giờ — Retina</p>
                            <a href="<?= SITE_URL ?>/products" class="sub-card-btn">
                                Xem ngay <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="sub-card-decor"><i class="fab fa-apple"></i></div>
                </div>

                <!-- Card 2: Laptop Gaming -->
                <div class="hero-sub-card" style="
                    background: linear-gradient(135deg, rgba(20,5,50,.65) 0%, rgba(80,10,10,.48) 100%),
                                url('<?= ASSETS_URL ?>/images/banner-new.jpg');
                    background-size: cover;
                    background-position: center;">
                    <div class="sub-card-overlay">
                        <div>
                            <span class="sub-card-label">HOT DEAL</span>
                            <h3 class="sub-card-title">Laptop Gaming<br>Giảm đến 30%</h3>
                            <p class="sub-card-desc">RTX 4060 — 144Hz — 16GB RAM</p>
                            <a href="<?= SITE_URL ?>/products" class="sub-card-btn">
                                Mua ngay <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="sub-card-decor"><i class="fas fa-gamepad"></i></div>
                </div>

            </div>
            <!-- /hero-sub -->

        </div><!-- /hero-grid -->
    </div>
</section>

<!-- =========================================================
     DANH MỤC SẢN PHẨM — Hình ảnh thật, không dùng icon
     ========================================================= -->
<section class="section">
    <div class="container">
        <div class="section-card">
            <div class="section-head">
                <h2 class="section-title">Danh mục sản phẩm</h2>
                <a href="<?= SITE_URL ?>/products" class="section-more">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="cat-img-grid">
                <?php foreach ($homeCategories as $cat): ?>
                <a href="<?= SITE_URL ?>/category/<?= htmlspecialchars($cat['slug']) ?>"
                   class="cat-img-card">
                    <div class="cat-img-thumb">
                        <img src="<?= ASSETS_URL ?>/images/<?= htmlspecialchars($cat['img']) ?>"
                             alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy"
                             onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                    </div>
                    <div class="cat-img-name"><?= htmlspecialchars($cat['name']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     SẢN PHẨM NỔI BẬT — 4 sản phẩm hàng ngang (dynamic)
     ========================================================= -->
<?php if (!empty($featured)): ?>
<section class="section section-products">
    <div class="container">
        <div class="section-card">
            <div class="section-head">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <a href="<?= SITE_URL ?>/products" class="section-more">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="pg-grid">
                <?php foreach (array_slice($featured, 0, 4) as $product): ?>
                <?php include __DIR__ . '/../products/_product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =========================================================
     SẢN PHẨM BÁN CHẠY — 4 sản phẩm hàng ngang (dynamic)
     ========================================================= -->
<?php if (!empty($bestSellers)): ?>
<section class="section section-products">
    <div class="container">
        <div class="section-card">
            <div class="section-head">
                <h2 class="section-title">Sản phẩm bán chạy</h2>
                <a href="<?= SITE_URL ?>/products?sort=best_seller" class="section-more">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="pg-grid">
                <?php foreach (array_slice($bestSellers, 0, 4) as $product): ?>
                <?php include __DIR__ . '/../products/_product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =========================================================
     CHÍNH SÁCH (giữ nguyên)
     ========================================================= -->
<section class="section" style="padding-top:0;padding-bottom:40px">
    <div class="container">
        <div class="why-us-grid">
            <div class="why-item">
                <i class="fas fa-shield-alt"></i>
                <h6>Chính hãng 100%</h6>
                <small>Cam kết nguồn gốc rõ ràng</small>
            </div>
            <div class="why-item">
                <i class="fas fa-shipping-fast"></i>
                <h6>Giao hàng nhanh</h6>
                <small>Free ship đơn từ 500K</small>
            </div>
            <div class="why-item">
                <i class="fas fa-headset"></i>
                <h6>Hỗ trợ 24/7</h6>
                <small>Hotline: 1900 9999</small>
            </div>
            <div class="why-item">
                <i class="fas fa-medal"></i>
                <h6>Bảo hành dài hạn</h6>
                <small>Bảo hành toàn quốc</small>
            </div>
        </div>
    </div>
</section>

<?php
$extraJs = ['slider.js'];
include ROOT_PATH . '/views/layouts/footer.php';
?>
