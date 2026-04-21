// pressaff — tabs по категориям.
// Тикает только при наличии .theme-pressaff на body.
// Загружает статьи категории через /pickprism/v1/feed?category=<slug>&per_page=6.

const cfg = () => (typeof window !== 'undefined' && window.Pickprism) || {};

const escape = (str) =>
  String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

/** Рендерит карточку вкладки в виде pa-card */
function tabCardHTML(item) {
  const thumb = item.thumbnail
    ? `<a class="pa-card__media" href="${escape(item.url)}" tabindex="-1" aria-hidden="true">
        <img
          src="${escape(item.thumbnail.url)}"
          ${item.thumbnail.srcset ? `srcset="${escape(item.thumbnail.srcset)}"` : ''}
          ${item.thumbnail.sizes ? `sizes="${escape(item.thumbnail.sizes)}"` : ''}
          width="${escape(item.thumbnail.width)}"
          height="${escape(item.thumbnail.height)}"
          loading="lazy"
          decoding="async"
          alt="${escape(item.thumbnail.alt || item.title)}"
        />
      </a>`
    : `<div class="pa-card__media">
        <div class="pa-card__media-placeholder">
          <svg width="32" height="32" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="currentColor" d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2Zm-10-7-2.5 3.25L6 12l-3 4h18l-4.5-6-3 4-2.5-3.25Z"/>
          </svg>
        </div>
      </div>`;

  const tag = (item.tags && item.tags.length)
    ? `<a class="pa-card__badge" href="${escape(item.tags[0].url)}">${escape(item.tags[0].name)}</a>`
    : '';

  const authorInitial = item.author ? escape(item.author.charAt(0).toUpperCase()) : 'A';

  return `
    <article class="pa-card reveal">
      ${thumb}
      <div class="pa-card__body">
        ${tag}
        <h3 class="pa-card__title">
          <a href="${escape(item.url)}" rel="bookmark">${escape(item.title)}</a>
        </h3>
        <div class="pa-card__meta">
          <span class="pa-card__author-avatar" aria-hidden="true">${authorInitial}</span>
          <time datetime="${escape(item.dateIso)}">${escape(item.date)}</time>
        </div>
      </div>
    </article>
  `;
}

/** Загружает посты для категории и заполняет panel */
function loadTabPanel(panel, catSlug) {
  const { restUrl, nonce, i18n = {} } = cfg();
  if (!restUrl) return;

  // Если уже загружено — не повторяем
  if (panel.dataset.loaded === 'true') return;

  const loadingEl = panel.querySelector('[data-tab-loading]');
  const errorEl = panel.querySelector('[data-tab-error]');
  const gridEl = panel.querySelector('[data-tab-grid]');

  if (loadingEl) loadingEl.hidden = false;
  if (errorEl) { errorEl.hidden = true; errorEl.textContent = ''; }

  const params = new URLSearchParams({
    type: 'category',
    value: catSlug,
    paged: '1',
    per_page: '6',
  });

  fetch(`${restUrl}feed?${params.toString()}`, {
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-WP-Nonce': nonce || '',
    },
  })
    .then((r) => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    })
    .then((data) => {
      const items = data.items || [];
      if (!gridEl) return;

      if (items.length === 0) {
        gridEl.innerHTML = `<p style="color:var(--c-text-subtle);font-size:var(--fs-sm);grid-column:1/-1">${
          i18n.noResults || 'Статей не найдено'
        }</p>`;
      } else {
        gridEl.innerHTML = items.map(tabCardHTML).join('');
        // Регистрируем анимации reveal
        window.dispatchEvent(new CustomEvent('pickprism:reveal-refresh'));
      }

      panel.dataset.loaded = 'true';
    })
    .catch(() => {
      if (errorEl) {
        errorEl.textContent = i18n.errorGeneric || 'Ошибка загрузки. Попробуйте ещё раз.';
        errorEl.hidden = false;
      }
    })
    .finally(() => {
      if (loadingEl) loadingEl.hidden = true;
    });
}

export function initPressaffTabs() {
  if (!document.body.classList.contains('theme-pressaff')) return;

  const tabsSection = document.querySelector('[data-pa-tabs]');
  if (!tabsSection) return;

  const tabBtns = tabsSection.querySelectorAll('[data-pa-tab]');
  const panels = tabsSection.querySelectorAll('[data-pa-panel]');

  if (!tabBtns.length || !panels.length) return;

  function activateTab(tabBtn) {
    const targetId = tabBtn.dataset.paTab;

    // Переключаем активные классы кнопок
    tabBtns.forEach((btn) => {
      const isActive = btn.dataset.paTab === targetId;
      btn.classList.toggle('is-active', isActive);
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    // Переключаем видимые панели и грузим данные если нужно
    panels.forEach((panel) => {
      const isActive = panel.dataset.paPanel === targetId;
      panel.classList.toggle('is-active', isActive);

      if (isActive) {
        const catSlug = panel.dataset.paCat;
        if (catSlug) {
          loadTabPanel(panel, catSlug);
        }
      }
    });
  }

  tabBtns.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      activateTab(btn);
    });

    // Поддержка клавиатурной навигации
    btn.addEventListener('keydown', (e) => {
      const btnsArr = Array.from(tabBtns);
      const idx = btnsArr.indexOf(btn);

      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        e.preventDefault();
        const next = btnsArr[(idx + 1) % btnsArr.length];
        next.focus();
        activateTab(next);
      } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        e.preventDefault();
        const prev = btnsArr[(idx - 1 + btnsArr.length) % btnsArr.length];
        prev.focus();
        activateTab(prev);
      }
    });
  });

  // Активируем первый таб и загружаем его данные сразу
  const firstActive = tabsSection.querySelector('[data-pa-tab].is-active') || tabBtns[0];
  if (firstActive) {
    activateTab(firstActive);
  }
}
