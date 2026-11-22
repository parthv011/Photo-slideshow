<?php
// JSON feed for the slideshow screen. Returns every visible photo (newest last).
require __DIR__ . '/config.php';
session_write_close();
header('Content-Type: application/json'); header('Cache-Control: no-store');
$rows = db()->query('SELECT p.id, p.filename, p.visitor_id, v.name, v.visit_count
                     FROM photos p JOIN visitors v ON v.id = p.visitor_id
                     WHERE p.is_visible = 1 ORDER BY p.id DESC LIMIT 300')->fetchAll();
echo json_encode(array_map(fn($r) => [
    'id' => (int)$r['id'], 'url' => 'uploads/' . $r['filename'],
    'visitor' => (int)$r['visitor_id'], 'name' => $r['name'], 'returning' => $r['visit_count'] > 1,
], array_reverse($rows)));
