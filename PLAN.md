# PLAN — Удаление feed-tabs с главной

**Ветка:** `feature/redesign`
**Дата:** 2026-04-29

## Задача

Убрать категорийные табы над лентой на главной. Сама лента (sticky + ha-card grid + infinite-scroll) остаётся, фильтр по категориям доступен через `/category/{slug}/`.

## Скоуп

### Удалить

- Вызов `template-parts/feed-tabs` в `front-page.php`
- `template-parts/feed-tabs.php`
- `assets/src/scss/components/_feed-tabs.scss`
- `assets/src/js/category-tabs.js`
- Импорт + init `initCategoryTabs` в `assets/src/js/main.js`
- `@use 'components/feed-tabs'` в `assets/src/scss/main.scss`
- Listener `pickprism:feed-reset` в `assets/src/js/infinite-scroll.js` (мёртвый — событие диспатчилось только из category-tabs.js)
- Раздел «Category-tabs: fallback при отсутствии JS» в `TECH_DEBT.md`
- Упоминания feed-tabs / category-tabs в `CLAUDE.md` (структура, история редизайна)

### Не трогаем

- `data-feed-container` / `data-feed-list` / `data-feed-sentinel` — нужны для infinite-scroll
- REST `/pickprism/v1/feed` — продолжает обслуживать infinite-scroll и архивы
- `category.php` / `tag.php` / `archive.php` — независимые шаблоны

## Шаги

1. **frontend** — удалить файлы и ссылки, прогнать `npm run build`.
2. **reviewer** — проверить, что не осталось висячих классов/событий/импортов.
3. **qa** — главная без табов, infinite-scroll работает, category/tag archive целы.
4. Обновить `CLAUDE.md`.

## Риски

- Низкие. Удаляется изолированный UI-блок без серверной зависимости.
- Старые ссылки вида `/?feed_cat=X` — не использовались (табы переключали через JS, без query-param).

## Откат

`git revert <commit>` — никаких миграций БД, никаких изменений API.
