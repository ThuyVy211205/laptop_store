<?php include ROOT_PATH . '/views/bo_cuc/dau_trang.php'; ?>

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
                                   value="<?= htmlspecialchars($user['ho_ten'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="shipping_phone" id="ckPhone" class="form-control form-tech" required
                                   placeholder="0901234567" value="<?= htmlspecialchars($user['so_dien_thoai'] ?? '') ?>">
                            <small class="text-danger mt-1" id="ckPhoneErr" style="display:none;"></small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="shipping_email" id="ckEmail" class="form-control form-tech"
                                   value="<?= htmlspecialchars($user['thu_dien_tu'] ?? '') ?>">
                            <small class="text-danger mt-1" id="ckEmailErr" style="display:none;"></small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" class="form-control form-tech" rows="2" required
                                      placeholder="Số nhà, đường, phường, quận, tỉnh/thành"><?= htmlspecialchars($user['dia_chi'] ?? '') ?></textarea>
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
                            $price = $item['gia_khuyen_mai'] ?: $item['gia'];
                        ?>
                        <div class="order-item">
                            <img src="<?= imgUrl($item['hinh_thu_nho']) ?>" alt=""
                                 onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/images/no-image.webp'">
                            <div class="order-item-info">
                                <div class="order-item-name"><?= htmlspecialchars(truncate($item['ten'], 40)) ?></div>
                                <small class="text-muted">SL: <?= $item['so_luong'] ?> × <?= formatPrice($price) ?></small>
                            </div>
                            <strong class="text-warning"><?= formatPrice($price * $item['so_luong']) ?></strong>
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
            document.getElementById('summaryDiscount').textContent = '-' + data.giam_gia_dinh_dang;
            document.getElementById('summaryTotal').textContent = data.tong_moi_dinh_dang;
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

// === Validation ===
const PHONE_RE = /^(0|\+84)[3-9]\d{8}$/;
const EMAIL_RE = /^[^\s@]+@gmail\.com$/i;

function ckValidate(input, errId, test, msg) {
    const val = input.value.trim();
    const err = document.getElementById(errId);
    if (!test(val)) {
        err.textContent = msg;
        err.style.display = '';
        input.style.borderColor = '#dc2626';
        return false;
    }
    err.style.display = 'none';
    input.style.borderColor = '';
    return true;
}

const ckPhone = document.getElementById('ckPhone');
const ckEmail = document.getElementById('ckEmail');

ckPhone.addEventListener('blur', () =>
    ckValidate(ckPhone, 'ckPhoneErr', v => PHONE_RE.test(v), 'Số điện thoại không hợp lệ (ví dụ: 0901234567)'));
ckEmail.addEventListener('blur', () =>
    ckValidate(ckEmail, 'ckEmailErr', v => !v || EMAIL_RE.test(v), 'Email không hợp lệ (ví dụ: ten@gmail.com)'));

// === Disable submit while processing ===
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const okPhone = ckValidate(ckPhone, 'ckPhoneErr', v => PHONE_RE.test(v), 'Số điện thoại không hợp lệ (ví dụ: 0901234567)');
    const okEmail = ckValidate(ckEmail, 'ckEmailErr', v => !v || EMAIL_RE.test(v), 'Email không hợp lệ (ví dụ: ten@gmail.com)');
    if (!okPhone || !okEmail) { e.preventDefault(); return; }

    const btn = this.querySelector('.btn-place-order');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
});
</script>

<?php include ROOT_PATH . '/views/bo_cuc/chan_trang.php'; ?>
