<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/cart">Giỏ hàng</a></li>
            <li class="breadcrumb-item active">Thanh toán</li>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <h1 class="page-heading"><i class="fas fa-credit-card me-2"></i>Thanh toán</h1>

    <form method="POST" action="<?= SITE_URL ?>/checkout/place" id="checkoutForm">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Shipping Info -->
                <div class="checkout-card">
                    <h5 class="checkout-card-title">
                        <i class="fas fa-truck me-2"></i>Thông tin giao hàng
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_name" class="form-control form-tech" required
                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="shipping_phone" class="form-control form-tech" required
                                   placeholder="0901234567" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="shipping_email" class="form-control form-tech"
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" class="form-control form-tech" rows="2" required
                                      placeholder="Số nhà, đường, phường, quận, tỉnh/thành"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="note" class="form-control form-tech" rows="2"
                                      placeholder="Ghi chú cho người bán (tùy chọn)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-card mt-4">
                    <h5 class="checkout-card-title">
                        <i class="fas fa-money-check-alt me-2"></i>Phương thức thanh toán
                    </h5>
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <div class="payment-card">
                                <i class="fas fa-truck"></i>
                                <div>
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <small>Trả tiền khi nhận hàng tận tay</small>
                                </div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="bank">
                            <div class="payment-card">
                                <i class="fas fa-university"></i>
                                <div>
                                    <strong>Chuyển khoản ngân hàng</strong>
                                    <small>Vietcombank, Techcombank, ...</small>
                                </div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="momo">
                            <div class="payment-card">
                                <i class="fas fa-mobile-alt" style="color:#a50064"></i>
                                <div>
                                    <strong>Ví MoMo</strong>
                                    <small>Thanh toán nhanh qua ví điện tử</small>
                                </div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="vnpay">
                            <div class="payment-card">
                                <i class="fas fa-credit-card" style="color:#005baa"></i>
                                <div>
                                    <strong>VNPay</strong>
                                    <small>Thẻ ATM / Visa / Master</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- ============ ORDER SUMMARY ============ -->
            <div class="col-lg-4">
                <div class="order-summary-card sticky-top" style="top: 100px;">
                    <h5 class="summary-title">
                        <i class="fas fa-receipt me-2"></i>Đơn hàng (<?= count($items) ?> sản phẩm)
                    </h5>

                    <div class="order-items-list">
                        <?php foreach ($items as $item):
                            $price = $item['sale_price'] ?: $item['price'];
                        ?>
                        <div class="order-item">
                            <img src="<?= imgUrl($item['thumbnail']) ?>" alt=""
                                 onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/images/no-image.webp'">
                            <div class="order-item-info">
                                <div class="order-item-name"><?= htmlspecialchars(truncate($item['name'], 40)) ?></div>
                                <small class="text-muted">SL: <?= $item['quantity'] ?> × <?= formatPrice($price) ?></small>
                            </div>
                            <strong class="text-warning"><?= formatPrice($price * $item['quantity']) ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <hr>

                    <!-- Voucher -->
                    <div class="voucher-apply mb-3">
                        <label class="form-label small">Mã giảm giá</label>
                        <div class="input-group">
                            <input type="text" name="voucher_code" id="voucherCode"
                                   class="form-control form-tech" placeholder="Nhập mã voucher">
                            <button type="button" class="btn btn-outline-tech" id="applyVoucherBtn">Áp dụng</button>
                        </div>
                        <div id="voucherResult" class="mt-2"></div>
                    </div>

                    <hr>

                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <strong id="summarySubtotal" data-value="<?= $subtotal ?>"><?= formatPrice($subtotal) ?></strong>
                    </div>
                    <div class="summary-row" id="discountRow" style="display:none;">
                        <span class="text-success">Giảm giá:</span>
                        <strong class="text-success" id="summaryDiscount">-0đ</strong>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <hr>
                    <div class="summary-row summary-total">
                        <span>Tổng cộng:</span>
                        <strong class="text-warning" id="summaryTotal"><?= formatPrice($subtotal) ?></strong>
                    </div>

                    <button type="submit" class="btn btn-tech w-100 mt-3 btn-place-order">
                        <i class="fas fa-check me-2"></i>Đặt hàng ngay
                    </button>

                    <div class="summary-trust mt-3 text-center">
                        <small><i class="fas fa-shield-alt me-1"></i> Thông tin được mã hóa an toàn</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// === Apply Voucher ===
document.getElementById('applyVoucherBtn').addEventListener('click', async () => {
    const code = document.getElementById('voucherCode').value.trim();
    if (!code) {
        document.getElementById('voucherResult').innerHTML =
            '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Vui lòng nhập mã</small>';
        return;
    }

    const subtotal = parseFloat(document.getElementById('summarySubtotal').dataset.value);

    try {
        const res = await fetch(SITE_URL + '/api/voucher/check', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({code: code, total: subtotal}),
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('voucherResult').innerHTML =
                '<small class="text-success"><i class="fas fa-check-circle me-1"></i>' + data.message + '</small>';
            document.getElementById('discountRow').style.display = 'flex';
            document.getElementById('summaryDiscount').textContent = '-' + data.discount_formatted;
            document.getElementById('summaryTotal').textContent = data.new_total_formatted;
        } else {
            document.getElementById('voucherResult').innerHTML =
                '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>' + data.message + '</small>';
            document.getElementById('discountRow').style.display = 'none';
            document.getElementById('summaryTotal').textContent =
                document.getElementById('summarySubtotal').textContent;
        }
    } catch (e) {
        console.error(e);
    }
});

// === Disable submit while processing ===
document.getElementById('checkoutForm').addEventListener('submit', function() {
    const btn = this.querySelector('.btn-place-order');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
});
</script>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>
