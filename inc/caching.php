<?php
/**
 * Транзиенты для виджетов: топ-категории в футере, популярные в sidebar.
 * Инвалидация — при публикации/обновлении постов.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

const PICKPRISM_CACHE_TTL = 6 * HOUR_IN_SECONDS;

/**
 * Топ категорий по количеству постов.
 *
 * @param int $limit
 * @return WP_Term[]
 */
function pickprism_get_top_categories( int $limit = 10 ): array {
	$key   = 'pickprism_top_categories_' . $limit;
	$cache = get_transient( $key );

	if ( is_array( $cache ) ) {
		return $cache;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $limit,
		)
	);

	$terms = ( is_wp_error( $terms ) || ! is_array( $terms ) ) ? array() : $terms;

	set_transient( $key, $terms, PICKPRISM_CACHE_TTL );
	return $terms;
}

/**
 * Популярные теги.
 *
 * @param int $limit
 * @return WP_Term[]
 */
function pickprism_get_popular_tags( int $limit = 20 ): array {
	$key   = 'pickprism_popular_tags_' . $limit;
	$cache = get_transient( $key );

	if ( is_array( $cache ) ) {
		return $cache;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $limit,
		)
	);

	$terms = ( is_wp_error( $terms ) || ! is_array( $terms ) ) ? array() : $terms;

	set_transient( $key, $terms, PICKPRISM_CACHE_TTL );
	return $terms;
}

/**
 * Популярные посты — по количеству комментариев за последние 60 дней.
 *
 * @param int $limit
 * @return WP_Post[]
 */
function pickprism_get_popular_posts( int $limit = 5 ): array {
	$key   = 'pickprism_popular_posts_' . $limit;
	$cache = get_transient( $key );

	if ( is_array( $cache ) ) {
		return $cache;
	}

	$q = new WP_Query(
		array(
			'posts_per_page'         => $limit,
			'orderby'                => 'comment_count',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'post_status'            => 'publish',
			'fields'                 => '',
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
			'date_query'             => array(
				array( 'after' => '60 days ago' ),
			),
		)
	);

	$posts = $q->posts ?: array();

	// Fallback: если все посты свежие и без комментариев — берём самые свежие.
	if ( empty( $posts ) ) {
		$q = new WP_Query(
			array(
				'posts_per_page'         => $limit,
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'post_status'            => 'publish',
				'update_post_meta_cache' => true,
				'update_post_term_cache' => true,
			)
		);
		$posts = $q->posts ?: array();
	}

	set_transient( $key, $posts, PICKPRISM_CACHE_TTL );
	return $posts;
}

/**
 * Реестр известных транзиент-ключей темы. Любой новый кеш — добавлять сюда.
 *
 * @return string[]
 */
function pickprism_transient_keys(): array {
	$keys = array(
		'pickprism_all_categories',
	);

	foreach ( array( 5, 10, 20 ) as $limit ) {
		$keys[] = 'pickprism_top_categories_' . $limit;
		$keys[] = 'pickprism_popular_tags_' . $limit;
		$keys[] = 'pickprism_popular_posts_' . $limit;
	}

	return apply_filters( 'pickprism_transient_keys', $keys );
}

/**
 * Инвалидация кэшей при публикации и удалении.
 *
 * Используем delete_transient — корректно работает и с options-store, и с
 * external object cache (Redis/Memcached). Прямой DELETE в options не очищал бы
 * Redis-бэкенд транзиентов.
 */
function pickprism_flush_widget_cache(): void {
	foreach ( pickprism_transient_keys() as $key ) {
		delete_transient( $key );
	}
}

add_action( 'save_post', 'pickprism_flush_widget_cache' );
add_action( 'deleted_post', 'pickprism_flush_widget_cache' );
add_action( 'trashed_post', 'pickprism_flush_widget_cache' );
add_action( 'edited_term', 'pickprism_flush_widget_cache' );
add_action( 'created_term', 'pickprism_flush_widget_cache' );
add_action( 'delete_term', 'pickprism_flush_widget_cache' );

/**
 * Исключаем REST-поиск из кэша LiteSpeed.
 * Фильтр litespeed_cache_tagged / litespeed_no_cache — безопасный сигнал.
 */
add_action(
	'rest_api_init',
	static function (): void {
		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
			return;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		if ( strpos( $uri, '/pickprism/v1/' ) !== false ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			do_action( 'litespeed_control_set_nocache', 'pickprism rest endpoint' );
			nocache_headers();
		}
	},
	1
);
