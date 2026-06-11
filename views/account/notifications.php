<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/account">Tài khoản</a></li>
            <li class="breadcrumb-item active">Thông báo &amp; Email</li>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <div class="row g-4">

        <!-- Account Sidebar -->
        <div class="col-lg-3">
            <div class="account-sidebar">
                <ul class="account-nav">
                    <li><a href="<?= SITE_URL ?>/account"><i class="fas fa-user"></i> Hồ sơ</a></li>
                    <li><a href="<?= SITE_URL ?>/order"><i class="fas fa-box"></i> Đơn hàng</a></li>
                    <li><a href="<?= SITE_URL ?>/wishlist"><i class="fas fa-heart"></i> Yêu thích</a></li>
                    <li><a href="<?= SITE_URL ?>/account/notifications" class="active"><i class="fas fa-bell"></i> Thông báo</a></li>
                    <li><a href="<?= SITE_URL ?>/auth/logout" class="text-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-lg-9">
            <div class="checkout-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="checkout-card-title mb-0">
                        <i class="fas fa-bell me-2"></i>Thông báo &amp; Email
                        <?php
                        $unread = array_filter($notifications, fn($n) => !$n['is_read']);
                        if (count($unread) > 0):
                        ?>
                        <span class="badge bg-danger ms-2"><?= count($unread) ?></span>
                        <?php endif; ?>
                    </h5>
                    <?php if (count($unread) > 0): ?>
                    <a href="<?= SITE_URL ?>/account/notifications?read=all"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($notifications)): ?>
                <div class="text-center py-5" style="color:#9ca3af;">
                    <i class="fas fa-bell-slash" style="font-size:48px;opacity:.3;display:block;margin-bottom:16px;"></i>
                    <p>Bạn chưa có thông báo nào.</p>
                </div>
                <?php else: ?>
                <div class="notification-list">
                    <?php foreach ($notifications as $n): ?>
                    <?php
                        $typeIcon  = 'fas fa-bell';
                        $typeColor = '#6b7280';
                        if ($n['type'] === 'order')      { $typeIcon = 'fas fa-box';           $typeColor = '#2563eb'; }
                        if ($n['type'] === 'admin_mail') { $typeIcon = 'fas fa-envelope';      $typeColor = '#7c3aed'; }
                        if ($n['type'] === 'info')       { $typeIcon = 'fas fa-info-circle';   $typeColor = '#0891b2'; }
                        if ($n['type'] === 'success')    { $typeIcon = 'fas fa-check-circle';  $typeColor = '#16a34a'; }
                    ?>
                    <div class="notification-item <?= $n['is_read'] ? '' : 'unread' ?>"
                         data-id="<?= $n['id'] ?>"
                         style="display:flex;gap:14px;padding:16px 0;border-bottom:1px solid #f1f5f9;cursor:pointer;">

                        <div style="width:40px;height:40px;border-radius:50%;background:<?= $typeColor ?>22;
                                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="<?= $typeIcon ?>" style="color:<?= $typeColor ?>;font-size:16px;"></i>
                        </div>

                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                                <div style="font-weight:<?= $n['is_read'] ? '500' : '700' ?>;font-size:14px;color:#1e2a3b;line-height:1.4;">
                                    <?= htmlspecialchars($n['title']) ?>
                                    <?php if (!$n['is_read']): ?>
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                                          background:#2563eb;margin-left:6px;vertical-align:middle;"></span>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size:11.5px;color:#9ca3af;white-space:nowrap;flex-shrink:0;">
                                    <?= date('d/m H:i', strtotime($n['created_at'])) ?>
                                </span>
                            </div>
                            <div style="font-size:13px;color:#6b7280;margin-top:4px;line-height:1.6;">
                                <?= nl2br(htmlspecialchars($n['content'])) ?>
                            </div>
                            <?php if ($n['link']): ?>
                            <a href="<?= htmlspecialchars($n['link']) ?>"
                               style="font-size:12.5px;color:#2563eb;margin-top:6px;display:inline-block;">
                                Xem chi tiết <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<style>
.notification-item.unread { background: #f8faff; border-radius: 8px; padding: 16px 12px !important; margin-bottom: 2px; }
.notification-item:last-child { border-bottom: none !important; }
</style>

<script>
// Đánh dấu đã đọc khi nhấn vào thông báo
document.querySelectorAll('.notification-item').forEach(function(el) {
    el.addEventListener('click', function() {
        var id = this.dataset.id;
        this.classList.remove('unread');
        var dot = this.querySelector('[style*="border-radius:50%;background:#2563eb"]');
        if (dot) dot.remove();
        fetch('<?= SITE_URL ?>/account/markRead', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        });
    });
});
</script>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>
