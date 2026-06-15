<?php
require_once __DIR__ . '/../database/db.php';

$stmt = db()->prepare(
    'SELECT title, slug, excerpt, category, published_at
     FROM posts
     WHERE status = "published"
     ORDER BY published_at DESC
     LIMIT 6'
);
$stmt->execute();
$posts = $stmt->fetchAll();
?>
<div class="home-hero">
    <h1 class="home-hero-title">The Framers' Method</h1>
    <p class="home-hero-tagline">Restoring political stability through decentralization and deliberation in American elections.</p>
</div>

<div class="home-feed-header">
    <span class="home-feed-label">Latest</span>
    <a href="/blog" class="home-feed-all">All posts →</a>
</div>

<?php if (empty($posts)): ?>
<p class="blog-empty">No posts published yet.</p>
<?php else: ?>
<div class="posts-grid">
    <?php foreach ($posts as $post): ?>
    <article class="post-card">
        <?php if ($post['category']): ?>
            <span class="post-card-category"><?php echo htmlspecialchars($post['category']); ?></span>
        <?php endif; ?>
        <h2 class="post-card-title">
            <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a>
        </h2>
        <?php if ($post['excerpt']): ?>
            <p class="post-card-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
        <?php endif; ?>
        <div class="post-card-footer">
            <span class="post-card-date"><?php echo date('F j, Y', strtotime($post['published_at'])); ?></span>
            <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" class="post-card-link">Read more →</a>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
