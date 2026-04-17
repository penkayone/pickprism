<?php
/**
 * Базовая настройка темы: supports, меню, image sizes, textdomain.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'pickprism', PICKPRISM_DIR . 'languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'navigation-widgets',
			)
		);
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 40,
				'width'       => 160,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		// Image sizes, заточенные под карточки ленты и hero single.
		add_image_size( 'pickprism-card', 800, 500, true );
		add_image_size( 'pickprism-card-2x', 1600, 1000, true );
		add_image_size( 'pickprism-hero', 1600, 800, true );

		register_nav_menus(
			array(
				'primary' => __( 'Главное меню', 'pickprism' ),
				'footer'  => __( 'Меню футера', 'pickprism' ),
			)
		);
	}
);

/**
 * Добавляет собственные размеры изображений в выпадашку "Размер" медиабиблиотеки.
 *
 * @param array<string,string> $sizes Существующие размеры.
 * @return array<string,string>
 */
add_filter(
	'image_size_names_choose',
	static function ( array $sizes ): array {
		$sizes['pickprism-card'] = __( 'Pickprism — карточка', 'pickprism' );
		$sizes['pickprism-hero'] = __( 'Pickprism — hero', 'pickprism' );
		return $sizes;
	}
);

/**
 * Регистрирует sidebar.
 */
add_action(
	'widgets_init',
	static function (): void {
		register_sidebar(
			array(
				'name'          => __( 'Боковая колонка', 'pickprism' ),
				'id'            => 'sidebar-primary',
				'description'   => __( 'Правый сайдбар на страницах со списком статей и на single.', 'pickprism' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			)
		);
	}
);
