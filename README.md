# Pickprism — WordPress theme

Тема для сайта статей в духе g2.com: чистая типографика, карточки, AJAX-поиск, бесконечная лента, закреплённые посты с ручным порядком.

## Стек

- **PHP 7.4+** (оттестировано на 8.2)
- **WordPress 6.0+**
- **SCSS** + **Vite** для сборки
- **Vanilla JS**: AJAX-поиск с `AbortController`, infinite scroll через `IntersectionObserver`, reveal-анимации
- **REST API** темы: `/wp-json/pickprism/v1/search`, `/wp-json/pickprism/v1/feed`

## Установка

```bash
# Тема уже лежит в wp-content/themes/pickprism
# Активируйте её:
wp theme activate pickprism
```

## Разработка

```bash
# Установка зависимостей (разово)
npm install

# Watch-режим: пересобирает dist при изменении файлов
npm run dev

# Продакшн-сборка
npm run build

# Очистка dist
npm run clean
```

Собранные файлы лежат в `assets/dist/` и **коммитятся в git** — это минимизирует зависимости на проде.

## Структура

```
pickprism/
├── style.css                      # метаданные темы
├── functions.php                  # точка входа, подгружает /inc
├── index.php                      # fallback-шаблон
├── front-page.php                 # главная: hero + sticky + лента
├── home.php                       # страница блога (без hero)
├── single.php                     # статья
├── archive.php / category.php / tag.php
├── search.php / 404.php
├── header.php / footer.php / sidebar.php
│
├── template-parts/
│   ├── hero.php                   # hero-секция главной
│   ├── search-form.php            # форма поиска (size: md|lg)
│   ├── taxonomy-chips.php         # чипсы категорий/тегов
│   ├── card-article.php           # карточка в ленте
│   ├── card-article-sticky.php    # крупная карточка sticky
│   ├── pagination.php             # пагинация + sentinel для infinite scroll
│   ├── sidebar-popular.php        # популярные посты
│   └── sidebar-tags.php           # облако тегов
│
├── inc/
│   ├── setup.php                  # supports, меню, image sizes
│   ├── enqueue.php                # CSS/JS + wp_localize через inline-script
│   ├── cleanup.php                # чистка head от мусора
│   ├── security.php               # headers, XML-RPC off, author-enum block
│   ├── query-optimizations.php    # pre_get_posts + прогрев кэшей
│   ├── caching.php                # транзиенты для sidebar/footer, LiteSpeed bypass
│   ├── sticky.php                 # metabox sticky_order + сортировка
│   ├── ajax-search.php            # REST /pickprism/v1/search и /feed + rate limit
│   └── fixtures.php               # wp-cli команды генерации тестовых данных
│
├── assets/
│   ├── src/                       # исходники (SCSS + JS)
│   │   ├── scss/
│   │   │   ├── abstracts/         # _tokens.scss (CSS-переменные), _mixins.scss
│   │   │   ├── base/              # reset, typography, layout
│   │   │   └── components/        # header, hero, card, sidebar и т.д.
│   │   └── js/                    # main, search, infinite-scroll, animations
│   └── dist/                      # собранные CSS/JS (в git)
│
├── package.json / vite.config.js
└── README.md
```

## Тестовые данные (фикстуры)

Для демонстрации темы на реальном объёме есть WP-CLI команда — генерирует до 1000 постов, 18 категорий, 50 тегов, 7 закреплённых постов, 30 уникальных картинок (переиспользуются по кругу).

```bash
# Полный прогон: 1000 постов с картинками (~35 секунд)
wp pickprism fixtures

# Кастомизация
wp pickprism fixtures --posts=500 --categories=10 --tags=30 --sticky=5

# Без загрузки картинок (быстрее)
wp pickprism fixtures --posts=200 --skip-images

# Перед новой генерацией снести старые фикстуры
wp pickprism fixtures --purge --posts=1000

# Только очистка
wp pickprism purge
```

`purge` удаляет все посты типа `post` и только те вложения, которые были созданы фикстурами (помечены `_pickprism_fixture=1`).

Картинки берутся с `https://picsum.photos` и загружаются в медиабиблиотеку — работает `srcset`, `lazy loading` и оптимизации LiteSpeed.

## REST API

### `GET /wp-json/pickprism/v1/search`

Мгновенный поиск для dropdown.

**Параметры:**
- `q` (string, **required**) — запрос, 2..100 символов
- `limit` (int, default 8) — 1..20

**Rate limit:** 60 запросов в минуту на IP.

### `GET /wp-json/pickprism/v1/feed`

Подгрузка следующей страницы ленты (infinite scroll).

**Параметры:**
- `type` (string, default `home`) — один из `home`, `category`, `tag`, `search`
- `value` (string) — slug категории/тега или поисковый запрос
- `paged` (int, default 2) — 1..1000
- `per_page` (int) — 1..24

**Rate limit:** 120 запросов в минуту на IP.

## Закреплённые посты

Sticky-посты выводятся на главной отдельным блоком **крупными карточками** в порядке поля `sticky_order` (ASC), при равенстве — по дате DESC.

Поле задаётся в метабоксе "Pickprism: приоритет закрепа" в сайдбаре редактора поста. Сам флаг sticky ставится стандартным чекбоксом WordPress.

## Кэширование

Транзиенты (`6 часов`) для:
- `pickprism_top_categories_*` — футер
- `pickprism_popular_posts_*` — sidebar
- `pickprism_popular_tags_*` — hero и sidebar

Инвалидация автоматически при `save_post`, `deleted_post`, `edited_term` и т.д.

REST-эндпоинты темы помечены `DONOTCACHEPAGE` и `litespeed_control_set_nocache` — не попадают в page cache.

## Производительность

На референсной машине с 1000 постами:
- TTFB главной ~33 мс
- TTFB single ~26 мс
- CSS: 21 КБ / gzip 4.9 КБ
- JS: 6 КБ / gzip 2.4 КБ

Оптимизации:
- `update_post_term_cache` и `update_post_meta_cache` прогреваются в `pre_get_posts` для основного запроса
- `WP_POST_REVISIONS = 5`
- Emoji-скрипты ядра удалены
- Карточки используют `pickprism-card` (800x500) + srcset
- `loading="lazy"` на всех картинках кроме hero single

## Безопасность

- Все строки на выводе escape'нуты (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`)
- На входе REST — `sanitize_callback` + `validate_callback`
- Nonce `X-WP-Nonce` используется клиентом, rate-limit на endpoints
- XML-RPC выключен
- `/wp-json/wp/v2/users` закрыт для анонимов
- `?author=1` enumeration блокируется редиректом
- Security headers: `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy`

## Мультиязычность

Текущая версия **одноязычная** (русский), но все строки обёрнуты в `__()` / `_e()` с текст-доменом `pickprism` — готово к подключению WPML/Polylang и генерации `.po`/`.mo` через WP-CLI:

```bash
wp i18n make-pot . languages/pickprism.pot --domain=pickprism
```

## Локальный шрифт

Сейчас Inter подключается через Google Fonts (с `preconnect`). Чтобы перейти на self-hosted:

1. Положите `inter-variable.woff2` в `assets/fonts/`
2. В `assets/src/scss/base/_typography.scss` раскомментируйте блок `@font-face`
3. В `inc/enqueue.php` уберите `wp_enqueue_style('pickprism-fonts', ...)`
4. Пересоберите: `npm run build`

## Известные ограничения

- Мобильное меню: в `header.php` зарезервирован слот, но сам гамбургер и dropdown — следующая итерация
- Комментарии используют дефолтный `comments_template()` — стили подходят, но кастомного `comments.php` нет

## Лицензия

GPL-2.0-or-later
