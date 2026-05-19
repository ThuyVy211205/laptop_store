<?php
/**
 * Reusable filter sidebar for product listing & category pages
 * Variables expected: $categories (array), $currentCategoryId (int|null)
 */
$currentCategoryId = $currentCategoryId ?? ($_GET['category'] ?? null);
$minPrice          = $_GET['min_price'] ?? '';
$maxPrice          = $_GET['max_price'] ?? '';
$selectedSort      = $_GET['sort'] ?? 'newest';
?>

<aside class="sidebar-filter">
    <!-- Categories -->
    <div class="filter-block">
        <h6 class="filter-title">
            <i class="fas fa-th-large me-2"></i>Danh mục
        </h6>
        <ul class="filter-category-list">
            <li>
                <a href="<?= SITE_URL ?>/products" class="<?= !$currentCategoryId ? 'active' : '' ?>">
                    <i class="fas fa-th"></i> Tất cả sản phẩm
                </a>
            </li>
            <?php foreach (($categories ?? []) as $cat): ?>
            <li>
                <a href="<?= SITE_URL ?>/category/<?= htmlspecialchars($cat['slug']) ?>"
                   class="<?= $currentCategoryId == $cat['id'] ? 'active' : '' ?>">
                    <i class="fas fa-<?= htmlspecialchars($cat['icon'] ?? 'folder') ?>"></i>
                    <?= htmlspecialchars($cat['name']) ?>
                    <?php if (!empty($cat['product_count'])): ?>
                    <span class="count">(<?= $cat['product_count'] ?>)</span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Price Range -->
    <div class="filter-block">
        <h6 class="filter-title">
            <i class="fas fa-tags me-2"></i>Khoảng giá
        </h6>
        <form method="GET" action="" class="price-filter-form">
            <?php
            // Preserve existing query params
            foreach ($_GET as $key => $val) {
                if (!in_array($key, ['min_price', 'max_price', 'page'])):
            ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
            <?php endif; } ?>

            <div class="price-options">
                <?php
                $priceRanges = [
                    ['min' => 0,        'max' => 10000000,  'label' => 'Dưới 10 triệu'],
                    ['min' => 10000000, 'max' => 20000000,  'label' => '10 - 20 triệu'],
                    ['min' => 20000000, 'max' => 30000000,  'label' => '20 - 30 triệu'],
                    ['min' => 30000000, 'max' => 50000000,  'label' => '30 - 50 triệu'],
                    ['min' => 50000000, 'max' => 0,         'label' => 'Trên 50 triệu'],
                ];
                foreach ($priceRanges as $range):
                    $checked = ($minPrice == $range['min'] && $maxPrice == $range['max']) ? 'checked' : '';
                ?>
                <label class="price-option">
                    <input type="radio" name="price_range" value="<?= $range['min'] ?>-<?= $range['max'] ?>"
                           data-min="<?= $range['min'] ?>" data-max="<?= $range['max'] ?>" <?= $checked ?>>
                    <span><?= $range['label'] ?></span>
                </label>
                <?php endforeach; ?>
            </div>

            <div class="price-custom mt-3">
                <input type="number" name="min_price" placeholder="Từ" min="0"
                       value="<?= htmlspecialchars($minPrice) ?>" class="form-control form-control-sm mb-2">
                <input type="number" name="max_price" placeholder="Đến" min="0"
                       value="<?= htmlspecialchars($maxPrice) ?>" class="form-control form-control-sm mb-2">
                <button type="submit" class="btn btn-tech w-100 btn-sm">
                    <i class="fas fa-filter me-1"></i>Áp dụng
                </button>
            </div>
        </form>
    </div>

    <!-- Sort -->
    <div class="filter-block">
        <h6 class="filter-title">
            <i class="fas fa-sort me-2"></i>Sắp xếp
        </h6>
        <ul class="filter-sort-list">
            <?php
            $sortOptions = [
                'newest'      => 'Mới nhất',
                'price_asc'   => 'Giá: Thấp đến cao',
                'price_desc'  => 'Giá: Cao đến thấp',
                'best_seller' => 'Bán chạy nhất',
                'rating'      => 'Đánh giá cao',
            ];
            $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
            $params  = $_GET;
            foreach ($sortOptions as $val => $label):
                $params['sort'] = $val;
                $url = $baseUrl . '?' . http_build_query($params);
            ?>
            <li>
                <a href="<?= $url ?>" class="<?= $selectedSort === $val ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Voucher Promo Block -->
    <div class="filter-block filter-voucher-block">
        <h6 class="filter-title">
            <i class="fas fa-gift me-2"></i>Voucher đang có
        </h6>
        <div class="voucher-promo">
            <span class="voucher-code-promo">TECH10</span>
            <p class="mb-0">Giảm 10% đơn từ 500K</p>
        </div>
        <div class="voucher-promo">
            <span class="voucher-code-promo">WELCOME500</span>
            <p class="mb-0">Giảm 500K đơn từ 5M</p>
        </div>
    </div>
</aside>

<script>
// Price range radio → set min/max inputs
document.querySelectorAll('input[name="price_range"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const minInput = document.querySelector('input[name="min_price"]');
        const maxInput = document.querySelector('input[name="max_price"]');
        if (minInput) minInput.value = this.dataset.min;
        if (maxInput) maxInput.value = this.dataset.max == '0' ? '' : this.dataset.max;
    });
});
</script>