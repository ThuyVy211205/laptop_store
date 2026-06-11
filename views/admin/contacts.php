<?php
if (empty($_SESSION['admin'])) {
    header('Location: ' . SITE_URL . '/admin/login');
    exit;
}
$admin    = $_SESSION['admin'];
$contacts = $contacts ?? [];
$counts   = $counts   ?? ['all'=>0,'new'=>0,'read'=>0,'replied'=>0];
$filter   = $_GET['status']  ?? '';
$search   = $_GET['search']  ?? '';

$statusLabel = ['new' => 'Mới', 'read' => 'Đã đọc', 'replied' => 'Đã phản hồi'];
$statusClass = ['new' => 'danger', 'read' => 'secondary', 'replied' => 'success'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin nhắn liên hệ — VQSTORE Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body>

<div class="admin-sidebar-overlay" id="sidebarOverlay"></div>
<div class="admin-wrapper">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= SITE_URL ?>/admin" class="admin-brand">
            <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.png?v=<?= filemtime(ROOT_PATH.'/assets/images/logo_VQSTORE.png') ?>" alt="VQStore" style="height:65px;width:auto;display:block;">
        </a>
        <nav class="admin-nav">
            <span class="admin-nav-label">Tổng quan</span>
            <a href="<?= SITE_URL ?>/admin" class="admin-nav-item"><i class="fas fa-th-large"></i> Dashboard</a>

            <span class="admin-nav-label">Quản lý</span>
            <a href="<?= SITE_URL ?>/admin/products"  class="admin-nav-item"><i class="fas fa-laptop"></i> Sản phẩm</a>
            <a href="<?= SITE_URL ?>/admin/orders"    class="admin-nav-item"><i class="fas fa-shopping-bag"></i> Đơn hàng</a>
            <a href="<?= SITE_URL ?>/admin/customers" class="admin-nav-item"><i class="fas fa-users"></i> Khách hàng</a>
            <a href="<?= SITE_URL ?>/admin/employees" class="admin-nav-item"><i class="fas fa-user-tie"></i> Nhân viên</a>
            <a href="<?= SITE_URL ?>/admin/vouchers"  class="admin-nav-item"><i class="fas fa-tag"></i> Voucher</a>
            <a href="<?= SITE_URL ?>/admin/contacts"  class="admin-nav-item active">
                <i class="fas fa-envelope"></i> Tin nhắn
                <?php if ($counts['new'] > 0): ?>
                <span class="badge-count"><?= $counts['new'] ?></span>
                <?php endif; ?>
            </a>

            <span class="admin-nav-label">Báo cáo</span>
            <a href="<?= SITE_URL ?>/admin/revenue" class="admin-nav-item"><i class="fas fa-chart-line"></i> Doanh thu</a>

            <div class="admin-nav-divider"></div>
            <a href="<?= SITE_URL ?>" class="admin-nav-item" target="_blank"><i class="fas fa-external-link-alt"></i> Xem trang web</a>
            <a href="<?= SITE_URL ?>/admin/logout" class="admin-nav-item"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <div class="admin-main">

        <!-- Topbar -->
        <header class="admin-topbar">
            <button class="admin-menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="admin-topbar-title">Tin nhắn liên hệ</div>
            <div class="admin-topbar-right">
                <span class="admin-user"><i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($admin['full_name']) ?></span>
            </div>
        </header>

        <div class="admin-content">

            <!-- Flash message -->
            <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Header + bộ lọc -->
            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                <div>
                    <h4 class="fw-700 mb-0">Tin nhắn liên hệ</h4>
                    <small class="text-muted">Quản lý và phản hồi tin nhắn từ khách hàng</small>
                </div>
                <div class="ms-auto d-flex gap-2 flex-wrap">
                    <!-- Tìm kiếm -->
                    <form method="GET" action="<?= SITE_URL ?>/admin/contacts" class="d-flex gap-2">
                        <?php if ($filter): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>">
                        <?php endif; ?>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Tên, email, chủ đề..." value="<?= htmlspecialchars($search) ?>" style="min-width:200px">
                        <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                        <?php if ($search): ?>
                        <a href="<?= SITE_URL ?>/admin/contacts<?= $filter ? '?status='.$filter : '' ?>" class="btn btn-sm btn-outline-secondary">Xóa</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Tabs trạng thái -->
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <a href="<?= SITE_URL ?>/admin/contacts<?= $search ? '?search='.urlencode($search) : '' ?>"
                   class="btn btn-sm <?= !$filter ? 'btn-dark' : 'btn-outline-secondary' ?>">
                    Tất cả <span class="badge bg-secondary ms-1"><?= $counts['all'] ?></span>
                </a>
                <a href="<?= SITE_URL ?>/admin/contacts?status=new<?= $search ? '&search='.urlencode($search) : '' ?>"
                   class="btn btn-sm <?= $filter==='new' ? 'btn-danger' : 'btn-outline-danger' ?>">
                    Mới <span class="badge bg-danger ms-1"><?= $counts['new'] ?></span>
                </a>
                <a href="<?= SITE_URL ?>/admin/contacts?status=read<?= $search ? '&search='.urlencode($search) : '' ?>"
                   class="btn btn-sm <?= $filter==='read' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                    Đã đọc <span class="badge bg-secondary ms-1"><?= $counts['read'] ?></span>
                </a>
                <a href="<?= SITE_URL ?>/admin/contacts?status=replied<?= $search ? '&search='.urlencode($search) : '' ?>"
                   class="btn btn-sm <?= $filter==='replied' ? 'btn-success' : 'btn-outline-success' ?>">
                    Đã phản hồi <span class="badge bg-success ms-1"><?= $counts['replied'] ?></span>
                </a>
            </div>

            <!-- Bảng tin nhắn -->
            <div class="admin-card">
                <?php if (empty($contacts)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                    <p>Không có tin nhắn nào.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Người gửi</th>
                                <th>Chủ đề</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th style="width:120px"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($contacts as $c): ?>
                            <tr class="<?= $c['status'] === 'new' ? 'table-warning' : '' ?>">
                                <td class="text-muted" style="font-size:12px">#<?= $c['id'] ?></td>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($c['name']) ?></div>
                                    <div class="text-muted" style="font-size:12px"><?= htmlspecialchars($c['email']) ?></div>
                                    <?php if ($c['phone']): ?>
                                    <div class="text-muted" style="font-size:12px"><i class="fas fa-phone fa-xs me-1"></i><?= htmlspecialchars($c['phone']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($c['order_id']): ?>
                                    <div style="font-size:11px;color:#6366f1">Đơn: <?= htmlspecialchars($c['order_id']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width:160px">
                                    <span class="text-truncate d-block" style="max-width:150px" title="<?= htmlspecialchars($c['subject'] ?? '') ?>">
                                        <?= htmlspecialchars($c['subject'] ?: '—') ?>
                                    </span>
                                </td>
                                <td style="max-width:220px">
                                    <span class="text-truncate d-block" style="max-width:210px" title="<?= htmlspecialchars($c['message']) ?>">
                                        <?= htmlspecialchars(mb_substr($c['message'], 0, 80)) ?><?= mb_strlen($c['message']) > 80 ? '…' : '' ?>
                                    </span>
                                    <?php if ($c['reply_content']): ?>
                                    <div class="mt-1" style="font-size:11px;color:#16a34a">
                                        <i class="fas fa-reply fa-xs me-1"></i>
                                        Phản hồi bởi <?= htmlspecialchars($c['replied_by']) ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $statusClass[$c['status']] ?? 'secondary' ?>">
                                        <?= $statusLabel[$c['status']] ?? $c['status'] ?>
                                    </span>
                                </td>
                                <td style="font-size:12px;color:#64748b;white-space:nowrap">
                                    <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Nút xem & phản hồi -->
                                        <button class="btn btn-sm btn-primary" title="Xem & phản hồi"
                                                onclick="openReply(<?= htmlspecialchars(json_encode($c)) ?>)">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        <?php if ($c['status'] === 'new'): ?>
                                        <a href="<?= SITE_URL ?>/admin/contacts/read?id=<?= $c['id'] ?>"
                                           class="btn btn-sm btn-outline-secondary" title="Đánh dấu đã đọc">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="<?= SITE_URL ?>/admin/contacts/delete?id=<?= $c['id'] ?>"
                                           class="btn btn-sm btn-outline-danger" title="Xóa"
                                           onclick="return confirm('Xóa tin nhắn này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /admin-content -->
    </div><!-- /admin-main -->
</div><!-- /admin-wrapper -->

<!-- Modal xem & phản hồi -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-envelope-open me-2"></i>Chi tiết tin nhắn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Thông tin khách -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label text-muted" style="font-size:12px">Người gửi</label>
                        <div id="rName" class="fw-600"></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label text-muted" style="font-size:12px">Email</label>
                        <div id="rEmail"></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label text-muted" style="font-size:12px">Điện thoại</label>
                        <div id="rPhone"></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label text-muted" style="font-size:12px">Mã đơn hàng</label>
                        <div id="rOrderId"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted" style="font-size:12px">Chủ đề</label>
                        <div id="rSubject"></div>
                    </div>
                </div>

                <!-- Nội dung tin nhắn -->
                <div class="p-3 rounded mb-3" style="background:#f8fafd;border:1px solid #e2e8f0">
                    <label class="form-label text-muted" style="font-size:12px">Nội dung</label>
                    <div id="rMessage" style="white-space:pre-wrap;line-height:1.7"></div>
                </div>

                <!-- Phản hồi cũ nếu có -->
                <div id="rReplyBlock" class="p-3 rounded mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;display:none">
                    <label class="form-label text-muted" style="font-size:12px">Phản hồi trước đó</label>
                    <div id="rReplyContent" style="white-space:pre-wrap;line-height:1.7"></div>
                    <div id="rRepliedBy" class="mt-1" style="font-size:12px;color:#16a34a"></div>
                </div>

                <!-- Form phản hồi -->
                <form method="POST" action="<?= SITE_URL ?>/admin/contacts/reply" id="replyForm">
                    <input type="hidden" name="id" id="rId">
                    <div class="mb-3">
                        <label class="form-label fw-600">Nội dung phản hồi <span class="text-danger">*</span></label>
                        <textarea name="reply_content" id="rReplyInput" class="form-control" rows="5"
                                  placeholder="Nhập nội dung phản hồi gửi đến khách hàng..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Gửi phản hồi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/js/admin.js"></script>
<script>
function openReply(data) {
    document.getElementById('rId').value       = data.id;
    document.getElementById('rName').textContent    = data.name;
    document.getElementById('rEmail').textContent   = data.email;
    document.getElementById('rPhone').textContent   = data.phone    || '—';
    document.getElementById('rOrderId').textContent = data.order_id || '—';
    document.getElementById('rSubject').textContent = data.subject  || '—';
    document.getElementById('rMessage').textContent = data.message;

    const replyBlock = document.getElementById('rReplyBlock');
    if (data.reply_content) {
        document.getElementById('rReplyContent').textContent = data.reply_content;
        document.getElementById('rRepliedBy').textContent    =
            'Phản hồi bởi ' + data.replied_by + ' — ' + (data.replied_at || '');
        replyBlock.style.display = 'block';
        document.getElementById('rReplyInput').value = '';
    } else {
        replyBlock.style.display = 'none';
        document.getElementById('rReplyInput').value = '';
    }

    new bootstrap.Modal(document.getElementById('replyModal')).show();
}
</script>
</body>
</html>
