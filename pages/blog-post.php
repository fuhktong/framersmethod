<?php
require_once __DIR__ . '/../database/db.php';

$stmt = db()->prepare(
    'SELECT * FROM posts WHERE slug = ? AND status = "published"'
);
$stmt->execute([$blog_slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    echo '<div class="blog-post-not-found"><h1>Post not found</h1><a href="/blog">← Back to blog</a></div>';
    return;
}
?>
<article class="blog-post">
    <div class="blog-post-header">
        <a href="/blog" class="blog-post-back">← Blog</a>
        <?php if ($post['category']): ?>
            <span class="post-card-category"><?php echo htmlspecialchars($post['category']); ?></span>
        <?php endif; ?>
        <h1 class="blog-post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="blog-post-date"><?php echo date('F j, Y', strtotime($post['published_at'])); ?></p>
    </div>
    <div class="blog-post-body">
        <?php echo nl2br(htmlspecialchars($post['body'])); ?>
    </div>
</article>
