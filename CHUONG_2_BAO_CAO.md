CHƯƠNG 2: CƠ SỞ LÝ THUYẾT VÀ NỀN TẢNG CÔNG NGHỆ

2.1. Cơ sở lý thuyết

2.1.1. Tổng quan về thương mại điện tử (E-Commerce)

Thương mại điện tử (E-Commerce) là hình thức mua bán, trao đổi hàng hóa và dịch vụ thông qua các phương tiện điện tử, chủ yếu là Internet. Theo Tổ chức Thương mại Thế giới (WTO), thương mại điện tử bao gồm toàn bộ chu trình từ quảng bá sản phẩm, đàm phán, ký kết hợp đồng, thanh toán đến phân phối và hỗ trợ khách hàng — tất cả được thực hiện trên môi trường số.

Một hệ thống thương mại điện tử điển hình bao gồm các thành phần chính: giao diện người dùng (front-end), hệ thống quản lý sản phẩm và danh mục, giỏ hàng, xử lý đơn hàng, cổng thanh toán, quản lý khách hàng và báo cáo phân tích. Các mô hình phổ biến bao gồm B2C (Business-to-Consumer), B2B (Business-to-Business), C2C (Consumer-to-Consumer) và B2G (Business-to-Government). Đồ án này triển khai mô hình B2C — doanh nghiệp bán hàng trực tiếp đến người tiêu dùng cuối.

2.1.2. Kiến trúc MVC (Model-View-Controller)

MVC là một mẫu kiến trúc phần mềm được giới thiệu lần đầu tiên trong ngữ cảnh của ngôn ngữ lập trình Smalltalk-80 tại Xerox PARC vào những năm 1970. MVC phân tách ứng dụng thành ba tầng độc lập:

- Model: Chịu trách nhiệm quản lý dữ liệu, logic nghiệp vụ và các quy tắc xử lý. Model tương tác trực tiếp với cơ sở dữ liệu, thực hiện các thao tác CRUD (Create, Read, Update, Delete) và trả về dữ liệu đã được xử lý. Trong đồ án này, mỗi bảng trong cơ sở dữ liệu được ánh xạ thành một lớp Model riêng biệt (SanPham, NguoiDung, DonHang, v.v.).

- View: Lớp trình bày, chịu trách nhiệm hiển thị dữ liệu cho người dùng. View không chứa logic nghiệp vụ mà chỉ nhận dữ liệu từ Controller và render ra HTML. Các thành phần giao diện được tổ chức theo cấu trúc thành phần (component), cho phép tái sử dụng các phần giao diện như thẻ sản phẩm, thanh điều hướng, sidebar.

- Controller: Đóng vai trò trung gian, tiếp nhận yêu cầu từ người dùng, gọi Model để xử lý dữ liệu, sau đó chuyển dữ liệu đã xử lý đến View để hiển thị. Controller không chứa logic truy vấn SQL mà chỉ điều phối luồng dữ liệu. Trong đồ án này, mỗi nhóm chức năng (sản phẩm, giỏ hàng, đơn hàng, quản trị) được tổ chức thành một Controller riêng.

Ưu điểm của kiến trúc MVC bao gồm: phân tách trách nhiệm rõ ràng giúp dễ bảo trì và mở rộng; các thành phần có thể được phát triển và kiểm thử độc lập; cho phép nhiều lập trình viên làm việc song song trên các tầng khác nhau.

Đồ án này triển khai MVC thuần (không sử dụng framework) nhằm giúp sinh viên hiểu sâu bản chất của kiến trúc này, từ đó có nền tảng vững chắc để tiếp cận các framework MVC phổ biến như Laravel, Symfony hay ASP.NET MVC sau này.

2.1.3. Mô hình Front Controller

Front Controller là một mẫu thiết kế (Design Pattern) trong đó tất cả yêu cầu (request) từ người dùng được dẫn qua một điểm vào duy nhất — thường là file index.php. Bộ điều phối này phân tích URL, xác định Controller và Action tương ứng, sau đó gọi phương thức phù hợp để xử lý yêu cầu.

Trong đồ án, file index.php đóng vai trò Front Controller. URL được viết lại thông qua module mod_rewrite của Apache (file .htaccess) thành dạng tham số url, sau đó được phân tách thành các segment: segment đầu tiên xác định route (Controller), segment thứ hai xác định action (phương thức), các segment còn lại là tham số. Ví dụ, URL /product/asus-rog-strix-g15 được phân giải thành route = "product", action = "detail", tham số = "asus-rog-strix-g15".

2.1.4. Giỏ hàng và quy trình đặt hàng

Giỏ hàng (Shopping Cart) là thành phần cốt lõi của bất kỳ hệ thống thương mại điện tử nào, cho phép người dùng tạm lưu trữ các sản phẩm họ muốn mua trước khi tiến hành thanh toán. Hệ thống hỗ trợ hai cơ chế lưu trữ giỏ hàng:

- Session-based Cart: Dành cho khách chưa đăng nhập. Dữ liệu giỏ hàng được lưu trong biến $_SESSION dưới dạng mảng key-value, trong đó key là ID sản phẩm và value chứa số lượng.

- Database-based Cart: Dành cho người dùng đã đăng nhập. Dữ liệu được lưu trong bảng gio_hang, liên kết với bảng nguoi_dung qua khóa ngoại. Khi khách hàng đăng nhập, giỏ hàng từ session được đồng bộ (merge) vào cơ sở dữ liệu.

Quy trình đặt hàng trải qua các bước: (1) thêm sản phẩm vào giỏ hàng; (2) kiểm tra tồn kho; (3) áp dụng mã giảm giá (nếu có); (4) điền thông tin giao hàng và chọn phương thức thanh toán; (5) tạo đơn hàng và chi tiết đơn trong transaction để đảm bảo tính toàn vẹn dữ liệu; (6) trừ tồn kho; (7) gửi email xác nhận đơn hàng; (8) cập nhật chỉ số khách hàng (tổng chi tiêu, hạng thành viên).

2.1.5. Hệ thống xếp hạng thành viên (Membership Tier)

Hệ thống phân hạng khách hàng dựa trên tổng chi tiêu tích lũy là một chiến lược phổ biến trong thương mại điện tử nhằm khuyến khích khách hàng quay lại mua sắm và tăng giá trị vòng đời khách hàng (Customer Lifetime Value - CLV). Đồ án triển khai ba cấp độ thành viên:

- Silver (Bạc): Mặc định cho tất cả khách hàng mới, tổng chi tiêu dưới 15 triệu đồng.
- Gold (Vàng): Tổng chi tiêu từ 15 triệu đến dưới 50 triệu đồng.
- Diamond (Kim cương): Tổng chi tiêu từ 50 triệu đồng trở lên.

Hạng thành viên được tự động cập nhật sau mỗi lần đặt hàng thành công hoặc hủy đơn. Mỗi cấp độ có biểu tượng và màu sắc riêng, được hiển thị trên giao diện người dùng để tạo cảm giác đặc quyền và khích lệ khách hàng chi tiêu nhiều hơn.

2.1.6. Xác thực và phân quyền

Xác thực (Authentication) là quá trình xác minh danh tính của người dùng. Hệ thống hỗ trợ hai phương thức xác thực:

- Xác thực truyền thống: Người dùng đăng ký tài khoản với email và mật khẩu. Mật khẩu được băm (hash) bằng thuật toán bcrypt thông qua hàm password_hash() của PHP trước khi lưu vào cơ sở dữ liệu. Khi đăng nhập, hệ thống sử dụng password_verify() để so sánh mật khẩu người dùng nhập với giá trị hash đã lưu.

- Xác thực qua Google OAuth 2.0: Người dùng có thể đăng nhập bằng tài khoản Google. Hệ thống sử dụng giao thức OAuth 2.0 với Authorization Code Grant — chuyển hướng người dùng đến trang đăng nhập của Google, nhận authorization code, đổi code lấy access token, sau đó gọi Google API để lấy thông tin người dùng (email, tên, ảnh đại diện).

Phân quyền (Authorization): Hệ thống có hai vùng phân quyền chính — người dùng thông thường (front-end) và quản trị viên (admin). Mỗi Controller kiểm tra quyền truy cập trước khi thực thi bất kỳ hành động nào, đảm bảo rằng chỉ những người dùng được ủy quyền mới có thể truy cập vào các chức năng tương ứng. Session được bảo vệ bằng cơ chế tái tạo ID định kỳ (session regeneration) và CSRF token để chống tấn công Cross-Site Request Forgery.

2.2. Các nền tảng công nghệ của dự án

2.2.1. Ngôn ngữ lập trình PHP 8.x

PHP (PHP: Hypertext Preprocessor) là ngôn ngữ lập trình kịch bản phía máy chủ (server-side scripting), được thiết kế đặc biệt cho phát triển web. Phiên bản PHP 8 mang lại nhiều cải tiến đáng kể về hiệu năng nhờ JIT (Just-In-Time) Compiler, cú pháp hiện đại hơn với named arguments, union types, match expression, và constructor property promotion.

Đồ án tận dụng các tính năng của PHP 8 bao gồm: strict typing cho Model, PDO (PHP Data Objects) với prepared statements để chống SQL Injection, session management, file upload handling, và các hàm xử lý chuỗi/mảng phong phú. PHP được chọn vì khả năng triển khai nhanh, chi phí hosting thấp, tài liệu phong phú và cộng đồng hỗ trợ lớn — phù hợp cho môi trường học thuật và các dự án vừa và nhỏ.

2.2.2. Hệ quản trị cơ sở dữ liệu MySQL

MySQL là hệ quản trị cơ sở dữ liệu quan hệ mã nguồn mở, được phát triển bởi Oracle Corporation. MySQL sử dụng ngôn ngữ truy vấn có cấu trúc SQL (Structured Query Language) và hoạt động theo mô hình client-server. Những ưu điểm chính bao gồm: hiệu năng cao, độ tin cậy, dễ sử dụng, hỗ trợ transaction với InnoDB engine, và khả năng mở rộng tốt.

Cơ sở dữ liệu của đồ án gồm 15 bảng được thiết kế theo chuẩn 3NF (Third Normal Form) để giảm thiểu dư thừa dữ liệu và đảm bảo tính toàn vẹn. Các bảng chính bao gồm: san_pham (sản phẩm), danh_muc (danh mục), nguoi_dung (người dùng), gio_hang (giỏ hàng), don_hang (đơn hàng), chi_tiet_don (chi tiết đơn hàng), phieu_giam_gia (mã giảm giá), yeu_thich (yêu thích), danh_gia (đánh giá), binh_luan (bình luận), thong_bao (thông báo), lien_he (liên hệ), nhan_vien (nhân viên), quan_tri_vien (quản trị viên), thanh_toan (thanh toán), anh_san_pham (ảnh sản phẩm), và bang_quang_cao (banner quảng cáo).

Các mối quan hệ khóa ngoại (foreign key) được thiết lập để đảm bảo tính nhất quán: san_pham.id_danh_muc → danh_muc.id, gio_hang.id_san_pham → san_pham.id, don_hang.id_nguoi_dung → nguoi_dung.id, chi_tiet_don.id_don_hang → don_hang.id, v.v. Engine InnoDB được sử dụng để hỗ trợ transaction — đặc biệt quan trọng trong quy trình đặt hàng, nơi nhiều thao tác ghi phải được thực hiện đồng bộ (tạo đơn, trừ tồn kho, tạo bản ghi thanh toán) hoặc rollback toàn bộ nếu có lỗi.

2.2.3. Máy chủ Web Apache và XAMPP

Apache HTTP Server là phần mềm máy chủ web mã nguồn mở, được phát triển và duy trì bởi Apache Software Foundation. Apache chiếm thị phần lớn nhất trong các máy chủ web toàn cầu nhờ tính ổn định, khả năng mở rộng qua module, và hỗ trợ đa nền tảng.

XAMPP (X - Cross-platform, A - Apache, M - MySQL/MariaDB, P - PHP, P - Perl) là gói phần mềm tích hợp, cung cấp môi trường phát triển web local đầy đủ. XAMPP được sử dụng trong đồ án này vì: cài đặt nhanh, cấu hình đơn giản, tích hợp sẵn Apache + MySQL + PHP, có phpMyAdmin để quản lý cơ sở dữ liệu qua giao diện web, và hoàn toàn miễn phí.

Module mod_rewrite của Apache được cấu hình trong file .htaccess để thực hiện URL rewriting — chuyển đổi các URL thân thiện (ví dụ: /product/laptop-gaming) thành tham số cho Front Controller. Ngoài ra, .htaccess còn được cấu hình để chặn truy cập trực tiếp vào các thư mục nhạy cảm (config, models, controllers), nén nội dung (gzip compression), và thiết lập cache cho tài nguyên tĩnh.

2.2.4. Thư viện và công nghệ Front-end

Giao diện người dùng được xây dựng với các công nghệ front-end sau:

- HTML5: Cấu trúc trang web với các thẻ ngữ nghĩa (semantic tags) như header, nav, section, article, footer giúp cải thiện SEO và khả năng tiếp cận (accessibility).

- CSS3: Sử dụng CSS custom properties (biến CSS) để quản lý theme màu sắc tập trung, hỗ trợ dark mode thông qua class html.dark. Layout sử dụng CSS Grid và Flexbox cho bố cục linh hoạt, responsive. Các hiệu ứng chuyển động (transition, transform) được áp dụng để tăng trải nghiệm người dùng.

- Bootstrap 5.3: Framework CSS phổ biến được sử dụng cho hệ thống lưới (grid system), các component UI (button, card, modal, form, badge, breadcrumb, alert), và tiện ích responsive. Bootstrap giúp đẩy nhanh quá trình phát triển giao diện và đảm bảo tính nhất quán trên các trình duyệt.

- JavaScript (Vanilla): Xử lý các tương tác phía client như thêm vào giỏ hàng (AJAX), xem nhanh sản phẩm (modal), tìm kiếm gợi ý (autocomplete), chuyển đổi dark mode, quản lý thông báo (notification bell), mobile drawer menu, và xác thực form phía client.

- Chart.js 4.4: Thư viện vẽ biểu đồ JavaScript mã nguồn mở, sử dụng HTML5 Canvas. Được dùng trong trang quản trị (Admin Dashboard) để hiển thị biểu đồ doanh thu theo tháng, giúp quản trị viên trực quan hóa dữ liệu và đưa ra quyết định kinh doanh.

- Font Awesome 6.4: Bộ icon vector phổ biến, cung cấp hàng nghìn biểu tượng scalable, được sử dụng xuyên suốt dự án cho navigation, button, badge, và các thành phần giao diện khác.

- Google Fonts (Inter, Rajdhani): Font chữ hiện đại, tối ưu cho màn hình, được tải từ Google Fonts CDN. Inter được dùng cho nội dung văn bản chính, Rajdhani được dùng cho các tiêu đề và logo với phong cách công nghệ.

2.2.5. Các giao thức và API bên thứ ba

- SMTP (Simple Mail Transfer Protocol): Đồ án triển khai một Mailer class tùy chỉnh sử dụng socket connection raw TCP để gửi email qua Gmail SMTP. Quy trình gửi email bao gồm: mở kết nối socket đến smtp.gmail.com:587, bắt tay EHLO, kích hoạt STARTTLS để mã hóa kết nối, xác thực AUTH LOGIN với App Password của Gmail, và gửi email dưới dạng MIME multipart/related hỗ trợ HTML và ảnh nhúng CID (Content-ID) cho email xác nhận đơn hàng hiển thị ảnh sản phẩm trực tiếp trong nội dung.

- Google OAuth 2.0: Giao thức ủy quyền tiêu chuẩn cho phép người dùng đăng nhập bằng tài khoản Google mà không cần tạo mật khẩu riêng. Luồng hoạt động: (1) Người dùng bấm nút "Đăng nhập với Google" → (2) Hệ thống tạo state token chống CSRF và chuyển hướng đến Google Accounts → (3) Người dùng đồng ý cấp quyền → (4) Google chuyển hướng về callback URL kèm authorization code → (5) Server đổi code lấy access token qua Google Token Endpoint → (6) Gọi Google UserInfo API lấy thông tin người dùng → (7) Tạo hoặc cập nhật tài khoản trong hệ thống.

- REST API nội bộ: Hệ thống cung cấp các endpoint API riêng (dưới route /api) trả về JSON để phục vụ các thao tác AJAX: tìm kiếm sản phẩm, quản lý giỏ hàng, kiểm tra mã giảm giá, toggle yêu thích, thêm đánh giá/bình luận, và quản lý thông báo. API được thiết kế theo phong cách RESTful với HTTP method phù hợp (GET cho đọc dữ liệu, POST cho thay đổi dữ liệu).

2.2.6. Bảo mật

Đồ án triển khai các biện pháp bảo mật sau:

- Prepared Statements: Tất cả truy vấn SQL đều sử dụng PDO prepared statements với tham số ràng buộc (parameter binding), ngăn chặn hoàn toàn SQL Injection.

- CSRF Protection: Mỗi phiên làm việc được cấp một CSRF token ngẫu nhiên (32 byte), token này được nhúng vào tất cả các form POST và được kiểm tra khi xử lý request, ngăn chặn tấn công Cross-Site Request Forgery.

- Session Security: Session được cấu hình với cookie httponly (không thể truy cập từ JavaScript), sử dụng SameSite=Lax để chống CSRF, và được tái tạo (regenerate) định kỳ mỗi 30 phút để chống session fixation.

- Password Hashing: Mật khẩu người dùng được băm bằng thuật toán bcrypt (PASSWORD_DEFAULT) với salt tự động, đảm bảo ngay cả khi cơ sở dữ liệu bị xâm phạm, mật khẩu gốc cũng không thể bị khôi phục.

- Input Sanitization: Dữ liệu đầu vào từ người dùng được làm sạch qua hàm htmlspecialchars() khi hiển thị (chống XSS) và validate (kiểm tra định dạng email, số điện thoại, kiểu dữ liệu) trước khi xử lý.

- File Upload Validation: Hệ thống kiểm tra loại MIME, kích thước file (giới hạn 5MB) và phần mở rộng trước khi lưu ảnh upload, ngăn chặn upload file độc hại.

- Directory Protection: Các thư mục nhạy cảm (config, models, controllers) được chặn truy cập trực tiếp từ trình duyệt thông qua cấu hình .htaccess.
