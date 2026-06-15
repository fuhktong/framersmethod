<?php
require_once __DIR__ . '/../../database/db.php';

$errors = [];
$v = ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'category' => '', 'status' => 'draft'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['title']    = trim($_POST['title'] ?? '');
    $v['slug']     = trim($_POST['slug'] ?? '');
    $v['excerpt']  = trim($_POST['excerpt'] ?? '');
    $v['body']     = trim($_POST['body'] ?? '');
    $v['category'] = trim($_POST['category'] ?? '');
    $v['status']   = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';

    if (!$v['title']) $errors[] = 'Title is required.';
    if (!$v['slug'])  $errors[] = 'Slug is required.';
    if (!$v['body'])  $errors[] = 'Body is required.';

    if (empty($errors)) {
        $check = db()->prepare('SELECT id FROM posts WHERE slug = ?');
        $check->execute([$v['slug']]);
        if ($check->fetch()) $errors[] = 'That slug is already in use.';
    }

    if (empty($errors)) {
        $published_at = $v['status'] === 'published' ? date('Y-m-d H:i:s') : null;
        $stmt = db()->prepare(
            'INSERT INTO posts (title, slug, excerpt, body, category, status, author_id, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$v['title'], $v['slug'], $v['excerpt'], $v['body'], $v['category'], $v['status'], $_SESSION['admin_id'], $published_at]);
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
    <title>New Post — Admin</title>
    <link rel="stylesheet" href="/admin/admin.css" />
</head>
<body class="admin-page">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <main class="admin-main">
        <div class="admin-main-header">
            <h2>New Post</h2>
            <a href="/admin/posts" class="admin-btn-secondary">← Back</a>
        </div>

        <?php if ($errors): ?>
        <div class="admin-errors">
            <?php foreach ($errors as $e): ?><p><?php echo htmlspecialchars($e); ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/posts/new" class="post-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($v['title']); ?>" required />
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($v['slug']); ?>" required />
                <span class="form-hint">framersmethod.com/blog/<strong id="slug-preview"><?php echo htmlspecialchars($v['slug']); ?></strong></span>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($v['category']); ?>" placeholder="e.g. Electoral Reform" />
            </div>
            <div class="form-group">
                <label for="excerpt">Excerpt</label>
                <textarea id="excerpt" name="excerpt" rows="3" placeholder="Short summary shown in the feed..."><?php echo htmlspecialchars($v['excerpt']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea id="body" name="body" rows="24" required><?php echo htmlspecialchars($v['body']); ?></textarea>
            </div>
            <div class="form-group form-group-inline">
                <label>Status</label>
                <div class="radio-group">
                    <label class="radio-label"><input type="radio" name="status" value="draft" <?php echo $v['status'] === 'draft' ? 'checked' : ''; ?> /> Draft</label>
                    <label class="radio-label"><input type="radio" name="status" value="published" <?php echo $v['status'] === 'published' ? 'checked' : ''; ?> /> Published</label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="admin-btn">Save Post</button>
            </div>
        </form>
    </main>
    <script>
    const titleEl = document.getElementById('title');
    const slugEl  = document.getElementById('slug');
    const preview = document.getElementById('slug-preview');
    let manualSlug = slugEl.value.length > 0;

    function slugify(s) {
        return s.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }
    titleEl.addEventListener('input', () => {
        if (!manualSlug) { const s = slugify(titleEl.value); slugEl.value = s; preview.textContent = s; }
    });
    slugEl.addEventListener('input', () => { manualSlug = true; preview.textContent = slugEl.value; });
    </script>
</body>
</html>
