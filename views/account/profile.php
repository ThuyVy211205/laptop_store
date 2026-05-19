<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

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
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= imgUrl($user['avatar']) ?>" alt="">
                        <?php else: ?>
                            <span><?= mb_strtoupper(mb_substr($user['full_name'], 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <h6 class="account-name"><?= htmlspecialchars($user['full_name']) ?></h6>
                    <?php $rank = getUserRankInfo($user['rank'] ?? 'silver'); ?>
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
                            <h3 style="font-size:1.2rem"><?= formatPrice($user['total_spent']) ?></h3>
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
                                 src="<?= !empty($user['avatar']) ? imgUrl($user['avatar']) : ASSETS_URL . '/images/no-avatar.png' ?>"
                                 alt="" class="avatar-preview"
                                 onerror="this.src='<?= ASSETS_URL ?>/images/no-avatar.png'">
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
                                   value="<?= htmlspecialchars($user['full_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control form-tech" readonly
                                   value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control form-tech"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="birthday" class="form-control form-tech"
                                   value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gender" class="form-control form-tech">
                                <option value="">-- Chọn --</option>
                                <option value="male"   <?= ($user['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Nam</option>
                                <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                                <option value="other"  <?= ($user['gender'] ?? '') === 'other'  ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="address" class="form-control form-tech" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
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
            <div class="checkout-card mt-4">
                <h5 class="checkout-card-title d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history me-2"></i>Đơn hàng gần đây</span>
                    <a href="<?= SITE_URL ?>/order" class="btn btn-sm btn-outline-tech">Xem tất cả</a>
                </h5>
                <div class="table-responsive">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong class="text-primary"><?= htmlspecialchars($order['order_code']) ?></strong></td>
                                <td><small><?= formatDate($order['created_at']) ?></small></td>
                                <td><strong class="text-warning"><?= formatPrice($order['total_amount']) ?></strong></td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'pending'   => ['Chờ XN','warning'],
                                        'confirmed' => ['Xác nhận','info'],
                                        'shipping'  => ['Đang giao','primary'],
                                        'delivered' => ['Đã giao','secondary'],
                                        'completed' => ['Hoàn thành','success'],
                                        'cancelled' => ['Đã hủy','danger'],
                                    ];
                                    $s = $statusMap[$order['status']] ?? ['?','secondary'];
                                    ?>
                                    <span class="badge bg-<?= $s[1] ?>"><?= $s[0] ?></span>
                                </td>
                                <td>
                                    <a href="<?= SITE_URL ?>/order/detail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-tech">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Avatar preview
document.getElementById('avatarInput')?.addEventListener('change', function(e) {
    if (e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = ev => document.getElementById('avatarPreview').src = ev.target.result;
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>