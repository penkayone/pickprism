# PLAN — форма комментариев (design + functionality)

**Ветка:** `feature/comment-form`
**Дата:** 2026-04-17

## Цель
Нативные WP-комментарии на single.php, свой дизайн в духе g2, AJAX-отправка с прогрессивным улучшением и многослойной anti-spam защитой без внешних сервисов. Без Gravatar — свой плейсхолдер с инициалом.

## Принципы (утверждено заказчиком)

- **Только анонимные комментарии.** Регистрации и пользователей на сайте не будет. Форма всегда требует имя и email, не ссылается на «вошли как…».
- **Тёмный текст на мягких фонах.** Палитра аватаров — 8 soft-цветов, текст всегда `--c-text`.
- **Свой reply-механизм.** Штатный `comment-reply.js` не подключается.

### Реализация анонимности

- В `comment_form_defaults`:
  - `must_log_in` → `''`
  - `logged_in_as` → `''`
  - `comment_notes_after` → `''`
  - `comment_notes_before` → свой текст (по желанию, без упоминания логина).
- Фильтр `option_comment_registration` → всегда `0` на фронте темы — чтобы настройка «требовать регистрацию» не влияла на UI и REST.
- В REST-обработчике:
  - `$comment_data['user_id'] = 0` всегда, даже если `is_user_logged_in()`.
  - `comment_author`, `comment_author_email` — только из POST, из REST-аргументов; никогда не подменяем полями текущего пользователя.
  - Передаём `$comment_data` в `wp_handle_comment_submission` уже «обезличенным».
- В `pickprism_comment_callback` — никаких классов/бейджей «автор поста»:
  - не используем `.bypostauthor`, `.comment-author-admin`;
  - не выводим post-author badge.
- Reply-кнопка показывается всем без условий (ни `comment_registration`, ни `is_user_logged_in` не проверяем).
- На single — никаких ссылок «войти, чтобы комментировать».

## Скоуп

### Backend (`inc/comments.php` — новый модуль)
- Регистрация модуля в `functions.php` → `pickprism_require_modules([...,'comments'])`.
- **`comments.php` в корне темы** — шаблон, подключается из `single.php` через `comments_template()`.
- **Кастомный вывод списка** через `wp_list_comments( ['callback' => 'pickprism_comment_callback'] )`.
- **Фильтры на форму:**
  - `comment_form_default_fields` — убрать поле `url`.
  - `comment_form_fields` — добавить honeypot (`website_url`, `aria-hidden`, label off-screen), скрытое `pickprism_ts` (timestamp), и nonce `pickprism_comment`.
  - `comment_form_defaults` — переопределить разметку полей (class-имена `comment-form__*`), заголовки на русском через `__()`; обнулить `must_log_in` и `logged_in_as`.
  - `option_comment_registration` → `0`.
- **REST endpoint `POST /pickprism/v1/comments`:**
  - Аргументы: `comment_post_ID`, `author`, `email`, `comment` (content), `parent` (0 или ID), `pickprism_ts`, `website_url` (honeypot), `_wpnonce` (wp_rest), `pickprism_comment_nonce`.
  - Permission callback: `pickprism_rest_rate_limit('comments', 3)` — 3 попытки в минуту на IP.
  - Проверки (порядок):
    1. nonce (`wp_rest` + `pickprism_comment`).
    2. Referer хост совпадает с `home_url()`.
    3. Honeypot `website_url` пустой.
    4. Timestamp: `time() - ts >= 4` сек и `<= 3600` сек.
    5. Post существует, опубликован, `comments_open($id)`.
    6. `wp_handle_comment_submission()` с форсированным `user_id = 0` — ядро WP (duplicate, flood, disallowed_keys, max length, moderation). `comment_registration` игнорируется (флаг заглушён фильтром).
  - Валидация полей: имя 2..50, email — `is_email`, content 3..5000 символов.
  - Ответ JSON: `{ status: 'approved'|'pending'|'error', message, html?, id? }`.
    - `html` — отрендеренный один комментарий (через ob_start + `pickprism_comment_callback` в режиме render-one) чтобы клиент мог вставить в DOM без перезагрузки.
- **Плейсхолдер аватара:**
  - `pickprism_comment_avatar( string $email, string $name, int $size = 44 ): string` → `<div class="comment-avatar" data-bg="N">И</div>`.
  - Цвет — детерминированно: `hexdec( substr(md5(strtolower(trim($email))),0,2) ) % 8` → индекс палитры.
  - Инициал — первая буква имени (mb-safe, верхний регистр, фоллбэк `?`).
  - Фильтр `get_avatar` → возвращать наш плейсхолдер только на single (чтоб не ломать админку/виджеты).
- **i18n:** все строки через `__()`/`_e()` c `pickprism`.
- **Escape/sanitize:** `sanitize_text_field` на имя, `sanitize_email`, `wp_kses` на content (как ядро). Вывод — `esc_html`/`esc_attr`/`wp_kses_post`.

### Frontend
- **`assets/src/scss/components/_comments.scss`** — новый файл, импорт в `main.scss`.
  - Список: flex-row (аватар слева 44px → 36px mobile + тело справа), header (имя bold + `·` + дата muted + «Ответить» справа), разделитель `border-bottom --c-border` между ветками верхнего уровня.
  - Вложенность: `.children` → `margin-inline-start: --sp-6`, макс 2 уровня, 3-й и глубже визуально как 2-й.
  - Форма: токены от `.search-form` (радиус, бордер, focus-ring `--c-accent-soft`), сетка `1fr 1fr` для имя+email на ≥ md, стекается на mobile. Textarea `min-height: 120px`, `field-sizing: content` (+ JS autoresize fallback). Кнопка — `--c-accent`, loading state (spinner), disabled opacity 0.6.
  - Honeypot: `position:absolute; left:-9999px; width:1px; height:1px; opacity:0; pointer-events:none` + `tabindex="-1"` + `autocomplete="off"`.
  - Состояния: `.comment-form__notice--success`, `--error`, «комментарии закрыты», «будьте первым».
  - **Палитра аватаров (8 soft фонов, тёмный текст `--c-text`):**
    - `--avatar-c-0: #e8eaff;` (синий)
    - `--avatar-c-1: #e3f5ec;` (зелёный)
    - `--avatar-c-2: #fdecef;` (розовый)
    - `--avatar-c-3: #fff0da;` (персиковый)
    - `--avatar-c-4: #ece6fb;` (сиреневый)
    - `--avatar-c-5: #e0f1f6;` (голубой)
    - `--avatar-c-6: #fbeedb;` (жёлто-бежевый)
    - `--avatar-c-7: #eef3e0;` (оливковый)
    - Точные значения подбираются верстальщиком, главное — все мягкие, контраст AA с `--c-text`.

- **`assets/src/js/comments.js`** — новый файл, импорт в `main.js`.
  - При DOMContentLoaded: если форма есть → проставляем `hidden pickprism_ts` = `Math.floor(Date.now()/1000)`.
  - `submit` перехват → fetch POST на `Pickprism.restUrl + 'comments'`, заголовок `X-WP-Nonce: Pickprism.nonce`, тело — `URLSearchParams` из формы.
  - Disabled submit + spinner на время запроса, отмена double-submit.
  - Success: если `approved` — `insertAdjacentHTML` в список, scroll, очистить форму; если `pending` — notice.
  - Error: inline notice с текстом с сервера.
  - Reply: перехват клика на `.comment-reply-link`, перенос формы под целевой комментарий, `comment_parent` hidden. Штатный `comment-reply.js` НЕ подключаем.
  - Прогрессивное улучшение: если JS упал → стандартный POST на `wp-comments-post.php`.

- **`inc/enqueue.php`** — enqueue `pickprism-comments` handle + `commentNonce` в `window.Pickprism`, только на `is_singular('post')` + `comments_open()`.

### Security (обязательный этап)
- Аудит: nonce, CSRF (Referer), XSS в выводе (инициал → `esc_html` + `mb_substr`), rate-limit, honeypot-логика, утечки (не возвращаем email/IP автора).
- `get_avatar` override не ломает админку / виджеты.
- `wp_handle_comment_submission` вызывается корректно, `user_id = 0` форсирован.
- Проверить что `comment_registration` реально отключён фильтром (не полагаемся на опцию БД).

### QA
1. Гость постит коммент → approved или pending.
2. Honeypot заполнен → 400.
3. Submit раньше 4 сек → 400.
4. 4-й коммент за минуту с одного IP → 429.
5. Без JS → стандартный POST работает (fallback).
6. Reply → вложенность верна, `comment_parent` проставлен.
7. Аватар: инициал верен, цвет детерминирован (один email = один цвет).
8. XSS: `<script>` в имени/контенте экранирован.
9. Mobile: форма стекается, аватары 36px.
10. Закрытые комментарии → notice.
11. **Админ залогинен и постит** → комментарий сохраняется с `user_id = 0`, без бейджа «автор поста».
12. Настройка WP «требовать регистрацию для комментирования» включена → форма всё равно работает для анонимов.
13. Нет ссылок «войти, чтобы комментировать» в любом состоянии.

## Вне скоупа
- Лайки, голосование, редактирование комментов.
- Кастомная модерация в админке.
- Уведомления автору/подписка на ветку.
- Markdown/rich text — стандартный allowed HTML ядра.
- Self-host аватаров.

## Риски
- **`wp_handle_comment_submission` делает `wp_die` на ошибках** → оборачиваем через `wp_die_handler` фильтр, бросаем `Exception`, ловим в REST → отвечаем JSON.
- **`get_avatar` фильтр конфликтует с LiteSpeed lazy** → применяем только `is_singular()`.
- **Fallback POST на `wp-comments-post.php` редиректит** — приемлемо для no-JS edge case.
- **Rate-limit 3/мин** — согласовано для спокойного блога.

## Порядок делегирования
1. `backend` — `inc/comments.php`, `comments.php`, REST, аватар, фильтры анонимности.
2. `frontend` — `_comments.scss` + `comments.js` + правки `enqueue.php` + импорты.
3. `security` — аудит (особое внимание: `user_id = 0`, отсутствие утечек, анонимность при залогиненном админе).
4. `reviewer` — code review.
5. `qa` — сценарии выше.
6. `npm run build` → коммит `assets/dist/`.
7. Финальный отчёт заказчику. PR не создаём.

## Коммиты (логически 3)
- `feat(comments): add template, custom list callback and avatar placeholder`
- `feat(comments): add REST endpoint with anti-spam (honeypot, time-trap, rate-limit)`
- `feat(comments): add styles and AJAX submit with progressive enhancement`

## Оценка
~6-8 часов работы агентов параллельно: backend 3-4ч, frontend 2-3ч, security+qa 1-2ч.
