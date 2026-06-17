<?php
$extraCss = ['product.css'];
include ROOT_PATH . '/views/bo_cuc/dau_trang.php';

// ── normalise variables ──
$pageTitle           = $pageTitle           ?? 'Phụ kiện';
$pageSubtitle        = $pageSubtitle        ?? 'Khám phá tất cả phụ kiện công nghệ tại VQSTORE';
$pageType            = 'phu-kien';
$currentSort         = $currentSort         ?? ($_GET['sort'] ?? 'newest');
$sidebarCategories   = $sidebarCategories   ?? [];
$selectedCategories  = $selectedCategories  ?? [];
$selectedPriceRanges = $selectedPriceRanges ?? [];
$products            = $products            ?? [];
$totalCount          = $totalCount          ?? 0;
$totalPages          = $totalPages          ?? 1;
$currentPage         = max(1, (int)($_GET['page'] ?? 1));
$groupedProducts     = $groupedProducts     ?? null;

$priceRangeOptions = [
    ['value' => '0-10000000',        'label' => 'Dưới 10 triệu'],
    ['value' => '10000000-20000000', 'label' => 'Từ 10 – 20 triệu'],
    ['value' => '20000000+',         'label' => 'Trên 20 triệu'],
];

$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
$qParams = $_GET;
?>

<!-- ===================== BREADCRUMB ===================== -->
<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a>
            </li>
            <li class="breadcrumb-item active">Phụ kiện</li>
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
                <!-- preserve type & sort -->
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

                    <!-- Group: Loại phụ kiện -->
                    <?php if (!empty($sidebarCategories)): ?>
                    <div class="pl-filter-group">
                        <div class="pl-filter-group-title">Loại phụ kiện</div>
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
document.querySelectorAll('#pl-filter-form input[type="checkbox"]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        document.getElementById('pl-filter-form').submit();
    });
});
</script>

<?php include ROOT_PATH . '/views/bo_cuc/chan_trang.php'; ?>
