<?php
/**
 * Чистка head и прочий мусор, который WP тащит по умолчанию.
 * Меньше лишнего = быстрее и безопаснее.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	}
);

// Намеренно НЕ снимаем ?ver= с JS/CSS — это единственный механизм cache-busting.
// Без него браузер хранит старую сборку до ручной очистки кеша.
