<?php
if (empty($_SESSION['admin'])) { header('Location: ' . SITE_URL . '/admin/login'); exit; }
$admin = $_SESSION['admin']; $activePage = 'customers';
$rankLabel = ['silver'=>'Silver','gold'=>'Gold','diamond'=>'Diamond'];
$rankColor = ['silver'=>'#94a3b8','gold'=>'#f59e0b','diamond'=>'#06b6d4'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng — VQStore Admin</title>
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
            <h5>Quản lý khách hàng</h5>
            <small>Tổng cộng <?= number_format(count($customers ?? [])) ?> khách hàng</small>
        </div>
    </header>
    <div class="admin-content">
        <div class="admin-page-header">
            <div>
                <h4 class="admin-page-title">Khách hàng</h4>
                <div class="admin-page-breadcrumb"><a href="<?= SITE_URL ?>/admin">Admin</a> / Khách hàng</div>
            </div>
        </div>

        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-3">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-toolbar">
                <form method="GET" class="d-flex gap-2 flex-wrap" style="flex:1;">
                    <div class="admin-search-wrap">
                        <input type="text" name="search" placeholder="Tìm tên, email, SĐT..."
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    <select name="rank" class="admin-select" onchange="this.form.submit()">
                        <option value="">Tất cả rank</option>
                        <?php foreach ($rankLabel as $r => $l): ?>
                        <option value="<?= $r ?>" <?= ($_GET['rank'] ?? '') === $r ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr>
                        <th>#</th><th>Khách hàng</th><th>SĐT</th><th>Rank</th>
                        <th>Đơn hàng</th><th>Chi tiêu</th><th>Ngày đăng ký</th><th>Trạng thái</th><th>Thao tác</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!empty($customers)): foreach ($customers as $c): ?>
                    <tr>
                        <td style="color:#8a96b8;font-size:12px;"><?= $c['id'] ?></td>
                        <td>
                            <div style="font-weight:600;"><?= htmlspecialchars($c['ho_ten']) ?></div>
                            <div style="font-size:11.5px;color:#8a96b8;"><?= htmlspecialchars($c['thu_dien_tu']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($c['so_dien_thoai'] ?? '—') ?></td>
                        <td>
                            <span style="color:<?= $rankColor[$c['hang']] ?? '#94a3b8' ?>;font-weight:600;">
                                <?= $rankLabel[$c['hang']] ?? $c['hang'] ?>
                            </span>
                        </td>
                        <td><?= number_format($c['tong_don_hang']) ?></td>
                        <td style="font-weight:600;color:#dc2626;"><?= formatPrice($c['tong_chi_tieu']) ?></td>
                        <td style="font-size:12.5px;color:#8a96b8;"><?= date('d/m/Y', strtotime($c['ngay_tao'])) ?></td>
                        <td>
                            <span class="badge-status <?= $c['trang_thai'] ?>">
                                <?= $c['trang_thai'] === 'active' ? 'Hoạt động' : 'Bị khóa' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= SITE_URL ?>/admin/customers/toggle?id=<?= $c['id'] ?>"
                               class="admin-action-btn <?= $c['trang_thai'] === 'active' ? 'delete' : 'edit' ?>"
                               title="<?= $c['trang_thai'] === 'active' ? 'Khóa tài khoản' : 'Mở khóa' ?>"
                               onclick="return confirm('<?= $c['trang_thai'] === 'active' ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?' ?>')">
                                <i class="fas fa-<?= $c['trang_thai'] === 'active' ? 'lock' : 'unlock' ?>"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="9" class="text-center py-5" style="color:#8a96b8;">
                        <i class="fas fa-users d-block mb-2" style="font-size:34px;opacity:.35;"></i>
                        Không có khách hàng nào
                    </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
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
</body></html>
