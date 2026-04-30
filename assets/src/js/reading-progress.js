// Reading progress bar — показывает % прокрутки статьи (на single).
// Обновляется через requestAnimationFrame, уважает prefers-reduced-motion (если motion
// отключен, не мигает анимацией, но цифра всё равно отражает прогресс).

const prefersReducedMotion = () =>
  typeof window !== 'undefined' &&
  window.matchMedia &&
  window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export function initReadingProgress() {
  const progress = document.querySelector('[data-reading-progress]');
  const bar = document.querySelector('[data-reading-progress-bar]');
  const article = document.querySelector('.pa-article');

  if (!progress || !bar || !article) return;

  let ticking = false;

  const update = () => {
    const rect = article.getBoundingClientRect();
    const wh = window.innerHeight || 0;
    const total = rect.height - wh;

    if (total <= 0) {
      bar.style.width = '100%';
      ticking = false;
      return;
    }

    const scrolled = Math.max(0, -rect.top);
    const pct = Math.max(0, Math.min(1, scrolled / total));
    bar.style.width = `${(pct * 100).toFixed(2)}%`;
    ticking = false;
  };

  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    if (prefersReducedMotion()) {
      update();
    } else {
      requestAnimationFrame(update);
    }
  };

  update();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });
}
