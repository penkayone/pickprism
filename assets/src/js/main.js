// Pickprism — entry point (редизайн pressaff-style).
import '../scss/main.scss';

import { initSearch } from './search.js';
import { initInfiniteScroll } from './infinite-scroll.js';
import { initRevealAnimations } from './animations.js';
import { initComments } from './comments.js';
import { initReadingProgress } from './reading-progress.js';
import { initScrollTop } from './scroll-top.js';
import { initMobileMenu } from './mobile-menu.js';

const ready = (fn) => {
  if (document.readyState !== 'loading') {
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', fn, { once: true });
  }
};

ready(() => {
  document.documentElement.classList.remove('no-js');
  document.documentElement.classList.add('has-js');

  // Всегда — search (в шапке на всех страницах).
  initSearch();

  // Reveal-анимации для .reveal-элементов — всегда.
  initRevealAnimations();

  // Кнопка «Наверх» — всегда, JS сам проверит наличие [data-scroll-top].
  initScrollTop();

  // Мобильный бургер-drawer — модуль сам проверит наличие toggle/drawer.
  initMobileMenu();

  // Лента / infinite scroll — только если на странице есть feed-container.
  if (document.querySelector('[data-feed-container]')) {
    initInfiniteScroll();
  }

  // Reading progress — только на single post.
  if (document.querySelector('[data-reading-progress]')) {
    initReadingProgress();
  }

  // Comments — только если есть форма/список.
  if (document.querySelector('.comment-form') || document.querySelector('.pa-clist')) {
    initComments();
  }

  // Копирование ссылки (mobile share bar + share-horizontal).
  const copyToClipboard = (text) => {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    // Fallback для http/insecure context (например, *.local в dev).
    return new Promise((resolve, reject) => {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.setAttribute('readonly', '');
      ta.style.position = 'fixed';
      ta.style.top = '0';
      ta.style.left = '0';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      try {
        const ok = document.execCommand('copy');
        document.body.removeChild(ta);
        ok ? resolve() : reject(new Error('execCommand copy failed'));
      } catch (err) {
        document.body.removeChild(ta);
        reject(err);
      }
    });
  };

  document.querySelectorAll('[data-copy-link]').forEach((btn) => {
    let resetTimer = null;
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const url = btn.dataset.copyLink || '';
      if (!url) return;
      copyToClipboard(url).then(() => {
        const original = btn.getAttribute('aria-label') || '';
        btn.setAttribute('aria-label', 'Скопировано');
        btn.classList.add('is-copied');
        clearTimeout(resetTimer);
        resetTimer = setTimeout(() => {
          btn.setAttribute('aria-label', original);
          btn.classList.remove('is-copied');
        }, 1600);
      }).catch(() => {});
    });
  });
});
