<?php
$extraCss = ['product.css'];
include ROOT_PATH . '/views/layouts/header.php';
?>

<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Yêu thích</li>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <h1 class="page-heading"><i class="fas fa-heart text-danger me-2"></i>Sản phẩm yêu thích</h1>

    <?php if (!empty($items)): ?>
    <div class="wishlist-grid" id="wishlistGrid">
        <?php foreach ($items as $product): ?>
        <?php $inWishlist = true; include ROOT_PATH . '/views/products/_product-card.php'; $inWishlist = false; ?>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state text-center py-5">
        <i class="fas fa-heart fa-3x text-muted mb-3"></i>
        <h4>Chưa có sản phẩm yêu thích nào</h4>
        <p class="text-muted">Lưu lại những sản phẩm bạn quan tâm để mua sau</p>
        <a href="<?= SITE_URL ?>/products" class="btn btn-tech">
            <i class="fas fa-shopping-bag me-2"></i>Khám phá sản phẩm
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>