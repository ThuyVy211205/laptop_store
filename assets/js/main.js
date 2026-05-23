/* ============================================================
   TechStore — main.js
   ============================================================ */
(function(){
'use strict';

/* ── Cart Sidebar ── */
var cartBtn     = document.getElementById('cartToggleBtn');
var cartSidebar = document.getElementById('cartSidebar');
var cartOverlay = document.getElementById('cartOverlay');
var closeCart   = document.getElementById('closeCartBtn');

function openCart(){
    if(!cartSidebar) return;
    cartSidebar.classList.add('show');
    if(cartOverlay) cartOverlay.classList.add('show');
    document.body.style.overflow = 'hidden';
    loadCartSidebar();
}
function closeCartFn(){
    if(!cartSidebar) return;
    cartSidebar.classList.remove('show');
    if(cartOverlay) cartOverlay.classList.remove('show');
    document.body.style.overflow = '';
}
function loadCartSidebar(){
    if(!window.SITE_URL) return;
    var body  = document.getElementById('cartSidebarBody');
    var badge = document.getElementById('cartBadgeSidebar');
    var total = document.getElementById('cartTotalAmount');
    if(body) body.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x" style="color:var(--color-primary)"></i></div>';
    fetch(window.SITE_URL + '/cart/sidebar', {
        headers:{'X-Requested-With':'XMLHttpRequest'}
    }).then(function(r){ return r.json(); }).then(function(d){
        if(!d.success) return;
        if(body)  body.innerHTML  = d.html;
        if(badge) badge.textContent = d.count;
        if(total) total.textContent = d.total;
    }).catch(function(){});
}

if(cartBtn)     cartBtn.addEventListener('click', openCart);
if(closeCart)   closeCart.addEventListener('click', closeCartFn);
if(cartOverlay) cartOverlay.addEventListener('click', closeCartFn);

/* ── Quick View ── */
document.addEventListener('click', function(e){
    var btn = e.target.closest('.quickview-btn');
    if(!btn || !window.SITE_URL) return;
    e.preventDefault();
    var pid = btn.dataset.id;
    if(!pid) return;
    var modalEl = document.getElementById('quickViewModal');
    var body    = document.getElementById('quickViewBody');
    if(!modalEl || !body) return;
    body.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x" style="color:var(--color-primary)"></i></div>';
    var bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
    fetch(window.SITE_URL + '/products/quickview/' + pid, {
        headers:{'X-Requested-With':'XMLHttpRequest'}
    }).then(function(r){ return r.ok ? r.text() : '<p class="text-center p-4">Không tải được sản phẩm</p>'; })
      .then(function(html){ body.innerHTML = html; })
      .catch(function(){ body.innerHTML = '<p class="text-center p-4">Không tải được sản phẩm</p>'; });
});

})();
