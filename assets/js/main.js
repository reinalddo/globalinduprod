document.addEventListener('DOMContentLoaded', () => {
  const scrollTopBtn = document.querySelector('[data-scroll-top]');
  const navToggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-primary-nav]');
  const navSearch = document.querySelector('[data-search-panel]');
  const searchToggle = navSearch ? navSearch.querySelector('.nav-search__toggle') : null;
  const searchForm = navSearch ? navSearch.querySelector('.nav-search__form') : null;
  const searchInput = searchForm ? searchForm.querySelector('input[type="search"]') : null;
  const siteHeader = document.querySelector('.site-header');
  const languageLinks = document.querySelectorAll('.nav-languages__link');
  const heroSlider = document.querySelector('[data-hero-slider]');

  if ('scrollRestoration' in window.history) {
    window.history.scrollRestoration = 'manual';
  }

  window.addEventListener('load', () => {
    if (!window.location.hash) {
      window.scrollTo({ top: 0, behavior: 'auto' });
    }
  });

  if (scrollTopBtn) {
    window.addEventListener('scroll', () => {
      scrollTopBtn.classList.toggle('is-visible', window.scrollY > 360);
    });
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  const closeSearch = () => {
    if (!navSearch || !searchForm) {
      return;
    }
    navSearch.classList.remove('is-active');
    if (!searchForm.hidden) {
      searchForm.hidden = true;
    }
    if (searchToggle) {
      searchToggle.setAttribute('aria-expanded', 'false');
    }
  };

  const openSearch = () => {
    if (!navSearch || !searchForm) {
      return;
    }
    navSearch.classList.add('is-active');
    if (searchForm.hidden) {
      searchForm.hidden = false;
    }
    if (searchToggle) {
      searchToggle.setAttribute('aria-expanded', 'true');
    }
    if (searchInput) {
      window.requestAnimationFrame(() => searchInput.focus());
    }
  };

  if (navToggle && nav) {
    navToggle.setAttribute('aria-expanded', 'false');
    nav.setAttribute('aria-hidden', 'true');

    navToggle.addEventListener('click', () => {
      const isActive = navToggle.classList.toggle('is-active');
      nav.classList.toggle('is-active', isActive);
      navToggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
      nav.setAttribute('aria-hidden', isActive ? 'false' : 'true');
      if (!isActive) {
        closeSearch();
      }
    });
    nav.querySelectorAll('.navbar-item').forEach((link) => {
      link.addEventListener('click', () => {
        navToggle.classList.remove('is-active');
        nav.classList.remove('is-active');
        navToggle.setAttribute('aria-expanded', 'false');
        nav.setAttribute('aria-hidden', 'true');
        closeSearch();
      });
    });
  }

  if (searchToggle && searchForm && navSearch) {
    searchToggle.addEventListener('click', (event) => {
      event.preventDefault();
      const expanded = searchToggle.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        closeSearch();
      } else {
        openSearch();
      }
    });

    document.addEventListener('click', (event) => {
      if (!navSearch.contains(event.target)) {
        closeSearch();
      }
    });

    window.addEventListener('keyup', (event) => {
      if (event.key === 'Escape') {
        closeSearch();
      }
    });
  }

  if (siteHeader) {
    const syncHeaderStyle = () => {
      siteHeader.classList.toggle('is-condensed', window.scrollY > 60);
    };
    syncHeaderStyle();
    window.addEventListener('scroll', syncHeaderStyle, { passive: true });
  }

  if (languageLinks.length) {
    languageLinks.forEach((link) => {
      link.addEventListener('click', (event) => {
        event.preventDefault();
        languageLinks.forEach((item) => item.classList.remove('is-active'));
        event.currentTarget.classList.add('is-active');
      });
    });
  }

  if (heroSlider) {
    const track = heroSlider.querySelector('[data-hero-track]');
    const slides = track ? Array.from(track.querySelectorAll('[data-hero-slide]')) : [];
    const prevBtn = heroSlider.querySelector('[data-hero-prev]');
    const nextBtn = heroSlider.querySelector('[data-hero-next]');
    const dotsContainer = heroSlider.querySelector('[data-hero-dots]');
    const dots = dotsContainer ? Array.from(dotsContainer.querySelectorAll('[data-hero-dot]')) : [];
    const AUTOPLAY_DELAY = 10000;
    let currentIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
    if (currentIndex < 0) {
      currentIndex = 0;
      if (slides[0]) {
        slides[0].classList.add('is-active');
      }
      if (dots[0]) {
        dots[0].classList.add('is-active');
      }
    }
    dots.forEach((dot) => {
      dot.setAttribute('aria-pressed', dot.classList.contains('is-active') ? 'true' : 'false');
    });
    let autoTimer = null;

    const updateSlides = (newIndex) => {
      if (!slides.length) {
        return;
      }
      const boundedIndex = (newIndex + slides.length) % slides.length;
      const activeSlide = track.querySelector('.hero-slide.is-active');
      const activeDot = dotsContainer ? dotsContainer.querySelector('.hero-slider__dot.is-active') : null;
      if (activeSlide) {
        activeSlide.classList.remove('is-active');
      }
      slides[boundedIndex].classList.add('is-active');
      if (dots.length) {
        if (activeDot) {
          activeDot.classList.remove('is-active');
          activeDot.setAttribute('aria-pressed', 'false');
        }
        const targetDot = dots[boundedIndex];
        if (targetDot) {
          targetDot.classList.add('is-active');
          targetDot.setAttribute('aria-pressed', 'true');
        }
      }
      currentIndex = boundedIndex;
    };

    const startAutoplay = () => {
      if (slides.length <= 1) {
        return;
      }
      if (autoTimer) {
        window.clearInterval(autoTimer);
      }
      autoTimer = window.setInterval(() => {
        updateSlides(currentIndex + 1);
      }, AUTOPLAY_DELAY);
    };

    const stopAutoplay = () => {
      if (autoTimer) {
        window.clearInterval(autoTimer);
        autoTimer = null;
      }
    };

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        updateSlides(currentIndex - 1);
        startAutoplay();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        updateSlides(currentIndex + 1);
        startAutoplay();
      });
    }

    if (dots.length) {
      dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          updateSlides(index);
          startAutoplay();
        });
      });
    }

    if (slides.length > 1) {
      heroSlider.addEventListener('mouseenter', stopAutoplay);
      heroSlider.addEventListener('mouseleave', startAutoplay);
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
          startAutoplay();
        } else {
          stopAutoplay();
        }
      });

      startAutoplay();
    }
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
