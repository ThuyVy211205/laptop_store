<?php
$price    = $product['gia'];
$sale     = $product['gia_khuyen_mai'] ?? null;
$final    = $sale ?: $price;
$discount = $sale ? calcDiscount($price, $sale) : 0;

$qvShowDiscount = $discount > 0;
$thumb        = !empty($images) ? imgUrl($images[0]['duong_dan_anh']) : imgUrl($product['hinh_thu_nho']);
$thumbFallback = imgUrl($product['hinh_thu_nho']);
?>
<div class="row g-0">
    <div class="col-md-5">
        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($product['ten']) ?>"
             style="width:100%;height:320px;object-fit:contain;padding:16px;background:var(--color-surface-2)"
             onerror="this.onerror=null;this.src='<?= htmlspecialchars($thumbFallback) ?>'">
    </div>
    <div class="col-md-7" style="padding:24px">
        <small style="color:var(--color-text-3);text-transform:uppercase;letter-spacing:.5px">
            <?= htmlspecialchars($product['ten_danh_muc'] ?? '') ?>
        </small>
        <h4 style="margin:8px 0 16px;color:var(--color-text)">
            <?= htmlspecialchars($product['ten']) ?>
        </h4>
        <div style="margin-bottom:16px">
            <span style="font-size:26px;font-weight:700;color:var(--color-red)"><?= formatPrice($final) ?></span>
            <?php if($sale && $sale < $price && $qvShowDiscount): ?>
            <span style="font-size:15px;color:#6d7fa8;text-decoration:line-through;margin-left:8px"><?= formatPrice($price) ?></span>
            <span style="background:var(--color-red);color:#fff;padding:2px 8px;border-radius:5px;font-size:12px;font-weight:700;margin-left:6px">-<?= $discount ?>%</span>
            <?php endif; ?>
        </div>
        <?php if(!empty($product['mo_ta'])): ?>
        <p style="color:var(--color-text-2);font-size:14px;margin-bottom:20px;line-height:1.6">
            <?= nl2br(htmlspecialchars($product['mo_ta'])) ?>
        </p>
        <?php endif; ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button class="btn btn-tech btn-add-cart" data-id="<?= $product['id'] ?>" style="flex:1;min-width:130px;padding:10px 22px;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:0!important;width:auto!important">
                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
            </button>
            <a href="<?= SITE_URL ?>/product/<?= htmlspecialchars($product['duong_dan']) ?>" class="btn btn-outline" style="flex:1;min-width:130px;display:flex;align-items:center;justify-content:center;gap:8px">
                Xem chi tiết <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php if(!empty($product['ton_kho']) && $product['ton_kho'] > 0): ?>
        <p style="color:var(--color-green);font-size:13px;margin-top:12px">
            <i class="fas fa-check-circle"></i> Còn hàng (<?= $product['ton_kho'] ?> sản phẩm)
        </p>
        <?php else: ?>
        <p style="color:var(--color-red);font-size:13px;margin-top:12px">
            <i class="fas fa-times-circle"></i> Hết hàng
        </p>
        <?php endif; ?>
    </div>
</div>
