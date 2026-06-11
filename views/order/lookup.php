<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<!-- Breadcrumb -->
<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Tra cứu đơn hàng</li>
        </ol>
    </div>
</nav>

<div class="container py-5" style="max-width:760px;">

    <!-- Search form -->
    <div class="checkout-card mb-4">
        <h1 class="page-heading mb-1" style="font-size:1.5rem;">
            <i class="fas fa-search me-2 text-primary"></i>Tra cứu đơn hàng
        </h1>
        <p class="text-muted mb-4" style="font-size:14px;">Nhập mã đơn hàng để xem trạng thái và thông tin giao hàng.</p>

        <form method="GET" action="<?= SITE_URL ?>/order/lookup" class="d-flex gap-2">
            <input type="text"
                   name="code"
                   value="<?= htmlspecialchars($code) ?>"
                   placeholder="Ví dụ: TS2506120ABCD"
                   class="form-control"
                   style="font-family:monospace;letter-spacing:.04em;"
                   autofocus>
            <button type="submit" class="btn btn-primary px-4 flex-shrink-0">
                <i class="fas fa-search me-1"></i>Tra cứu
            </button>
        </form>

        <?php if ($error): ?>
        <div class="alert alert-danger mt-3 mb-0 py-2" style="font-size:14px;">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($order): ?>
    <?php
    $statusMap = [
        'pending'   => ['label' => 'Chờ xác nhận', 'badge' => 'bg-warning text-dark'],
        'confirmed' => ['label' => 'Đã xác nhận',  'badge' => 'bg-info text-white'],
        'shipping'  => ['label' => 'Đang giao',     'badge' => 'bg-primary text-white'],
        'delivered' => ['label' => 'Đã giao',        'badge' => 'bg-success text-white'],
        'completed' => ['label' => 'Hoàn thành',     'badge' => 'bg-success text-white'],
        'cancelled' => ['label' => 'Đã hủy',         'badge' => 'bg-danger text-white'],
    ];
    $payMap = [
        'cod'           => 'Tiền mặt khi nhận (COD)',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo'          => 'Ví MoMo',
        'vnpay'         => 'VNPay',
    ];
    $statusInfo = $statusMap[$order['status']] ?? ['label' => $order['status'], 'badge' => 'bg-secondary'];
    $steps      = ['pending','confirmed','shipping','delivered','completed'];
    $stepLabels = ['Chờ xác nhận','Đã xác nhận','Đang giao','Đã giao','Hoàn thành'];
    $stepIcons  = ['clock','check-circle','truck','box-open','star'];
    $curIdx     = array_search($order['status'], $steps);
    ?>

    <!-- Order header -->
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1">Đơn hàng #<?= htmlspecialchars($order['order_code']) ?></h5>
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i><?= formatDateTime($order['created_at']) ?>
            </small>
        </div>
        <span class="badge <?= $statusInfo['badge'] ?> px-3 py-2"><?= $statusInfo['label'] ?></span>
    </div>

    <!-- Status Timeline -->
    <div class="checkout-card mb-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-route me-2 text-primary"></i>Trạng thái đơn hàng</h6>

        <?php if ($order['status'] === 'cancelled'): ?>
        <div class="alert alert-danger d-flex align-items-center gap-3 mb-0">
            <i class="fas fa-times-circle fa-2x flex-shrink-0"></i>
            <div>
                <strong class="d-block">Đơn hàng đã bị hủy</strong>
                <?php if (!empty($order['cancel_reason'])): ?>
                <small class="text-muted">Lý do: <?= htmlspecialchars($order['cancel_reason']) ?></small>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="order-timeline">
            <?php foreach ($steps as $i => $step):
                $isDone   = ($curIdx !== false && $i < $curIdx);
                $isActive = ($order['status'] === $step);
            ?>
            <div class="ot-step <?= $isDone ? 'done' : '' ?> <?= $isActive ? 'active' : '' ?>">
                <div class="ot-dot"><i class="fas fa-<?= $isDone ? 'check' : $stepIcons[$i] ?>"></i></div>
                <div class="ot-label"><?= $stepLabels[$i] ?></div>
            </div>
            <?php if ($i < count($steps) - 1): ?>
            <div class="ot-line <?= ($curIdx !== false && $i < $curIdx) ? 'done' : '' ?>"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- Products -->
        <div class="col-lg-7">
            <div class="checkout-card">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-box-open me-2 text-primary"></i>Sản phẩm đã đặt
                    <span class="badge bg-secondary ms-1"><?= count($details) ?></span>
                </h6>
                <?php foreach ($details as $item): ?>
                <div class="order-item-row">
                    <div class="order-item-img-wrap">
                        <?php $imgSrc = $item['first_image'] ?: $item['thumbnail'] ?: $item['product_thumbnail'] ?? ''; ?>
                        <img src="<?= htmlspecialchars(imgUrl($imgSrc)) ?>"
                             alt="<?= htmlspecialchars($item['product_name']) ?>"
                             onerror="this.onerror=null;this.style.display='none'">
                    </div>
                    <div class="order-item-info">
                        <span class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                        <small class="text-muted">
                            <?= formatPrice($item['price']) ?> &times; <strong><?= $item['quantity'] ?></strong>
                        </small>
                    </div>
                    <div class="order-item-subtotal"><?= formatPrice($item['subtotal']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary + Shipping -->
        <div class="col-lg-5 d-flex flex-column gap-3">

            <!-- Tổng tiền -->
            <div class="order-summary-card">
                <h6 class="summary-title"><i class="fas fa-receipt me-2 text-primary"></i>Tóm tắt</h6>
                <div class="summary-row">
                    <span class="text-muted">Tạm tính</span>
                    <span><?= formatPrice($order['subtotal']) ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="summary-row text-success">
                    <span>Giảm giá</span>
                    <span>-<?= formatPrice($order['discount_amount']) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span class="text-muted">Phí vận chuyển</span>
                    <span class="text-success fw-semibold">Miễn phí</span>
                </div>
                <div class="summary-row summary-total mt-1">
                    <span class="fw-bold">Tổng cộng</span>
                    <strong class="text-warning fs-5"><?= formatPrice($order['total_amount']) ?></strong>
                </div>
            </div>

            <!-- Giao hàng -->
            <div class="checkout-card">
                <h6 class="fw-bold mb-3"><i class="fas fa-truck me-2 text-primary"></i>Thông tin giao hàng</h6>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-user fa-fw"></i></span>
                        <span class="fw-semibold"><?= htmlspecialchars($order['shipping_name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-phone fa-fw"></i></span>
                        <span><?= htmlspecialchars($order['shipping_phone']) ?></span>
                    </div>
                    <div class="info-row align-items-start">
                        <span class="info-label"><i class="fas fa-map-marker-alt fa-fw"></i></span>
                        <span><?= htmlspecialchars($order['shipping_address']) ?></span>
                    </div>
                    <?php if (!empty($order['note'])): ?>
                    <div class="info-row align-items-start">
                        <span class="info-label"><i class="fas fa-sticky-note fa-fw"></i></span>
                        <span class="text-muted fst-italic"><?= htmlspecialchars($order['note']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thanh toán -->
            <div class="checkout-card">
                <h6 class="fw-bold mb-3"><i class="fas fa-credit-card me-2 text-primary"></i>Thanh toán</h6>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label text-muted">Phương thức</span>
                        <span><?= $payMap[$order['payment_method']] ?? $order['payment_method'] ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label text-muted">Trạng thái</span>
                        <span class="badge <?= $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                            <?= $order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($order['status'] === 'pending' && !isLoggedIn()): ?>
            <div class="alert alert-info py-2 mb-0" style="font-size:13px;">
                <i class="fas fa-info-circle me-1"></i>
                <a href="<?= SITE_URL ?>/auth/login" class="fw-semibold">Đăng nhập</a> để hủy đơn hàng nếu cần.
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php endif; ?>
</div>

<style>
.order-timeline { display:flex; align-items:center; gap:0; overflow-x:auto; padding-bottom:4px; }
.ot-step { display:flex; flex-direction:column; align-items:center; gap:8px; flex-shrink:0; min-width:80px; }
.ot-dot { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center;
          background:var(--color-surface-2); border:2px solid var(--color-border); color:var(--color-text-3);
          font-size:16px; transition:all .25s; }
.ot-label { font-size:12px; color:var(--color-text-3); font-weight:500; text-align:center; white-space:nowrap; }
.ot-step.done .ot-dot  { background:var(--color-green); border-color:var(--color-green); color:#fff; }
.ot-step.done .ot-label { color:var(--color-text); font-weight:600; }
.ot-step.active .ot-dot { background:var(--bs-primary,#1d4ed8); border-color:var(--bs-primary,#1d4ed8); color:#fff;
                           box-shadow:0 0 0 4px rgba(29,78,216,.18); }
.ot-step.active .ot-label { color:var(--bs-primary,#1d4ed8); font-weight:700; }
.ot-line { flex:1; height:2px; background:var(--color-border); min-width:20px; margin-bottom:28px; transition:background .25s; }
.ot-line.done { background:var(--color-green); }
.order-item-img-wrap { width:64px; height:64px; border-radius:10px; overflow:hidden; flex-shrink:0;
                        background:var(--color-surface-2); border:1px solid var(--color-border); }
.order-item-img-wrap img { width:100%; height:100%; object-fit:cover; display:block; }
.order-item-name { font-size:14px; font-weight:600; color:var(--color-text); display:block; margin-bottom:4px; line-height:1.4; }
.order-item-subtotal { font-size:15px; font-weight:700; color:var(--color-warning,#f59e0b); white-space:nowrap; flex-shrink:0; }
.info-list { display:flex; flex-direction:column; gap:10px; }
.info-row { display:flex; align-items:center; gap:10px; font-size:14px; }
.info-label { color:var(--color-text-3); flex-shrink:0; min-width:24px; }
@media(max-width:576px) {
    .ot-step { min-width:64px; } .ot-dot { width:36px; height:36px; font-size:13px; }
    .ot-label { font-size:11px; } .ot-line { min-width:10px; margin-bottom:24px; }
}
</style>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>
