<?php
$extraCss = ['product.css', 'promotions.css'];
include ROOT_PATH . '/views/layouts/header.php';

$vcColors = ['vc--blue', 'vc--red', 'vc--amber', 'vc--green'];
?>

<!-- ── BREADCRUMB ─────────────────────────────────────────────────── -->
<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Khuyến Mãi</li>
        </ol>
    </div>
</nav>

<!-- ── HERO BANNER ───────────────────────────────────────────────── -->
<section class="promo-hero">
    <div class="container">
        <div class="promo-hero__img-wrap">
            <?php
                $promoImgUrl = !empty($promoBanner['image'])
                    ? SITE_URL . '/' . ltrim($promoBanner['image'], '/')
                    : ASSETS_URL . '/images/banner-sale.jpg';
                $promoAlt = $promoBanner['title'] ?? 'Siêu Sale';
            ?>
            <img src="<?= htmlspecialchars($promoImgUrl) ?>"
                 alt="<?= htmlspecialchars($promoAlt) ?>" class="promo-hero__img">
            <div class="promo-hero__overlay">
                <div class="promo-hero__stats">
                    <div class="promo-hero__stat">
                        <span class="promo-hero__stat-num"><?= count($products) ?>+</span>
                        <span class="promo-hero__stat-lbl">Sản phẩm giảm giá</span>
                    </div>
                    <div class="promo-hero__stat">
                        <span class="promo-hero__stat-num">50%</span>
                        <span class="promo-hero__stat-lbl">Giảm tối đa</span>
                    </div>
                    <div class="promo-hero__stat">
                        <span class="promo-hero__stat-num"><?= count($vouchers) ?></span>
                        <span class="promo-hero__stat-lbl">Mã voucher</span>
                    </div>
                </div>
                <div class="promo-hero__actions">
                    <a href="#promo-products" class="promo-hero__cta">
                        <i class="fas fa-bolt"></i> Xem ngay
                    </a>
                    <a href="#voucher-zone" class="promo-hero__cta-ghost">
                        <i class="fas fa-ticket-alt"></i> Lấy voucher
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── VOUCHER ZONE ───────────────────────────────────────────────── -->
<?php if (!empty($vouchers)): ?>
<section class="promo-section" id="voucher-zone">
    <div class="container">
        <div class="promo-section-head">
            <h2 class="promo-section-title"><i class="fas fa-ticket-alt"></i> Voucher Zone</h2>
            <span class="text-muted" style="font-size:13px">Sao chép mã và áp dụng khi thanh toán</span>
        </div>
        <div class="voucher-grid">
            <?php foreach ($vouchers as $i => $v):
                $color     = $vcColors[$i % count($vcColors)];
                $discLabel = $v['type'] === 'percent'
                    ? $v['value'] . '%'
                    : number_format($v['value'], 0, ',', '.') . 'đ';
                $minLabel  = $v['min_order'] > 0
                    ? 'Đơn từ ' . number_format($v['min_order'], 0, ',', '.') . 'đ'
                    : 'Không giới hạn';
                $maxLabel  = $v['max_discount']
                    ? 'Giảm tối đa ' . number_format($v['max_discount'], 0, ',', '.') . 'đ'
                    : '';
                $expLabel  = $v['expires_at']
                    ? date('d/m/Y', strtotime($v['expires_at']))
                    : 'Không giới hạn';
                $vcId = 'vc_' . $v['id'];
            ?>
            <div class="voucher-card <?= $color ?>">
                <div class="voucher-inner">
                    <span class="voucher-tag"><?= $v['type'] === 'percent' ? 'Giảm %' : 'Giảm tiền' ?></span>
                    <div class="voucher-discount"><?= $discLabel ?></div>
                    <p class="voucher-desc">
                        <?= $minLabel ?><br>
                        <?= $maxLabel ?>
                    </p>
                    <span class="voucher-expiry">
                        <i class="far fa-clock"></i>
                        HSD: <?= $expLabel ?>
                    </span>
                </div>
                <div class="voucher-footer">
                    <span class="voucher-code" id="<?= $vcId ?>"><?= htmlspecialchars($v['code']) ?></span>
                    <button class="voucher-copy-btn" onclick="copyVoucher('<?= $vcId ?>',this)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── SẢN PHẨM KHUYẾN MÃI ───────────────────────────────────────── -->
<section class="promo-section" id="promo-products">
    <div class="container">
        <div class="promo-section-head">
            <h2 class="promo-section-title">
                <i class="fas fa-fire"></i> Sản phẩm giảm giá hôm nay
            </h2>
            <div class="promo-tabs">
                <button class="promo-tab active" data-filter="all">Tất cả</button>
                <button class="promo-tab" data-filter="laptop"><i class="fas fa-laptop me-1"></i>Laptop</button>
                <button class="promo-tab" data-filter="phukien"><i class="fas fa-headphones me-1"></i>Phụ kiện</button>
            </div>
        </div>

        <?php if (!empty($products)): ?>
        <div class="promo-product-grid">
            <?php foreach ($products as $product): ?>
            <?php include ROOT_PATH . '/views/products/_product-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-center py-5 text-muted">Hiện chưa có sản phẩm khuyến mãi.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ── CHÍNH SÁCH ─────────────────────────────────────────────────── -->
<section class="promo-section">
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
    // Inject badge giảm giá góc trên trái chỉ trong trang khuyến mãi
    document.querySelectorAll('.promo-product-grid .product-card').forEach(function (card) {
        var tag = card.querySelector('.price-discount-tag');
        if (!tag) return;
        var pct = tag.textContent.trim().replace(/^-/, ''); // "-20%" → "20%"
        var badge = document.createElement('span');
        badge.className = 'discount-badge';
        badge.textContent = 'Giảm ' + pct;
        card.insertBefore(badge, card.firstChild);
    });
}());

(function () {
    var laptopSlugs = ['laptop-gaming', 'laptop-van-phong', 'macbook'];
    var tabs  = document.querySelectorAll('.promo-tabs .promo-tab');
    var cards = document.querySelectorAll('.promo-product-grid .product-card');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');

            var filter = tab.dataset.filter;
            cards.forEach(function (card) {
                var slug = card.dataset.cat || '';
                var show = filter === 'all'
                    ? true
                    : filter === 'laptop'
                        ? laptopSlugs.indexOf(slug) !== -1
                        : slug !== '' && laptopSlugs.indexOf(slug) === -1;
                card.style.display = show ? '' : 'none';
            });
        });
    });
}());

function copyVoucher(id, btn) {
    var code = document.getElementById(id).textContent.trim();
    navigator.clipboard.writeText(code).then(function() {
        btn.classList.add('copied');
        btn.innerHTML = '<i class="fas fa-check"></i> Đã copy!';
        setTimeout(function() {
            btn.classList.remove('copied');
            btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
        }, 2000);
    });
}
</script>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>
