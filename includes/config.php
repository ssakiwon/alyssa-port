<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Manila');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/* ---------- Site info ---------- */
$site = [
    'name'      => 'Alyssa Abundo',
    'email'     => 'alexisssaki03@gmail.com',
    'facebook'  => 'https://www.facebook.com/assylasixela.odnuba.77',
    'instagram' => 'https://www.instagram.com/yssatwotatlo?igsh=MTBwdTdxZGo2MTV6cA==',
];

/* ---------- Database (XAMPP defaults: change if yours differ) ---------- */
$dbConfig = [
    'host' => 'localhost',
    'name' => 'alyssa_portfolio',
    'user' => 'root',
    'pass' => '',
];

// ADMIN ACCOUNT: every inquiry goes to this account's inbox (inquiries.php).
// It is created automatically the first time the site runs. The password is only
// used at creation, so change it here BEFORE the first run (or later, update the
// password_hash of this user in phpMyAdmin).
$admin = [
    'name'     => 'Alyssa Abundo',
    'email'    => 'alexisssaki03@gmail.com',
    'password' => 'admin123',
];

set_exception_handler(function (Throwable $ex) {
    error_log((string) $ex);   // full details go to XAMPP's php_error.log
    http_response_code(500);
    $msg = $ex instanceof PDOException
        ? 'Could not reach the database. Is MySQL running in XAMPP?'
        : 'Something went wrong. Please try again.';
    echo '<p style="font-family:sans-serif;padding:2rem">' . htmlspecialchars($msg) . '</p>';
});

/* ---------- Helpers ---------- */
function e(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Single-line input: strip tags and line breaks
function clean_line(?string $v): string {
    $v = strip_tags(trim($v ?? ''));
    return str_replace(["\r", "\n"], ' ', $v);
}

function first_name(string $name): string {
    return explode(' ', trim($name))[0] ?: $name;
}

function redirect(string $to): never {
    header('Location: ' . $to);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool {
    return is_string($token) && hash_equals(csrf_token(), $token);
}

function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function get_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Database ---------- */
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    global $dbConfig;
    $c    = $dbConfig;
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $dsn = "mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
    } catch (PDOException $ex) {
        $unknownDb = (int) $ex->getCode() === 1049 || str_contains($ex->getMessage(), 'Unknown database');
        if (!$unknownDb) throw $ex;

        // Database doesn't exist yet: create it, then connect
        $boot = new PDO("mysql:host={$c['host']};charset=utf8mb4", $c['user'], $c['pass'], $opts);
        $boot->exec("CREATE DATABASE IF NOT EXISTS `{$c['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
    }

    install_schema($pdo);
    return $pdo;
}

function install_schema(PDO $pdo): void {
    global $admin;

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name          VARCHAR(100) NOT NULL,
        email         VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role          ENUM('user','admin') NOT NULL DEFAULT 'user',
        created_at    DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS inquiries (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id    INT UNSIGNED NULL,
        name       VARCHAR(100) NOT NULL,
        email      VARCHAR(190) NOT NULL,
        subject    VARCHAR(150) NOT NULL,
        message    TEXT NOT NULL,
        is_read    TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_inquiry_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Make sure the admin account exists and really is an admin
    $adminEmail = strtolower($admin['email']);
    $st = $pdo->prepare('SELECT id, role FROM users WHERE email = ?');
    $st->execute([$adminEmail]);
    $row = $st->fetch();

    if (!$row) {
        $pdo->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?)')
            ->execute([$admin['name'], $adminEmail, password_hash($admin['password'], PASSWORD_DEFAULT), 'admin', date('Y-m-d H:i:s')]);
    } elseif ($row['role'] !== 'admin') {
        $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$row['id']]);
    }
}

/* ---------- Auth ---------- */
function current_user(): ?array {
    static $user = false;
    if ($user !== false) return $user;

    $user = null;
    if (!empty($_SESSION['user_id'])) {
        $st = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $st->execute([$_SESSION['user_id']]);
        $user = $st->fetch() ?: null;
        if (!$user) unset($_SESSION['user_id']);
    }
    return $user;
}

function login_user(array $row): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $row['id'];
}

function require_login(): void {
    if (!current_user()) {
        flash('error', 'Please log in first.');
        redirect('login.php?next=' . urlencode(basename($_SERVER['SCRIPT_NAME'])));
    }
}
