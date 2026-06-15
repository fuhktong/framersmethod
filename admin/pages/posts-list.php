<?php
require_once __DIR__ . '/../../database/db.php';

$posts = db()->query(
    'SELECT id, title, category, status, published_at, created_at FROM posts ORDER BY created_at DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Posts — Admin</title>
    <link rel="stylesheet" href="/admin/admin.css" />
</head>
<body class="admin-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <main class="admin-main">
        <div class="admin-main-header">
            <h2>Posts</h2>
            <a href="/admin/posts/new" class="admin-btn">+ New Post</a>
        </div>

        <?php if (empty($posts)): ?>
            <p class="admin-empty">No posts yet. <a href="/admin/posts/new">Create your first post.</a></p>
        <?php else: ?>
        <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['category'] ?? '—'); ?></td>
                    <td><span class="status-badge status-<?php echo $post['status']; ?>"><?php echo $post['status']; ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                    <td class="admin-table-actions">
                        <a href="/admin/posts/edit/<?php echo $post['id']; ?>">Edit</a>
                        <form method="POST" action="/admin/posts/delete" onsubmit="return confirm('Delete this post?')">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>" />
                            <button type="submit" class="btn-link btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
