<?php
/**
 * Переключатель визуальных тем (design variants).
 *
 * Храним выбранный вариант в user meta (по-пользовательски, чтобы посетителей
 * не затронуло). Переключение — кнопкой в админ-баре для пользователей
 * с capability `edit_theme_options`.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

const PICKPRISM_THEME_META = 'pickprism_theme_variant';

/**
 * Список доступных вариантов: slug => человекочитаемое имя.
 *
 * @return array<string,string>
 */
function pickprism_theme_variants(): array {
	return array(
		'default'  => __( 'Default', 'pickprism' ),
		'pressaff' => __( 'Pressaff', 'pickprism' ),
	);
}

/**
 * Текущий выбранный вариант. Для анонимов и невалидных значений — 'default'.
 */
function pickprism_current_theme_variant(): string {
	if ( ! is_user_logged_in() ) {
		return 'default';
	}
	$value    = (string) get_user_meta( get_current_user_id(), PICKPRISM_THEME_META, true );
	$variants = pickprism_theme_variants();
	return isset( $variants[ $value ] ) ? $value : 'default';
}

/**
 * Добавляем класс `theme-<slug>` на body, если вариант не default.
 */
add_filter(
	'body_class',
	static function ( array $classes ): array {
		$variant = pickprism_current_theme_variant();
		if ( 'default' !== $variant ) {
			$classes[] = 'theme-' . sanitize_html_class( $variant );
		}
		return $classes;
	}
);

/**
 * Кнопка-переключатель в админ-баре. Один клик → следующий вариант по кругу.
 * Текущий и следующий вариант видны в подписи: «Design: Default → Pressaff».
 */
add_action(
	'admin_bar_menu',
	static function ( WP_Admin_Bar $bar ): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$variants = pickprism_theme_variants();
		$slugs    = array_keys( $variants );
		$current  = pickprism_current_theme_variant();
		$idx      = array_search( $current, $slugs, true );
		$next     = $slugs[ ( ( false === $idx ? 0 : (int) $idx ) + 1 ) % count( $slugs ) ];

		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'pickprism_set_theme',
					'theme'  => $next,
				),
				admin_url( 'admin-post.php' )
			),
			'pickprism_set_theme'
		);

		$bar->add_node(
			array(
				'id'     => 'pickprism-theme',
				'parent' => 'top-secondary',
				/* translators: 1: текущий вариант, 2: следующий вариант */
				'title'  => esc_html( sprintf( __( 'Design: %1$s → %2$s', 'pickprism' ), $variants[ $current ], $variants[ $next ] ) ),
				'href'   => esc_url( $url ),
				'meta'   => array(
					'title' => __( 'Переключить вариант дизайна', 'pickprism' ),
				),
			)
		);
	},
	100
);

/**
 * Обработчик переключения: проверяет capability + nonce, сохраняет user meta,
 * редиректит обратно.
 */
add_action(
	'admin_post_pickprism_set_theme',
	static function (): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die(
				esc_html__( 'Недостаточно прав.', 'pickprism' ),
				esc_html__( 'Отказано', 'pickprism' ),
				array( 'response' => 403 )
			);
		}

		check_admin_referer( 'pickprism_set_theme' );

		$theme    = isset( $_GET['theme'] ) ? sanitize_key( wp_unslash( (string) $_GET['theme'] ) ) : 'default';
		$variants = pickprism_theme_variants();
		if ( ! isset( $variants[ $theme ] ) ) {
			$theme = 'default';
		}

		update_user_meta( get_current_user_id(), PICKPRISM_THEME_META, $theme );

		$back = wp_get_referer();
		wp_safe_redirect( $back ?: home_url( '/' ) );
		exit;
	}
);
