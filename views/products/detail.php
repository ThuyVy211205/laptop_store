<?php
$extraCss = ['product.css'];
include ROOT_PATH . '/views/layouts/header.php';
?>
<?php
$finalPrice = $product['sale_price'] ?: $product['price'];
$discount   = $product['sale_price'] ? calcDiscount($product['price'], $product['sale_price']) : 0;

$ramOptions     = [];
$storageOptions = [];
if (!empty($specs)) {
    foreach ($specs as $key => $value) {
        $k = strtolower(is_array($value) ? ($value['key'] ?? $key) : $key);
        $v = is_array($value) ? ($value['value'] ?? '') : $value;
        if (strpos($k, 'ram') !== false || strpos($k, 'bộ nhớ') !== false) {
            $ramOptions[] = $v;
        }
        if (strpos($k, 'ssd') !== false || strpos($k, 'ổ cứng') !== false || strpos($k, 'storage') !== false || strpos($k, 'nvme') !== false) {
            $storageOptions[] = $v;
        }
    }
}
?>

<!-- Breadcrumb -->
<nav class="pd-breadcrumb-bar">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/products">Sản phẩm</a></li>
            <?php if (!empty($product['category_slug'])): ?>
            <li class="breadcrumb-item">
                <a href="<?= SITE_URL ?>/category/<?= htmlspecialchars($product['category_slug']) ?>">
                    <?= htmlspecialchars($product['category_name']) ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?= htmlspecialchars(truncate($product['name'], 55)) ?></li>
        </ol>
    </div>
</nav>

<div class="pd-page">
<div class="container">

    <!-- =====================================================================
         PRODUCT MAIN — 50 / 50 two-column grid
    ====================================================================== -->
    <div class="pd-grid">

        <!-- ── LEFT  ── Gallery -->
        <section class="pd-gallery">

            <!-- Main display image -->
            <div class="pd-gallery__main" id="galleryMain">
                <?php if ($discount > 0): ?>
                <span class="pd-gallery__badge pd-gallery__badge--sale">-<?= $discount ?>%</span>
                <?php endif; ?>
                <?php if (!empty($product['is_new'])): ?>
                <span class="pd-gallery__badge pd-gallery__badge--new">Mới</span>
                <?php endif; ?>
                <img
                    src="<?= imgUrl($product['thumbnail']) ?>"
                    alt="<?= htmlspecialchars($product['name']) ?>"
                    id="mainImage"
                    onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'"
                >
                <button class="pd-gallery__arrow pd-gallery__arrow--prev" id="galleryPrev" type="button" aria-label="Ảnh trước">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="pd-gallery__arrow pd-gallery__arrow--next" id="galleryNext" type="button" aria-label="Ảnh sau">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Thumbnail strip — 4 square tiles -->
            <div class="pd-gallery__thumbs" id="thumbStrip">
                <!-- Tile 0 — thumbnail (always shown) -->
                <button class="pd-gallery__thumb active" data-src="<?= imgUrl($product['thumbnail']) ?>" type="button" aria-label="View 1">
                    <img src="<?= imgUrl($product['thumbnail']) ?>" alt="" onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                </button>
                <?php $shown = 0; foreach ($images as $img): if ($shown >= 3) break; $shown++; ?>
                <button class="pd-gallery__thumb" data-src="<?= imgUrl($img['image_path']) ?>" type="button" aria-label="View <?= $shown + 1 ?>">
                    <img src="<?= imgUrl($img['image_path']) ?>" alt="" onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                </button>
                <?php endforeach; ?>
                <?php /* Pad to always show 4 thumbnails */
                for ($p = 1 + $shown; $p < 4; $p++): ?>
                <button class="pd-gallery__thumb" data-src="<?= imgUrl($product['thumbnail']) ?>" type="button">
                    <img src="<?= imgUrl($product['thumbnail']) ?>" alt="" onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                </button>
                <?php endfor; ?>
            </div>

            <!-- ── Commitment badges ── -->
            <div class="pd-commits">
                <div class="pd-commit">
                    <div class="pd-commit__icon"><i class="fas fa-shipping-fast"></i></div>
                    <span class="pd-commit__text">Miễn phí vận chuyển</span>
                </div>
                <div class="pd-commit">
                    <div class="pd-commit__icon"><i class="fas fa-shield-alt"></i></div>
                    <span class="pd-commit__text">Bảo hành 12 tháng</span>
                </div>
                <div class="pd-commit">
                    <div class="pd-commit__icon"><i class="fas fa-undo-alt"></i></div>
                    <span class="pd-commit__text">Đổi trả 30 ngày</span>
                </div>
            </div>

        </section>
        <!-- /gallery -->

        <!-- ── RIGHT ── Product info -->
        <section class="pd-info">

            <!-- Brand + availability row -->
            <div class="pd-info__toprow">
                <?php if (!empty($product['brand'])): ?>
                <span class="pd-info__brand"><?= htmlspecialchars($product['brand']) ?></span>
                <?php endif; ?>
                <?php if ($product['stock'] > 0): ?>
                <span class="pd-info__stock pd-info__stock--in">
                    <i class="fas fa-circle"></i> Còn hàng
                </span>
                <?php else: ?>
                <span class="pd-info__stock pd-info__stock--out">
                    <i class="fas fa-circle"></i> Hết hàng
                </span>
                <?php endif; ?>
            </div>

            <!-- Title -->
            <h1 class="pd-info__title"><?= htmlspecialchars($product['name']) ?></h1>

            <!-- Stars + review link + sold -->
            <div class="pd-info__meta">
                <?php $ra = (float)($product['rating_avg'] ?? 0); ?>
                <span class="pd-info__stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= floor($ra)): ?>
                    <i class="fas fa-star"></i>
                    <?php elseif ($i - 0.5 <= $ra): ?>
                    <i class="fas fa-star-half-alt"></i>
                    <?php else: ?>
                    <i class="far fa-star"></i>
                    <?php endif; ?>
                    <?php endfor; ?>
                </span>
                <?php if ($ra > 0): ?>
                <span class="pd-info__rating-num"><?= number_format($ra, 1) ?></span>
                <?php endif; ?>
                <?php if (!empty($product['rating_count'])): ?>
                <a href="#pd-reviews" class="pd-info__review-link" id="scrollToReviews">
                    (<?= number_format($product['rating_count']) ?> đánh giá)
                </a>
                <?php else: ?>
                <a href="#pd-reviews" class="pd-info__review-link" id="scrollToReviews">Viết đánh giá đầu tiên</a>
                <?php endif; ?>
                <?php if (!empty($product['sold_quantity'])): ?>
                <span class="pd-info__sep">|</span>
                <span class="pd-info__sold"><i class="fas fa-fire"></i> Đã bán <?= number_format($product['sold_quantity']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Short description snippet -->
            <?php if (!empty($product['description'])): ?>
            <p class="pd-info__desc"><?= nl2br(htmlspecialchars(truncate($product['description'], 220))) ?></p>
            <?php endif; ?>

            <!-- ── Pricing ── -->
            <div class="pd-price">
                <span class="pd-price__current"><?= formatPrice($finalPrice) ?></span>
                <?php if ($discount > 0): ?>
                <span class="pd-price__original"><?= formatPrice($product['price']) ?></span>
                <span class="pd-price__badge">Tiết kiệm <?= formatPrice($product['price'] - $finalPrice) ?></span>
                <?php endif; ?>
            </div>

            <hr class="pd-divider">

            <!-- ── Configuration: RAM + Storage pills ── -->
            <?php if (!empty($ramOptions) || !empty($storageOptions)): ?>
            <div class="pd-config">
                <p class="pd-config__heading">Cấu hình</p>
                <?php if (!empty($ramOptions)): ?>
                <div class="pd-config__row">
                    <span class="pd-config__label">RAM</span>
                    <div class="pd-config__chips" data-group="ram">
                        <?php foreach ($ramOptions as $i => $ram): ?>
                        <button class="pd-chip<?= $i === 0 ? ' is-active' : '' ?>" type="button"><?= htmlspecialchars($ram) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($storageOptions)): ?>
                <div class="pd-config__row">
                    <span class="pd-config__label">Bộ nhớ</span>
                    <div class="pd-config__chips" data-group="storage">
                        <?php foreach ($storageOptions as $i => $st): ?>
                        <button class="pd-chip<?= $i === 0 ? ' is-active' : '' ?>" type="button"><?= htmlspecialchars($st) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- ── Block 2: Color / Variant cards ── -->
            <?php
            $currentColor = trim($product['color'] ?? '');
            if (empty($currentColor)) {
                $catSlug   = strtolower($product['category_slug'] ?? '');
                $brandName = strtolower($product['brand'] ?? '');
                if (strpos($catSlug, 'gaming') !== false) {
                    $currentColor = 'Đen';
                } elseif (strpos($catSlug, 'macbook') !== false || strpos($brandName, 'apple') !== false) {
                    $currentColor = 'Bạc';
                } elseif (strpos($brandName, 'lenovo') !== false) {
                    $currentColor = 'Đen';
                } else {
                    $currentColor = 'Bạc';
                }
            }
            ?>
            <div class="pd-cv">
                <p class="pd-config__heading">Màu sắc</p>
                <div class="pd-cv__list">
                    <!-- Current product — always active -->
                    <div class="pd-cv__card is-active">
                        <span class="pd-cv__check"><i class="fas fa-check"></i></span>
                        <div class="pd-cv__img">
                            <img src="<?= imgUrl($product['thumbnail']) ?>"
                                 alt="<?= htmlspecialchars($currentColor) ?>"
                                 onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                        </div>
                        <div class="pd-cv__info">
                            <span class="pd-cv__name" id="selectedColorName"><?= htmlspecialchars($currentColor) ?></span>
                            <span class="pd-cv__price"><?= formatPrice($finalPrice) ?></span>
                        </div>
                    </div>
                    <?php foreach ($colorVariants as $cv): ?>
                    <?php
                    $cvFinal = $cv['sale_price'] ?: $cv['price'];
                    $cvColor = trim($cv['color'] ?? '');
                    if (empty($cvColor)) {
                        $cvCatSlug   = strtolower($cv['category_slug'] ?? $product['category_slug'] ?? '');
                        $cvBrandName = strtolower($cv['brand'] ?? '');
                        if (strpos($cvCatSlug, 'gaming') !== false) {
                            $cvColor = 'Đen';
                        } elseif (strpos($cvCatSlug, 'macbook') !== false || strpos($cvBrandName, 'apple') !== false) {
                            $cvColor = 'Bạc';
                        } elseif (strpos($cvBrandName, 'lenovo') !== false) {
                            $cvColor = 'Đen';
                        } else {
                            $cvColor = 'Bạc';
                        }
                    }
                    ?>
                    <a class="pd-cv__card"
                       href="<?= SITE_URL ?>/product/<?= htmlspecialchars($cv['slug']) ?>">
                        <div class="pd-cv__img">
                            <img src="<?= imgUrl($cv['thumbnail']) ?>"
                                 alt="<?= htmlspecialchars($cvColor) ?>"
                                 onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                        </div>
                        <div class="pd-cv__info">
                            <span class="pd-cv__name"><?= htmlspecialchars($cvColor) ?></span>
                            <span class="pd-cv__price"><?= formatPrice($cvFinal) ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ── Block 3: Promotions & Vouchers ── -->
            <div class="pd-promos">
                <div class="pd-promos__head">
                    <i class="fas fa-tag"></i>
                    <span>Ưu đãi &amp; Khuyến mãi</span>
                </div>
                <ul class="pd-promos__list">
                    <li class="pd-promo-item">
                        <span class="pd-promo-tag">-10%</span>
                        <span>Giảm 10% cho thành viên mới — Mã: <strong>TECH10</strong></span>
                    </li>
                    <li class="pd-promo-item">
                        <span class="pd-promo-tag pd-promo-tag--gift">Quà</span>
                        <span>Tặng túi chống sốc laptop trị giá 200.000₫</span>
                    </li>
                    <li class="pd-promo-item">
                        <span class="pd-promo-tag pd-promo-tag--free">Free</span>
                        <span>Miễn phí cài đặt Office &amp; phần mềm bản quyền</span>
                    </li>
                </ul>
            </div>

            <?php if ($product['stock'] > 0): ?>

            <!-- ── Quantity control ── -->
            <div class="pd-qty">
                <span class="pd-qty__label">Số lượng</span>
                <div class="pd-qty__ctrl">
                    <button class="pd-qty__btn" id="qtyMinus" type="button"><i class="fas fa-minus"></i></button>
                    <input class="pd-qty__input" type="number" id="qtyInput" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                    <button class="pd-qty__btn" id="qtyPlus"  type="button"><i class="fas fa-plus"></i></button>
                </div>
                <span class="pd-qty__left">Còn <?= $product['stock'] ?> sản phẩm</span>
            </div>

            <!-- ── CTA buttons ── -->
            <div class="pd-cta">
                <button class="pd-cta__buy btn-buy-now" data-id="<?= $product['id'] ?>" type="button">
                    <i class="fas fa-bolt"></i> MUA NGAY
                </button>
                <button class="pd-cta__cart btn-add-cart-detail" data-id="<?= $product['id'] ?>" type="button">
                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                </button>
            </div>

            <!-- ── Wishlist + Share ── -->
            <div class="pd-actions">
                <button class="pd-action wishlist-btn-detail" data-id="<?= $product['id'] ?>" type="button">
                    <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                    <span><?= $inWishlist ? 'Đã yêu thích' : 'Yêu thích' ?></span>
                </button>
                <button class="pd-action" type="button"
                    onclick="if(navigator.share){navigator.share({title:document.title,url:location.href})}else{navigator.clipboard.writeText(location.href);if(window.showToast)showToast('success','Đã copy link!');}">
                    <i class="fas fa-share-alt"></i>
                    <span>Chia sẻ</span>
                </button>
            </div>

            <?php else: ?>
            <div class="pd-outofstock">
                <i class="fas fa-times-circle"></i> Sản phẩm hiện đã hết hàng
            </div>
            <?php endif; ?>

        </section>
        <!-- /info -->

    </div>
    <!-- /pd-grid -->

    <!-- =====================================================================
         TABS
    ====================================================================== -->
    <div class="pd-tabs" id="pd-tabs">
        <nav class="pd-tabs__nav">
            <button class="pd-tabs__btn is-active" data-tab="specs"    type="button"><i class="fas fa-microchip"></i> Thông số</button>
            <button class="pd-tabs__btn"           data-tab="desc"     type="button"><i class="fas fa-align-left"></i> Mô tả</button>
            <button class="pd-tabs__btn"           data-tab="reviews"  type="button" id="pd-review-tab"><i class="fas fa-star"></i> Đánh giá (<?= count($reviews) ?>)</button>
            <button class="pd-tabs__btn"           data-tab="comments" type="button"><i class="fas fa-comments"></i> Hỏi đáp (<?= count($comments) ?>)</button>
        </nav>

        <div class="pd-tabs__body">

            <!-- SPECS -->
            <div class="pd-tabs__panel is-active" id="tab-specs">
                <?php if (!empty($specs)): ?>
                <table class="pd-specs">
                    <?php foreach ($specs as $key => $value): ?>
                    <tr>
                        <td class="pd-specs__key"><?= htmlspecialchars(is_array($value) ? ($value['key'] ?? $key) : $key) ?></td>
                        <td class="pd-specs__val"><?= htmlspecialchars(is_array($value) ? ($value['value'] ?? '') : $value) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p class="pd-empty">Chưa có thông số kỹ thuật.</p>
                <?php endif; ?>
            </div>

            <!-- DESCRIPTION -->
            <div class="pd-tabs__panel" id="tab-desc">
                <div class="pd-desc"><?= nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả chi tiết.')) ?></div>
            </div>

            <!-- REVIEWS -->
            <div class="pd-tabs__panel" id="tab-reviews">
                <?php if (isLoggedIn()): ?>
                <div class="pd-review-form">
                    <h5 class="pd-review-form__title">Viết đánh giá của bạn</h5>
                    <div class="pd-stars-input" id="ratingStars">
                        <i class="far fa-star" data-rate="1"></i>
                        <i class="far fa-star" data-rate="2"></i>
                        <i class="far fa-star" data-rate="3"></i>
                        <i class="far fa-star" data-rate="4"></i>
                        <i class="far fa-star" data-rate="5"></i>
                    </div>
                    <textarea id="reviewContent" class="form-control mt-2" rows="3"
                        placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."></textarea>
                    <button class="btn btn-tech mt-2" id="submitReviewBtn">
                        <i class="fas fa-paper-plane me-2"></i>Gửi đánh giá
                    </button>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <a href="<?= SITE_URL ?>/auth/login">Đăng nhập</a> để viết đánh giá.
                </div>
                <?php endif; ?>

                <div class="pd-review-list">
                    <?php foreach ($reviews as $review): ?>
                    <div class="pd-review">
                        <div class="pd-review__avatar">
                            <?php if (!empty($review['avatar'])): ?>
                            <img src="<?= imgUrl($review['avatar']) ?>" alt="">
                            <?php else: ?>
                            <span><?= mb_strtoupper(mb_substr($review['full_name'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="pd-review__body">
                            <strong class="pd-review__name"><?= htmlspecialchars($review['full_name']) ?></strong>
                            <?= starRating($review['rating']) ?>
                            <p class="pd-review__text"><?= nl2br(htmlspecialchars($review['content'])) ?></p>
                            <time class="pd-review__time"><?= timeAgo($review['created_at']) ?></time>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($reviews)): ?>
                    <p class="pd-empty">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- COMMENTS -->
            <div class="pd-tabs__panel" id="tab-comments">
                <?php if (isLoggedIn()): ?>
                <div class="mb-4">
                    <textarea id="commentContent" class="form-control" rows="3"
                        placeholder="Đặt câu hỏi về sản phẩm..."></textarea>
                    <button class="btn btn-tech mt-2" id="submitCommentBtn">
                        <i class="fas fa-paper-plane me-2"></i>Gửi câu hỏi
                    </button>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <a href="<?= SITE_URL ?>/auth/login">Đăng nhập</a> để đặt câu hỏi.
                </div>
                <?php endif; ?>

                <div class="pd-comment-list">
                    <?php foreach ($comments as $comment): ?>
                    <div class="pd-comment">
                        <div class="pd-comment__avatar">
                            <?php if (!empty($comment['avatar'])): ?>
                            <img src="<?= imgUrl($comment['avatar']) ?>" alt="">
                            <?php else: ?>
                            <span><?= mb_strtoupper(mb_substr($comment['full_name'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="pd-comment__body">
                            <strong class="pd-comment__name"><?= htmlspecialchars($comment['full_name']) ?></strong>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                            <time class="pd-review__time"><?= timeAgo($comment['created_at']) ?></time>

                            <?php if (!empty($comment['replies'])): ?>
                            <div class="pd-replies">
                                <?php foreach ($comment['replies'] as $reply): ?>
                                <div class="pd-reply">
                                    <div class="pd-comment__avatar pd-comment__avatar--sm">
                                        <span><?= mb_strtoupper(mb_substr($reply['full_name'], 0, 1)) ?></span>
                                    </div>
                                    <div>
                                        <strong class="pd-comment__name"><?= htmlspecialchars($reply['full_name']) ?></strong>
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                        <time class="pd-review__time"><?= timeAgo($reply['created_at']) ?></time>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($comments)): ?>
                    <p class="pd-empty">Chưa có câu hỏi nào.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- =====================================================================
         RELATED PRODUCTS — 4-column grid
    ====================================================================== -->
    <?php if (!empty($related)): ?>
    <section class="pd-related">
        <div class="pd-related__head">
            <h3 class="pd-related__title">
                <i class="fas fa-th-large"></i> Sản phẩm liên quan
            </h3>
            <a href="<?= SITE_URL ?>/products" class="pd-related__more">
                Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="pd-related__grid">
            <?php foreach ($related as $product): ?>
            <?php include __DIR__ . '/_product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div><!-- /container -->
</div><!-- /pd-page -->

<script>
/* ─── TABS ─── */
document.querySelectorAll('.pd-tabs__btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.pd-tabs__btn').forEach(function(b)   { b.classList.remove('is-active'); });
        document.querySelectorAll('.pd-tabs__panel').forEach(function(p) { p.classList.remove('is-active'); });
        this.classList.add('is-active');
        var panel = document.getElementById('tab-' + this.dataset.tab);
        if (panel) panel.classList.add('is-active');
    });
});

/* Smooth-scroll + open reviews tab from star-link */
document.getElementById('scrollToReviews')?.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('pd-review-tab')?.click();
    document.getElementById('pd-tabs')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

/* ─── CONFIGURATION CHIPS ─── */
document.querySelectorAll('.pd-config__chips').forEach(function(group) {
    group.querySelectorAll('.pd-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            group.querySelectorAll('.pd-chip').forEach(function(c) { c.classList.remove('is-active'); });
            this.classList.add('is-active');
        });
    });
});

/* ─── COLOR VARIANT CARDS ─── */
document.querySelectorAll('.pd-cv__card').forEach(function(card) {
    card.addEventListener('click', function() {
        if (this.tagName === 'A') return; // let anchor navigate
        document.querySelectorAll('.pd-cv__card').forEach(function(c) { c.classList.remove('is-active'); });
        this.classList.add('is-active');
        var label = document.getElementById('selectedColorName');
        if (label) label.textContent = this.querySelector('.pd-cv__name')?.textContent?.trim() || '';
    });
});

/* ─── GALLERY ─── */
(function () {
    var thumbs  = Array.from(document.querySelectorAll('.pd-gallery__thumb'));
    var mainImg = document.getElementById('mainImage');
    var prevBtn = document.getElementById('galleryPrev');
    var nextBtn = document.getElementById('galleryNext');
    var current = 0;

    function goTo(index) {
        if (!thumbs.length) return;
        current = (index + thumbs.length) % thumbs.length;
        thumbs.forEach(function (t) { t.classList.remove('active'); });
        thumbs[current].classList.add('active');
        if (!mainImg) return;
        var src = thumbs[current].dataset.src;
        mainImg.style.opacity = '0';
        mainImg.style.transform = 'scale(0.97)';
        setTimeout(function () {
            mainImg.src = src;
            mainImg.style.opacity = '1';
            mainImg.style.transform = 'scale(1)';
        }, 140);
    }

    thumbs.forEach(function (thumb, i) {
        thumb.addEventListener('click', function () { goTo(i); });
    });

    if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); });
}());

/* ─── QUANTITY ─── */
var qtyInput = document.getElementById('qtyInput');
document.getElementById('qtyMinus')?.addEventListener('click', function() {
    if (parseInt(qtyInput.value) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
});
document.getElementById('qtyPlus')?.addEventListener('click', function() {
    if (parseInt(qtyInput.value) < parseInt(qtyInput.max)) qtyInput.value = parseInt(qtyInput.value) + 1;
});

/* ─── ADD TO CART ─── */
document.querySelector('.btn-add-cart-detail')?.addEventListener('click', function() {
    addToCartDetail(this.dataset.id, qtyInput ? parseInt(qtyInput.value) : 1);
});

/* ─── BUY NOW ─── */
document.querySelector('.btn-buy-now')?.addEventListener('click', async function() {
    await addToCartDetail(this.dataset.id, qtyInput ? parseInt(qtyInput.value) : 1);
    window.location.href = SITE_URL + '/checkout';
});

async function addToCartDetail(productId, quantity) {
    try {
        const res = await fetch(SITE_URL + '/cart/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'product_id=' + productId + '&quantity=' + quantity,
        });
        const data = await res.json();
        if (data.success) {
            document.querySelectorAll('#cartBadgeNav, #cartBadgeSidebar').forEach(function(el) {
                if (el) el.textContent = data.cart_count;
            });
            if (window.showToast) showToast('success', data.message);
        } else {
            if (window.showToast) showToast('error', data.message);
        }
    } catch (e) { console.error(e); }
}

/* ─── RATING STARS (write review) ─── */
var selectedRating = 0;
document.querySelectorAll('#ratingStars i').forEach(function(star) {
    star.addEventListener('click', function() {
        selectedRating = parseInt(this.dataset.rate);
        document.querySelectorAll('#ratingStars i').forEach(function(s, i) {
            s.className = i < selectedRating ? 'fas fa-star' : 'far fa-star';
        });
    });
    star.addEventListener('mouseover', function() {
        var r = parseInt(this.dataset.rate);
        document.querySelectorAll('#ratingStars i').forEach(function(s, i) {
            s.className = i < r ? 'fas fa-star' : 'far fa-star';
        });
    });
});
document.getElementById('ratingStars')?.addEventListener('mouseleave', function() {
    document.querySelectorAll('#ratingStars i').forEach(function(s, i) {
        s.className = i < selectedRating ? 'fas fa-star' : 'far fa-star';
    });
});

/* ─── SUBMIT REVIEW ─── */
document.getElementById('submitReviewBtn')?.addEventListener('click', async function() {
    var content = document.getElementById('reviewContent').value.trim();
    if (!selectedRating) { alert('Vui lòng chọn số sao'); return; }
    if (!content)        { alert('Vui lòng nhập nội dung'); return; }
    const res  = await fetch(SITE_URL + '/api/review/add', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: <?= $product['id'] ?>, rating: selectedRating, content }),
    });
    const data = await res.json();
    if (data.success) { alert('Đã gửi đánh giá! Tải lại trang...'); location.reload(); }
    else              { alert(data.message); }
});

/* ─── SUBMIT COMMENT ─── */
document.getElementById('submitCommentBtn')?.addEventListener('click', async function() {
    var content = document.getElementById('commentContent').value.trim();
    if (!content) { alert('Vui lòng nhập câu hỏi'); return; }
    const res  = await fetch(SITE_URL + '/api/comment/add', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: <?= $product['id'] ?>, content }),
    });
    const data = await res.json();
    if (data.success) { alert('Đã gửi câu hỏi!'); location.reload(); }
    else              { alert(data.message); }
});

/* ─── WISHLIST ─── */
document.querySelector('.wishlist-btn-detail')?.addEventListener('click', async function() {
    if (!window.IS_LOGGED_IN) {
        if (confirm('Vui lòng đăng nhập để sử dụng. Bạn muốn đăng nhập?'))
            window.location.href = SITE_URL + '/auth/login';
        return;
    }
    const res  = await fetch(SITE_URL + '/api/wishlist/toggle', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: <?= $product['id'] ?> }),
    });
    const data = await res.json();
    if (data.success) {
        var icon = this.querySelector('i');
        var text = this.querySelector('span');
        if (data.action === 'added') { icon.className = 'fas fa-heart'; text.textContent = 'Đã yêu thích'; }
        else                         { icon.className = 'far fa-heart'; text.textContent = 'Yêu thích'; }
        if (window.showToast) showToast('success', data.message);
    }
});
</script>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>
