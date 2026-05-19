<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<?php
$finalPrice = $product['sale_price'] ?: $product['price'];
$discount   = $product['sale_price'] ? calcDiscount($product['price'], $product['sale_price']) : 0;
?>

<!-- Breadcrumb -->
<nav class="breadcrumb-wrap">
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
            <li class="breadcrumb-item active"><?= htmlspecialchars(truncate($product['name'], 50)) ?></li>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <div class="row g-4">
        <!-- ============ GALLERY ============ -->
        <div class="col-lg-6">
            <div class="product-gallery">
                <div class="gallery-main" id="galleryMain">
                    <?php if ($discount > 0): ?>
                    <span class="badge-discount-large">-<?= $discount ?>%</span>
                    <?php endif; ?>
                    <img src="<?= imgUrl($product['thumbnail']) ?>" alt="" id="mainImage"
                         onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                </div>
                <?php if (!empty($images)): ?>
                <div class="gallery-thumbs">
                    <div class="thumb-item active" data-src="<?= imgUrl($product['thumbnail']) ?>">
                        <img src="<?= imgUrl($product['thumbnail']) ?>" alt="">
                    </div>
                    <?php foreach ($images as $img): ?>
                    <div class="thumb-item" data-src="<?= imgUrl($img['image_path']) ?>">
                        <img src="<?= imgUrl($img['image_path']) ?>" alt=""
                             onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============ BUY BOX ============ -->
        <div class="col-lg-6">
            <div class="product-info">
                <?php if (!empty($product['brand'])): ?>
                <span class="product-brand">Thương hiệu: <strong><?= htmlspecialchars($product['brand']) ?></strong></span>
                <?php endif; ?>

                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

                <div class="product-meta">
                    <?php if (!empty($product['rating_count'])): ?>
                    <div class="meta-item">
                        <?= starRating($product['rating_avg']) ?>
                        <span>(<?= $product['rating_count'] ?> đánh giá)</span>
                    </div>
                    <span class="meta-separator">|</span>
                    <?php endif; ?>
                    <div class="meta-item">
                        <i class="fas fa-eye"></i> <?= number_format($product['views'] ?? 0) ?> lượt xem
                    </div>
                    <span class="meta-separator">|</span>
                    <div class="meta-item">
                        <i class="fas fa-fire text-warning"></i> Đã bán <?= number_format($product['sold_quantity'] ?? 0) ?>
                    </div>
                    <span class="meta-separator">|</span>
                    <div class="meta-item">
                        <?php if ($product['stock'] > 0): ?>
                        <span class="text-success"><i class="fas fa-check-circle"></i> Còn hàng (<?= $product['stock'] ?>)</span>
                        <?php else: ?>
                        <span class="text-danger"><i class="fas fa-times-circle"></i> Hết hàng</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Price -->
                <div class="product-price-block">
                    <div class="price-main"><?= formatPrice($finalPrice) ?></div>
                    <?php if ($discount > 0): ?>
                    <div class="price-meta">
                        <span class="price-old"><?= formatPrice($product['price']) ?></span>
                        <span class="price-save">Tiết kiệm <?= formatPrice($product['price'] - $finalPrice) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Vouchers -->
                <div class="product-vouchers">
                    <h6><i class="fas fa-gift me-2"></i>Voucher khả dụng</h6>
                    <div class="voucher-list">
                        <span class="voucher-tag">TECH10</span>
                        <span class="voucher-tag">WELCOME500</span>
                        <span class="voucher-tag">GAME20</span>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="product-benefits">
                    <div class="benefit-item">
                        <i class="fas fa-shipping-fast"></i>
                        <div><strong>Giao hàng nhanh</strong><small>Free ship đơn từ 500K</small></div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-medal"></i>
                        <div><strong>Bảo hành</strong><small>12 tháng chính hãng</small></div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-undo"></i>
                        <div><strong>Đổi trả</strong><small>Trong 7 ngày</small></div>
                    </div>
                </div>

                <!-- Quantity + Buttons -->
                <?php if ($product['stock'] > 0): ?>
                <div class="product-buy-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn" id="qtyMinus" type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" id="qtyInput" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button class="qty-btn" id="qtyPlus" type="button"><i class="fas fa-plus"></i></button>
                    </div>
                    <button class="btn btn-outline-tech btn-add-cart-detail" data-id="<?= $product['id'] ?>">
                        <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ
                    </button>
                    <button class="btn btn-tech btn-buy-now" data-id="<?= $product['id'] ?>">
                        <i class="fas fa-bolt me-2"></i>Mua ngay
                    </button>
                </div>
                <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>Sản phẩm đã hết hàng
                </div>
                <?php endif; ?>

                <!-- Wishlist -->
                <div class="product-extra-actions mt-3">
                    <button class="btn-extra wishlist-btn-detail" data-id="<?= $product['id'] ?>">
                        <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                        <span><?= $inWishlist ? 'Đã yêu thích' : 'Thêm vào yêu thích' ?></span>
                    </button>
                    <button class="btn-extra" onclick="if(navigator.share){navigator.share({title:document.title,url:location.href})}else{navigator.clipboard.writeText(location.href);alert('Đã copy link!');}">
                        <i class="fas fa-share-alt"></i>
                        <span>Chia sẻ</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ TABS ============ -->
    <div class="product-tabs mt-5">
        <ul class="nav nav-tabs tab-nav-tech" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-specs">
                    <i class="fas fa-microchip me-1"></i>Thông số kỹ thuật
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-desc">
                    <i class="fas fa-info-circle me-1"></i>Mô tả
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-reviews">
                    <i class="fas fa-star me-1"></i>Đánh giá (<?= count($reviews) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-comments">
                    <i class="fas fa-comments me-1"></i>Hỏi đáp (<?= count($comments) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content tab-content-tech">
            <!-- SPECS -->
            <div class="tab-pane fade show active" id="tab-specs">
                <?php if (!empty($specs)): ?>
                <table class="specs-table">
                    <?php foreach ($specs as $key => $value): ?>
                    <tr>
                        <td class="specs-key"><?= htmlspecialchars(is_array($value) ? $value['key'] : $key) ?></td>
                        <td class="specs-val"><?= htmlspecialchars(is_array($value) ? $value['value'] : $value) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p class="text-muted">Chưa có thông số kỹ thuật</p>
                <?php endif; ?>
            </div>

            <!-- DESCRIPTION -->
            <div class="tab-pane fade" id="tab-desc">
                <div class="product-desc">
                    <?= nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả chi tiết.')) ?>
                </div>
            </div>

            <!-- REVIEWS -->
            <div class="tab-pane fade" id="tab-reviews">
                <?php if (isLoggedIn()): ?>
                <div class="review-form mb-4">
                    <h5>Viết đánh giá của bạn</h5>
                    <div class="rating-stars-input" id="ratingStars">
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
                    <a href="<?= SITE_URL ?>/auth/login" class="text-warning">Đăng nhập</a> để viết đánh giá
                </div>
                <?php endif; ?>

                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-avatar">
                            <?php if (!empty($review['avatar'])): ?>
                                <img src="<?= imgUrl($review['avatar']) ?>" alt="">
                            <?php else: ?>
                                <span><?= mb_strtoupper(mb_substr($review['full_name'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="review-body">
                            <strong><?= htmlspecialchars($review['full_name']) ?></strong>
                            <?= starRating($review['rating']) ?>
                            <p class="review-content"><?= nl2br(htmlspecialchars($review['content'])) ?></p>
                            <small class="text-muted"><?= timeAgo($review['created_at']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($reviews)): ?>
                    <p class="text-center text-muted py-4">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- COMMENTS -->
            <div class="tab-pane fade" id="tab-comments">
                <?php if (isLoggedIn()): ?>
                <div class="comment-form mb-4">
                    <textarea id="commentContent" class="form-control" rows="3"
                              placeholder="Đặt câu hỏi về sản phẩm..."></textarea>
                    <button class="btn btn-tech mt-2" id="submitCommentBtn">
                        <i class="fas fa-paper-plane me-2"></i>Gửi câu hỏi
                    </button>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <a href="<?= SITE_URL ?>/auth/login" class="text-warning">Đăng nhập</a> để bình luận
                </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-avatar">
                            <?php if (!empty($comment['avatar'])): ?>
                                <img src="<?= imgUrl($comment['avatar']) ?>" alt="">
                            <?php else: ?>
                                <span><?= mb_strtoupper(mb_substr($comment['full_name'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-body">
                            <strong><?= htmlspecialchars($comment['full_name']) ?></strong>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                            <small class="text-muted"><?= timeAgo($comment['created_at']) ?></small>

                            <?php if (!empty($comment['replies'])): ?>
                            <div class="comment-replies mt-2">
                                <?php foreach ($comment['replies'] as $reply): ?>
                                <div class="reply-item">
                                    <div class="comment-avatar comment-avatar-sm">
                                        <span><?= mb_strtoupper(mb_substr($reply['full_name'], 0, 1)) ?></span>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($reply['full_name']) ?></strong>
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                        <small class="text-muted"><?= timeAgo($reply['created_at']) ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($comments)): ?>
                    <p class="text-center text-muted py-4">Chưa có câu hỏi nào</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ RELATED PRODUCTS ============ -->
    <?php if (!empty($related)): ?>
    <section class="section mt-5">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-cubes"></i> Sản phẩm liên quan
            </h3>
        </div>
        <div class="row g-3">
            <?php foreach ($related as $product): ?>
            <div class="col-lg-3 col-md-4 col-6">
                <?php include __DIR__ . '/_product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
// === GALLERY ===
document.querySelectorAll('.thumb-item').forEach(thumb => {
    thumb.addEventListener('click', () => {
        document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
        document.getElementById('mainImage').src = thumb.dataset.src;
    });
});

// === QUANTITY ===
const qtyInput = document.getElementById('qtyInput');
document.getElementById('qtyMinus')?.addEventListener('click', () => {
    if (parseInt(qtyInput.value) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
});
document.getElementById('qtyPlus')?.addEventListener('click', () => {
    const max = parseInt(qtyInput.max);
    if (parseInt(qtyInput.value) < max) qtyInput.value = parseInt(qtyInput.value) + 1;
});

// === ADD TO CART (detail) ===
document.querySelector('.btn-add-cart-detail')?.addEventListener('click', function() {
    const productId = this.dataset.id;
    const quantity = parseInt(qtyInput.value);
    addToCartDetail(productId, quantity);
});

// === BUY NOW ===
document.querySelector('.btn-buy-now')?.addEventListener('click', async function() {
    const productId = this.dataset.id;
    const quantity = parseInt(qtyInput.value);
    await addToCartDetail(productId, quantity);
    window.location.href = SITE_URL + '/checkout';
});

async function addToCartDetail(productId, quantity) {
    try {
        const res = await fetch(SITE_URL + '/api/cart/add', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({product_id: productId, quantity: quantity}),
        });
        const data = await res.json();
        if (data.success) {
            // Update cart badges
            document.querySelectorAll('#cartBadgeNav, #cartBadgeSidebar').forEach(el => {
                if (el) el.textContent = data.count;
            });
            if (window.showToast) showToast('success', data.message);
        } else {
            if (window.showToast) showToast('error', data.message);
        }
    } catch (e) {
        console.error(e);
    }
}

// === RATING STARS ===
let selectedRating = 0;
document.querySelectorAll('#ratingStars i').forEach(star => {
    star.addEventListener('click', function() {
        selectedRating = parseInt(this.dataset.rate);
        document.querySelectorAll('#ratingStars i').forEach((s, i) => {
            s.className = i < selectedRating ? 'fas fa-star' : 'far fa-star';
        });
    });
    star.addEventListener('mouseover', function() {
        const rate = parseInt(this.dataset.rate);
        document.querySelectorAll('#ratingStars i').forEach((s, i) => {
            s.className = i < rate ? 'fas fa-star' : 'far fa-star';
        });
    });
});
document.getElementById('ratingStars')?.addEventListener('mouseleave', () => {
    document.querySelectorAll('#ratingStars i').forEach((s, i) => {
        s.className = i < selectedRating ? 'fas fa-star' : 'far fa-star';
    });
});

// === SUBMIT REVIEW ===
document.getElementById('submitReviewBtn')?.addEventListener('click', async () => {
    const content = document.getElementById('reviewContent').value.trim();
    if (!selectedRating) { alert('Vui lòng chọn số sao'); return; }
    if (!content) { alert('Vui lòng nhập nội dung'); return; }

    const res = await fetch(SITE_URL + '/api/review/add', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            product_id: <?= $product['id'] ?>,
            rating: selectedRating,
            content: content,
        }),
    });
    const data = await res.json();
    if (data.success) {
        alert('Đã gửi đánh giá! Tải lại trang...');
        location.reload();
    } else {
        alert(data.message);
    }
});

// === SUBMIT COMMENT ===
document.getElementById('submitCommentBtn')?.addEventListener('click', async () => {
    const content = document.getElementById('commentContent').value.trim();
    if (!content) { alert('Vui lòng nhập câu hỏi'); return; }

    const res = await fetch(SITE_URL + '/api/comment/add', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            product_id: <?= $product['id'] ?>,
            content: content,
        }),
    });
    const data = await res.json();
    if (data.success) {
        alert('Đã gửi câu hỏi!');
        location.reload();
    } else {
        alert(data.message);
    }
});

// === WISHLIST ===
document.querySelector('.wishlist-btn-detail')?.addEventListener('click', async function() {
    if (!window.IS_LOGGED_IN) {
        if (confirm('Vui lòng đăng nhập để sử dụng. Bạn muốn đăng nhập?')) {
            window.location.href = SITE_URL + '/auth/login';
        }
        return;
    }
    const res = await fetch(SITE_URL + '/api/wishlist/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({product_id: <?= $product['id'] ?>}),
    });
    const data = await res.json();
    if (data.success) {
        const icon = this.querySelector('i');
        const text = this.querySelector('span');
        if (data.action === 'added') {
            icon.className = 'fas fa-heart';
            text.textContent = 'Đã yêu thích';
        } else {
            icon.className = 'far fa-heart';
            text.textContent = 'Thêm vào yêu thích';
        }
        if (window.showToast) showToast('success', data.message);
    }
});
</script>

<?php
$extraCss = ['product.css'];
include ROOT_PATH . '/views/layouts/footer.php';
?>