<?php
require_once __DIR__ . '/../contact/env_loader.php';

foreach ([__DIR__ . '/../.env', __DIR__ . '/../../.env'] as $env_path) {
    if (file_exists($env_path)) {
        loadEnv($env_path);
        break;
    }
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = env('DB_HOST', 'localhost');
    $name = env('DB_NAME');
    $user = env('DB_USERNAME');
    $pass = env('DB_PASSWORD');

    $pdo = new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    return $pdo;
}
