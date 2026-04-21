// Expanding search overlay (pressaff-style).
// Кнопка-иконка в шапке раскрывает поле на всю ширину до логотипа.
// Debounced live-поиск через REST /pickprism/v1/search. ESC/клик вне — закрывает.

const cfg = () => (typeof window !== 'undefined' && window.Pickprism) || {};

const escape = (str) =>
  String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

function renderItem(item) {
  const thumb = item.thumbnail
    ? `<span class="search-result__thumb"><img src="${escape(item.thumbnail)}" alt="" loading="lazy" decoding="async"></span>`
    : '<span class="search-result__thumb"></span>';
  return `
    <a href="${escape(item.url)}" class="search-result" role="option">
      ${thumb}
      <span class="search-result__body">
        <span class="search-result__title">${escape(item.title)}</span>
        <span class="search-result__excerpt">${escape(item.excerpt || '')}</span>
      </span>
    </a>
  `;
}

function renderDropdown(dropdown, data) {
  const { i18n = {} } = cfg();
  if (!data || !data.items || data.items.length === 0) {
    dropdown.innerHTML = `<div class="search-empty">${escape(i18n.noResults || 'Ничего не найдено')}</div>`;
    return;
  }
  const items = data.items.map(renderItem).join('');
  const viewAll = data.viewAll
    ? `<a class="search-view-all" href="${escape(data.viewAll)}">${escape(i18n.showAll || 'Показать все результаты')} →</a>`
    : '';
  dropdown.innerHTML = items + viewAll;
}

export function initSearch() {
  const form = document.querySelector('[data-search]');
  if (!form) return;

  const header = document.querySelector('.pa-header');
  const toggleBtn = document.querySelector('[data-search-toggle]');
  const closeBtn = form.querySelector('[data-search-close]');
  const input = form.querySelector('[data-search-input]');
  const dropdown = form.querySelector('[data-search-dropdown]');

  if (!toggleBtn || !input || !dropdown) return;

  const { restUrl, nonce, searchMinLen = 2, searchDebounce = 300, i18n = {} } = cfg();

  let controller = null;
  let timer = 0;
  let isOpen = false;

  const closeDropdown = () => {
    dropdown.hidden = true;
  };
  const openDropdown = () => {
    dropdown.hidden = false;
  };

  const openSearch = () => {
    if (isOpen) return;
    isOpen = true;
    form.classList.add('is-open');
    form.setAttribute('aria-hidden', 'false');
    input.setAttribute('tabindex', '0');
    header && header.classList.add('pa-header--searching');
    // Фокус на инпут — после анимации.
    setTimeout(() => input.focus(), 260);
  };

  const closeSearch = () => {
    if (!isOpen) return;
    isOpen = false;
    form.classList.remove('is-open');
    form.setAttribute('aria-hidden', 'true');
    input.setAttribute('tabindex', '-1');
    header && header.classList.remove('pa-header--searching');
    closeDropdown();
    if (controller) controller.abort();
  };

  toggleBtn.addEventListener('click', (e) => {
    e.preventDefault();
    openSearch();
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', (e) => {
      e.preventDefault();
      closeSearch();
    });
  }

  // ESC закрывает.
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen) {
      closeSearch();
    }
  });

  // Клик вне form (но внутри header) — закрываем.
  document.addEventListener('click', (e) => {
    if (!isOpen) return;
    if (form.contains(e.target)) return;
    if (toggleBtn.contains(e.target)) return;
    closeSearch();
  });

  if (!restUrl) return;

  const run = (query) => {
    if (controller) controller.abort();
    controller = new AbortController();

    dropdown.innerHTML = `<div class="search-loading">${escape(i18n.searching || 'Ищем…')}</div>`;
    openDropdown();

    const url = `${restUrl}search?q=${encodeURIComponent(query)}`;
    const headers = { Accept: 'application/json' };
    if (nonce) headers['X-WP-Nonce'] = nonce;

    fetch(url, { headers, credentials: 'same-origin', signal: controller.signal })
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .then((data) => renderDropdown(dropdown, data))
      .catch((err) => {
        if (err.name === 'AbortError') return;
        dropdown.innerHTML = `<div class="search-empty">${escape(i18n.errorGeneric || 'Ошибка')}</div>`;
      });
  };

  input.addEventListener('input', () => {
    const q = input.value.trim();
    clearTimeout(timer);

    if (q.length < searchMinLen) {
      if (controller) controller.abort();
      closeDropdown();
      return;
    }

    timer = setTimeout(() => run(q), searchDebounce);
  });

  // Не даём отправить форму (используем live-поиск), но оставляем fallback для JS-off:
  // без JS форма идёт на home_url с query ?s= — стандартный поиск WordPress.
}
