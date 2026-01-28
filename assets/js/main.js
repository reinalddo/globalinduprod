document.addEventListener('DOMContentLoaded', () => {
  const languageToggle = document.querySelector('[data-toggle="language-menu"]');
  const languageMenu = document.querySelector('[data-language-menu]');
  const scrollTopBtn = document.querySelector('[data-scroll-top]');
  const navToggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-primary-nav]');
  const searchPanel = document.querySelector('[data-search-panel]');
  const searchToggle = searchPanel ? searchPanel.querySelector('.top-search__toggle') : null;
  const searchContent = searchPanel ? searchPanel.querySelector('.top-search__panel') : null;

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

  if (searchToggle && searchContent) {
    const mobileQuery = window.matchMedia('(max-width: 720px)');

    const resetPanelStyles = () => {
      searchContent.style.position = '';
      searchContent.style.left = '';
      searchContent.style.right = '';
      searchContent.style.margin = '';
      searchContent.style.top = '';
      searchContent.style.removeProperty('--search-panel-top');
    };

    const positionMobilePanel = () => {
      if (!mobileQuery.matches) {
        resetPanelStyles();
        return;
      }
      const rect = searchToggle.getBoundingClientRect();
      const topOffset = Math.max(rect.bottom + 8, 0);
      searchContent.style.setProperty('--search-panel-top', `${Math.round(topOffset)}px`);
    };

    const closePanel = () => {
      searchToggle.setAttribute('aria-expanded', 'false');
      searchContent.hidden = true;
    };

    const openPanel = (shouldFocus = true) => {
      if (mobileQuery.matches) {
        positionMobilePanel();
      } else {
        resetPanelStyles();
      }
      searchToggle.setAttribute('aria-expanded', 'true');
      searchContent.hidden = false;
      if (shouldFocus) {
        const input = searchContent.querySelector('input[type="search"]');
        window.requestAnimationFrame(() => input && input.focus());
      }
    };

    const syncPanel = () => {
      if (mobileQuery.matches) {
        closePanel();
      } else {
        resetPanelStyles();
        openPanel(false);
      }
    };

    syncPanel();

    if (typeof mobileQuery.addEventListener === 'function') {
      mobileQuery.addEventListener('change', syncPanel);
    } else if (typeof mobileQuery.addListener === 'function') {
      mobileQuery.addListener(syncPanel);
    }

    searchToggle.addEventListener('click', () => {
      if (!mobileQuery.matches) {
        return;
      }
      const expanded = searchToggle.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        closePanel();
      } else {
        openPanel();
      }
    });

    document.addEventListener('click', (event) => {
      if (mobileQuery.matches && !searchPanel.contains(event.target)) {
        closePanel();
      }
    });

    window.addEventListener('resize', () => {
      if (mobileQuery.matches && searchToggle.getAttribute('aria-expanded') === 'true') {
        positionMobilePanel();
      }
    });
  }
});
