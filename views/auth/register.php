<?php
$extraCss = ['auth.css'];
$hideNav  = true;
include ROOT_PATH . '/views/layouts/header.php';
?>


<div class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="auth-card-tech">
                    <div class="auth-header-tech">
                        <div class="auth-logo-tech">
                            <span class="logo-tech">VQ</span><span class="logo-store">STORE</span>
                        </div>
                        <h3 class="auth-title-tech">Đăng ký tài khoản</h3>
                        <p class="auth-subtitle-tech">Tạo tài khoản để nhận ưu đãi độc quyền</p>
                    </div>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= SITE_URL ?>/auth/register" id="registerForm">
                        <div class="form-group-tech">
                            <label class="form-label-tech">Họ và tên <span class="text-danger">*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="full_name" class="form-control form-tech"
                                       placeholder="Nguyễn Văn A" required
                                       value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-tech">
                                    <label class="form-label-tech">Email <span class="text-danger">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email" name="email" class="form-control form-tech"
                                               placeholder="email@example.com" required
                                               value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-tech">
                                    <label class="form-label-tech">Số điện thoại</label>
                                    <div class="input-wrap">
                                        <i class="fas fa-phone input-icon"></i>
                                        <input type="tel" name="phone" class="form-control form-tech"
                                               placeholder="0901234567"
                                               value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-tech">
                                    <label class="form-label-tech">Mật khẩu <span class="text-danger">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" name="password" id="pwdInput"
                                               class="form-control form-tech" placeholder="Ít nhất 6 ký tự"
                                               required minlength="6">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-tech">
                                    <label class="form-label-tech">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" name="confirm_password" id="pwdConfirmInput"
                                               class="form-control form-tech" placeholder="Nhập lại mật khẩu"
                                               required minlength="6">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password strength -->
                        <div class="pwd-strength" id="pwdStrength">
                            <div class="pwd-strength-bar"><div class="pwd-strength-fill" id="pwdFill"></div></div>
                            <small id="pwdText" class="text-muted">Độ mạnh mật khẩu</small>
                        </div>

                        <label class="checkbox-tech mt-3">
                            <input type="checkbox" name="agree_terms" required>
                            <span>Tôi đồng ý với <a href="<?= SITE_URL ?>/policy/terms" class="text-warning">điều khoản sử dụng</a> và <a href="<?= SITE_URL ?>/policy/privacy" class="text-warning">chính sách bảo mật</a></span>
                        </label>

                        <button type="submit" class="btn btn-tech w-100 mt-3">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký
                        </button>

                        <p class="auth-footer-text">
                            Đã có tài khoản?
                            <a href="<?= SITE_URL ?>/auth/login" class="text-warning">Đăng nhập ngay</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password strength meter
document.getElementById('pwdInput').addEventListener('input', function() {
    const val = this.value;
    let strength = 0;
    if (val.length >= 6) strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const fill = document.getElementById('pwdFill');
    const text = document.getElementById('pwdText');
    const widths = ['0%','20%','40%','60%','80%','100%'];
    const colors = ['#ef4444','#ef4444','#f59e0b','#f59e0b','#10b981','#10b981'];
    const labels = ['Quá yếu','Yếu','Trung bình','Khá','Mạnh','Rất mạnh'];

    fill.style.width = widths[strength];
    fill.style.background = colors[strength];
    text.textContent = labels[strength];
    text.style.color = colors[strength];
});

// Check password match
document.getElementById('pwdConfirmInput').addEventListener('input', function() {
    const pwd = document.getElementById('pwdInput').value;
    if (this.value && this.value !== pwd) {
        this.style.borderColor = '#ef4444';
    } else {
        this.style.borderColor = '';
    }
});
</script>

