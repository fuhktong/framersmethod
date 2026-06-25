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
    $post['featured_image'] = trim($_POST['featured_image'] ?? '');
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
        $publish_input = trim($_POST['published_at'] ?? '');
        $published_at = null;
        if ($publish_input !== '') {
            $published_at = date('Y-m-d H:i:s', strtotime($publish_input));
        } elseif ($post['status'] === 'published') {
            $published_at = $post['published_at'] ?? date('Y-m-d H:i:s');
        }

        $upd = db()->prepare(
            'UPDATE posts SET title=?, slug=?, excerpt=?, body=?, category=?, featured_image=?, status=?, published_at=? WHERE id=?'
        );
        $upd->execute([$post['title'], $post['slug'], $post['excerpt'], $post['body'], $post['category'], $post['featured_image'] ?: null, $post['status'], $published_at, $post_id]);
        header('Location: /admin/posts');
        exit;
    }
}

// Pre-fill the publish-date field (date format)
$publish_value = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? trim($_POST['published_at'] ?? '')
    : (!empty($post['published_at']) ? date('Y-m-d', strtotime($post['published_at'])) : '');
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
                <span class="form-hint">framersmethod.com/post/<strong id="slug-preview"><?php echo htmlspecialchars($post['slug']); ?></strong></span>
            </div>
            <div class="form-group">
                <label for="tag-input">Categories</label>
                <div class="tag-chips" id="tag-chips"></div>
                <input type="text" id="tag-input" class="tag-input" placeholder="Type a category, press Enter…" autocomplete="off" />
                <input type="hidden" name="category" id="category" value="<?php echo htmlspecialchars($post['category'] ?? ''); ?>" />
                <span class="form-hint">Press Enter, Tab, or comma to add each one. They appear as tags on the post.</span>
            </div>
            <div class="form-group">
                <div class="label-row">
                    <label for="excerpt">Excerpt</label>
                    <span class="char-counter" id="excerpt-counter"></span>
                </div>
                <textarea id="excerpt" name="excerpt" rows="3" maxlength="150"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="featured-file">Featured image</label>
                <input type="hidden" name="featured_image" id="featured_image" value="<?php echo htmlspecialchars($post['featured_image'] ?? ''); ?>" />
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
                <textarea id="body" name="body" rows="24" required><?php echo htmlspecialchars($post['body']); ?></textarea>
                <span class="form-hint">Markdown supported: <code>**bold**</code>, <code>*italic*</code>, <code># Heading</code>, <code>- list</code>, <code>[link](url)</code>, <code>![alt](url)</code>.</span>
            </div>
            <div class="form-group">
                <label for="published_at">Publish date</label>
                <input type="date" id="published_at" name="published_at" value="<?php echo htmlspecialchars($publish_value); ?>" />
                <span class="form-hint">Change this to backdate the post. Leave blank to use the current date when publishing.</span>
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
    <script src="/admin/tag-input.js"></script>
    <script src="/admin/post-editor.js"></script>
    <script>
    const slugEl  = document.getElementById('slug');
    const preview = document.getElementById('slug-preview');
    slugEl.addEventListener('input', () => { preview.textContent = slugEl.value; });
    </script>
</body>
</html>
