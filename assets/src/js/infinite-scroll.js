// Infinite scroll ленты.
// Подгружает следующую страницу через REST /pickprism/v1/feed.
// Если JS отключён — работает обычная пагинация (её рендерит PHP).

const cfg = () => (typeof window !== 'undefined' && window.Pickprism) || {};

const escape = (str) =>
  String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

function cardHTML(item) {
  const tags = (item.tags || [])
    .map(
      (t) =>
        `<a class="chip chip--tag chip--sm" href="${escape(t.url)}">#${escape(t.name)}</a>`
    )
    .join('');

  const media = item.thumbnail
    ? `<a class="card__media" href="${escape(item.url)}" tabindex="-1" aria-hidden="true">
        <img class="card__img"
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
    : '';

  return `
    <article class="card card--article reveal" data-post-id="${escape(item.id)}">
      ${tags ? `<div class="card__tags">${tags}</div>` : ''}
      <h2 class="card__title"><a href="${escape(item.url)}" rel="bookmark">${escape(item.title)}</a></h2>
      ${media}
      <div class="card__excerpt">${escape(item.excerpt || '')}</div>
      <div class="card__meta">
        <time datetime="${escape(item.dateIso)}">${escape(item.date)}</time>
      </div>
    </article>
  `;
}

export function initInfiniteScroll() {
  const container = document.querySelector('[data-feed-container]');
  if (!container) return;

  const list = container.querySelector('[data-feed-list]');
  const sentinel = container.querySelector('[data-feed-sentinel]');
  const loadMoreBtn = container.querySelector('[data-feed-load-more]');
  const status = container.querySelector('[data-feed-status]');
  const linksBlock = container.querySelector('.pagination__links');

  if (!list) return;

  const { restUrl, feed, i18n = {} } = cfg();
  if (!restUrl || !feed) return;

  let paged = (feed.paged || 1) + 1;
  let isLoading = false;
  let ended = false;
  let observer = null;

  // Поддерживаем оба режима: автоподгрузка + кнопка «показать ещё».
  // Прячем классическую нумерованную пагинацию — JS берёт её функции.
  if (linksBlock) linksBlock.hidden = true;
  if (sentinel) sentinel.hidden = false;
  if (loadMoreBtn) loadMoreBtn.hidden = false;

  const setStatus = (text) => {
    if (status) status.textContent = text || '';
  };

  const loadNext = () => {
    if (isLoading || ended) return;
    isLoading = true;
    setStatus(i18n.loading || 'Загружаем…');
    if (loadMoreBtn) loadMoreBtn.disabled = true;

    const params = new URLSearchParams({
      type: feed.type || 'home',
      value: feed.value || '',
      paged: String(paged),
      per_page: String(feed.perPage || 10),
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
        if (items.length === 0 && !data.hasMore) {
          ended = true;
          setStatus(i18n.endOfFeed || 'Это все статьи');
          if (loadMoreBtn) loadMoreBtn.hidden = true;
          if (observer && sentinel) observer.unobserve(sentinel);
          return;
        }

        const fragment = document.createDocumentFragment();
        const holder = document.createElement('div');
        holder.innerHTML = items.map(cardHTML).join('');
        while (holder.firstChild) fragment.appendChild(holder.firstChild);
        list.appendChild(fragment);

        // Регистрируем новые reveal-элементы.
        window.dispatchEvent(new CustomEvent('pickprism:reveal-refresh'));

        paged += 1;
        if (!data.hasMore) {
          ended = true;
          setStatus(i18n.endOfFeed || 'Это все статьи');
          if (loadMoreBtn) loadMoreBtn.hidden = true;
          if (observer && sentinel) observer.unobserve(sentinel);
        } else {
          setStatus('');
        }
      })
      .catch(() => {
        setStatus(i18n.errorGeneric || 'Ошибка');
      })
      .finally(() => {
        isLoading = false;
        if (loadMoreBtn) loadMoreBtn.disabled = false;
      });
  };

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', loadNext);
  }

  if (sentinel && 'IntersectionObserver' in window) {
    observer = new IntersectionObserver(
      (entries) => {
        for (const e of entries) {
          if (e.isIntersecting) loadNext();
        }
      },
      { rootMargin: '400px 0px' }
    );
    observer.observe(sentinel);
  }
}
