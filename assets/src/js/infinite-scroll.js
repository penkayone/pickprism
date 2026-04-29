// Infinite scroll ленты (редизайн ha-card). Подгружает след. страницу через
// REST /pickprism/v1/feed. Без JS — работает классическая пагинация от PHP.

const cfg = () => (typeof window !== 'undefined' && window.Pickprism) || {};

const escape = (str) =>
  String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

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
      type: (window.Pickprism && window.Pickprism.feed && window.Pickprism.feed.type) || feed.type || 'home',
      value: (window.Pickprism && window.Pickprism.feed && window.Pickprism.feed.value) || feed.value || '',
      paged: String(paged),
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
