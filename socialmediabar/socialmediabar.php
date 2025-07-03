<!-- Social Media Bar -->
<section class="socialmediabar" id="socialmediabar">
    <a href="https://bsky.app/profile/framersmethod.bsky.social" target="_blank" rel="noreferrer">
        <img src="/images/whitelogobluesky.png" alt="The Framers' Method on Bluesky" />
    </a>
    <a href="https://www.instagram.com/framersmethod/" target="_blank" rel="noreferrer">
        <img src="/images/whitelogoinsta.png" alt="The Framers' Method on Instagram" />
    </a>
    <a href="https://www.tiktok.com/@framersmethod" target="_blank" rel="noreferrer">
        <img src="/images/whitelogotiktok.png" alt="The Framers' Method on TikTok" />
    </a>
    <a href="https://www.youtube.com/@framersmethod/featured" target="_blank" rel="noreferrer">
        <img src="/images/whitelogoyoutube.png" alt="The Framers' Method on YouTube" />
    </a>
    <a href="https://medium.com/@framersmethod" target="_blank" rel="noreferrer">
        <img src="/images/whitelogomedium.png" alt="The Framers' Method - Medium" />
    </a>
    <a href="https://substack.com/@framersmethod" target="_blank" rel="noreferrer">
        <img src="/images/whitelogosubstack.png" alt="The Framers' Method - Substack" />
    </a>
    <a href="https://a.co/d/0dimzJAr" target="_blank" rel="noreferrer">
        <img src="/images/whitelogoamazon.png" alt="On The Framers' Method Book - Amazon" />
    </a>
</section>

<script>
// Responsive social media bar
function updateSocialMediaBar() {
    const socialBar = document.getElementById('socialmediabar');
    const isMobile = window.innerWidth <= 650;
    
    if (isMobile) {
        socialBar.innerHTML = `
            <div class="socialmediabar-row">
                <a href="https://bsky.app/profile/framersmethod.bsky.social" target="_blank" rel="noreferrer">
                    <img src="/images/whitelogobluesky.png" alt="The Framers' Method on Bluesky" />
                </a>
                <a href="https://www.instagram.com/framersmethod/" target="_blank" rel="noreferrer">
                    <img src="/images/whitelogoinsta.png" alt="The Framers' Method on Instagram" />
                </a>
                <a href="https://www.youtube.com/@framersmethod/featured" target="_blank" rel="noreferrer">
                    <img src="/images/whitelogoyoutube.png" alt="The Framers' Method on YouTube" />
                </a>
                <a href="https://www.tiktok.com/@framersmethod" target="_blank" rel="noreferrer">
                    <img src="/images/whitelogotiktok.png" alt="The Framers' Method on TikTok" />
                </a>

            </div>
            <div class="socialmediabar-row">
                <a href="https://medium.com/@framersmethod" target="_blank" rel="noreferrer">
                    <img src="/images/whitelogomedium.png" alt="The Framers' Method - Medium" />
                </a>
                <a href="https://substack.com/@framersmethod" target="_blank" rel="noreferrer">
                    <img src="/images/whitelogosubstack.png" alt="The Framers' Method - Substack" />
                </a>
                <a href="https://a.co/d/0dimzJAr" target="_blank" rel="noreferrer">
                    <img src="/images/whitelogoamazon.png" alt="On The Framers' Method Book - Amazon" />
                </a>
            </div>
        `;
    } else {
        socialBar.innerHTML = `
        <a href="https://bsky.app/profile/framersmethod.bsky.social" target="_blank" rel="noreferrer">
            <img src="/images/whitelogobluesky.png" alt="The Framers' Method on Bluesky" />
        </a>
        <a href="https://www.instagram.com/framersmethod/" target="_blank" rel="noreferrer">
            <img src="/images/whitelogoinsta.png" alt="The Framers' Method on Instagram" />
        </a>
        <a href="https://www.tiktok.com/@framersmethod" target="_blank" rel="noreferrer">
            <img src="/images/whitelogotiktok.png" alt="The Framers' Method on TikTok" />
        </a>
        <a href="https://www.youtube.com/@framersmethod/featured" target="_blank" rel="noreferrer">
            <img src="/images/whitelogoyoutube.png" alt="The Framers' Method on YouTube" />
        </a>
        <a href="https://medium.com/@framersmethod" target="_blank" rel="noreferrer">
            <img src="/images/whitelogomedium.png" alt="The Framers' Method - Medium" />
        </a>
        <a href="https://substack.com/@framersmethod" target="_blank" rel="noreferrer">
            <img src="/images/whitelogosubstack.png" alt="The Framers' Method - Substack" />
        </a>
        <a href="https://a.co/d/0dimzJAr" target="_blank" rel="noreferrer">
            <img src="/images/whitelogoamazon.png" alt="On The Framers' Method Book - Amazon" />
        </a>
        `;
    }
}

// Initialize on load and resize
window.addEventListener('load', updateSocialMediaBar);
window.addEventListener('resize', updateSocialMediaBar);
</script>