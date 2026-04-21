<?php
/**
 * Template router: подменяет топ-уровневый шаблон (front-page.php, single.php,
 * index.php и т.п.) на одноимённый из `themes/<variant>/`, если он существует.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'template_include',
	static function ( string $template ): string {
		$variant = function_exists( 'pickprism_current_theme_variant' )
			? pickprism_current_theme_variant()
			: 'default';

		if ( 'default' === $variant ) {
			return $template;
		}

		$basename = basename( $template );
		$override = PICKPRISM_DIR . 'themes/' . $variant . '/' . $basename;

		return is_readable( $override ) ? $override : $template;
	}
);
