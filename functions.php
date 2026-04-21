<?php
/**
 * Точка входа темы Pickprism.
 * Подключает модули из /inc.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

/**
 * Версия темы.
 * Используется для cache-busting ассетов и в REST-ответах.
 */
if ( ! defined( 'PICKPRISM_VERSION' ) ) {
	define( 'PICKPRISM_VERSION', '1.0.0' );
}

if ( ! defined( 'PICKPRISM_DIR' ) ) {
	define( 'PICKPRISM_DIR', trailingslashit( get_template_directory() ) );
}

if ( ! defined( 'PICKPRISM_URI' ) ) {
	define( 'PICKPRISM_URI', trailingslashit( get_template_directory_uri() ) );
}

/**
 * Подгружает файлы из /inc по списку.
 *
 * @param array<string> $files Относительные пути без расширения.
 */
function pickprism_require_modules( array $files ): void {
	foreach ( $files as $file ) {
		$path = PICKPRISM_DIR . 'inc/' . $file . '.php';
		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
}

pickprism_require_modules(
	array(
		'setup',
		'enqueue',
		'cleanup',
		'security',
		'query-optimizations',
		'caching',
		'sticky',
		'ajax-search',
		'comments',
		'fixtures',
		'theme-switcher',
		'template-router',
	)
);
