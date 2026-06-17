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
$sort       = $sort       ?? '';
$limit      = $limit      ?? 0;

/* Build query-string helper */
function adminQs($extra = []) {
    global $search, $category_id, $status, $sort, $limit;
    $base = [];
    if ($search)     $base['search']      = $search;
    if ($category_id)$base['id_danh_muc'] = $category_id;
    if ($status)     $base['status']      = $status;
    if ($sort)       $base['sort']        = $sort;
    if ($limit)      $base['limit']       = $limit;
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
            <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.png?v=<?= filemtime(ROOT_PATH.'/assets/images/logo_VQSTORE.png') ?>" alt="VQStore" style="height:65px;width:auto;display:block;">
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
            <a href="<?= SITE_URL ?>/admin/contacts" class="admin-nav-item">
                <i class="fas fa-envelope"></i> Tin nhắn
                <?php
                $newContactCount = db()->fetch("SELECT COUNT(*) AS c FROM lien_he WHERE trang_thai='new'")['c'] ?? 0;
                if ($newContactCount > 0): ?>
                <span class="badge-count"><?= $newContactCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/admin/mail" class="admin-nav-item">
                <i class="fas fa-paper-plane"></i> Gửi Email
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
                <?= mb_strtoupper(mb_substr($admin['ho_ten'], 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
            <div>
                <div class="admin-sidebar-name"><?= htmlspecialchars($admin['ho_ten']) ?></div>
                <div class="admin-sidebar-role"><?= htmlspecialchars($admin['vai_tro'] ?? 'Admin') ?></div>
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
                <small>Tổng cộng <?= number_format($total) ?> sản phẩm
                    <?php if ($sort === 'bestseller'): ?>
                    <span style="color:#d97706;margin-left:4px;"><i class="fas fa-fire"></i> Bán chạy nhất</span>
                    <?php elseif ($sort === 'worstseller'): ?>
                    <span style="color:#94a3b8;margin-left:4px;"><i class="fas fa-snowflake"></i> Bán chậm nhất</span>
                    <?php endif; ?>
                </small>
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
                <button class="btn-admin btn-admin-primary" onclick="openAddModal()">
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
                                <?= htmlspecialchars($cat['ten']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="status" class="admin-select" onchange="this.form.submit()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Đang bán</option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Ẩn</option>
                        </select>

                        <?php if ($search || $category_id || $status): ?>
                        <a href="<?= SITE_URL ?>/admin/products<?= $sort ? adminQs([]) : '' ?>" class="btn-admin btn-admin-outline btn-admin-sm">
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
                                        <img src="<?= htmlspecialchars(imgUrl($p['hinh_thu_nho'] ?? '')) ?>"
                                             class="prod-thumb" alt=""
                                             onerror="this.onerror=null;this.src='<?= noImageUrl() ?>'">
                                        <div>
                                            <div style="font-weight:600;color:#1e2a3b;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                <?= htmlspecialchars($p['ten']) ?>
                                            </div>
                                            <div style="font-size:11.5px;color:#8a96b8;">
                                                <?= htmlspecialchars($p['thuong_hieu'] ?? '') ?>
                                                <?php if ($p['ma_san_pham']): ?>
                                                · SKU: <?= htmlspecialchars($p['ma_san_pham']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:#4a5568;"><?= htmlspecialchars($p['ten_danh_muc'] ?? '—') ?></td>
                                <td style="font-weight:600;"><?= formatPrice($p['gia']) ?></td>
                                <td>
                                    <?php if ($p['gia_khuyen_mai']): ?>
                                    <span style="color:#dc2626;font-weight:600;"><?= formatPrice($p['gia_khuyen_mai']) ?></span>
                                    <?php else: ?>
                                    <span style="color:#c0c8d8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['ton_kho'] <= 5 && $p['ton_kho'] > 0): ?>
                                    <span style="color:#d97706;font-weight:600;"><?= $p['ton_kho'] ?> <i class="fas fa-exclamation-triangle" style="font-size:11px;"></i></span>
                                    <?php elseif ($p['ton_kho'] == 0): ?>
                                    <span style="color:#dc2626;font-weight:600;">Hết hàng</span>
                                    <?php else: ?>
                                    <span><?= number_format($p['ton_kho']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($p['so_luong_ban'] ?? 0) ?></td>
                                <td>
                                    <span class="badge-status <?= $p['trang_thai'] ?>">
                                        <?= $p['trang_thai'] === 'active' ? 'Đang bán' : 'Ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="admin-action-btn edit" title="Sửa"
                                                onclick="openEditModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="admin-action-btn delete" title="Xóa"
                                                onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['ten'])) ?>')">
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

                <!--Phân trang-->
                <?php if ($total > 0 && !$limit): ?>
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

<!-- ===== THÊM / CHỈNH SỬA SẢN PHẨM ===== -->
<div class="modal fade admin-modal" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= SITE_URL ?>/admin/products" enctype="multipart/form-data" id="productForm" novalidate>
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id"     id="productId"   value="">
                <input type="hidden" name="current_thumbnail" id="currentThumbnail" value="">
                <div class="row g-3">
                    <!-- Lỗi xác thực -->
                    <div class="col-12" id="formError" style="display:none;">
                        <div class="alert alert-danger py-2 mb-0" id="formErrorMsg"></div>
                    </div>

                    <!-- Tên -->
                    <div class="col-12">
                        <label class="admin-form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="fName" class="admin-form-control"
                               placeholder="VD: Laptop Dell Inspiron 15 3520"
                               oninput="this.style.borderColor=''">
                    </div>

                    <!--Danh mục + Thương hiệu-->
                    <div class="col-md-6">
                        <label class="admin-form-label">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" id="fCategory" class="admin-form-control"
                                onchange="this.style.borderColor=''"
                                onfocus="this.style.borderColor=''">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['ten']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="admin-form-label">Thương hiệu</label>
                        <input type="text" name="brand" id="fBrand" class="admin-form-control"
                               placeholder="Dell, HP, Apple...">
                    </div>

                    <!-- Nhóm biến thể-->
                    <div class="col-12">
                        <label class="admin-form-label">Nhóm biến thể màu sắc
                            <span style="font-weight:400;color:#9ca3af;font-size:12px;">(để trống nếu không có biến thể — nhập cùng tên nhóm cho các sản phẩm cùng dòng)</span>
                        </label>
                        <input type="text" name="nhom_bien_the" id="fNhomBienThe" class="admin-form-control"
                               placeholder="VD: hp-pavilion-15-series">
                    </div>

                    <!-- Ảnh thu nhỏ — được đặt ở đây để luôn hiển thị mà không cần cuộn chuột -->
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

                    <!-- SKU -->
                    <div class="col-md-6">
                        <label class="admin-form-label">Mã SKU</label>
                        <input type="text" name="sku" id="fSku" class="admin-form-control"
                               placeholder="DELL-INS-15-3520">
                    </div>

                    <!-- Giá + Giá bán-->
                    <div class="col-md-3">
                        <label class="admin-form-label">Giá gốc (đ) <span class="text-danger">*</span></label>
                        <input type="number" name="price" id="fPrice" class="admin-form-control"
                               placeholder="Nhập giá" min="0" oninput="this.style.borderColor=''"
                               onchange="this.style.borderColor=''">
                    </div>
                    <div class="col-md-3">
                        <label class="admin-form-label">Giá KM (đ)</label>
                        <input type="number" name="sale_price" id="fSalePrice" class="admin-form-control"
                               placeholder="0" min="0">
                    </div>

                    <!-- Kho + Tình trạng -->
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

                    <!-- Mô tả -->
                    <div class="col-12">
                        <label class="admin-form-label">Mô tả</label>
                        <textarea name="description" id="fDescription" class="admin-form-control"
                                  placeholder="Mô tả sản phẩm..." rows="3"></textarea>
                    </div>

                    <!-- Trưng bày ảnh (tối đa 4) -->
                    <div class="col-12">
                        <label class="admin-form-label">Ảnh gallery (tối đa 4 ảnh)
                            <span style="font-weight:400;color:#9ca3af;font-size:12px;">— để trống ô nào thì giữ nguyên ảnh cũ</span>
                        </label>
                        <div class="row g-2" id="galleryInputs">
                            <?php for ($gi = 1; $gi <= 4; $gi++): ?>
                            <div class="col-6 col-md-3">
                                <div class="gallery-slot" id="gallerySlot<?= $gi ?>"
                                     style="border:2px dashed #e2e8f0;border-radius:8px;padding:8px;text-align:center;cursor:pointer;min-height:90px;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;"
                                     onclick="document.getElementById('galleryFile<?= $gi ?>').click()">
                                    <img id="galleryPreview<?= $gi ?>" src="" alt=""
                                         style="display:none;max-width:100%;max-height:70px;border-radius:4px;object-fit:cover;">
                                    <div id="galleryPlaceholder<?= $gi ?>" style="color:#9ca3af;font-size:12px;">
                                        <i class="fas fa-plus-circle" style="font-size:20px;margin-bottom:4px;display:block;"></i>
                                        Ảnh <?= $gi ?>
                                    </div>
                                </div>
                                <input type="file" name="gallery_<?= $gi ?>" id="galleryFile<?= $gi ?>"
                                       accept="image/*" style="display:none;"
                                       onchange="previewGallery(this, <?= $gi ?>)">
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-admin btn-admin-outline" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" form="productForm" class="btn-admin btn-admin-primary" id="submitBtn">
                    <i class="fas fa-save me-1"></i> Lưu sản phẩm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== XÓA XÁC NHẬN ===== -->
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
// Dọn sạch Bootstrap backdrop bị kẹt từ trạng thái trang trước
(function() {
    document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
})();

// ---- Thanh bên di động
document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay').addEventListener('click', function () {
    document.getElementById('adminSidebar').classList.remove('open');
    this.classList.remove('show');
});

var _productModal = null;
function getProductModal() {
    if (!_productModal) {
        _productModal = new bootstrap.Modal(document.getElementById('productModal'));
    }
    return _productModal;
}

// ---- Người trợ giúp thư viện----
function previewGallery(input, slot) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
        alert('Chỉ chấp nhận JPG, PNG, WEBP'); input.value = ''; return;
    }
    var reader = new FileReader();
    reader.onload = function(e) { showGalleryPreview(slot, e.target.result); };
    reader.readAsDataURL(file);
}
function showGalleryPreview(slot, src) {
    var preview     = document.getElementById('galleryPreview' + slot);
    var placeholder = document.getElementById('galleryPlaceholder' + slot);
    preview.src = src;
    preview.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
}
function clearGalleryPreviews() {
    for (var i = 1; i <= 4; i++) {
        var preview     = document.getElementById('galleryPreview' + i);
        var placeholder = document.getElementById('galleryPlaceholder' + i);
        var fileInput   = document.getElementById('galleryFile' + i);
        if (preview)     { preview.src = ''; preview.style.display = 'none'; }
        if (placeholder) placeholder.style.display = 'block';
        if (fileInput)   fileInput.value = '';
    }
}

// ----Người trợ giúp thư viện ----
function openAddModal() {
    document.getElementById('productModalTitle').textContent = 'Thêm sản phẩm mới';
    document.getElementById('formAction').value  = 'add';
    document.getElementById('productId').value   = '';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus me-1"></i> Thêm sản phẩm';
    document.getElementById('productForm').reset();
    document.getElementById('formError').style.display = 'none';
    clearImg();
    clearGalleryPreviews();
    getProductModal().show();
}

// ---- Chỉnh sửa phương thức ----
function openEditModal(p) {
    document.getElementById('productModalTitle').textContent = 'Chỉnh sửa sản phẩm';
    document.getElementById('formAction').value       = 'edit';
    document.getElementById('productId').value        = p.id;
    document.getElementById('fName').value            = p.ten            || '';
    document.getElementById('fCategory').value        = p.id_danh_muc   || '';
    document.getElementById('fBrand').value           = p.thuong_hieu   || '';
    document.getElementById('fSku').value             = p.ma_san_pham   || '';
    document.getElementById('fPrice').value           = p.gia           || '';
    document.getElementById('fSalePrice').value       = p.gia_khuyen_mai|| '';
    document.getElementById('fStock').value           = p.ton_kho         || 0;
    document.getElementById('fStatus').value          = p.trang_thai      || 'active';
    document.getElementById('fDescription').value     = p.mo_ta           || '';
    document.getElementById('fFeatured').checked      = p.la_noi_bat == 1;
    document.getElementById('currentThumbnail').value = p.hinh_thu_nho    || '';
    document.getElementById('fNhomBienThe').value     = p.nhom_bien_the   || '';
    document.getElementById('submitBtn').innerHTML    = '<i class="fas fa-save me-1"></i> Cập nhật';
    document.getElementById('formError').style.display = 'none';

    if (p.hinh_thu_nho) {
        const isAsset = p.hinh_thu_nho.startsWith('assets/');
        document.getElementById('imgPreview').src = isAsset
            ? '<?= SITE_URL ?>/' + p.hinh_thu_nho
            : '<?= UPLOAD_URL ?>/' + p.hinh_thu_nho;
        document.getElementById('imgPreviewWrap').style.display = 'flex';
        document.getElementById('imgPreviewWrap').style.alignItems = 'center';
    } else {
        clearImg();
    }

    // Load ảnh gallery hiện có
    clearGalleryPreviews();
    if (p.gallery && Array.isArray(p.gallery)) {
        p.gallery.forEach(function(img) {
            var slot = img.thu_tu;
            if (slot >= 1 && slot <= 4) {
                var src = '<?= UPLOAD_URL ?>/' + img.duong_dan_anh;
                showGalleryPreview(slot, src);
            }
        });
    }

    getProductModal().show();
}

// ---- Xóa xác nhận----
function confirmDelete(id, name) {
    document.getElementById('deleteProductName').textContent = '"' + name + '"';
    document.getElementById('deleteConfirmBtn').href =
        '<?= SITE_URL ?>/admin/products/delete?id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ---- JS xác nhận----
document.getElementById('productForm').addEventListener('submit', function (e) {
    var fName  = document.getElementById('fName');
    var fCat   = document.getElementById('fCategory');
    var fPrice = document.getElementById('fPrice');
    var name   = fName.value.trim();
    var catId  = fCat.value;
    var price  = parseFloat(fPrice.value);
    var msg    = '';
    var focusEl = null;

    // Xóa highlight lỗi trên các trường
    [fName, fCat, fPrice].forEach(function(el) {
        el.style.borderColor = '';
    });

    if (!name)              { msg = 'Vui lòng nhập tên sản phẩm.';  focusEl = fName; }
    else if (!catId)        { msg = 'Vui lòng chọn danh mục.';       focusEl = fCat; }
    else if (!(price > 0))  { msg = 'Giá gốc phải lớn hơn 0.';      focusEl = fPrice; }

    if (msg) {
        e.preventDefault();
        // Đánh dấu trường không hợp lệ
        if (focusEl) focusEl.style.borderColor = '#dc2626';
        // Hiển thị thông báo lỗi ở đầu form
        document.getElementById('formErrorMsg').textContent = msg;
        document.getElementById('formError').style.display = 'block';
        // Cuộn modal lên đầu để lỗi hiển thị
        var modalBody = document.querySelector('#productModal .modal-body');
        if (modalBody) modalBody.scrollTop = 0;
    }
});

// ---- Xem trước hình ảnh ----
function previewImg(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            alert('Chỉ chấp nhận ảnh JPG, PNG, WEBP');
            input.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Ảnh không được vượt quá 5MB');
            input.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('imgPreviewWrap').style.display = 'flex';
            document.getElementById('imgPreviewWrap').style.alignItems = 'center';
        };
        reader.readAsDataURL(file);
    }
}

function clearImg() {
    document.getElementById('thumbnailInput').value = '';
    document.getElementById('imgPreview').src       = '';
    document.getElementById('imgPreviewWrap').style.display = 'none';
    document.getElementById('currentThumbnail').value = '';
}

// Đặt lại form khi đóng modal
document.getElementById('productModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('productForm').reset();
    document.getElementById('formError').style.display = 'none';
    clearImg();
    document.getElementById('formAction').value = 'add';
    document.getElementById('productId').value  = '';
});
</script>
</body>
</html>
