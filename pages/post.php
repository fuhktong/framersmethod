<?php
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/markdown.php';
require_once __DIR__ . '/../subscribe/subscribe-banner.php';
require_once __DIR__ . '/book-banner.php';

$stmt = db()->prepare(
    'SELECT * FROM posts WHERE slug = ? AND status = "published"'
);
$stmt->execute([$post_slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    echo '<div class="post-not-found"><h1>Post not found</h1><a href="/posts">← Back to posts</a></div>';
    return;
}
?>
<?php if (!empty($post['featured_image'])): ?>
    <img class="post-article-image" src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
<?php endif; ?>
<article class="post-article">
    <div class="post-article-header">
        <h1 class="post-article-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="post-article-date"><?php echo date('F j, Y', strtotime($post['published_at'])); ?></p>
        <?php if (!empty($post['category'])): ?>
            <div class="post-card-tags">
                <?php foreach (array_filter(array_map('trim', explode(',', $post['category']))) as $tag): ?>
                    <span class="post-card-category"><?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="post-article-body">
        <?php
        // Render Markdown, then swap the [subscribe] and [book] shortcodes for their banners.
        $body_html = render_markdown($post['body']);
        $body_html = str_replace('<p>[subscribe]</p>', subscribe_banner(), $body_html);
        echo str_replace('<p>[book]</p>', book_banner(), $body_html);
        ?>
    </div>
</article>
