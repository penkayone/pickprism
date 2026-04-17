# pickprism — WordPress theme

Блог-тема для сайта pickprism (1000+ статей). Дизайн в духе g2.com: чистый, корпоративный, нежные анимации.

## Стек

- **Backend:** WordPress classic theme (не FSE), PHP 8+
- **Frontend:** Vite + SCSS + Vanilla JS (без React/Vue/Tailwind)
- **Анимации:** CSS transitions + IntersectionObserver (без GSAP)
- **Шрифт:** Inter через Google Fonts + preconnect (TODO: перевести на self-host)
- **Плагины-соседи:** LiteSpeed Cache, Rank Math SEO, Wordfence

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
├── *.php                           Шаблоны: front-page, home, single, archive, category, tag, search, 404, header, footer, sidebar, index
├── template-parts/                 Переиспользуемые блоки: hero, card-article(+sticky), search-form, taxonomy-chips, sidebar-popular/tags, pagination
├── inc/                            Модули логики (подключаются из functions.php)
│   ├── setup.php                   theme supports, menus, image sizes
│   ├── enqueue.php                 Стили/скрипты, версионирование через filemtime
│   ├── ajax-search.php             REST /pickprism/v1/search и /feed + rate-limit
│   ├── sticky.php                  Metabox sticky_order + сортировка
│   ├── query-optimizations.php     pre_get_posts, no_found_rows
│   ├── caching.php                 Обёртки над transients
│   ├── cleanup.php                 Отключение emoji, embeds, XML-RPC
│   ├── security.php                Headers, nonce, escape/sanitize хелперы
│   └── fixtures.php                WP-CLI команды для тестовых данных
├── assets/
│   ├── src/scss/                   Исходники (abstracts/base/components)
│   │   └── abstracts/_tokens.scss  ДИЗАЙН-ТОКЕНЫ (цвета, spacing, тени, радиусы) — править дизайн отсюда
│   ├── src/js/                     main, search, infinite-scroll, animations
│   └── dist/                       Скомпилированное (коммитится в git)
├── vite.config.js, package.json
└── README.md
```

## Ключевые решения

| Решение | Почему |
|---|---|
| Classic theme, не FSE | Проще и предсказуемее для блога с кастомным дизайном |
| SCSS вместо Tailwind | Кастомный дизайн под g2 — Tailwind утяжелит HTML |
| Vanilla JS | Тема блога, SPA не нужен, SEO/perf критичны |
| Sticky posts + metabox `sticky_order` (без ACF) | Нативный WP + одно поле порядка, без зависимости от ACF |
| REST endpoint `/pickprism/v1/search` | Обёртка над WP_Query с ограничениями, кэшем и rate-limit; исключён из LiteSpeed |
| Infinite scroll через REST | 12-20 постов за раз, кнопка fallback |
| Шрифт Inter через CDN | Временно — TODO self-host (экономия ~10-20мс LCP) |
| Транзиенты для sidebar/footer | Инвалидация на save_post/edited_term |

## Дизайн

- Палитра: нежный сине-фиолетовый акцент, белый фон, мягкие тени (4 уровня), радиусы 8-12px
- Spacing scale: 4/8px
- Все токены: `assets/src/scss/abstracts/_tokens.scss` ← править отсюда для дизайн-итераций
- Ожидаются 1-3 итерации по дизайну (макета нет, делаем по референсу g2.com)

## Правила работы

- **Одна задача = одна ветка = один PR** (текущая ветка: `feature/initial-theme`)
- Коммиты на английском: `type(scope): description` (feat / fix / refactor / docs / chore)
- Все строки в PHP оборачивать в `__()` / `_e()` с текст-доменом `pickprism` (мультиязычность пока не нужна, но задел есть)
- Escape на выводе ВСЕГДА: `esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`
- Sanitize на входе
- REST-эндпоинты: nonce, валидация, rate-limit
- `assets/dist/` коммитится в git (чтобы тема работала без `npm install` на сервере)
- После правок SCSS/JS обязательно `npm run build` перед коммитом

## Производительность (цели)

- LCP < 1.5s, CLS < 0.1, TBT < 200ms
- Lighthouse Performance ≥ 85 на mobile с 1000 постами
- Бандл: CSS ~21KB (gzip 4.9), JS ~6KB (gzip 2.4)

## Что не сделано (TODO)

- [ ] Мобильное бургер-меню (слот в header.php есть, drawer/кнопка не реализованы — 1-2ч)
- [ ] Self-host шрифта Inter (сейчас через Google Fonts CDN)
- [ ] Настройка WebP в LiteSpeed (делается в плагине, не в теме — devops-шаг)
- [ ] Итерации по дизайну после ревью заказчика

## История

- **2026-04-17** — Инициализация темы. Backend + frontend + REST + фикстуры + QA на 1000 постах. PR создан с ветки `feature/initial-theme`.
