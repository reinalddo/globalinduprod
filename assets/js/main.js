document.addEventListener('DOMContentLoaded', () => {
  const languageToggle = document.querySelector('[data-toggle="language-menu"]');
  const languageMenu = document.querySelector('[data-language-menu]');
  const scrollTopBtn = document.querySelector('[data-scroll-top]');
  const navToggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-primary-nav]');

  if (languageToggle && languageMenu) {
    languageToggle.addEventListener('click', () => {
      languageMenu.classList.toggle('is-visible');
    });
    document.addEventListener('click', (event) => {
      if (!languageMenu.contains(event.target) && !languageToggle.contains(event.target)) {
        languageMenu.classList.remove('is-visible');
      }
    });
  }

  if (scrollTopBtn) {
    window.addEventListener('scroll', () => {
      scrollTopBtn.classList.toggle('is-visible', window.scrollY > 360);
    });
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  if (navToggle && nav) {
    navToggle.setAttribute('aria-expanded', 'false');
    nav.setAttribute('aria-hidden', 'true');

    navToggle.addEventListener('click', () => {
      const isActive = navToggle.classList.toggle('is-active');
      nav.classList.toggle('is-active', isActive);
      navToggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
      nav.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    });
    nav.querySelectorAll('.navbar-item').forEach((link) => {
      link.addEventListener('click', () => {
        navToggle.classList.remove('is-active');
        nav.classList.remove('is-active');
        navToggle.setAttribute('aria-expanded', 'false');
        nav.setAttribute('aria-hidden', 'true');
      });
    });
  }
});
