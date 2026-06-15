<?php
session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');
$admin_path = preg_replace('#^/admin#', '', $path) ?: '/';

$matches = [];

switch (true) {
    case $admin_path === '/login':
        include __DIR__ . '/pages/login.php';
        break;
    case $admin_path === '/logout':
        include __DIR__ . '/pages/logout.php';
        break;
    case $admin_path === '/seed':
        include __DIR__ . '/seed.php';
        break;
    case $admin_path === '/posts':
        include __DIR__ . '/auth.php';
        include __DIR__ . '/pages/posts-list.php';
        break;
    case $admin_path === '/posts/new':
        include __DIR__ . '/auth.php';
        include __DIR__ . '/pages/posts-new.php';
        break;
    case (bool)preg_match('#^/posts/edit/(\d+)$#', $admin_path, $matches):
        include __DIR__ . '/auth.php';
        $post_id = (int)$matches[1];
        include __DIR__ . '/pages/posts-edit.php';
        break;
    case $admin_path === '/posts/delete':
        include __DIR__ . '/auth.php';
        include __DIR__ . '/pages/posts-delete.php';
        break;
    case $admin_path === '' || $admin_path === '/':
    default:
        include __DIR__ . '/auth.php';
        include __DIR__ . '/pages/dashboard.php';
        break;
}
