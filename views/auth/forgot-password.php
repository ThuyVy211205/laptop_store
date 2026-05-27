<?php
$extraCss = ['auth.css'];
$hideNav  = true;
include ROOT_PATH . '/views/layouts/header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card-tech">
                    <div class="auth-header-tech">
                        <div class="auth-icon-circle">
                            <i class="fas fa-key"></i>
                        </div>
                        <h3 class="auth-title-tech">Quên mật khẩu?</h3>
                        <p class="auth-subtitle-tech">
                            Nhập email của bạn, chúng tôi sẽ gửi link đặt lại mật khẩu
                        </p>
                    </div>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($message)): ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= SITE_URL ?>/auth/forgotPassword">
                        <div class="form-group-tech">
                            <label class="form-label-tech">Email</label>
                            <div class="input-wrap">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" class="form-control form-tech"
                                       placeholder="email@example.com" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-tech w-100 mt-3">
                            <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu
                        </button>

                        <p class="auth-footer-text">
                            Nhớ ra mật khẩu?
                            <a href="<?= SITE_URL ?>/auth/login" class="text-warning">Đăng nhập</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>