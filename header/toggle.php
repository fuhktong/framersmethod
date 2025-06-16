<!-- Mobile Navigation -->
<div class="mobile-nav">
    <button class="hamburger" onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="mobile-menu" id="mobile-menu">
        <ul>
            <li><a href="/general-caucus" onclick="closeMobileMenu()">GENERAL CAUCUS</a></li>
            <li><a href="/how-it-works" onclick="closeMobileMenu()">HOW THE GENERAL CAUCUS WORKS</a></li>
            <li><a href="/democracy-vs-republic" onclick="closeMobileMenu()">DEMOCRACY vs REPUBLIC</a></li>
            <li><a href="/hamilton-method" onclick="closeMobileMenu()">HAMILTON METHOD</a></li>
            <li><a href="/electors-convention" onclick="closeMobileMenu()">ELECTORS' CONVENTION</a></li>
            <li><a href="/book" onclick="closeMobileMenu()">BOOK</a></li>
            <li><a href="/faq" onclick="closeMobileMenu()">FAQ</a></li>
            <li><a href="/contribute" onclick="closeMobileMenu()">CONTRIBUTE</a></li>
            <li><a href="/team" onclick="closeMobileMenu()">TEAM</a></li>
            <li><a href="/contact-us" onclick="closeMobileMenu()">CONTACT</a></li>
        </ul>
    </div>
</div>

<script>
function toggleMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.getElementById('mobile-menu');
    
    hamburger.classList.toggle('open');
    mobileMenu.classList.toggle('open');
}

function closeMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.getElementById('mobile-menu');
    
    hamburger.classList.remove('open');
    mobileMenu.classList.remove('open');
}
</script>