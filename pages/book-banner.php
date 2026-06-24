<?php
/**
 * Returns the markup for the inline book banner.
 * Used by the [book] shortcode in post bodies (and reusable elsewhere).
 */
function book_banner(): string
{
    ob_start();
    ?>
    <aside class="book-banner">
        <img class="book-banner-image" src="/images/bookimage.png" alt="On the Framers' Method: How the Electoral College and the Hamilton Method Can Defeat Populism and Tyranny" />
        <div class="book-banner-text">
            <h3 class="book-banner-title">On the Framers&rsquo; Electoral College</h3>
            <p class="book-banner-sub">How the Hamilton Method and an Electors&rsquo; Convention can defeat populism and tyranny.</p>
        </div>
        <a class="book-banner-cta" href="https://www.amazon.com/dp/B0GPFH9GDG" target="_blank" rel="noreferrer">
            Purchase on Amazon
        </a>
    </aside>
    <?php
    return ob_get_clean();
}
