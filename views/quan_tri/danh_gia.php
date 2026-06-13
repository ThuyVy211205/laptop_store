<?php
if (empty($_SESSION['admin'])) { header('Location: ' . SITE_URL . '/admin/login'); exit; }
$admin = $_SESSION['admin']; $activePage = 'reviews';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá — VQStore Admin</title>
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
            <h5>Quản lý đánh giá</h5>
            <small>Tổng cộng <?= count($reviews ?? []) ?> đánh giá</small>
        </div>
    </header>
    <div class="admin-content">
        <div class="admin-page-header">
            <div>
                <h4 class="admin-page-title">Đánh giá</h4>
                <div class="admin-page-breadcrumb"><a href="<?= SITE_URL ?>/admin">Admin</a> / Đánh giá</div>
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
                        <input type="text" name="search" placeholder="Tìm theo tên, sản phẩm, nội dung..."
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    <select name="rating" class="admin-select" onchange="this.form.submit()">
                        <option value="">Tất cả sao</option>
                        <?php for ($r = 5; $r >= 1; $r--): ?>
                        <option value="<?= $r ?>" <?= ($_GET['rating'] ?? '') == $r ? 'selected' : '' ?>><?= $r ?> sao</option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr>
                        <th>Sản phẩm</th><th>Khách hàng</th><th>Đánh giá</th><th>Nội dung</th>
                        <th>Ngày</th><th>Thao tác</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!empty($reviews)): foreach ($reviews as $rv): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= htmlspecialchars(imgUrl($rv['hinh_thu_nho'] ?? '')) ?>"
                                     style="width:40px;height:40px;object-fit:contain;border-radius:6px;border:1px solid #e5e7eb;" alt=""
                                     onerror="this.onerror=null;this.src='<?= noImageUrl() ?>'">
                                <span style="font-size:12.5px;font-weight:600;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= htmlspecialchars($rv['ten_san_pham']) ?>
                                </span>
                            </div>
                        </td>
                        <td style="font-weight:600;"><?= htmlspecialchars($rv['user_name']) ?></td>
                        <td>
                            <span style="color:#f59e0b;">
                                <?= str_repeat('★', (int)$rv['diem_so']) ?><?= str_repeat('☆', 5-(int)$rv['diem_so']) ?>
                            </span>
                            <span style="font-size:12px;color:#8a96b8;margin-left:4px;"><?= $rv['diem_so'] ?>/5</span>
                        </td>
                        <td style="max-width:200px;font-size:12.5px;color:#4a5568;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($rv['noi_dung'] ?? '—') ?>
                        </td>
                        <td style="font-size:12px;color:#8a96b8;"><?= date('d/m/Y', strtotime($rv['ngay_tao'])) ?></td>
                        <td>
                            <a href="<?= SITE_URL ?>/admin/reviews/delete?id=<?= $rv['id'] ?>"
                               class="admin-action-btn delete" title="Xóa"
                               onclick="return confirm('Xóa đánh giá này?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center py-5" style="color:#8a96b8;">
                        <i class="fas fa-star d-block mb-2" style="font-size:34px;opacity:.35;"></i>
                        Không có đánh giá nào
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
