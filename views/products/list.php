<?php include ROOT_PATH . '/views/layouts/header.php'; ?>

<!-- Breadcrumb -->
<nav class="breadcrumb-wrap">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a></li>
            <?php if (!empty($category)): ?>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/products">Sản phẩm</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($category['name']) ?></li>
            <?php else: ?>
            <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle ?? 'Sản phẩm') ?></li>
            <?php endif; ?>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <!-- Page Header -->
    <div class="listing-header">
        <h1 class="listing-title">
            <?php if (!empty($category)): ?>
                <i class="fas fa-<?= htmlspecialchars($category['icon'] ?? 'folder') ?>"></i>
                <?= htmlspecialchars($category['name']) ?>
            <?php else: ?>
                <i class="fas fa-shopping-bag"></i>
                <?= htmlspecialchars($pageTitle ?? 'Tất cả sản phẩm') ?>
            <?php endif; ?>
        </h1>
        <div class="listing-meta">
            Tìm thấy <strong class="text-warning"><?= number_format($totalCount ?? 0) ?></strong> sản phẩm
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <?php
            $currentCategoryId = $category['id'] ?? null;
            include ROOT_PATH . '/views/layouts/sidebar.php';
            ?>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <!-- Toolbar -->
            <div class="listing-toolbar">
                <div class="listing-info">
                    Hiển thị <strong><?= count($products) ?></strong> trong <?= number_format($totalCount) ?> sản phẩm
                </div>
                <div class="listing-sort-mobile d-lg-none">
                    <select class="form-select form-select-sm" onchange="window.location.href=this.value">
                        <?php
                        $sortOptions = [
                            'newest'      => 'Mới nhất',
                            'price_asc'   => 'Giá: Thấp → Cao',
                            'price_desc'  => 'Giá: Cao → Thấp',
                            'best_seller' => 'Bán chạy',
                            'rating'      => 'Đánh giá cao',
                        ];
                        $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
                        $params  = $_GET;
                        $selectedSort = $_GET['sort'] ?? 'newest';
                        foreach ($sortOptions as $val => $label):
                            $params['sort'] = $val;
                            $url = $baseUrl . '?' . http_build_query($params);
                        ?>
                        <option value="<?= $url ?>" <?= $selectedSort === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Products -->
            <?php if (!empty($products)): ?>
            <div class="row g-3">
                <?php foreach ($products as $product): ?>
                <div class="col-lg-4 col-md-6 col-6">
                    <?php include __DIR__ . '/_product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                    $currentPage = (int)($_GET['page'] ?? 1);
                    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
                    $params  = $_GET;

                    // Previous
                    if ($currentPage > 1):
                        $params['page'] = $currentPage - 1;
                        $url = $baseUrl . '?' . http_build_query($params);
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $url ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php endif; ?>

                    <?php
                    // Show max 7 page numbers, with ellipsis logic
                    $start = max(1, $currentPage - 3);
                    $end   = min($totalPages, $currentPage + 3);
                    if ($start > 1) {
                        $params['page'] = 1;
                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?' . http_build_query($params) . '">1</a></li>';
                        if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }

                    for ($i = $start; $i <= $end; $i++):
                        $params['page'] = $i;
                        $url = $baseUrl . '?' . http_build_query($params);
                    ?>
                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $url ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php
                    if ($end < $totalPages) {
                        if ($end < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        $params['page'] = $totalPages;
                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?' . http_build_query($params) . '">' . $totalPages . '</a></li>';
                    }

                    // Next
                    if ($currentPage < $totalPages):
                        $params['page'] = $currentPage + 1;
                        $url = $baseUrl . '?' . http_build_query($params);
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $url ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>Không tìm thấy sản phẩm nào</h4>
                <p class="text-muted">Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                <a href="<?= SITE_URL ?>/products" class="btn btn-tech mt-2">
                    <i class="fas fa-redo me-2"></i>Xem tất cả sản phẩm
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$extraCss = ['product.css'];
include ROOT_PATH . '/views/layouts/footer.php';
?>