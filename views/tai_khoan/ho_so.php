<?php include ROOT_PATH . '/views/bo_cuc/dau_trang.php'; ?>

<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Tài khoản</li>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="account-sidebar">
                <!-- Avatar Card -->
                <div class="account-user-card">
                    <div class="account-avatar">
                        <?php if (!empty($user['anh_dai_dien'])): ?>
                            <img src="<?= imgUrl($user['anh_dai_dien']) ?>" alt="">
                        <?php else: ?>
                            <span><?= mb_strtoupper(mb_substr($user['ho_ten'] ?? '', 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <h6 class="account-name"><?= htmlspecialchars($user['ho_ten'] ?? '') ?></h6>
                    <?php $rank = getUserRankInfo($user['hang'] ?? 'silver'); ?>
                    <span class="account-rank" style="color:<?= $rank['color'] ?>">
                        <?= $rank['icon'] ?> <?= $rank['name'] ?> Member
                    </span>
                </div>

                <!-- Nav -->
                <ul class="account-nav">
                    <li><a href="<?= SITE_URL ?>/account" class="active"><i class="fas fa-user"></i> Hồ sơ</a></li>
                    <li><a href="<?= SITE_URL ?>/order"><i class="fas fa-box"></i> Đơn hàng</a></li>
                    <li><a href="<?= SITE_URL ?>/wishlist"><i class="fas fa-heart"></i> Yêu thích</a></li>
                    <li><a href="<?= SITE_URL ?>/auth/logout" class="text-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </div>

        <!-- Main -->
        <div class="col-lg-9">
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card-user">
                        <i class="fas fa-shopping-bag stat-icon-user"></i>
                        <div>
                            <h3><?= $totalOrders ?></h3>
                            <small>Tổng đơn hàng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card-user">
                        <i class="fas fa-check-circle stat-icon-user text-success"></i>
                        <div>
                            <h3><?= $successOrders ?></h3>
                            <small>Đơn thành công</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card-user">
                        <i class="fas fa-coins stat-icon-user text-warning"></i>
                        <div>
                            <h3 style="font-size:1.2rem"><?= formatPrice($user['tong_chi_tieu'] ?? 0) ?></h3>
                            <small>Tổng chi tiêu</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="checkout-card">
                <h5 class="checkout-card-title"><i class="fas fa-user-edit me-2"></i>Thông tin cá nhân</h5>
                <form method="POST" action="<?= SITE_URL ?>/account/update" enctype="multipart/form-data">
                    <input type="hidden" name="type" value="profile">

                    <!-- Avatar upload -->
                    <div class="text-center mb-4">
                        <div class="avatar-upload-wrap">
                            <img id="avatarPreview"
                                 src="<?= !empty($user['anh_dai_dien']) ? imgUrl($user['anh_dai_dien']) : ASSETS_URL . '/images/no-avatar.png' ?>"
                                 alt="" class="avatar-preview"
                                 onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/images/no-avatar.png'">
                            <label class="btn-avatar-upload">
                                <i class="fas fa-camera"></i>
                                <input type="file" name="avatar" id="avatarInput" accept="image/*" hidden>
                            </label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="full_name" class="form-control form-tech" required
                                   value="<?= htmlspecialchars($user['ho_ten'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control form-tech" readonly
                                   value="<?= htmlspecialchars($user['thu_dien_tu'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" name="phone" id="profilePhone" class="form-control form-tech"
                                   value="<?= htmlspecialchars($user['so_dien_thoai'] ?? '') ?>">
                            <small class="text-danger mt-1" id="profilePhoneErr" style="display:none;"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="birthday" class="form-control form-tech"
                                   value="<?= htmlspecialchars($user['ngay_sinh'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gender" class="form-control form-tech">
                                <option value="">-- Chọn --</option>
                                <option value="male"   <?= ($user['gioi_tinh'] ?? '') === 'male'   ? 'selected' : '' ?>>Nam</option>
                                <option value="female" <?= ($user['gioi_tinh'] ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                                <option value="other"  <?= ($user['gioi_tinh'] ?? '') === 'other'  ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="address" class="form-control form-tech" rows="2"><?= htmlspecialchars($user['dia_chi'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-tech">
                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="checkout-card mt-4">
                <h5 class="checkout-card-title"><i class="fas fa-key me-2"></i>Đổi mật khẩu</h5>
                <form method="POST" action="<?= SITE_URL ?>/account/update">
                    <input type="hidden" name="type" value="password">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" name="current_password" class="form-control form-tech" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control form-tech" required minlength="6">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Xác nhận</label>
                            <input type="password" name="confirm_password" class="form-control form-tech" required minlength="6">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-tech">
                                <i class="fas fa-key me-2"></i>Đổi mật khẩu
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Recent Orders -->
            <?php if (!empty($recentOrders)): ?>
            <div class="mt-4">
                <h5 class="checkout-card-title d-flex justify-content-between align-items-center mb-3">
                    <span><i class="fas fa-history me-2"></i>Đơn hàng gần đây</span>
                    <a href="<?= SITE_URL ?>/order" class="btn btn-sm btn-outline-tech">Xem tất cả</a>
                </h5>
                <?php
                $statusMap = [
                    'pending'   => ['Chờ xác nhận','warning'],
                    'confirmed' => ['Đã xác nhận','info'],
                    'shipping'  => ['Đang giao','primary'],
                    'delivered' => ['Đã giao','secondary'],
                    'completed' => ['Hoàn thành','success'],
                    'cancelled' => ['Đã hủy','danger'],
                ];
                foreach ($recentOrders as $order):
                    $s = $statusMap[$order['trang_thai']] ?? ['?','secondary'];
                ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <strong class="text-primary"><i class="fas fa-receipt me-2"></i>Mã đơn: <?= htmlspecialchars($order['ma_don_hang']) ?></strong>
                        <small class="text-muted"><?= formatDateTime($order['ngay_tao']) ?></small>
                    </div>
                    <div class="order-card-body">
                        <?php if (!empty($order['items'])): ?>
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item-row">
                            <img src="<?= imgUrl($item['hinh_thu_nho']) ?>"
                                 onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/no-image.webp'"
                                 alt="<?= htmlspecialchars($item['ten_san_pham']) ?>">
                            <div class="order-item-info">
                                <strong><?= htmlspecialchars($item['ten_san_pham']) ?></strong>
                                <small>SL: <?= $item['so_luong'] ?> x <?= formatPrice($item['gia']) ?></small>
                            </div>
                            <strong class="order-item-subtotal"><?= formatPrice($item['tam_tinh']) ?></strong>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="order-card-footer">
                        <span class="badge bg-<?= $s[1] ?>"><?= $s[0] ?></span>
                        <a href="<?= SITE_URL ?>/order/detail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-tech">
                            <i class="fas fa-eye me-1"></i>Chi tiết
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Xem trước ảnh đại diện khi chọn file
document.getElementById('avatarInput')?.addEventListener('change', function(e) {
    if (e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = ev => document.getElementById('avatarPreview').src = ev.target.result;
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Kiểm tra định dạng số điện thoại Việt Nam
const PHONE_RE = /^(0|\+84)[3-9]\d{8}$/;
const profilePhone = document.getElementById('profilePhone');

function checkProfilePhone() {
    const val = profilePhone.value.trim();
    const err = document.getElementById('profilePhoneErr');
    if (val && !PHONE_RE.test(val)) {
        err.textContent = 'Số điện thoại không hợp lệ (ví dụ: 0901234567)';
        err.style.display = '';
        profilePhone.style.borderColor = '#dc2626';
        return false;
    }
    err.style.display = 'none';
    profilePhone.style.borderColor = '';
    return true;
}

profilePhone.addEventListener('blur', checkProfilePhone);
profilePhone.closest('form').addEventListener('submit', function(e) {
    if (!checkProfilePhone()) e.preventDefault();
});
</script>

<?php include ROOT_PATH . '/views/bo_cuc/chan_trang.php'; ?>
