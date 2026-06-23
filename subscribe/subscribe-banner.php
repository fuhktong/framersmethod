<?php
/**
 * Returns the markup for the inline subscribe banner.
 * Used by the [subscribe] shortcode in post bodies (and reusable elsewhere).
 */
function subscribe_banner(): string
{
    ob_start();
    ?>
    <aside class="subscribe-banner">
        <img class="subscribe-banner-image" src="/images/framersmethod14.png" alt="The Framers' Method" />
        <div class="subscribe-banner-text">
            <h3 class="subscribe-banner-title">Enjoyed this? There&rsquo;s more.</h3>
            <p class="subscribe-banner-sub">New essays on electoral reform, delivered to your inbox.</p>
        </div>
        <form class="subscribe-form" novalidate>
            <div class="subscribe-fields">
                <input type="email" name="email" class="subscribe-input" placeholder="you@example.com" required aria-label="Email address" autocomplete="email" />
                <button type="submit" class="subscribe-button">Subscribe</button>
            </div>
            <input type="text" name="website" class="subscribe-hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
            <p class="subscribe-message" role="status" aria-live="polite"></p>
        </form>
    </aside>
    <?php
    return ob_get_clean();
}
