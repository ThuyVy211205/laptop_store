<?php include ROOT_PATH . '/views/bo_cuc/dau_trang.php'; ?>

<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Lịch sử mua hàng</li>
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
                    <li><a href="<?= SITE_URL ?>/account"><i class="fas fa-user"></i> Hồ sơ</a></li>
                    <li><a href="<?= SITE_URL ?>/order"><i class="fas fa-box"></i> Đơn hàng</a></li>
                    <li><a href="<?= SITE_URL ?>/account/history" class="active"><i class="fas fa-history"></i> Lịch sử mua hàng</a></li>
                    <li><a href="<?= SITE_URL ?>/wishlist"><i class="fas fa-heart"></i> Yêu thích</a></li>
                    <li><a href="<?= SITE_URL ?>/auth/logout" class="text-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </div>

        <!-- Main -->
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1" style="font-weight:700;color:#1e2a3b;">
                        <i class="fas fa-history me-2" style="color:#6366f1;"></i>Lịch sử mua hàng
                    </h4>
                    <p class="mb-0" style="font-size:13px;color:#8a96b8;">
                        Tổng hợp tất cả sản phẩm bạn đã mua từ các đơn hàng hoàn thành
                    </p>
                </div>
                <span class="badge bg-light text-secondary border" style="font-size:13px;padding:8px 14px;">
                    <?= $collectionCount ?> sản phẩm · Tổng chi: <?= formatPrice($totalSpent) ?>
                </span>
            </div>

            <?php if (!empty($collection)): ?>
            <div class="checkout-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width:640px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width:56px;"></th>
                                <th>Sản phẩm</th>
                                <th>Đơn giá mua</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                                <th>Ngày mua</th>
                                <th class="text-end">Đơn hàng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collection as $item): ?>
                            <tr>
                                <td>
                                    <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($item['duong_dan'] ?? '#') ?>">
                                        <img src="<?= imgUrl($item['hinh_thu_nho']) ?>"
                                             alt="<?= htmlspecialchars($item['ten_san_pham']) ?>"
                                             style="width:44px;height:44px;object-fit:contain;border-radius:6px;border:1px solid #e5e7eb;"
                                             onerror="this.onerror=null;this.src='<?= noImageUrl() ?>'">
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($item['duong_dan'] ?? '#') ?>"
                                       style="color:#1e2a3b;font-weight:600;font-size:13.5px;text-decoration:none;">
                                        <?= htmlspecialchars($item['ten_san_pham']) ?>
                                    </a>
                                </td>
                                <td style="font-weight:600;"><?= formatPrice($item['gia_mua']) ?></td>
                                <td><?= $item['so_luong'] ?></td>
                                <td style="font-weight:700;color:#dc2626;"><?= formatPrice($item['tam_tinh']) ?></td>
                                <td style="font-size:12.5px;color:#8a96b8;"><?= formatDate($item['ngay_mua']) ?></td>
                                <td class="text-end">
                                    <a href="<?= SITE_URL ?>/order/detail/<?= $item['id_don_hang'] ?>"
                                       class="btn btn-sm btn-outline-tech">
                                        <?= htmlspecialchars($item['ma_don_hang']) ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="checkout-card text-center py-5">
                <i class="fas fa-box-open d-block mb-3" style="font-size:40px;color:#cbd5e1;"></i>
                <h5 style="color:#8a96b8;">Chưa có lịch sử mua hàng</h5>
                <p style="color:#8a96b8;font-size:14px;">Hãy mua sắm và hoàn thành đơn hàng đầu tiên của bạn!</p>
                <a href="<?= SITE_URL ?>/products" class="btn btn-tech mt-2">
                    <i class="fas fa-shopping-bag me-2"></i>Khám phá sản phẩm
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/views/bo_cuc/chan_trang.php'; ?>
