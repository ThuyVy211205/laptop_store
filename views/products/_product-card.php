<?php
/**
 * Product Card Component (partial)
 * Expects: $product (array)
 */
$price      = $product['price'];
$salePrice  = $product['sale_price'] ?? null;
$discount   = $salePrice ? calcDiscount($price, $salePrice) : 0;
$finalPrice = $salePrice ?: $price;

// Limit discount display per page for natural ecommerce look
if (!isset($GLOBALS['_pc_badge_discount'])) $GLOBALS['_pc_badge_discount'] = 0;

// Products explicitly excluded from showing discount
$_noDiscountIds = [2, 3];

$showDiscount = $discount > 0
                && !in_array((int)$product['id'], $_noDiscountIds)
                && $GLOBALS['_pc_badge_discount'] < 3;

if ($showDiscount) $GLOBALS['_pc_badge_discount']++;
?>
<div class="product-card" data-product-id="<?= $product['id'] ?>">

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
    <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($product['slug']) ?>"
       class="product-img-wrap">
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
        <div class="product-rating">
            <?php if (!empty($product['rating_count']) && $product['rating_count'] > 0): ?>
            <span class="stars"><?= starRating($product['rating_avg']) ?></span>
            <small>(<?= $product['rating_count'] ?>)</small>
            <?php else: ?>
            <span class="stars"><?= starRating(0) ?></span>
            <small class="text-muted">Chưa có đánh giá</small>
            <?php endif; ?>
        </div>

        <!-- Price — vertical layout: current on top, old + discount tag below (max 3 discounts shown) -->
        <div class="product-price">
            <span class="price-current"><?= formatPrice($finalPrice) ?></span>
            <?php if ($showDiscount && $salePrice && $salePrice < $price): ?>
            <div class="price-row-old">
                <span class="price-old"><?= formatPrice($price) ?></span>
                <span class="price-discount-tag">-<?= $discount ?>%</span>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
