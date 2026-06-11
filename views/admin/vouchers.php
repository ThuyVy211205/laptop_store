<?php
if (empty($_SESSION['admin'])) { header('Location: ' . SITE_URL . '/admin/login'); exit; }
$admin = $_SESSION['admin']; $activePage = 'vouchers';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý voucher — VQStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body>
<div class="admin-wrapper">
<?php include __DIR__ . '/_sidebar.php'; ?>
<div class="admin-main">
    <header class="admin-topbar">
        <button class="admin-mobile-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="admin-topbar-title">
            <h5>Quản lý Voucher</h5>
            <small>Tổng cộng <?= count($vouchers ?? []) ?> voucher</small>
        </div>
    </header>
    <div class="admin-content">
        <div class="admin-page-header">
            <div>
                <h4 class="admin-page-title">Voucher</h4>
                <div class="admin-page-breadcrumb"><a href="<?= SITE_URL ?>/admin">Admin</a> / Voucher</div>
            </div>
            <button class="btn-admin btn-admin-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Thêm voucher
            </button>
        </div>

        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-3">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr>
                        <th>Mã</th><th>Loại</th><th>Giá trị</th><th>Đơn tối thiểu</th>
                        <th>Đã dùng / SL</th><th>Hết hạn</th><th>Trạng thái</th><th>Thao tác</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!empty($vouchers)): foreach ($vouchers as $v): ?>
                    <?php
                        $expired = $v['expires_at'] && strtotime($v['expires_at']) < time();
                        $depleted = $v['quantity'] > 0 && $v['used_count'] >= $v['quantity'];
                    ?>
                    <tr>
                        <td><strong style="color:#2563eb;font-family:monospace;"><?= htmlspecialchars($v['code']) ?></strong></td>
                        <td><?= $v['type'] === 'percent' ? 'Phần trăm' : 'Cố định' ?></td>
                        <td style="font-weight:600;color:#10b981;">
                            <?= $v['type'] === 'percent' ? $v['value'].'%' : formatPrice($v['value']) ?>
                        </td>
                        <td><?= $v['min_order'] > 0 ? formatPrice($v['min_order']) : '—' ?></td>
                        <td>
                            <span style="color:<?= $depleted ? '#dc2626' : '#1e2a3b' ?>;">
                                <?= $v['used_count'] ?> / <?= $v['quantity'] ?: '∞' ?>
                            </span>
                        </td>
                        <td style="font-size:12.5px;color:<?= $expired ? '#dc2626' : '#8a96b8' ?>;">
                            <?= $v['expires_at'] ? date('d/m/Y H:i', strtotime($v['expires_at'])) : '—' ?>
                        </td>
                        <td>
                            <?php if ($expired): ?>
                            <span class="badge-status inactive">Hết hạn</span>
                            <?php elseif (!$v['is_active']): ?>
                            <span class="badge-status inactive">Tắt</span>
                            <?php else: ?>
                            <span class="badge-status active">Đang dùng</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="admin-action-btn edit" title="Sửa"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($v), ENT_QUOTES) ?>)">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <a href="<?= SITE_URL ?>/admin/vouchers/delete?id=<?= $v['id'] ?>"
                                   class="admin-action-btn delete" title="Xóa"
                                   onclick="return confirm('Xóa voucher <?= htmlspecialchars(addslashes($v['code'])) ?>?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center py-5" style="color:#8a96b8;">
                        <i class="fas fa-tag d-block mb-2" style="font-size:34px;opacity:.35;"></i>
                        Chưa có voucher nào
                    </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade admin-modal" id="voucherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="voucherModalTitle">Thêm voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/admin/vouchers">
                <input type="hidden" name="action" id="vAction" value="add">
                <input type="hidden" name="id" id="vId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="admin-form-label">Mã voucher <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="vCode" class="admin-form-control"
                                   placeholder="VD: SUMMER20" style="text-transform:uppercase;" required>
                        </div>
                        <div class="col-md-3">
                            <label class="admin-form-label">Loại</label>
                            <select name="type" id="vType" class="admin-form-control">
                                <option value="percent">Phần trăm (%)</option>
                                <option value="fixed">Cố định (đ)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="admin-form-label">Giá trị <span class="text-danger">*</span></label>
                            <input type="number" name="value" id="vValue" class="admin-form-control"
                                   placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="admin-form-label">Đơn tối thiểu (đ)</label>
                            <input type="number" name="min_order" id="vMinOrder" class="admin-form-control"
                                   placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="admin-form-label">Giảm tối đa (đ)</label>
                            <input type="number" name="max_discount" id="vMaxDiscount" class="admin-form-control"
                                   placeholder="Không giới hạn" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="admin-form-label">Số lượng (0 = ∞)</label>
                            <input type="number" name="quantity" id="vQuantity" class="admin-form-control"
                                   placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="admin-form-label">Hết hạn</label>
                            <input type="datetime-local" name="expires_at" id="vExpiresAt" class="admin-form-control">
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <label class="admin-switch">
                                <input type="checkbox" name="is_active" id="vIsActive" value="1" checked>
                                <span class="admin-switch-track"></span>
                                <span>Kích hoạt</span>
                            </label>
                        </div>
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
function openAddModal() {
    document.getElementById('voucherModalTitle').textContent = 'Thêm voucher';
    document.getElementById('vAction').value = 'add';
    document.getElementById('vId').value = '';
    document.getElementById('vCode').value = '';
    document.getElementById('vType').value = 'percent';
    document.getElementById('vValue').value = '';
    document.getElementById('vMinOrder').value = '0';
    document.getElementById('vMaxDiscount').value = '';
    document.getElementById('vQuantity').value = '0';
    document.getElementById('vExpiresAt').value = '';
    document.getElementById('vIsActive').checked = true;
    new bootstrap.Modal(document.getElementById('voucherModal')).show();
}
function openEditModal(v) {
    document.getElementById('voucherModalTitle').textContent = 'Sửa voucher';
    document.getElementById('vAction').value = 'edit';
    document.getElementById('vId').value = v.id;
    document.getElementById('vCode').value = v.code || '';
    document.getElementById('vType').value = v.type || 'percent';
    document.getElementById('vValue').value = v.value || '';
    document.getElementById('vMinOrder').value = v.min_order || '0';
    document.getElementById('vMaxDiscount').value = v.max_discount || '';
    document.getElementById('vQuantity').value = v.quantity || '0';
    document.getElementById('vExpiresAt').value = v.expires_at ? v.expires_at.substring(0,16) : '';
    document.getElementById('vIsActive').checked = v.is_active == 1;
    new bootstrap.Modal(document.getElementById('voucherModal')).show();
}
</script>
</body></html>
