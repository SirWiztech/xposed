(function () {
  'use strict';

  // Header shrink/blur on scroll
  const header = document.getElementById('siteHeader');
  if (header) {
    window.addEventListener('scroll', function () {
      header.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
  }

  // Reveal-on-scroll
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('in'); });
  }

  // Animated count-up for stats on the homepage
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length && 'IntersectionObserver' in window) {
    const cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseFloat(el.dataset.count);
        const suffix = el.dataset.countSuffix || '';
        const dur = 1200;
        const t0 = performance.now();
        function step(now) {
          const p = Math.min(1, (now - t0) / dur);
          const eased = 1 - Math.pow(1 - p, 3);
          el.textContent = Math.round(target * eased).toLocaleString('en-US') + suffix;
          if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
        cio.unobserve(el);
      });
    }, { threshold: 0.3 });
    counters.forEach(function (el) { cio.observe(el); });
  }

  // Back button — history.back() with a safe home fallback
  const backBtn = document.getElementById('backBtn');
  if (backBtn) {
    backBtn.addEventListener('click', function () {
      if (document.referrer && document.referrer !== location.href) {
        history.back();
      } else {
        location.href = backBtn.dataset.home || 'index.php';
      }
    });
  }

  // Custom cursor — desktop only
  const cursor = document.getElementById('cursorDot');
  if (cursor && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    window.addEventListener('mousemove', function (e) {
      cursor.style.left = e.clientX + 'px';
      cursor.style.top = e.clientY + 'px';
    }, { passive: true });
    document.querySelectorAll('a, button, .video-card, .product').forEach(function (el) {
      el.addEventListener('mouseenter', function () { cursor.classList.add('expand'); });
      el.addEventListener('mouseleave', function () { cursor.classList.remove('expand'); });
    });
  }

  // Mobile nav — hamburger toggle
  const menuBtn = document.querySelector('.menu-btn');
  const navLinks = document.querySelector('nav.links');
  if (menuBtn && navLinks) {
    function setMenu(open) {
      header.classList.toggle('nav-open', open);
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      menuBtn.textContent = open ? '✕' : '☰';
      menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      document.body.style.overflow = open ? 'hidden' : '';
    }
    menuBtn.addEventListener('click', function () {
      setMenu(!header.classList.contains('nav-open'));
    });
    navLinks.addEventListener('click', function (e) {
      if (e.target.closest('a')) setMenu(false);
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth > 900 && header.classList.contains('nav-open')) setMenu(false);
    });
  }

  // Video rail drag-scroll (desktop)
  const rail = document.getElementById('videoRail');
  if (rail) {
    let down = false, startX = 0, scrollLeft = 0;
    rail.addEventListener('mousedown', function (e) {
      down = true; startX = e.pageX - rail.offsetLeft; scrollLeft = rail.scrollLeft;
      rail.style.cursor = 'grabbing';
    });
    rail.addEventListener('mouseleave', function () { down = false; rail.style.cursor = ''; });
    rail.addEventListener('mouseup', function () { down = false; rail.style.cursor = ''; });
    rail.addEventListener('mousemove', function (e) {
      if (!down) return;
      e.preventDefault();
      const x = e.pageX - rail.offsetLeft;
      rail.scrollLeft = scrollLeft - (x - startX);
    });
  }
})();
