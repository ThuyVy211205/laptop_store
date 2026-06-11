<?php
/* Admin check — controller should have already verified, but guard here too */
if (empty($_SESSION['admin'])) {
    header('Location: ' . SITE_URL . '/admin/login');
    exit;
}

$admin       = $_SESSION['admin'];
$stats       = $stats       ?? ['total_revenue'=>0,'total_orders'=>0,'total_products'=>0,'total_customers'=>0,'pending_orders'=>0];
$recentOrders= $recentOrders?? [];
$topProducts = $topProducts ?? [];
$revenueData = $revenueData ?? [];

$statusLabels = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — VQSTORE Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-wrapper">

    <!-- ===== SIDEBAR ===== -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= SITE_URL ?>/admin" class="admin-brand">
            <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.png?v=<?= filemtime(ROOT_PATH.'/assets/images/logo_VQSTORE.png') ?>" alt="VQStore" style="height:65px;width:auto;display:block;">
        </a>

        <nav class="admin-nav">
            <span class="admin-nav-label">Tổng quan</span>
            <a href="<?= SITE_URL ?>/admin" class="admin-nav-item active">
                <i class="fas fa-th-large"></i> Dashboard
            </a>

            <span class="admin-nav-label">Quản lý</span>
            <a href="<?= SITE_URL ?>/admin/products" class="admin-nav-item">
                <i class="fas fa-laptop"></i> Sản phẩm
            </a>
            <a href="<?= SITE_URL ?>/admin/orders" class="admin-nav-item">
                <i class="fas fa-shopping-bag"></i> Đơn hàng
                <?php if (($stats['pending_orders'] ?? 0) > 0): ?>
                <span class="badge-count"><?= $stats['pending_orders'] ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/admin/customers" class="admin-nav-item">
                <i class="fas fa-users"></i> Khách hàng
            </a>
            <a href="<?= SITE_URL ?>/admin/employees" class="admin-nav-item">
                <i class="fas fa-user-tie"></i> Nhân viên
            </a>
            <a href="<?= SITE_URL ?>/admin/vouchers" class="admin-nav-item">
                <i class="fas fa-tag"></i> Voucher
            </a>
            <a href="<?= SITE_URL ?>/admin/reviews" class="admin-nav-item">
                <i class="fas fa-star"></i> Đánh giá
            </a>
            <a href="<?= SITE_URL ?>/admin/contacts" class="admin-nav-item">
                <i class="fas fa-envelope"></i> Tin nhắn
                <?php
                $newContactCount = db()->fetch("SELECT COUNT(*) AS c FROM lien_he WHERE status='new'")['c'] ?? 0;
                if ($newContactCount > 0): ?>
                <span class="badge-count"><?= $newContactCount ?></span>
                <?php endif; ?>
            </a>

            <span class="admin-nav-label">Báo cáo</span>
            <a href="<?= SITE_URL ?>/admin/revenue" class="admin-nav-item">
                <i class="fas fa-chart-line"></i> Doanh thu
            </a>

            <div class="admin-nav-divider"></div>

            <a href="<?= SITE_URL ?>" class="admin-nav-item" target="_blank">
                <i class="fas fa-external-link-alt"></i> Xem trang web
            </a>
            <a href="<?= SITE_URL ?>/admin/logout" class="admin-nav-item">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </nav>

        <div class="admin-sidebar-user">
            <div class="admin-sidebar-avatar">
                <?= mb_strtoupper(mb_substr($admin['full_name'], 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
            <div>
                <div class="admin-sidebar-name"><?= htmlspecialchars($admin['full_name']) ?></div>
                <div class="admin-sidebar-role"><?= htmlspecialchars($admin['role'] ?? 'Admin') ?></div>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <div class="admin-main">

        <!-- Top bar -->
        <header class="admin-topbar">
            <button class="admin-mobile-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="admin-topbar-title">
                <h5>Dashboard</h5>
                <small>Chào mừng trở lại, <?= htmlspecialchars($admin['full_name']) ?>!</small>
            </div>
            <div class="admin-topbar-right">
                <a href="<?= SITE_URL ?>/admin/orders?status=pending" class="admin-topbar-btn" title="Đơn hàng chờ xử lý">
                    <i class="fas fa-bell"></i>
                    <?php if (($stats['pending_orders'] ?? 0) > 0): ?>
                    <span class="admin-topbar-notif"></span>
                    <?php endif; ?>
                </a>
                <a href="<?= SITE_URL ?>" target="_blank" class="admin-topbar-btn" title="Xem trang web">
                    <i class="fas fa-globe"></i>
                </a>
            </div>
        </header>

        <!-- Content -->
        <div class="admin-content">

            <!-- Page header -->
            <div class="admin-page-header">
                <div>
                    <h4 class="admin-page-title">Tổng quan</h4>
                    <div class="admin-page-breadcrumb">
                        <a href="<?= SITE_URL ?>/admin">Admin</a>
                        <span> / Dashboard</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-light text-secondary border" style="font-size:12px;padding:7px 12px;">
                        <i class="fas fa-calendar me-1"></i><?= date('d/m/Y') ?>
                    </span>
                </div>
            </div>

            <!-- ===== STATS GRID ===== -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon blue"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-value"><?= formatPrice($stats['total_revenue'] ?? 0) ?></div>
                        <div class="stat-card-label">Doanh thu (hoàn thành)</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon green"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
                        <div class="stat-card-label">Tổng đơn hàng</div>
                        <?php if (($stats['pending_orders'] ?? 0) > 0): ?>
                        <div class="stat-card-change warn">
                            <i class="fas fa-clock"></i> <?= $stats['pending_orders'] ?> chờ xử lý
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon amber"><i class="fas fa-laptop"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-value"><?= number_format($stats['total_products'] ?? 0) ?></div>
                        <div class="stat-card-label">Sản phẩm</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon purple"><i class="fas fa-users"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-value"><?= number_format($stats['total_customers'] ?? 0) ?></div>
                        <div class="stat-card-label">Khách hàng</div>
                    </div>
                </div>
            </div>

            <!-- ===== CHARTS ROW ===== -->
            <div class="row g-4 mb-4">
                <!-- Revenue chart -->
                <div class="col-lg-8">
                    <div class="admin-card mb-0">
                        <div class="admin-card-header">
                            <h6 class="admin-card-title">
                                <i class="fas fa-chart-bar"></i> Doanh thu 6 tháng gần nhất
                            </h6>
                            <a href="<?= SITE_URL ?>/admin/revenue" class="btn-admin btn-admin-outline btn-admin-sm">
                                Chi tiết
                            </a>
                        </div>
                        <div class="admin-card-body">
                            <div style="position:relative; height:260px;">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top products -->
                <div class="col-lg-4">
                    <div class="admin-card mb-0" style="height:100%;">
                        <div class="admin-card-header">
                            <h6 class="admin-card-title">
                                <i class="fas fa-fire"></i> Top sản phẩm bán chạy
                            </h6>
                            <a href="<?= SITE_URL ?>/admin/products" class="btn-admin btn-admin-outline btn-admin-sm">Xem tất cả</a>
                        </div>
                        <div class="admin-card-body" style="padding-top:8px;padding-bottom:8px;">
                            <?php if (!empty($topProducts)): ?>
                                <?php
                                $rankClass = ['gold','silver','bronze','',''];
                                foreach ($topProducts as $i => $p):
                                ?>
                                <div class="mini-stat">
                                    <div class="mini-stat-rank <?= $rankClass[$i] ?? '' ?>"><?= $i + 1 ?></div>
                                    <div class="mini-stat-info">
                                        <div class="mini-stat-name"><?= htmlspecialchars($p['name']) ?></div>
                                        <div class="mini-stat-sub">Đã bán: <?= number_format($p['sold_quantity']) ?></div>
                                    </div>
                                    <div class="mini-stat-val"><?= formatPrice($p['final_price']) ?></div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center py-4 mb-0" style="color:#8a96b8;font-size:14px;">
                                    <i class="fas fa-inbox d-block mb-2" style="font-size:28px;opacity:.4;"></i>
                                    Chưa có dữ liệu
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== RECENT ORDERS ===== -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h6 class="admin-card-title">
                        <i class="fas fa-clock"></i> Đơn hàng gần đây
                    </h6>
                    <a href="<?= SITE_URL ?>/admin/orders" class="btn-admin btn-admin-outline btn-admin-sm">Xem tất cả</a>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($recentOrders)): ?>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>
                                    <strong style="color:#2563eb;font-family:'Exo 2',sans-serif;">
                                        <?= htmlspecialchars($order['order_code']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:#1e2a3b;"><?= htmlspecialchars($order['shipping_name']) ?></div>
                                    <div style="font-size:12px;color:#8a96b8;"><?= htmlspecialchars($order['shipping_phone']) ?></div>
                                </td>
                                <td>
                                    <strong style="color:#059669;"><?= formatPrice($order['total_amount']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($order['payment_status'] === 'paid'): ?>
                                    <span class="badge-status completed">Đã thanh toán</span>
                                    <?php else: ?>
                                    <span class="badge-status pending">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-status <?= htmlspecialchars($order['status']) ?>">
                                        <?= $statusLabels[$order['status']] ?? $order['status'] ?>
                                    </span>
                                </td>
                                <td style="color:#8a96b8;font-size:12.5px;"><?= formatDateTime($order['created_at']) ?></td>
                                <td>
                                    <a href="<?= SITE_URL ?>/admin/orders/detail/<?= $order['id'] ?>"
                                       class="admin-action-btn view" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5" style="color:#8a96b8;">
                                    <i class="fas fa-inbox d-block mb-2" style="font-size:32px;opacity:.35;"></i>
                                    Chưa có đơn hàng nào
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mở/đóng sidebar trên mobile
document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay').addEventListener('click', function () {
    document.getElementById('adminSidebar').classList.remove('open');
    this.classList.remove('show');
});

// Khởi tạo biểu đồ doanh thu bằng Chart.js
(function () {
    const labels = <?= json_encode(array_column($revenueData, 'month')) ?>;
    const data   = <?= json_encode(array_map('floatval', array_column($revenueData, 'total'))) ?>;

    if (!document.getElementById('revenueChart')) return;

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Doanh thu',
                data,
                backgroundColor: 'rgba(37,99,235,.18)',
                borderColor: '#2563eb',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(37,99,235,.32)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + Number(ctx.raw).toLocaleString('vi-VN') + 'đ'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.04)' },
                    ticks: {
                        callback: v => (v / 1000000).toFixed(0) + 'M',
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 } }
                }
            }
        }
    });
})();
</script>
</body>
</html>
