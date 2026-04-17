// Живой поиск: debounce + AbortController + dropdown с результатами.
// Nonce заголовок X-WP-Nonce на случай работы поверх REST с авторизованной сессией.

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
    : '';
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

function renderDropdown(dropdown, data, query) {
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
  void query; // зарезервировано на случай подсветки.
}

function bindForm(form) {
  const input = form.querySelector('[data-search-input]');
  const dropdown = form.querySelector('[data-search-dropdown]');
  if (!input || !dropdown) return;

  const { restUrl, nonce, searchMinLen = 2, searchDebounce = 300, i18n = {} } = cfg();
  if (!restUrl) return;

  let controller = null;
  let timer = 0;

  const close = () => {
    dropdown.hidden = true;
    input.setAttribute('aria-expanded', 'false');
  };
  const open = () => {
    dropdown.hidden = false;
    input.setAttribute('aria-expanded', 'true');
  };

  const run = (query) => {
    if (controller) controller.abort();
    controller = new AbortController();

    dropdown.innerHTML = `<div class="search-loading">${escape(i18n.searching || 'Ищем…')}</div>`;
    open();

    const url = `${restUrl}search?q=${encodeURIComponent(query)}`;
    const headers = { Accept: 'application/json' };
    if (nonce) headers['X-WP-Nonce'] = nonce;

    fetch(url, { headers, credentials: 'same-origin', signal: controller.signal })
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .then((data) => renderDropdown(dropdown, data, query))
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
      close();
      return;
    }

    timer = setTimeout(() => run(q), searchDebounce);
  });

  input.addEventListener('focus', () => {
    if (input.value.trim().length >= searchMinLen && dropdown.innerHTML.trim()) {
      open();
    }
  });

  document.addEventListener('click', (e) => {
    if (!form.contains(e.target)) close();
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      close();
      input.blur();
    }
  });
}

export function initSearch() {
  document.querySelectorAll('[data-search]').forEach(bindForm);
}
