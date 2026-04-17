// Pickprism — entry point.
import '../scss/main.scss';

import { initSearch } from './search.js';
import { initInfiniteScroll } from './infinite-scroll.js';
import { initRevealAnimations } from './animations.js';

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

  initSearch();
  initInfiniteScroll();
  initRevealAnimations();
});
