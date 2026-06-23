<?php
require_once __DIR__ . '/../database/db.php';

$recent = db()->query(
    'SELECT title, slug, excerpt, featured_image, published_at
     FROM posts
     WHERE status = "published"
     ORDER BY published_at DESC
     LIMIT 3'
)->fetchAll();
?>
<div class="subscribe-page">
    <header class="subscribe-hero">
        <h1 class="subscribe-hero-title">Subscribe to The Framers&rsquo; Method</h1>
        <p class="subscribe-hero-lede">
            New essays on electoral reform &mdash; the General Caucus, the Electors&rsquo; Convention,
            and the ideas behind a more deliberative democracy &mdash; delivered straight to your inbox.
        </p>
    </header>

    <div class="subscribe-card">
        <div class="subscribe-card-head">
            <img class="subscribe-card-logo" src="/images/framersmethod14.png" alt="The Framers' Method" />
            <ul class="subscribe-benefits">
                <li>Original essays and analysis, roughly once a month</li>
                <li>No spam, and we never sell your data</li>
                <li>Unsubscribe anytime, in one click</li>
            </ul>
        </div>
        <form class="subscribe-form" novalidate>
            <div class="subscribe-fields">
                <input type="email" name="email" class="subscribe-input" placeholder="you@example.com" required aria-label="Email address" autocomplete="email" />
                <button type="submit" class="subscribe-button">Subscribe</button>
            </div>
            <input type="text" name="website" class="subscribe-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
            <p class="subscribe-message" role="status" aria-live="polite"></p>
        </form>
        <p class="subscribe-fineprint">We&rsquo;ll send a confirmation link to finish subscribing.</p>
    </div>

    <?php if (!empty($recent)): ?>
    <section class="subscribe-recent">
        <h2 class="subscribe-recent-title">Recent essays</h2>
        <ul class="subscribe-recent-list">
            <?php foreach ($recent as $post): ?>
            <li class="subscribe-recent-item">
                <a href="/post/<?php echo htmlspecialchars($post['slug']); ?>" class="subscribe-recent-link">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img class="subscribe-recent-thumb" src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="" />
                    <?php endif; ?>
                    <span class="subscribe-recent-text">
                        <span class="subscribe-recent-date"><?php echo date('F j, Y', strtotime($post['published_at'])); ?></span>
                        <span class="subscribe-recent-headline"><?php echo htmlspecialchars($post['title']); ?></span>
                        <?php if (!empty($post['excerpt'])): ?>
                            <span class="subscribe-recent-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></span>
                        <?php endif; ?>
                    </span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>
</div>
