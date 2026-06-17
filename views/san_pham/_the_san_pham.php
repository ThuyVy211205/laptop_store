<?php
/**
 * Component Thẻ Sản Phẩm (partial)
 * Nhận: $product (mảng thông tin sản phẩm)
 */
$price      = $product['gia'];
$salePrice  = $product['gia_khuyen_mai'] ?? null;
$discount   = $salePrice ? calcDiscount($price, $salePrice) : 0;
$finalPrice = $salePrice ?: $price;

$showDiscount = $discount > 0;
$isOutOfStock = (int)($product['ton_kho'] ?? 0) <= 0;
$isNew        = in_array((int)$product['id'], getNewProductIds());
?>
<div class="product-card<?= $isOutOfStock ? ' out-of-stock' : '' ?>" data-product-id="<?= $product['id'] ?>" data-cat="<?= htmlspecialchars($product['duong_dan_danh_muc'] ?? '') ?>">

    <!-- Badges (top‑left) -->
    <div class="product-badges">
        <?php if ($isNew && !$isOutOfStock): ?>
        <span class="badge-new">Mới</span>
        <?php endif; ?>
    </div>

    <!-- Wishlist & Quick view -->
    <div class="product-actions">
        <button class="action-btn wishlist-btn<?= !empty($inWishlist) ? ' active' : '' ?>"
                data-id="<?= $product['id'] ?>" title="Yêu thích">
            <i class="<?= !empty($inWishlist) ? 'fas' : 'far' ?> fa-heart"></i>
        </button>
        <button class="action-btn quickview-btn" data-id="<?= $product['id'] ?>" title="Xem nhanh">
            <i class="fas fa-eye"></i>
        </button>
    </div>

    <!-- Image -->
    <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($product['duong_dan']) ?>"
       class="product-img-wrap">
        <?php if ($isOutOfStock): ?>
        <div class="out-of-stock-overlay">
            <span class="out-of-stock-badge">Hết hàng</span>
        </div>
        <?php endif; ?>
        <img src="<?= imgUrl($product['hinh_thu_nho']) ?>"
             alt="<?= htmlspecialchars($product['ten']) ?>" loading="lazy"
             onerror="this.onerror=null;this.src='<?= noImageUrl() ?>'">
    </a>

    <!-- Body -->
    <div class="product-body">
        <?php if (!empty($product['ten_danh_muc'])): ?>
        <small class="product-cat"><?= htmlspecialchars($product['ten_danh_muc']) ?></small>
        <?php endif; ?>

        <h6 class="product-name">
            <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($product['duong_dan']) ?>">
                <?= htmlspecialchars($product['ten']) ?>
            </a>
        </h6>

        <!-- Rating -->
        <div class="product-rating">
            <?php if (!empty($product['so_danh_gia']) && $product['so_danh_gia'] > 0): ?>
            <span class="stars"><?= starRating($product['diem_danh_gia']) ?></span>
            <small>(<?= $product['so_danh_gia'] ?>)</small>
            <?php else: ?>
            <span class="stars"><?= starRating(0) ?></span>
            <small class="text-muted">Chưa có đánh giá</small>
            <?php endif; ?>
        </div>

        <!-- Price — vertical layout: current on top, old + discount tag below (max 3 discounts shown) -->
        <div class="product-price">
            <span class="price-current<?= $isOutOfStock ? ' text-muted' : '' ?>"><?= formatPrice($finalPrice) ?></span>
            <?php if ($showDiscount && $salePrice && $salePrice < $price): ?>
            <div class="price-row-old">
                <span class="price-old"><?= formatPrice($price) ?></span>
                <span class="price-discount-tag">-<?= $discount ?>%</span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($isOutOfStock): ?>
        <div class="card-out-of-stock-label">Tạm hết hàng</div>
        <?php endif; ?>

    </div>
</div>
