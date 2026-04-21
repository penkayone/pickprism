// Category tabs на главной — фильтрация ленты через REST /pickprism/v1/feed.
// Клик на таб → AJAX-подгрузка первой страницы → подмена ha-feed__grid.
// Сбрасывает состояние infinite-scroll (через событие pickprism:feed-reset).

const cfg = () => (typeof window !== 'undefined' && window.Pickprism) || {};

const escape = (str) =>
  String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

// Конвертер REST-item → HTML карточки (совпадает с infinite-scroll.js).
function cardHTML(item) {
  const hue = typeof item.hue === 'number' ? item.hue : 24;
  const categoryName = item.primaryCategory ? item.primaryCategory.name : '';
  const categoryLetter = categoryName ? categoryName.charAt(0).toUpperCase() : 'P';

  let cover = '';
  if (item.thumbnail && item.thumbnail.url) {
    cover = `
      <div class="ha-cover">
        <img class="ha-cover__img"
          src="${escape(item.thumbnail.url)}"
          ${item.thumbnail.srcset ? `srcset="${escape(item.thumbnail.srcset)}"` : ''}
          ${item.thumbnail.sizes ? `sizes="${escape(item.thumbnail.sizes)}"` : ''}
          width="${escape(item.thumbnail.width || 800)}"
          height="${escape(item.thumbnail.height || 500)}"
          loading="lazy"
          decoding="async"
          alt="${escape(item.thumbnail.alt || item.title)}"
        />
        ${categoryName ? `<span class="ha-cover__cat">${escape(categoryName)}</span>` : ''}
      </div>
    `;
  } else {
    cover = `
      <div class="ha-cover" style="--hue: ${hue};">
        <div class="ha-cover__bg" aria-hidden="true"></div>
        <div class="ha-cover__letter" aria-hidden="true">${escape(categoryLetter)}</div>
        ${categoryName ? `<span class="ha-cover__cat">${escape(categoryName)}</span>` : ''}
      </div>
    `;
  }

  return `
    <a class="ha-card reveal" href="${escape(item.url)}" data-post-id="${escape(item.id)}">
      ${cover}
      <div class="ha-card__body">
        <div class="ha-card__meta">
          ${item.isNew ? `<span class="ha-card__new">Новое</span>` : ''}
          <span class="ha-card__date"><time datetime="${escape(item.dateIso)}">${escape(item.date)}</time></span>
          <span class="ha-card__dot" aria-hidden="true">·</span>
          <span class="ha-card__read">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            ${escape(item.readTime || 1)} мин
          </span>
        </div>
        <h3 class="ha-card__title">${escape(item.title)}</h3>
        ${item.excerpt ? `<p class="ha-card__excerpt">${escape(item.excerpt)}</p>` : ''}
      </div>
    </a>
  `;
}

export function initCategoryTabs() {
  const tabs = document.querySelectorAll('[data-feed-tab]');
  if (!tabs.length) return;

  const container = document.querySelector('[data-feed-container]');
  if (!container) return;

  const list = container.querySelector('[data-feed-list]');
  if (!list) return;

  const { restUrl, feed, i18n = {} } = cfg();
  if (!restUrl || !feed) return;

  let isLoading = false;

  const activate = (btn) => {
    tabs.forEach((t) => {
      t.classList.remove('is-active');
      t.setAttribute('aria-selected', 'false');
    });
    btn.classList.add('is-active');
    btn.setAttribute('aria-selected', 'true');
  };

  const loadTab = (type, value) => {
    if (isLoading) return;
    isLoading = true;
    list.setAttribute('aria-busy', 'true');

    const params = new URLSearchParams({
      type,
      value: value || '',
      paged: '1',
      per_page: String(feed.perPage || 12),
    });

    fetch(`${restUrl}feed?${params.toString()}`, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .then((data) => {
        const items = data.items || [];
        list.innerHTML = items.length
          ? items.map(cardHTML).join('')
          : `<p class="empty-state__text">${escape(i18n.noResults || 'Ничего не найдено')}</p>`;

        // Обновляем window.Pickprism.feed чтобы infinite-scroll знал текущий контекст.
        if (window.Pickprism && window.Pickprism.feed) {
          window.Pickprism.feed.type = type;
          window.Pickprism.feed.value = value || '';
          window.Pickprism.feed.paged = 1;
        }

        // Сигнал для infinite-scroll: сбросить paged и сенситель.
        window.dispatchEvent(
          new CustomEvent('pickprism:feed-reset', { detail: { type, value, hasMore: !!data.hasMore } })
        );
        window.dispatchEvent(new CustomEvent('pickprism:reveal-refresh'));
      })
      .catch(() => {
        list.innerHTML = `<p class="empty-state__text">${escape(i18n.errorGeneric || 'Ошибка')}</p>`;
      })
      .finally(() => {
        isLoading = false;
        list.removeAttribute('aria-busy');
      });
  };

  tabs.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const type = btn.dataset.feedType || 'home';
      const value = btn.dataset.feedValue || '';
      activate(btn);
      loadTab(type, value);
    });
  });
}
