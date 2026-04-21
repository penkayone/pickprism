<?php
/**
 * Подключение стилей и скриптов темы.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

/**
 * Возвращает версию ассета по mtime (если файл есть) — для dev-cache-bust.
 * В prod fallback — PICKPRISM_VERSION.
 */
function pickprism_asset_version( string $relative ): string {
	$path = PICKPRISM_DIR . ltrim( $relative, '/' );
	if ( is_readable( $path ) ) {
		return (string) filemtime( $path );
	}
	return PICKPRISM_VERSION;
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$css_rel = 'assets/dist/css/main.css';
		$js_rel  = 'assets/dist/js/main.js';

		// Google Fonts — Inter 400..700 (можно заменить на self-hosted в assets/fonts/).
		wp_enqueue_style(
			'pickprism-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
			array(),
			null
		);

		// Дополнительный шрифт под активный вариант дизайна.
		$variant = function_exists( 'pickprism_current_theme_variant' )
			? pickprism_current_theme_variant()
			: 'default';
		if ( 'pressaff' === $variant ) {
			wp_enqueue_style(
				'pickprism-fonts-manrope',
				'https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&display=swap',
				array(),
				null
			);
		}

		wp_enqueue_style(
			'pickprism-main',
			PICKPRISM_URI . $css_rel,
			array( 'pickprism-fonts' ),
			pickprism_asset_version( $css_rel )
		);

		wp_enqueue_script(
			'pickprism-main',
			PICKPRISM_URI . $js_rel,
			array(),
			pickprism_asset_version( $js_rel ),
			true
		);

		// Данные для JS: REST URL, nonce, текущий запрос для infinite scroll.
		$data = array(
			'restUrl'        => esc_url_raw( rest_url( 'pickprism/v1/' ) ),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'homeUrl'        => esc_url_raw( home_url( '/' ) ),
			'searchMinLen'   => 2,
			'searchDebounce' => 300,
			'feed'           => pickprism_feed_context(),
			'i18n'           => array(
				'searching'    => __( 'Ищем…', 'pickprism' ),
				'noResults'    => __( 'Ничего не найдено', 'pickprism' ),
				'showAll'      => __( 'Показать все результаты', 'pickprism' ),
				'loadMore'     => __( 'Показать ещё', 'pickprism' ),
				'loading'      => __( 'Загружаем…', 'pickprism' ),
				'endOfFeed'    => __( 'Это все статьи', 'pickprism' ),
				'errorGeneric' => __( 'Что-то пошло не так. Попробуйте ещё раз.', 'pickprism' ),
			),
		);

		wp_add_inline_script(
			'pickprism-main',
			'window.Pickprism = ' . wp_json_encode( $data ) . ';',
			'before'
		);

		// Штатный comment-reply.js нам не нужен — у нас свой обработчик в comments.js.
		// Отменяем регистрацию на single, чтобы браузер не тянул лишний скрипт.
		if ( is_singular() && comments_open() ) {
			wp_dequeue_script( 'comment-reply' );
			wp_deregister_script( 'comment-reply' );
		}
	},
	20 // после ядра, чтобы dequeue comment-reply сработал.
);

/**
 * Гасим штатный wp_enqueue_scripts для threaded-replies — тема сама решает.
 * add_action на wp_default_scripts не нужен, достаточно dequeue выше.
 */

/**
 * Preconnect для Google Fonts — чуть быстрее FCP.
 */
add_filter(
	'wp_resource_hints',
	static function ( array $hints, string $relation ): array {
		if ( 'preconnect' === $relation ) {
			$hints[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
			$hints[] = 'https://fonts.googleapis.com';
		}
		return $hints;
	},
	10,
	2
);

/**
 * Ставим в <html> no-js → заменяем на has-js сразу в JS. Классика для прогрессивного UX.
 */
add_filter(
	'language_attributes',
	static function ( string $output ): string {
		return $output . ' data-theme="pickprism"';
	}
);

/**
 * Контекст ленты для infinite scroll — какой запрос догружать.
 *
 * @return array<string,mixed>
 */
function pickprism_feed_context(): array {
	$ctx = array(
		'type'    => 'home',
		'value'   => '',
		'paged'   => max( 1, (int) get_query_var( 'paged', 1 ) ),
		'perPage' => (int) get_option( 'posts_per_page', 10 ),
	);

	if ( is_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$ctx['type']  = 'category';
			$ctx['value'] = (string) $term->slug;
		}
	} elseif ( is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$ctx['type']  = 'tag';
			$ctx['value'] = (string) $term->slug;
		}
	} elseif ( is_search() ) {
		$ctx['type']  = 'search';
		$ctx['value'] = (string) get_search_query( false );
	} elseif ( is_home() || is_front_page() ) {
		$ctx['type'] = 'home';
	}

	return $ctx;
}

/**
 * Удаляет Emoji-скрипты ядра (не нужны, весят и тормозят).
 */
add_action(
	'init',
	static function (): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}
);
