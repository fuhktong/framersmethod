<?php
require_once __DIR__ . '/../../database/db.php';

$errors = [];
$v = ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'category' => '', 'featured_image' => '', 'published_at' => '', 'status' => 'draft'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['title']          = trim($_POST['title'] ?? '');
    $v['slug']           = trim($_POST['slug'] ?? '');
    $v['excerpt']        = trim($_POST['excerpt'] ?? '');
    $v['body']           = trim($_POST['body'] ?? '');
    $v['category']       = trim($_POST['category'] ?? '');
    $v['featured_image'] = trim($_POST['featured_image'] ?? '');
    $v['published_at']   = trim($_POST['published_at'] ?? '');
    $v['status']         = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';

    if (!$v['title']) $errors[] = 'Title is required.';
    if (!$v['slug'])  $errors[] = 'Slug is required.';
    if (!$v['body'])  $errors[] = 'Body is required.';

    if (empty($errors)) {
        $check = db()->prepare('SELECT id FROM posts WHERE slug = ?');
        $check->execute([$v['slug']]);
        if ($check->fetch()) $errors[] = 'That slug is already in use.';
    }

    if (empty($errors)) {
        $published_at = null;
        if ($v['published_at'] !== '') {
            $published_at = date('Y-m-d H:i:s', strtotime($v['published_at']));
        } elseif ($v['status'] === 'published') {
            $published_at = date('Y-m-d H:i:s');
        }
        $stmt = db()->prepare(
            'INSERT INTO posts (title, slug, excerpt, body, category, featured_image, status, author_id, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$v['title'], $v['slug'], $v['excerpt'], $v['body'], $v['category'], $v['featured_image'] ?: null, $v['status'], $_SESSION['admin_id'], $published_at]);
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
                <span class="form-hint">framersmethod.com/post/<strong id="slug-preview"><?php echo htmlspecialchars($v['slug']); ?></strong></span>
            </div>
            <div class="form-group">
                <label for="tag-input">Categories</label>
                <div class="tag-chips" id="tag-chips"></div>
                <input type="text" id="tag-input" class="tag-input" placeholder="Type a category, press Enter…" autocomplete="off" />
                <input type="hidden" name="category" id="category" value="<?php echo htmlspecialchars($v['category']); ?>" />
                <span class="form-hint">Press Enter, Tab, or comma to add each one. They appear as tags on the post.</span>
            </div>
            <div class="form-group">
                <div class="label-row">
                    <label for="excerpt">Excerpt</label>
                    <span class="char-counter" id="excerpt-counter"></span>
                </div>
                <textarea id="excerpt" name="excerpt" rows="3" maxlength="150" placeholder="Short summary shown in the feed..."><?php echo htmlspecialchars($v['excerpt']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="featured-file">Featured image</label>
                <input type="hidden" name="featured_image" id="featured_image" value="<?php echo htmlspecialchars($v['featured_image']); ?>" />
                <img id="featured-preview" class="featured-preview" alt="Featured image preview" />
                <div class="featured-controls">
                    <input type="file" id="featured-file" accept="image/*" />
                    <button type="button" id="featured-remove" class="btn-link btn-danger">Remove</button>
                </div>
                <span class="form-hint">Shown on the homepage feed and post cards. Use a 16:9 image (1920×1080 ideal); it's center-cropped to fit, so keep the subject centered. Max <?php echo htmlspecialchars(ini_get('upload_max_filesize')); ?>B.</span>
            </div>
            <div class="form-group">
                <div class="label-row">
                    <label for="body">Body <span class="label-note">Markdown</span></label>
                    <div class="label-actions">
                        <button type="button" id="insert-subscribe" class="admin-btn-secondary btn-inline">Subscribe banner</button>
                        <button type="button" id="insert-book" class="admin-btn-secondary btn-inline">Book banner</button>
                        <button type="button" id="insert-image" class="admin-btn-secondary btn-inline">Insert image</button>
                        <input type="file" id="insert-image-file" accept="image/*" hidden />
                    </div>
                </div>
                <textarea id="body" name="body" rows="24" required><?php echo htmlspecialchars($v['body']); ?></textarea>
                <span class="form-hint">Markdown supported: <code>**bold**</code>, <code>*italic*</code>, <code># Heading</code>, <code>- list</code>, <code>[link](url)</code>, <code>![alt](url)</code>.</span>
            </div>
            <div class="form-group">
                <label for="published_at">Publish date</label>
                <input type="date" id="published_at" name="published_at" value="<?php echo htmlspecialchars($v['published_at']); ?>" />
                <span class="form-hint">Leave blank to use the current date when publishing. Set this to backdate older posts.</span>
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
    <script src="/admin/tag-input.js"></script>
    <script src="/admin/post-editor.js"></script>
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
