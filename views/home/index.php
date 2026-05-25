<?php
/* ─── Banner slides — 4 ảnh full-width ─── */
$bannerSlides = [
    ['img' => 'banner-macbook.jpg',   'alt' => 'MacBook'],
    ['img' => 'banner-laptop-vp.jpg', 'alt' => 'Laptop Văn phòng'],
    ['img' => 'banner-laptop-gm.jpg', 'alt' => 'Laptop Gaming'],
    ['img' => 'banner-phukien.jpg',   'alt' => 'Phụ kiện'],
];

/* ─── Danh mục — dùng ảnh thật từ assets/images ─── */
$homeCategories = [
    ['name' => 'Laptop Gaming',    'slug' => 'laptop-gaming',   'img' => 'laptop_asus_gaming_rog_strix_g15.webp'],
    ['name' => 'Laptop Văn phòng', 'slug' => 'laptop-van-phong', 'img' => 'lenovo-IdeaPad-slim5.webp'],
    ['name' => 'MacBook',          'slug' => 'macbook',          'img' => 'macbook-air-m2.webp'],
    ['name' => 'Chuột',            'slug' => 'chuot-gaming',     'img' => 'chuot-gaming-asus-tuf.webp'],
    ['name' => 'Tai nghe',         'slug' => 'tai-nghe',         'img' => 'tai-nghe-JBL.webp'],
    ['name' => 'Bàn phím',         'slug' => 'ban-phim-co',      'img' => 'ban-phim-keychron.webp'],
];

$extraCss = ['home.css'];
include ROOT_PATH . '/views/layouts/header.php';
?>

<!-- =========================================================
     BANNER — Slider bo góc, cùng layout với các section
     ========================================================= -->
<section class="banner-section">
    <div class="container">
        <div class="banner-fullwidth" id="bannerSlider">
            <?php foreach ($bannerSlides as $i => $slide): ?>
            <div class="banner-slide <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= ASSETS_URL ?>/images/<?= htmlspecialchars($slide['img']) ?>"
                     alt="<?= htmlspecialchars($slide['alt']) ?>"
                     <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
            </div>
            <?php endforeach; ?>

            <button class="banner-arrow banner-arrow--prev" id="bannerPrev" type="button" aria-label="Slide trước">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="banner-arrow banner-arrow--next" id="bannerNext" type="button" aria-label="Slide tiếp">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
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

<script>
(function(){
    var el = document.getElementById('bannerSlider');
    if(!el) return;
    var items = el.querySelectorAll('.banner-slide');
    if(items.length < 2) return;
    var cur = 0, timer;

    var goTo = function(n){
        items[cur].classList.remove('active');
        cur = (n + items.length) % items.length;
        items[cur].classList.add('active');
    };
    var startTimer = function(){
        clearInterval(timer);
        timer = setInterval(function(){ goTo(cur + 1); }, 5000);
    };

    var prev = document.getElementById('bannerPrev');
    var next = document.getElementById('bannerNext');
    if(prev) prev.addEventListener('click', function(){ goTo(cur - 1); startTimer(); });
    if(next) next.addEventListener('click', function(){ goTo(cur + 1); startTimer(); });

    startTimer();
}());
</script>

<?php
$extraJs = ['slider.js'];
include ROOT_PATH . '/views/layouts/footer.php';
?>
