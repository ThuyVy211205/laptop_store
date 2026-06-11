<?php
/**
 * AdminController
 * Handles all admin panel pages and actions.
 */
class AdminController {

    private $db;

    public function __construct() {
        $this->db = db();
    }

    /* ---- Auth guard ---- */
    private function requireAdmin() {
        if (empty($_SESSION['admin'])) {
            header('Location: ' . SITE_URL . '/auth/login');
            exit;
        }
    }

    /* ================================================================
       Dashboard
       ================================================================ */
    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $this->requireAdmin();

        $stats = [
            'total_revenue'   => $this->db->fetch(
                "SELECT COALESCE(SUM(total_amount),0) AS c FROM orders WHERE status='completed'"
            )['c'] ?? 0,
            'total_orders'    => $this->db->fetch("SELECT COUNT(*) AS c FROM orders")['c'] ?? 0,
            'total_products'  => $this->db->fetch("SELECT COUNT(*) AS c FROM products")['c'] ?? 0,
            'total_customers' => $this->db->fetch("SELECT COUNT(*) AS c FROM users")['c'] ?? 0,
            'pending_orders'  => $this->db->fetch(
                "SELECT COUNT(*) AS c FROM orders WHERE status='pending'"
            )['c'] ?? 0,
        ];

        $recentOrders = $this->db->fetchAll(
            "SELECT o.*, u.full_name AS user_name
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC
             LIMIT 10"
        );

        $topProducts = $this->db->fetchAll(
            "SELECT name, thumbnail, sold_quantity,
                    COALESCE(sale_price, price) AS final_price
             FROM products
             WHERE status = 'active'
             ORDER BY sold_quantity DESC
             LIMIT 5"
        );

        $revenueData = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at,'%m/%Y') AS month,
                    SUM(total_amount) AS total
             FROM orders
             WHERE status = 'completed'
               AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(created_at,'%m/%Y')
             ORDER BY MIN(created_at) ASC"
        );

        include ROOT_PATH . '/views/admin/dashboard.php';
    }

    /* ================================================================
       Products
       ================================================================ */
    public function products($param = null) {
        $this->requireAdmin();

        /* Handle DELETE */
        if ($param === 'delete') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id) {
                $this->db->execute("DELETE FROM products WHERE id = ?", [$id]);
                setFlash('success', 'Đã xóa sản phẩm!');
            }
            header('Location: ' . SITE_URL . '/admin/products');
            exit;
        }

        /* Handle ADD / EDIT (POST) */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveProduct();
            return;
        }

        /* List */
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $limit     = 15;
        $offset    = ($page - 1) * $limit;
        $search    = trim($_GET['search'] ?? '');
        $category_id = (int)($_GET['category_id'] ?? 0);
        $status    = in_array($_GET['status'] ?? '', ['active','inactive']) ? $_GET['status'] : '';

        $where  = [];
        $params = [];

        if ($search) {
            $where[]  = "(p.name LIKE ? OR p.sku LIKE ? OR p.brand LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($category_id) {
            $where[]  = "p.category_id = ?";
            $params[] = $category_id;
        }
        if ($status) {
            $where[]  = "p.status = ?";
            $params[] = $status;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $products = $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             $whereClause
             ORDER BY p.created_at DESC
             LIMIT $limit OFFSET $offset",
            $params
        );

        $total      = (int)($this->db->fetch(
            "SELECT COUNT(*) AS c FROM products p $whereClause", $params
        )['c'] ?? 0);
        $totalPages = $total > 0 ? (int)ceil($total / $limit) : 1;

        $categories = $this->db->fetchAll(
            "SELECT id, name FROM categories WHERE status='active' ORDER BY name"
        );

        include ROOT_PATH . '/views/admin/products.php';
    }

    /* ----------------------------------------------------------------
       Save product (add or edit)
       ---------------------------------------------------------------- */
    private function saveProduct() {
        $action = $_POST['action'] ?? 'add';
        $id     = (int)($_POST['id'] ?? 0);

        $name        = trim($_POST['name']        ?? '');
        $category_id = (int)($_POST['category_id']?? 0);
        $brand       = trim($_POST['brand']       ?? '');
        $sku         = trim($_POST['sku']         ?? '');
        $price       = (float)($_POST['price']    ?? 0);
        $sale_price  = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $stock       = (int)($_POST['stock']      ?? 0);
        $description = trim($_POST['description'] ?? '');
        $pstatus     = in_array($_POST['status'] ?? '', ['active','inactive'])
                       ? $_POST['status'] : 'active';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        if (!$name || !$category_id || $price <= 0) {
            setFlash('error', 'Vui lòng điền đầy đủ thông tin bắt buộc!');
            header('Location: ' . SITE_URL . '/admin/products');
            exit;
        }

        /* Thumbnail upload */
        $thumbnail = $_POST['current_thumbnail'] ?? null;
        if (!empty($_FILES['thumbnail']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed) && $_FILES['thumbnail']['size'] <= MAX_UPLOAD_SIZE) {
                $filename = 'product_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], UPLOAD_PATH . '/' . $filename)) {
                    $thumbnail = $filename;
                }
            }
        }

        $data = [
            'category_id' => $category_id,
            'name'        => $name,
            'brand'       => $brand ?: null,
            'sku'         => $sku   ?: null,
            'price'       => $price,
            'sale_price'  => $sale_price,
            'stock'       => $stock,
            'description' => $description ?: null,
            'status'      => $pstatus,
            'is_featured' => $is_featured,
            'thumbnail'   => $thumbnail,
        ];

        if ($action === 'edit' && $id) {
            $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $data['id'] = $id;
            $this->db->execute("UPDATE products SET $sets WHERE id = :id", $data);
            setFlash('success', 'Cập nhật sản phẩm thành công!');
        } else {
            $data['slug']       = createSlug($name) . '-' . time();
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('products', $data);
            setFlash('success', 'Thêm sản phẩm thành công!');
        }

        header('Location: ' . SITE_URL . '/admin/products');
        exit;
    }

    /* ================================================================
       Orders — Quản lý đơn hàng
       ================================================================ */
    public function orders($param = null) {
        $this->requireAdmin();

        /* Update status */
        if ($param === 'status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $allowed = ['pending','confirmed','shipping','delivered','completed','cancelled'];
            if ($id && in_array($status, $allowed)) {
                $reason = trim($_POST['cancel_reason'] ?? '');

                // Lấy thông tin đơn hàng trước khi cập nhật để gửi thông báo
                $order = $this->db->fetch(
                    "SELECT user_id, order_code FROM orders WHERE id=?", [$id]
                );

                if ($status === 'cancelled' && $reason) {
                    $this->db->execute(
                        "UPDATE orders SET status='cancelled', cancel_reason=? WHERE id=?",
                        [$reason, $id]
                    );
                } else {
                    $this->db->execute("UPDATE orders SET status=? WHERE id=?", [$status, $id]);
                }

                // COD: tự động đánh dấu đã thanh toán khi hoàn thành
                if ($status === 'completed') {
                    $orderExtra = $this->db->fetch(
                        "SELECT payment_method, payment_status FROM orders WHERE id=?", [$id]
                    );
                    if ($orderExtra && $orderExtra['payment_method'] === 'cod' && $orderExtra['payment_status'] === 'pending') {
                        $this->db->execute(
                            "UPDATE orders SET payment_status='paid' WHERE id=?", [$id]
                        );
                    }
                }

                // Gửi thông báo cho khách hàng khi trạng thái thay đổi
                if ($order && $order['user_id']) {
                    $statusMessages = [
                        'confirmed'  => ['title' => 'Đơn hàng đã được xác nhận', 'content' => 'Đơn hàng #%s đã được xác nhận và đang được chuẩn bị.'],
                        'shipping'   => ['title' => 'Đơn hàng đang giao hàng', 'content' => 'Đơn hàng #%s đang trên đường giao đến bạn.'],
                        'delivered'  => ['title' => 'Đơn hàng đã giao thành công', 'content' => 'Đơn hàng #%s đã được giao thành công. Vui lòng xác nhận đã nhận hàng.'],
                        'completed'  => ['title' => 'Đơn hàng hoàn thành', 'content' => 'Đơn hàng #%s đã hoàn thành. Cảm ơn bạn đã mua hàng!'],
                        'cancelled'  => ['title' => 'Đơn hàng đã bị hủy', 'content' => 'Đơn hàng #%s đã bị hủy' . ($reason ? '. Lý do: ' . $reason : '') . '.'],
                    ];
                    if (isset($statusMessages[$status])) {
                        $msg = $statusMessages[$status];
                        $this->db->insert('notifications', [
                            'user_id' => $order['user_id'],
                            'title'   => $msg['title'],
                            'content' => sprintf($msg['content'], $order['order_code']),
                            'type'    => 'order',
                            'link'    => SITE_URL . '/order/detail/' . $id,
                        ]);
                    }
                }

                setFlash('success', 'Đã cập nhật trạng thái đơn hàng!');
            }
            header('Location: ' . SITE_URL . '/admin/orders');
            exit;
        }

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        $status = in_array($_GET['status'] ?? '', ['pending','confirmed','shipping','delivered','completed','cancelled'])
                  ? $_GET['status'] : '';

        $where  = []; $params = [];
        if ($search) {
            $where[]  = "(o.order_code LIKE ? OR o.shipping_name LIKE ? OR o.shipping_phone LIKE ?)";
            $params   = array_merge($params, ["%$search%","%$search%","%$search%"]);
        }
        if ($status) { $where[] = "o.status = ?"; $params[] = $status; }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $orders = $this->db->fetchAll(
            "SELECT o.*, u.full_name AS user_name
             FROM orders o LEFT JOIN users u ON o.user_id = u.id
             $whereClause ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset",
            $params
        );
        $total      = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM orders o $whereClause", $params)['c'] ?? 0);
        $totalPages = max(1, (int)ceil($total / $limit));

        $statusCounts = [];
        foreach (['pending','confirmed','shipping','delivered','completed','cancelled'] as $s) {
            $statusCounts[$s] = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM orders WHERE status=?",[$s])['c'] ?? 0);
        }

        include ROOT_PATH . '/views/admin/orders.php';
    }

    /* ================================================================
       Customers — Quản lý khách hàng
       ================================================================ */
    public function customers($param = null) {
        $this->requireAdmin();

        if ($param === 'toggle' && isset($_GET['id'])) {
            $id   = (int)$_GET['id'];
            $user = $this->db->fetch("SELECT status FROM users WHERE id=?", [$id]);
            if ($user) {
                $newStatus = $user['status'] === 'active' ? 'blocked' : 'active';
                $this->db->execute("UPDATE users SET status=? WHERE id=?", [$newStatus, $id]);
                setFlash('success', 'Đã cập nhật trạng thái tài khoản!');
            }
            header('Location: ' . SITE_URL . '/admin/customers');
            exit;
        }

        $search = trim($_GET['search'] ?? '');
        $rank   = in_array($_GET['rank'] ?? '', ['silver','gold','diamond']) ? $_GET['rank'] : '';

        $where  = []; $params = [];
        if ($search) {
            $where[]  = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params   = array_merge($params, ["%$search%","%$search%","%$search%"]);
        }
        if ($rank) { $where[] = "rank = ?"; $params[] = $rank; }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $customers = $this->db->fetchAll(
            "SELECT * FROM users $whereClause ORDER BY created_at DESC", $params
        );

        include ROOT_PATH . '/views/admin/customers.php';
    }

    /* ================================================================
       Employees — Quản lý nhân viên
       ================================================================ */
    public function employees($param = null) {
        $this->requireAdmin();

        if ($param === 'delete' && isset($_GET['id'])) {
            $this->db->execute("DELETE FROM employees WHERE id=?", [(int)$_GET['id']]);
            setFlash('success', 'Đã xóa nhân viên!');
            header('Location: ' . SITE_URL . '/admin/employees'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action   = $_POST['action'] ?? 'add';
            $id       = (int)($_POST['id'] ?? 0);
            $fullName = trim($_POST['full_name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $role     = trim($_POST['role'] ?? 'Nhân viên bán hàng');
            $password = $_POST['password'] ?? '';
            $status   = in_array($_POST['status'] ?? '', ['active','blocked']) ? $_POST['status'] : 'active';

            if (!$fullName || !$email) {
                setFlash('error', 'Vui lòng điền đầy đủ thông tin!');
                header('Location: ' . SITE_URL . '/admin/employees'); exit;
            }

            if ($action === 'edit' && $id) {
                $data = ['full_name'=>$fullName,'email'=>$email,'phone'=>$phone,'role'=>$role,'status'=>$status];
                if ($password) $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                $sets = implode(', ', array_map(fn($k) => "$k=:$k", array_keys($data)));
                $data['id'] = $id;
                $this->db->execute("UPDATE employees SET $sets WHERE id=:id", $data);
                setFlash('success', 'Đã cập nhật nhân viên!');
            } else {
                if (!$password) { setFlash('error', 'Mật khẩu không được để trống!'); header('Location: ' . SITE_URL . '/admin/employees'); exit; }
                $this->db->insert('employees', [
                    'full_name' => $fullName, 'email' => $email, 'phone' => $phone,
                    'role' => $role, 'status' => $status,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                setFlash('success', 'Đã thêm nhân viên!');
            }
            header('Location: ' . SITE_URL . '/admin/employees'); exit;
        }

        $employees = $this->db->fetchAll("SELECT * FROM employees ORDER BY created_at DESC");
        include ROOT_PATH . '/views/admin/employees.php';
    }

    /* ================================================================
       Vouchers — Quản lý mã giảm giá
       ================================================================ */
    public function vouchers($param = null) {
        $this->requireAdmin();

        if ($param === 'delete' && isset($_GET['id'])) {
            $this->db->execute("DELETE FROM vouchers WHERE id=?", [(int)$_GET['id']]);
            setFlash('success', 'Đã xóa voucher!');
            header('Location: ' . SITE_URL . '/admin/vouchers'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action     = $_POST['action'] ?? 'add';
            $id         = (int)($_POST['id'] ?? 0);
            $code       = strtoupper(trim($_POST['code'] ?? ''));
            $type       = in_array($_POST['type'] ?? '', ['percent','fixed']) ? $_POST['type'] : 'percent';
            $value      = (float)($_POST['value'] ?? 0);
            $minOrder   = (float)($_POST['min_order'] ?? 0);
            $maxDiscount= !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
            $quantity   = (int)($_POST['quantity'] ?? 0);
            $expiresAt  = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
            $isActive   = isset($_POST['is_active']) ? 1 : 0;

            if (!$code || $value <= 0) {
                setFlash('error', 'Mã voucher và giá trị không được để trống!');
                header('Location: ' . SITE_URL . '/admin/vouchers'); exit;
            }

            $data = ['code'=>$code,'type'=>$type,'value'=>$value,'min_order'=>$minOrder,
                     'max_discount'=>$maxDiscount,'quantity'=>$quantity,'expires_at'=>$expiresAt,'is_active'=>$isActive];

            if ($action === 'edit' && $id) {
                $sets = implode(', ', array_map(fn($k) => "$k=:$k", array_keys($data)));
                $data['id'] = $id;
                $this->db->execute("UPDATE vouchers SET $sets WHERE id=:id", $data);
                setFlash('success', 'Đã cập nhật voucher!');
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('vouchers', $data);
                setFlash('success', 'Đã thêm voucher!');
            }
            header('Location: ' . SITE_URL . '/admin/vouchers'); exit;
        }

        $vouchers = $this->db->fetchAll("SELECT * FROM vouchers ORDER BY created_at DESC");
        include ROOT_PATH . '/views/admin/vouchers.php';
    }

    /* ================================================================
       Reviews — Quản lý đánh giá
       ================================================================ */
    public function reviews($param = null) {
        $this->requireAdmin();

        if ($param === 'delete' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $review = $this->db->fetch("SELECT product_id FROM reviews WHERE id=?", [$id]);
            if ($review) {
                $this->db->execute("DELETE FROM reviews WHERE id=?", [$id]);
                // Recompute rating
                $pid = $review['product_id'];
                $agg = $this->db->fetch("SELECT COUNT(*) AS cnt, AVG(rating) AS avg FROM reviews WHERE product_id=?", [$pid]);
                $this->db->execute(
                    "UPDATE products SET rating_count=?, rating_avg=? WHERE id=?",
                    [(int)$agg['cnt'], round((float)$agg['avg'], 2), $pid]
                );
                setFlash('success', 'Đã xóa đánh giá!');
            }
            header('Location: ' . SITE_URL . '/admin/reviews'); exit;
        }

        $search  = trim($_GET['search'] ?? '');
        $rating  = (int)($_GET['rating'] ?? 0);
        $where   = []; $params = [];
        if ($search) {
            $where[]  = "(u.full_name LIKE ? OR p.name LIKE ? OR r.content LIKE ?)";
            $params   = array_merge($params, ["%$search%","%$search%","%$search%"]);
        }
        if ($rating >= 1 && $rating <= 5) { $where[] = "r.rating=?"; $params[] = $rating; }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $reviews = $this->db->fetchAll(
            "SELECT r.*, u.full_name AS user_name, p.name AS product_name, p.thumbnail
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             JOIN products p ON r.product_id = p.id
             $whereClause ORDER BY r.created_at DESC",
            $params
        );

        include ROOT_PATH . '/views/admin/reviews.php';
    }

    /* ================================================================
       Revenue — Báo cáo doanh thu
       ================================================================ */
    public function revenue() {
        $this->requireAdmin();

        $period = in_array($_GET['period'] ?? '', ['week','month','year']) ? $_GET['period'] : 'month';

        $summary = [
            'today'  => (float)($this->db->fetch("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE status IN ('completed','delivered') AND DATE(created_at)=CURDATE()")['t'] ?? 0),
            'week'   => (float)($this->db->fetch("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE status IN ('completed','delivered') AND YEARWEEK(created_at,1)=YEARWEEK(NOW(),1)")['t'] ?? 0),
            'month'  => (float)($this->db->fetch("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE status IN ('completed','delivered') AND YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())")['t'] ?? 0),
            'year'   => (float)($this->db->fetch("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE status IN ('completed','delivered') AND YEAR(created_at)=YEAR(NOW())")['t'] ?? 0),
            'total_orders'     => (int)($this->db->fetch("SELECT COUNT(*) AS c FROM orders")['c'] ?? 0),
            'completed_orders' => (int)($this->db->fetch("SELECT COUNT(*) AS c FROM orders WHERE status IN ('completed','delivered')")['c'] ?? 0),
            'cancelled_orders' => (int)($this->db->fetch("SELECT COUNT(*) AS c FROM orders WHERE status='cancelled'")['c'] ?? 0),
        ];

        if ($period === 'week') {
            $chartData = $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at,'%d/%m') AS label, COALESCE(SUM(total_amount),0) AS total
                 FROM orders WHERE status IN ('completed','delivered') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC"
            );
        } elseif ($period === 'year') {
            $chartData = $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at,'%m/%Y') AS label, COALESCE(SUM(total_amount),0) AS total
                 FROM orders WHERE status IN ('completed','delivered') AND YEAR(created_at)=YEAR(NOW())
                 GROUP BY MONTH(created_at) ORDER BY MONTH(created_at) ASC"
            );
        } else {
            $chartData = $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at,'%d/%m') AS label, COALESCE(SUM(total_amount),0) AS total
                 FROM orders WHERE status IN ('completed','delivered') AND YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())
                 GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC"
            );
        }

        $topProducts = $this->db->fetchAll(
            "SELECT p.name, p.thumbnail, p.sold_quantity,
                    COALESCE(p.sale_price, p.price) AS final_price,
                    COALESCE(SUM(od.subtotal),0) AS revenue
             FROM products p
             LEFT JOIN order_details od ON od.product_id = p.id
             LEFT JOIN orders o ON od.order_id = o.id AND o.status IN ('completed','delivered')
             GROUP BY p.id ORDER BY revenue DESC LIMIT 10"
        );

        $ordersByStatus = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status"
        );

        include ROOT_PATH . '/views/admin/revenue.php';
    }

    /* ================================================================
       Contacts — Tin nhắn liên hệ
       ================================================================ */
    public function contacts($param = null) {
        $this->requireAdmin();

        /* Phản hồi tin nhắn (POST) */
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param === 'reply') {
            $id      = (int)($_POST['id'] ?? 0);
            $reply   = trim($_POST['reply_content'] ?? '');
            $replier = $_SESSION['admin']['full_name'] ?? 'Admin';

            if ($id && $reply) {
                $this->db->execute(
                    "UPDATE contacts SET status = 'replied', reply_content = ?, replied_by = ?, replied_at = NOW() WHERE id = ?",
                    [$reply, $replier, $id]
                );

                // Tạo notification cho khách hàng nếu họ có tài khoản
                $contact = $this->db->fetch("SELECT * FROM contacts WHERE id = ?", [$id]);
                if ($contact) {
                    $user = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$contact['email']]);
                    if ($user) {
                        $subject = $contact['subject'] ?: 'Tin nhắn liên hệ';
                        $this->db->execute(
                            "INSERT INTO notifications (user_id, title, content, type, link) VALUES (?, ?, ?, 'reply', ?)",
                            [
                                $user['id'],
                                'Phản hồi: ' . mb_substr($subject, 0, 80),
                                mb_substr($reply, 0, 200),
                                SITE_URL . '/contact'
                            ]
                        );
                    }
                }

                setFlash('success', 'Đã gửi phản hồi thành công!');
            }
            header('Location: ' . SITE_URL . '/admin/contacts');
            exit;
        }

        /* Đánh dấu đã đọc */
        if ($param === 'read') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id) {
                $this->db->execute(
                    "UPDATE contacts SET status = 'read' WHERE id = ? AND status = 'new'",
                    [$id]
                );
            }
            header('Location: ' . SITE_URL . '/admin/contacts');
            exit;
        }

        /* Xóa tin nhắn */
        if ($param === 'delete') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id) {
                $this->db->execute("DELETE FROM contacts WHERE id = ?", [$id]);
                setFlash('success', 'Đã xóa tin nhắn!');
            }
            header('Location: ' . SITE_URL . '/admin/contacts');
            exit;
        }

        /* Danh sách tin nhắn */
        $filter  = in_array($_GET['status'] ?? '', ['new','read','replied']) ? $_GET['status'] : '';
        $search  = trim($_GET['search'] ?? '');

        $where  = [];
        $params = [];
        if ($filter) { $where[] = "status = ?"; $params[] = $filter; }
        if ($search) {
            $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ?)";
            $params  = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
        }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $contacts = $this->db->fetchAll(
            "SELECT * FROM contacts $whereClause ORDER BY created_at DESC",
            $params
        );

        $counts = [
            'all'     => $this->db->fetch("SELECT COUNT(*) AS c FROM contacts")['c'] ?? 0,
            'new'     => $this->db->fetch("SELECT COUNT(*) AS c FROM contacts WHERE status='new'")['c'] ?? 0,
            'read'    => $this->db->fetch("SELECT COUNT(*) AS c FROM contacts WHERE status='read'")['c'] ?? 0,
            'replied' => $this->db->fetch("SELECT COUNT(*) AS c FROM contacts WHERE status='replied'")['c'] ?? 0,
        ];

        include ROOT_PATH . '/views/admin/contacts.php';
    }

    /* ================================================================
       Admin Mail — gửi email cho khách hàng
       ================================================================ */
    public function mail($param = null)
    {
        $this->requireAdmin();
        $admin = $_SESSION['admin'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject     = trim($_POST['subject']      ?? '');
            $content     = trim($_POST['content']      ?? '');
            $recipientId = $_POST['recipient_id']      ?? 'all';

            if (!$subject || !$content) {
                setFlash('error', 'Vui lòng nhập tiêu đề và nội dung email.');
                redirect('/admin/mail');
                return;
            }

            // Lấy danh sách người nhận
            if ($recipientId === 'all') {
                $recipients    = $this->db->fetchAll("SELECT id, full_name, email FROM users WHERE status='active' AND email != ''");
                $recipientType = 'all';
            } else {
                $recipients    = $this->db->fetchAll("SELECT id, full_name, email FROM users WHERE id=? AND status='active'", [(int)$recipientId]);
                $recipientType = 'single';
            }

            if (empty($recipients)) {
                setFlash('error', 'Không tìm thấy khách hàng nào để gửi.');
                redirect('/admin/mail');
                return;
            }

            $sentOk = 0;
            $sentFail = 0;

            foreach ($recipients as $user) {
                // 1. Lưu vào notifications để khách xem trong tài khoản
                $this->db->insert('notifications', [
                    'user_id' => $user['id'],
                    'title'   => $subject,
                    'content' => $content,
                    'type'    => 'admin_mail',
                    'link'    => SITE_URL . '/account/notifications',
                ]);

                // 2. Gửi email thật qua SMTP
                $htmlBody = buildMailHtml($subject, nl2br(htmlspecialchars($content)));
                $smtpErr  = '';
                if (sendMail($user['email'], $user['full_name'], $subject, $htmlBody, $smtpErr)) {
                    $sentOk++;
                } else {
                    $sentFail++;
                }
            }

            $total = count($recipients);
            if ($sentFail === 0) {
                setFlash('success', "Đã gửi thành công $sentOk/$total email!");
            } elseif ($sentOk === 0) {
                setFlash('error', "Đã lưu thông báo cho $total khách trong hệ thống, nhưng gửi SMTP thất bại ($sentFail lỗi). Kiểm tra App Password.");
            } else {
                setFlash('warning', "Đã lưu thông báo cho $total khách. Gửi SMTP: $sentOk thành công, $sentFail thất bại.");
            }
            redirect('/admin/mail');
            return;
        }

        // GET — form soạn email
        $users = $this->db->fetchAll(
            "SELECT id, full_name, email FROM users WHERE status='active' ORDER BY full_name"
        );
        $activePage = 'mail';
        require_once ROOT_PATH . '/views/admin/mail.php';
    }

    /* ================================================================
       Login / Logout
       ================================================================ */
    public function login() {
        if (!empty($_SESSION['admin'])) {
            header('Location: ' . SITE_URL . '/admin');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = $_POST['password'] ?? '';

            if ($email && $password) {
                $admin = $this->db->fetch(
                    "SELECT * FROM admins WHERE email = ? AND status = 'active'",
                    [$email]
                );
                if ($admin && password_verify($password, $admin['password'])) {
                    $_SESSION['admin'] = [
                        'id'        => $admin['id'],
                        'full_name' => $admin['full_name'],
                        'email'     => $admin['email'],
                        'role'      => $admin['role'],
                    ];
                    header('Location: ' . SITE_URL . '/admin');
                    exit;
                }
            }
            $error = 'Email hoặc mật khẩu không đúng!';
        }

        /* Inline login page */
        $this->renderLoginPage($error);
    }

    public function logout() {
        session_destroy();
        header('Location: ' . SITE_URL);
        exit;
    }

    /* ----------------------------------------------------------------
       Inline admin login page (no separate view file needed)
       ---------------------------------------------------------------- */
    private function renderLoginPage($error = '') {
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Đăng nhập Admin — VQSTORE</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
            <style>
                body { background: #f0f4fb; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                .login-card { background:#fff; border-radius:20px; padding:40px 44px; box-shadow:0 8px 40px rgba(0,0,0,.1); width:100%; max-width:420px; animation: fadeUp .35s ease; }
                @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
                .login-brand { text-align:center; margin-bottom:28px; }
                .login-icon { width:56px;height:56px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:15px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:22px;color:#fff; }
                .login-title { font-family:'Rajdhani',sans-serif;font-size:26px;font-weight:700;color:#1e2a3b; }
                .login-sub { font-size:13.5px;color:#8a96b8;margin-top:4px; }
                .login-input { width:100%;border:1.5px solid #d4d9e8;background:#f8fafd;padding:11px 14px;border-radius:10px;font-size:14px;outline:none;transition:border-color .2s,box-shadow .2s;font-family:inherit; }
                .login-input:focus { border-color:#2563eb;background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.09); }
                .login-label { font-size:13px;font-weight:600;color:#4a5568;margin-bottom:7px;display:block; }
                .login-btn { width:100%;padding:12px;background:linear-gradient(135deg,#2563eb,#4f8ef8);color:#fff;border:none;border-radius:10px;font-size:14.5px;font-weight:700;cursor:pointer;transition:opacity .2s;font-family:inherit; }
                .login-btn:hover { opacity:.9; }
            </style>
        </head>
        <body>
            <div class="login-card">
                <div class="login-brand">
                    <div class="login-icon"><i class="fas fa-laptop"></i></div>
                    <div class="login-title">VQSTORE Admin</div>
                    <div class="login-sub">Đăng nhập vào bảng điều khiển</div>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="font-size:13.5px;">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= SITE_URL ?>/admin/login">
                    <div class="mb-3">
                        <label class="login-label">Email</label>
                        <input type="email" name="email" class="login-input"
                               placeholder="admin@techstore.vn" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-4">
                        <label class="login-label">Mật khẩu</label>
                        <input type="password" name="password" class="login-input"
                               placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                    </button>
                </form>

                <p class="text-center mt-4 mb-0" style="font-size:13px;color:#8a96b8;">
                    <a href="<?= SITE_URL ?>" style="color:#2563eb;">← Quay về trang chủ</a>
                </p>
            </div>
        </body>
        </html>
        <?php
    }
}
