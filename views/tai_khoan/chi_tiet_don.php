<?php include ROOT_PATH . '/views/bo_cuc/dau_trang.php'; ?>

<?php
$statusMap = [
    'pending'         => ['label' => 'Chờ xác nhận',    'badge' => 'bg-warning text-dark'],
    'confirmed'       => ['label' => 'Đã xác nhận',      'badge' => 'bg-info text-white'],
    'shipping'        => ['label' => 'Đang giao',         'badge' => 'bg-primary text-white'],
    'delivery_failed' => ['label' => 'Giao hàng thất bại', 'badge' => 'bg-danger text-white'],
    'delivered'       => ['label' => 'Đã giao',            'badge' => 'bg-success text-white'],
    'completed'       => ['label' => 'Hoàn thành',         'badge' => 'bg-success text-white'],
    'cancelled'       => ['label' => 'Đã hủy',             'badge' => 'bg-danger text-white'],
];
$payMap = [
    'cod'           => ['label' => 'Tiền mặt khi nhận (COD)', 'icon' => 'money-bill-wave'],
    'bank'          => ['label' => 'Chuyển khoản ngân hàng',  'icon' => 'university'],
    'momo'          => ['label' => 'Ví MoMo',                 'icon' => 'wallet'],
    'vnpay'         => ['label' => 'VNPay',                   'icon' => 'credit-card'],
];
$statusInfo = $statusMap[$order['trang_thai']] ?? ['label' => $order['trang_thai'], 'badge' => 'bg-secondary'];
$payInfo    = $payMap[$order['phuong_thuc_thanh_toan']] ?? ['label' => $order['phuong_thuc_thanh_toan'], 'icon' => 'credit-card'];

$steps      = ['pending','confirmed','shipping','delivered','completed'];
$stepLabels = ['Chờ xác nhận','Đã xác nhận','Đang giao','Đã giao','Hoàn thành'];
$stepIcons  = ['clock','check-circle','truck','box-open','star'];
$curIdx     = array_search($order['trang_thai'], $steps);
?>

<!-- Breadcrumb -->
<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/order">Đơn hàng của tôi</a></li>
            <li class="breadcrumb-item active">#<?= htmlspecialchars($order['ma_don_hang']) ?></li>
        </ol>
    </div>
</nav>

<div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h1 class="page-heading mb-1">Đơn hàng #<?= htmlspecialchars($order['ma_don_hang']) ?></h1>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <small class="text-muted"><i class="fas fa-clock me-1"></i><?= formatDateTime($order['ngay_tao']) ?></small>
                <span class="badge <?= $statusInfo['badge'] ?>"><?= $statusInfo['label'] ?></span>
            </div>
        </div>
        <a href="<?= SITE_URL ?>/order" class="btn btn-outline-tech">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Status Timeline -->
    <div class="checkout-card mb-4">
        <h5 class="checkout-card-title"><i class="fas fa-route me-2 text-primary"></i>Trạng thái đơn hàng</h5>

        <?php if ($order['trang_thai'] === 'cancelled'): ?>
        <div class="alert alert-danger d-flex align-items-center gap-3 mb-0">
            <i class="fas fa-times-circle fa-2x flex-shrink-0"></i>
            <div>
                <strong class="d-block">Đơn hàng đã bị hủy</strong>
                <?php if (!empty($order['ly_do_huy'])): ?>
                <small class="text-muted">Lý do: <?= htmlspecialchars($order['ly_do_huy']) ?></small>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($order['trang_thai'] === 'delivery_failed'): ?>
        <div class="alert alert-danger d-flex align-items-center gap-3 mb-0">
            <i class="fas fa-truck fa-2x flex-shrink-0"></i>
            <div>
                <strong class="d-block">Giao hàng không thành công</strong>
                <?php if (!empty($order['ly_do_huy'])): ?>
                <small class="text-muted">Lý do: <?= htmlspecialchars($order['ly_do_huy']) ?></small>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>

        <!-- Horizontal timeline for desktop, vertical for mobile -->
        <div class="order-timeline">
            <?php foreach ($steps as $i => $step):
                $isDone   = ($curIdx !== false && $i < $curIdx);
                $isActive = ($order['trang_thai'] === $step);
            ?>
            <div class="ot-step <?= $isDone ? 'done' : '' ?> <?= $isActive ? 'active' : '' ?>">
                <div class="ot-dot">
                    <i class="fas fa-<?= $isDone ? 'check' : $stepIcons[$i] ?>"></i>
                </div>
                <div class="ot-label"><?= $stepLabels[$i] ?></div>
            </div>
            <?php if ($i < count($steps) - 1): ?>
            <div class="ot-line <?= ($curIdx !== false && $i < $curIdx) ? 'done' : '' ?>"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($order['trang_thai'] === 'pending'): ?>
        <div class="mt-4 pt-3 border-top">
            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="fas fa-times me-2"></i>Hủy đơn hàng
            </button>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>

    <div class="row g-4">

        <!-- Left: Products -->
        <div class="col-lg-8">
            <div class="checkout-card">
                <h5 class="checkout-card-title">
                    <i class="fas fa-box-open me-2 text-primary"></i>
                    Sản phẩm đã đặt
                    <span class="badge bg-secondary ms-2"><?= count($details) ?> sản phẩm</span>
                </h5>

                <?php foreach ($details as $item): ?>
                <div class="order-item-row">
                    <div class="order-item-img-wrap">
                        <?php
                        $imgSrc = $item['anh_dau'] ?: $item['hinh_thu_nho'] ?: $item['hinh_san_pham'] ?? '';
                        ?>
                        <img src="<?= htmlspecialchars(imgUrl($imgSrc)) ?>"
                             alt="<?= htmlspecialchars($item['ten_san_pham']) ?>"
                             onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2264%22 height=%2264%22%3E%3Crect fill=%22%23f3f4f6%22 width=%2264%22 height=%2264%22/%3E%3Ctext fill=%22%23adb5bd%22 font-family=%22sans-serif%22 font-size=%2222%22 text-anchor=%22middle%22 x=%2232%22 y=%2240%22%3E%F0%9F%96%A5%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="order-item-info">
                        <?php if (!empty($item['duong_dan'])): ?>
                        <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($item['duong_dan']) ?>"
                           class="order-item-name text-decoration-none">
                            <?= htmlspecialchars($item['ten_san_pham']) ?>
                        </a>
                        <?php else: ?>
                        <span class="order-item-name"><?= htmlspecialchars($item['ten_san_pham']) ?></span>
                        <?php endif; ?>
                        <small class="text-muted">
                            Đơn giá: <?= formatPrice($item['gia']) ?>
                            &nbsp;×&nbsp;
                            <strong><?= $item['so_luong'] ?></strong>
                        </small>
                    </div>
                    <div class="order-item-subtotal">
                        <?= formatPrice($item['tam_tinh']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Summary + Shipping + Payment -->
        <div class="col-lg-4 d-flex flex-column gap-3">

            <!-- Order Summary -->
            <div class="order-summary-card">
                <h5 class="summary-title"><i class="fas fa-receipt me-2 text-primary"></i>Tóm tắt đơn hàng</h5>

                <div class="summary-row">
                    <span class="text-muted">Tạm tính</span>
                    <span><?= formatPrice($order['tam_tinh']) ?></span>
                </div>

                <?php if ($order['so_tien_giam'] > 0): ?>
                <div class="summary-row text-success">
                    <span>
                        Giảm giá
                        <?php if (!empty($order['ma_phieu_voucher'])): ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success ms-1" style="font-size:11px;">
                            <?= htmlspecialchars($order['ma_phieu_voucher']) ?>
                        </span>
                        <?php endif; ?>
                    </span>
                    <span>-<?= formatPrice($order['so_tien_giam']) ?></span>
                </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span class="text-muted">Phí vận chuyển</span>
                    <span class="text-success fw-semibold">Miễn phí</span>
                </div>

                <div class="summary-row summary-total mt-1">
                    <span class="fw-bold">Tổng cộng</span>
                    <strong class="text-warning fs-5"><?= formatPrice($order['tong_tien']) ?></strong>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="checkout-card">
                <h6 class="fw-bold mb-3"><i class="fas fa-truck me-2 text-primary"></i>Thông tin giao hàng</h6>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-user fa-fw"></i></span>
                        <span class="fw-semibold"><?= htmlspecialchars($order['ten_giao_hang']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-phone fa-fw"></i></span>
                        <span><?= htmlspecialchars($order['sdt_giao_hang']) ?></span>
                    </div>
                    <?php if (!empty($order['thu_dien_tu_giao_hang'])): ?>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-envelope fa-fw"></i></span>
                        <span><?= htmlspecialchars($order['thu_dien_tu_giao_hang']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-row align-items-start">
                        <span class="info-label"><i class="fas fa-map-marker-alt fa-fw"></i></span>
                        <span><?= htmlspecialchars($order['dia_chi_giao_hang']) ?></span>
                    </div>
                    <?php if (!empty($order['ghi_chu'])): ?>
                    <div class="info-row align-items-start">
                        <span class="info-label"><i class="fas fa-sticky-note fa-fw"></i></span>
                        <span class="text-muted fst-italic"><?= htmlspecialchars($order['ghi_chu']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="checkout-card">
                <h6 class="fw-bold mb-3"><i class="fas fa-<?= $payInfo['icon'] ?> me-2 text-primary"></i>Thanh toán</h6>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label text-muted">Phương thức</span>
                        <span><?= $payInfo['label'] ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label text-muted">Trạng thái</span>
                        <span class="badge <?= $order['trang_thai_thanh_toan'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                            <?= $order['trang_thai_thanh_toan'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Cancel Modal -->
<?php if ($order['trang_thai'] === 'pending'): ?>
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Hủy đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/order/cancel/<?= $order['id'] ?>">
                <div class="modal-body">
                    <p class="text-muted">Bạn có chắc muốn hủy đơn hàng <strong>#<?= htmlspecialchars($order['ma_don_hang']) ?></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lý do hủy</label>
                        <select name="reason" class="form-select">
                            <option value="Tôi đặt nhầm sản phẩm">Đặt nhầm sản phẩm</option>
                            <option value="Tôi muốn thay đổi địa chỉ giao hàng">Muốn thay đổi địa chỉ</option>
                            <option value="Tôi tìm thấy giá tốt hơn ở nơi khác">Giá tốt hơn ở nơi khác</option>
                            <option value="Khách hàng tự hủy">Lý do khác</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Giữ đơn hàng</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times me-2"></i>Xác nhận hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* ── Order Timeline (horizontal) ── */
.order-timeline {
    display: flex;
    align-items: center;
    gap: 0;
    overflow-x: auto;
    padding-bottom: 4px;
}
.ot-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    min-width: 80px;
}
.ot-dot {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: var(--color-surface-2);
    border: 2px solid var(--color-border);
    color: var(--color-text-3);
    font-size: 16px;
    transition: all .25s;
}
.ot-label {
    font-size: 12px;
    color: var(--color-text-3);
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
}
.ot-step.done .ot-dot {
    background: var(--color-green);
    border-color: var(--color-green);
    color: #fff;
}
.ot-step.done .ot-label { color: var(--color-text); font-weight: 600; }

.ot-step.active .ot-dot {
    background: var(--bs-primary, #1d4ed8);
    border-color: var(--bs-primary, #1d4ed8);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(29,78,216,.18);
}
.ot-step.active .ot-label { color: var(--bs-primary, #1d4ed8); font-weight: 700; }

.ot-line {
    flex: 1;
    height: 2px;
    background: var(--color-border);
    min-width: 20px;
    margin-bottom: 28px;
    transition: background .25s;
}
.ot-line.done { background: var(--color-green); }

/* ── Product item image wrapper ── */
.order-item-img-wrap {
    width: 68px; height: 68px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--color-surface-2);
    border: 1px solid var(--color-border);
}
.order-item-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.order-item-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-text);
    display: block;
    line-height: 1.4;
    word-break: break-word;
}
.order-item-subtotal {
    font-size: 15px;
    font-weight: 700;
    color: var(--color-warning, #f59e0b);
    white-space: nowrap;
    flex-shrink: 0;
    margin-left: auto;
}

/* ── Info list ── */
.info-list { display: flex; flex-direction: column; gap: 10px; }
.info-row { display: flex; align-items: center; gap: 10px; font-size: 14px; }
.info-label { color: var(--color-text-3); flex-shrink: 0; min-width: 24px; }

/* ── Mobile ── */
@media (max-width: 576px) {
    .ot-step { min-width: 64px; }
    .ot-dot { width: 36px; height: 36px; font-size: 13px; }
    .ot-label { font-size: 11px; }
    .ot-line { min-width: 10px; margin-bottom: 24px; }
    .order-item-img-wrap { width: 56px; height: 56px; }
}
</style>

<?php include ROOT_PATH . '/views/bo_cuc/chan_trang.php'; ?>
