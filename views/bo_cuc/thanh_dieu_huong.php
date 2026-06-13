<?php
/* Load categories for nav dropdowns */
require_once ROOT_PATH . '/models/DanhMuc.php';
$_navCatModel = new DanhMuc();
$_navCats     = $_navCatModel->getAll();

/* Group: laptop vs accessories */
$_laptopCats = array_values(array_filter($_navCats, function($c) {
    return stripos($c['ten'] . ' ' . $c['duong_dan'], 'laptop') !== false
        || stripos($c['ten'] . ' ' . $c['duong_dan'], 'macbook') !== false;
}));
$_accCats = array_values(array_filter($_navCats, function($c) {
    $n = strtolower($c['ten']); $s = strtolower($c['duong_dan'] ?? '');
    return preg_match('/chu[oô]t|tai[\s-]?nghe|b[àa]n[\s-]?ph[íi]m|ph[ụu][\s-]?ki[eê]n/', $n . ' ' . $s);
}));
?>

<!-- =========================================================
     MAIN NAVBAR
     ========================================================= -->
<nav class="navbar-main">
    <div class="container">
        <div class="navbar-row">

            <!-- Logo -->
            <a href="<?= SITE_URL ?>" class="nav-logo">
                <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.png?v=<?= filemtime(ROOT_PATH.'/assets/images/logo_VQSTORE.png') ?>" alt="Laptop Store" style="height:65px;width:auto;display:block;">
            </a>

            <!-- Search -->
            <div class="nav-search-wrap">
                <form action="<?= SITE_URL ?>/products" method="GET" class="nav-search-form" id="searchForm">
                    <input type="text" name="q" id="searchInput"
                           placeholder="Tìm kiếm laptop, phụ kiện..."
                           autocomplete="off"
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" aria-label="Tìm kiếm">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <div class="nav-search-dropdown" id="searchDropdown"></div>
            </div>

            <!-- Right side -->
            <div class="nav-right">

                <!-- Plain nav links (desktop) -->
                <a href="<?= SITE_URL ?>/products"
                   class="nav-link-plain" style="display:none" id="navSP">Sản phẩm</a>
                <a href="<?= SITE_URL ?>/products?type=phu-kien"
                   class="nav-link-plain" style="display:none" id="navPK">Phụ kiện</a>
                <a href="<?= SITE_URL ?>/khuyen-mai"
                   class="nav-link-plain" style="display:none" id="navKM">Khuyến mãi</a>
                <a href="<?= SITE_URL ?>/contact"
                   class="nav-link-plain" style="display:none" id="navLH">Liên hệ</a>

                <!-- Notification Bell (chỉ hiện khi đăng nhập) -->
                <?php if (isLoggedIn() && $currentUser): ?>
                <div class="nav-notif-wrap" id="notifWrap">
                    <button class="nav-icon-btn" id="notifToggleBtn" title="Thông báo" aria-label="Thông báo">
                        <i class="fas fa-bell"></i>
                        <span class="nav-badge nav-notif-badge d-none" id="notifBadge">0</span>
                    </button>
                    <div class="nav-notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <span class="fw-600">Thông báo</span>
                            <button class="notif-read-all" id="notifReadAll">Đánh dấu tất cả đã đọc</button>
                        </div>
                        <div class="notif-list" id="notifList">
                            <div class="notif-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>Chưa có thông báo nào</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Cart -->
                <button class="nav-icon-btn cart-toggle" id="cartToggleBtn" title="Giỏ hàng">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="nav-badge" id="cartBadgeNav"><?= $cartCount ?></span>
                </button>

                <!-- Account -->
                <?php if (isLoggedIn() && $currentUser): ?>
                <!-- Khách hàng đã đăng nhập -->
                <div class="nav-item">
                    <button class="nav-link-btn nav-user-btn">
                        <div class="nav-user-avatar">
                            <?php if (!empty($currentUser['anh_dai_dien'])): ?>
                                <img src="<?= imgUrl($currentUser['anh_dai_dien']) ?>" alt="">
                            <?php else: ?>
                                <span><?= mb_strtoupper(mb_substr($currentUser['ho_ten'], 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="d-none d-xl-inline" style="font-size:13px;max-width:88px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-left:4px">
                            <?= htmlspecialchars(mb_substr($currentUser['ho_ten'], 0, 10)) ?>
                        </span>
                        <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px"></i>
                    </button>
                    <div class="nav-dropdown nav-user-dropdown">
                        <div class="nav-dd-header">
                            <?php $rank = getUserRankInfo($currentUser['hang'] ?? 'silver'); ?>
                            <strong><?= htmlspecialchars($currentUser['ho_ten']) ?></strong>
                            <small style="color:<?= $rank['color'] ?>"><?= $rank['icon'] ?> <?= $rank['name'] ?></small>
                            <small><?= htmlspecialchars($currentUser['thu_dien_tu']) ?></small>
                        </div>
                        <a href="<?= SITE_URL ?>/account"><i class="fas fa-user"></i> Tài khoản</a>
                        <a href="<?= SITE_URL ?>/order"><i class="fas fa-box"></i> Đơn hàng</a>
                        <a href="<?= SITE_URL ?>/wishlist"><i class="fas fa-heart"></i> Yêu thích</a>
                        <hr>
                        <a href="<?= SITE_URL ?>/auth/logout" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </div>
                </div>

                <?php elseif (isAdminLoggedIn()): ?>
                <!-- Admin / Nhân viên đang xem trang -->
                <?php
                    $adminSession  = $_SESSION['admin'];
                    $adminInitial = mb_strtoupper(mb_substr($adminSession['ho_ten'], 0, 1));
                    $adminRole = $adminSession['vai_tro'] ?? 'admin';
                    $isSuperAdmin  = in_array($adminRole, ['super_admin', 'admin']);
                    $roleLabel     = $isSuperAdmin ? 'Quản trị viên' : $adminRole;
                    $roleColor     = $isSuperAdmin ? '#6366f1' : '#f59e0b';
                    $roleIcon      = $isSuperAdmin ? '🛡️' : '👤';
                    $avatarBg      = $isSuperAdmin ? 'linear-gradient(135deg,#6366f1,#2563eb)' : 'linear-gradient(135deg,#f59e0b,#ef4444)';
                ?>
                <div class="nav-item">
                    <button class="nav-link-btn nav-user-btn">
                        <div class="nav-user-avatar" style="background:<?= $avatarBg ?>">
                            <span><?= $adminInitial ?></span>
                        </div>
                        <span class="d-none d-xl-inline" style="font-size:13px;max-width:88px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-left:4px">
                            <?= htmlspecialchars(mb_substr($adminSession['ho_ten'], 0, 10)) ?>
                        </span>
                        <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px"></i>
                    </button>
                    <div class="nav-dropdown nav-user-dropdown">
                        <div class="nav-dd-header">
                            <strong><?= htmlspecialchars($adminSession['ho_ten']) ?></strong>
                            <small class="nav-role-badge" style="color:<?= $roleColor ?> !important;font-weight:600">
                                <?= $roleIcon ?> <?= htmlspecialchars($roleLabel) ?>
                            </small>
                            <small><?= htmlspecialchars($adminSession['thu_dien_tu']) ?></small>
                        </div>
                        <a href="<?= SITE_URL ?>/admin">
                            <i class="fas fa-tachometer-alt"></i> Bảng điều khiển
                        </a>
                        <a href="<?= SITE_URL ?>/admin/products">
                            <i class="fas fa-laptop"></i> Quản lý sản phẩm
                        </a>
                        <a href="<?= SITE_URL ?>/admin/orders">
                            <i class="fas fa-shopping-bag"></i> Quản lý đơn hàng
                        </a>
                        <hr>
                        <a href="<?= SITE_URL ?>/admin/logout" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </div>
                </div>

                <?php else: ?>
                <!-- Chưa đăng nhập -->
                <a href="<?= SITE_URL ?>/auth/login"
                   class="nav-account-link nav-account-link--sm d-none d-sm-flex">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Đăng nhập</span>
                </a>
                <a href="<?= SITE_URL ?>/auth/register"
                   class="nav-account-link nav-account-link--reg d-none d-md-flex">
                    <i class="fas fa-user-plus"></i>
                    <span>Đăng ký</span>
                </a>
                <?php endif; ?>

                <!-- Theme toggle -->
                <button class="nav-theme-btn" id="themeToggle" title="Chuyển sáng/tối">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>

                <!-- Mobile menu -->
                <button class="nav-mobile-btn d-lg-none" id="mobileToggle" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>

            </div><!-- /nav-right -->
        </div><!-- /navbar-row -->
    </div>
</nav>

<!-- Mobile Drawer -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="drawer-header">
        <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.png?v=<?= filemtime(ROOT_PATH.'/assets/images/logo_VQSTORE.png') ?>" alt="VQStore" style="height:65px;width:auto;display:block;">
        <button class="drawer-close" id="drawerClose"><i class="fas fa-times"></i></button>
    </div>
    <nav class="drawer-nav">
        <a href="<?= SITE_URL ?>/products">
            <i class="fas fa-laptop"></i> Sản phẩm Laptop
        </a>
        <a href="<?= SITE_URL ?>/products?type=phu-kien">
            <i class="fas fa-plug"></i> Phụ kiện
        </a>
        <a href="<?= SITE_URL ?>/khuyen-mai">
            <i class="fas fa-bolt"></i> Khuyến mãi
        </a>
        <a href="<?= SITE_URL ?>/contact">
            <i class="fas fa-phone-alt"></i> Liên hệ
        </a>
        <?php if (isLoggedIn() && $currentUser): ?>
        <a href="<?= SITE_URL ?>/account">
            <i class="fas fa-user-circle"></i> Xin chào, <?= htmlspecialchars(mb_substr($currentUser['ho_ten'], 0, 15)) ?>
        </a>
        <a href="<?= SITE_URL ?>/auth/logout" style="color:#ef4444">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
        <?php elseif (isAdminLoggedIn()): ?>
        <a href="<?= SITE_URL ?>/admin" style="color:var(--color-primary);font-weight:700">
            <i class="fas fa-tachometer-alt"></i> Bảng điều khiển
        </a>
        <a href="<?= SITE_URL ?>/admin/logout" style="color:#ef4444">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất Admin
        </a>
        <?php else: ?>
        <a href="<?= SITE_URL ?>/auth/login" style="color:var(--color-primary);font-weight:700">
            <i class="fas fa-user-circle"></i> Đăng nhập / Đăng ký
        </a>
        <?php endif; ?>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<script>
/* ── Theme toggle ── */
(function(){
    var html  = document.documentElement;
    var btn   = document.getElementById('themeToggle');
    var icon  = document.getElementById('themeIcon');
    var saved = localStorage.getItem('ts_theme');

    function setTheme(dark){
        html.classList.toggle('dark', dark);
        if(icon) icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
    }
    setTheme(saved === 'dark');

    if(btn) btn.addEventListener('click', function(){
        var isDark = html.classList.contains('dark');
        localStorage.setItem('ts_theme', isDark ? 'light' : 'dark');
        setTheme(!isDark);
    });

    /* Show desktop nav items */
    function showDesktop(){
        var isLg = window.innerWidth >= 992;
        ['navSP','navPK','navKM','navLH'].forEach(function(id){
            var el = document.getElementById(id);
            if(el) el.style.display = isLg ? '' : 'none';
        });
    }
    showDesktop();
    window.addEventListener('resize', showDesktop);

    /* Mobile drawer */
    var drawer  = document.getElementById('mobileDrawer');
    var overlay = document.getElementById('mobileOverlay');
    var toggleB = document.getElementById('mobileToggle');
    var closeB  = document.getElementById('drawerClose');

    function openDrawer(){ drawer.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow='hidden'; }
    function closeDrawer(){ drawer.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow=''; }

    if(toggleB) toggleB.addEventListener('click', openDrawer);
    if(closeB)  closeB.addEventListener('click', closeDrawer);
    if(overlay) overlay.addEventListener('click', closeDrawer);
})();

/* ── Notification Bell ── */
(function(){
    var btn      = document.getElementById('notifToggleBtn');
    var dropdown = document.getElementById('notifDropdown');
    var badge    = document.getElementById('notifBadge');
    var list     = document.getElementById('notifList');
    var readAll  = document.getElementById('notifReadAll');
    if (!btn) return;

    var isOpen   = false;
    var loaded   = false;

    function iconForType(type) {
        if (type === 'reply')   return '<i class="fas fa-reply notif-icon notif-icon--reply"></i>';
        if (type === 'voucher') return '<i class="fas fa-tag notif-icon notif-icon--voucher"></i>';
        if (type === 'order')   return '<i class="fas fa-box notif-icon notif-icon--order"></i>';
        return '<i class="fas fa-bell notif-icon notif-icon--info"></i>';
    }

    function timeAgo(dateStr) {
        var diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60)   return 'Vừa xong';
        if (diff < 3600) return Math.floor(diff/60) + ' phút trước';
        if (diff < 86400) return Math.floor(diff/3600) + ' giờ trước';
        return Math.floor(diff/86400) + ' ngày trước';
    }

    function resolveLink(link) {
        if (!link) return '';
        if (link.indexOf('http') === 0) return link;
        return window.SITE_URL + '/' + link.replace(/^\//, '');
    }

    function renderItems(items) {
        if (!items.length) {
            list.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>Chưa có thông báo nào</p></div>';
            return;
        }
        list.innerHTML = items.map(function(n) {
            var cls = n.da_doc == 1 ? 'notif-item notif-item--read' : 'notif-item notif-item--unread';
            var resolved = resolveLink(n.lien_ket);
            var href = resolved ? ' href="' + resolved + '"' : '';
            var tag  = resolved ? 'a' : 'div';
            return '<' + tag + ' class="' + cls + '"' + href + ' data-id="' + n.id + '">'
                 + iconForType(n.loai)
                 + '<div class="notif-body">'
                 + '<div class="notif-title">' + escHtml(n.tieu_de) + '</div>'
                 + (n.noi_dung ? '<div class="notif-content">' + escHtml(n.noi_dung) + '</div>' : '')
                 + '<div class="notif-time">' + timeAgo(n.ngay_tao) + '</div>'
                 + '</div></' + tag + '>';
        }).join('');

        list.querySelectorAll('.notif-item--unread').forEach(function(el) {
            el.addEventListener('click', function() {
                markRead(parseInt(el.dataset.id));
                el.classList.replace('notif-item--unread', 'notif-item--read');
            });
        });
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function loadNotifications() {
        fetch(window.SITE_URL + '/api/notifications')
            .then(function(r){ return r.json(); })
            .then(function(data) {
                updateBadge(data.chua_doc);
                renderItems(data.items || []);
                loaded = true;
            })
            .catch(function(){});
    }

    function markRead(id) {
        fetch(window.SITE_URL + '/api/notifications/read', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        }).then(function(r){ return r.json(); })
          .then(function(d){ updateBadge(d.chua_doc); });
    }

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        isOpen = !isOpen;
        dropdown.classList.toggle('open', isOpen);
        if (isOpen && !loaded) loadNotifications();
    });

    if (readAll) {
        readAll.addEventListener('click', function() {
            fetch(window.SITE_URL + '/api/notifications/read', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: 0})
            }).then(function(r){ return r.json(); })
              .then(function(d){
                  updateBadge(d.chua_doc);
                  list.querySelectorAll('.notif-item--unread').forEach(function(el){
                      el.classList.replace('notif-item--unread', 'notif-item--read');
                  });
              });
        });
    }

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== btn) {
            isOpen = false;
            dropdown.classList.remove('open');
        }
    });

    // Load badge count ngay khi trang tải
    loadNotifications();

    // Tự động kiểm tra thông báo mới mỗi 60 giây
    setInterval(function() {
        fetch(window.SITE_URL + '/api/notifications')
            .then(function(r){ return r.json(); })
            .then(function(data) {
                updateBadge(data.chua_doc);
                if (isOpen) renderItems(data.items || []);
            })
            .catch(function(){});
    }, 60000);
})();
</script>
