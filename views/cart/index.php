<?php
$extraCss = ['cart.css'];
include ROOT_PATH . '/views/layouts/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Giỏ hàng</li>
        </ol>
    </div>
</nav>

<div class="container py-4">

    <?php if (!empty($items)): ?>

    <div class="cart-page-header">
        <h1><i class="fas fa-shopping-cart"></i>Giỏ hàng
            <span class="cart-count-badge"><?= count($items) ?> sản phẩm</span>
        </h1>
    </div>

    <div class="row g-4">
        <!-- ============ CART ITEMS ============ -->
        <div class="col-lg-8">
            <div class="cart-items-list" id="cartItemsList">
                <?php foreach ($items as $item):
                    $itemId   = $item['product_id'] ?? $item['id'];
                    $price    = $item['sale_price'] ?: $item['price'];
                    $subtotal = $price * $item['quantity'];
                    $hasDiscount = $item['sale_price'] && $item['sale_price'] < $item['price'];
                    $lowStock = $item['stock'] <= 5;
                ?>
                <div class="cart-item-card" data-id="<?= $itemId ?>" data-price="<?= $price ?>">
                    <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($item['slug']) ?>" class="cart-item-img">
                        <img src="<?= imgUrl($item['thumbnail']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                             onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/images/no-image.webp'">
                    </a>

                    <div class="cart-item-info">
                        <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($item['slug']) ?>" class="cart-item-name">
                            <?= htmlspecialchars($item['name']) ?>
                        </a>
                        <div class="cart-item-meta">
                            <span class="cart-item-price"><?= formatPrice($price) ?></span>
                            <?php if ($hasDiscount): ?>
                            <span class="cart-item-price-old"><?= formatPrice($item['price']) ?></span>
                            <span class="cart-item-discount-tag">
                                -<?= calcDiscount($item['price'], $item['sale_price']) ?>%
                            </span>
                            <?php endif; ?>
                        </div>
                        <span class="cart-item-stock<?= $lowStock ? ' low' : '' ?>">
                            <i class="fas fa-<?= $lowStock ? 'exclamation-triangle' : 'box' ?> me-1"></i>
                            Còn <?= $item['stock'] ?> sản phẩm<?= $lowStock ? ' — sắp hết!' : '' ?>
                        </span>
                    </div>

                    <div class="cart-item-controls">
                        <div class="cart-qty-wrap">
                            <button class="qty-btn qty-decrease" data-id="<?= $itemId ?>" type="button">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="qty-input" data-id="<?= $itemId ?>"
                                   value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                            <button class="qty-btn qty-increase" data-id="<?= $itemId ?>" type="button">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="cart-item-bottom">
                            <span class="cart-subtotal"><?= formatPrice($subtotal) ?></span>
                            <button class="btn-remove-item" data-id="<?= $itemId ?>" title="Xóa khỏi giỏ">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-footer-actions">
                <a href="<?= SITE_URL ?>/products" class="btn btn-outline-tech">
                    <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
        </div>

        <!-- ============ ORDER SUMMARY ============ -->
        <div class="col-lg-4">
            <div class="order-summary-card">
                <h5 class="summary-title">
                    <i class="fas fa-receipt"></i>Tóm tắt đơn hàng
                </h5>

                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <strong id="cartSubtotal"><?= formatPrice($total) ?></strong>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span class="text-success fw-600"><i class="fas fa-check-circle me-1"></i>Miễn phí</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Tổng cộng:</span>
                    <strong id="cartTotalDisplay"><?= formatPrice($total) ?></strong>
                </div>

                <a href="<?= SITE_URL ?>/checkout" class="btn btn-tech w-100 mt-3 btn-checkout">
                    <i class="fas fa-credit-card me-2"></i>Tiến hành thanh toán
                </a>

                <div class="summary-trust">
                    <span><i class="fas fa-lock"></i>Bảo mật SSL</span>
                    <span><i class="fas fa-shield-alt"></i>Thanh toán an toàn</span>
                    <span><i class="fas fa-undo"></i>Đổi trả 7 ngày</span>
                </div>
            </div>

            <div class="voucher-hint mt-3">
                <i class="fas fa-gift text-warning" style="font-size:18px;flex-shrink:0"></i>
                <span>Bạn có thể áp dụng <strong>mã giảm giá</strong> ở bước thanh toán</span>
            </div>

            <div class="cart-trust-badges">
                <div class="trust-badge">
                    <i class="fas fa-shipping-fast"></i>
                    <div><strong>Giao hàng nhanh</strong>Free ship từ 500K</div>
                </div>
                <div class="trust-badge">
                    <i class="fas fa-medal"></i>
                    <div><strong>Bảo hành</strong>12 tháng chính hãng</div>
                </div>
                <div class="trust-badge">
                    <i class="fas fa-headset"></i>
                    <div><strong>Hỗ trợ 24/7</strong>Tư vấn miễn phí</div>
                </div>
                <div class="trust-badge">
                    <i class="fas fa-credit-card"></i>
                    <div><strong>Thanh toán</strong>Đa dạng hình thức</div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ============ EMPTY CART ============ -->
    <div class="empty-cart">
        <div class="empty-cart-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <h3>Giỏ hàng đang trống</h3>
        <p>Hãy khám phá hàng nghìn sản phẩm và thêm vào giỏ hàng để tiếp tục</p>
        <a href="<?= SITE_URL ?>/products" class="btn btn-tech">
            <i class="fas fa-shopping-bag me-2"></i>Bắt đầu mua sắm
        </a>
    </div>
    <?php endif; ?>

</div>

<script>
// === QUANTITY UPDATE ===
document.querySelectorAll('.qty-decrease, .qty-increase').forEach(btn => {
    btn.addEventListener('click', function() {
        const id    = this.dataset.id;
        const input = document.querySelector('.qty-input[data-id="' + id + '"]');
        let qty = parseInt(input.value);

        if (this.classList.contains('qty-decrease')) {
            if (qty > 1) qty--;
        } else {
            if (qty < parseInt(input.max)) qty++;
        }
        input.value = qty;
        updateCartItem(id, qty);
    });
});

document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', function() {
        let qty = parseInt(this.value);
        if (qty < 1) qty = 1;
        if (qty > parseInt(this.max)) qty = parseInt(this.max);
        this.value = qty;
        updateCartItem(this.dataset.id, qty);
    });
});

async function updateCartItem(productId, quantity) {
    try {
        const res = await fetch(SITE_URL + '/api/cart/update', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({product_id: productId, quantity: quantity}),
        });
        const data = await res.json();
        if (data.success) {
            const card  = document.querySelector('.cart-item-card[data-id="' + productId + '"]');
            const price = parseFloat(card.dataset.price);
            card.querySelector('.cart-subtotal').textContent = formatPriceJS(price * quantity);

            document.getElementById('cartSubtotal').textContent    = data.total_formatted;
            document.getElementById('cartTotalDisplay').textContent = data.total_formatted;

            document.querySelectorAll('#cartBadgeNav, #cartBadgeSidebar').forEach(el => {
                if (el) el.textContent = data.count;
            });
        }
    } catch (e) { console.error(e); }
}

// === REMOVE ITEM ===
document.querySelectorAll('.btn-remove-item').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
        const id  = this.dataset.id;
        const res = await fetch(SITE_URL + '/api/cart/remove', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({product_id: id}),
        });
        const data = await res.json();
        if (data.success) {
            document.querySelector('.cart-item-card[data-id="' + id + '"]').remove();
            document.getElementById('cartSubtotal').textContent    = data.total_formatted;
            document.getElementById('cartTotalDisplay').textContent = data.total_formatted;
            document.querySelectorAll('#cartBadgeNav, #cartBadgeSidebar').forEach(el => {
                if (el) el.textContent = data.count;
            });
            if (data.count == 0) location.reload();
        }
    });
});

function formatPriceJS(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
}
</script>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>
