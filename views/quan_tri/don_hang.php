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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng — VQStore Admin</title>
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
            <h5>Quản lý đơn hàng</h5>
            <small>Tổng cộng <?= number_format($total ?? 0) ?> đơn hàng</small>
        </div>
    </header>
    <div class="admin-content">
        <div class="admin-page-header">
            <div>
                <h4 class="admin-page-title">Đơn hàng</h4>
                <div class="admin-page-breadcrumb"><a href="<?= SITE_URL ?>/admin">Admin</a> / Đơn hàng</div>
            </div>
        </div>

        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-3">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Status tabs -->
        <div class="d-flex gap-2 flex-wrap mb-3">
            <?php
            $allCount = array_sum($statusCounts ?? []);
            $curStatus = $_GET['status'] ?? '';
            ?>
            <a href="<?= SITE_URL ?>/admin/orders<?= $search ? '?search='.urlencode($search) : '' ?>"
               class="btn-admin btn-admin-sm <?= !$curStatus ? 'btn-admin-primary' : 'btn-admin-outline' ?>">
                Tất cả <span class="badge bg-secondary ms-1"><?= $allCount ?></span>
            </a>
            <?php foreach ($statusLabels as $s => $lbl): ?>
            <a href="<?= SITE_URL ?>/admin/orders?status=<?= $s ?><?= $search ? '&search='.urlencode($search) : '' ?>"
               class="btn-admin btn-admin-sm <?= $curStatus === $s ? 'btn-admin-primary' : 'btn-admin-outline' ?>">
                <?= $lbl ?>
                <?php if (!empty($statusCounts[$s])): ?>
                <span class="badge bg-secondary ms-1"><?= $statusCounts[$s] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="admin-card">
            <div class="admin-toolbar">
                <form method="GET" class="d-flex gap-2 flex-wrap" style="flex:1;">
                    <?php if ($curStatus): ?><input type="hidden" name="status" value="<?= htmlspecialchars($curStatus) ?>"><?php endif; ?>
                    <div class="admin-search-wrap">
                        <input type="text" name="search" placeholder="Tìm mã đơn, tên, SĐT..."
                               value="<?= htmlspecialchars($search ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    <?php if (!empty($search)): ?>
                    <a href="<?= SITE_URL ?>/admin/orders<?= $curStatus ? '?status='.$curStatus : '' ?>" class="btn-admin btn-admin-outline btn-admin-sm">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th style="width:120px;">Thao tác</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!empty($orders)): foreach ($orders as $o): ?>
                    <tr>
                        <td><strong style="color:#2563eb;"><?= htmlspecialchars($o['ma_don_hang']) ?></strong></td>
                        <td>
                            <div style="font-weight:600;"><?= htmlspecialchars($o['ten_giao_hang']) ?></div>
                            <div style="font-size:11.5px;color:#8a96b8;"><?= htmlspecialchars($o['sdt_giao_hang']) ?></div>
                        </td>
                        <td style="font-weight:700;color:#dc2626;"><?= formatPrice($o['tong_tien']) ?></td>
                        <td>
                            <?php $pm = ['cod'=>'COD','bank'=>'Chuyển khoản','momo'=>'MoMo','vnpay'=>'VNPay'];
                                  echo htmlspecialchars($pm[$o['phuong_thuc_thanh_toan']] ?? $o['phuong_thuc_thanh_toan']); ?>
                        </td>
                        <td style="font-size:12.5px;color:#8a96b8;"><?= date('d/m/Y H:i', strtotime($o['ngay_tao'])) ?></td>
                        <td>
                            <span class="badge-status" style="background:<?= $statusColors[$o['trang_thai']] ?>1a;color:<?= $statusColors[$o['trang_thai']] ?>;border-color:<?= $statusColors[$o['trang_thai']] ?>40;">
                                <?= $statusLabels[$o['trang_thai']] ?? $o['trang_thai'] ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="admin-action-btn edit" title="Cập nhật trạng thái"
                                        onclick="openStatusModal(<?= htmlspecialchars(json_encode($o), ENT_QUOTES) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="<?= SITE_URL ?>/order/detail/<?= $o['id'] ?>" target="_blank"
                                   class="admin-action-btn" title="Xem chi tiết" style="color:#6366f1;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center py-5" style="color:#8a96b8;">
                        <i class="fas fa-box-open d-block mb-2" style="font-size:34px;opacity:.35;"></i>
                        Không có đơn hàng nào
                    </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($totalPages) && $totalPages > 1):
                $curPage = (int)($_GET['page'] ?? 1);
            ?>
            <div class="admin-pagination">
                <div class="admin-pagination-info">Trang <?= $curPage ?> / <?= $totalPages ?></div>
                <div class="admin-page-list">
                    <?php for ($i = max(1,$curPage-2); $i <= min($totalPages,$curPage+2); $i++): ?>
                    <a href="?page=<?= $i ?><?= $curStatus ? '&status='.$curStatus : '' ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>"
                       class="admin-page-btn <?= $i === $curPage ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<!-- Update status modal -->
<div class="modal fade admin-modal" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật trạng thái đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/admin/orders/status">
                <input type="hidden" name="id" id="statusOrderId">
                <div class="modal-body">
                    <p style="font-size:13.5px;color:#8a96b8;" id="statusOrderCode"></p>
                    <label class="admin-form-label">Trạng thái mới</label>
                    <select name="status" id="statusSelect" class="admin-form-control" onchange="toggleCancelReason(this.value)">
                        <?php foreach ($statusLabels as $s => $lbl): ?>
                        <option value="<?= $s ?>"><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="cancelReasonWrap" class="mt-3" style="display:none;">
                        <label class="admin-form-label">Lý do hủy</label>
                        <textarea name="cancel_reason" class="admin-form-control" rows="2" placeholder="Nhập lý do..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin btn-admin-outline" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
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
function openStatusModal(o) {
    document.getElementById('statusOrderId').value = o.id;
    document.getElementById('statusOrderCode').textContent = 'Đơn #' + o.ma_don_hang + ' — ' + o.ten_giao_hang;
    document.getElementById('statusSelect').value = o.trang_thai;
    toggleCancelReason(o.trang_thai);
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}
function toggleCancelReason(val) {
    document.getElementById('cancelReasonWrap').style.display = val === 'cancelled' ? 'block' : 'none';
}
</script>
</body>
</html>
