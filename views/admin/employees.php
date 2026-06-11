<?php
if (empty($_SESSION['admin'])) { header('Location: ' . SITE_URL . '/admin/login'); exit; }
$admin = $_SESSION['admin']; $activePage = 'employees';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên — VQStore Admin</title>
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
            <h5>Quản lý nhân viên</h5>
            <small>Tổng cộng <?= count($employees ?? []) ?> nhân viên</small>
        </div>
    </header>
    <div class="admin-content">
        <div class="admin-page-header">
            <div>
                <h4 class="admin-page-title">Nhân viên</h4>
                <div class="admin-page-breadcrumb"><a href="<?= SITE_URL ?>/admin">Admin</a> / Nhân viên</div>
            </div>
            <button class="btn-admin btn-admin-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Thêm nhân viên
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
                        <th>#</th><th>Nhân viên</th><th>SĐT</th><th>Vai trò</th>
                        <th>Ngày tạo</th><th>Trạng thái</th><th>Thao tác</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!empty($employees)): foreach ($employees as $e): ?>
                    <tr>
                        <td style="color:#8a96b8;font-size:12px;"><?= $e['id'] ?></td>
                        <td>
                            <div style="font-weight:600;"><?= htmlspecialchars($e['full_name']) ?></div>
                            <div style="font-size:11.5px;color:#8a96b8;"><?= htmlspecialchars($e['email']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($e['phone'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($e['role']) ?></td>
                        <td style="font-size:12.5px;color:#8a96b8;"><?= date('d/m/Y', strtotime($e['created_at'])) ?></td>
                        <td>
                            <span class="badge-status <?= $e['status'] ?>">
                                <?= $e['status'] === 'active' ? 'Hoạt động' : 'Bị khóa' ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="admin-action-btn edit" title="Sửa"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($e), ENT_QUOTES) ?>)">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <a href="<?= SITE_URL ?>/admin/employees/delete?id=<?= $e['id'] ?>"
                                   class="admin-action-btn delete" title="Xóa"
                                   onclick="return confirm('Xóa nhân viên này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center py-5" style="color:#8a96b8;">
                        <i class="fas fa-user-tie d-block mb-2" style="font-size:34px;opacity:.35;"></i>
                        Chưa có nhân viên nào
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
<div class="modal fade admin-modal" id="empModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="empModalTitle">Thêm nhân viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/admin/employees">
                <input type="hidden" name="action" id="empAction" value="add">
                <input type="hidden" name="id" id="empId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="admin-form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="empName" class="admin-form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="admin-form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="empEmail" class="admin-form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="admin-form-label">SĐT</label>
                            <input type="text" name="phone" id="empPhone" class="admin-form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="admin-form-label">Vai trò</label>
                            <input type="text" name="role" id="empRole" class="admin-form-control" value="Nhân viên bán hàng">
                        </div>
                        <div class="col-md-6">
                            <label class="admin-form-label">Trạng thái</label>
                            <select name="status" id="empStatus" class="admin-form-control">
                                <option value="active">Hoạt động</option>
                                <option value="blocked">Bị khóa</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="admin-form-label" id="passLabel">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="empPassword" class="admin-form-control" placeholder="Để trống nếu không đổi">
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
    document.getElementById('empModalTitle').textContent = 'Thêm nhân viên';
    document.getElementById('empAction').value = 'add';
    document.getElementById('empId').value = '';
    document.getElementById('empName').value = '';
    document.getElementById('empEmail').value = '';
    document.getElementById('empPhone').value = '';
    document.getElementById('empRole').value = 'Nhân viên bán hàng';
    document.getElementById('empStatus').value = 'active';
    document.getElementById('empPassword').value = '';
    document.getElementById('passLabel').innerHTML = 'Mật khẩu <span class="text-danger">*</span>';
    new bootstrap.Modal(document.getElementById('empModal')).show();
}
function openEditModal(e) {
    document.getElementById('empModalTitle').textContent = 'Chỉnh sửa nhân viên';
    document.getElementById('empAction').value = 'edit';
    document.getElementById('empId').value = e.id;
    document.getElementById('empName').value = e.full_name || '';
    document.getElementById('empEmail').value = e.email || '';
    document.getElementById('empPhone').value = e.phone || '';
    document.getElementById('empRole').value = e.role || '';
    document.getElementById('empStatus').value = e.status || 'active';
    document.getElementById('empPassword').value = '';
    document.getElementById('passLabel').innerHTML = 'Mật khẩu <small class="text-muted">(để trống nếu không đổi)</small>';
    new bootstrap.Modal(document.getElementById('empModal')).show();
}
</script>
</body></html>
