<?php
require __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

function fail(string $m) { flash('danger', $m); header('Location: index.php'); exit; }

if (!hash_equals(csrf(), $_POST['csrf'] ?? '')) fail('Your session expired. Please try again.');

// Basic rate limit: 3 uploads per minute per session (protects against floods)
$now = time();
$_SESSION['hits'] = array_values(array_filter($_SESSION['hits'] ?? [], fn($t) => $t > $now - 60));
if (count($_SESSION['hits']) >= 3) fail('Too many uploads. Please wait a minute and try again.');

$name  = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
if (mb_strlen($name) < 1 || mb_strlen($name) > 60) fail('Please enter your name (up to 60 characters).');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Please enter a valid email. We use it only to recognise you next time.');
if (empty($_POST['consent'])) fail('Please tick the consent box. Photos are shown publicly on the display.');

$f = $_FILES['photos'] ?? null;
if (!$f || !is_array($f['name'])) fail('Please choose at least one photo.');

$valid = [];
foreach ($f['name'] as $i => $orig) {
    $err = $f['error'][$i];
    if ($err === UPLOAD_ERR_NO_FILE) continue;
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) fail("“" . $orig . "” is too large. Maximum is " . (MAX_FILE_BYTES / 1048576) . " MB per photo.");
    if ($err !== UPLOAD_ERR_OK) fail('Upload failed. Please try again.');
    $tmp = $f['tmp_name'][$i];
    if (!is_uploaded_file($tmp)) fail('Invalid upload.');
    if ($f['size'][$i] > MAX_FILE_BYTES) fail("“" . $orig . "” is too large. Maximum is " . (MAX_FILE_BYTES / 1048576) . " MB per photo.");
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);              // real type, not the browser's claim
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) fail("“" . $orig . "” is not a JPG, PNG or WebP image.");
    $info = @getimagesize($tmp);
    if (!$info || $info[0] * $info[1] > 40_000_000) fail("“" . $orig . "” is not a valid image or has too many pixels.");
    $valid[] = ['tmp' => $tmp, 'orig' => mb_substr(basename($orig), 0, 120), 'mime' => $mime];
}
if (!$valid) fail('Please choose at least one photo.');
if (count($valid) > MAX_FILES) fail('You can upload up to ' . MAX_FILES . ' photos at a time.');

// Re-encode every image with GD: strips EXIF/GPS and any hidden payload, resizes for the TV.
$saved = [];
foreach ($valid as $v) {
    $img = @imagecreatefromstring(file_get_contents($v['tmp']));
    if (!$img) { foreach ($saved as $s) @unlink(UPLOAD_DIR . $s['file']); fail('Could not read “' . $v["orig"] . '”.'); }
    if ($v['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
        $o = @exif_read_data($v['tmp'])['Orientation'] ?? 1;
        if ($deg = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0) $img = imagerotate($img, $deg, 0);
    }
    if (imagesx($img) > 1920) $img = imagescale($img, 1920);
    $file = bin2hex(random_bytes(16)) . '.jpg';                         // unguessable, collision-safe for parallel uploads
    imagejpeg($img, UPLOAD_DIR . $file, 85);
    $saved[] = ['file' => $file, 'orig' => $v['orig'], 'size' => filesize(UPLOAD_DIR . $file), 'token' => bin2hex(random_bytes(16))];
}

try {
    $pdo = db(); $pdo->beginTransaction();
    $hash = hash('sha256', $email . env('EMAIL_SALT', ''));
    $pdo->prepare('INSERT INTO visitors (name, email_hash) VALUES (?, ?)
                   ON DUPLICATE KEY UPDATE name = VALUES(name), visit_count = visit_count + 1,
                   last_visit_at = NOW(), id = LAST_INSERT_ID(id)')->execute([$name, $hash]);
    $vid = (int)$pdo->lastInsertId();
    $ins = $pdo->prepare('INSERT INTO photos (visitor_id, filename, original_name, size_bytes, consent_at, delete_token)
                          VALUES (?, ?, ?, ?, NOW(), ?)');
    foreach ($saved as $s) $ins->execute([$vid, $s['file'], $s['orig'], $s['size'], $s['token']]);
    $visits = (int)$pdo->query("SELECT visit_count FROM visitors WHERE id = $vid")->fetchColumn();
    $pdo->commit();
} catch (Throwable $ex) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    foreach ($saved as $s) @unlink(UPLOAD_DIR . $s['file']);
    error_log($ex->getMessage());
    fail('Something went wrong on our side. Please try again.');
}

$_SESSION['hits'][] = $now;
flash('success', $visits > 1 ? "Welcome back, $name! Your photos are going on screen now, along with your earlier ones." : "Thanks, $name! Your photos are going on screen now.",
      ['photos' => array_map(fn($s) => ['file' => $s['file'], 'token' => $s['token']], $saved)]);
header('Location: index.php'); exit;
