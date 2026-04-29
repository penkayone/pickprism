# pickprism — WordPress theme

Блог-тема для сайта pickprism (1000+ статей). Редизайн 2026-04-21 в стиле pressaff: тёплый оранжевый акцент + Manrope/Inter, расширенный sidebar на 5 блоков, article-страница с full-bleed hero, TL;DR/callouts/pullquote, mobile share bar, progress-bar чтения.

## Стек

- **Backend:** WordPress classic theme (не FSE), PHP 8+
- **Frontend:** Vite + SCSS + Vanilla JS (без React/Vue/Tailwind)
- **Анимации:** CSS transitions + IntersectionObserver (без GSAP)
- **Шрифты:** Inter (body) + Manrope (display) через Google Fonts + preconnect (TODO: self-host)
- **Плагины-соседи:** LiteSpeed Cache, Rank Math SEO (знает о `rank_math_primary_category`), Wordfence

## Команды

```bash
npm run dev                        # Vite watch
npm run build                      # Продакшен-сборка → assets/dist/
wp pickprism fixtures              # Сгенерировать 1000 постов, 15-20 категорий, 50 тегов, 5-10 sticky
wp pickprism fixtures --purge      # Очистить и сгенерировать заново
wp pickprism purge                 # Удалить только фикстуры
```

## Структура

```
pickprism/
├── *.php                                   Шаблоны: front-page, home, single, archive, category, tag, search, 404, header, footer, sidebar, index
├── templates/                              Standalone-шаблоны (подключаются через template_include):
│   └── all-categories.php                  Страница /categories/ — список всех непустых категорий
├── template-parts/                         Переиспользуемые блоки:
│   ├── hero-home.php / hero-article.php    (тёмный centered home / full-bleed single)
│   ├── card-article.php / card-article-sticky.php  (обычная / wide-sticky карточки)
│   ├── sidebar-categories / -trending / -newsletter / -authors / -tags.php
│   ├── popular-tags.php                    (pill-секция внизу home)
│   ├── author-block / prev-next / related / share-horizontal / mobile-share-bar / reading-progress.php
│   ├── pagination.php
│   └── (legacy) hero / search-form / taxonomy-chips / sidebar-popular.php
├── inc/                                    Модули логики (подключаются из functions.php)
│   ├── setup.php                           theme supports, menus, image sizes
│   ├── enqueue.php                         Стили/скрипты, Manrope + Inter, версионирование через filemtime
│   ├── ajax-search.php                     REST /pickprism/v1/search и /feed (payload: hue, readTime, isNew, primaryCategory)
│   ├── sticky.php                          Metabox sticky_order + сортировка
│   ├── query-optimizations.php             pre_get_posts, no_found_rows
│   ├── caching.php                         Обёртки над transients (categories, tags, popular)
│   ├── template-helpers.php                ← НОВОЕ (2026-04-21): primary_category, hue-helpers, reading_time (post_meta), is_new, render_cover, authors_of_the_week
│   ├── categories-page.php                 ← НОВОЕ (2026-04-29): rewrite /categories/ → templates/all-categories.php (transient-flush на первой загрузке)
│   ├── cleanup.php                         Отключение emoji, embeds, XML-RPC
│   ├── security.php                        Headers, nonce, escape/sanitize хелперы
│   ├── comments.php                        callback на .pa-comment/.pa-reply, бейдж «Автор», REST-endpoint подачи
│   └── fixtures.php                        WP-CLI команды для тестовых данных
├── assets/
│   ├── src/scss/
│   │   ├── abstracts/_tokens.scss          ДИЗАЙН-ТОКЕНЫ (orange accent, Manrope + Inter) — править дизайн отсюда
│   │   └── components/                     header, hero, card, sidebar, article, comments, footer, popular-tags, reading-progress, all-categories, buttons, pagination, animations, 404, search-form, chips
│   ├── src/js/                             main, search, infinite-scroll, animations, comments, reading-progress
│   └── dist/                               Скомпилированное (коммитится в git)
├── vite.config.js, package.json
└── README.md
```

## Ключевые решения

| Решение | Почему |
|---|---|
| Classic theme, не FSE | Проще и предсказуемее для блога с кастомным дизайном |
| SCSS вместо Tailwind | Кастомный дизайн под pressaff — Tailwind утяжелит HTML |
| Vanilla JS | Тема блога, SPA не нужен, SEO/perf критичны |
| Sticky posts + metabox `sticky_order` (без ACF) | Нативный WP + одно поле порядка, без зависимости от ACF |
| REST endpoint `/pickprism/v1/search` и `/feed` | Обёртки над WP_Query с ограничениями, кэшем и rate-limit; исключены из LiteSpeed |
| Infinite scroll через REST | 12-20 постов за раз, кнопка fallback |
| Шрифты Inter + Manrope через CDN | Временно — TODO self-host (экономия ~10-20мс LCP) |
| Транзиенты для sidebar/footer/authors | Инвалидация на save_post / edited_term / deleted_user / profile_update |
| **Cover fallback** (редизайн 2026-04-21) | Если нет featured image — CSS-градиент с детерминированным hue от term_id первичной категории + первая буква категории. Один пост = один стабильный фон. |
| **Reading time в post_meta** (редизайн) | Пересчёт на `save_post` (200 wpm, минимум 1 мин). Кэш в `_pickprism_reading_time`. Для старых постов (1000 фикстур) — ленивый расчёт на лету, без записи. WP-CLI `backfill-reading-time` вынесен в TECH_DEBT. |
| **Бейдж «Автор»** в комментариях (редизайн) | Коммент помечен как авторский если email совпадает с email автора поста (user_id всегда 0 в анонимном режиме). |
| **Авторы недели** (редизайн) | `get_users(orderby=post_count, number=4)` с transient-кэшем 6ч. Инвалидация на save_post / deleted_user / profile_update. |
| **Primary category** через Rank Math | meta-ключ `rank_math_primary_category` → fallback `get_the_category()[0]`. |
| **Страница /categories/** (2026-04-29) | Не page-объект — отдельный rewrite-rule + query-var + template_include на `templates/all-categories.php`. Flush rewrite один раз через transient `pickprism_categories_rewrite_flushed` + повторно на `after_switch_theme`. Не зависит от опций WP, переживает реимпорт БД. |

## Дизайн

- **Палитра:** `#ff7a1a` orange + warm off-whites (`#fbf9f6` для article, `#f6f1e7` для home) + dark `#10131a / #14171f` (header-hero, newsletter, footer)
- **Шрифты:** Manrope 500–900 (h1–h4, кнопки, avatar-инициалы), Inter 400–700 (body, UI)
- **Body:** 17px / 1.72 / max-width 760px на статье
- **Container:** 1280px (article), 1320px (home `.ha-container`)
- **Радиусы:** 10 / 12 / 14 / 16 / 20 / 24 (от кнопок до крупных карточек)
- **Spacing scale:** 4/8 px
- **Все токены:** `assets/src/scss/abstracts/_tokens.scss` ← править отсюда для дизайн-итераций

### Hue-fallback для обложек

`pickprism_cover_hue( int $post_id ): int` → 0–359, детерминированно от `term_id` первичной категории (fallback — slug). Передаётся инлайн-стилем `style="--hue: <N>"` на `.ha-cover` / `.ha-sticky` / `.pa-rcard`. CSS-градиент `linear-gradient(135deg, hsl(var(--hue) 78% 58%), hsl(var(--hue)+30 82% 44%))`. Поверх — первая буква категории 180px (или 320px в sticky-cover).

### Единый рендерер обложки

`pickprism_render_cover( $post_id, $size )` — размер `md` | `sm` | `lg` | `row`. Если `has_post_thumbnail` → `<img>`, иначе gradient + буква.

## Правила работы

- **Одна задача = одна ветка = один PR** (текущая ветка: `feature/redesign`)
- Коммиты на английском: `type(scope): description` (feat / fix / refactor / docs / chore)
- Все строки в PHP оборачивать в `__()` / `_e()` с текст-доменом `pickprism`
- Escape на выводе ВСЕГДА: `esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`
- Sanitize на входе
- REST-эндпоинты: nonce, валидация, rate-limit
- `assets/dist/` коммитится в git (чтобы тема работала без `npm install` на сервере)
- После правок SCSS/JS обязательно `npm run build` перед коммитом

## Производительность (цели)

- LCP < 1.5s, CLS < 0.1, TBT < 200ms
- Lighthouse Performance ≥ 85 на mobile с 1000 постами
- Бандл (после чисток 2026-04-29 + удаления hero-stats): **CSS ~82.1 KB (gzip ~14.45 KB), JS ~12.8 KB (gzip ~4.7 KB)**

## Что не сделано (TODO)

- [ ] Мобильное бургер-меню (слот в header.php есть, drawer/кнопка не реализованы — 1-2ч)
- [ ] Self-host шрифтов Inter + Manrope (сейчас через Google Fonts CDN)
- [ ] Настройка WebP в LiteSpeed (делается в плагине, не в теме — devops-шаг)
- [ ] Шорткоды для TL;DR / callouts / pullquote / inline-CTA (стили готовы, шорткодов нет — см. TECH_DEBT)
- [ ] Интеграция newsletter-формы (сейчас `onsubmit="return false"`)
- [ ] WP-CLI команда `backfill-reading-time` для бэкфилла меты на старые посты

## История

- **2026-04-17** — Инициализация темы. Backend + frontend + REST + фикстуры + QA на 1000 постах. PR создан с ветки `feature/initial-theme`.
- **2026-04-20** — Форма комментариев: ревью (reviewer / security / qa) прошло без blocker-ов. Уборка minor-находок (ветка `feature/comment-form`): удалён дублирующий `option_comment_registration`, добавлена валидация глубины reply против `thread_comments_depth`, убран мёртвый legacy-ключ `user_ID`, подкладывание `$_POST` + `wp_handle_comment_submission` обёрнуты в try/finally.
- **2026-04-21** — Полный редизайн в стиле pressaff (ветка `feature/redesign`). Замена токенов на orange-warm палитру + Manrope/Inter. Переписаны header (sticky + expanding search), footer (тёмный 2-col), home (hero-home → sticky + ha-card grid → единый sidebar на 6 блоков → popular-tags), article (hero-full-bleed → article-body с TL;DR/callouts/quote/inline-CTA → author-block → prev-next → related → horizontal-share → mobile-share-bar + reading-progress), comments (pa-comment / pa-reply + thread-линия + бейдж «Автор»). Новый backend-модуль `inc/template-helpers.php` с hue-helpers, reading-time (с post_meta-кэшем), is-new (7 дней), render_cover, authors_of_the_week (transient 6ч). REST `/feed` расширен полями hue/readTime/isNew/primaryCategory. JS: новый `reading-progress.js`, `search.js` переписан под expanding overlay. PICKPRISM_VERSION → 1.1.0.
- **2026-04-29** — Чистка дублей на главной и в sidebar (ветка `feature/redesign`):
  - Удалены **feed-tabs** (категорийные табы над лентой): `template-parts/feed-tabs.php`, `assets/src/scss/components/_feed-tabs.scss`, `assets/src/js/category-tabs.js`, импорты в `main.js` / `main.scss`, мёртвый листенер `pickprism:feed-reset` в `infinite-scroll.js`. Фильтр по категориям остаётся на `/category/{slug}/`.
  - Удалена **секция категорий** с главной (дублировала `sidebar-categories`): `template-parts/categories-section.php`, `assets/src/scss/components/_categories-section.scss`, `@use` в `main.scss`, вызов `get_template_part()` в `front-page.php`. Хелперы `pickprism_get_top_categories` и `pickprism_term_hue` сохранены — используются в header / footer / sidebar-categories / hero (legacy).
  - Удалён **sidebar-telegram** (дублировал TG-ссылки в header/footer): `template-parts/sidebar-telegram.php`, секция `.ha-side__tg*` в `_sidebar.scss`, `.ha-side__tg` из группового селектора `bp-down-xl`. Фильтры `pickprism_telegram_url/subs/posts_per_week` удалены вместе с шаблоном (нигде не были зарегистрированы через `add_filter`). Sidebar теперь на 5 блоков: categories / trending / newsletter / authors / tags.
  - Структура главной: hero-home → feed (sticky + сетка) + sidebar (5 блоков) → popular-tags.
- **2026-04-29** — Страница `/categories/` со всеми категориями (ветка `feature/redesign`):
  - Новый модуль `inc/categories-page.php` — регистрирует rewrite-rule `^categories/?$`, query-var `pickprism_categories`, фильтр `template_include` на `templates/all-categories.php`. Flush rewrite-rules один раз на первой загрузке через transient `pickprism_categories_rewrite_flushed` (TTL 1 год) + принудительно на `after_switch_theme`. В template_include выставляется `is_404 = false` и `status_header(200)`, чтобы пустой главный query не превращал страницу в 404.
  - Новый шаблон `templates/all-categories.php` — H1 «Все категории», описание, сетка плиток `repeat(auto-fill, minmax(280px, 1fr))`. Каждая плитка: hue-иконка с первой буквой категории + название + плюрализованный счётчик постов (`_n`) + опциональное описание (`wp_trim_words` 14 слов) + стрелка. Источник — `get_terms(taxonomy=category, hide_empty=true, orderby=count desc)`. Empty state на случай пустого списка.
  - Новый SCSS `assets/src/scss/components/_all-categories.scss` (`.pa-allcats-*`) — подключён в `main.scss` в секции «Standalone-страницы». Использует только токены, BEM-нейминг, focus-ring, mobile-first одна колонка ≤640px.
  - Ссылка «все →» в `template-parts/sidebar-categories.php` обновлена с `home_url('/')` на `home_url('/categories/')`.
  - Бандл: **CSS ~82.1 KB (gzip ~14.45 KB), JS ~12.8 KB (gzip ~4.7 KB)** (после удаления hero-stats — см. запись ниже).

- **2026-04-29** — Hero-stats удалён с главной (ветка `feature/redesign`):
  - Из `template-parts/hero-home.php` удалён блок `.ha-hero__stats` (3 цифры: статьи / авторы / частота обновлений) вместе с инлайн-подсчётом `wp_count_posts` + `get_users(has_published_posts)`. Линия-разделитель над цифрами (`border-top` на `.ha-hero__stats`) и вертикальные `.ha-hero__stats-sep` ушли вместе со стилями.
  - В `assets/src/scss/components/_hero.scss` удалены селекторы `&__stats` и `&__stats-sep`. У `&__ctas` убран лишний `margin-bottom: 48px` — отступ снизу теперь обеспечивает `padding` секции.
  - Hero теперь: kicker → title → lead → CTAs.
