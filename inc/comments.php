<?php
/**
 * Нативные WP-комментарии с анонимным режимом, своим шаблоном,
 * плейсхолдером-аватаром и REST endpoint для AJAX-отправки.
 *
 * Ключевые инварианты:
 * - Всегда анонимные комментарии: user_id = 0 даже для залогиненных.
 * - Никаких ссылок «войти, чтобы комментировать».
 * - comment_registration настройки сайта игнорируется — тема форсит анонимный режим.
 * - Никаких бейджей «автор поста» в выводе.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * 1. Анонимный режим: отключаем всё, что привязывает коммент к юзеру.
 * ------------------------------------------------------------------------- */

/**
 * Не отправляем уведомления об одобрении модератору от имени юзера,
 * не подставляем avatar пользователя и т.п. — оставляем ядру решать,
 * но сам коммент идёт обезличенным (user_id=0) через preprocess_comment ниже.
 */
add_filter(
	'preprocess_comment',
	static function ( array $data ): array {
		// Форсим user_id = 0 на уровне ядра — даже если коммент прилетел не через наш REST.
		$data['user_id'] = 0;
		return $data;
	},
	1 // рано — до остальных фильтров.
);

/* -------------------------------------------------------------------------
 * 2. Дефолты формы: своя разметка, без logged_in_as / must_log_in.
 * ------------------------------------------------------------------------- */

/**
 * Строит HTML полей «Имя» и «Email» в анонимном стиле — один и тот же набор
 * используется и для анонимов, и для авторизованных (WP на логине вырезает
 * эти поля из формы, ниже мы их возвращаем вручную).
 *
 * @return array{author:string,email:string}
 */
function pickprism_comment_author_email_fields(): array {
	$commenter = wp_get_current_commenter();
	$req       = (bool) get_option( 'require_name_email' );
	$req_attr  = $req ? 'required aria-required="true"' : '';

	$author = sprintf(
		'<p class="comment-form__row comment-form__row--author">' .
			'<label for="author" class="comment-form__label">%s%s</label>' .
			'<input id="author" name="author" type="text" class="comment-form__input" value="%s" maxlength="50" autocomplete="name" %s />' .
		'</p>',
		esc_html__( 'Имя', 'pickprism' ),
		$req ? ' <span class="comment-form__req" aria-hidden="true">*</span>' : '',
		esc_attr( $commenter['comment_author'] ),
		$req_attr
	);

	$email = sprintf(
		'<p class="comment-form__row comment-form__row--email">' .
			'<label for="email" class="comment-form__label">%s%s</label>' .
			'<input id="email" name="email" type="email" class="comment-form__input" value="%s" maxlength="100" autocomplete="email" %s />' .
		'</p>',
		esc_html__( 'Email', 'pickprism' ),
		$req ? ' <span class="comment-form__req" aria-hidden="true">*</span>' : '',
		esc_attr( $commenter['comment_author_email'] ),
		$req_attr
	);

	return array(
		'author' => $author,
		'email'  => $email,
	);
}

/**
 * Единая нормализация полей формы — срабатывает и для гостей, и для залогиненных.
 *
 * WP-ядро в `comment_form()` рендерит поля по циклу с условием
 * `! is_user_logged_in() || ! isset( $original_fields[ $name ] )`. Из-за этого
 * родные ключи `author`/`email`/`url` для залогиненного юзера в вывод не попадают.
 * Мы выпиливаем их из массива и добавляем author/email под уникальными ключами
 * `pickprism_author` / `pickprism_email` — HTML-атрибуты `name` остаются
 * `author` и `email`, т.е. бэкенду и REST ничего переучивать не нужно.
 *
 * Дополнительно: переопределяем textarea, добавляем honeypot + timestamp + nonce,
 * упорядочиваем поля.
 */
add_filter(
	'comment_form_fields',
	static function ( array $fields ): array {
		unset( $fields['author'], $fields['email'], $fields['url'] );

		$custom = pickprism_comment_author_email_fields();

		$comment_field = sprintf(
			'<p class="comment-form__row comment-form__row--comment">' .
				'<label for="comment" class="comment-form__label">%s <span class="comment-form__req" aria-hidden="true">*</span></label>' .
				'<textarea id="comment" name="comment" class="comment-form__textarea" rows="5" maxlength="5000" required aria-required="true"></textarea>' .
			'</p>',
			esc_html__( 'Комментарий', 'pickprism' )
		);

		$ordered = array(
			'pickprism_author' => $custom['author'],
			'pickprism_email'  => $custom['email'],
			'comment'          => $comment_field,
		);

		foreach ( $fields as $key => $value ) {
			if ( ! isset( $ordered[ $key ] ) && 'comment' !== $key ) {
				$ordered[ $key ] = $value;
			}
		}

		// Honeypot. Реальный пользователь не заполнит — скрыт CSS + aria-hidden + tabindex=-1.
		$honeypot = sprintf(
			'<p class="comment-form__honeypot" aria-hidden="true">' .
				'<label for="website_url">%s</label>' .
				'<input id="website_url" name="website_url" type="text" tabindex="-1" autocomplete="off" value="" />' .
			'</p>',
			esc_html__( 'Если вы человек, оставьте это поле пустым', 'pickprism' )
		);

		// Timestamp — клиент обновит в JS, сервер сверит разницу.
		$ts = sprintf(
			'<input type="hidden" name="pickprism_ts" value="%d" />',
			time()
		);

		// Доп nonce поверх штатного.
		$extra_nonce = sprintf(
			'<input type="hidden" name="pickprism_comment_nonce" value="%s" />',
			esc_attr( wp_create_nonce( 'pickprism_comment' ) )
		);

		$ordered['pickprism_extras'] = $honeypot . $ts . $extra_nonce;

		return $ordered;
	}
);

/**
 * Переопределяем дефолты самой формы (заголовки, подписи, кнопка, logged_in_as).
 */
add_filter(
	'comment_form_defaults',
	static function ( array $defaults ): array {
		$defaults['title_reply']          = __( 'Оставить комментарий', 'pickprism' );
		$defaults['title_reply_to']       = __( 'Ответить %s', 'pickprism' );
		$defaults['cancel_reply_link']    = __( 'Отменить ответ', 'pickprism' );
		$defaults['label_submit']         = __( 'Отправить', 'pickprism' );
		$defaults['submit_button']        = '<button name="%1$s" type="submit" id="%2$s" class="btn btn--primary comment-form__submit">%4$s</button>';
		$defaults['submit_field']         = '<p class="comment-form__actions">%1$s %2$s</p>';
		$defaults['comment_notes_before'] = '<p class="comment-form__notes">' . esc_html__( 'Ваш email не будет опубликован. Обязательные поля помечены *.', 'pickprism' ) . '</p>';
		$defaults['comment_notes_after']  = '';
		$defaults['must_log_in']          = ''; // на всякий случай — у нас анонимный режим.
		$defaults['logged_in_as']         = ''; // убираем «вы вошли как …».
		$defaults['class_form']           = 'comment-form';
		$defaults['class_container']      = 'comment-respond';
		$defaults['title_reply_before']   = '<h3 id="reply-title" class="comment-respond__title">';
		$defaults['title_reply_after']    = '</h3>';

		return $defaults;
	}
);

/**
 * Полностью отключаем настройку «требовать регистрацию» на фронте.
 * `pre_option_*` срабатывает ДО чтения опции из БД и перекрывает её —
 * отдельный `option_comment_registration` не нужен. Админка не затрагивается:
 * фильтр активен только на запросах, где подключается тема (фронт + REST).
 */
add_filter( 'pre_option_comment_registration', '__return_zero' );

/* -------------------------------------------------------------------------
 * 3. Кастомный вывод списка комментариев.
 * ------------------------------------------------------------------------- */

/**
 * True, если коммент от автора поста (сравниваем email).
 */
function pickprism_comment_is_by_post_author( WP_Comment $comment ): bool {
	$post_id = (int) $comment->comment_post_ID;
	if ( $post_id <= 0 ) {
		return false;
	}
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	$post_author_email = (string) get_the_author_meta( 'user_email', (int) $post->post_author );
	$comment_email     = strtolower( trim( (string) $comment->comment_author_email ) );
	if ( $post_author_email === '' || $comment_email === '' ) {
		return false;
	}
	return strtolower( $post_author_email ) === $comment_email;
}

/**
 * Callback для wp_list_comments. Рендерит ОДИН уровень, WP сам обходит дерево.
 * Редизайн: .pa-comment (depth=1) / .pa-reply (depth>1) + бейдж «Автор».
 *
 * @param WP_Comment $comment
 * @param array      $args
 * @param int        $depth
 */
function pickprism_comment_callback( $comment, $args, $depth ): void {
	$tag       = ( ! empty( $args['style'] ) && 'div' === $args['style'] ) ? 'div' : 'li';
	$author    = (string) $comment->comment_author;
	$email     = (string) $comment->comment_author_email;
	$content   = (string) $comment->comment_content;
	$date_iso  = get_comment_date( 'c', $comment );
	$date_disp = get_comment_date( '', $comment );
	$max_depth = (int) ( $args['max_depth'] ?? 5 );
	$is_author = pickprism_comment_is_by_post_author( $comment );
	$is_reply  = $depth > 1;
	$size      = $is_reply ? 36 : 44;

	$classes = array( $is_reply ? 'pa-reply' : 'pa-comment' );
	if ( $is_author ) {
		$classes[] = 'is-author';
	}

	?>
	<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<div class="pa-comment__avatar<?php echo $is_reply ? ' pa-comment__avatar--sm' : ''; ?>" aria-hidden="true">
			<?php echo pickprism_comment_avatar( $email, $author, $size ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — avatar returns escaped HTML. ?>
		</div>
		<div class="pa-comment__body">
			<div class="pa-comment__head">
				<span class="pa-comment__author">
					<?php echo esc_html( $author !== '' ? $author : __( 'Аноним', 'pickprism' ) ); ?>
					<?php if ( $is_author ) : ?>
						<span class="pa-comment__badge"><?php esc_html_e( 'Автор', 'pickprism' ); ?></span>
					<?php endif; ?>
				</span>
				<time class="pa-comment__time" datetime="<?php echo esc_attr( $date_iso ); ?>">
					<?php echo esc_html( $date_disp ); ?>
				</time>
				<?php if ( '0' === $comment->comment_approved ) : ?>
					<span class="pa-comment__pending"><?php esc_html_e( 'Ожидает одобрения', 'pickprism' ); ?></span>
				<?php endif; ?>
			</div>

			<div class="pa-comment__text">
				<?php echo wp_kses_post( wpautop( $content ) ); ?>
			</div>

			<div class="pa-comment__actions">
				<?php
				echo get_comment_reply_link(
					array_merge(
						$args,
						array(
							'reply_text' => __( 'Ответить', 'pickprism' ),
							'depth'      => $depth,
							'max_depth'  => $max_depth,
							'before'     => '',
							'after'      => '',
						)
					)
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — WP-core escaped.
				?>
			</div>
		</div>
	<?php
	// Закрывающий тег для <li>/<div> ставит сам WP, если end-callback не задан.
}

/**
 * Закрывающий тег для callback'а — чтобы WP не дописывал свой </li>.
 */
function pickprism_comment_end_callback(): void {
	echo "</li>\n";
}

/**
 * Force style=ul для wp_list_comments — чтобы children wrapper был <ul>,
 * который мы переименуем в .pa-replies через pickprism_replies_class_swap в comments.php.
 */
add_filter(
	'wp_list_comments_args',
	static function ( $args ) {
		if ( is_array( $args ) ) {
			$args['style'] = 'ul';
		}
		return $args;
	}
);

/**
 * Переименовывает class="children" → class="pa-replies" в HTML-строке списка комментариев.
 * Вызывается из comments.php через ob_start/ob_end.
 */
function pickprism_replies_class_swap( string $html ): string {
	return str_replace( 'class="children"', 'class="pa-replies"', $html );
}


/* -------------------------------------------------------------------------
 * 4. Плейсхолдер аватара (без Gravatar).
 * ------------------------------------------------------------------------- */

/**
 * Генерирует HTML плейсхолдера: цветной квадрат + инициал.
 */
function pickprism_comment_avatar( string $email, string $name, int $size = 44 ): string {
	$email_norm = strtolower( trim( $email ) );
	$hash       = md5( $email_norm !== '' ? $email_norm : $name );
	$idx        = hexdec( substr( $hash, 0, 2 ) ) % 8;

	$initial = '';
	$clean   = trim( $name );
	if ( $clean !== '' ) {
		$first   = mb_substr( $clean, 0, 1, 'UTF-8' );
		$initial = mb_strtoupper( $first, 'UTF-8' );
	}
	if ( $initial === '' ) {
		$initial = '?';
	}

	return sprintf(
		'<span class="comment-avatar" data-bg="%1$d" style="width:%2$dpx;height:%2$dpx" aria-hidden="true">%3$s</span>',
		(int) $idx,
		(int) $size,
		esc_html( $initial )
	);
}

/**
 * Подменяем get_avatar только на single (не трогаем админку/виджеты).
 *
 * @param string $avatar      HTML by core.
 * @param mixed  $id_or_email User id, WP_Comment, email string.
 * @param int    $size        Requested size.
 * @return string
 */
add_filter(
	'get_avatar',
	static function ( string $avatar, $id_or_email, int $size ): string {
		if ( is_admin() || ! is_singular() ) {
			return $avatar;
		}

		$email = '';
		$name  = '';

		if ( $id_or_email instanceof WP_Comment ) {
			$email = (string) $id_or_email->comment_author_email;
			$name  = (string) $id_or_email->comment_author;
		} elseif ( is_string( $id_or_email ) ) {
			$email = $id_or_email;
		} elseif ( is_object( $id_or_email ) && isset( $id_or_email->comment_author_email ) ) {
			$email = (string) $id_or_email->comment_author_email;
			$name  = isset( $id_or_email->comment_author ) ? (string) $id_or_email->comment_author : '';
		}

		return pickprism_comment_avatar( $email, $name, (int) $size );
	},
	10,
	3
);

/* -------------------------------------------------------------------------
 * 5. REST endpoint: POST /pickprism/v1/comments.
 * ------------------------------------------------------------------------- */

add_action(
	'rest_api_init',
	static function (): void {
		register_rest_route(
			'pickprism/v1',
			'/comments',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'pickprism_rest_submit_comment',
				'permission_callback' => static fn() => pickprism_rest_rate_limit( 'comments', 3 ),
				'args'                => array(
					'comment_post_ID'         => array(
						'required' => true,
						'type'     => 'integer',
					),
					'author'                  => array(
						'required' => true,
						'type'     => 'string',
					),
					'email'                   => array(
						'required' => true,
						'type'     => 'string',
					),
					'comment'                 => array(
						'required' => true,
						'type'     => 'string',
					),
					'comment_parent'          => array(
						'required' => false,
						'type'     => 'integer',
						'default'  => 0,
					),
					'pickprism_ts'            => array(
						'required' => true,
						'type'     => 'integer',
					),
					'website_url'             => array(
						'required' => false,
						'type'     => 'string',
						'default'  => '',
					),
					'pickprism_comment_nonce' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			)
		);
	}
);

/**
 * Обработчик REST submit. Возвращает JSON {status, message, html?, id?}.
 *
 * @return WP_REST_Response|WP_Error
 */
function pickprism_rest_submit_comment( WP_REST_Request $request ) {
	// 1. Nonce (wp_rest проверяется платформой через X-WP-Nonce + cookie_auth).
	$extra_nonce = (string) $request->get_param( 'pickprism_comment_nonce' );
	if ( ! wp_verify_nonce( $extra_nonce, 'pickprism_comment' ) ) {
		return new WP_Error(
			'pickprism_bad_nonce',
			__( 'Устаревшая форма. Обновите страницу и попробуйте снова.', 'pickprism' ),
			array( 'status' => 403 )
		);
	}

	// 2. Referer хост == home_url host.
	$referer = (string) $request->get_header( 'referer' );
	if ( $referer === '' ) {
		return new WP_Error( 'pickprism_bad_referer', __( 'Неверный источник запроса.', 'pickprism' ), array( 'status' => 403 ) );
	}
	$ref_host  = wp_parse_url( $referer, PHP_URL_HOST );
	$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( ! $ref_host || ! $home_host || strcasecmp( $ref_host, $home_host ) !== 0 ) {
		return new WP_Error( 'pickprism_bad_referer', __( 'Неверный источник запроса.', 'pickprism' ), array( 'status' => 403 ) );
	}

	// 3. Honeypot.
	$honeypot = trim( (string) $request->get_param( 'website_url' ) );
	if ( $honeypot !== '' ) {
		// Молчим — отвечаем 400 без деталей, чтобы боту не дать подсказку.
		return new WP_Error( 'pickprism_spam', __( 'Не удалось отправить комментарий.', 'pickprism' ), array( 'status' => 400 ) );
	}

	// 4. Timestamp: 4..3600 сек назад.
	$ts    = (int) $request->get_param( 'pickprism_ts' );
	$delta = time() - $ts;
	if ( $delta < 4 ) {
		return new WP_Error( 'pickprism_too_fast', __( 'Слишком быстро. Подождите пару секунд.', 'pickprism' ), array( 'status' => 400 ) );
	}
	if ( $delta > HOUR_IN_SECONDS ) {
		return new WP_Error( 'pickprism_stale', __( 'Форма устарела. Обновите страницу.', 'pickprism' ), array( 'status' => 400 ) );
	}

	// 5. Post.
	$post_id = (int) $request->get_param( 'comment_post_ID' );
	$post    = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_Error( 'pickprism_no_post', __( 'Публикация не найдена.', 'pickprism' ), array( 'status' => 404 ) );
	}
	if ( ! comments_open( $post_id ) ) {
		return new WP_Error( 'pickprism_closed', __( 'Комментарии к этой публикации закрыты.', 'pickprism' ), array( 'status' => 403 ) );
	}

	// 6. Валидация полей.
	$author  = sanitize_text_field( (string) $request->get_param( 'author' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$content = trim( (string) $request->get_param( 'comment' ) );
	$parent  = (int) $request->get_param( 'comment_parent' );

	if ( mb_strlen( $author ) < 2 || mb_strlen( $author ) > 50 ) {
		return new WP_Error( 'pickprism_bad_author', __( 'Имя должно быть от 2 до 50 символов.', 'pickprism' ), array( 'status' => 400 ) );
	}
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'pickprism_bad_email', __( 'Введите корректный email.', 'pickprism' ), array( 'status' => 400 ) );
	}
	if ( mb_strlen( $content ) < 3 || mb_strlen( $content ) > 5000 ) {
		return new WP_Error( 'pickprism_bad_content', __( 'Комментарий должен быть от 3 до 5000 символов.', 'pickprism' ), array( 'status' => 400 ) );
	}
	if ( $parent > 0 ) {
		$parent_comment = get_comment( $parent );
		if ( ! $parent_comment || (int) $parent_comment->comment_post_ID !== $post_id ) {
			return new WP_Error( 'pickprism_bad_parent', __( 'Родительский комментарий не найден.', 'pickprism' ), array( 'status' => 400 ) );
		}

		// Валидация глубины: не даём превысить thread_comments_depth.
		// Если threading выключен — пропускаем, плоский режим сам схлопнет parent в 0.
		if ( (int) get_option( 'thread_comments' ) === 1 ) {
			$max_depth = (int) get_option( 'thread_comments_depth', 5 );
			if ( $max_depth < 1 ) {
				$max_depth = 5;
			}

			// Считаем глубину parent-цепочки: parent=depth 1, его parent=2, и т.д.
			// Защита от поврежденных данных (петля parent → самого себя или цикла):
			// не больше $max_depth + 1 итераций.
			$parent_depth = 1;
			$cursor       = $parent_comment;
			$guard        = 0;
			while ( (int) $cursor->comment_parent > 0 && $guard < $max_depth + 1 ) {
				$next = get_comment( (int) $cursor->comment_parent );
				if ( ! $next ) {
					break;
				}
				$parent_depth++;
				$cursor = $next;
				$guard++;
			}

			// Глубина нового коммента = parent_depth + 1.
			if ( $parent_depth + 1 > $max_depth ) {
				return new WP_Error(
					'pickprism_too_deep',
					__( 'Слишком глубокая вложенность. Ответьте на комментарий уровнем выше.', 'pickprism' ),
					array( 'status' => 400 )
				);
			}
		}
	}

	// 7. Передаём в ядро через wp_handle_comment_submission. Оно делает duplicate/flood/moderation/disallowed.
	// wp_handle_comment_submission читает из $_POST — подкладываем, а в finally восстанавливаем,
	// чтобы не протекать наружу глобальным стейтом при любом исходе (успех / WP_Error / исключение).
	$post_backup = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing — сохраняем снимок для восстановления.

	// Форсим анонимность — даже если админ залогинен.
	$force_anon = static function ( array $data ): array {
		$data['user_id'] = 0;
		return $data;
	};

	// wp_handle_comment_submission вызывает wp_die при ошибке — ловим через фильтр.
	$die_handler = static function () {
		return static function ( $message, $title = '', $args = array() ): void {
			$msg = is_wp_error( $message ) ? $message->get_error_message() : (string) $message;
			throw new RuntimeException( $msg !== '' ? $msg : __( 'Не удалось отправить комментарий.', 'pickprism' ) );
		};
	};

	$submission_error = null;
	$comment          = null;

	try {
		$_POST['comment_post_ID'] = $post_id;
		$_POST['author']          = $author;
		$_POST['email']           = $email;
		$_POST['url']             = '';
		$_POST['comment']         = $content;
		$_POST['comment_parent']  = $parent;

		add_filter( 'preprocess_comment', $force_anon, 9 );
		add_filter( 'wp_die_handler', $die_handler );
		add_filter( 'wp_die_ajax_handler', $die_handler );
		add_filter( 'wp_die_json_handler', $die_handler );

		try {
			$comment = wp_handle_comment_submission(
				array(
					'comment_post_ID' => $post_id,
					'author'          => $author,
					'email'           => $email,
					'url'             => '',
					'comment'         => $content,
					'comment_parent'  => $parent,
				)
			);
		} catch ( \Throwable $e ) {
			$submission_error = $e->getMessage();
		}
	} finally {
		remove_filter( 'preprocess_comment', $force_anon, 9 );
		remove_filter( 'wp_die_handler', $die_handler );
		remove_filter( 'wp_die_ajax_handler', $die_handler );
		remove_filter( 'wp_die_json_handler', $die_handler );
		$_POST = $post_backup; // phpcs:ignore WordPress.Security.NonceVerification.Missing — восстанавливаем снимок.
	}

	if ( $submission_error !== null ) {
		return new WP_Error( 'pickprism_submit_failed', $submission_error, array( 'status' => 400 ) );
	}

	if ( is_wp_error( $comment ) ) {
		return new WP_Error(
			'pickprism_submit_failed',
			$comment->get_error_message() ?: __( 'Не удалось отправить комментарий.', 'pickprism' ),
			array( 'status' => 400 )
		);
	}

	if ( ! $comment instanceof WP_Comment ) {
		return new WP_Error( 'pickprism_submit_failed', __( 'Не удалось отправить комментарий.', 'pickprism' ), array( 'status' => 500 ) );
	}

	// Защитный пояс: если ядро где-то проставило user_id — зануляем.
	if ( (int) $comment->user_id !== 0 ) {
		wp_update_comment(
			array(
				'comment_ID' => (int) $comment->comment_ID,
				'user_id'    => 0,
			)
		);
		$comment->user_id = 0;
	}

	$approved = ( '1' === (string) $comment->comment_approved );

	$html = '';
	if ( $approved ) {
		$html = pickprism_render_single_comment( $comment );
	}

	return rest_ensure_response(
		array(
			'status'  => $approved ? 'approved' : 'pending',
			'message' => $approved
				? __( 'Комментарий опубликован.', 'pickprism' )
				: __( 'Комментарий отправлен и появится после одобрения.', 'pickprism' ),
			'id'      => (int) $comment->comment_ID,
			'parent'  => (int) $comment->comment_parent,
			'html'    => $html,
		)
	);
}

/**
 * Рендерит один комментарий через наш callback (для AJAX insert).
 *
 * Для AJAX-вставки точная глубина не критична — JS вставит элемент в нужный
 * уровень DOM-дерева. Но нам важно попасть в ветку `depth > 1`, если parent
 * есть, чтобы получить модификатор `comment-item--nested`.
 *
 * @param WP_Comment $comment
 */
function pickprism_render_single_comment( WP_Comment $comment ): string {
	// Walker_Comment перед вызовом callback выставляет $GLOBALS['comment']; в AJAX-рендере
	// делаем то же, иначе comment_ID()/get_comment_reply_link() работают с «прежним» комментом.
	$prev               = $GLOBALS['comment'] ?? null;
	$GLOBALS['comment'] = $comment;

	ob_start();
	pickprism_comment_callback(
		$comment,
		array(
			'style'     => 'ol',
			'max_depth' => (int) get_option( 'thread_comments_depth', 5 ),
		),
		max( 1, (int) $comment->comment_parent > 0 ? 2 : 1 )
	);
	echo "</li>\n";
	$html = (string) ob_get_clean();

	$GLOBALS['comment'] = $prev;

	return $html;
}
