<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_admin_uri = $_SERVER['REQUEST_URI'] ?? '';
$_admin_in_email = strpos($_admin_uri, '/admin/emailservice') !== false;
$_admin_in_posts = strpos($_admin_uri, '/admin/posts') !== false;
?>
<header class="admin-header">
    <a class="admin-header-title" href="/admin">The Framers' Method — Admin</a>
    <nav class="admin-nav">
        <a href="/admin/posts"<?php if ($_admin_in_posts) echo ' class="active"'; ?>>Posts</a>
        <a href="/admin/emailservice/campaigns.php"<?php if ($_admin_in_email) echo ' class="active"'; ?>>Email</a>
        <a href="/admin/logout">Sign out</a>
    </nav>
</header>
