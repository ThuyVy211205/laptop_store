<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/account">Tài khoản</a></li>
            <li class="breadcrumb-item active">Đơn hàng</li>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <h1 class="page-heading"><i class="fas fa-box me-2"></i>Đơn hàng của tôi</h1>

    <!-- Status Tabs -->
    <div class="status-tabs mb-4">
        <?php
        $tabs = [
            ''          => 'Tất cả',
            'pending'   => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'shipping'  => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
        $currentStatus = $_GET['status'] ?? '';
        foreach ($tabs as $val => $label):
        ?>
        <a href="<?= SITE_URL ?>/order<?= $val ? '?status=' . $val : '' ?>"
           class="status-tab <?= $currentStatus === $val ? 'active' : '' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($orders)): ?>
    <div class="orders-list">
        <?php foreach ($orders as $order):
            $statusMap = [
                'pending'   => ['Chờ xác nhận','warning'],
                'confirmed' => ['Đã xác nhận','info'],
                'shipping'  => ['Đang giao','primary'],
                'delivered' => ['Đã giao','secondary'],
                'completed' => ['Hoàn thành','success'],
                'cancelled' => ['Đã hủy','danger'],
            ];
            $s = $statusMap[$order['status']] ?? ['?','secondary'];
        ?>
        <div class="order-card">
            <div class="order-card-header">
                <strong class="text-primary"><i class="fas fa-receipt me-2"></i>Mã đơn: <?= htmlspecialchars($order['order_code']) ?></strong>
                <small class="text-muted"><?= formatDateTime($order['created_at']) ?></small>
            </div>
            <div class="order-card-body">
                <?php if (!empty($order['items'])): ?>
                <?php foreach ($order['items'] as $item): ?>
                <div class="order-item-row">
                    <img src="<?= imgUrl($item['thumbnail']) ?>"
                         onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/no-image.webp'"
                         alt="<?= htmlspecialchars($item['product_name']) ?>">
                    <div class="order-item-info">
                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                        <small>SL: <?= $item['quantity'] ?> x <?= formatPrice($item['price']) ?></small>
                    </div>
                    <strong class="order-item-subtotal"><?= formatPrice($item['subtotal']) ?></strong>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
             </div>
            <div class="order-card-footer">
                <span class="badge bg-<?= $s[1] ?>"><?= $s[0] ?></span>
                <div class="d-flex gap-2">
                    <a href="<?= SITE_URL ?>/order/detail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-tech">
                        <i class="fas fa-eye me-1"></i>Chi tiết
                    </a>
                    <?php if ($order['status'] === 'pending'): ?>
                    <form method="POST" action="<?= SITE_URL ?>/order/cancel/<?= $order['id'] ?>" style="display:inline">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng?')">
                            <i class="fas fa-times me-1"></i>Hủy đơn
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state text-center py-5">
        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
        <h4>Chưa có đơn hàng nào</h4>
        <p class="text-muted">Hãy mua sắm để xem đơn hàng tại đây</p>
        <a href="<?= SITE_URL ?>/products" class="btn btn-tech">
            <i class="fas fa-shopping-bag me-2"></i>Mua sắm ngay
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>