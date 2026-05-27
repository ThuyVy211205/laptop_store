</main><!-- end main-content -->

<!-- ===================== FOOTER ===================== -->
<footer class="footer-tech">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <!-- About -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-logo mb-3">
                        <span class="logo-tech">VQ</span><span class="logo-store">STORE</span>
                    </div>
                    <p class="footer-desc">
                        Cửa hàng phân phối Laptop, Phụ kiện công nghệ chính hãng hàng đầu Việt Nam.
                        Cam kết sản phẩm chính hãng 100%, bảo hành toàn quốc.
                    </p>
                    <div class="footer-social mt-3">
                        <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-link" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-title">Về chúng tôi</h6>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>/about">Giới thiệu</a></li>
                        <li><a href="<?= SITE_URL ?>/news">Tin tức</a></li>
                        <li><a href="<?= SITE_URL ?>/recruit">Tuyển dụng</a></li>
                        <li><a href="<?= SITE_URL ?>/store">Hệ thống cửa hàng</a></li>
                        <li><a href="<?= SITE_URL ?>/contact">Liên hệ</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-title">Chính sách</h6>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>/policy/shipping">Vận chuyển</a></li>
                        <li><a href="<?= SITE_URL ?>/policy/return">Đổi trả</a></li>
                        <li><a href="<?= SITE_URL ?>/policy/warranty">Bảo hành</a></li>
                        <li><a href="<?= SITE_URL ?>/policy/payment">Thanh toán</a></li>
                        <li><a href="<?= SITE_URL ?>/policy/privacy">Bảo mật</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-title">Liên hệ</h6>
                    <ul class="footer-contact-list">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Nguyễn Văn Cừ, Q.5, TP.HCM</li>
                        <li><i class="fas fa-phone"></i> Hotline: <strong>1900 9999</strong></li>
                        <li><i class="fas fa-envelope"></i> support@techstore.vn</li>
                        <li><i class="fas fa-clock"></i> 8:00 - 22:00 (Tất cả các ngày)</li>
                    </ul>

                    <!-- Newsletter -->
                    <div class="newsletter mt-3">
                        <p class="mb-2"><strong>Đăng ký nhận khuyến mãi</strong></p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Email của bạn" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment / Trust -->
    <div class="footer-payment">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-2 mb-md-0"><strong>Hình thức thanh toán:</strong></p>
                    <div class="payment-icons">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-paypal"></i>
                        <span class="badge bg-secondary">COD</span>
                        <span class="badge" style="background:#a50064">MoMo</span>
                        <span class="badge" style="background:#005baa">VNPay</span>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <strong>Bộ Công Thương đã thông báo</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <small>&copy; <?= date('Y') ?> VQSTORE.vn — All Rights Reserved.</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small>Made with <i class="fas fa-heart text-danger"></i> in Vietnam</small>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top -->
<button class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- ===================== SCRIPTS ===================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/js/main.js"></script>
<script src="<?= ASSETS_URL ?>/js/cart.js?v=2"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= ASSETS_URL ?>/js/<?= $js ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>