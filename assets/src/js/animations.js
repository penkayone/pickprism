// Reveal при скролле через IntersectionObserver.
// Уважает prefers-reduced-motion.

const prefersReducedMotion = () =>
  typeof window !== 'undefined' &&
  window.matchMedia &&
  window.matchMedia('(prefers-reduced-motion: reduce)').matches;

let revealObserver = null;

function observeAll() {
  if (!revealObserver) return;
  document.querySelectorAll('.reveal:not([data-reveal])').forEach((el) => {
    revealObserver.observe(el);
  });
}

export function initRevealAnimations() {
  if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
    document.querySelectorAll('.reveal').forEach((el) => el.setAttribute('data-reveal', 'in'));
    return;
  }

  revealObserver = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          entry.target.setAttribute('data-reveal', 'in');
          revealObserver.unobserve(entry.target);
        }
      }
    },
    { rootMargin: '0px 0px -10% 0px', threshold: 0.05 }
  );

  observeAll();

  // Перерегистрация после infinite-scroll.
  window.addEventListener('pickprism:reveal-refresh', observeAll);
}
