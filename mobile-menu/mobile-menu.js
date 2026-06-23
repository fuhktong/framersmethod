(function () {
  function els() {
    return {
      hamburger: document.getElementById('hamburger'),
      menu: document.getElementById('mobile-menu'),
    };
  }

  // Keep the drawer flush with the bottom of the header. Header height isn't
  // a fixed value below the 600px breakpoint (the links wrap), so it has to
  // be measured rather than hardcoded.
  function syncHeaderHeight() {
    const header = document.querySelector('.site-header');
    if (header) {
      document.documentElement.style.setProperty(
        '--header-height',
        header.offsetHeight + 'px'
      );
    }
  }

  function setMenuOpen(isOpen) {
    const { hamburger, menu } = els();
    if (!hamburger || !menu) return;

    // Re-measure right before opening so the offset can't go stale between
    // page load and the moment the drawer is actually opened.
    if (isOpen) syncHeaderHeight();

    hamburger.classList.toggle('open', isOpen);
    menu.classList.toggle('open', isOpen);
    hamburger.setAttribute('aria-expanded', String(isOpen));
    hamburger.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');

    document.body.classList.toggle('menu-open', isOpen);
    document.documentElement.classList.toggle('menu-open', isOpen);
  }

  function isMenuOpen() {
    const { menu } = els();
    return !!menu && menu.classList.contains('open');
  }

  // This script is loaded at the end of the body, after the header and
  // drawer markup, so the elements already exist — no need to wait for
  // DOMContentLoaded.
  (function attach() {
    const { hamburger, menu } = els();
    if (!hamburger || !menu) return;

    hamburger.addEventListener('click', function () {
      setMenuOpen(!isMenuOpen());
    });

    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        setMenuOpen(false);
      });
    });
  })();

  syncHeaderHeight();
  window.addEventListener('resize', syncHeaderHeight);
  window.addEventListener('load', syncHeaderHeight);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') setMenuOpen(false);
  });

  document.addEventListener('click', function (e) {
    const { hamburger, menu } = els();
    if (!isMenuOpen()) return;
    if (menu.contains(e.target) || (hamburger && hamburger.contains(e.target))) {
      return;
    }
    setMenuOpen(false);
  });
})();
