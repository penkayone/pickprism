<?php
/**
 * Шапка сайта.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'К содержимому', 'pickprism' ); ?></a>

<header class="site-header" role="banner">
	<div class="container site-header__inner">
		<div class="site-header__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-header__title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Основные ссылки', 'pickprism' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'menu menu--primary',
						'depth'          => 1,
						'fallback_cb'    => '__return_empty_string',
					)
				);
			}
			?>
		</nav>

		<div class="site-header__actions">
			<a
				class="btn btn--ghost btn--icon"
				href="https://www.reddit.com/"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'Наш Reddit', 'pickprism' ); ?>"
			>
				<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M22 12a2.5 2.5 0 0 0-4.2-1.8c-1.6-1-3.6-1.6-5.7-1.7l1-4.3 3 .7a1.8 1.8 0 1 0 .2-1.2l-3.6-.8a.6.6 0 0 0-.7.5l-1.1 4.8c-2.2.1-4.2.7-5.8 1.7a2.5 2.5 0 1 0-3 3.8c-.1.4-.1.8-.1 1.2 0 3.7 4.3 6.7 9.6 6.7s9.6-3 9.6-6.7c0-.4 0-.8-.1-1.2A2.5 2.5 0 0 0 22 12Zm-14.5 2a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm8.6 3.8c-1.1 1-3 1.1-3.8 1.1-.8 0-2.7-.1-3.8-1.1a.5.5 0 1 1 .7-.7c.7.7 2.2.9 3.1.9.9 0 2.4-.2 3.1-.9a.5.5 0 1 1 .7.7Zm-.1-2.3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Z"/>
				</svg>
				<span><?php esc_html_e( 'Reddit', 'pickprism' ); ?></span>
			</a>

			<a
				class="btn btn--primary btn--icon"
				href="https://t.me/"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'Наш Telegram', 'pickprism' ); ?>"
			>
				<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M9.8 15.5 9.6 19c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.7.4 1.3.2 1.5-.7L21 5.6c.3-1.2-.4-1.7-1.2-1.4L3.6 10.5c-1.2.5-1.2 1.1-.2 1.4l4.1 1.3 9.5-6c.5-.3.9-.1.5.3l-7.7 7Z"/>
				</svg>
				<span><?php esc_html_e( 'Telegram', 'pickprism' ); ?></span>
			</a>
		</div>
	</div>
</header>
