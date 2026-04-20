# PLAN — cleanup minor issues по форме комментариев

**Ветка:** `feature/comment-form`
**Дата:** 2026-04-20

## Задача
Быстрая уборка minor-находок из code review перед merge: устранить дублирующийся фильтр, добавить валидацию глубины reply, убрать мёртвый legacy-ключ, обернуть подкладывание `$_POST` в try/finally. Скоуп — только 4 точечных фикса по списку, без рефакторинга.

## Стек
PHP 8+ / WordPress (classic theme). Только `inc/comments.php`. Фронт не трогаем, `npm run build` не нужен.

## Решение

### Фикс 1 — дублирующийся фильтр (`inc/comments.php:26`)
Удалить строку `add_filter( 'option_comment_registration', '__return_zero' )`. Оставить только `pre_option_comment_registration` (строка 182) — он срабатывает раньше и перекрывает `option_*`. Хэндлер — общий `__return_zero`, отдельной функции нет. Комментарий над фильтром приведём в соответствие.

### Фикс 2 — валидация depth reply (`inc/comments.php:449-454`)
В блоке валидации `comment_parent` после проверки «parent существует + принадлежит посту» добавить:
- Если `get_option('thread_comments')` выключен — пропускаем (плоский режим, глубина всегда 1).
- Иначе считаем глубину parent: цикл вверх по `comment_parent`, инкремент счётчика. Защита от поврежденных данных — лимит 100 итераций.
- Глубина нового коммента = (глубина parent) + 1. Если > `get_option('thread_comments_depth', 5)` → `WP_Error 'pickprism_too_deep'` 400 с сообщением «Слишком глубокая вложенность. Ответьте на комментарий выше».

### Фикс 3 — чистка `user_ID` (`inc/comments.php:468`)
В клоужере `$force_anon` убрать `$data['user_ID'] = 0;` — это legacy-ключ camelCase, современное ядро WP его не читает. Оставить `user_id` (snake_case).

### Фикс 4 — try/finally на `$_POST` (`inc/comments.php:458-506`)
Обернуть подкладывание `$_POST` и последующий вызов `wp_handle_comment_submission` в `try/finally`:
- Перед подкладыванием: `$post_backup = $_POST;`
- Подкладываем нужные ключи.
- Существующий `try/catch` для `wp_die` остаётся внутри (catch возвращает WP_Error).
- В `finally` — `$_POST = $post_backup;` + `remove_filter` для `preprocess_comment` и `wp_die_*` (сейчас они снимаются отдельно в catch и после try; в finally это будет надёжнее и без дублирования).

### Комментарий why к `pickprism_render_single_comment` depth (строка 569)
Короткий комментарий: для AJAX insert точная глубина не важна (элемент вставляется JS-ом в нужный уровень), но нужен >1 чтобы получить класс `comment-item--nested`, если parent есть.

## Шаги
1. Реализация 4 фиксов в `inc/comments.php` → `backend`
2. Code review качества правок (регрессии, логика depth-подсчёта, чистота try/finally) → `reviewer`
3. QA: edge cases и regression → `qa`
4. Создать `TECH_DEBT.md` в корне темы с пунктом про дизайн-токены для фоновых цветов warning/success/error
5. Обновить историю в `CLAUDE.md` темы: строчка за 2026-04-20
6. Один коммит: `fix(comments): cleanup minor issues from code review`

## Acceptance criteria
- [ ] `option_comment_registration` в `inc/comments.php` отсутствует (есть только `pre_option_*`)
- [ ] Reply с глубиной > `thread_comments_depth` возвращает 400 с понятным сообщением
- [ ] Reply на допустимой глубине работает как раньше
- [ ] При `thread_comments=0` валидация depth пропускается
- [ ] В `$force_anon` нет строки `$data['user_ID']`
- [ ] `$_POST` восстанавливается после вызова `wp_handle_comment_submission` (в т.ч. на ошибке)
- [ ] Фильтры `preprocess_comment` и `wp_die_*` снимаются через finally — нет висящих фильтров
- [ ] Reviewer: нет регрессий, логика depth корректна
- [ ] QA: edge cases проходят (глубокий reply блокируется, допустимая глубина проходит, обычный коммент без parent работает, flat-режим обходит depth-валидацию)
- [ ] `TECH_DEBT.md` создан
- [ ] `CLAUDE.md` темы обновлён

## Риски
- Защита от бесконечной петли в цикле подсчёта глубины — лимит 100 итераций (реально в WP-реализации не встречается, но на повреждённых данных защитит).
- `try/finally` вокруг уже существующего `try/catch` — стандартный паттерн, reviewer проверит что фильтры и `$_POST` снимаются корректно на всех путях выхода.

## Оценка
~30-45 минут (backend ~15 мин, reviewer ~10 мин, qa ~15 мин).
