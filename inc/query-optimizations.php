<?php
/**
 * Оптимизации основных запросов.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

/**
 * Прогреваем кэш метаданных и терминов для карточек ленты,
 * чтобы не было N+1 при выводе тегов и превью.
 */
add_action(
	'pre_get_posts',
	static function ( WP_Query $q ): void {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}

		if ( $q->is_home() || $q->is_front_page() || $q->is_category() || $q->is_tag() || $q->is_search() ) {
			$q->set( 'update_post_term_cache', true );
			$q->set( 'update_post_meta_cache', true );
			$q->set( 'no_found_rows', false ); // нужна пагинация
			$q->set( 'ignore_sticky_posts', false );
		}
	}
);

/**
 * Отключаем пагинацию по комментариям — экономит один COUNT на single.
 */
add_filter( 'comments_template_query_args', static function ( array $args ): array {
	$args['no_found_rows'] = true;
	return $args;
} );

/**
 * Ограничиваем глубину ревизий (необязательно, но на 1000 постах эффект заметен).
 */
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 5 );
}
