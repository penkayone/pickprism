# PLAN — Страница «Все категории» (`/categories/`)

**Ветка:** `feature/redesign`
**Дата:** 2026-04-29

## Контекст
В sidebar-блоке «Категории» (`template-parts/sidebar-categories.php`) ссылка «все →» ведёт на `home_url('/')` — то есть просто на главную, где у пользователя те же 6 плиток. Это баг UX: ссылка есть, страницы нет.

## Решение
Rewrite rule + `template_include` (вариант **(б)**) — самодостаточно, не требует ручного создания WP-страницы, не зависит от выбора `page_for_posts`.

## Скоуп

### Backend
1. Новый модуль `inc/categories-page.php`:
   - Регистрирует query-var `pickprism_categories`.
   - Top rewrite: `^categories/?$` → `index.php?pickprism_categories=1`.
   - `template_include` — если query-var === `'1'`, отдаёт `templates/all-categories.php`.
   - `after_switch_theme` → `flush_rewrite_rules()`.
   - Самовосстановление: при первой загрузке если правило не найдено — `flush_rewrite_rules(false)` один раз через option-флаг `pickprism_categories_rules_v1`.
   - `pre_get_posts` — для нашего запроса не выполнять основной WP_Query (не нужно, рендерим вручную).
   - Фильтр `document_title_parts` — корректный `<title>` «Все категории — Site Name».
2. Подключить модуль в `functions.php` (`pickprism_require_modules`).
3. В `template-parts/sidebar-categories.php` заменить `home_url('/')` на `home_url('/categories/')`.

### Frontend
4. Новый шаблон `templates/all-categories.php`:
   - `get_header()` / `get_footer()`.
   - `<main class="ha-allcats">` с заголовком «Все категории» + лидом.
   - Получает `get_terms('category', hide_empty=true, orderby=count, order=DESC)`.
   - Каждая плитка: hue-градиент (`--hue` от `pickprism_term_hue`), большая буква (первая буква названия), название, счётчик постов; ссылка `get_term_link`.
   - Если категорий 0 — empty-state.
5. SCSS `assets/src/scss/components/_all-categories.scss`:
   - Сетка `repeat(auto-fill, minmax(220px, 1fr))`, gap по spacing-scale.
   - Плитка: фиксированная высота / aspect-ratio, hue-градиент `linear-gradient(135deg, hsl(var(--hue) 78% 58%), hsl(...) 44%)`, буква 96–120px, название + count внизу, hover (lift + accent border).
   - Подключить `@use` в `assets/src/scss/main.scss`.

### Сборка и документация
6. `npm run build`.
7. `CLAUDE.md`:
   - Добавить `inc/categories-page.php` в список модулей.
   - Добавить `templates/all-categories.php` в структуру.
   - В историю — `2026-04-29`: убраны дубли (feed-tabs / categories-section с главной / sidebar-telegram), добавлена страница `/categories/`.
   - Обновить цифры бандла.

## Делегирование
1. `backend` — `inc/categories-page.php`, подключение, ссылка в sidebar.
2. `frontend` — `templates/all-categories.php`, SCSS, сборка.
3. `reviewer` — общий code review.
4. `qa` — открыть `/categories/`, клик из sidebar, проверить заголовок страницы, hue-градиенты, корректность счётчиков, отсутствие регрессий главной и архивов категорий.

Не нужны: `security` (нет auth/payments/user data), `devops` (нет инфры), `tech-writer` (CLAUDE.md делает лид).

## Риски
- Кэш rewrite rules после пула на других окружениях — митигируем option-флагом `pickprism_categories_rules_v1`.
- Конфликт с ручной WP-страницей со slug `categories` — наш top rewrite перебьёт; зафиксируем комментарием в коде.
- LiteSpeed закэширует `/categories/`. Инвалидация на CRUD термов — TECH_DEBT, не блокер.

## Откат
`git revert <commit>` + `wp rewrite flush` (или зайти в Settings → Permalinks).

## Оценка
2-3 часа.
