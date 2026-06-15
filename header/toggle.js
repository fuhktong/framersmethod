(function () {
  function els() {
    return {
      hamburger: document.querySelector('.hamburger'),
      menu: document.getElementById('mobile-menu'),
    };
  }

  function setMenuOpen(isOpen) {
    const { hamburger, menu } = els();
    if (!hamburger || !menu) return;

    hamburger.classList.toggle('open', isOpen);
    menu.classList.toggle('open', isOpen);
    hamburger.setAttribute('aria-expanded', String(isOpen));
    hamburger.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');

    // Freeze the page behind the drawer (see toggle.css for why both elements).
    document.body.classList.toggle('menu-open', isOpen);
    document.documentElement.classList.toggle('menu-open', isOpen);
  }

  // Called from the inline handlers in toggle.php.
  window.toggleMobileMenu = function () {
    const { menu } = els();
    if (menu) setMenuOpen(!menu.classList.contains('open'));
  };

  window.closeMobileMenu = function () {
    setMenuOpen(false);
  };

  // Keep the drawer flush with the bottom of the header at any viewport size,
  // so no hardcoded top offset is needed.
  function syncHeaderHeight() {
    const header = document.querySelector('header');
    if (header) {
      document.documentElement.style.setProperty(
        '--header-height',
        header.offsetHeight + 'px'
      );
    }
  }
  syncHeaderHeight();
  window.addEventListener('resize', syncHeaderHeight);
  window.addEventListener('load', syncHeaderHeight);

  // Close on Escape.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') setMenuOpen(false);
  });

  // Close when clicking outside the open drawer.
  document.addEventListener('click', function (e) {
    const { hamburger, menu } = els();
    if (!menu || !menu.classList.contains('open')) return;
    if (menu.contains(e.target) || (hamburger && hamburger.contains(e.target))) {
      return;
    }
    setMenuOpen(false);
  });
})();
