<?php
// Sao chép file này thành secrets.php và điền thông tin thật vào

// ===== Database =====
define('DB_HOST',    '127.0.0.1');
define('DB_NAME',    'laptop_store');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ===== SMTP (Gmail App Password) =====
define('SMTP_USER',         'your_email@gmail.com');
define('SMTP_PASS',         'your_app_password');
define('SMTP_NOREPLY_USER', 'your_email@gmail.com');
define('SMTP_NOREPLY_PASS', 'your_app_password');
define('MAIL_FROM',         'your_email@gmail.com');

// ===== Google OAuth =====
define('GOOGLE_CLIENT_ID',     'your_google_client_id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret');
