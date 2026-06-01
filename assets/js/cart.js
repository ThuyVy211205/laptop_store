/* ============================================================
   TechStore — cart.js
   Add to cart + wishlist toggle + toast (loaded on every page)
   ============================================================ */

/* ── Global toast (dùng chung cho mọi trang) ── */
window.showToast = function(msg, type) {
    var c = document.getElementById('toastContainer');
    if(!c) return;
    var icons = { success:'fa-check-circle', error:'fa-exclamation-circle',
                  warning:'fa-exclamation-triangle', info:'fa-info-circle' };
    var t = document.createElement('div');
    t.className = 'toast-tech toast-' + (type || 'success');
    t.innerHTML = '<i class="toast-icon fas ' + (icons[type] || icons.success) + '"></i>'
                + '<span>' + msg + '</span>';
    c.appendChild(t);
    setTimeout(function(){
        t.style.opacity = '0';
        t.style.transition = 'opacity .4s';
        setTimeout(function(){ t.remove(); }, 400);
    }, 3000);
};

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
            showToast(d.message || 'Đã thêm vào giỏ hàng', 'success');
        } else {
            btn.innerHTML = originalHtml;
            showToast(d.message || 'Có lỗi xảy ra', 'error');
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

/* ── Wishlist toggle (product cards — mọi trang) ── */
document.addEventListener('click', function(e){
    var btn = e.target.closest('.wishlist-btn');
    /* Chặn double-fire: click vào <i> bên trong button vẫn bubble lên document
       dù button đang disabled → phải check thủ công */
    if(!btn || btn.disabled || btn.dataset.wlLoading === '1' || !window.SITE_URL) return;
    e.preventDefault();
    e.stopPropagation();

    if(!window.IS_LOGGED_IN){
        window.location.href = window.SITE_URL + '/auth/login';
        return;
    }

    var pid = btn.dataset.id;
    if(!pid) return;

    btn.disabled = true;
    btn.dataset.wlLoading = '1';
    fetch(window.SITE_URL + '/api/wishlist/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'product_id=' + pid
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        btn.disabled = false;
        btn.dataset.wlLoading = '0';
        if(d.success){
            var added = d.action === 'added';
            var icon  = btn.querySelector('i');
            if(icon) icon.className = added ? 'fas fa-heart' : 'far fa-heart';
            btn.classList.toggle('active', added);
            showToast(added ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích',
                      added ? 'success' : 'info');

            /* Nếu đang ở trang wishlist và vừa bỏ yêu thích → xóa card khỏi DOM */
            if(!added && btn.closest('.wishlist-grid')) {
                var card   = btn.closest('[class*="col-"]') || btn.closest('.product-card');
                if(card) {
                    card.style.transition = 'opacity .3s, transform .3s';
                    card.style.opacity    = '0';
                    card.style.transform  = 'scale(0.9)';
                    setTimeout(function(){
                        card.remove();
                        /* Nếu hết sản phẩm → hiển thị trạng thái rỗng */
                        var grid = document.getElementById('wishlistGrid');
                        if(grid && !grid.querySelector('.product-card')) {
                            grid.outerHTML =
                                '<div class="empty-state text-center py-5">'
                              + '<i class="fas fa-heart fa-3x text-muted mb-3 d-block"></i>'
                              + '<h4>Chưa có sản phẩm yêu thích nào</h4>'
                              + '<p class="text-muted">Lưu lại những sản phẩm bạn quan tâm để mua sau</p>'
                              + '<a href="' + window.SITE_URL + '/products" class="btn btn-tech">'
                              + '<i class="fas fa-shopping-bag me-2"></i>Khám phá sản phẩm</a>'
                              + '</div>';
                        }
                    }, 300);
                }
            }
        } else if(d.require_login){
            window.location.href = window.SITE_URL + '/auth/login';
        } else {
            showToast(d.message || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(function(){ btn.disabled = false; btn.dataset.wlLoading = '0'; });
});

})();
