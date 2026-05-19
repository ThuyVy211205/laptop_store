<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

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
    <h1 class="page-heading">
        <i class="fas fa-shopping-cart me-2"></i>Giỏ hàng của bạn
    </h1>

    <?php if (!empty($items)): ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="cart-table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th colspan="2">Sản phẩm</th>
                            <th class="text-center">Đơn giá</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-center">Thành tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item):
                            $itemId = $item['product_id'] ?? $item['id'];
                            $price  = $item['sale_price'] ?: $item['price'];
                            $subtotal = $price * $item['quantity'];
                        ?>
                        <tr data-id="<?= $itemId ?>" data-price="<?= $price ?>">
                            <td class="cart-img-col">
                                <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($item['slug']) ?>">
                                    <img src="<?= imgUrl($item['thumbnail']) ?>" alt=""
                                         onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                                </a>
                            </td>
                            <td class="cart-name-col">
                                <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($item['slug']) ?>" class="cart-product-name">
                                    <?= htmlspecialchars($item['name']) ?>
                                </a>
                                <small class="cart-stock">Còn <?= $item['stock'] ?> sản phẩm</small>
                            </td>
                            <td class="text-center">
                                <span class="text-warning fw-600"><?= formatPrice($price) ?></span>
                                <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                <br><small class="price-old"><?= formatPrice($item['price']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="cart-qty">
                                    <button class="qty-btn qty-decrease" data-id="<?= $itemId ?>"><i class="fas fa-minus"></i></button>
                                    <input type="number" class="qty-input" data-id="<?= $itemId ?>"
                                           value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                                    <button class="qty-btn qty-increase" data-id="<?= $itemId ?>"><i class="fas fa-plus"></i></button>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="cart-subtotal text-warning fw-700"><?= formatPrice($subtotal) ?></span>
                            </td>
                            <td class="text-center">
                                <button class="btn-remove-item" data-id="<?= $itemId ?>" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-actions mt-3">
                <a href="<?= SITE_URL ?>/products" class="btn btn-outline-tech">
                    <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
        </div>

        <!-- ============ ORDER SUMMARY ============ -->
        <div class="col-lg-4">
            <div class="order-summary-card">
                <h5 class="summary-title">
                    <i class="fas fa-receipt me-2"></i>Tóm tắt đơn hàng
                </h5>

                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <strong id="cartSubtotal"><?= formatPrice($total) ?></strong>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span class="text-success">Miễn phí</span>
                </div>
                <hr>
                <div class="summary-row summary-total">
                    <span>Tổng cộng:</span>
                    <strong class="text-warning" id="cartTotalDisplay"><?= formatPrice($total) ?></strong>
                </div>

                <a href="<?= SITE_URL ?>/checkout" class="btn btn-tech w-100 mt-3 btn-checkout">
                    <i class="fas fa-credit-card me-2"></i>Tiến hành thanh toán
                </a>

                <div class="summary-trust mt-3">
                    <small><i class="fas fa-lock me-1"></i> Thanh toán an toàn, bảo mật</small>
                </div>
            </div>

            <!-- Voucher hint -->
            <div class="voucher-hint mt-3">
                <i class="fas fa-gift text-warning"></i>
                <small>Bạn có thể áp dụng voucher ở bước thanh toán</small>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Empty cart -->
    <div class="empty-cart text-center py-5">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
        <h3>Giỏ hàng đang trống</h3>
        <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
        <a href="<?= SITE_URL ?>/products" class="btn btn-tech mt-2">
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
            // Update row subtotal
            const row = document.querySelector('tr[data-id="' + productId + '"]');
            const price = parseFloat(row.dataset.price);
            row.querySelector('.cart-subtotal').textContent = formatPriceJS(price * quantity);

            // Update total
            document.getElementById('cartSubtotal').textContent = data.total_formatted;
            document.getElementById('cartTotalDisplay').textContent = data.total_formatted;

            // Update badges
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
        const id = this.dataset.id;
        const res = await fetch(SITE_URL + '/api/cart/remove', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({product_id: id}),
        });
        const data = await res.json();
        if (data.success) {
            document.querySelector('tr[data-id="' + id + '"]').remove();
            document.getElementById('cartSubtotal').textContent = data.total_formatted;
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