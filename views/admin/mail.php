<?php
if (empty($_SESSION['admin'])) { header('Location: ' . SITE_URL . '/admin/login'); exit; }
$admin      = $_SESSION['admin'];
$activePage = 'mail';
$users = $users ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Email — VQSTORE Admin</title>
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
            <h5>Gửi Email cho khách hàng</h5>
            <small>Email được lưu vào tài khoản khách &amp; gửi qua Gmail SMTP</small>
        </div>
    </header>

    <div class="admin-content">

        <!-- Page header -->
        <div class="admin-page-header">
            <div>
                <h4 class="admin-page-title">Email Marketing</h4>
                <div class="admin-page-breadcrumb">
                    <a href="<?= SITE_URL ?>/admin">Admin</a> / Email
                </div>
            </div>
        </div>

        <!-- Flash messages -->
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'danger') ?> alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'warning' ? 'exclamation-triangle' : 'exclamation-circle') ?> me-2"></i>
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- SMTP config warning if not set -->
        <?php if (!SMTP_PASS): ?>
        <div class="alert alert-warning d-flex align-items-start gap-3 mb-3">
            <i class="fas fa-exclamation-triangle mt-1" style="color:#d97706;font-size:18px;flex-shrink:0;"></i>
            <div>
                <strong>Chưa cấu hình App Password!</strong><br>
                Mở file <code>config/database.php</code> và điền App Password 16 ký tự vào
                <code>define('SMTP_PASS', '...')</code>.<br>
                Email vẫn được lưu vào tài khoản khách, nhưng sẽ không gửi được qua Gmail cho đến khi điền đủ.
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- LEFT: Compose form -->
            <div class="col-lg-5">
                <div class="admin-card">
                    <div style="padding:20px 24px 0;border-bottom:1px solid var(--adm-border);margin-bottom:20px;">
                        <h6 style="font-weight:700;color:var(--adm-text);margin:0 0 16px;">
                            <i class="fas fa-paper-plane me-2" style="color:#2563eb;"></i>Soạn Email mới
                        </h6>
                    </div>
                    <div style="padding:0 24px 24px;">
                        <form method="POST" action="<?= SITE_URL ?>/admin/mail" id="mailForm">

                            <div class="mb-3">
                                <label class="admin-form-label">Người nhận <span class="text-danger">*</span></label>
                                <select name="recipient_id" id="recipientId" class="admin-form-control" required>
                                    <option value="all">📢 Gửi tất cả khách hàng (<?= count($users) ?> người)</option>
                                    <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['full_name']) ?> — <?= htmlspecialchars($u['email']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="admin-form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="admin-form-control"
                                       placeholder="VD: Khuyến mãi tháng 6 — Giảm đến 30%!" required maxlength="200">
                            </div>

                            <div class="mb-3">
                                <label class="admin-form-label">Nội dung <span class="text-danger">*</span></label>
                                <textarea name="content" class="admin-form-control" rows="8"
                                          placeholder="Nhập nội dung email..." required></textarea>
                                <small style="color:var(--adm-text-muted);font-size:12px;">
                                    Nội dung sẽ được định dạng HTML và gửi kèm thương hiệu VQSTORE.
                                </small>
                            </div>

                            <!-- Preview send info -->
                            <div id="sendInfo" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;
                                 padding:10px 14px;margin-bottom:16px;font-size:13px;color:#0369a1;">
                                <i class="fas fa-info-circle me-1"></i>
                                <span id="sendInfoText">Sẽ gửi đến <strong><?= count($users) ?> khách hàng</strong></span>
                            </div>

                            <button type="submit" class="btn-admin btn-admin-primary w-100" id="sendBtn">
                                <i class="fas fa-paper-plane me-2"></i>Gửi Email
                            </button>
                        </form>
                    </div>
                </div>

                <!-- SMTP status card -->
                <div class="admin-card mt-3" style="padding:18px 20px;">
                    <h6 style="font-size:13px;font-weight:700;color:var(--adm-text);margin-bottom:12px;">
                        <i class="fas fa-cog me-2" style="color:#6b7280;"></i>Cấu hình SMTP
                    </h6>
                    <div style="font-size:12.5px;line-height:1.9;">
                        <div><span style="color:#6b7280;">Host:</span> <strong><?= SMTP_HOST ?>:<?= SMTP_PORT ?></strong></div>
                        <div><span style="color:#6b7280;">Tài khoản:</span> <strong><?= SMTP_USER ?></strong></div>
                        <div><span style="color:#6b7280;">App Password:</span>
                            <?php if (SMTP_PASS): ?>
                            <span style="color:#16a34a;font-weight:600;"><i class="fas fa-check-circle"></i> Đã cấu hình</span>
                            <?php else: ?>
                            <span style="color:#dc2626;font-weight:600;"><i class="fas fa-times-circle"></i> Chưa điền</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Sent history -->
            <div class="col-lg-7">
            </div>

        </div><!-- /row -->
    </div><!-- /admin-content -->
</div><!-- /admin-main -->
</div><!-- /admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mobile sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay').addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.remove('open');
    this.classList.remove('show');
});

// Update send info when recipient changes
var selectEl    = document.getElementById('recipientId');
var infoText    = document.getElementById('sendInfoText');
var totalUsers  = <?= count($users) ?>;

selectEl.addEventListener('change', function() {
    if (this.value === 'all') {
        infoText.innerHTML = 'Sẽ gửi đến <strong>' + totalUsers + ' khách hàng</strong>';
    } else {
        var text = this.options[this.selectedIndex].text;
        infoText.innerHTML = 'Sẽ gửi đến <strong>1 khách hàng</strong>: ' + text.split('—')[0].trim();
    }
});

// Confirm before sending to all
document.getElementById('mailForm').addEventListener('submit', function(e) {
    var isAll = document.getElementById('recipientId').value === 'all';
    if (isAll && totalUsers > 0) {
        if (!confirm('Bạn sắp gửi email đến TẤT CẢ ' + totalUsers + ' khách hàng.\nTiếp tục?')) {
            e.preventDefault();
        }
    }
});
</script>
</body>
</html>
