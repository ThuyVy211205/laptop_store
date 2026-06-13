<?php $pageTitle = '404 — Không tìm thấy trang'; ?>
<?php include ROOT_PATH . '/views/bo_cuc/dau_trang.php'; ?>

<div class="container py-5 my-5">
    <div class="text-center py-5 my-5">
        <div class="error-404-wrap">
            <h1 class="error-404-number">404</h1>
            <h2 class="error-404-title">Oops! Trang không tồn tại</h2>
            <p class="error-404-text">
                Trang bạn đang tìm kiếm có thể đã bị xóa, đổi tên hoặc tạm thời không khả dụng.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
                <a href="<?= SITE_URL ?>" class="btn btn-tech">
                    <i class="fas fa-home me-2"></i>Về trang chủ
                </a>
                <a href="<?= SITE_URL ?>/products" class="btn btn-outline-tech">
                    <i class="fas fa-shopping-bag me-2"></i>Xem sản phẩm
                </a>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/views/bo_cuc/chan_trang.php'; ?>