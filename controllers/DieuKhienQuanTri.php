<?php
/**
 * Điều Khiển Quản Trị
 * Dashboard, quản lý sản phẩm, đơn hàng, khách hàng, nhân viên, voucher,
 * đánh giá, liên hệ, báo cáo doanh thu, gửi email
 */

class DieuKhienQuanTri {

    // ── Bảo vệ tất cả phương thức admin ────────────────────────

    private function requireAdmin() {
        if (empty($_SESSION['admin'])) {
            header('Location: ' . SITE_URL . '/admin/login');
            exit;
        }
    }

    // ── Đăng nhập / Đăng xuất ──────────────────────────────────

    public function login() {
        if (!empty($_SESSION['admin'])) {
            header('Location: ' . SITE_URL . '/admin');
            exit;
        }

        // Auto-login trên localhost (chỉ dùng khi phát triển, bỏ qua khi vừa logout)
        if (empty($_GET['bye']) && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
            $admin = db()->fetch(
                "SELECT * FROM quan_tri_vien WHERE trang_thai = 'active' ORDER BY id ASC LIMIT 1"
            );
            if ($admin) {
                $_SESSION['admin'] = [
                    'id'          => $admin['id'],
                    'ho_ten'      => $admin['ho_ten'],
                    'thu_dien_tu' => $admin['thu_dien_tu'],
                    'vai_tro'     => $admin['vai_tro'],
                ];
                header('Location: ' . SITE_URL . '/admin');
                exit;
            }
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($email && $password) {
                $admin = db()->fetch(
                    "SELECT * FROM quan_tri_vien WHERE thu_dien_tu = ? AND trang_thai = 'active'",
                    [$email]
                );
                if ($admin && password_verify($password, $admin['mat_khau'])) {
                    $_SESSION['admin'] = [
                        'id'      => $admin['id'],
                        'ho_ten'  => $admin['ho_ten'],
                        'thu_dien_tu' => $admin['thu_dien_tu'],
                        'vai_tro' => $admin['vai_tro'],
                    ];
                    header('Location: ' . SITE_URL . '/admin');
                    exit;
                }
                $error = 'Email hoặc mật khẩu không đúng.';
            } else {
                $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
            }
        }

        $pageTitle = 'Đăng nhập quản trị';
        require_once ROOT_PATH . '/views/quan_tri/dang_nhap.php';
    }

    public function logout() {
        unset($_SESSION['admin']);
        header('Location: ' . SITE_URL);
        exit;
    }

    // ── Dashboard ──────────────────────────────────────────────

    public function dashboard() {
        $this->requireAdmin();

        $db = db();

        $stats = [
            'total_revenue'   => $db->fetch(
                "SELECT COALESCE(SUM(tong_tien),0) AS r FROM don_hang WHERE trang_thai IN ('completed','delivered')"
            )['r'] ?? 0,
            'tong_don_hang'   => $db->fetch("SELECT COUNT(*) AS c FROM don_hang")['c'] ?? 0,
            'total_products'  => $db->fetch("SELECT COUNT(*) AS c FROM san_pham WHERE trang_thai='active'")['c'] ?? 0,
            'total_customers' => $db->fetch("SELECT COUNT(*) AS c FROM nguoi_dung")['c'] ?? 0,
            'pending_orders'  => $db->fetch("SELECT COUNT(*) AS c FROM don_hang WHERE trang_thai='pending'")['c'] ?? 0,
        ];

        $recentOrders = $db->fetchAll(
            "SELECT id, ma_don_hang, ten_giao_hang, sdt_giao_hang, tong_tien,
                    phuong_thuc_thanh_toan, trang_thai_thanh_toan, trang_thai, ngay_tao
             FROM don_hang ORDER BY ngay_tao DESC LIMIT 10"
        );

        $topProducts = $db->fetchAll(
            "SELECT p.ten, p.so_luong_ban,
                    COALESCE(p.gia_khuyen_mai, p.gia) AS gia_cuoi
             FROM san_pham p
             WHERE p.trang_thai = 'active'
             ORDER BY p.so_luong_ban DESC
             LIMIT 5"
        );

        $year       = (int)date('Y');
        $rawRevenue = $db->fetchAll(
            "SELECT MONTH(ngay_tao) AS thang, COALESCE(SUM(tong_tien),0) AS tong
             FROM don_hang
             WHERE trang_thai IN ('completed','delivered') AND YEAR(ngay_tao) = ?
             GROUP BY MONTH(ngay_tao) ORDER BY MONTH(ngay_tao)",
            [$year]
        );
        $monthNames  = ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
        $revenueMap  = array_column($rawRevenue, 'tong', 'thang');
        $curMonth    = (int)date('n');
        $revenueData = [];
        for ($m = max(1, $curMonth - 5); $m <= $curMonth; $m++) {
            $revenueData[] = ['month' => $monthNames[$m-1], 'total' => $revenueMap[$m] ?? 0];
        }

        require_once ROOT_PATH . '/views/quan_tri/bang_dieu_khien.php';
    }

    // ── Sản phẩm ───────────────────────────────────────────────

    public function products($param = null) {
        $this->requireAdmin();

        require_once ROOT_PATH . '/models/SanPham.php';
        require_once ROOT_PATH . '/models/DanhMuc.php';
        $sanPhamModel = new SanPham();
        $danhMucModel = new DanhMuc();

        if ($param === 'delete' && isset($_GET['id'])) {
            $sanPhamModel->delete((int)$_GET['id']);
            setFlash('success', 'Đã xóa sản phẩm.');
            redirect('/admin/products');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $formAction  = $_POST['action'];
            $ten         = trim($_POST['name']          ?? '');
            $idDanhMuc   = (int)($_POST['category_id']  ?? 0);
            $thuongHieu  = trim($_POST['brand']         ?? '');
            $maSanPham   = trim($_POST['sku']           ?? '');
            $gia         = (float)($_POST['price']      ?? 0);
            $giaKM       = (float)($_POST['sale_price'] ?? 0);
            $tonKho      = (int)($_POST['stock']        ?? 0);
            $trangThai   = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
            $laNoBat     = isset($_POST['is_featured']) ? 1 : 0;
            $moTa        = trim($_POST['description']   ?? '');

            if (!$ten || !$idDanhMuc || $gia <= 0) {
                setFlash('error', 'Vui lòng điền đầy đủ tên, danh mục và giá sản phẩm.');
                redirect('/admin/products');
                return;
            }

            // Xử lý upload ảnh đại diện
            $hinhThuNho = trim($_POST['current_thumbnail'] ?? '');
            if (!empty($_FILES['thumbnail']['tmp_name']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $file    = $_FILES['thumbnail'];
                $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp']) && $file['size'] <= MAX_UPLOAD_SIZE) {
                    $filename = uniqid('sp_') . '.' . $ext;
                    $uploadDir = ROOT_PATH . '/tai_len/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
                    $hinhThuNho = $filename;
                }
            }

            $editId = ($formAction === 'edit') ? (int)($_POST['id'] ?? 0) : 0;

            // Tạo slug duy nhất từ tên sản phẩm
            $baseSlug = createSlug($ten);
            $slug     = $baseSlug;
            $counter  = 2;
            while (true) {
                $checkSlug = $editId
                    ? db()->fetch("SELECT id FROM san_pham WHERE duong_dan = ? AND id != ?", [$slug, $editId])
                    : db()->fetch("SELECT id FROM san_pham WHERE duong_dan = ?", [$slug]);
                if (!$checkSlug) break;
                $slug = $baseSlug . '-' . $counter++;
            }

            $nhomBienThe = trim($_POST['nhom_bien_the'] ?? '') ?: null;

            $data = [
                'ten'            => $ten,
                'duong_dan'      => $slug,
                'id_danh_muc'    => $idDanhMuc,
                'thuong_hieu'    => $thuongHieu ?: null,
                'nhom_bien_the'  => $nhomBienThe,
                'ma_san_pham'    => $maSanPham  ?: null,
                'gia'            => $gia,
                'gia_khuyen_mai' => $giaKM > 0 ? $giaKM : null,
                'ton_kho'        => $tonKho,
                'trang_thai'     => $trangThai,
                'la_noi_bat'     => $laNoBat,
                'mo_ta'          => $moTa ?: null,
                'hinh_thu_nho'   => $hinhThuNho ?: null,
            ];

            if ($formAction === 'edit' && $editId) {
                $sanPhamModel->update($editId, $data);
                $savedId = $editId;
                setFlash('success', 'Đã cập nhật sản phẩm thành công.');
            } else {
                $savedId = $sanPhamModel->create($data);
                setFlash('success', 'Đã thêm sản phẩm mới thành công.');
            }

            // Xử lý gallery ảnh (tối đa 4 ảnh)
            $uploadDir = ROOT_PATH . '/tai_len/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowedExt = ['jpg','jpeg','png','webp'];
            for ($i = 1; $i <= 4; $i++) {
                $key = 'gallery_' . $i;
                if (empty($_FILES[$key]['tmp_name']) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) continue;
                $file    = $_FILES[$key];
                $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt) || $file['size'] > MAX_UPLOAD_SIZE) continue;
                $filename = uniqid('gallery_') . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
                // Xóa ảnh cũ cùng vị trí (thu_tu = i) rồi insert mới
                db()->execute(
                    "DELETE FROM anh_san_pham WHERE id_san_pham = ? AND thu_tu = ?",
                    [$savedId, $i]
                );
                db()->insert('anh_san_pham', [
                    'id_san_pham'   => $savedId,
                    'duong_dan_anh' => $filename,
                    'thu_tu'        => $i,
                ]);
            }
            redirect('/admin/products');
            return;
        }

        $search      = trim($_GET['search']      ?? '');
        $category_id = (int)($_GET['id_danh_muc'] ?? 0) ?: null;
        $status      = trim($_GET['status']       ?? '');
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $perPage     = 15;
        $offset      = ($page - 1) * $perPage;

        $products   = $sanPhamModel->adminGetAll($search, $category_id, $status);
        $total      = count($products);
        $products   = array_slice($products, $offset, $perPage);
        $totalPages = (int)ceil($total / $perPage);
        $categories = $danhMucModel->getAll();

        // Batch load gallery images cho trang hiện tại
        if ($products) {
            $ids = array_column($products, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $galleryRows = db()->fetchAll(
                "SELECT id_san_pham, thu_tu, duong_dan_anh FROM anh_san_pham WHERE id_san_pham IN ($placeholders) ORDER BY thu_tu ASC",
                $ids
            );
            $galleryMap = [];
            foreach ($galleryRows as $row) {
                $galleryMap[$row['id_san_pham']][] = $row;
            }
            foreach ($products as &$p) {
                $p['gallery'] = $galleryMap[$p['id']] ?? [];
            }
            unset($p);
        }

        require_once ROOT_PATH . '/views/quan_tri/san_pham.php';
    }

    // ── Đơn hàng ───────────────────────────────────────────────

    public function orders($param = null) {
        $this->requireAdmin();

        require_once ROOT_PATH . '/models/DonHang.php';
        $donHangModel = new DonHang();

        if ($param === 'status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $reason = trim($_POST['cancel_reason'] ?? '');
            if ($id && $status) {
                $order = $donHangModel->getById($id);
                if ($status === 'cancelled') {
                    $result = $donHangModel->cancel($id, $reason);
                } else {
                    $result = $donHangModel->updateStatus($id, $status);
                }

                if (!$result) {
                    $labels = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'delivered' => 'Đã giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
                    $from = $labels[$order['trang_thai']] ?? $order['trang_thai'];
                    $to   = $labels[$status] ?? $status;
                    setFlash('danger', "Không thể cập nhật trạng thái. Chuyển từ \"$from\" sang \"$to\" không hợp lệ.");
                    redirect('/admin/orders');
                    return;
                }

                // Thông báo chuông cho khách hàng
                if ($order && !empty($order['id_nguoi_dung'])) {
                    $statusMap = [
                        'confirmed' => ['Đơn hàng đã được xác nhận',       'Đơn hàng #%s đã được xác nhận và đang được chuẩn bị.',          'order'],
                        'shipping'  => ['Đơn hàng đang trên đường giao',    'Đơn hàng #%s đã được giao cho đơn vị vận chuyển, đang trên đường đến bạn.', 'order'],
                        'delivered' => ['Đơn hàng đã giao thành công',      'Đơn hàng #%s đã được giao thành công. Cảm ơn bạn đã mua hàng!', 'success'],
                        'completed' => ['Đơn hàng hoàn thành',              'Đơn hàng #%s đã hoàn tất. Đừng quên để lại đánh giá sản phẩm nhé!', 'success'],
                        'cancelled' => ['Đơn hàng đã bị hủy',               'Đơn hàng #%s đã bị hủy' . ($reason ? '. Lý do: ' . $reason : '.'), 'order'],
                    ];
                    if (isset($statusMap[$status])) {
                        [$title, $bodyTpl, $loai] = $statusMap[$status];
                        db()->insert('thong_bao', [
                            'id_nguoi_dung' => $order['id_nguoi_dung'],
                            'tieu_de'       => $title,
                            'noi_dung'      => sprintf($bodyTpl, $order['ma_don_hang']),
                            'loai'          => $loai,
                            'lien_ket'      => '/order/detail/' . $id,
                        ]);
                    }
                }

                setFlash('success', 'Đã cập nhật trạng thái đơn hàng.');
            }
            redirect('/admin/orders');
            return;
        }

        $search      = trim($_GET['search'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $perPage     = 20;

        $allOrders    = $donHangModel->adminGetAll($search, $statusFilter);
        $total        = count($allOrders);
        $totalPages   = (int)ceil($total / $perPage);
        $orders       = array_slice($allOrders, ($page - 1) * $perPage, $perPage);

        $statusCounts = [];
        foreach ($allOrders as $o) {
            $s = $o['trang_thai'];
            $statusCounts[$s] = ($statusCounts[$s] ?? 0) + 1;
        }

        require_once ROOT_PATH . '/views/quan_tri/don_hang.php';
    }

    /** Chi tiết đơn hàng dành cho admin */
    public function orderDetail($id) {
        $this->requireAdmin();

        require_once ROOT_PATH . '/models/DonHang.php';
        $donHangModel = new DonHang();

        $order   = $donHangModel->getById($id);
        if (!$order) {
            redirect('/admin/orders');
            return;
        }

        $details = $donHangModel->getDetails($id);
        $pageTitle = 'Chi tiết đơn hàng #' . $order['ma_don_hang'];
        require_once ROOT_PATH . '/views/quan_tri/chi_tiet_don.php';
    }

    // ── Khách hàng ────────────────────────────────────────────

    public function customers() {
        $this->requireAdmin();

        require_once ROOT_PATH . '/models/NguoiDung.php';
        $nguoiDungModel = new NguoiDung();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
            $nguoiDungModel->toggleStatus((int)$_POST['toggle_id']);
            redirect('/admin/customers');
            return;
        }

        $search  = trim($_GET['search'] ?? '');
        $rank    = trim($_GET['rank']   ?? '');
        $customers = $nguoiDungModel->adminGetAll($search, $rank);

        require_once ROOT_PATH . '/views/quan_tri/khach_hang.php';
    }

    // ── Nhân viên ─────────────────────────────────────────────

    public function employees($param = null) {
        $this->requireAdmin();

        $db = db();

        if ($param === 'delete' && isset($_GET['id'])) {
            $db->query("DELETE FROM nhan_vien WHERE id = ?", [(int)$_GET['id']]);
            setFlash('success', 'Đã xóa nhân viên.');
            redirect('/admin/employees');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hoTen   = trim($_POST['ho_ten']         ?? '');
            $email   = trim($_POST['thu_dien_tu']    ?? '');
            $sdt     = trim($_POST['so_dien_thoai']  ?? '');
            $vaiTro  = trim($_POST['vai_tro']         ?? 'Nhân viên bán hàng');
            $matKhau = trim($_POST['mat_khau']        ?? '');

            if ($hoTen && $email && $matKhau) {
                $hash = password_hash($matKhau, PASSWORD_DEFAULT);
                $db->query(
                    "INSERT INTO nhan_vien (ho_ten, thu_dien_tu, mat_khau, so_dien_thoai, vai_tro)
                     VALUES (?, ?, ?, ?, ?)",
                    [$hoTen, $email, $hash, $sdt ?: null, $vaiTro]
                );
                setFlash('success', 'Đã thêm nhân viên mới.');
            } else {
                setFlash('error', 'Vui lòng điền đầy đủ thông tin bắt buộc.');
            }
            redirect('/admin/employees');
            return;
        }

        $employees = $db->fetchAll(
            "SELECT * FROM nhan_vien ORDER BY ngay_tao DESC"
        );

        require_once ROOT_PATH . '/views/quan_tri/nhan_vien.php';
    }

    // ── Voucher / Phiếu giảm giá ──────────────────────────────

    public function vouchers($param = null) {
        $this->requireAdmin();

        require_once ROOT_PATH . '/models/PhieuGiamGia.php';
        $phieuModel = new PhieuGiamGia();

        if ($param === 'delete' && isset($_GET['id'])) {
            $phieuModel->delete((int)$_GET['id']);
            setFlash('success', 'Đã xóa voucher.');
            redirect('/admin/vouchers');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ma_phieu'           => strtoupper(trim($_POST['ma_phieu']           ?? '')),
                'loai'               => $_POST['loai']               ?? 'percent',
                'gia_tri'            => (float)($_POST['gia_tri']            ?? 0),
                'don_hang_toi_thieu' => (float)($_POST['don_hang_toi_thieu'] ?? 0),
                'so_luong'           => (int)($_POST['so_luong']           ?? 0),
                'ngay_het_han'       => $_POST['ngay_het_han'] ?? null,
                'dang_hoat_dong'     => 1,
            ];
            if ($data['ma_phieu'] && $data['gia_tri'] > 0) {
                $phieuModel->create($data);
                setFlash('success', 'Đã tạo voucher ' . $data['ma_phieu'] . '.');
            } else {
                setFlash('error', 'Mã phiếu và giá trị không được để trống.');
            }
            redirect('/admin/vouchers');
            return;
        }

        $vouchers = $phieuModel->getAll();

        require_once ROOT_PATH . '/views/quan_tri/phieu_giam_gia.php';
    }

    // ── Đánh giá ──────────────────────────────────────────────

    public function reviews($param = null) {
        $this->requireAdmin();

        $db = db();

        if ($param === 'delete' && isset($_GET['id'])) {
            $db->query("DELETE FROM danh_gia WHERE id = ?", [(int)$_GET['id']]);
            setFlash('success', 'Đã xóa đánh giá.');
            redirect('/admin/reviews');
            return;
        }

        $reviews = $db->fetchAll(
            "SELECT dg.*, sp.ten AS ten_san_pham, sp.hinh_thu_nho,
                    nd.ho_ten AS user_name
             FROM danh_gia dg
             JOIN san_pham  sp ON sp.id = dg.id_san_pham
             JOIN nguoi_dung nd ON nd.id = dg.id_nguoi_dung
             ORDER BY dg.ngay_tao DESC"
        );

        require_once ROOT_PATH . '/views/quan_tri/danh_gia.php';
    }

    // ── Liên hệ ───────────────────────────────────────────────

    public function contacts($param = null) {
        $this->requireAdmin();

        $db = db();

        if ($param === 'read' && isset($_GET['id'])) {
            $db->query(
                "UPDATE lien_he SET trang_thai='read' WHERE id=? AND trang_thai='new'",
                [(int)$_GET['id']]
            );
            redirect('/admin/contacts');
            return;
        }

        if ($param === 'delete' && isset($_GET['id'])) {
            $db->query("DELETE FROM lien_he WHERE id = ?", [(int)$_GET['id']]);
            setFlash('success', 'Đã xóa tin nhắn.');
            redirect('/admin/contacts');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            (isset($_POST['reply_id']) || isset($_POST['id']) || $param === 'reply')) {
            $id    = (int)($_POST['reply_id'] ?? $_POST['id'] ?? 0);
            $reply = trim($_POST['reply_content'] ?? '');
            if ($id && $reply) {
                $lienHe = $db->fetch("SELECT * FROM lien_he WHERE id = ?", [$id]);
                $db->query(
                    "UPDATE lien_he SET trang_thai='replied', noi_dung_tra_loi=?,
                            nguoi_tra_loi=?, ngay_tra_loi=NOW() WHERE id=?",
                    [$reply, $_SESSION['admin']['ho_ten'], $id]
                );

                // Thông báo chuông nếu email khớp với tài khoản
                if ($lienHe) {
                    $user = $db->fetch(
                        "SELECT id FROM nguoi_dung WHERE thu_dien_tu = ? LIMIT 1",
                        [$lienHe['thu_dien_tu']]
                    );
                    if ($user) {
                        $subject = $lienHe['chu_de'] ? ' về "' . $lienHe['chu_de'] . '"' : '';
                        $db->insert('thong_bao', [
                            'id_nguoi_dung' => $user['id'],
                            'tieu_de'       => 'VQSTORE đã phản hồi tin nhắn của bạn',
                            'noi_dung'      => 'Chúng tôi đã phản hồi tin nhắn của bạn' . $subject . ': ' . $reply,
                            'loai'          => 'admin_mail',
                            'lien_ket'      => '/account/notifications',
                        ]);
                    }
                }

                setFlash('success', 'Đã lưu phản hồi.');
            }
            redirect('/admin/contacts');
            return;
        }

        $filter  = trim($_GET['status'] ?? '');
        $search  = trim($_GET['search'] ?? '');
        $sql     = "SELECT * FROM lien_he WHERE 1=1";
        $params  = [];
        if ($filter) { $sql .= " AND trang_thai=?";         $params[] = $filter; }
        if ($search) { $sql .= " AND (ten LIKE ? OR thu_dien_tu LIKE ?)";
                       $params[] = '%'.$search.'%'; $params[] = '%'.$search.'%'; }
        $sql    .= " ORDER BY ngay_tao DESC";
        $contacts = $db->fetchAll($sql, $params);

        $counts = [
            'all'     => $db->fetch("SELECT COUNT(*) AS c FROM lien_he")['c']             ?? 0,
            'new'     => $db->fetch("SELECT COUNT(*) AS c FROM lien_he WHERE trang_thai='new'")['c']     ?? 0,
            'read'    => $db->fetch("SELECT COUNT(*) AS c FROM lien_he WHERE trang_thai='read'")['c']    ?? 0,
            'replied' => $db->fetch("SELECT COUNT(*) AS c FROM lien_he WHERE trang_thai='replied'")['c'] ?? 0,
        ];

        require_once ROOT_PATH . '/views/quan_tri/lien_he.php';
    }

    // ── Doanh thu ─────────────────────────────────────────────

    public function revenue() {
        $this->requireAdmin();

        require_once ROOT_PATH . '/models/DonHang.php';
        $donHangModel = new DonHang();

        $period = $_GET['period'] ?? 'month';

        $rawData = match ($period) {
            'week'  => $donHangModel->getRevenueByWeek(),
            'year'  => $donHangModel->getRevenueByMonth(date('Y')),
            default => $donHangModel->getRevenueByMonth(date('Y')),
        };

        if ($period === 'week') {
            $chartData = array_map(fn($r) => [
                'label' => 'Tuần ' . $r['tuan'],
                'total' => $r['tong'],
            ], $rawData);
        } else {
            $monthNames = ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
            $chartData  = array_map(fn($r) => [
                'label' => $monthNames[(int)$r['thang'] - 1],
                'total' => $r['tong'],
            ], $rawData);
        }

        $summaryRaw = $donHangModel->getRevenueSummary();
        $db = db();
        $summary    = [
            'today'            => $summaryRaw['hom_nay'] ?? 0,
            'week'             => $summaryRaw['tuan']    ?? 0,
            'month'            => $summaryRaw['thang']   ?? 0,
            'year'             => $summaryRaw['nam']     ?? 0,
            'tong_don_hang'    => $db->fetch("SELECT COUNT(*) AS c FROM don_hang")['c'] ?? 0,
            'completed_orders' => $db->fetch("SELECT COUNT(*) AS c FROM don_hang WHERE trang_thai='completed'")['c'] ?? 0,
            'cancelled_orders' => $db->fetch("SELECT COUNT(*) AS c FROM don_hang WHERE trang_thai='cancelled'")['c'] ?? 0,
        ];

        require_once ROOT_PATH . '/views/quan_tri/doanh_thu.php';
    }

    // ── Gửi Email ─────────────────────────────────────────────

    public function mail() {
        $this->requireAdmin();

        require_once ROOT_PATH . '/ho_tro/GuiThu.php';
        require_once ROOT_PATH . '/models/NguoiDung.php';
        $nguoiDungModel = new NguoiDung();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recipientId = $_POST['recipient_id'] ?? 'all';
            $subject     = trim($_POST['subject']  ?? '');
            $content     = trim($_POST['content']  ?? '');

            if (!$subject || !$content) {
                setFlash('error', 'Vui lòng nhập tiêu đề và nội dung email.');
                redirect('/admin/mail');
                return;
            }

            $htmlBody = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;">'
                      . '<div style="background:#2563eb;padding:20px;text-align:center;">'
                      . '<h2 style="color:#fff;margin:0;">VQSTORE</h2></div>'
                      . '<div style="padding:24px;background:#fff;">'
                      . '<p>' . nl2br(htmlspecialchars($content)) . '</p>'
                      . '</div>'
                      . '<div style="background:#f5f5f5;padding:12px;text-align:center;font-size:12px;color:#888;">'
                      . 'VQSTORE — Hệ thống laptop &amp; phụ kiện</div></div>';

            if ($recipientId === 'all') {
                $targets = db()->fetchAll("SELECT id, ho_ten, thu_dien_tu FROM nguoi_dung WHERE trang_thai='active'");
            } else {
                $u = db()->fetch("SELECT id, ho_ten, thu_dien_tu FROM nguoi_dung WHERE id=?", [(int)$recipientId]);
                $targets = $u ? [$u] : [];
            }

            $sent = $failed = 0;
            foreach ($targets as $u) {
                $err = '';
                Mailer::send($u['thu_dien_tu'], $u['ho_ten'], $subject, $htmlBody, $err);
                $err ? $failed++ : $sent++;
            }

            $msg = "Đã gửi: $sent";
            if ($failed) $msg .= ", thất bại: $failed";
            setFlash($failed ? 'warning' : 'success', $msg);
            redirect('/admin/mail');
            return;
        }

        $users = db()->fetchAll(
            "SELECT id, ho_ten, thu_dien_tu FROM nguoi_dung WHERE trang_thai='active' ORDER BY ho_ten"
        );

        require_once ROOT_PATH . '/views/quan_tri/thu.php';
    }

}
