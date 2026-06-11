<?php
/**
 * Thanh điều hướng Admin (Sidebar)
 * Nhận: $admin (mảng session admin), $activePage (chuỗi định danh trang hiện tại)
 */
$admin      = $admin      ?? $_SESSION['admin'] ?? ['full_name' => 'Admin', 'role' => 'Admin'];
$activePage = $activePage ?? '';
$newContactCount = db()->fetch("SELECT COUNT(*) AS c FROM lien_he WHERE status='new'")['c'] ?? 0;
$pendingOrders   = db()->fetch("SELECT COUNT(*) AS c FROM don_hang WHERE status='pending'")['c'] ?? 0;
?>
<div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <a href="<?= SITE_URL ?>/admin" class="admin-brand">
        <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.png?v=<?= filemtime(ROOT_PATH.'/assets/images/logo_VQSTORE.png') ?>" alt="VQStore" style="height:65px;width:auto;display:block;">
    </a>

    <nav class="admin-nav">
        <span class="admin-nav-label">Tổng quan</span>
        <a href="<?= SITE_URL ?>/admin" class="admin-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>

        <span class="admin-nav-label">Quản lý</span>
        <a href="<?= SITE_URL ?>/admin/products" class="admin-nav-item <?= $activePage === 'products' ? 'active' : '' ?>">
            <i class="fas fa-laptop"></i> Sản phẩm
        </a>
        <a href="<?= SITE_URL ?>/admin/orders" class="admin-nav-item <?= $activePage === 'orders' ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag"></i> Đơn hàng
            <?php if ($pendingOrders > 0): ?>
            <span class="badge-count"><?= $pendingOrders ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/customers" class="admin-nav-item <?= $activePage === 'customers' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Khách hàng
        </a>
        <a href="<?= SITE_URL ?>/admin/employees" class="admin-nav-item <?= $activePage === 'employees' ? 'active' : '' ?>">
            <i class="fas fa-user-tie"></i> Nhân viên
        </a>
        <a href="<?= SITE_URL ?>/admin/vouchers" class="admin-nav-item <?= $activePage === 'vouchers' ? 'active' : '' ?>">
            <i class="fas fa-tag"></i> Voucher
        </a>
        <a href="<?= SITE_URL ?>/admin/reviews" class="admin-nav-item <?= $activePage === 'reviews' ? 'active' : '' ?>">
            <i class="fas fa-star"></i> Đánh giá
        </a>
        <a href="<?= SITE_URL ?>/admin/contacts" class="admin-nav-item <?= $activePage === 'contacts' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Tin nhắn
            <?php if ($newContactCount > 0): ?>
            <span class="badge-count"><?= $newContactCount ?></span>
            <?php endif; ?>
        </a>
        <span class="admin-nav-label">Báo cáo</span>
        <a href="<?= SITE_URL ?>/admin/revenue" class="admin-nav-item <?= $activePage === 'revenue' ? 'active' : '' ?>">
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
