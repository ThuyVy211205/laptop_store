/* ============================================================
   TechStore — cart.js
   Add to cart (product cards) + cart sidebar item removal
   ============================================================ */
(function(){
'use strict';

/* ── Add to cart (product list / accessories pages) ── */
document.addEventListener('click', function(e){
    var btn = e.target.closest('.btn-add-cart');
    if(!btn || !window.SITE_URL) return;
    e.preventDefault();

    var pid = btn.dataset.id;
    if(!pid) return;

    /* Visual feedback while loading */
    var originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang thêm...';

    fetch(window.SITE_URL + '/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'product_id=' + pid + '&quantity=1'
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        btn.disabled = false;
        if(d.success){
            /* Update cart count badges */
            ['cartBadgeNav','cartBadgeSidebar'].forEach(function(id){
                var el = document.getElementById(id);
                if(el) el.textContent = d.cart_count;
            });
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Đã thêm!';
            btn.style.background = 'var(--color-green)';
            setTimeout(function(){
                btn.innerHTML = originalHtml;
                btn.style.background = '';
            }, 1800);
            if(window.showToast) showToast('success', d.message || 'Đã thêm vào giỏ hàng');
        } else {
            btn.innerHTML = originalHtml;
            if(window.showToast) showToast('error', d.message || 'Có lỗi xảy ra');
        }
    })
    .catch(function(){
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
});

/* ── Remove item from cart sidebar ── */
document.addEventListener('click', function(e){
    var btn = e.target.closest('.btn-remove-mini');
    if(!btn || !window.SITE_URL) return;
    var pid = btn.dataset.id;
    if(!pid) return;

    btn.disabled = true;
    fetch(window.SITE_URL + '/cart/remove', {
        method:'POST',
        headers:{
            'Content-Type':'application/x-www-form-urlencoded',
            'X-Requested-With':'XMLHttpRequest'
        },
        body:'product_id=' + pid
    }).then(function(r){ return r.json(); }).then(function(d){
        if(!d.success){ btn.disabled = false; return; }
        /* Update badges */
        ['cartBadgeNav','cartBadgeSidebar'].forEach(function(id){
            var el = document.getElementById(id);
            if(el) el.textContent = d.cart_count;
        });
        var total = document.getElementById('cartTotalAmount');
        if(total) total.textContent = d.total;
        /* Remove the row */
        var row = btn.closest('.cart-mini-item');
        if(row) row.remove();
        /* Empty state */
        var body = document.getElementById('cartSidebarBody');
        if(body && !body.querySelector('.cart-mini-item')){
            body.innerHTML = '<div class="text-center py-5" style="color:var(--color-text-3)">'
                + '<i class="fas fa-shopping-bag fa-3x mb-3 d-block" style="color:var(--color-border)"></i>'
                + '<p class="mb-0">Giỏ hàng của bạn đang trống</p>'
                + '<a href="' + window.SITE_URL + '/products" style="font-size:13px;color:var(--color-primary)">Xem sản phẩm</a>'
                + '</div>';
        }
    }).catch(function(){ btn.disabled = false; });
});

})();
