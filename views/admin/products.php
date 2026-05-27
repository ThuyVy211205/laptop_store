<?php
if (empty($_SESSION['admin'])) {
    header('Location: ' . SITE_URL . '/admin/login');
    exit;
}

$admin      = $_SESSION['admin'];
$products   = $products   ?? [];
$categories = $categories ?? [];
$total      = $total      ?? 0;
$totalPages = $totalPages ?? 1;
$page       = $page       ?? 1;
$search     = $search     ?? '';
$category_id= $category_id?? '';
$status     = $status     ?? '';

/* Build query-string helper */
function adminQs($extra = []) {
    global $search, $category_id, $status, $page;
    $base = [];
    if ($search)     $base['search']      = $search;
    if ($category_id)$base['category_id'] = $category_id;
    if ($status)     $base['status']      = $status;
    $merged = array_merge($base, $extra);
    return $merged ? '?' . http_build_query($merged) : '';
}

$from = $total > 0 ? ($page - 1) * 15 + 1 : 0;
$to   = min($page * 15, $total);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm — VQSTORE Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body>

<div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-wrapper">

    <!-- ===== SIDEBAR ===== -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= SITE_URL ?>/admin" class="admin-brand">
            <div class="admin-brand-icon"><i class="fas fa-laptop"></i></div>
            <div class="admin-brand-text">TECH<span>STORE</span></div>
        </a>

        <nav class="admin-nav">
            <span class="admin-nav-label">Tổng quan</span>
            <a href="<?= SITE_URL ?>/admin" class="admin-nav-item">
                <i class="fas fa-th-large"></i> Dashboard
            </a>

            <span class="admin-nav-label">Quản lý</span>
            <a href="<?= SITE_URL ?>/admin/products" class="admin-nav-item active">
                <i class="fas fa-laptop"></i> Sản phẩm
            </a>
            <a href="<?= SITE_URL ?>/admin/orders" class="admin-nav-item">
                <i class="fas fa-shopping-bag"></i> Đơn hàng
            </a>
            <a href="<?= SITE_URL ?>/admin/customers" class="admin-nav-item">
                <i class="fas fa-users"></i> Khách hàng
            </a>
            <a href="<?= SITE_URL ?>/admin/employees" class="admin-nav-item">
                <i class="fas fa-user-tie"></i> Nhân viên
            </a>
            <a href="<?= SITE_URL ?>/admin/vouchers" class="admin-nav-item">
                <i class="fas fa-tag"></i> Voucher
            </a>
            <a href="<?= SITE_URL ?>/admin/reviews" class="admin-nav-item">
                <i class="fas fa-star"></i> Đánh giá
            </a>

            <span class="admin-nav-label">Báo cáo</span>
            <a href="<?= SITE_URL ?>/admin/revenue" class="admin-nav-item">
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

    <!-- ===== MAIN ===== -->
    <div class="admin-main">

        <header class="admin-topbar">
            <button class="admin-mobile-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="admin-topbar-title">
                <h5>Quản lý sản phẩm</h5>
                <small>Tổng cộng <?= number_format($total) ?> sản phẩm</small>
            </div>
            <div class="admin-topbar-right">
                <a href="<?= SITE_URL ?>/admin/orders?status=pending" class="admin-topbar-btn" title="Đơn hàng chờ">
                    <i class="fas fa-bell"></i>
                </a>
            </div>
        </header>

        <div class="admin-content">

            <!-- Page header -->
            <div class="admin-page-header">
                <div>
                    <h4 class="admin-page-title">Sản phẩm</h4>
                    <div class="admin-page-breadcrumb">
                        <a href="<?= SITE_URL ?>/admin">Admin</a>
                        <span> / Sản phẩm</span>
                    </div>
                </div>
                <button class="btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#productModal"
                        onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </button>
            </div>

            <!-- Flash message -->
            <?php
            $flash = getFlash();
            if ($flash):
            ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Main card -->
            <div class="admin-card">

                <!-- Toolbar / filters -->
                <div class="admin-toolbar">
                    <form method="GET" action="" class="d-flex gap-2 flex-wrap align-items-center" style="flex:1;">
                        <div class="admin-search-wrap">
                            <input type="text" name="search" placeholder="Tìm tên, SKU, thương hiệu..."
                                   value="<?= htmlspecialchars($search) ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>

                        <select name="category_id" class="admin-select" onchange="this.form.submit()">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="status" class="admin-select" onchange="this.form.submit()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Đang bán</option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Ẩn</option>
                        </select>

                        <?php if ($search || $category_id || $status): ?>
                        <a href="<?= SITE_URL ?>/admin/products" class="btn-admin btn-admin-outline btn-admin-sm">
                            <i class="fas fa-times"></i> Xoá bộ lọc
                        </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Table -->
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:48px;">#</th>
                                <th>Sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Giá KM</th>
                                <th>Tồn kho</th>
                                <th>Đã bán</th>
                                <th>Trạng thái</th>
                                <th style="width:110px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td style="color:#8a96b8;font-size:12px;"><?= $p['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($p['thumbnail']): ?>
                                        <img src="<?= UPLOAD_URL ?>/<?= htmlspecialchars($p['thumbnail']) ?>"
                                             class="prod-thumb" alt="">
                                        <?php else: ?>
                                        <div class="prod-thumb d-flex align-items-center justify-content-center"
                                             style="background:#f0f4fb;color:#c0c8d8;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight:600;color:#1e2a3b;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </div>
                                            <div style="font-size:11.5px;color:#8a96b8;">
                                                <?= htmlspecialchars($p['brand'] ?? '') ?>
                                                <?php if ($p['sku']): ?>
                                                · SKU: <?= htmlspecialchars($p['sku']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:#4a5568;"><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                                <td style="font-weight:600;"><?= formatPrice($p['price']) ?></td>
                                <td>
                                    <?php if ($p['sale_price']): ?>
                                    <span style="color:#dc2626;font-weight:600;"><?= formatPrice($p['sale_price']) ?></span>
                                    <?php else: ?>
                                    <span style="color:#c0c8d8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['stock'] <= 5 && $p['stock'] > 0): ?>
                                    <span style="color:#d97706;font-weight:600;"><?= $p['stock'] ?> <i class="fas fa-exclamation-triangle" style="font-size:11px;"></i></span>
                                    <?php elseif ($p['stock'] == 0): ?>
                                    <span style="color:#dc2626;font-weight:600;">Hết hàng</span>
                                    <?php else: ?>
                                    <span><?= number_format($p['stock']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($p['sold_quantity'] ?? 0) ?></td>
                                <td>
                                    <span class="badge-status <?= $p['status'] ?>">
                                        <?= $p['status'] === 'active' ? 'Đang bán' : 'Ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="admin-action-btn edit" title="Sửa"
                                                onclick="openEditModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="admin-action-btn delete" title="Xóa"
                                                onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5" style="color:#8a96b8;">
                                    <i class="fas fa-box-open d-block mb-2" style="font-size:34px;opacity:.35;"></i>
                                    <?= $search || $category_id || $status ? 'Không tìm thấy sản phẩm phù hợp' : 'Chưa có sản phẩm nào' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total > 0): ?>
                <div class="admin-pagination">
                    <div class="admin-pagination-info">
                        Hiển thị <?= $from ?>–<?= $to ?> trong <?= number_format($total) ?> sản phẩm
                    </div>
                    <div class="admin-page-list">
                        <a href="<?= SITE_URL ?>/admin/products<?= adminQs(['page' => $page - 1]) ?>"
                           class="admin-page-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-left" style="font-size:11px;"></i>
                        </a>
                        <?php
                        $start = max(1, $page - 2);
                        $end   = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                        <a href="<?= SITE_URL ?>/admin/products<?= adminQs(['page' => $i]) ?>"
                           class="admin-page-btn <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>
                        <a href="<?= SITE_URL ?>/admin/products<?= adminQs(['page' => $page + 1]) ?>"
                           class="admin-page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-right" style="font-size:11px;"></i>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /.admin-card -->
        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<!-- ===== ADD / EDIT PRODUCT MODAL ===== -->
<div class="modal fade admin-modal" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/admin/products" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id"     id="productId"   value="">
                <input type="hidden" name="current_thumbnail" id="currentThumbnail" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-12">
                            <label class="admin-form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="fName" class="admin-form-control"
                                   placeholder="VD: Laptop Dell Inspiron 15 3520" required>
                        </div>

                        <!-- Category + Brand -->
                        <div class="col-md-6">
                            <label class="admin-form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" id="fCategory" class="admin-form-control" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="admin-form-label">Thương hiệu</label>
                            <input type="text" name="brand" id="fBrand" class="admin-form-control"
                                   placeholder="Dell, HP, Apple...">
                        </div>

                        <!-- SKU -->
                        <div class="col-md-6">
                            <label class="admin-form-label">Mã SKU</label>
                            <input type="text" name="sku" id="fSku" class="admin-form-control"
                                   placeholder="DELL-INS-15-3520">
                        </div>

                        <!-- Price + Sale price -->
                        <div class="col-md-3">
                            <label class="admin-form-label">Giá gốc (đ) <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="fPrice" class="admin-form-control"
                                   placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="admin-form-label">Giá khuyến mãi (đ)</label>
                            <input type="number" name="sale_price" id="fSalePrice" class="admin-form-control"
                                   placeholder="0" min="0">
                        </div>

                        <!-- Stock + Status -->
                        <div class="col-md-4">
                            <label class="admin-form-label">Tồn kho</label>
                            <input type="number" name="stock" id="fStock" class="admin-form-control"
                                   placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="admin-form-label">Trạng thái</label>
                            <select name="status" id="fStatus" class="admin-form-control">
                                <option value="active">Đang bán</option>
                                <option value="inactive">Ẩn</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-2">
                            <label class="admin-switch">
                                <input type="checkbox" name="is_featured" id="fFeatured" value="1">
                                <span class="admin-switch-track"></span>
                                <span>Nổi bật</span>
                            </label>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="admin-form-label">Mô tả</label>
                            <textarea name="description" id="fDescription" class="admin-form-control"
                                      placeholder="Mô tả sản phẩm..."></textarea>
                        </div>

                        <!-- Thumbnail -->
                        <div class="col-12">
                            <label class="admin-form-label">Ảnh đại diện</label>
                            <div id="uploadZone" class="upload-zone" onclick="document.getElementById('thumbnailInput').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div style="font-weight:600;margin-bottom:4px;">Nhấn để chọn ảnh</div>
                                <div style="font-size:12px;">JPG, PNG, WEBP — tối đa 5MB</div>
                            </div>
                            <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*"
                                   style="display:none;" onchange="previewImg(this)">
                            <div id="imgPreviewWrap" class="mt-2" style="display:none;">
                                <img id="imgPreview" src="" alt="" class="img-preview">
                                <button type="button" class="btn btn-sm btn-light ms-2" onclick="clearImg()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin btn-admin-outline" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-admin btn-admin-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i> Lưu sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== DELETE CONFIRM MODAL ===== -->
<div class="modal fade admin-modal" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div style="width:60px;height:60px;background:rgba(220,38,38,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-trash" style="color:#dc2626;font-size:22px;"></i>
                </div>
                <p style="font-size:15px;font-weight:600;color:#1e2a3b;margin-bottom:8px;">
                    Bạn chắc chắn muốn xóa?
                </p>
                <p id="deleteProductName" style="font-size:13.5px;color:#8a96b8;"></p>
                <p style="font-size:13px;color:#dc2626;">Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-admin btn-admin-outline" data-bs-dismiss="modal">Hủy</button>
                <a id="deleteConfirmBtn" href="#" class="btn-admin btn-admin-danger">
                    <i class="fas fa-trash me-1"></i> Xóa sản phẩm
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ---- Mobile sidebar ----
document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay').addEventListener('click', function () {
    document.getElementById('adminSidebar').classList.remove('open');
    this.classList.remove('show');
});

// ---- Add modal ----
function openAddModal() {
    document.getElementById('productModalTitle').textContent = 'Thêm sản phẩm mới';
    document.getElementById('formAction').value  = 'add';
    document.getElementById('productId').value   = '';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus me-1"></i> Thêm sản phẩm';
    document.getElementById('productForm').reset();
    clearImg();
}

// ---- Edit modal ----
function openEditModal(p) {
    document.getElementById('productModalTitle').textContent = 'Chỉnh sửa sản phẩm';
    document.getElementById('formAction').value       = 'edit';
    document.getElementById('productId').value        = p.id;
    document.getElementById('fName').value            = p.name       || '';
    document.getElementById('fCategory').value        = p.category_id|| '';
    document.getElementById('fBrand').value           = p.brand      || '';
    document.getElementById('fSku').value             = p.sku        || '';
    document.getElementById('fPrice').value           = p.price      || '';
    document.getElementById('fSalePrice').value       = p.sale_price || '';
    document.getElementById('fStock').value           = p.stock      || 0;
    document.getElementById('fStatus').value          = p.status     || 'active';
    document.getElementById('fDescription').value     = p.description|| '';
    document.getElementById('fFeatured').checked      = p.is_featured == 1;
    document.getElementById('currentThumbnail').value = p.thumbnail  || '';
    document.getElementById('submitBtn').innerHTML    = '<i class="fas fa-save me-1"></i> Cập nhật';

    // Show current thumbnail
    if (p.thumbnail) {
        document.getElementById('imgPreview').src      = '<?= UPLOAD_URL ?>/' + p.thumbnail;
        document.getElementById('imgPreviewWrap').style.display = 'flex';
        document.getElementById('imgPreviewWrap').style.alignItems = 'center';
    } else {
        clearImg();
    }

    new bootstrap.Modal(document.getElementById('productModal')).show();
}

// ---- Delete confirm ----
function confirmDelete(id, name) {
    document.getElementById('deleteProductName').textContent = '"' + name + '"';
    document.getElementById('deleteConfirmBtn').href =
        '<?= SITE_URL ?>/admin/products/delete?id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ---- Image preview ----
function previewImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('imgPreviewWrap').style.display = 'flex';
            document.getElementById('imgPreviewWrap').style.alignItems = 'center';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearImg() {
    document.getElementById('thumbnailInput').value    = '';
    document.getElementById('imgPreview').src          = '';
    document.getElementById('imgPreviewWrap').style.display = 'none';
    document.getElementById('currentThumbnail').value  = '';
}

// Reset modal on close
document.getElementById('productModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('productForm').reset();
    clearImg();
    document.getElementById('formAction').value = 'add';
    document.getElementById('productId').value  = '';
});
</script>
</body>
</html>
