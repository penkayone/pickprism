<?php
/**
 * ACF: пути load/save JSON в /acf/ и регистрация Options Page «Главная страница».
 *
 * Требует Advanced Custom Fields PRO (для acf_add_options_page).
 * При отсутствии плагина модуль молча выходит — тема продолжает работать.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

/**
 * Папка с ACF JSON-файлами внутри темы.
 */
if ( ! defined( 'PICKPRISM_ACF_DIR' ) ) {
	define( 'PICKPRISM_ACF_DIR', PICKPRISM_DIR . 'acf' );
}

/**
 * Куда ACF записывает JSON при сохранении группы полей в админке.
 * Один путь — берём свой, чтобы не мусорить в /acf-json/.
 *
 * @param string $path Текущий путь.
 * @return string
 */
function pickprism_acf_save_json( string $path ): string {
	return PICKPRISM_ACF_DIR;
}
add_filter( 'acf/settings/save_json', 'pickprism_acf_save_json' );

/**
 * Откуда ACF читает JSON-группы. Добавляем нашу папку рядом с дефолтной.
 *
 * @param array<string> $paths Текущие пути.
 * @return array<string>
 */
function pickprism_acf_load_json( array $paths ): array {
	$paths[] = PICKPRISM_ACF_DIR;
	return $paths;
}
add_filter( 'acf/settings/load_json', 'pickprism_acf_load_json' );

/**
 * Регистрация Options Page «Настройки сайта» (ACF PRO).
 * Все глобальные настройки темы (hero главной, и впредь — футер, шапка, соц. сети и т. п.)
 * выносятся сюда и разделяются вкладками внутри одной группы полей.
 */
function pickprism_acf_register_options_pages(): void {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'  => __( 'Настройки сайта', 'pickprism' ),
			'menu_title'  => __( 'Настройки сайта', 'pickprism' ),
			'menu_slug'   => 'pickprism-settings',
			'capability'  => 'edit_theme_options',
			'icon_url'    => 'dashicons-admin-generic',
			'position'    => 59,
			'redirect'    => false,
			'autoload'    => true,
		)
	);
}
add_action( 'acf/init', 'pickprism_acf_register_options_pages' );
