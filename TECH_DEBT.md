# Tech debt — pickprism

Список вещей, которые стоит починить, но они не блокируют текущие задачи.
Пополняется в процессе работы. Когда берётся в работу — заводится отдельная задача/ветка.

---

## Дизайн-токены для status-цветов (warning / success / error)

**Где:** `assets/src/scss/components/_comments.scss`
- `.comment-pending` (строки ~152–161): `background: #fff4d1; color: #8a6a00;` — warning-фон.
- `.comment-form__notice--success` (строки ~370–374): `background: #e5f6ec; color: #1f6a42; border: 1px solid #b8e0c9;`.
- `.comment-form__notice--error` (строки ~376–380): `background: #fdecec; color: #9a2a2e; border: 1px solid #f5c3c5;`.

**Проблема.** Цвета захардкожены в хексах и разбросаны по компоненту. В `abstracts/_tokens.scss` для status-ролей нет ни bg, ни border, ни text-оттенков — есть только solid-акценты (`--c-danger` и т.п.).

**Что сделать.** Добавить в `_tokens.scss` триплеты для трёх ролей (warning / success / error / info), по шаблону Bootstrap/Radix:

```scss
--c-warning-bg:     #fff4d1;
--c-warning-fg:     #8a6a00;
--c-warning-border: #f0dfa5;

--c-success-bg:     #e5f6ec;
--c-success-fg:     #1f6a42;
--c-success-border: #b8e0c9;

--c-error-bg:       #fdecec;
--c-error-fg:       #9a2a2e;
--c-error-border:   #f5c3c5;
```

Затем прогнать `_comments.scss` и заменить захардкоженные значения на переменные. После — пересобрать (`npm run build`) и закоммитить `assets/dist/`.

**Оценка:** 15–20 минут.
**Приоритет:** low. Влияет только на расширяемость темы (вторая итерация дизайна, тёмная тема).
