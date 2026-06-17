# Hỏi - Đáp Code Dự Án VQSTORE

> Tổng hợp các câu hỏi giáo viên có thể hỏi khi bảo vệ đồ án, kèm vị trí code chính xác trong dự án.

---

## 1. KIẾN TRÚC TỔNG QUAN

### Q: Dự án dùng pattern gì? Mô tả luồng request?
**A:** Dùng **MVC + Front Controller**. Mọi request đi qua `index.php` → phân tích URL `?url=` → gọi đúng controller/action.

- `index.php:13-21` — Tách URL thành `$route`, `$action`, `$param`
- `index.php:27-135` — Switch-case định tuyến đến controller
- VD: `/products?type=laptop` → `DieuKhienSanPham::index()`
- VD: `/cart/add` → `DieuKhienGioHang::add()`

### Q: Controller giao tiếp với View như thế nào?
**A:** Controller `require_once` file view. View nằm trong scope của controller method nên truy cập được tất cả biến local ($products, $totalPages...). View bắt đầu bằng include header (`dau_trang.php`) và kết thúc bằng footer (`chan_trang.php`).

### Q: Thư mục `thu_vien/` chứa gì?
**A:** 3 file helper:
- `ham.php` — Hàm tiện ích: `formatPrice()`, `imgUrl()`, `paginate()`, `sendMail()`, `buildOrderConfirmEmail()`
- `tro_giup.php` — Auth helpers: `isLoggedIn()`, `requireLogin()`, `getCurrentUser()`, `getCartCount()`
- `phien.php` — Flash message: `setFlash()`, `getFlash()`

### Q: Dự án có bao nhiêu bảng trong database?
**A:** 16 bảng, gồm: `san_pham`, `danh_muc`, `nguoi_dung`, `gio_hang`, `don_hang`, `chi_tiet_don_hang`, `danh_gia`, `binh_luan`, `yeu_thich`, `phieu_giam_gia`, `thong_bao`, `anh_san_pham`, `quan_tri_vien`, `nhan_vien`, `ma_xac_minh`, `lien_he`.

File: `co_so_du_lieu/laptop_store.sql`

---

## 2. KẾT NỐI CƠ SỞ DỮ LIỆU

### Q: Kết nối database ở đâu? Dùng pattern gì?
**A:** `config/co_so_du_lieu.php:55-141` — Class `Database` dùng **Singleton Pattern**. Chỉ có 1 instance PDO duy nhất.

```php
// Dòng kết nối chính (dòng 62-69)
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
```

### Q: Lấy instance DB khắp dự án bằng cách nào?
**A:** Hàm `db()` ở `co_so_du_lieu.php:138-140`:
```php
function db() { return Database::getInstance(); }
```
Gọi `$this->db = db();` trong constructor của mọi Model.

### Q: Các phương thức query chính của class Database?
**A:** `fetch()`, `fetchAll()`, `query()`, `insert()`, `execute()`, `beginTransaction()`, `commit()`, `rollback()`. Tất cả dùng Prepared Statements (PDO prepared).

### Q: Dùng CSRF token hay chống SQL injection bằng gì?
- **SQL Injection**: Prepared Statements với `PDO::ATTR_EMULATE_PREPARES = false` → tham số luôn được bind an toàn
- **XSS**: Output dùng `htmlspecialchars()` trong view
- **Session**: `session_start()` tại `co_so_du_lieu.php:29-31`

---

## 3. ROUTING & URL

### Q: URL dạng `?url=products/type/laptop` được xử lý thế nào?
**A:** `index.php:13-21`:
1. Lấy `$_GET['url']`, trim `/`
2. `explode('/', $url)` → mảng segments
3. `$route = segments[0]` (controller), `$action = segments[1]` (method), `$param = segments[2]` (tham số)

### Q: Làm sao để thêm một route mới?
**A:** Thêm case vào switch-case trong `index.php:27-135`, ví dụ:
```php
case 'blog':
    require_once 'controllers/DieuKhienBlog.php';
    (new DieuKhienBlog())->$action($param);
    break;
```

---

## 4. GIỎ HÀNG

### Q: Code thêm vào giỏ hàng nằm ở đâu?
**A:**
- **Route**: `POST /cart/add` → `index.php:63-66` → `DieuKhienGioHang::add()`
- **Controller**: `controllers/DieuKhienGioHang.php:29-61`
- **Model**: `models/GioHang.php:45-90`

### Q: Cơ chế giỏ hàng cho khách (chưa đăng nhập) và user (đã đăng nhập)?
**A:**
- **Khách**: Lưu vào `$_SESSION['cart']` dạng `[productId => ['so_luong' => n]]` — `GioHang.php:79-89`
- **User**: Lưu vào bảng `gio_hang` trong DB — `GioHang.php:54-77`

### Q: Khi khách đăng nhập, giỏ hàng xử lý ra sao?
**A:** `GioHang::mergeSessionToDb()` — `models/GioHang.php:166-196`: Gộp giỏ hàng session vào DB, xử lý trùng lặp (cộng dồn số lượng), giới hạn theo tồn kho, sau đó xóa session cart.

### Q: Kiểm tra tồn kho khi thêm vào giỏ?
**A:** `GioHang.php:46-52`: Kiểm tra `ton_kho > 0` trước khi thêm. Nếu đã có trong giỏ, kiểm tra `so_luong_moi <= ton_kho`.

### Q: AJAX cập nhật sidebar giỏ hàng?
**A:** `DieuKhienGioHang::sidebar()` — `controllers/DieuKhienGioHang.php:145-187`: Trả về JSON chứa HTML mini cart + tổng tiền + số lượng.

---

## 5. PHÂN TRANG

### Q: Code phân trang sản phẩm nằm ở đâu?
**A:** 3 nơi:
- **Helper**: `thu_vien/ham.php:170-235` — Hàm `paginate($totalPages, $currentPage, $baseUrl, $queryParams)`
- **Gọi trong view**: `views/san_pham/danh_sach.php:512` và `views/san_pham/phu_kien.php:175`
- **Tính toán**: Controller `DieuKhienSanPham::index()` tính `$page`, `$offset`, `$totalPages`

### Q: Mỗi trang hiển thị bao nhiêu sản phẩm?
**A:** `define('ITEMS_PER_PAGE', 8)` tại `config/co_so_du_lieu.php:24`.

### Q: Khi chuyển trang, các bộ lọc (danh mục, giá, sắp xếp) có bị mất không?
**A:** Không. Hàm `paginate()` dùng `http_build_query($queryParams)` với `$pp['page'] = $i` để giữ nguyên tất cả GET params.

### Q: SQL phân trang viết ở đâu?
**A:** `models/SanPham.php:72`:
```sql
LIMIT 8 OFFSET 0   -- dòng: $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
```
Dùng `(int)` cast để chống SQL injection.

### Q: Làm sao biết tổng số trang?
**A:** Controller gọi `$this->sanPhamModel->countAll($filters)` → `SanPham.php:77-106` → `SELECT COUNT(*)`, sau đó `$totalPages = ceil($totalCount / ITEMS_PER_PAGE)`.

---

## 6. TÌM KIẾM & LỌC SẢN PHẨM

### Q: Tìm kiếm sản phẩm hoạt động thế nào?
**A:** Controller `DieuKhienSanPham::index()` kiểm tra `$_GET['q']` → `SanPham::getAll()` thêm điều kiện:
```sql
AND (p.ten LIKE ? OR p.mo_ta LIKE ?)
```
với params `'%từ_khóa%'` — `models/SanPham.php:47-50`

### Q: Lọc theo danh mục, giá, thương hiệu?
**A:** Cùng trong `SanPham::getAll()`:
- Danh mục: `WHERE p.id_danh_muc IN (...)` hoặc `= ?`
- Giá: `COALESCE(p.gia_khuyen_mai, p.gia) >= ? AND <= ?`
- Thương hiệu: `p.thuong_hieu = ?`
- Flash sale: `p.la_flash_sale = 1 AND p.ket_thuc_flash_sale > NOW()`

### Q: Sắp xếp sản phẩm có những lựa chọn nào?
**A:** 5 kiểu — `models/SanPham.php:53-70`:
- `newest`: `ORDER BY p.ngay_tao DESC`
- `price_asc`: `ORDER BY COALESCE(p.gia_khuyen_mai, p.gia) ASC`
- `price_desc`: Giá giảm dần
- `best_seller`: `ORDER BY p.so_luong_ban DESC`
- `rating`: `ORDER BY p.diem_danh_gia DESC`

### Q: AJAX gợi ý tìm kiếm?
**A:** `DieuKhienSanPham::searchSuggest()` — `controllers/DieuKhienSanPham.php:196-214`. Trả JSON các sản phẩm gợi ý khi gõ ≥ 2 ký tự.

---

## 7. THANH TOÁN & VOUCHER

### Q: Luồng checkout hoạt động thế nào?
**A:**
1. `GET /checkout` → `DieuKhienThanhToan::index()` — Hiển thị form
2. User nhập thông tin + chọn phương thức thanh toán (COD, bank_transfer, momo, vnpay)
3. `POST /checkout` → `DieuKhienThanhToan::process()` — Validate, tạo đơn hàng, trừ tồn kho, xóa giỏ

### Q: Áp dụng voucher/phiếu giảm giá ở đâu?
**A:**
- **Frontend**: `views/thanh_toan/thanh_toan.php:186-210` — AJAX gọi `/api/voucher/check`
- **API**: `controllers/DieuKhienAPI.php:190-216` — `voucherCheck()` trả về JSON `{giam_gia_dinh_dang, tong_moi_dinh_dang}`
- **Model**: `models/PhieuGiamGia.php:28-66` — `apply($code, $orderTotal)` xử lý % hoặc số tiền cố định

### Q: Voucher có những loại nào?
**A:** 2 loại:
- `percent`: Giảm theo % (có `giam_toi_da` giới hạn)
- `fixed`: Giảm số tiền cố định
- Kiểm tra: hạn sử dụng, số lần dùng, đơn tối thiểu

### Q: Mua ngay (buy now) hoạt động thế nào?
**A:** `DieuKhienGioHang::buynow()` — `controllers/DieuKhienGioHang.php:91-142`:
- `requireLogin()` bắt buộc
- Kiểm tra tồn kho
- Lưu `$_SESSION['buy_now']`
- Redirect thẳng đến `/checkout`

---

## 8. XÁC THỰC (AUTH)

### Q: Đăng nhập xử lý ở đâu?
**A:** `controllers/DieuKhienXacThuc.php:18-61` — `login()` method:
- Check `isLoggedIn()` → nếu rồi thì redirect `/account`
- POST: lấy email/password, `password_verify()` với hash trong DB
- Hỗ trợ 3 loại tài khoản: user (`nguoi_dung`), admin (`quan_tri_vien`), nhân viên (`nhan_vien`)

### Q: Đăng ký tài khoản mới?
**A:** `DieuKhienXacThuc::register()` — Validate email/mật khẩu, `password_hash(PASSWORD_DEFAULT)`, insert vào `nguoi_dung`, tự động đăng nhập.

### Q: Google OAuth hoạt động thế nào?
**A:** `DieuKhienXacThuc::googleLogin()` + `googleCallback()`:
- Redirect đến Google OAuth consent screen
- Nhận code → đổi lấy access token → lấy user info
- Nếu email đã tồn tại → đăng nhập; nếu chưa → tạo user mới

### Q: Session lưu những gì sau khi đăng nhập?
**A:** `$_SESSION['id_nguoi_dung']` (user), hoặc `$_SESSION['admin']` (admin với id, ho_ten, email, vai_tro).

### Q: Phân quyền admin vs user?
**A:**
- `requireLogin()` — `thu_vien/tro_giup.php:76-81`: Chặn route cần đăng nhập
- `requireAdmin()` — `thu_vien/tro_giup.php:84-88`: Chặn route admin
- Admin panel tại route `/admin`

---

## 9. CHI TIẾT SẢN PHẨM

### Q: Trang chi tiết sản phẩm lấy những dữ liệu gì?
**A:** `DieuKhienSanPham::detail($slug)` — `controllers/DieuKhienSanPham.php:147-180`:
- Sản phẩm chính (theo slug)
- Ảnh gallery (`anh_san_pham`)
- Đánh giá + bình luận (kèm phản hồi)
- Sản phẩm liên quan (cùng danh mục)
- Biến thể màu sắc (theo `nhom_bien_the`)
- Kiểm tra wishlist của user

### Q: Bình luận có hỗ trợ phản hồi (reply) không?
**A:** Có. `SanPham::getComments()` — `models/SanPham.php:287-308`: Lấy comment cha (`id_cha IS NULL`), sau đó với mỗi comment cha lấy replies (`id_cha = ?`). Cấu trúc lồng 1 cấp.

### Q: Đánh giá sản phẩm tính điểm trung bình ra sao?
**A:** `SanPham::recalcRating()` — `models/SanPham.php:323-333`: `SELECT AVG(diem_so)` sau mỗi lần thêm đánh giá, cập nhật vào `san_pham.diem_danh_gia` và `san_pham.so_danh_gia`.

---

## 10. ADMIN

### Q: Trang admin quản lý những gì?
**A:** `controllers/DieuKhienQuanTri.php`: Dashboard, quản lý sản phẩm (CRUD), danh mục, đơn hàng, người dùng, voucher, thông báo, liên hệ.

### Q: Admin quản lý sản phẩm CRUD như thế nào?
**A:** Model `SanPham.php:350-378`:
- `create($data)` → `insert()`
- `update($id, $data)` → `UPDATE san_pham SET ... WHERE id = ?`
- `delete($id)` → `DELETE FROM san_pham WHERE id = ?`
- `adminGetAll()` → Lấy tất cả sản phẩm (không giới hạn active)

### Q: Kiểm tra tồn kho thấp?
**A:** `SanPham::getLowStock()` — `models/SanPham.php:381-388`: `WHERE ton_kho <= LOW_STOCK_WARNING` (5). Dùng cho dashboard admin.

---

## 11. EMAIL

### Q: Gửi email dùng thư viện gì?
**A:** Custom class `GuiThu` (`ho_tro/GuiThu.php`) dùng PHPMailer, gọi qua 2 hàm:
- `sendMail()` — Email marketing từ `SMTP_USER` (vqstore296)
- `sendOrderMail()` — Email tự động (xác nhận đơn hàng) từ `SMTP_NOREPLY_USER`

File: `thu_vien/ham.php:200-214`

### Q: Cấu hình SMTP?
**A:** `config/co_so_du_lieu.php:43-46`: Host `smtp.gmail.com`, port `587`.

---

## 12. FLASH SALE & KHUYẾN MÃI

### Q: Flash sale hoạt động thế nào?
**A:**
- Sản phẩm flash sale có `la_flash_sale = 1`, `gia_khuyen_mai IS NOT NULL`, `ket_thuc_flash_sale > NOW()` hoặc NULL
- Model: `SanPham::getFlashSale()` — `models/SanPham.php:176-188`
- View: `views/san_pham/danh_sach.php:29-351` — Hiển thị countdown + voucher zone + product grid
- Countdown: JavaScript đếm ngược đến 23:59:59

### Q: Trang khuyến mãi riêng?
**A:** Route `/promotions` hoặc `/khuyen-mai` → `DieuKhienKhuyenMai::index()`.

---

## 13. DANH SÁCH YÊU THÍCH (WISHLIST)

### Q: Thêm/xóa sản phẩm yêu thích?
**A:** `controllers/DieuKhienYeuThich.php` + `models/YeuThich.php`:
- `toggle($productId)`: Nếu đã thích → xóa; chưa thích → thêm
- Bắt buộc đăng nhập (`requireLogin()`)

### Q: Kiểm tra sản phẩm có trong wishlist?
**A:** `YeuThich::isInWishlist($userId, $productId)` — Dùng trong trang chi tiết sản phẩm để hiển thị trái tim đỏ/xám.

---

## 14. BẢO MẬT

### Q: Mật khẩu lưu thế nào?
**A:** Dùng `password_hash($password, PASSWORD_DEFAULT)` khi đăng ký, `password_verify($password, $hash)` khi đăng nhập.

### Q: CSRF protection?
**A:** Form checkout có token nhưng chưa implement đầy đủ. Các form POST khác dùng AJAX với `fetch()`.

### Q: XSS prevention?
**A:** Mọi output trong view dùng `htmlspecialchars()`.

### Q: SQL injection?
**A:** 100% Prepared Statements qua PDO. Int cast cho LIMIT/OFFSET: `(int)$limit`, `(int)$offset`.

---

## 15. MỘT SỐ CÂU HỎI KHÁC

### Q: `formatPrice()` làm gì?
**A:** `thu_vien/ham.php:8-10`: `number_format($amount, 0, ',', '.') . 'đ'` → `1500000` thành `1.500.000đ`.

### Q: `imgUrl()` làm gì?
**A:** Trả về URL đầy đủ của ảnh, kiểm tra file tồn tại, nếu không trả về ảnh mặc định.

### Q: Controller nào nặng nhất (nhiều code nhất)?
- `DieuKhienSanPham.php` (309 dòng) — Xử lý danh sách, tìm kiếm, lọc, chi tiết, quickview, search suggest
- `DieuKhienXacThuc.php` (374 dòng) — Login, register, forgot password, reset, Google OAuth

### Q: Model nào có nhiều method nhất?
**A:** `SanPham.php` (416 dòng) — 20+ methods: CRUD, search, filter, reviews, comments, stock, variants, related, admin.

### Q: Làm sao để debug dự án?
**A:** `config/co_so_du_lieu.php:34-41`: `define('DEBUG', true)` → `error_reporting(E_ALL)` + `display_errors = 1`.

---

## SƠ ĐỒ THƯ MỤC QUAN TRỌNG

```
laptop_store/
├── index.php              ← Front Controller (Routing)
├── config/
│   ├── co_so_du_lieu.php  ← DB Singleton + constants
│   └── bi_mat.php         ← DB credentials, OAuth keys
├── controllers/
│   ├── DieuKhienSanPham.php   ← Products (list, detail, search, filter)
│   ├── DieuKhienGioHang.php   ← Cart (add, remove, buynow)
│   ├── DieuKhienThanhToan.php ← Checkout
│   ├── DieuKhienXacThuc.php   ← Auth (login, register, Google)
│   ├── DieuKhienQuanTri.php   ← Admin panel
│   ├── DieuKhienAPI.php       ← AJAX endpoints
│   └── ...
├── models/
│   ├── SanPham.php        ← Product queries
│   ├── GioHang.php        ← Cart logic (DB + session)
│   ├── NguoiDung.php      ← User CRUD
│   ├── PhieuGiamGia.php   ← Voucher validation
│   └── ...
├── views/
│   ├── bo_cuc/            ← Layout (header, footer)
│   ├── san_pham/          ← Product pages + partials
│   ├── gio_hang/          ← Cart page
│   ├── thanh_toan/        ← Checkout page
│   └── ...
├── thu_vien/
│   ├── ham.php            ← Utility functions
│   ├── tro_giup.php       ← Auth helpers
│   └── phien.php          ← Flash messages
└── assets/
    ├── css/
    │   ├── style.css      ← Global styles
    │   └── product.css    ← Product page styles
    └── js/
```
