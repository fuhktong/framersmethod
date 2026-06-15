<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/posts');
    exit;
}

require_once __DIR__ . '/../../database/db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = db()->prepare('DELETE FROM posts WHERE id = ?');
    $stmt->execute([$id]);
}

header('Location: /admin/posts');
exit;
