// Comments — AJAX-отправка на /pickprism/v1/comments + свой reply-механизм.
// Прогрессивное улучшение: если JS упал, штатный POST на wp-comments-post.php работает.
// Штатный comment-reply.js НЕ подключается — эта реализация заменяет его.

const cfg = () => (typeof window !== 'undefined' && window.Pickprism) || {};

function $(sel, root = document) {
  return root.querySelector(sel);
}

function $$(sel, root = document) {
  return Array.from(root.querySelectorAll(sel));
}

/**
 * Возвращает или создаёт узел <p class="comment-form__notice">...</p> в форме.
 */
function ensureNotice(form) {
  let el = form.querySelector('.comment-form__notice');
  if (!el) {
    el = document.createElement('p');
    el.className = 'comment-form__notice';
    el.setAttribute('role', 'status');
    el.setAttribute('aria-live', 'polite');
    el.hidden = true;
    form.insertBefore(el, form.firstChild);
  }
  return el;
}

function showNotice(form, type, message) {
  const el = ensureNotice(form);
  el.className = `comment-form__notice comment-form__notice--${type}`;
  el.textContent = message;
  el.hidden = false;
}

function hideNotice(form) {
  const el = form.querySelector('.comment-form__notice');
  if (el) el.hidden = true;
}

/**
 * Обновляет hidden pickprism_ts актуальным timestamp (при монтировании формы).
 */
function refreshTimestamp(form) {
  const ts = form.querySelector('input[name="pickprism_ts"]');
  if (ts) ts.value = Math.floor(Date.now() / 1000);
}

// -------------------------------------------------------------------------
// Reply — свой обработчик.
// -------------------------------------------------------------------------

/**
 * WP-разметка reply-ссылки: <a class="comment-reply-link"
 *   data-commentid, data-postid, data-respondelement="respond", ...>.
 * Мы перемещаем #respond под целевой коммент и ставим hidden comment_parent.
 */
function initReply() {
  const respond = document.getElementById('respond');
  if (!respond) return;

  // Запомним изначальную позицию #respond, чтоб можно было вернуть назад.
  const placeholder = document.createElement('span');
  placeholder.id = 'respond-placeholder';
  placeholder.hidden = true;
  respond.parentNode.insertBefore(placeholder, respond);

  document.addEventListener('click', (e) => {
    const link = e.target.closest('a.comment-reply-link');
    if (!link) return;
    e.preventDefault();

    const commentId = link.getAttribute('data-commentid') || '0';
    const target = document.getElementById(`comment-${commentId}`);
    if (!target) return;

    // Убедимся, что в форме есть hidden comment_parent.
    let parentInput = respond.querySelector('input[name="comment_parent"]');
    if (!parentInput) {
      parentInput = document.createElement('input');
      parentInput.type = 'hidden';
      parentInput.name = 'comment_parent';
      const form = respond.querySelector('form');
      if (form) form.appendChild(parentInput);
    }
    parentInput.value = commentId;

    // Переместим форму под target.
    target.appendChild(respond);

    // Покажем «отменить ответ».
    const cancel = respond.querySelector('#cancel-comment-reply-link');
    if (cancel) {
      cancel.style.display = '';
      cancel.addEventListener('click', cancelReply, { once: true });
    }

    // Скролл + фокус.
    const textarea = respond.querySelector('textarea[name="comment"]');
    if (textarea) {
      textarea.focus({ preventScroll: true });
      textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });

  function cancelReply(e) {
    e.preventDefault();
    const parentInput = respond.querySelector('input[name="comment_parent"]');
    if (parentInput) parentInput.value = '0';
    placeholder.parentNode.insertBefore(respond, placeholder);
    const cancel = respond.querySelector('#cancel-comment-reply-link');
    if (cancel) cancel.style.display = 'none';
  }

  // Скрываем "отменить" изначально.
  const cancelInit = respond.querySelector('#cancel-comment-reply-link');
  if (cancelInit) cancelInit.style.display = 'none';
}

// -------------------------------------------------------------------------
// AJAX submit.
// -------------------------------------------------------------------------

async function submitComment(form, e) {
  e.preventDefault();

  const { restUrl, nonce, i18n = {} } = cfg();
  if (!restUrl || !nonce) {
    // JS-конфиг не загружен — уступаем браузеру (фолбэк на action).
    form.submit();
    return;
  }

  const submitBtn = form.querySelector('.comment-form__submit');
  if (submitBtn) {
    if (submitBtn.getAttribute('aria-busy') === 'true') return; // double-submit guard
    submitBtn.setAttribute('aria-busy', 'true');
    submitBtn.disabled = true;
  }
  hideNotice(form);

  const fd = new FormData(form);
  // Обязательные поля для REST (имена совпадают).
  const body = new URLSearchParams();
  fd.forEach((v, k) => body.append(k, typeof v === 'string' ? v : ''));

  try {
    const res = await fetch(`${restUrl}comments`, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': nonce,
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      credentials: 'same-origin',
      body: body.toString(),
    });

    const data = await res.json().catch(() => null);

    if (!res.ok || !data) {
      const msg = (data && (data.message || data.code)) || i18n.errorGeneric || 'Что-то пошло не так.';
      showNotice(form, 'error', msg);
      return;
    }

    if (data.status === 'approved' && data.html) {
      insertApprovedComment(form, data);
      showNotice(form, 'success', data.message || 'Комментарий опубликован.');
      resetForm(form);
    } else if (data.status === 'pending') {
      showNotice(form, 'success', data.message || 'Комментарий отправлен и появится после одобрения.');
      resetForm(form);
    } else {
      showNotice(form, 'error', data.message || i18n.errorGeneric || 'Не удалось отправить комментарий.');
    }
  } catch (err) {
    console.error('[pickprism] comment submit failed', err);
    showNotice(form, 'error', i18n.errorGeneric || 'Сетевая ошибка. Попробуйте ещё раз.');
  } finally {
    if (submitBtn) {
      submitBtn.removeAttribute('aria-busy');
      submitBtn.disabled = false;
    }
  }
}

function resetForm(form) {
  const content = form.querySelector('textarea[name="comment"]');
  if (content) content.value = '';
  const parentInput = form.querySelector('input[name="comment_parent"]');
  if (parentInput) parentInput.value = '0';
  refreshTimestamp(form);
  // author/email оставляем — UX.
}

/**
 * Вставляет HTML одного комментария в список.
 * Если reply (parent > 0) — в .children у родителя (создаём при отсутствии).
 * Иначе — в конец верхнего списка.
 */
function insertApprovedComment(form, data) {
  const section = document.getElementById('comments');
  if (!section) return;

  const parentId = parseInt(
    form.querySelector('input[name="comment_parent"]')?.value || '0',
    10
  );

  let list = section.querySelector('.comment-list');
  if (!list) {
    list = document.createElement('ol');
    list.className = 'comment-list';
    const title = section.querySelector('.comments-title');
    if (title) {
      title.after(list);
    } else {
      section.prepend(list);
    }
  }

  const tmp = document.createElement('div');
  tmp.innerHTML = data.html.trim();
  const newItem = tmp.firstElementChild;
  if (!newItem) return;

  if (parentId > 0) {
    const parentEl = document.getElementById(`comment-${parentId}`);
    if (parentEl) {
      let children = parentEl.querySelector(':scope > .children');
      if (!children) {
        children = document.createElement('ol');
        children.className = 'children';
        parentEl.appendChild(children);
      }
      children.appendChild(newItem);
    } else {
      list.appendChild(newItem);
    }
  } else {
    list.appendChild(newItem);
  }

  // Scroll + subtle highlight.
  newItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
  newItem.style.transition = 'background-color 1.4s ease';
  newItem.style.backgroundColor = 'var(--c-accent-soft)';
  setTimeout(() => {
    newItem.style.backgroundColor = '';
  }, 1200);
}

// -------------------------------------------------------------------------
// Init
// -------------------------------------------------------------------------

export function initComments() {
  const form = document.getElementById('commentform');
  if (!form) return;

  refreshTimestamp(form);
  initReply();

  form.addEventListener('submit', (e) => submitComment(form, e));
}
