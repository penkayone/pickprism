<?php
/**
 * Базовые меры безопасности.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

/**
 * Прячем версию WP из RSS/генератора.
 */
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Отключаем XML-RPC полностью — он нам не нужен и это вектор брутфорса.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter(
	'wp_headers',
	static function ( array $headers ): array {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

/**
 * Запрещаем листинг пользователей через REST неаутентифицированным.
 * Открытый /wp-json/wp/v2/users раскрывает логины.
 */
add_filter(
	'rest_endpoints',
	static function ( array $endpoints ): array {
		if ( ! is_user_logged_in() ) {
			if ( isset( $endpoints['/wp/v2/users'] ) ) {
				unset( $endpoints['/wp/v2/users'] );
			}
			if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
				unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
			}
		}
		return $endpoints;
	}
);

/**
 * Блокируем author-enumeration (?author=1 → редирект на логин).
 */
add_action(
	'template_redirect',
	static function (): void {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);

/**
 * Security headers.
 * CSP намеренно мягкий — тему могут расширять плагинами, жёсткий CSP сломает гутенберг и карты.
 * При необходимости ужесточать — на уровне сервера (LiteSpeed/Nginx).
 */
add_action(
	'send_headers',
	static function (): void {
		if ( is_admin() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
	}
);
