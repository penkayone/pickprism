# Tech debt — pickprism

Список вещей, которые стоит починить, но они не блокируют текущие задачи.
Пополняется в процессе работы. Когда берётся в работу — заводится отдельная задача/ветка.

---

## Дизайн-токены для status-цветов (warning / success / error)

**Где:** `assets/src/scss/components/_comments.scss`
- `.comment-pending` (в разметке комментов, в т.ч. pa-comment__pending): warning-фон.
- `.comment-form__notice--success`: `background: #e5f6ec; color: #1f6a42; border: 1px solid #b8e0c9;`.
- `.comment-form__notice--error`: `background: #fdecec; color: #9a2a2e; border: 1px solid #f5c3c5;`.
- callouts (info/warn/tip) в `_article.scss`: каждый с захардкоженным bg/border/icon color.

**Проблема.** Цвета захардкожены в хексах и разбросаны по компонентам. В `abstracts/_tokens.scss` для status-ролей нет ни bg, ни border, ни text-оттенков.

**Что сделать.** Добавить в `_tokens.scss` триплеты для ролей (warning / success / error / info) и перевести хардкод на переменные.

**Оценка:** 15–20 минут.
**Приоритет:** low.

---

## MVP-шорткоды для редакторских блоков (TL;DR / callouts / pull-quote / inline-CTA)

**Где:** `assets/src/scss/components/_article.scss`

**Что есть сейчас.** Стили для `.pa-tldr`, `.pa-callout--info|warn|tip`, `.pa-quote`, `.pa-inlinecta` полностью готовы и работают по классам — редактор может вставлять их в HTML-режиме блока.

**Проблема.** Редактору неудобно писать сырой HTML. Нужны шорткоды.

**Что сделать.** Реализовать шорткоды `[tldr]...[/tldr]`, `[callout type=info|warn|tip title=""]...[/callout]`, `[pullquote author=""]...[/pullquote]`, `[inline-cta title="" text="" btn="" href=""]`. Обязательно: `wp_kses`-очистка, безопасные атрибуты, поддержка nested inline-тегов.

**Оценка:** 2–3 часа.
**Приоритет:** medium. Ускорит работу редакторов.

---

## Self-host шрифтов Inter + Manrope

**Где:** `inc/enqueue.php`

**Что есть сейчас.** Оба семейства тянутся через Google Fonts CDN + preconnect к fonts.gstatic.com.

**Проблема.** На LCP даёт +10-20ms, плюс зависимость от внешнего CDN.

**Что сделать.** Скачать variable-woff2 для Inter и Manrope, положить в `assets/fonts/`, убрать Google Fonts-link, заменить на локальные `@font-face` с `font-display: swap`.

**Оценка:** 30-45 минут.
**Приоритет:** medium. Хорошо влияет на LCP.

---

## WP-CLI команда `wp pickprism backfill-reading-time`

**Где:** `inc/fixtures.php` (или отдельный `inc/cli.php`)

**Что есть сейчас.** `pickprism_reading_time` считает минуты на лету и **НЕ записывает** в мету (запись только на `save_post`). У 1000 фикстурных постов меты нет — расчёт на каждой загрузке — small overhead.

**Проблема.** Если много постов без меты и пытаются часто попадать в ленту, расчёт выполняется на каждый GET. В теории приемлемо (str_word_count быстр), но не оптимально.

**Что сделать.** WP-CLI команду, которая проходит по всем публичным постам и пишет `_pickprism_reading_time`. Запускается один раз после миграции.

**Оценка:** 20 минут.
**Приоритет:** low (оптимизация; без критична до тех пор, пока среднее время запроса < 100ms).

---

## Sidebar-newsletter — реальная интеграция

**Где:** `template-parts/sidebar-newsletter.php`

**Что есть сейчас.** Форма с `action="#"` + `onsubmit="return false"` — не отправляет никуда.

**Что сделать.** Интегрировать с newsletter-сервисом (Mailchimp API / Sendgrid) либо подключить плагин подписки.

**Оценка:** 1-2 часа.
**Приоритет:** medium. CTA есть, но без бэкенда декоративный.

---

## Mobile-меню (бургер)

**Где:** `header.php` / `.pa-nav`

**Что есть сейчас.** На ≤960 `.pa-nav` скрывается через `display: none`. Бургер-меню не реализован — пользователь мобилки не видит навигацию.

**Что сделать.** Добавить кнопку-бургер рядом с CTA, drawer-панель с ссылками, открытие/закрытие с backdrop.

**Оценка:** 1-1.5 часа.
**Приоритет:** medium (унаследовано из старого TODO).
