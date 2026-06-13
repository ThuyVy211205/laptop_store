<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập quản trị — VQSTORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0f1729; font-family: 'Inter', sans-serif; min-height: 100vh;
               display: flex; align-items: center; justify-content: center; }
        .login-box { background: #1a2440; border: 1px solid rgba(255,255,255,.08);
                     border-radius: 16px; padding: 40px 36px; width: 100%; max-width: 400px; }
        .login-logo { text-align: center; margin-bottom: 28px; }
        .login-logo img { height: 56px; }
        .login-title { color: #e2e8f0; font-size: 22px; font-weight: 700; text-align: center;
                       margin-bottom: 6px; }
        .login-sub { color: #64748b; font-size: 13.5px; text-align: center; margin-bottom: 28px; }
        .form-label { color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        .form-control { background: #0f1729; border: 1px solid rgba(255,255,255,.1); color: #e2e8f0;
                        border-radius: 8px; padding: 10px 14px; font-size: 14px; }
        .form-control:focus { background: #0f1729; border-color: #2563eb; color: #e2e8f0;
                              box-shadow: 0 0 0 3px rgba(37,99,235,.2); }
        .form-control::placeholder { color: #475569; }
        .btn-login { background: #2563eb; color: #fff; border: none; border-radius: 8px;
                     padding: 11px; font-size: 15px; font-weight: 600; width: 100%;
                     transition: background .2s; }
        .btn-login:hover { background: #1d4ed8; }
        .alert-error { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.3);
                       color: #fca5a5; border-radius: 8px; padding: 10px 14px;
                       font-size: 13.5px; margin-bottom: 18px; }
        .back-link { display: block; text-align: center; margin-top: 20px;
                     color: #64748b; font-size: 13px; text-decoration: none; }
        .back-link:hover { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <img src="<?= ASSETS_URL ?>/images/logo_VQSTORE.png" alt="VQSTORE"
                 onerror="this.style.display='none'">
        </div>
        <div class="login-title">Quản trị viên</div>
        <div class="login-sub">Đăng nhập để truy cập trang quản trị</div>

        <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= SITE_URL ?>/admin/login">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="admin@vqstore.vn" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
            </button>
        </form>

        <a href="<?= SITE_URL ?>" class="back-link">
            <i class="fas fa-arrow-left me-1"></i> Về trang chủ
        </a>
    </div>
</body>
</html>
