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
    const navList = nav.querySelector('.nav-links');
    navToggle.addEventListener('click', () => {
      nav.classList.toggle('is-open');
      if (navList) {
        navList.classList.toggle('is-open');
      }
    });
    if (navList) {
      navList.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
          nav.classList.remove('is-open');
          navList.classList.remove('is-open');
        });
      });
    }
  }
});
