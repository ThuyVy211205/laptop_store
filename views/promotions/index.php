<?php
$extraCss = ['home.css', 'promotions.css'];
include ROOT_PATH . '/views/layouts/header.php';
?>

<!-- ─────────────────────────────────────────────────────────────
     HERO
     ───────────────────────────────────────────────────────────── -->
<section class="promo-hero">
    <div class="container">
        <div class="promo-hero__inner">
            <span class="promo-hero__badge"><i class="fas fa-bolt me-1"></i>Ưu đãi độc quyền</span>
            <h1 class="promo-hero__title">KHUYẾN MÃI <em>HOT</em></h1>
            <p class="promo-hero__sub">Hàng ngàn sản phẩm giảm giá sâu — chỉ trong thời gian có hạn!</p>
        </div>
    </div>
</section>


<!-- ─────────────────────────────────────────────────────────────
     FLASH SALE
     ───────────────────────────────────────────────────────────── -->
<?php if (!empty($flashProducts)): ?>
<section class="section section-products">
    <div class="container">
        <div class="section-card">
            <div class="section-head">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="promo-flash-tag"><i class="fas fa-bolt"></i> FLASH SALE</span>
                    <h2 class="section-title">Giá sốc hôm nay</h2>
                </div>
                <a href="<?= SITE_URL ?>/products" class="section-more">
                    Xem tất cả <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
            <div class="pg-grid">
                <?php foreach ($flashProducts as $product): ?>
                <?php include ROOT_PATH . '/views/products/_product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ─────────────────────────────────────────────────────────────
     DEAL BANNERS
     ───────────────────────────────────────────────────────────── -->
<section class="section section-products">
    <div class="container">
        <div class="promo-deals">
            <a href="<?= SITE_URL ?>/category/laptop-gaming" class="promo-deal promo-deal--gaming">
                <div class="promo-deal__inner">
                    <span class="promo-deal__label">Gaming</span>
                    <h3 class="promo-deal__heading">Giảm đến <strong>30%</strong></h3>
                    <p class="promo-deal__sub">Laptop Gaming cao cấp</p>
                    <span class="promo-deal__btn">Mua ngay <i class="fas fa-arrow-right ms-1"></i></span>
                </div>
            </a>
            <a href="<?= SITE_URL ?>/category/laptop-van-phong" class="promo-deal promo-deal--office">
                <div class="promo-deal__inner">
                    <span class="promo-deal__label">Văn phòng</span>
                    <h3 class="promo-deal__heading">Giảm đến <strong>25%</strong></h3>
                    <p class="promo-deal__sub">Laptop văn phòng mỏng nhẹ</p>
                    <span class="promo-deal__btn">Mua ngay <i class="fas fa-arrow-right ms-1"></i></span>
                </div>
            </a>
            <a href="<?= SITE_URL ?>/products/accessories" class="promo-deal promo-deal--access">
                <div class="promo-deal__inner">
                    <span class="promo-deal__label">Phụ kiện</span>
                    <h3 class="promo-deal__heading">Giảm đến <strong>40%</strong></h3>
                    <p class="promo-deal__sub">Chuột, bàn phím, tai nghe</p>
                    <span class="promo-deal__btn">Mua ngay <i class="fas fa-arrow-right ms-1"></i></span>
                </div>
            </a>
        </div>
    </div>
</section>


<!-- ─────────────────────────────────────────────────────────────
     TABS: Gaming / Văn phòng / Phụ kiện
     ───────────────────────────────────────────────────────────── -->
<section class="section section-products">
    <div class="container">
        <div class="section-card">
            <div class="section-head">
                <div class="promo-tabs" id="promoTabs">
                    <button class="promo-tab active" data-tab="gaming">
                        <i class="fas fa-gamepad me-1"></i>Gaming
                    </button>
                    <button class="promo-tab" data-tab="office">
                        <i class="fas fa-briefcase me-1"></i>Văn phòng
                    </button>
                    <button class="promo-tab" data-tab="access">
                        <i class="fas fa-headphones me-1"></i>Phụ kiện
                    </button>
                </div>
                <a href="<?= SITE_URL ?>/products" class="section-more">
                    Xem tất cả <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>

            <div class="promo-panel active" id="promo-gaming">
                <?php if (!empty($gamingProducts)): ?>
                <div class="pg-grid">
                    <?php foreach ($gamingProducts as $product): ?>
                    <?php include ROOT_PATH . '/views/products/_product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-center py-4 text-muted">Không có sản phẩm.</p>
                <?php endif; ?>
            </div>

            <div class="promo-panel" id="promo-office">
                <?php if (!empty($officeProducts)): ?>
                <div class="pg-grid">
                    <?php foreach ($officeProducts as $product): ?>
                    <?php include ROOT_PATH . '/views/products/_product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-center py-4 text-muted">Không có sản phẩm.</p>
                <?php endif; ?>
            </div>

            <div class="promo-panel" id="promo-access">
                <?php if (!empty($accessoryProducts)): ?>
                <div class="pg-grid">
                    <?php foreach ($accessoryProducts as $product): ?>
                    <?php include ROOT_PATH . '/views/products/_product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-center py-4 text-muted">Không có sản phẩm.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>


<!-- ─────────────────────────────────────────────────────────────
     CHÍNH SÁCH
     ───────────────────────────────────────────────────────────── -->
<section class="section" style="padding-top:0;padding-bottom:40px">
    <div class="container">
        <div class="why-us-grid">
            <div class="why-item">
                <i class="fas fa-tag"></i>
                <h6>Giá tốt nhất</h6>
                <small>Cam kết giá cạnh tranh</small>
            </div>
            <div class="why-item">
                <i class="fas fa-shield-alt"></i>
                <h6>Chính hãng 100%</h6>
                <small>Nguồn gốc rõ ràng</small>
            </div>
            <div class="why-item">
                <i class="fas fa-shipping-fast"></i>
                <h6>Giao hàng nhanh</h6>
                <small>Free ship đơn từ 500K</small>
            </div>
            <div class="why-item">
                <i class="fas fa-undo"></i>
                <h6>Đổi trả dễ dàng</h6>
                <small>30 ngày hoàn tiền</small>
            </div>
        </div>
    </div>
</section>


<script>
(function () {
    var tabs   = document.querySelectorAll('.promo-tab');
    var panels = document.querySelectorAll('.promo-panel');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('active'); });
            panels.forEach(function (p) { p.classList.remove('active'); });
            tab.classList.add('active');
            var panel = document.getElementById('promo-' + tab.dataset.tab);
            if (panel) panel.classList.add('active');
        });
    });
}());
</script>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>
