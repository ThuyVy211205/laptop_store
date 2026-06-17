<?php
$flashSale = !empty($_GET['flash_sale']);
$extraCss  = $flashSale ? ['product.css', 'promotions.css'] : ['product.css'];
include ROOT_PATH . '/views/bo_cuc/dau_trang.php';

// ── normalise variables so the view works from any controller path ──
$pageTitle          = $pageTitle          ?? 'Sản phẩm';
$pageSubtitle       = $pageSubtitle       ?? '';
$pageType           = $pageType           ?? 'laptop';
$currentSort        = $currentSort        ?? ($_GET['sort'] ?? 'newest');
$sidebarCategories  = $sidebarCategories  ?? [];
$selectedCategories = $selectedCategories ?? [];
$selectedPriceRanges= $selectedPriceRanges?? [];
$products           = $products           ?? [];
$totalCount         = $totalCount         ?? 0;
$totalPages         = $totalPages         ?? 1;
$currentPage        = max(1, (int)($_GET['page'] ?? 1));
$groupedProducts    = $groupedProducts    ?? null;

$priceRangeOptions = [
    ['value' => '0-10000000',         'label' => 'Dưới 10 triệu'],
    ['value' => '10000000-20000000',  'label' => 'Từ 10 – 20 triệu'],
    ['value' => '20000000+',          'label' => 'Trên 20 triệu'],
];

$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
$qParams = $_GET;
?>

<?php if ($flashSale): ?>
<?php
// ── Extra data for category deal tabs ──
$gamingProducts = db()->fetchAll(
    "SELECT p.*, c.ten AS ten_danh_muc, c.duong_dan AS duong_dan_danh_muc
     FROM san_pham p
     LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
     WHERE (c.duong_dan LIKE '%gaming%' OR c.ten LIKE '%Gaming%')
       AND p.ton_kho > 0
     ORDER BY p.ngay_tao DESC LIMIT 4"
);
$officeProducts = db()->fetchAll(
    "SELECT p.*, c.ten AS ten_danh_muc, c.duong_dan AS duong_dan_danh_muc
     FROM san_pham p
     LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
     WHERE (c.duong_dan LIKE '%laptop%' AND c.duong_dan NOT LIKE '%gaming%')
       AND p.ton_kho > 0
     ORDER BY p.ngay_tao DESC LIMIT 4"
);
$accessoryProducts = db()->fetchAll(
    "SELECT p.*, c.ten AS ten_danh_muc, c.duong_dan AS duong_dan_danh_muc
     FROM san_pham p
     LEFT JOIN danh_muc c ON p.id_danh_muc = c.id
     WHERE (c.duong_dan LIKE '%phu-kien%'
            OR c.ten LIKE '%chuột%'
            OR c.ten LIKE '%tai nghe%'
            OR c.ten LIKE '%bàn phím%')
       AND p.ton_kho > 0
     ORDER BY p.ngay_tao DESC LIMIT 4"
);
if (empty($gamingProducts))    $gamingProducts    = array_slice($products, 0, 4);
if (empty($officeProducts))    $officeProducts    = array_slice($products, 0, 4);
if (empty($accessoryProducts)) $accessoryProducts = array_slice($products, 0, 4);
?>

<!-- ===================== BREADCRUMB ===================== -->
<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Khuyến Mãi</li>
        </ol>
    </div>
</nav>

<!-- ===================== HERO BANNER ===================== -->
<section class="promo-hero">
    <div class="container">
        <div class="promo-hero__inner">
            <div class="promo-hero__tag">
                <i class="fas fa-circle"></i> ĐANG DIỄN RA
            </div>
            <h1 class="promo-hero__title">
                Siêu Sale<br><span class="gradient-text">Tháng Này</span>
            </h1>
            <p class="promo-hero__sub">
                Cơ hội vàng để sở hữu laptop &amp; phụ kiện công nghệ chính hãng với giá tốt nhất năm — giảm đến 40%!
            </p>
            <div class="promo-hero__actions">
                <a href="#flash-sale" class="promo-hero__cta">
                    <i class="fas fa-bolt"></i> Xem Flash Sale
                </a>
                <a href="#category-deals" class="promo-hero__cta-ghost">
                    <i class="fas fa-tags"></i> Ưu đãi danh mục
                </a>
            </div>
            <div class="promo-hero__stats">
                <div class="promo-hero__stat">
                    <span class="promo-hero__stat-num"><?= number_format($totalCount) ?>+</span>
                    <span class="promo-hero__stat-lbl">Sản phẩm giảm giá</span>
                </div>
                <div class="promo-hero__stat">
                    <span class="promo-hero__stat-num">40%</span>
                    <span class="promo-hero__stat-lbl">Giảm tối đa</span>
                </div>
                <div class="promo-hero__stat">
                    <span class="promo-hero__stat-num">4</span>
                    <span class="promo-hero__stat-lbl">Mã voucher</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== VOUCHER ZONE ===================== -->
<section class="promo-section promo-section--alt">
    <div class="container">
        <div class="promo-section-head">
            <h2 class="promo-section-title"><i class="fas fa-ticket-alt"></i> Voucher Zone</h2>
            <span class="text-muted" style="font-size:13px">Sao chép mã và áp dụng khi thanh toán</span>
        </div>
        <div class="voucher-grid">
            <div class="voucher-card vc--blue">
                <div class="voucher-inner">
                    <span class="voucher-tag">Laptop</span>
                    <div class="voucher-discount">10%</div>
                    <p class="voucher-desc">Giảm 10% cho tất cả laptop<br>Đơn hàng từ 15 triệu</p>
                    <span class="voucher-expiry"><i class="far fa-clock"></i> HH: 23:59 hôm nay</span>
                </div>
                <div class="voucher-footer">
                    <span class="voucher-code" id="vc1">LAPTOP10</span>
                    <button class="voucher-copy-btn" onclick="copyVoucher('vc1',this)"><i class="fas fa-copy"></i> Copy</button>
                </div>
            </div>
            <div class="voucher-card vc--red">
                <div class="voucher-inner">
                    <span class="voucher-tag">Flash Sale</span>
                    <div class="voucher-discount">15%</div>
                    <p class="voucher-desc">Giảm thêm 15% cho sản phẩm Flash Sale<br>Số lượng có hạn</p>
                    <span class="voucher-expiry"><i class="far fa-clock"></i> HH: 23:59 hôm nay</span>
                </div>
                <div class="voucher-footer">
                    <span class="voucher-code" id="vc2">FLASH15</span>
                    <button class="voucher-copy-btn" onclick="copyVoucher('vc2',this)"><i class="fas fa-copy"></i> Copy</button>
                </div>
            </div>
            <div class="voucher-card vc--amber">
                <div class="voucher-inner">
                    <span class="voucher-tag">Phụ kiện</span>
                    <div class="voucher-discount">20%</div>
                    <p class="voucher-desc">Giảm 20% toàn bộ phụ kiện<br>Không giới hạn giá trị đơn</p>
                    <span class="voucher-expiry"><i class="far fa-clock"></i> HH: 23:59 hôm nay</span>
                </div>
                <div class="voucher-footer">
                    <span class="voucher-code" id="vc3">ACC20</span>
                    <button class="voucher-copy-btn" onclick="copyVoucher('vc3',this)"><i class="fas fa-copy"></i> Copy</button>
                </div>
            </div>
            <div class="voucher-card vc--green">
                <div class="voucher-inner">
                    <span class="voucher-tag">Thành viên</span>
                    <div class="voucher-discount">5%</div>
                    <p class="voucher-desc">Dành riêng cho thành viên VQSTORE<br>Áp dụng mọi đơn hàng</p>
                    <span class="voucher-expiry"><i class="far fa-clock"></i> Không giới hạn</span>
                </div>
                <div class="voucher-footer">
                    <span class="voucher-code" id="vc4">VIP5</span>
                    <button class="voucher-copy-btn" onclick="copyVoucher('vc4',this)"><i class="fas fa-copy"></i> Copy</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FLASH SALE ===================== -->
<section class="promo-section" id="flash-sale">
    <div class="container">
        <div class="flash-header">
            <div class="flash-title-group">
                <div class="flash-icon"><i class="fas fa-bolt"></i></div>
                <div>
                    <h2 class="flash-title">FLASH <em>SALE</em></h2>
                    <p class="flash-sub">Giá sốc — Số lượng có hạn — Kết thúc lúc 23:59</p>
                </div>
            </div>
            <div class="flash-countdown">
                <div class="cd-block"><span class="cd-num" id="cd-h">00</span><span class="cd-lbl">Giờ</span></div>
                <span class="cd-sep">:</span>
                <div class="cd-block"><span class="cd-num" id="cd-m">00</span><span class="cd-lbl">Phút</span></div>
                <span class="cd-sep">:</span>
                <div class="cd-block"><span class="cd-num" id="cd-s">00</span><span class="cd-lbl">Giây</span></div>
            </div>
        </div>

        <?php if (!empty($products)): ?>
        <div class="flash-grid">
            <?php foreach ($products as $fp):
                $fpSale     = !empty($fp['gia_khuyen_mai']) ? (float)$fp['gia_khuyen_mai'] : (float)$fp['gia'];
                $fpOrig     = (float)$fp['gia'];
                $fpDiscount = calcDiscount($fpOrig, $fpSale);
                $fpStock    = max(0, (int)($fp['ton_kho'] ?? 0));
                $fpSold     = max(0, (int)($fp['so_luong_ban'] ?? 0));
                $fpTotal    = $fpSold + $fpStock;
                $fpPct      = $fpTotal > 0 ? min(100, round(($fpSold / $fpTotal) * 100)) : 30;

                // Đồng bộ bộ đếm badge với _product-card.php — tối đa 3 badge giảm giá mỗi trang
                if (!isset($GLOBALS['_pc_badge_discount'])) $GLOBALS['_pc_badge_discount'] = 0;
                if (!isset($GLOBALS['_pc_badge_flash']))    $GLOBALS['_pc_badge_flash']    = 0;
                $fpShowDiscount = $fpDiscount > 0                       && $GLOBALS['_pc_badge_discount'] < 3;
                $fpShowFlash    = !empty($fp['la_flash_sale'])           && $GLOBALS['_pc_badge_flash']    < 2;
                if ($fpShowDiscount) $GLOBALS['_pc_badge_discount']++;
                if ($fpShowFlash)    $GLOBALS['_pc_badge_flash']++;
            ?>
            <?php $fpIsOut = $fpStock <= 0; ?>
            <div class="flash-card<?= $fpIsOut ? ' out-of-stock' : '' ?>">
                <?php if ($fpShowDiscount && !$fpIsOut): ?>
                <div class="fc-badge">-<?= $fpDiscount ?>%</div>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($fp['duong_dan']) ?>" class="fc-img">
                    <?php if ($fpIsOut): ?>
                    <div class="out-of-stock-overlay">
                        <span class="out-of-stock-badge">Hết hàng</span>
                    </div>
                    <?php endif; ?>
                    <img src="<?= imgUrl($fp['hinh_thu_nho']) ?>"
                         alt="<?= htmlspecialchars($fp['ten']) ?>"
                         onerror="this.onerror=null;this.src='<?= noImageUrl() ?>'"
                </a>
                <div class="fc-body">
                    <p class="fc-name"><?= htmlspecialchars($fp['ten']) ?></p>
                    <div class="fc-prices">
                        <span class="fc-price-new<?= $fpIsOut ? ' text-muted' : '' ?>"><?= formatPrice($fpSale) ?></span>
                        <?php if ($fpShowDiscount && !$fpIsOut): ?>
                        <span class="fc-price-old"><?= formatPrice($fpOrig) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($fpIsOut): ?>
                    <span class="card-out-of-stock-label">Tạm hết hàng</span>
                    <?php else: ?>
                    <button class="fc-btn btn-add-cart" data-id="<?= (int)$fp['id'] ?>">
                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-center text-muted py-5">Hiện chưa có sản phẩm Flash Sale.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== CATEGORY DEALS (TABS) ===================== -->
<section class="promo-section promo-section--alt" id="category-deals">
    <div class="container">
        <div class="promo-section-head">
            <h2 class="promo-section-title"><i class="fas fa-tags"></i> Ưu Đãi Theo Danh Mục</h2>
            <a href="<?= SITE_URL ?>/products" class="promo-section-more">
                Xem tất cả <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="deals-tabs-nav">
            <button class="deals-tab-btn active" data-tab="gaming">
                <i class="fas fa-gamepad"></i> Gaming
            </button>
            <button class="deals-tab-btn" data-tab="office">
                <i class="fas fa-briefcase"></i> Văn phòng
            </button>
            <button class="deals-tab-btn" data-tab="accessories">
                <i class="fas fa-headphones"></i> Phụ kiện
            </button>
        </div>
        <div class="deals-panel active" id="tab-gaming">
            <?php foreach ($gamingProducts as $product): ?>
            <?php include __DIR__ . '/_the_san_pham.php'; ?>
            <?php endforeach; ?>
        </div>
        <div class="deals-panel" id="tab-office">
            <?php foreach ($officeProducts as $product): ?>
            <?php include __DIR__ . '/_the_san_pham.php'; ?>
            <?php endforeach; ?>
        </div>
        <div class="deals-panel" id="tab-accessories">
            <?php foreach ($accessoryProducts as $product): ?>
            <?php include __DIR__ . '/_the_san_pham.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== PRIVILEGES BANNER ===================== -->
<section class="promo-privileges">
    <div class="container">
        <div class="privileges-head">
            <h2>Đặc Quyền Thành Viên VQSTORE</h2>
            <p>Đăng ký ngay để nhận các ưu đãi độc quyền chỉ dành cho thành viên</p>
        </div>
        <div class="privilege-grid">
            <div class="privilege-card">
                <div class="privilege-icon pi--blue"><i class="fas fa-shield-alt"></i></div>
                <h3 class="privilege-title">Bảo Hành Chính Hãng</h3>
                <p class="privilege-desc">Tất cả sản phẩm được bảo hành chính hãng 12–24 tháng. Hỗ trợ kỹ thuật 24/7 qua hotline.</p>
                <span class="privilege-badge"><i class="fas fa-check"></i> Áp dụng ngay</span>
            </div>
            <div class="privilege-card">
                <div class="privilege-icon pi--amber"><i class="fas fa-truck"></i></div>
                <h3 class="privilege-title">Giao Hàng Miễn Phí</h3>
                <p class="privilege-desc">Miễn phí giao hàng toàn quốc cho đơn từ 5 triệu đồng. Giao trong 2–5 ngày làm việc.</p>
                <span class="privilege-badge"><i class="fas fa-check"></i> Đơn từ 5 triệu</span>
            </div>
            <div class="privilege-card">
                <div class="privilege-icon pi--green"><i class="fas fa-undo"></i></div>
                <h3 class="privilege-title">Đổi Trả Dễ Dàng</h3>
                <p class="privilege-desc">Đổi trả trong 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả. Hoàn tiền nhanh chóng.</p>
                <span class="privilege-badge"><i class="fas fa-check"></i> 7 ngày đổi trả</span>
            </div>
        </div>
    </div>
</section>

<script>
// Đếm ngược đến 23:59:59 cho flash sale
(function() {
    function tick() {
        var now  = new Date();
        var end  = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
        var diff = Math.max(0, Math.floor((end - now) / 1000));
        var h = Math.floor(diff / 3600);
        var m = Math.floor((diff % 3600) / 60);
        var s = diff % 60;
        document.getElementById('cd-h').textContent = String(h).padStart(2,'0');
        document.getElementById('cd-m').textContent = String(m).padStart(2,'0');
        document.getElementById('cd-s').textContent = String(s).padStart(2,'0');
    }
    tick();
    setInterval(tick, 1000);
})();

// Sao chép mã voucher vào clipboard
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

// Chuyển đổi tab danh mục trong phần deals
document.querySelectorAll('.deals-tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.deals-tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.deals-panel').forEach(function(p) { p.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});
</script>

<?php else: ?>

<!-- ===================== BREADCRUMB ===================== -->
<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a>
            </li>
            <?php if (!empty($category)): ?>
                <li class="breadcrumb-item">
                    <a href="<?= SITE_URL ?>/products">Sản phẩm</a>
                </li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($category['ten']) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item active">
                    <?= htmlspecialchars($pageTitle) ?>
                </li>
            <?php endif; ?>
        </ol>
    </div>
</nav>


<!-- ===================== MAIN BODY ===================== -->
<div class="container py-4">

    <!-- Page header -->
    <div class="pl-page-header">
        <h1 class="pl-page-title"><?= htmlspecialchars($pageTitle) ?></h1>
        <?php if ($pageSubtitle): ?>
        <span class="pl-page-subtitle"><?= htmlspecialchars($pageSubtitle) ?></span>
        <?php endif; ?>
    </div>

    <!-- Two-column layout -->
    <div class="pl-layout">

        <!-- ───────── SIDEBAR ───────── -->
        <aside class="pl-sidebar">
            <?php
            $clearParams = array_diff_key($qParams, array_flip(['categories', 'price_ranges', 'min_price', 'max_price', 'page']));
            $clearUrl    = $baseUrl . (!empty($clearParams) ? '?' . http_build_query($clearParams) : '');
            ?>
            <form id="pl-filter-form" method="GET" action="<?= htmlspecialchars($baseUrl) ?>">
                <!-- preserve sort & type & search -->
                <?php foreach ($qParams as $k => $v) {
                    if (in_array($k, ['categories', 'price_ranges', 'min_price', 'max_price', 'page'])) continue;
                    if (is_array($v)) {
                        foreach ($v as $vi):
                ?>
                <input type="hidden" name="<?= htmlspecialchars($k) ?>[]"
                       value="<?= htmlspecialchars($vi) ?>">
                <?php   endforeach;
                    } else { ?>
                <input type="hidden" name="<?= htmlspecialchars($k) ?>"
                       value="<?= htmlspecialchars($v) ?>">
                <?php }} ?>

                <div class="pl-sidebar-card">
                    <div class="pl-sidebar-head">
                        <i class="fas fa-sliders-h me-2"></i>Bộ lọc sản phẩm
                    </div>

                    <!-- Group: Dòng máy / Loại phụ kiện -->
                    <?php if (!empty($sidebarCategories)): ?>
                    <div class="pl-filter-group">
                        <div class="pl-filter-group-title">
                            <?= $pageType === 'phu-kien' ? 'Loại phụ kiện' : 'Dòng máy' ?>
                        </div>
                        <?php foreach ($sidebarCategories as $cat): ?>
                        <label class="pl-checkbox-item">
                            <input type="checkbox"
                                   name="categories[]"
                                   value="<?= (int)$cat['id'] ?>"
                                   <?= in_array((int)$cat['id'], $selectedCategories) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($cat['ten']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Group: Mức giá -->
                    <div class="pl-filter-group">
                        <div class="pl-filter-group-title">Mức giá</div>
                        <?php foreach ($priceRangeOptions as $opt): ?>
                        <label class="pl-checkbox-item">
                            <input type="checkbox"
                                   name="price_ranges[]"
                                   value="<?= $opt['value'] ?>"
                                   <?= in_array($opt['value'], $selectedPriceRanges) ? 'checked' : '' ?>>
                            <span><?= $opt['label'] ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Clear button -->
                    <button type="button" class="pl-clear-btn"
                            onclick="window.location.href='<?= htmlspecialchars($clearUrl) ?>'">Xóa bộ lọc
                    </button>
                </div>
            </form>
        </aside>

        <!-- ───────── MAIN CONTENT ───────── -->
        <div class="pl-main">

            <!-- Top bar -->
            <div class="pl-topbar">
                <span class="pl-count-text">
                    Hiển thị <strong><?= count($products) ?></strong>
                    trong <strong><?= number_format($totalCount) ?></strong> sản phẩm
                </span>
                <div class="pl-sort-group">
                    <span class="pl-sort-label">Sắp xếp theo:</span>
                    <?php
                    $sortOptions = [
                        'newest'     => 'Mới nhất',
                        'price_asc'  => 'Giá thấp → cao',
                        'price_desc' => 'Giá cao → thấp',
                    ];
                    foreach ($sortOptions as $val => $label):
                        $sp = $qParams;
                        $sp['sort'] = $val;
                        unset($sp['page']);
                        $sortUrl = $baseUrl . '?' . http_build_query($sp);
                        $active  = ($currentSort === $val) ? 'active' : '';
                    ?>
                    <a href="<?= htmlspecialchars($sortUrl) ?>"
                       class="pl-sort-btn <?= $active ?>"><?= $label ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Product grid — grouped by category when multiple selected -->
            <?php if (!empty($groupedProducts)): ?>
                <?php foreach ($groupedProducts as $group): ?>
                <div class="pl-category-group">
                    <div class="pl-category-group-header">
                        <h5 class="pl-category-group-title">
                            <i class="fas fa-layer-group me-2"></i><?= htmlspecialchars($group['ten']) ?>
                        </h5>
                        <span class="badge bg-secondary"><?= count($group['products']) ?> sản phẩm</span>
                    </div>
                    <div class="pl-product-grid">
                        <?php foreach ($group['products'] as $product): ?>
                        <?php include __DIR__ . '/_the_san_pham.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

            <?php elseif (!empty($products)): ?>
            <div class="pl-product-grid">
                <?php foreach ($products as $product): ?>
                <?php include __DIR__ . '/_the_san_pham.php'; ?>
                <?php endforeach; ?>
            </div>

            <?= paginate($totalPages, $currentPage, $baseUrl, $qParams) ?>

            <?php else: ?>
            <div class="empty-state text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
                <h4>Không tìm thấy sản phẩm nào</h4>
                <p class="text-muted">Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                <a href="<?= htmlspecialchars($clearUrl) ?>" class="btn btn-tech mt-2">
                    <i class="fas fa-redo me-2"></i>Xóa bộ lọc
                </a>
            </div>
            <?php endif; ?>

        </div><!-- /.pl-main -->
    </div><!-- /.pl-layout -->
</div><!-- /.container -->

<script>
// Auto-submit filter form on checkbox change
document.querySelectorAll('#pl-filter-form input[type="checkbox"]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        document.getElementById('pl-filter-form').submit();
    });
});
</script>

<?php endif; ?>

<?php include ROOT_PATH . '/views/bo_cuc/chan_trang.php'; ?>
