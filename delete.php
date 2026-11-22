<?php
require __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals(csrf(), $_POST['csrf'] ?? '')) {
    $st = db()->prepare('SELECT id, filename FROM photos WHERE delete_token = ? AND is_visible = 1');
    $st->execute([$_POST['token'] ?? '']);
    if ($p = $st->fetch()) {
        db()->prepare('UPDATE photos SET is_visible = 0 WHERE id = ?')->execute([$p['id']]);
        @unlink(UPLOAD_DIR . $p['filename']);
        flash('success', 'Photo removed from the display.');
    }
}
header('Location: index.php');
