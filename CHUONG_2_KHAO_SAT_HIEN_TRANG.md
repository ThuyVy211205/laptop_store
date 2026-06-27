CHƯƠNG 2: KHẢO SÁT HIỆN TRẠNG VÀ XÁC ĐỊNH YÊU CẦU HỆ THỐNG

2.1. Khảo sát hiện trạng

2.1.1. Hiện trạng kinh doanh laptop và phụ kiện công nghệ

Thị trường laptop và phụ kiện công nghệ tại Việt Nam đang có tốc độ tăng trưởng mạnh mẽ, được thúc đẩy bởi nhu cầu học tập, làm việc từ xa và giải trí số. Theo số liệu thống kê, doanh số laptop tại Việt Nam tăng trung bình 15-20% mỗi năm. Các thương hiệu phổ biến bao gồm Dell, HP, ASUS, Lenovo, Acer, Apple (MacBook) và MSI cho phân khúc gaming. Bên cạnh laptop, thị trường phụ kiện như chuột, bàn phím cơ, tai nghe gaming cũng ghi nhận mức tăng trưởng ấn tượng.

Tuy nhiên, phần lớn các cửa hàng kinh doanh laptop và phụ kiện hiện nay vẫn phụ thuộc vào kênh bán hàng truyền thống (cửa hàng vật lý, Facebook, Zalo). Các vấn đề tồn tại bao gồm:

- Quản lý sản phẩm và tồn kho thủ công, dễ sai sót, không cập nhật real-time giữa các kênh bán hàng.
- Quy trình đặt hàng qua tin nhắn, điện thoại gây mất thời gian, không chuyên nghiệp, dễ nhầm lẫn thông tin khách hàng.
- Thiếu công cụ quản lý khách hàng tập trung — không lưu được lịch sử mua hàng, không có chương trình khách hàng thân thiết.
- Thiếu hệ thống báo cáo, thống kê doanh thu — chủ cửa hàng không có dữ liệu để ra quyết định kinh doanh.
- Không có website chuyên nghiệp để tiếp cận khách hàng online, mất cơ hội cạnh tranh với các sàn thương mại điện tử lớn.

2.1.2. Hiện trạng các hệ thống tương tự

Hiện nay trên thị trường có nhiều nền tảng thương mại điện tử phổ biến mà các cửa hàng có thể lựa chọn:

- Shopify: Nền tảng SaaS (Software as a Service) của Canada, cho phép tạo website bán hàng nhanh chóng mà không cần kiến thức lập trình. Ưu điểm: giao diện đẹp, tích hợp thanh toán, hosting sẵn. Nhược điểm: chi phí hàng tháng cao (từ $29/tháng), phí giao dịch, khó tùy chỉnh sâu theo nhu cầu đặc thù của thị trường Việt Nam.

- Haravan, Sapo: Các nền tảng thương mại điện tử nội địa, được thiết kế riêng cho thị trường Việt Nam. Ưu điểm: hỗ trợ tiếng Việt, tích hợp vận chuyển nội địa (GHN, GHTK, Viettel Post). Nhược điểm: chi phí duy trì, giới hạn tùy biến, phụ thuộc vào nhà cung cấp.

- WooCommerce: Plugin thương mại điện tử mã nguồn mở cho WordPress. Ưu điểm: miễn phí, cộng đồng lớn, nhiều plugin mở rộng. Nhược điểm: yêu cầu kiến thức WordPress, hiệu năng kém với lượng sản phẩm lớn, quản lý phức tạp hơn so với giải pháp PHP thuần cho dự án học thuật.

- Shopee, Lazada, TikTok Shop: Các sàn thương mại điện tử B2C/C2C. Ưu điểm: lượng truy cập khổng lồ, hệ sinh thái thanh toán và vận chuyển tích hợp. Nhược điểm: phí hoa hồng cao (5-15%), cạnh tranh khốc liệt về giá, hạn chế xây dựng thương hiệu riêng, không sở hữu dữ liệu khách hàng.

Từ phân tích trên, nhận thấy nhu cầu xây dựng một website thương mại điện tử riêng, phù hợp với quy mô vừa và nhỏ, chi phí thấp, toàn quyền kiểm soát mã nguồn và dữ liệu khách hàng. Đồ án "VQSTORE — Cửa hàng Laptop & Phụ kiện Công nghệ" được phát triển nhằm đáp ứng nhu cầu này, đồng thời phục vụ mục tiêu học thuật.

2.2. Mô tả hệ thống

2.2.1. Tổng quan hệ thống

VQSTORE là một website thương mại điện tử chuyên kinh doanh laptop và phụ kiện công nghệ, xây dựng trên nền tảng PHP thuần theo mô hình MVC. Hệ thống phục vụ hai nhóm đối tượng chính:

- Khách hàng: Có thể duyệt và tìm kiếm sản phẩm theo danh mục (laptop, phụ kiện), thương hiệu, khoảng giá; xem chi tiết sản phẩm với đầy đủ thông số kỹ thuật, đánh giá, bình luận; thêm sản phẩm vào giỏ hàng; đặt hàng với nhiều phương thức thanh toán; theo dõi trạng thái đơn hàng; quản lý tài khoản cá nhân; và nhận thông báo về đơn hàng.

- Quản trị viên (Admin): Có thể quản lý toàn bộ hệ thống thông qua bảng điều khiển (dashboard) với các chức năng: quản lý sản phẩm và danh mục, quản lý đơn hàng và cập nhật trạng thái, quản lý khách hàng và nhân viên, quản lý mã giảm giá, duyệt đánh giá và tin nhắn liên hệ, xem báo cáo doanh thu và gửi email marketing đến khách hàng.

2.2.2. Phạm vi hệ thống

Hệ thống được triển khai dưới dạng web application, chạy trên môi trường XAMPP (Apache + MySQL + PHP). Các chức năng chính bao gồm:

- Front-end (phía khách hàng): Trang chủ, danh sách sản phẩm (có lọc và phân trang), chi tiết sản phẩm, giỏ hàng, thanh toán, quản lý đơn hàng, tài khoản cá nhân (hồ sơ, lịch sử mua hàng, yêu thích, thông báo), tra cứu đơn hàng công khai, khuyến mãi, liên hệ, đăng ký/đăng nhập (bao gồm Google OAuth).

- Back-end (phía quản trị): Dashboard thống kê, quản lý sản phẩm (CRUD + upload ảnh gallery), quản lý đơn hàng (cập nhật trạng thái + gửi thông báo), quản lý khách hàng, quản lý nhân viên, quản lý voucher/phiếu giảm giá, quản lý đánh giá, quản lý tin nhắn liên hệ, báo cáo doanh thu (biểu đồ + thống kê).

2.3. Yêu cầu của hệ thống mới

2.3.1. Mục tiêu

Hệ thống VQSTORE được xây dựng nhằm đạt được các mục tiêu sau:

- Số hóa toàn bộ quy trình bán hàng từ trưng bày sản phẩm, đặt hàng, thanh toán đến giao hàng, giảm thiểu sai sót thủ công và tiết kiệm thời gian cho cả khách hàng và nhân viên.

- Xây dựng một kênh bán hàng online chuyên nghiệp, tăng khả năng tiếp cận khách hàng trên toàn quốc, không giới hạn bởi vị trí địa lý của cửa hàng vật lý.

- Cung cấp công cụ quản lý tập trung cho chủ cửa hàng: theo dõi tồn kho real-time, thống kê doanh thu theo ngày/tuần/tháng/năm, quản lý khách hàng và lịch sử giao dịch.

- Xây dựng chương trình khách hàng thân thiết với hệ thống xếp hạng thành viên (Silver/Gold/Diamond) dựa trên tổng chi tiêu, khuyến khích khách hàng quay lại mua sắm.

- Tạo nền tảng có khả năng mở rộng trong tương lai: tích hợp thêm cổng thanh toán trực tuyến (Momo, VNPay, ZaloPay), phát triển ứng dụng di động, hoặc tích hợp với các kênh bán hàng khác.

2.3.2. Phạm vi áp dụng

Hệ thống hướng đến các đối tượng:

- Cửa hàng/công ty kinh doanh laptop và phụ kiện công nghệ quy mô vừa và nhỏ, có nhu cầu mở rộng kênh bán hàng online nhưng chưa có website riêng.
- Các dự án khởi nghiệp (startup) trong lĩnh vực thương mại điện tử với ngân sách hạn chế, cần một giải pháp mã nguồn mở, chi phí thấp, dễ tùy chỉnh.
- Môi trường học thuật: Sinh viên ngành Công nghệ Thông tin nghiên cứu kiến trúc MVC, quy trình phát triển web, và triển khai hệ thống thương mại điện tử thực tế.

2.4. Yêu cầu đối với người dùng

2.4.1. Yêu cầu đối với khách hàng

- Có thiết bị kết nối Internet (máy tính, laptop, điện thoại thông minh, máy tính bảng) với trình duyệt web hiện đại (Chrome, Firefox, Edge, Safari).
- Đối với các chức năng mua hàng: cần có địa chỉ email hợp lệ để nhận thông tin xác nhận đơn hàng và số điện thoại để liên hệ giao hàng.
- Đối với chức năng đặt hàng và quản lý tài khoản: cần đăng ký và đăng nhập tài khoản.
- Không yêu cầu kiến thức chuyên môn về công nghệ thông tin — giao diện được thiết kế trực quan, thân thiện với người dùng phổ thông.

2.4.2. Yêu cầu đối với quản trị viên

- Có kiến thức cơ bản về sử dụng máy tính và trình duyệt web.
- Được đào tạo ngắn về các chức năng quản trị: thêm/sửa/xóa sản phẩm, cập nhật trạng thái đơn hàng, quản lý khách hàng và mã giảm giá, xem báo cáo doanh thu.
- Có tài khoản quản trị viên được tạo bởi Super Admin, với phân quyền phù hợp theo vai trò.

2.5. Yêu cầu hệ thống

2.5.1. Yêu cầu phần cứng (phía máy chủ)

- CPU: Tối thiểu 2 nhân, khuyến nghị 4 nhân trở lên.
- RAM: Tối thiểu 2GB, khuyến nghị 4GB trở lên.
- Ổ cứng: Tối thiểu 10GB trống (cho hệ điều hành, web server, database và ảnh sản phẩm).
- Kết nối Internet ổn định với băng thông tối thiểu 10 Mbps.

2.5.2. Yêu cầu phần mềm (phía máy chủ)

- Hệ điều hành: Windows, Linux hoặc macOS.
- Web server: Apache 2.4+ với module mod_rewrite được kích hoạt.
- PHP: Phiên bản 8.0 trở lên, với các extension: PDO, MySQLi, OpenSSL, FileInfo, cURL, GD/Imagick (cho xử lý ảnh), Mbstring.
- MySQL: Phiên bản 5.7+ hoặc MariaDB 10.3+, với engine InnoDB hỗ trợ transaction và foreign key.
- Công cụ quản lý: phpMyAdmin (tùy chọn) để quản lý database qua giao diện web.

2.5.3. Yêu cầu phía người dùng (client)

- Trình duyệt web: Google Chrome 90+, Mozilla Firefox 88+, Microsoft Edge 90+, Safari 14+. JavaScript phải được kích hoạt.
- Độ phân giải màn hình: Tối thiểu 360px (hỗ trợ mobile), khuyến nghị 1366px trở lên cho trải nghiệm desktop đầy đủ.
- Không yêu cầu cài đặt thêm plugin hay phần mềm đặc biệt.

2.6. Yêu cầu chức năng

Dựa trên phân tích hiện trạng và mục tiêu hệ thống, các yêu cầu chức năng được phân thành hai nhóm:

2.6.1. Nhóm chức năng phía khách hàng (Front-end)

| Mã | Chức năng | Mô tả |
|---|---|---|
| F01 | Xem trang chủ | Hiển thị banner, danh mục sản phẩm, sản phẩm nổi bật, sản phẩm mới, sản phẩm bán chạy |
| F02 | Duyệt danh sách sản phẩm | Hiển thị danh sách sản phẩm theo danh mục (laptop, phụ kiện), có phân trang |
| F03 | Lọc và sắp xếp sản phẩm | Lọc theo danh mục, khoảng giá, thương hiệu; sắp xếp theo giá, mới nhất, bán chạy |
| F04 | Tìm kiếm sản phẩm | Tìm kiếm theo từ khóa, có gợi ý tự động (autocomplete) |
| F05 | Xem chi tiết sản phẩm | Hiển thị ảnh gallery, thông số kỹ thuật, giá, tồn kho, biến thể màu sắc, sản phẩm liên quan |
| F06 | Đánh giá và bình luận | Khách hàng đã mua có thể đánh giá sao, viết bình luận, phản hồi bình luận |
| F07 | Xem nhanh sản phẩm | Modal popup hiển thị thông tin cơ bản và nút thêm vào giỏ hàng |
| F08 | Giỏ hàng | Thêm, cập nhật số lượng, xóa sản phẩm; hỗ trợ cả khách chưa đăng nhập (session) và đã đăng nhập (database) |
| F09 | Mua ngay | Chuyển thẳng đến trang thanh toán với một sản phẩm được chọn |
| F10 | Thanh toán | Điền thông tin giao hàng, chọn phương thức thanh toán, áp dụng mã giảm giá |
| F11 | Đặt hàng | Tạo đơn hàng với transaction đảm bảo toàn vẹn dữ liệu, gửi email xác nhận |
| F12 | Theo dõi đơn hàng | Xem danh sách đơn hàng, chi tiết đơn, timeline trạng thái |
| F13 | Hủy đơn hàng | Hủy đơn ở trạng thái "Chờ xác nhận", hoàn tồn kho |
| F14 | Tra cứu đơn hàng | Tra cứu công khai theo mã đơn, không cần đăng nhập |
| F15 | Danh sách yêu thích | Thêm/xóa sản phẩm vào wishlist |
| F16 | Quản lý tài khoản | Xem/sửa hồ sơ, đổi mật khẩu, upload ảnh đại diện |
| F17 | Lịch sử mua hàng | Xem tất cả sản phẩm đã mua từ đơn hoàn thành, giá tại thời điểm mua, tổng chi tiêu |
| F18 | Thông báo | Nhận thông báo về trạng thái đơn hàng, phản hồi từ admin |
| F19 | Đăng ký | Tạo tài khoản mới với email (@gmail.com), mật khẩu, và thông tin cá nhân |
| F20 | Đăng nhập | Đăng nhập bằng email/mật khẩu hoặc Google OAuth |
| F21 | Quên mật khẩu | Gửi link đặt lại mật khẩu qua email |
| F22 | Liên hệ | Gửi tin nhắn đến admin qua form liên hệ |
| F23 | Xem khuyến mãi | Trang hiển thị sản phẩm giảm giá, voucher đang kích hoạt |
| F24 | Dark mode | Chuyển đổi giao diện sáng/tối |

2.6.2. Nhóm chức năng quản trị (Admin Back-end)

| Mã | Chức năng | Mô tả |
|---|---|---|
| A01 | Đăng nhập Admin | Đăng nhập bằng tài khoản quản trị viên |
| A02 | Dashboard | Tổng quan: doanh thu, đơn hàng, sản phẩm, khách hàng; biểu đồ doanh thu; top sản phẩm bán chạy và bán chậm |
| A03 | Quản lý sản phẩm | Thêm, sửa, xóa sản phẩm; upload ảnh đại diện + gallery; quản lý tồn kho, giá, trạng thái |
| A04 | Quản lý danh mục | Thêm, sửa, xóa danh mục sản phẩm |
| A05 | Quản lý đơn hàng | Xem danh sách, lọc theo trạng thái/ngày; cập nhật trạng thái đơn; xem chi tiết đơn |
| A06 | Hủy đơn hàng | Hủy đơn từ admin kèm lý do, hoàn tồn kho, hoàn chỉ số khách hàng |
| A07 | Quản lý khách hàng | Xem danh sách, tìm kiếm, khóa/mở khóa tài khoản, xem hạng thành viên |
| A08 | Quản lý nhân viên | Thêm nhân viên mới, cấp tài khoản admin |
| A09 | Quản lý voucher | Tạo mã giảm giá (% hoặc số tiền cố định), giới hạn số lượng, thời hạn |
| A10 | Quản lý đánh giá | Xem, xóa đánh giá của khách hàng |
| A11 | Quản lý tin nhắn liên hệ | Xem, đánh dấu đã đọc, trả lời và gửi thông báo đến khách hàng |
| A12 | Báo cáo doanh thu | Thống kê doanh thu theo ngày/tuần/tháng/năm, biểu đồ, số liệu đơn hàng thành công/hủy |
| A13 | Gửi email marketing | Gửi email đến một hoặc tất cả khách hàng đang hoạt động |

2.7. Yêu cầu phi chức năng

2.7.1. Hiệu năng (Performance)

- Thời gian tải trang: Trang chủ và trang danh sách sản phẩm phải tải trong vòng dưới 3 giây trên kết nối Internet thông thường (10 Mbps).
- Khả năng xử lý đồng thời: Hệ thống phải hỗ trợ ít nhất 50 người dùng truy cập đồng thời mà không giảm đáng kể thời gian phản hồi.
- Tối ưu tài nguyên: Sử dụng lazy loading cho ảnh, CSS/JS minification, và cache cho tài nguyên tĩnh thông qua cấu hình Apache mod_expires.
- Phân trang: Các danh sách dữ liệu lớn (sản phẩm, đơn hàng) phải được phân trang với kích thước trang hợp lý (8-20 items/trang) để tránh quá tải.

2.7.2. Bảo mật (Security)

- Chống SQL Injection: Tất cả truy vấn database sử dụng PDO prepared statements với parameter binding.
- Chống XSS (Cross-Site Scripting): Mọi dữ liệu đầu ra hiển thị trên HTML đều được escape qua hàm htmlspecialchars().
- Chống CSRF (Cross-Site Request Forgery): Mỗi form POST được nhúng CSRF token và kiểm tra token trước khi xử lý.
- Mã hóa mật khẩu: Sử dụng thuật toán bcrypt (PASSWORD_DEFAULT) để băm mật khẩu trước khi lưu.
- Session security: Session cookie được thiết lập httponly, SameSite=Lax, và tự động regenerate định kỳ.
- Phân quyền truy cập: Tất cả route admin đều kiểm tra xác thực trước khi thực thi.
- Bảo vệ thư mục nhạy cảm: Cấu hình .htaccess chặn truy cập trực tiếp vào các thư mục config, models, controllers.

2.7.3. Khả năng sử dụng (Usability)

- Giao diện người dùng (UI): Thiết kế theo phong cách hiện đại, tối giản (modern minimalist), lấy cảm hứng từ các trang thương mại điện tử lớn. Màu sắc chủ đạo: xanh dương (#2563eb) làm điểm nhấn.
- Trải nghiệm người dùng (UX): Các thao tác quan trọng (thêm vào giỏ, đặt hàng) có thể hoàn thành trong ít hơn 3 bước. Có thông báo phản hồi (flash message) sau mỗi hành động.
- Responsive: Giao diện tự động thích ứng với mọi kích thước màn hình (mobile, tablet, desktop) thông qua Bootstrap 5 Grid System và CSS media queries.
- Dark mode: Hỗ trợ chế độ nền tối để giảm mỏi mắt khi sử dụng vào ban đêm, có thể chuyển đổi bằng một nút bấm.
- Breadcrumb navigation: Hiển thị đường dẫn điều hướng giúp người dùng biết vị trí hiện tại và dễ dàng quay lại.
- Form validation: Kiểm tra dữ liệu nhập cả phía client (JavaScript) và server (PHP), hiển thị thông báo lỗi rõ ràng.

2.7.4. Độ tin cậy (Reliability)

- Tính toàn vẹn dữ liệu: Sử dụng transaction (begin/commit/rollback) cho các thao tác quan trọng như đặt hàng và hủy đơn — nếu một bước thất bại, toàn bộ thay đổi được rollback.
- Tính nhất quán: Foreign key constraints đảm bảo không có dữ liệu mồ côi (orphan records). Ví dụ: không thể xóa sản phẩm nếu vẫn còn chi tiết đơn hàng tham chiếu đến.
- Xử lý lỗi: Hệ thống có cơ chế bắt lỗi (try-catch) ở các điểm quan trọng, hiển thị thông báo lỗi thân thiện ở chế độ DEBUG và chuyển hướng an toàn ở chế độ production.
- Sao lưu dữ liệu: Cơ sở dữ liệu có thể được sao lưu định kỳ thông qua phpMyAdmin hoặc script cron job.

2.7.5. Khả năng mở rộng và bảo trì (Scalability & Maintainability)

- Kiến trúc MVC rõ ràng: Phân tách Model-View-Controller giúp dễ dàng thêm tính năng mới mà không ảnh hưởng đến code hiện có.
- Tổ chức mã nguồn: Cấu trúc thư mục rõ ràng (models, views, controllers, config, thu_vien, ho_tro, assets) giúp định vị và sửa lỗi nhanh chóng.
- Component-based views: Các thành phần giao diện được tái sử dụng (thẻ sản phẩm, thanh điều hướng, sidebar, footer) — thay đổi một file sẽ áp dụng cho toàn bộ hệ thống.
- Database migration: Các file SQL migration được lưu trong thư mục co_so_du_lieu để dễ dàng cập nhật cấu trúc database khi có thay đổi.
- Tương thích: Mã nguồn tuân thủ chuẩn PSR (PHP Standards Recommendation) cơ bản, không phụ thuộc vào framework cụ thể — có thể dễ dàng chuyển đổi hoặc tích hợp với các công nghệ khác.
