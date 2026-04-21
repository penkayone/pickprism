# PLAN — Редизайн темы pickprism → pressaff-style

**Ветка:** `feature/redesign` (уже создана, текущая)
**Дата:** 2026-04-21
**Статус:** утверждён заказчиком (A/A + доп. уточнения по cover/reading-time/«Новое»/авторам)

## Задача

Полный редизайн визуала темы под стилистику pressaff.com: тёплый оранжевый акцент + Manrope для заголовков + новая информационная архитектура главной (тёмный hero, секция категорий, лента с табами, расширенный sidebar) и страницы статьи (full-bleed hero, узкий контент, TL;DR/callouts/pullquote/inline-CTA, sidebar, mobile share bar, прогресс-бар чтения). Бэкенд-логику (REST-поиск, sticky, кэш, comments) — **не трогать**, только разметка и стили.

## Источник дизайна

Handoff-bundle от Claude Design распакован в `/tmp/pickprism-design/home/pickprism/project/`:

- `Home.html` + `home-components.jsx` + `home-styles.css` — главная
- `Article.html` + `article-content.jsx` + `article-layouts.jsx` + `article-styles.css` — статья
- `article-shared.jsx` — общие компоненты (header, footer, sidebar-блоки, TL;DR, callouts, pull-quote, inline CTA, author, prevnext, related, comments, mobile share bar, reading progress)

## Утверждённые решения (A/A)

1. **Секция «Категории» ПЕРЕД лентой на главной** — 6 плиток-иконок, класс `.ha-cats`. Для блога с 1000 статей даёт полезную навигацию сверху. CSS-скаффолд у дизайнера уже есть.
2. **Единый sidebar на 6 блоков** (categories, trending, telegram, newsletter, authors, tags) — переиспользуется на home и на single. Меньше кода, консистентность, Telegram-CTA на статье конвертирует лучше всего.

## Дополнительные уточнения (финальные от заказчика)

### Cover fallback (featured image)
- Если есть `the_post_thumbnail` — показываем её.
- Если нет — CSS-фон через `linear-gradient` с **детерминированным hue**, посчитанным от `term_id` (fallback: `slug`) **первичной категории** поста. Один и тот же пост всегда имеет один и тот же фон.
- Формула: `hue = abs(crc32(term_id ?: slug)) % 360`. Передаём через inline `style="--hue: 24"` на элементе `.ha-card__cover` / `.pa-card__cover`.
- Поверх фона — **первая буква категории** как крупный декор (моноширинно, 120-180px, Manrope, полупрозрачно). Для sticky широкой карточки — буква крупнее.
- Хелпер: `pickprism_cover_hue( int $post_id ): int` в `inc/template-helpers.php` (новый файл).

### Время чтения
- Рассчёт: `ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 )`, минимум `1` мин.
- **Кэш**: `post_meta '_pickprism_reading_time'`.
- Хуки:
  - `save_post` (non-revision, non-autosave) → пересчёт и `update_post_meta`.
  - При выводе: если мета пусто → посчитать на лету + записать в мету (ленивое заполнение для старых постов).
- Хелпер: `pickprism_reading_time( int $post_id ): int` в `inc/template-helpers.php`.
- Вывод: «%d мин», `_n( '%d мин', '%d мин', $n, 'pickprism' )` (пока одна форма).

### Бейдж «Новое»
- Условие: `get_post_time( 'U', true, $post_id ) > strtotime( '-7 days' )`.
- Хелпер: `pickprism_is_new( int $post_id ): bool`.
- Выводим только на `card-article.php` (обычные карточки ленты) и `card-article-sticky.php`.

### «Авторы недели» (sidebar-authors)
- `get_users( [ 'orderby' => 'post_count', 'order' => 'DESC', 'number' => 4, 'has_published_posts' => [ 'post' ] ] )`.
- Avatar через `get_avatar_url( $user_id, [ 'size' => 80 ] )`.
- Fallback: если аватар не вернулся — CSS gradient (тот же hue-механизм, но от `user_id`).
- Кэш: transient `pickprism_authors_week`, TTL 6ч, инвалидация на `save_post` / `delete_user`.

## Стек

PHP 8+ / WordPress (classic) + Vite + SCSS + Vanilla JS. Без React.

## Дизайн-токены (полная замена)

| Token | Value |
|---|---|
| Accent | `#ff7a1a` (hover `#e86a0c`, soft `#fff0e3`, soft-2 `#ffe0c4`) |
| BG article | `#fbf9f6` |
| BG home | `#f6f1e7` |
| Dark | `#10131a` / `#14171f` (header nav, cards newsletter) |
| Text | `#14171f` main, `#3d4350` secondary, `#6b7280` muted, `#9097a3` subtle |
| Border | `#ebe6dd` / `#d9d2c6` strong |
| Display font | Manrope 500–900 |
| Body font | Inter 400–700 |
| Body size | 17px, line-height 1.72 |
| Body width | 760px (medium) |
| Radii | 10 / 12 / 14 / 16 / 20 / 24 |
| Container max | 1280px |

## Решение

### 1. SCSS (переписываем большинство партиалов)

**`abstracts/_tokens.scss`** — полная замена палитры/шрифтов/радиусов/теней под pressaff.
**`base/_typography.scss`** — Manrope для h1–h4, Inter для body/UI.
**`base/_layout.scss`** — container 1280px.
**`components/_header.scss`** — sticky blur + expanding search overlay + лого-марка.
**`components/_hero.scss`** → два варианта:
- `.hero--home` — тёмный, centered, 88px Manrope, звёзды/свечения, кнопки, статы
- `.hero--article` — full-bleed, min-height 620-680px, breadcrumbs, кат-чип, title, meta, scroll-hint
**`components/_card.scss`** — cover с hue-градиентом по категории (через `var(--hue)`), бейдж «Новое», иконка чтения, padding 22px.
**`components/_sidebar.scss`** — 6 блоков (categories, trending, telegram-CTA, newsletter, authors, tags), sticky `top: 24px`.
**`components/_article.scss`** — контент, TL;DR, callouts (info/warn/tip), pull-quote, inline-CTA (тёмный со звёздами), author-block, prev/next, share horizontal, mobile-share-bar.
**`components/_comments.scss`** — новая разметка `.pa-comment` + replies с thread-линией + бейдж «Автор».
**`components/_footer.scss`** — тёмный footer, brand + 3 колонки + bottom.
Новые партиалы:
- `_categories-section.scss` — home-секция с 6 категориями-плитками
- `_feed-tabs.scss` — табы над лентой
- `_sticky.scss` — wide-sticky карточка на home (2-col: tint cover слева / body справа)
- `_reading-progress.scss` — progress-bar статьи
- `_popular-tags.scss` — pill-теги

Удаляем/упрощаем: `_chips.scss` (переезжает в `_article.scss` как `.pa-tag`), `_search-form.scss` (заменяется expanding-поиском в header).

### 2. Templates

**`header.php`** — sticky-blur шапка: логотип-марка (оранжевый квадрат с «P» + текст), nav, иконка поиска, CTA Telegram + search-overlay (хидден изначально, раскрывается по клику).
**`footer.php`** — новая разметка: brand-col (logo + tagline + соцсети) + 3 link-cols (Рубрики / Проект / Сервисы) + bottom (copyright + legal).
**`front-page.php`** → hero-home → categories-section → main-with-sidebar [feed (sticky + tabs + 2-col grid + pagination/infinite) + sidebar] → popular-tags.
**`single.php`** → reading-progress (fixed top) → hero-article (full-bleed, breadcrumbs, cat-chip, title, meta, scroll-hint) → main-with-sidebar [article-body + author-block + prev/next + tags + share-horizontal] [sidebar 6 блоков] → related → comments → mobile-share-bar.
**`sidebar.php`** — объединение 6 блоков через `get_template_part`.

Новые template-parts:
- `hero-home.php` — тёмный centered hero
- `hero-article.php` — full-bleed hero
- `categories-section.php` — 6 плиток категорий с иконками (6 встроенных SVG)
- `feed-tabs.php` — табы: Все + 4 популярных категории (через REST фильтр)
- `sidebar-categories.php` — блок «Категории» (6 пунктов + «все →»)
- `sidebar-trending.php` — блок «Читают сейчас» (5 пунктов с номерами)
- `sidebar-telegram.php` — Telegram CTA (синий gradient)
- `sidebar-newsletter.php` — форма подписки (тёмный фон)
- `sidebar-authors.php` — «Авторы недели» (топ-4 по post_count, get_avatar_url + gradient-fallback)
- `sidebar-tags.php` — обновить (pill-теги)
- `popular-tags.php` — секция внизу home
- `author-block.php` — блок автора под статьёй
- `prev-next.php` — навигация
- `related.php` — «Читайте дальше» (3 связанных по категории)
- `share-horizontal.php` — share под тегами статьи
- `mobile-share-bar.php` — sticky bottom share-bar
- `reading-progress.php` — div для progress-bar

Обновить существующие:
- `card-article.php` — cover с hue-градиентом + «Новое»-бейдж + время чтения (через `pickprism_reading_time`), без автора. Если `has_post_thumbnail` → img, иначе → `.ha-card__cover--fallback` с `style="--hue:N"` + первая буква основной категории.
- `card-article-sticky.php` — widescreen (2-col: tint cover с буквой категории + «Закреплено»-pin слева / body + author + CTA справа). Hue-fallback аналогично.

Блоки для контента (опционально, через `wp_kses`-friendly шорткоды; MVP — только CSS, автор сам добавляет в контент нужные классы):
- `[tldr]...[/tldr]`, `[callout type=info|warn|tip]...[/callout]`, `[pullquote author=""]...[/pullquote]`, `[inline-cta]...[/inline-cta]` — оставляем TODO в TECH_DEBT на будущее, в MVP стили есть и работают по классам `.pa-tldr`, `.pa-callout--info`, `.pa-quote`, `.pa-inlinecta`.

### 3. JS (assets/src/js)

- `search.js` — **переписать** под expanding overlay в шапке (300-400ms ease, ESC закрывает, фокус на input, скрытие nav/CTA). Оставить REST-вызов `/pickprism/v1/search` с debounce и рендер выпадающих результатов под инпутом.
- `reading-progress.js` — **новый**: скролл article → ширина `.pa-progress__bar`.
- `category-tabs.js` — **новый**: переключение табов на home, ре-фетч ленты через `/pickprism/v1/feed` с параметром категории.
- `main.js` — подключить новые модули + условная инициализация (reading-progress только на single, tabs только на home).
- `infinite-scroll.js` — адаптация селекторов (с `.feed__list` на `.ha-feed__grid`).
- `animations.js` — оставить как есть (работает по классу `.reveal`).
- `comments.js` — обновить селекторы под новую разметку комментариев.

### 4. Backend (минимум)

- `inc/enqueue.php` — добавить Manrope в Google Fonts URL (`family=Inter:...&family=Manrope:wght@500;600;700;800;900`).
- `inc/comments.php` — callback: обновить HTML под новую разметку (`.pa-comment__avatar`, `.pa-comment__body`, `.pa-comment__head`, `.pa-comment__author`, бейдж «Автор» через post_author check). Replies в `<ul class="pa-replies">`.
- **`inc/template-helpers.php` (новый):**
  - `pickprism_cover_hue( int $post_id ): int` — hue 0–359 от первичной категории (term_id → slug → fallback 24).
  - `pickprism_primary_category( int $post_id ): ?WP_Term` — Rank Math `rank_math_primary_category` или первая из `get_the_category()`.
  - `pickprism_reading_time( int $post_id ): int` — минуты, с кэшем в post_meta.
  - `pickprism_is_new( int $post_id ): bool` — свежее 7 дней.
  - `pickprism_render_cover( int $post_id ): void` — единая разметка cover (thumb or gradient + буква).
  - `pickprism_authors_of_the_week( int $limit = 4 ): array` — с transient-кэшем на 6ч.
- **`inc/caching.php` или hook в `template-helpers.php`:**
  - `save_post` → пересчёт `_pickprism_reading_time`, инвалидация `pickprism_authors_week`.
  - `delete_user` → инвалидация `pickprism_authors_week`.

Всё остальное (REST, rate-limit, sticky, основной кэш, fixtures, security) — **не трогаем**.

## Порядок работ (чек-пойнты)

1. **Tokens + база** (`abstracts/_tokens.scss`, `base/*.scss`) → сразу виден оранжевый + Manrope.
2. **Header + Footer + buttons** → общий wrapper работает на всех страницах.
3. **Home:** hero-home → categories-section → feed-tabs → sticky (new) → card-article (new) → sidebar (6 блоков) → popular-tags.
4. **Article:** reading-progress → hero-article → single.php → author/prev-next/related/share/mobile-bar → comments (PHP callback + SCSS).
5. **JS:** search (expanding), reading-progress, category-tabs, адаптация infinite-scroll/comments.
6. **Сборка + коммит dist:** `npm run build`, проверка размеров CSS/JS.
7. **QA:** Lighthouse ≥ 85 mobile, responsive breakpoints (480/640/960/1080/desktop), клавиатура, скринридер.

## Acceptance criteria

- [ ] Цветовая палитра — только `#ff7a1a` + нейтральные warm-greys; нигде нет `#5865f2`/`#3c4dff`.
- [ ] Шрифты Manrope (заголовки) + Inter (body) подгружаются через preconnect, правильно применяются.
- [ ] **Header:** sticky с blur, лого-марка «P», expanding search раскрывается на всю ширину до логотипа за 300ms, ESC закрывает, nav/CTA исчезают плавно.
- [ ] **Home hero:** тёмный centered, 88px title, звёзды twinkle, оранжевый glow, 2 кнопки, 3 стата с разделителями.
- [ ] **Home:** секция категорий (6 плиток с иконками и счётчиками) **перед лентой**, табы над лентой, wide-sticky закреплённая статья, grid 2-col cards, sidebar справа (6 блоков), секция popular-tags внизу.
- [ ] **Cover fallback:** у постов без `featured image` отображается детерминированный hue-градиент + первая буква категории; один и тот же пост всегда имеет тот же фон.
- [ ] **Время чтения:** отображается на карточках, кэшируется в `_pickprism_reading_time`, пересчёт на `save_post`.
- [ ] **Бейдж «Новое»:** виден только на постах свежее 7 дней.
- [ ] **Авторы недели:** 4 автора с наибольшим `post_count`, реальные аватары или gradient-fallback, кэш transient 6ч.
- [ ] **Article hero:** full-bleed min-height 620-680px, breadcrumbs, кат-чип, clamp-title с оранжевым em, meta (avatar + author + date + read), scroll-hint с bounce-анимацией.
- [ ] **Article content:** body 760px max, dropcap убран, TL;DR (желтоватый gradient с левой оранжевой полосой), callouts info/warn/tip (разные цвета), pull-quote (левая оранжевая полоса), inline-CTA (тёмный со звёздами и Telegram).
- [ ] **Article after:** author-block, prev/next, tags (pills), horizontal share, mobile sticky share-bar (≤960px).
- [ ] **Sidebar (единый, 6 блоков):** categories + trending + telegram (синий gradient) + newsletter (тёмный) + authors + tags — sticky top 24px, скрыт на ≤960. Работает и на home, и на single.
- [ ] **Comments:** новая разметка, replies с thread-линией, бейдж «Автор», комментарий автора на оранжевом фоне.
- [ ] **Reading progress:** fixed top 3px, обновляется по скроллу.
- [ ] **Footer:** тёмный, brand + 3 колонки + bottom.
- [ ] **Responsive:** проверены ≤480, ≤640, ≤720, ≤960, ≤1080, desktop.
- [ ] **Lighthouse Mobile Perf ≥ 85** на 1000 постах.
- [ ] CSS bundle ≤ 60 KB gzip (цель).
- [ ] Infinite-scroll / search / comments / reply — работают без регрессий.
- [ ] CLAUDE.md обновлён: запись от 2026-04-21 о редизайне.

## Шаги (делегирование) — финальный порядок

1. **frontend** — основная работа: SCSS, templates, JS, стабы в template-parts под hue-fallback/reading-time/бейдж/авторов. Один большой блок работы.
2. **backend** — точечные правки: `enqueue.php` (Manrope), `comments.php` (callback новой разметки), **новый `inc/template-helpers.php`** (cover_hue, primary_category, reading_time+meta-hook, is_new, render_cover, authors_of_the_week+transient), подключение файла в `functions.php`. Хуки на `save_post`/`delete_user`.
3. **reviewer** — ревью качества редизайна, следование дизайну, корректность BEM, escape/sanitize, отсутствие регрессий в REST/sticky/кэше.
4. **qa** — responsive + функциональные тесты (search, infinite-scroll, комменты, reply, hue-fallback на постах без картинки, пересчёт reading-time при редактировании, бейдж «Новое» на свежих фикстурах, авторы недели).
5. **tech-writer** — обновить CLAUDE.md (запись 2026-04-21) + README (секция «Дизайн-система / hue-fallback / reading-time meta»).
6. Коммит(ы) на английском: допустимо разбить на `feat(design): tokens + header + footer + home redesign` и `feat(design): article + comments + reading progress`, итоговый PR один.

## Риски

- Большой объём работы — разбить на 2 коммита (home + article) если в одном неудобно.
- REST-поиск с rate-limit: старая модалка могла работать иначе; при переписывании проверить, что debounce + nonce сохранены.
- Category-tabs через REST: текущий `/pickprism/v1/feed` принимает `type=category&value=slug` — используем это, не придумываем новый endpoint.
- Mobile share bar: `body { padding-bottom: 68px }` только на single ≤960.
- Шрифт Manrope добавляет ~20-40 KB — оставляем только нужные начертания (500, 600, 700, 800, 900).
- `get_users` с `orderby=post_count` на больших базах может быть медленным — поэтому обязательно transient-кэш.
- Старые 1000 фикстурных постов не имеют `_pickprism_reading_time` — ленивое заполнение при первой загрузке (по одному meta-write на пост) даст всплеск запросов. Мягкий фолбэк: на первой загрузке рассчитываем без записи, запись только при `save_post`. Альтернатива: WP-CLI команда `wp pickprism backfill-reading-time`. MVP — ленивый расчёт **без** записи в мету, запись только на `save_post`. Если медленно — backfill-команда.

## Оценка

~4-6 часов работы (frontend ~3h, backend ~30min на хелперы + comments + enqueue, reviewer ~30min, qa ~45min, writer ~15min).
