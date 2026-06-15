<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../database/db.php';

$username = env('ADMIN_USERNAME');
$password = env('ADMIN_PASSWORD');

if (!$username || !$password) {
    die('ADMIN_USERNAME or ADMIN_PASSWORD not set in .env');
}

$pdo = db();

$existing = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$existing->execute([$username]);

if ($existing->fetch()) {
    die("User '{$username}' already exists. Seed not needed.");
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
$stmt->execute([$username, $hash]);

echo "Admin user '{$username}' created successfully. You can now log in at /admin/login.";
