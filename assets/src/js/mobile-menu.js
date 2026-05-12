// Мобильный drawer (бургер-меню). Открывается кнопкой [data-menu-toggle],
// закрывается крестиком, кликом по backdrop, клавишей Escape и при resize
// до desktop-брейкпоинта (где меню видно нативно).
const DESKTOP_BP = 960; // должен совпадать с $bp-lg в _tokens.scss

export function initMobileMenu() {
  const toggle = document.querySelector('[data-menu-toggle]');
  const drawer = document.querySelector('[data-drawer]');
  if (!toggle || !drawer) return;

  const closers = drawer.querySelectorAll('[data-drawer-close]');
  let lastFocus = null;

  const open = () => {
    if (drawer.classList.contains('is-open')) return;
    lastFocus = document.activeElement;
    drawer.hidden = false;
    // forсируем reflow, чтобы перехода opacity/transform отработали с hidden→is-open.
    void drawer.offsetWidth;
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    toggle.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    toggle.setAttribute('aria-label', 'Закрыть меню');
    document.body.classList.add('is-menu-open');

    const firstFocusable = drawer.querySelector('a, button, input, [tabindex]:not([tabindex="-1"])');
    if (firstFocusable) firstFocusable.focus({ preventScroll: true });
  };

  const close = () => {
    if (!drawer.classList.contains('is-open')) return;
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    toggle.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Открыть меню');
    document.body.classList.remove('is-menu-open');

    const onEnd = (e) => {
      if (e.target !== drawer.querySelector('.pa-drawer__panel')) return;
      drawer.hidden = true;
      drawer.removeEventListener('transitionend', onEnd);
    };
    drawer.addEventListener('transitionend', onEnd);

    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus({ preventScroll: true });
    }
  };

  toggle.addEventListener('click', () => {
    drawer.classList.contains('is-open') ? close() : open();
  });

  closers.forEach((el) => el.addEventListener('click', close));

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
  });

  // Если ширина выросла до desktop — закрываем (там есть нативное меню).
  let resizeTimer = null;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      if (window.innerWidth >= DESKTOP_BP && drawer.classList.contains('is-open')) {
        close();
      }
    }, 120);
  });
}
