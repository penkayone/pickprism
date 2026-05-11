// Pickprism — entry point (редизайн pressaff-style).
import '../scss/main.scss';

import { initSearch } from './search.js';
import { initInfiniteScroll } from './infinite-scroll.js';
import { initRevealAnimations } from './animations.js';
import { initComments } from './comments.js';
import { initReadingProgress } from './reading-progress.js';
import { initScrollTop } from './scroll-top.js';

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
  document.querySelectorAll('[data-copy-link]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const url = btn.dataset.copyLink || '';
      if (!url || !navigator.clipboard) return;
      navigator.clipboard.writeText(url).then(() => {
        const original = btn.getAttribute('aria-label') || '';
        btn.setAttribute('aria-label', 'Скопировано');
        btn.style.color = 'var(--c-accent)';
        setTimeout(() => {
          btn.setAttribute('aria-label', original);
          btn.style.color = '';
        }, 1600);
      });
    });
  });
});
