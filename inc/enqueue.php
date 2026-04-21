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

		// Inter (body) + Manrope (display) — оба семейства одним запросом.
		wp_enqueue_style(
			'pickprism-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800;900&display=swap',
			array(),
			null
		);

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
		if ( is_singular() && comments_open() ) {
			wp_dequeue_script( 'comment-reply' );
			wp_deregister_script( 'comment-reply' );
		}
	},
	20
);

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
 * Дата-атрибут темы в <html> — для JS-feature detection.
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
		'perPage' => (int) get_option( 'posts_per_page', 12 ),
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
