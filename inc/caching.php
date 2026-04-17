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
			'posts_per_page'      => $limit,
			'orderby'             => 'comment_count',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'fields'              => '',
			'date_query'          => array(
				array( 'after' => '60 days ago' ),
			),
		)
	);

	$posts = $q->posts ?: array();

	// Fallback: если все посты свежие и без комментариев — берём самые свежие.
	if ( empty( $posts ) ) {
		$q = new WP_Query(
			array(
				'posts_per_page'      => $limit,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'post_status'         => 'publish',
			)
		);
		$posts = $q->posts ?: array();
	}

	set_transient( $key, $posts, PICKPRISM_CACHE_TTL );
	return $posts;
}

/**
 * Инвалидация кэшей при публикации и удалении.
 */
function pickprism_flush_widget_cache(): void {
	global $wpdb;

	// Точечно удаляем по префиксу через options (транзиенты хранятся там при отсутствии object cache).
	$like = $wpdb->esc_like( '_transient_pickprism_' ) . '%';
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$like,
			$wpdb->esc_like( '_transient_timeout_pickprism_' ) . '%'
		)
	);

	if ( wp_using_ext_object_cache() ) {
		wp_cache_flush_group( 'pickprism' );
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
