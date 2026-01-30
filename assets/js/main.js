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

  const assignAosDefaults = () => {
    const applyAnimation = (selector, animation, options = {}) => {
      const {
        delayStep = 0,
        baseDelay = 0,
        duration,
        easing,
        offset,
      } = options;
      const elements = document.querySelectorAll(selector);
      if (!elements.length) {
        return;
      }
      elements.forEach((element, index) => {
        if (!element.hasAttribute('data-aos')) {
          element.setAttribute('data-aos', animation);
        }
        if (delayStep || baseDelay) {
          const delayValue = baseDelay + index * delayStep;
          if (!element.hasAttribute('data-aos-delay')) {
            element.setAttribute('data-aos-delay', String(delayValue));
          }
        }
        if (duration && !element.hasAttribute('data-aos-duration')) {
          element.setAttribute('data-aos-duration', String(duration));
        }
        if (typeof easing === 'string' && !element.hasAttribute('data-aos-easing')) {
          element.setAttribute('data-aos-easing', easing);
        }
        if (typeof offset === 'number' && !element.hasAttribute('data-aos-offset')) {
          element.setAttribute('data-aos-offset', String(offset));
        }
      });
    };

    applyAnimation('main section', 'fade-up', { offset: 160 });
    applyAnimation('.hero .hero-content > *', 'fade-up', { delayStep: 120, duration: 800 });
    applyAnimation('.section-heading', 'fade-up', { delayStep: 100 });
    applyAnimation('.brand-grid .brand-card', 'zoom-in', { delayStep: 70, baseDelay: 70, duration: 650 });
    applyAnimation('.services-grid .service-card', 'fade-up', { delayStep: 90, baseDelay: 180, duration: 720 });
    applyAnimation('.clients-grid .client-card', 'fade-up', { delayStep: 70, baseDelay: 140, duration: 680 });
    applyAnimation('.home-services__cta .home-services__link, .cta, .button.is-primary', 'zoom-in', { baseDelay: 320, duration: 620 });
    applyAnimation('main .card, main article', 'fade-up', { delayStep: 80, offset: 140 });
  };

  assignAosDefaults();

  if (window.AOS && typeof window.AOS.init === 'function') {
    window.AOS.init({
      duration: 750,
      easing: 'ease-out-cubic',
      offset: 120,
      once: true,
    });
    window.addEventListener('load', () => {
      if (window.AOS && typeof window.AOS.refreshHard === 'function') {
        window.AOS.refreshHard();
      }
    });
  }
});
