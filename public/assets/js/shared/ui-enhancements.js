(() => {
  const HEADER_SELECTOR = '.public-header, .topbar';

  const getHeaderOffset = () => {
    const header = document.querySelector(HEADER_SELECTOR);
    if (!header) {
      return 0;
    }

    return Math.ceil(header.getBoundingClientRect().height);
  };

  const scrollToHash = (hash) => {
    if (!hash || hash === '#') {
      return;
    }

    const target = document.querySelector(hash);
    if (!target) {
      return;
    }

    const offset = getHeaderOffset() + 12;
    const top = window.scrollY + target.getBoundingClientRect().top - offset;
    window.scrollTo({ top, behavior: 'smooth' });
  };

  const initSmoothScroll = () => {
    const anchors = document.querySelectorAll('a[href*="#"]');
    anchors.forEach((anchor) => {
      anchor.addEventListener('click', (event) => {
        const href = anchor.getAttribute('href') || '';
        if (!href.includes('#')) {
          return;
        }

        const url = new URL(href, window.location.href);
        const isSamePage =
          url.origin === window.location.origin &&
          url.pathname === window.location.pathname;

        if (!isSamePage || !url.hash) {
          return;
        }

        event.preventDefault();
        history.replaceState(null, '', url.hash);
        scrollToHash(url.hash);
      });
    });

    if (window.location.hash) {
      window.setTimeout(() => scrollToHash(window.location.hash), 80);
    }
  };

  const initScrollTopButton = () => {
    const button = document.querySelector('[data-scroll-top]');
    if (!button) {
      return;
    }

    const toggleVisibility = () => {
      if (window.scrollY > 360) {
        button.classList.add('is-visible');
      } else {
        button.classList.remove('is-visible');
      }
    };

    window.addEventListener('scroll', toggleVisibility, { passive: true });
    toggleVisibility();

    button.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  };

  const initMenuToggles = () => {
    const toggles = document.querySelectorAll('[data-menu-toggle]');
    toggles.forEach((toggle) => {
      const targetId = toggle.getAttribute('data-menu-target');
      if (!targetId) {
        return;
      }

      const menu = document.getElementById(targetId);
      if (!menu) {
        return;
      }

      const closeMenu = () => {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir menu principal');
      };

      toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute(
          'aria-label',
          open ? 'Fechar menu principal' : 'Abrir menu principal'
        );
      });

      menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
          if (window.matchMedia('(max-width: 960px)').matches) {
            closeMenu();
          }
        });
      });

      window.addEventListener('resize', () => {
        if (!window.matchMedia('(max-width: 960px)').matches) {
          closeMenu();
        }
      });

      document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
          return;
        }

        if (target.closest('[data-menu-toggle]') === toggle || target.closest(`#${targetId}`)) {
          return;
        }

        if (window.matchMedia('(max-width: 960px)').matches) {
          closeMenu();
        }
      });
    });
  };

  const initNavActiveTracking = () => {
    const navs = document.querySelectorAll('[data-nav-track]');
    navs.forEach((nav) => {
      const setActiveByLocation = () => {
        const currentPath = window.location.pathname;
        const currentHash = window.location.hash;

        if (!currentHash) {
          return;
        }

        const matching = Array.from(nav.querySelectorAll('a')).find((link) => {
          const href = link.getAttribute('href') || '';
          if (!href.includes('#')) {
            return false;
          }

          const url = new URL(href, window.location.href);
          return url.pathname === currentPath && url.hash === currentHash;
        });

        if (!matching) {
          return;
        }

        nav.querySelectorAll('a.is-active').forEach((active) => {
          active.classList.remove('is-active');
        });
        matching.classList.add('is-active');
      };

      nav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
          nav.querySelectorAll('a.is-active').forEach((active) => {
            active.classList.remove('is-active');
          });

          link.classList.add('is-active');
        });
      });

      setActiveByLocation();
    });
  };

  const initRevealEffects = () => {
    const targets = document.querySelectorAll(
      '.landing-hero-inner, .landing-section-header, .landing-grid-3, .landing-grid-2, .landing-metrics, .landing-cta-strip, .table-card, .auth-card'
    );

    if (targets.length === 0) {
      return;
    }

    targets.forEach((target) => {
      target.classList.add('reveal-on-scroll');
    });

    targets.forEach((target) => {
      let order = 0;
      Array.from(target.children).forEach((child) => {
        if (!(child instanceof HTMLElement)) {
          return;
        }

        if (child.classList.contains('reveal-on-scroll')) {
          return;
        }

        child.classList.add('reveal-cascade-item');
        if (!child.style.getPropertyValue('--reveal-delay')) {
          child.style.setProperty('--reveal-delay', `${Math.min(order * 0.16, 1.2).toFixed(2)}s`);
        }
        order += 1;
      });
    });

    const activateReveal = (element) => {
      window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
          element.classList.add('is-visible');
        });
      });
    };

    if (!('IntersectionObserver' in window)) {
      targets.forEach((target) => {
        activateReveal(target);
      });
      return;
    }

    const observer = new IntersectionObserver(
      (entries, currentObserver) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          activateReveal(entry.target);
          currentObserver.unobserve(entry.target);
        });
      },
      {
        threshold: 0.08,
        rootMargin: '0px 0px -6% 0px',
      }
    );

    targets.forEach((target) => {
      observer.observe(target);
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    initSmoothScroll();
    initScrollTopButton();
    initMenuToggles();
    initNavActiveTracking();
    initRevealEffects();
  });
})();
