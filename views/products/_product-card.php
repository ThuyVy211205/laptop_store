<?php
/**
 * Product Card Component (partial)
 * Expects: $product (array)
 */
$price     = $product['price'];
$salePrice = $product['sale_price'] ?? null;
$discount  = $salePrice ? calcDiscount($price, $salePrice) : 0;
$finalPrice = $salePrice ?: $price;
?>
<div class="product-card" data-product-id="<?= $product['id'] ?>">
    <!-- Badges -->
    <div class="product-badges">
        <?php if ($discount > 0): ?>
        <span class="badge-discount">-<?= $discount ?>%</span>
        <?php endif; ?>
        <?php if (!empty($product['is_flash_sale'])): ?>
        <span class="badge-flash"><i class="fas fa-bolt"></i> FLASH</span>
        <?php endif; ?>
        <?php if (!empty($product['is_new'])): ?>
        <span class="badge-new">NEW</span>
        <?php endif; ?>
    </div>

    <!-- Wishlist & Quick view -->
    <div class="product-actions">
        <button class="action-btn wishlist-btn" data-id="<?= $product['id'] ?>" title="Yêu thích">
            <i class="far fa-heart"></i>
        </button>
        <button class="action-btn quickview-btn" data-id="<?= $product['id'] ?>" title="Xem nhanh">
            <i class="fas fa-eye"></i>
        </button>
    </div>

    <!-- Image -->
    <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($product['slug']) ?>" class="product-image">
        <img src="<?= imgUrl($product['thumbnail']) ?>"
             alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy"
             onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
    </a>

    <!-- Body -->
    <div class="product-body">
        <?php if (!empty($product['category_name'])): ?>
        <small class="product-cat"><?= htmlspecialchars($product['category_name']) ?></small>
        <?php endif; ?>

        <h6 class="product-name">
            <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($product['slug']) ?>">
                <?= htmlspecialchars($product['name']) ?>
            </a>
        </h6>

        <!-- Rating -->
        <?php if (!empty($product['rating_count']) && $product['rating_count'] > 0): ?>
        <div class="product-rating">
            <?= starRating($product['rating_avg']) ?>
            <small>(<?= $product['rating_count'] ?>)</small>
        </div>
        <?php endif; ?>

        <!-- Price -->
        <div class="product-price">
            <span class="price-current"><?= formatPrice($finalPrice) ?></span>
            <?php if ($salePrice && $salePrice < $price): ?>
            <span class="price-old"><?= formatPrice($price) ?></span>
            <?php endif; ?>
        </div>

        <!-- Stock progress for flash sale -->
        <?php if (!empty($product['is_flash_sale']) && !empty($product['sold_quantity'])): ?>
        <?php
            $sold  = $product['sold_quantity'];
            $stock = $sold + $product['stock'];
            $percent = $stock > 0 ? min(100, ($sold / $stock) * 100) : 0;
        ?>
        <div class="product-stock-bar">
            <div class="stock-bar-fill" style="width: <?= $percent ?>%"></div>
            <span class="stock-text"><i class="fas fa-fire"></i> Đã bán <?= $sold ?></span>
        </div>
        <?php endif; ?>

        <!-- Add to cart -->
        <button class="btn btn-tech btn-sm w-100 mt-2 btn-add-cart" data-id="<?= $product['id'] ?>">
            <i class="fas fa-shopping-cart me-1"></i>Thêm vào giỏ
        </button>
    </div>
</div>