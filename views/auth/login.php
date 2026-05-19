<?php
$extraCss = ['auth.css'];
include ROOT_PATH . '/views/layouts/header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card-tech">
                    <div class="auth-header-tech">
                        <div class="auth-logo-tech">
                            <span class="logo-tech">TECH</span><span class="logo-store">STORE</span>
                        </div>
                        <h3 class="auth-title-tech">Đăng nhập</h3>
                        <p class="auth-subtitle-tech">Chào mừng trở lại! Hãy đăng nhập để tiếp tục mua sắm</p>
                    </div>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= SITE_URL ?>/auth/login" id="loginForm">
                        <div class="form-group-tech">
                            <label class="form-label-tech">Email</label>
                            <div class="input-wrap">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" class="form-control form-tech"
                                       placeholder="email@example.com" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group-tech">
                            <label class="form-label-tech">Mật khẩu</label>
                            <div class="input-wrap">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password" id="passwordInput"
                                       class="form-control form-tech" placeholder="••••••••" required>
                                <button type="button" class="btn-show-pwd" id="togglePwd">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-tech">
                                <input type="checkbox" name="remember"> <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <a href="<?= SITE_URL ?>/auth/forgotPassword" class="forgot-link">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="btn btn-tech w-100 mt-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </button>

                        <div class="auth-divider"><span>HOẶC</span></div>

                        <button type="button" class="btn btn-google w-100">
                            <i class="fab fa-google me-2"></i>Đăng nhập với Google
                        </button>

                        <p class="auth-footer-text">
                            Chưa có tài khoản?
                            <a href="<?= SITE_URL ?>/auth/register" class="text-warning">Đăng ký ngay</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password
document.getElementById('togglePwd').addEventListener('click', function() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
});
</script>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>