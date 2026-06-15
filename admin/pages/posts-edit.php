<?php
require_once __DIR__ . '/../../database/db.php';

$stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: /admin/posts');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post['title']    = trim($_POST['title'] ?? '');
    $post['slug']     = trim($_POST['slug'] ?? '');
    $post['excerpt']  = trim($_POST['excerpt'] ?? '');
    $post['body']     = trim($_POST['body'] ?? '');
    $post['category'] = trim($_POST['category'] ?? '');
    $post['status']   = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';

    if (!$post['title']) $errors[] = 'Title is required.';
    if (!$post['slug'])  $errors[] = 'Slug is required.';
    if (!$post['body'])  $errors[] = 'Body is required.';

    if (empty($errors)) {
        $check = db()->prepare('SELECT id FROM posts WHERE slug = ? AND id != ?');
        $check->execute([$post['slug'], $post_id]);
        if ($check->fetch()) $errors[] = 'That slug is already in use.';
    }

    if (empty($errors)) {
        $published_at = $post['status'] === 'published'
            ? ($post['published_at'] ?? date('Y-m-d H:i:s'))
            : null;

        $upd = db()->prepare(
            'UPDATE posts SET title=?, slug=?, excerpt=?, body=?, category=?, status=?, published_at=? WHERE id=?'
        );
        $upd->execute([$post['title'], $post['slug'], $post['excerpt'], $post['body'], $post['category'], $post['status'], $published_at, $post_id]);
        header('Location: /admin/posts');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Post — Admin</title>
    <link rel="stylesheet" href="/admin/admin.css" />
</head>
<body class="admin-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <main class="admin-main">
        <div class="admin-main-header">
            <h2>Edit Post</h2>
            <a href="/admin/posts" class="admin-btn-secondary">← Back</a>
        </div>

        <?php if ($errors): ?>
        <div class="admin-errors">
            <?php foreach ($errors as $e): ?><p><?php echo htmlspecialchars($e); ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/posts/edit/<?php echo $post_id; ?>" class="post-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required />
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>" required />
                <span class="form-hint">framersmethod.com/blog/<strong id="slug-preview"><?php echo htmlspecialchars($post['slug']); ?></strong></span>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($post['category'] ?? ''); ?>" placeholder="e.g. Electoral Reform" />
            </div>
            <div class="form-group">
                <label for="excerpt">Excerpt</label>
                <textarea id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea id="body" name="body" rows="24" required><?php echo htmlspecialchars($post['body']); ?></textarea>
            </div>
            <div class="form-group form-group-inline">
                <label>Status</label>
                <div class="radio-group">
                    <label class="radio-label"><input type="radio" name="status" value="draft" <?php echo $post['status'] === 'draft' ? 'checked' : ''; ?> /> Draft</label>
                    <label class="radio-label"><input type="radio" name="status" value="published" <?php echo $post['status'] === 'published' ? 'checked' : ''; ?> /> Published</label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="admin-btn">Update Post</button>
            </div>
        </form>
    </main>
    <script>
    const slugEl  = document.getElementById('slug');
    const preview = document.getElementById('slug-preview');
    slugEl.addEventListener('input', () => { preview.textContent = slugEl.value; });
    </script>
</body>
</html>
