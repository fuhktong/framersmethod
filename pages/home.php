<?php
require_once __DIR__ . '/../database/db.php';

$stmt = db()->prepare(
    'SELECT title, slug, excerpt, category, featured_image, published_at
     FROM posts
     WHERE status = "published"
     ORDER BY published_at DESC
     LIMIT 6'
);
$stmt->execute();
$posts = $stmt->fetchAll();
?>
<?php if (empty($posts)): ?>
<p class="posts-empty">No posts published yet.</p>
<?php else: ?>
<div class="posts-grid">
    <?php foreach ($posts as $post): ?>
    <a class="post-card" href="/post/<?php echo htmlspecialchars($post['slug']); ?>">
        <?php if (!empty($post['featured_image'])): ?>
            <span class="post-card-image">
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
            </span>
        <?php endif; ?>
        <div class="post-card-body">
            <h2 class="post-card-title"><?php echo htmlspecialchars($post['title']); ?></h2>
            <span class="post-card-date"><?php echo date('F j, Y', strtotime($post['published_at'])); ?></span>
            <?php if (!empty($post['category'])): ?>
                <span class="post-card-tags">
                    <?php foreach (array_filter(array_map('trim', explode(',', $post['category']))) as $tag): ?>
                        <span class="post-card-category"><?php echo htmlspecialchars($tag); ?></span>
                    <?php endforeach; ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($post['excerpt'])): ?>
                <p class="post-card-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
            <?php endif; ?>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
