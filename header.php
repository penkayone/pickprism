<?php
/**
 * Шапка сайта (pressaff-стиль): sticky-blur, логотип-марка «P»,
 * основная навигация, иконка поиска (expanding), CTA Telegram.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$page_class = is_front_page() ? 'ha-page' : '';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class( $page_class ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'К содержимому', 'pickprism' ); ?></a>

<?php if ( is_singular( 'post' ) ) : ?>
	<?php get_template_part( 'template-parts/reading-progress' ); ?>
<?php endif; ?>

<header class="pa-header" role="banner" data-header>
	<div class="pa-header__inner">
		<a class="pa-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php
			$first_letter = strtoupper( mb_substr( get_bloginfo( 'name' ), 0, 1, 'UTF-8' ) );
			if ( $first_letter === '' ) {
				$first_letter = 'P';
			}
			?>
			<span class="pa-logo__mark" aria-hidden="true"><?php echo esc_html( $first_letter ); ?></span>
			<span class="pa-logo__text"><?php bloginfo( 'name' ); ?></span>
		</a>

		<nav class="pa-nav" aria-label="<?php esc_attr_e( 'Основные ссылки', 'pickprism' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'menu_class'     => 'pa-nav__list',
						'depth'          => 1,
						'fallback_cb'    => '__return_empty_string',
					)
				);
			} else {
				// Fallback-линки — категории + базовые страницы.
				$fallback_cats = pickprism_get_top_categories( 4 );
				foreach ( $fallback_cats as $fc ) :
					$link = get_term_link( $fc );
					if ( ! is_wp_error( $link ) ) :
						?>
						<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $fc->name ); ?></a>
						<?php
					endif;
				endforeach;
			}
			?>
		</nav>

		<div class="pa-header__cta">
			<button
				type="button"
				class="pa-iconbtn"
				aria-label="<?php esc_attr_e( 'Поиск', 'pickprism' ); ?>"
				data-search-toggle
			>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
					<circle cx="11" cy="11" r="7"/>
					<path d="m21 21-4.3-4.3"/>
				</svg>
			</button>

			<a
				class="pa-btn pa-btn--accent"
				href="https://t.me/"
				target="_blank"
				rel="noopener noreferrer"
			>
				<?php esc_html_e( 'Telegram', 'pickprism' ); ?>
			</a>
		</div>

		<form
			class="pa-search"
			role="search"
			aria-hidden="true"
			data-search
			action="<?php echo esc_url( home_url( '/' ) ); ?>"
			method="get"
		>
			<svg class="pa-search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
				<circle cx="11" cy="11" r="7"/>
				<path d="m21 21-4.3-4.3"/>
			</svg>
			<input
				type="search"
				name="s"
				class="pa-search__input"
				placeholder="<?php esc_attr_e( 'Поиск по статьям, авторам, тегам…', 'pickprism' ); ?>"
				autocomplete="off"
				data-search-input
				tabindex="-1"
			/>
			<kbd class="pa-search__kbd">ESC</kbd>
			<button type="button" class="pa-search__close" aria-label="<?php esc_attr_e( 'Закрыть поиск', 'pickprism' ); ?>" data-search-close>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false">
					<path d="M6 6l12 12M18 6 6 18"/>
				</svg>
			</button>
			<div class="pa-search__dropdown" hidden data-search-dropdown></div>
		</form>
	</div>
</header>
