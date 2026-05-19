<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="success-wrap">
        <!-- Animated Check -->
        <div class="success-icon">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>

        <h1 class="success-title">Đặt hàng thành công!</h1>
        <p class="success-subtitle">
            Cảm ơn bạn đã mua sắm tại TechStore.<br>
            Mã đơn hàng của bạn: <strong class="text-warning"><?= htmlspecialchars($order['order_code']) ?></strong>
        </p>

        <!-- Status Timeline -->
        <div class="success-timeline">
            <div class="success-step active">
                <div class="step-icon"><i class="fas fa-check"></i></div>
                <div class="step-label">Đặt hàng</div>
            </div>
            <div class="step-line"></div>
            <div class="success-step">
                <div class="step-icon"><i class="fas fa-box"></i></div>
                <div class="step-label">Xác nhận</div>
            </div>
            <div class="step-line"></div>
            <div class="success-step">
                <div class="step-icon"><i class="fas fa-truck"></i></div>
                <div class="step-label">Vận chuyển</div>
            </div>
            <div class="step-line"></div>
            <div class="success-step">
                <div class="step-icon"><i class="fas fa-home"></i></div>
                <div class="step-label">Hoàn thành</div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="success-summary">
            <div class="summary-row">
                <span>Mã đơn hàng:</span>
                <strong><?= htmlspecialchars($order['order_code']) ?></strong>
            </div>
            <div class="summary-row">
                <span>Ngày đặt:</span>
                <span><?= formatDateTime($order['created_at']) ?></span>
            </div>
            <div class="summary-row">
                <span>Người nhận:</span>
                <span><?= htmlspecialchars($order['shipping_name']) ?></span>
            </div>
            <div class="summary-row">
                <span>SĐT:</span>
                <span><?= htmlspecialchars($order['shipping_phone']) ?></span>
            </div>
            <div class="summary-row">
                <span>Địa chỉ:</span>
                <span><?= htmlspecialchars($order['shipping_address']) ?></span>
            </div>
            <div class="summary-row">
                <span>Thanh toán:</span>
                <?php
                $payMap = ['cod'=>'COD','bank'=>'Chuyển khoản','momo'=>'MoMo','vnpay'=>'VNPay'];
                ?>
                <span><?= $payMap[$order['payment_method']] ?? $order['payment_method'] ?></span>
            </div>
            <hr>
            <div class="summary-row">
                <span>Tạm tính:</span>
                <span><?= formatPrice($order['subtotal']) ?></span>
            </div>
            <?php if ($order['discount_amount'] > 0): ?>
            <div class="summary-row text-success">
                <span>Giảm giá <?= !empty($order['voucher_code']) ? '(' . $order['voucher_code'] . ')' : '' ?>:</span>
                <span>-<?= formatPrice($order['discount_amount']) ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row summary-total">
                <span>Tổng cộng:</span>
                <strong class="text-warning"><?= formatPrice($order['total_amount']) ?></strong>
            </div>
        </div>

        <!-- Actions -->
        <div class="success-actions mt-4">
            <a href="<?= SITE_URL ?>/order/detail/<?= $order['id'] ?>" class="btn btn-tech">
                <i class="fas fa-file-alt me-2"></i>Xem chi tiết đơn hàng
            </a>
            <a href="<?= SITE_URL ?>/products" class="btn btn-outline-tech">
                <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
            </a>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>