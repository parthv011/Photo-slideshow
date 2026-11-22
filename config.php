<?php
// Loads .env, starts session, shared helpers.
$f = __DIR__ . '/.env';
if (is_file($f)) foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
    if ($l[0] === '#' || !str_contains($l, '=')) continue;
    [$k, $v] = explode('=', $l, 2); $_ENV[trim($k)] = trim($v);
}
function env($k, $d = null) { return $_ENV[$k] ?? $d; }
define('MAX_FILE_BYTES', (int)env('MAX_FILE_MB', 5) * 1024 * 1024);
define('MAX_FILES', (int)env('MAX_FILES', 5));
define('SLIDE_SECONDS', (int)env('SLIDE_SECONDS', 8));
define('UPLOAD_DIR', __DIR__ . '/uploads/');
session_start();

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf() { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function db(): PDO {
    static $pdo;
    return $pdo ??= new PDO(
        'mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_NAME', 'photo_slideshow') . ';charset=utf8mb4',
        env('DB_USER', 'root'), env('DB_PASS', ''),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}
function flash(string $type, string $msg, array $extra = []) { $_SESSION['flash'] = compact('type', 'msg', 'extra'); }
