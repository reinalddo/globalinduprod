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
    const isToggleVisible = () => window.getComputedStyle(navToggle).display !== 'none';

    const closeNav = (shouldManageFocus = true) => {
      const shouldRefocusToggle = shouldManageFocus && nav.contains(document.activeElement) && typeof navToggle.focus === 'function';
      navToggle.classList.remove('is-active');
      nav.classList.remove('is-active');
      navToggle.setAttribute('aria-expanded', 'false');
      if (isToggleVisible()) {
        if (shouldRefocusToggle) {
          navToggle.focus({ preventScroll: true });
        }
        nav.setAttribute('inert', '');
      } else {
        nav.removeAttribute('inert');
      }
    };

    const openNav = () => {
      navToggle.classList.add('is-active');
      nav.classList.add('is-active');
      navToggle.setAttribute('aria-expanded', 'true');
      nav.removeAttribute('inert');
    };

    const syncNavVisibility = () => {
      if (isToggleVisible()) {
        const expanded = navToggle.classList.contains('is-active');
        navToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        nav.toggleAttribute('inert', !expanded);
        return;
      }
      nav.classList.remove('is-active');
      nav.removeAttribute('inert');
      navToggle.classList.remove('is-active');
      navToggle.setAttribute('aria-expanded', 'false');
    };

    syncNavVisibility();
    window.requestAnimationFrame(syncNavVisibility);
    window.addEventListener('resize', syncNavVisibility);
    window.addEventListener('load', syncNavVisibility);

    navToggle.addEventListener('click', () => {
      if (!isToggleVisible()) {
        return;
      }
      if (navToggle.classList.contains('is-active')) {
        closeNav();
        closeSearch();
        return;
      }
      openNav();
    });

    nav.querySelectorAll('.navbar-item').forEach((link) => {
      link.addEventListener('click', () => {
        closeNav();
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

  if (searchForm && searchInput) {
    searchForm.addEventListener('submit', (event) => {
      const value = searchInput.value ? searchInput.value.trim() : '';
      if (value === '') {
        event.preventDefault();
        searchInput.value = '';
        searchInput.focus();
        return;
      }
      searchInput.value = value;
      closeSearch();
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
    const clickedFromPointer = new WeakSet();

    languageLinks.forEach((link) => {
      const href = link.getAttribute('href');
      const isRealLink = href && href !== '#';

      link.addEventListener('mousedown', () => {
        clickedFromPointer.add(link);
      });

      if (!isRealLink) {
        link.addEventListener('click', (event) => {
          event.preventDefault();
          languageLinks.forEach((item) => item.classList.remove('is-active'));
          event.currentTarget.classList.add('is-active');
        });
        return;
      }

      link.addEventListener('click', (event) => {
        if (clickedFromPointer.has(link)) {
          clickedFromPointer.delete(link);
          return;
        }
        event.preventDefault();
        window.location.assign(href);
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
    const hasMultipleSlides = slides.length > 1;
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
      if (!hasMultipleSlides) {
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

    if (hasMultipleSlides) {
      let gestureStartX = null;
      let gestureStartY = null;
      const SWIPE_THRESHOLD = 45;

      const storeGestureStart = (x, y) => {
        gestureStartX = x;
        gestureStartY = y;
      };

      const resetGesture = () => {
        gestureStartX = null;
        gestureStartY = null;
      };

      const handleGestureEnd = (x, y) => {
        if (gestureStartX === null || gestureStartY === null) {
          return;
        }
        const deltaX = x - gestureStartX;
        const deltaY = y - gestureStartY;
        resetGesture();
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > SWIPE_THRESHOLD) {
          if (deltaX < 0) {
            updateSlides(currentIndex + 1);
          } else {
            updateSlides(currentIndex - 1);
          }
        }
      };

      if (window.PointerEvent) {
        heroSlider.addEventListener('pointerdown', (event) => {
          if (event.pointerType === 'mouse') {
            return;
          }
          storeGestureStart(event.clientX, event.clientY);
          if (typeof heroSlider.setPointerCapture === 'function') {
            heroSlider.setPointerCapture(event.pointerId);
          }
          stopAutoplay();
        });

        heroSlider.addEventListener('pointerup', (event) => {
          if (event.pointerType === 'mouse') {
            return;
          }
          if (typeof heroSlider.releasePointerCapture === 'function') {
            heroSlider.releasePointerCapture(event.pointerId);
          }
          handleGestureEnd(event.clientX, event.clientY);
          startAutoplay();
        });

        heroSlider.addEventListener('pointercancel', (event) => {
          if (typeof heroSlider.releasePointerCapture === 'function' && event.pointerId != null) {
            heroSlider.releasePointerCapture(event.pointerId);
          }
          resetGesture();
          startAutoplay();
        });
      } else {
        heroSlider.addEventListener('touchstart', (event) => {
          if (event.changedTouches && event.changedTouches[0]) {
            const touch = event.changedTouches[0];
            storeGestureStart(touch.clientX, touch.clientY);
            stopAutoplay();
          }
        }, { passive: true });

        const handleTouchEnd = (event) => {
          if (event.changedTouches && event.changedTouches[0]) {
            const touch = event.changedTouches[0];
            handleGestureEnd(touch.clientX, touch.clientY);
            startAutoplay();
          }
        };

        heroSlider.addEventListener('touchend', handleTouchEnd);
        document.addEventListener('touchend', handleTouchEnd);

        const handleTouchCancel = () => {
          resetGesture();
          startAutoplay();
        };

        heroSlider.addEventListener('touchcancel', handleTouchCancel);
        document.addEventListener('touchcancel', handleTouchCancel);
      }
    }

    if (hasMultipleSlides) {
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
