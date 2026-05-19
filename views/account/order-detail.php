<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/order">Đơn hàng</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($order['order_code']) ?></li>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="page-heading mb-0">Đơn hàng #<?= htmlspecialchars($order['order_code']) ?></h1>
            <small class="text-muted">Đặt lúc <?= formatDateTime($order['created_at']) ?></small>
        </div>
        <a href="<?= SITE_URL ?>/order" class="btn btn-outline-tech">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Status Timeline (Vertical) -->
    <div class="checkout-card mb-4">
        <h5 class="checkout-card-title"><i class="fas fa-route me-2"></i>Trạng thái đơn hàng</h5>
        <?php
        $steps = ['pending','confirmed','shipping','delivered','completed'];
        $stepLabels = ['Chờ xác nhận','Đã xác nhận','Đang giao hàng','Đã giao','Hoàn thành'];
        $stepIcons  = ['clock','check-circle','truck','box-open','star'];
        $curIdx = array_search($order['status'], $steps);
        ?>

        <?php if ($order['status'] === 'cancelled'): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="fas fa-times-circle fa-2x"></i>
            <div>
                <strong>Đơn hàng đã bị hủy</strong>
                <?php if (!empty($order['cancel_reason'])): ?>
                <br><small>Lý do: <?= htmlspecialchars($order['cancel_reason']) ?></small>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="vertical-timeline">
            <?php foreach ($steps as $i => $step): ?>
            <div class="vt-step <?= $curIdx !== false && $i <= $curIdx ? 'done' : '' ?> <?= $order['status'] === $step ? 'active' : '' ?>">
                <div class="vt-dot"><i class="fas fa-<?= $stepIcons[$i] ?>"></i></div>
                <div class="vt-label"><?= $stepLabels[$i] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($order['status'] === 'pending'): ?>
        <form method="POST" action="<?= SITE_URL ?>/order/cancel/<?= $order['id'] ?>" class="mt-3">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng?')">
                <i class="fas fa-times me-2"></i>Hủy đơn hàng
            </button>
        </form>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- Products -->
        <div class="col-lg-8">
            <div class="checkout-card">
                <h5 class="checkout-card-title"><i class="fas fa-box me-2"></i>Sản phẩm</h5>
                <div class="table-responsive">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th colspan="2">Sản phẩm</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $item): ?>
                            <tr>
                                <td style="width:60px">
                                    <img src="<?= imgUrl($item['thumbnail']) ?>"
                                         style="width:60px;height:60px;object-fit:cover;border-radius:8px"
                                         onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                                </td>
                                <td>
                                    <?php if (!empty($item['slug'])): ?>
                                    <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($item['slug']) ?>" class="text-decoration-none">
                                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                    </a>
                                    <?php else: ?>
                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">x<?= $item['quantity'] ?></td>
                                <td class="text-end"><?= formatPrice($item['price']) ?></td>
                                <td class="text-end text-warning fw-700"><?= formatPrice($item['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="order-summary-card">
                <h5 class="summary-title"><i class="fas fa-receipt me-2"></i>Tóm tắt</h5>
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <strong><?= formatPrice($order['subtotal']) ?></strong>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="summary-row text-success">
                    <span>Giảm giá <?= !empty($order['voucher_code']) ? '(' . htmlspecialchars($order['voucher_code']) . ')' : '' ?>:</span>
                    <span>-<?= formatPrice($order['discount_amount']) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span class="text-success">Miễn phí</span>
                </div>
                <hr>
                <div class="summary-row summary-total">
                    <span>Tổng cộng:</span>
                    <strong class="text-warning"><?= formatPrice($order['total_amount']) ?></strong>
                </div>
            </div>

            <div class="checkout-card mt-3">
                <h6><i class="fas fa-truck me-2"></i>Thông tin giao hàng</h6>
                <hr>
                <p class="mb-1"><strong><?= htmlspecialchars($order['shipping_name']) ?></strong></p>
                <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i><?= htmlspecialchars($order['shipping_phone']) ?></p>
                <?php if (!empty($order['shipping_email'])): ?>
                <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($order['shipping_email']) ?></p>
                <?php endif; ?>
                <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-muted"></i><?= htmlspecialchars($order['shipping_address']) ?></p>
                <?php if (!empty($order['note'])): ?>
                <hr>
                <small class="text-muted"><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></small>
                <?php endif; ?>
            </div>

            <div class="checkout-card mt-3">
                <h6><i class="fas fa-credit-card me-2"></i>Thanh toán</h6>
                <hr>
                <?php $payMap = ['cod'=>'COD (Tiền mặt)','bank'=>'Chuyển khoản','momo'=>'Ví MoMo','vnpay'=>'VNPay']; ?>
                <p class="mb-1">Phương thức: <span class="badge bg-info"><?= $payMap[$order['payment_method']] ?? $order['payment_method'] ?></span></p>
                <p class="mb-0">Trạng thái:
                    <span class="badge <?= $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>