<?php
/**
 * Database Configuration & Connection
 * VQSTORE - Laptop & Tech Accessories Store
 */

// ===== Site Configuration =====
define('SITE_NAME', 'VQSTORE');
define('SITE_URL', 'http://localhost/laptop_store');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_URL', SITE_URL . '/uploads');
define('ASSETS_URL', SITE_URL . '/assets');

// ===== Path Configuration =====
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('ADMIN_PATH', ROOT_PATH . '/admin');

// ===== Database Configuration =====
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'laptop_store');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ===== Other Settings =====
define('LOW_STOCK_WARNING', 5);
define('ITEMS_PER_PAGE', 12);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// ===== Session & Timezone =====
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== Error Reporting (set false in production) =====
define('DEBUG', true);
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ===== Mail / SMTP Configuration =====
define('MAIL_FROM_NAME', 'VQSTORE');
define('SMTP_HOST',      'smtp.gmail.com');
define('SMTP_PORT',      587);

// Tài khoản gửi email marketing (admin → khách) — Yêu cầu 2
define('SMTP_USER',      'vyphan21222005@gmail.com');
define('SMTP_PASS',      'yfsdvgxfrgjizshb');

// Tài khoản no-reply (xác nhận đơn hàng tự động) — Yêu cầu 1
define('SMTP_NOREPLY_USER', 'vyphan21222005@gmail.com');
define('SMTP_NOREPLY_PASS', 'yfsdvgxfrgjizshb');
define('MAIL_FROM',          'vyphan21222005@gmail.com');

// ===== Google OAuth (placeholder) =====
define('GOOGLE_CLIENT_ID',     '80367818855-pdd5786ncki43103lstsae3gom0180r9.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/auth/googleCallback');

/**
 * Database Class - PDO Singleton
 */
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed.");
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert($table, $data)
    {
        $cols = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($cols) VALUES ($placeholders)";
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function execute($sql, $params = [])
    {
        return $this->query($sql, $params)->rowCount();
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    public function commit()
    {
        return $this->pdo->commit();
    }
    public function rollback()
    {
        return $this->pdo->rollBack();
    }
}

/**
 * Helper function to get DB instance
 */
function db()
{
    return Database::getInstance();
}