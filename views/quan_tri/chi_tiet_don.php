<?php
if (empty($_SESSION['admin'])) { header('Location: ' . SITE_URL . '/admin/login'); exit; }
$admin      = $_SESSION['admin'];
$activePage = 'orders';
$statusLabels = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
];
$statusColors = [
    'pending'   => '#f59e0b',
    'confirmed' => '#3b82f6',
    'shipping'  => '#8b5cf6',
    'delivered' => '#06b6d4',
    'completed' => '#10b981',
    'cancelled' => '#ef4444',
];
$payLabels = [
    'cod'  => 'COD',
    'bank' => 'Chuyển khoản',
    'momo' => 'MoMo',
    'vnpay'=> 'VNPay',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — VQStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body>
<div class="admin-wrapper">
<?php include __DIR__ . '/_thanh_ben.php'; ?>
<div class="admin-main">
    <header class="admin-topbar">
        <button class="admin-mobile-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="admin-topbar-title">
            <h5>Chi tiết đơn hàng</h5>
            <small>#<?= htmlspecialchars($order['ma_don_hang']) ?></small>
        </div>
    </header>
    <div class="admin-content">
        <div class="admin-page-header">
            <div>
                <h4 class="admin-page-title">#<?= htmlspecialchars($order['ma_don_hang']) ?></h4>
                <div class="admin-page-breadcrumb">
                    <a href="<?= SITE_URL ?>/admin">Admin</a> /
                    <a href="<?= SITE_URL ?>/admin/orders">Đơn hàng</a> /
                    #<?= htmlspecialchars($order['ma_don_hang']) ?>
                </div>
            </div>
            <a href="<?= SITE_URL ?>/admin/orders" class="btn-admin btn-admin-outline btn-admin-sm">
                <i class="fas fa-arrow-left me-1"></i>Quay lại
            </a>
        </div>

        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-3">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Thông tin đơn hàng -->
            <div class="col-lg-4">
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin đơn hàng</h6>
                    </div>
                    <div class="admin-card-body" style="font-size:13.5px;">
                        <div class="mb-2">
                            <small class="text-muted d-block">Mã đơn</small>
                            <strong style="color:#2563eb;"><?= htmlspecialchars($order['ma_don_hang']) ?></strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Ngày đặt</small>
                            <?= date('d/m/Y H:i', strtotime($order['ngay_tao'])) ?>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Trạng thái</small>
                            <span class="badge-status" style="background:<?= $statusColors[$order['trang_thai']] ?>1a;color:<?= $statusColors[$order['trang_thai']] ?>;border-color:<?= $statusColors[$order['trang_thai']] ?>40;">
                                <?= $statusLabels[$order['trang_thai']] ?? $order['trang_thai'] ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Thanh toán</small>
                            <?= htmlspecialchars($payLabels[$order['phuong_thuc_thanh_toan']] ?? $order['phuong_thuc_thanh_toan']) ?>
                            &mdash;
                            <?php if ($order['trang_thai_thanh_toan'] === 'paid'): ?>
                            <span class="text-success fw-semibold">Đã thanh toán</span>
                            <?php else: ?>
                            <span class="text-warning fw-semibold">Chưa thanh toán</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($order['ly_do_huy'])): ?>
                        <div class="mb-0">
                            <small class="text-muted d-block">Lý do hủy</small>
                            <span class="text-danger"><?= htmlspecialchars($order['ly_do_huy']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h6 class="mb-0"><i class="fas fa-truck me-2"></i>Thông tin giao hàng</h6>
                    </div>
                    <div class="admin-card-body" style="font-size:13.5px;">
                        <div class="mb-2">
                            <small class="text-muted d-block">Người nhận</small>
                            <strong><?= htmlspecialchars($order['ten_giao_hang']) ?></strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Số điện thoại</small>
                            <?= htmlspecialchars($order['sdt_giao_hang']) ?>
                        </div>
                        <?php if (!empty($order['thu_dien_tu_giao_hang'])): ?>
                        <div class="mb-2">
                            <small class="text-muted d-block">Email</small>
                            <?= htmlspecialchars($order['thu_dien_tu_giao_hang']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="mb-0">
                            <small class="text-muted d-block">Địa chỉ</small>
                            <?= htmlspecialchars($order['dia_chi_giao_hang']) ?>
                        </div>
                        <?php if (!empty($order['ghi_chu'])): ?>
                        <div class="mb-0 mt-2">
                            <small class="text-muted d-block">Ghi chú</small>
                            <em class="text-muted"><?= htmlspecialchars($order['ghi_chu']) ?></em>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sản phẩm -->
            <div class="col-lg-8">
                <div class="admin-card">
                    <div class="admin-card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-box-open me-2"></i>Sản phẩm đã đặt</h6>
                        <span class="badge bg-secondary"><?= count($details) ?> sản phẩm</span>
                    </div>
                    <div class="admin-card-body p-0">
                        <table class="admin-table mb-0" style="border:0;">
                            <thead>
                                <tr>
                                    <th style="width:60px;"></th>
                                    <th>Sản phẩm</th>
                                    <th style="width:100px;">Đơn giá</th>
                                    <th style="width:70px;">SL</th>
                                    <th style="width:110px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($details as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars(imgUrl($item['anh_dau'] ?: $item['hinh_thu_nho'] ?: $item['hinh_san_pham'] ?? '')) ?>"
                                             alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;">
                                    </td>
                                    <td>
                                        <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($item['ten_san_pham']) ?></div>
                                    </td>
                                    <td><?= formatPrice($item['gia']) ?></td>
                                    <td><?= $item['so_luong'] ?></td>
                                    <td style="font-weight:700;color:#dc2626;"><?= formatPrice($item['tam_tinh']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="admin-card-body border-top" style="background:#f8fafd;">
                        <div class="d-flex justify-content-end">
                            <table style="font-size:14px;">
                                <tr>
                                    <td style="padding:3px 20px 3px 0;color:#6b7280;">Tạm tính</td>
                                    <td style="padding:3px 0;font-weight:600;"><?= formatPrice($order['tam_tinh']) ?></td>
                                </tr>
                                <?php if ($order['so_tien_giam'] > 0): ?>
                                <tr>
                                    <td style="padding:3px 20px 3px 0;color:#16a34a;">Giảm giá <?= !empty($order['ma_phieu_voucher']) ? '(' . htmlspecialchars($order['ma_phieu_voucher']) . ')' : '' ?></td>
                                    <td style="padding:3px 0;color:#16a34a;font-weight:600;">-<?= formatPrice($order['so_tien_giam']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr style="border-top:2px solid #e5e7eb;">
                                    <td style="padding:8px 20px 8px 0;font-weight:700;font-size:15px;">Tổng cộng</td>
                                    <td style="padding:8px 0;font-weight:800;color:#dc2626;font-size:17px;"><?= formatPrice($order['tong_tien']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay').addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.remove('open');
    this.classList.remove('show');
});
</script>
</body>
</html>
