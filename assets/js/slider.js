/* ============================================================
   TechStore — slider.js
   ============================================================ */
(function(){
'use strict';


/* ── Featured Tabs ── */
var tabsEl  = document.getElementById('featuredTabs');
var featGrid = document.getElementById('featuredGrid');
if(tabsEl && featGrid){
    tabsEl.addEventListener('click', function(e){
        var btn = e.target.closest('.tab-btn');
        if(!btn) return;
        tabsEl.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        var tab = btn.dataset.tab;
        featGrid.querySelectorAll('.featured-item').forEach(function(item){
            item.style.display = (tab==='all' || item.dataset.cat===tab) ? '' : 'none';
        });
    });
}

/* ── Flash Countdown ── */
var cdEl = document.getElementById('flashCountdown');
if(cdEl){
    var endTs = cdEl.dataset.end ? new Date(cdEl.dataset.end).getTime() : 0;
    function tick(){
        var diff = endTs - Date.now();
        if(diff <= 0){ clearInterval(cdTimer); return; }
        var h=Math.floor(diff/3600000), m=Math.floor((diff%3600000)/60000), s=Math.floor((diff%60000)/1000);
        var hE=document.getElementById('cd-h'), mE=document.getElementById('cd-m'), sE=document.getElementById('cd-s');
        if(hE) hE.textContent = ('0'+h).slice(-2);
        if(mE) mE.textContent = ('0'+m).slice(-2);
        if(sE) sE.textContent = ('0'+s).slice(-2);
    }
    tick();
    var cdTimer = setInterval(tick, 1000);
}

/* ── Scroll to Top ── */
var stBtn = document.getElementById('scrollTopBtn');
if(stBtn){
    window.addEventListener('scroll', function(){ stBtn.classList.toggle('show', window.scrollY>400); });
    stBtn.addEventListener('click', function(){ window.scrollTo({top:0,behavior:'smooth'}); });
}

/* ── Search Autocomplete ── */
var si = document.getElementById('searchInput');
var sd = document.getElementById('searchDropdown');
if(si && sd && window.SITE_URL){
    var t;
    si.addEventListener('input', function(){
        clearTimeout(t);
        var q = this.value.trim();
        if(q.length < 2){ sd.classList.remove('show'); return; }
        t = setTimeout(function(){
            fetch(window.SITE_URL + '/products/search-suggest?q=' + encodeURIComponent(q))
                .then(function(r){ return r.ok ? r.json() : []; })
                .then(function(items){
                    if(!items || !items.length){ sd.classList.remove('show'); return; }
                    sd.innerHTML = items.map(function(p){
                        return '<div class="search-result-item" onclick="location.href=\''+window.SITE_URL+'/product/'+p.slug+'\'">'
                            +'<img src="'+(p.thumbnail||'')+'" onerror="this.style.display=\'none\'" alt="">'
                            +'<div class="info"><div class="name">'+p.name+'</div>'
                            +'<div class="price">'+(p.price_formatted||'')+'</div></div></div>';
                    }).join('');
                    sd.classList.add('show');
                }).catch(function(){ sd.classList.remove('show'); });
        }, 280);
    });
    document.addEventListener('click', function(e){
        if(!si.contains(e.target) && !sd.contains(e.target)) sd.classList.remove('show');
    });
}

/* ── Cart & Wishlist buttons (add to cart / wishlist) ── */
document.addEventListener('click', function(e){
    /* Add to cart */
    var cartBtn = e.target.closest('.btn-add-cart');
    if(cartBtn){
        e.preventDefault();
        var pid = cartBtn.dataset.id;
        if(!pid) return;
        cartBtn.disabled = true;
        fetch(window.SITE_URL + '/cart/add', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
            body:'product_id='+pid+'&quantity=1'
        }).then(function(r){ return r.json(); }).then(function(d){
            cartBtn.disabled = false;
            if(d.success){
                showToast('Đã thêm vào giỏ hàng!','success');
                var badge = document.getElementById('cartBadgeNav');
                if(badge && d.cart_count !== undefined) badge.textContent = d.cart_count;
            } else {
                showToast(d.message || 'Có lỗi xảy ra','error');
            }
        }).catch(function(){ cartBtn.disabled=false; });
    }

    /* Wishlist — handled globally in cart.js */
});

/* ── Toast helper — alias về window.showToast (định nghĩa trong cart.js) ── */
function showToast(msg, type){ if(window.showToast) window.showToast(msg, type); }

})();
