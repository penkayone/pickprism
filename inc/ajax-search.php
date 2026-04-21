<?php
/**
 * REST endpoints темы:
 * - GET /pickprism/v1/search   — мгновенный поиск для dropdown
 * - GET /pickprism/v1/feed     — подгрузка постов для infinite scroll
 *
 * Валидация и санитизация на входе. Escape на выводе.
 * Rate limit: 60 запросов в минуту на IP (мягкая защита).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'rest_api_init',
	static function (): void {
		register_rest_route(
			'pickprism/v1',
			'/search',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'pickprism_rest_search',
				'permission_callback' => static fn() => pickprism_rest_rate_limit( 'search', 60 ),
				'args'                => array(
					'q'     => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => static fn( $v ) => sanitize_text_field( (string) $v ),
						'validate_callback' => static function ( $v ): bool {
							$v = trim( (string) $v );
							return mb_strlen( $v ) >= 2 && mb_strlen( $v ) <= 100;
						},
					),
					'limit' => array(
						'required'          => false,
						'type'              => 'integer',
						'default'           => 8,
						'sanitize_callback' => static fn( $v ) => max( 1, min( 20, (int) $v ) ),
					),
				),
			)
		);

		register_rest_route(
			'pickprism/v1',
			'/feed',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'pickprism_rest_feed',
				'permission_callback' => static fn() => pickprism_rest_rate_limit( 'feed', 120 ),
				'args'                => array(
					'type'     => array(
						'required'          => false,
						'type'              => 'string',
						'default'           => 'home',
						'sanitize_callback' => static fn( $v ) => sanitize_key( (string) $v ),
						'validate_callback' => static function ( $v ): bool {
							return in_array( (string) $v, array( 'home', 'category', 'tag', 'search' ), true );
						},
					),
					'value'    => array(
						'required'          => false,
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => static fn( $v ) => sanitize_text_field( (string) $v ),
					),
					'paged'    => array(
						'required'          => false,
						'type'              => 'integer',
						'default'           => 2,
						'sanitize_callback' => static fn( $v ) => max( 1, min( 1000, (int) $v ) ),
					),
					'per_page' => array(
						'required'          => false,
						'type'              => 'integer',
						'default'           => (int) get_option( 'posts_per_page', 10 ),
						'sanitize_callback' => static fn( $v ) => max( 1, min( 24, (int) $v ) ),
					),
				),
			)
		);
	}
);

/**
 * Мягкий rate limit по IP + endpoint: N запросов в минуту.
 * Возвращает WP_Error с 429 при превышении.
 *
 * @return true|WP_Error
 */
function pickprism_rest_rate_limit( string $bucket, int $limit_per_minute ) {
	$ip = pickprism_client_ip();
	if ( '' === $ip ) {
		return true; // без IP лимитить невозможно; не блокируем.
	}

	$key     = 'ppr_rl_' . $bucket . '_' . md5( $ip );
	$current = (int) get_transient( $key );

	if ( $current >= $limit_per_minute ) {
		return new WP_Error(
			'pickprism_rate_limited',
			__( 'Слишком много запросов. Попробуйте через минуту.', 'pickprism' ),
			array( 'status' => 429 )
		);
	}

	set_transient( $key, $current + 1, MINUTE_IN_SECONDS );
	return true;
}

/**
 * Определяет клиентский IP, уважая типовые прокси-заголовки.
 */
function pickprism_client_ip(): string {
	$candidates = array();

	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$candidates[] = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
	}
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$xff          = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$first        = trim( (string) explode( ',', $xff )[0] );
		$candidates[] = $first;
	}
	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$candidates[] = sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) );
	}

	foreach ( $candidates as $ip ) {
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}
	}
	return '';
}

/**
 * Поиск для dropdown.
 */
function pickprism_rest_search( WP_REST_Request $request ): WP_REST_Response {
	$q     = trim( (string) $request->get_param( 'q' ) );
	$limit = (int) $request->get_param( 'limit' );

	$wp_query = new WP_Query(
		array(
			's'                   => $q,
			'posts_per_page'      => $limit,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'relevance',
			'post_type'           => 'post',
		)
	);

	$items = array();
	foreach ( $wp_query->posts as $p ) {
		/** @var WP_Post $p */
		$items[] = array(
			'id'        => (int) $p->ID,
			'title'     => html_entity_decode( wp_strip_all_tags( get_the_title( $p ) ), ENT_QUOTES, 'UTF-8' ),
			'url'       => get_permalink( $p ),
			'excerpt'   => wp_trim_words( wp_strip_all_tags( $p->post_excerpt ?: $p->post_content ), 20, '…' ),
			'date'      => get_the_date( '', $p ),
			'thumbnail' => get_the_post_thumbnail_url( $p, 'thumbnail' ) ?: null,
		);
	}

	$response = rest_ensure_response(
		array(
			'query'   => $q,
			'count'   => count( $items ),
			'items'   => $items,
			'viewAll' => add_query_arg( 's', rawurlencode( $q ), home_url( '/' ) ),
		)
	);

	$response->header( 'Cache-Control', 'private, max-age=30' );
	return $response;
}

/**
 * Infinite scroll — догрузка следующей страницы ленты.
 */
function pickprism_rest_feed( WP_REST_Request $request ) {
	$type     = (string) $request->get_param( 'type' );
	$value    = (string) $request->get_param( 'value' );
	$paged    = (int) $request->get_param( 'paged' );
	$per_page = (int) $request->get_param( 'per_page' );

	$args = array(
		'posts_per_page'      => $per_page,
		'paged'               => $paged,
		'post_status'         => 'publish',
		'post_type'           => 'post',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => false,
	);

	switch ( $type ) {
		case 'category':
			$term = get_term_by( 'slug', $value, 'category' );
			if ( ! $term instanceof WP_Term ) {
				return new WP_Error( 'pickprism_not_found', __( 'Категория не найдена', 'pickprism' ), array( 'status' => 404 ) );
			}
			$args['cat'] = (int) $term->term_id;
			break;

		case 'tag':
			$term = get_term_by( 'slug', $value, 'post_tag' );
			if ( ! $term instanceof WP_Term ) {
				return new WP_Error( 'pickprism_not_found', __( 'Тег не найден', 'pickprism' ), array( 'status' => 404 ) );
			}
			$args['tag_id'] = (int) $term->term_id;
			break;

		case 'search':
			if ( mb_strlen( $value ) < 2 ) {
				return new WP_Error( 'pickprism_bad_query', __( 'Слишком короткий запрос', 'pickprism' ), array( 'status' => 400 ) );
			}
			$args['s'] = $value;
			break;

		case 'home':
		default:
			break;
	}

	$wp_query = new WP_Query( $args );
	$items    = array();

	foreach ( $wp_query->posts as $p ) {
		$items[] = pickprism_post_to_card( $p );
	}

	$response = rest_ensure_response(
		array(
			'items'    => $items,
			'paged'    => $paged,
			'maxPages' => (int) $wp_query->max_num_pages,
			'hasMore'  => $paged < (int) $wp_query->max_num_pages,
			'found'    => (int) $wp_query->found_posts,
		)
	);
	$response->header( 'Cache-Control', 'private, max-age=60' );
	return $response;
}

/**
 * Приводит WP_Post к структуре карточки для JS.
 * Расширено для редизайна: hue, readTime, primaryCategory, isNew.
 *
 * @param WP_Post $p
 * @return array<string,mixed>
 */
function pickprism_post_to_card( WP_Post $p ): array {
	$tags = get_the_terms( $p, 'post_tag' );
	$tags = is_array( $tags ) ? $tags : array();

	$tags_slim = array_map(
		static function ( WP_Term $t ): array {
			return array(
				'name' => $t->name,
				'slug' => $t->slug,
				'url'  => get_term_link( $t ),
			);
		},
		array_slice( $tags, 0, 3 )
	);

	$thumb_id = get_post_thumbnail_id( $p );
	$thumb    = null;
	if ( $thumb_id ) {
		$src = wp_get_attachment_image_src( $thumb_id, 'pickprism-card' );
		if ( is_array( $src ) ) {
			$thumb = array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'srcset' => wp_get_attachment_image_srcset( $thumb_id, 'pickprism-card' ) ?: '',
				'sizes'  => wp_get_attachment_image_sizes( $thumb_id, 'pickprism-card' ) ?: '',
				'alt'    => (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ),
			);
		}
	}

	// Новые поля редизайна.
	$primary_term    = function_exists( 'pickprism_primary_category' ) ? pickprism_primary_category( (int) $p->ID ) : null;
	$primary_payload = null;
	if ( $primary_term instanceof WP_Term ) {
		$primary_payload = array(
			'id'   => (int) $primary_term->term_id,
			'name' => $primary_term->name,
			'slug' => $primary_term->slug,
			'url'  => get_term_link( $primary_term ),
		);
	}
	$hue       = function_exists( 'pickprism_cover_hue' ) ? pickprism_cover_hue( (int) $p->ID ) : 24;
	$read_time = function_exists( 'pickprism_reading_time' ) ? pickprism_reading_time( (int) $p->ID ) : 1;
	$is_new    = function_exists( 'pickprism_is_new' ) ? pickprism_is_new( (int) $p->ID ) : false;

	return array(
		'id'              => (int) $p->ID,
		'title'           => html_entity_decode( wp_strip_all_tags( get_the_title( $p ) ), ENT_QUOTES, 'UTF-8' ),
		'url'             => get_permalink( $p ),
		'excerpt'         => wp_trim_words( wp_strip_all_tags( $p->post_excerpt ?: $p->post_content ), 24, '…' ),
		'date'            => get_the_date( 'j F', $p ),
		'dateIso'         => get_the_date( 'c', $p ),
		'tags'            => $tags_slim,
		'thumbnail'       => $thumb,
		'hue'             => (int) $hue,
		'readTime'        => (int) $read_time,
		'isNew'           => (bool) $is_new,
		'primaryCategory' => $primary_payload,
	);
}
