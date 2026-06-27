<?php
/* ─── Banner slides — lấy từ DB (position = 'hero'), fallback tĩnh nếu rỗng ─── */
if (empty($banners)) {
    $banners = [
        ['hinh_anh' => 'assets/images/banner-macbook.jpg',    'tieu_de' => 'MacBook',         'lien_ket' => null],
        ['hinh_anh' => 'assets/images/banner-laptop-vp.jpg',  'tieu_de' => 'Laptop Văn phòng','lien_ket' => null],
        ['hinh_anh' => 'assets/images/banner-laptop-gm.jpg',  'tieu_de' => 'Laptop Gaming',   'lien_ket' => null],
        ['hinh_anh' => 'assets/images/banner-phukien.jpg',    'tieu_de' => 'Phụ kiện',        'lien_ket' => null],
    ];
}

/* ─── Danh mục — dùng ảnh thật từ assets/images ─── */
$homeCategories = [
    ['ten' => 'Laptop Gaming',    'duong_dan' => 'laptop-gaming',    'img' => 'laptop_asus_gaming_rog_strix_g15.webp'],
    ['ten' => 'Laptop Văn phòng', 'duong_dan' => 'laptop-van-phong', 'img' => 'lenovo-IdeaPad-slim5.webp'],
    ['ten' => 'MacBook',          'duong_dan' => 'macbook',          'img' => 'macbook-air-m2.webp'],
    ['ten' => 'Chuột',            'duong_dan' => 'chuot-gaming',     'img' => 'chuot-gaming-asus-tuf.webp'],
    ['ten' => 'Tai nghe',         'duong_dan' => 'tai-nghe',         'img' => 'tai-nghe-JBL.webp'],
    ['ten' => 'Bàn phím',         'duong_dan' => 'ban-phim-co',      'img' => 'ban-phim-keychron.webp'],
];

$extraCss = ['home.css', 'show-more.css'];
include ROOT_PATH . '/views/bo_cuc/dau_trang.php';
?>

<!-- =========================================================
     BANNER — Slider bo góc, cùng layout với các section
     ========================================================= -->
<section class="banner-section">
    <div class="container">
        <div class="banner-fullwidth" id="bannerSlider">
            <?php foreach ($banners as $i => $banner): ?>
                <div class="banner-slide <?= $i === 0 ? 'active' : '' ?>">
                    <?php $imgUrl = SITE_URL . '/' . ltrim($banner['hinh_anh'], '/'); ?>
                    <?php if (!empty($banner['lien_ket'])): ?>
                        <a href="<?= SITE_URL . htmlspecialchars($banner['lien_ket']) ?>">
                            <img src="<?= htmlspecialchars($imgUrl) ?>"
                                alt="<?= htmlspecialchars($banner['tieu_de'] ?? '') ?>"
                                <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                        </a>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($imgUrl) ?>"
                            alt="<?= htmlspecialchars($banner['tieu_de'] ?? '') ?>"
                            <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                    <?php endif; ?>
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
                    <a href="<?= SITE_URL ?>/category/<?= htmlspecialchars($cat['duong_dan']) ?>" class="cat-img-card">
                        <div class="cat-img-thumb">
                            <img src="<?= ASSETS_URL ?>/images/<?= htmlspecialchars($cat['img']) ?>"
                                alt="<?= htmlspecialchars($cat['ten']) ?>" loading="lazy"
                                onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/images/no-image.webp'">
                        </div>
                        <div class="cat-img-name"><?= htmlspecialchars($cat['ten']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     SẢN PHẨM NỔI BẬT — 4 hiện, còn lại ẩn + nút Xem thêm
     ========================================================= -->
<?php if (!empty($featured)): ?>
    <section class="section section-products">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
            </div>
            <div class="pg-grid">
                <?php $visibleFeat = 6; ?>
                <?php foreach ($featured as $i => $product): ?>
                    <div class="<?= $i >= $visibleFeat ? 'more-item more-item-feat' : '' ?>" style="<?= $i >= $visibleFeat ? 'display:none' : '' ?>">
                        <?php include __DIR__ . '/../san_pham/_the_san_pham.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($featured) > $visibleFeat): ?>
            <div class="show-more-wrap">
                <button type="button" class="show-more-btn" data-section="more-item-feat"
                        onclick="toggleShowMore('more-item-feat', this)">
                    <span>Xem thêm</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<!-- =========================================================
     SẢN PHẨM BÁN CHẠY — 4 hiện, còn lại ẩn + nút Xem thêm
     ========================================================= -->
<?php if (!empty($bestSellers)): ?>
    <section class="section section-products">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">Sản phẩm bán chạy</h2>
            </div>
            <div class="pg-grid">
                <?php $visibleBest = 6; ?>
                <?php foreach ($bestSellers as $i => $product): ?>
                    <div class="<?= $i >= $visibleBest ? 'more-item more-item-best' : '' ?>" style="<?= $i >= $visibleBest ? 'display:none' : '' ?>">
                        <?php include __DIR__ . '/../san_pham/_the_san_pham.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($bestSellers) > $visibleBest): ?>
            <div class="show-more-wrap">
                <button type="button" class="show-more-btn" data-section="more-item-best"
                        onclick="toggleShowMore('more-item-best', this)">
                    <span>Xem thêm</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <?php endif; ?>
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
    (function () {
        var el = document.getElementById('bannerSlider');
        if (!el) return;
        var items = el.querySelectorAll('.banner-slide');
        if (items.length < 2) return;
        var cur = 0, timer;

        var goTo = function (n) {
            items[cur].classList.remove('active');
            cur = (n + items.length) % items.length;
            items[cur].classList.add('active');
        };
        var startTimer = function () {
            clearInterval(timer);
            timer = setInterval(function () { goTo(cur + 1); }, 3000);
        };

        var prev = document.getElementById('bannerPrev');
        var next = document.getElementById('bannerNext');
        if (prev) prev.addEventListener('click', function () { goTo(cur - 1); startTimer(); });
        if (next) next.addEventListener('click', function () { goTo(cur + 1); startTimer(); });

        startTimer();
    }());
</script>

<?php
$extraJs = ['slider.js'];
include ROOT_PATH . '/views/bo_cuc/chan_trang.php';
?>
