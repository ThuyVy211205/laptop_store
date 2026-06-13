<?php
if (empty($_SESSION['admin'])) { header('Location: ' . SITE_URL . '/admin/login'); exit; }
$admin = $_SESSION['admin']; $activePage = 'revenue';
$period = $_GET['period'] ?? 'month';
$periodLabel = ['week'=>'7 ngày qua','month'=>'Tháng này','year'=>'Năm nay'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo doanh thu — VQStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="admin-wrapper">
<?php include __DIR__ . '/_thanh_ben.php'; ?>
<div class="admin-main">
    <header class="admin-topbar">
        <button class="admin-mobile-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="admin-topbar-title">
            <h5>Báo cáo doanh thu</h5>
            <small><?= $periodLabel[$period] ?></small>
        </div>
    </header>
    <div class="admin-content">
        <div class="admin-page-header">
            <div>
                <h4 class="admin-page-title">Doanh thu</h4>
                <div class="admin-page-breadcrumb"><a href="<?= SITE_URL ?>/admin">Admin</a> / Doanh thu</div>
            </div>
            <div class="d-flex gap-2">
                <?php foreach ($periodLabel as $p => $l): ?>
                <a href="?period=<?= $p ?>"
                   class="btn-admin btn-admin-sm <?= $period === $p ? 'btn-admin-primary' : 'btn-admin-outline' ?>">
                    <?= $l ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="admin-stat-body">
                        <div class="admin-stat-value"><?= formatPrice($summary['today'] ?? 0) ?></div>
                        <div class="admin-stat-label">Hôm nay</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon" style="background:rgba(37,99,235,.12);color:#2563eb;">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="admin-stat-body">
                        <div class="admin-stat-value"><?= formatPrice($summary['week'] ?? 0) ?></div>
                        <div class="admin-stat-label">Tuần này</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="admin-stat-body">
                        <div class="admin-stat-value"><?= formatPrice($summary['month'] ?? 0) ?></div>
                        <div class="admin-stat-label">Tháng này</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon" style="background:rgba(220,38,38,.12);color:#dc2626;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="admin-stat-body">
                        <div class="admin-stat-value"><?= formatPrice($summary['year'] ?? 0) ?></div>
                        <div class="admin-stat-label">Năm nay</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="admin-card" style="padding:20px;text-align:center;">
                    <div style="font-size:28px;font-weight:700;color:#1e2a3b;"><?= number_format($summary['tong_don_hang'] ?? 0) ?></div>
                    <div style="font-size:13px;color:#8a96b8;margin-top:4px;">Tổng đơn hàng</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-card" style="padding:20px;text-align:center;">
                    <div style="font-size:28px;font-weight:700;color:#10b981;"><?= number_format($summary['completed_orders'] ?? 0) ?></div>
                    <div style="font-size:13px;color:#8a96b8;margin-top:4px;">Đơn hoàn thành</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-card" style="padding:20px;text-align:center;">
                    <div style="font-size:28px;font-weight:700;color:#dc2626;"><?= number_format($summary['cancelled_orders'] ?? 0) ?></div>
                    <div style="font-size:13px;color:#8a96b8;margin-top:4px;">Đơn bị hủy</div>
                </div>
            </div>
        </div>

        <!-- Revenue chart -->
        <div class="admin-card mb-4" style="padding:24px;">
            <h6 style="font-weight:700;color:#1e2a3b;margin-bottom:20px;">
                <i class="fas fa-chart-bar me-2" style="color:#2563eb;"></i>
                Doanh thu — <?= $periodLabel[$period] ?>
            </h6>
            <canvas id="revenueChart" height="80"></canvas>
        </div>

        <!-- Top products -->
        <?php if (!empty($topProducts)): ?>
        <div class="admin-card">
            <div style="padding:16px 20px 0;font-weight:700;font-size:15px;color:#1e2a3b;border-bottom:1px solid #e5e7eb;padding-bottom:14px;margin-bottom:0;">
                <i class="fas fa-trophy me-2" style="color:#f59e0b;"></i> Top sản phẩm bán chạy
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr>
                        <th>#</th><th>Sản phẩm</th><th>Đã bán</th><th>Giá</th><th>Doanh thu</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($topProducts as $i => $p): ?>
                    <tr>
                        <td style="font-weight:700;color:<?= $i === 0 ? '#f59e0b' : ($i === 1 ? '#94a3b8' : ($i === 2 ? '#cd7f32' : '#8a96b8')) ?>;">
                            #<?= $i+1 ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= htmlspecialchars(imgUrl($p['hinh_thu_nho'] ?? '')) ?>"
                                     style="width:40px;height:40px;object-fit:contain;border-radius:6px;border:1px solid #e5e7eb;" alt=""
                                     onerror="this.onerror=null;this.src='<?= noImageUrl() ?>'">
                                <span style="font-size:13px;font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= htmlspecialchars($p['ten']) ?>
                                </span>
                            </div>
                        </td>
                        <td><?= number_format($p['so_luong_ban']) ?></td>
                        <td><?= formatPrice($p['gia_cuoi']) ?></td>
                        <td style="font-weight:700;color:#dc2626;"><?= formatPrice($p['revenue']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
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

const chartLabels = <?= json_encode(array_column($chartData ?? [], 'label')) ?>;
const chartValues = <?= json_encode(array_map(fn($r) => (float)$r['total'], $chartData ?? [])) ?>;

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Doanh thu (đ)',
            data: chartValues,
            backgroundColor: 'rgba(37,99,235,.18)',
            borderColor: '#2563eb',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => (v/1000000).toFixed(0) + 'M' }
            }
        }
    }
});
</script>
</body></html>
