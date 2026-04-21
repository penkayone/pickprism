<?php
/**
 * Pressaff — тёмный хедер.
 * Подключается напрямую из themes/pressaff/front-page.php (без get_header()).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

// Fallback-меню: 5 топ-категорий, если нет зарегистрированного меню.
$fallback_cats = array();
if ( ! has_nav_menu( 'primary' ) ) {
	$fallback_cats = pickprism_get_top_categories( 5 );
}
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

<header class="pa-header" role="banner">
	<div class="container pa-header__inner">

		<!-- Логотип -->
		<a class="pa-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<span class="pa-header__logo-mark" aria-hidden="true">p</span>
			<span><?php bloginfo( 'name' ); ?></span>
		</a>

		<!-- Основное меню -->
		<nav class="pa-header__nav" aria-label="<?php esc_attr_e( 'Основные ссылки', 'pickprism' ); ?>">
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'pa-header__menu',
						'depth'          => 1,
						'fallback_cb'    => '__return_empty_string',
					)
				);
				?>
			<?php elseif ( ! empty( $fallback_cats ) ) : ?>
				<ul class="pa-header__menu">
					<?php foreach ( $fallback_cats as $cat ) :
						$cat_link = get_term_link( $cat );
						if ( is_wp_error( $cat_link ) ) {
							continue;
						}
						?>
						<li>
							<a href="<?php echo esc_url( $cat_link ); ?>">
								<?php echo esc_html( $cat->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</nav>

		<!-- Правые действия -->
		<div class="pa-header__actions">
			<!-- Кнопка поиска -->
			<button
				class="pa-header__search-btn"
				type="button"
				aria-expanded="false"
				aria-controls="pa-header-search"
				aria-label="<?php esc_attr_e( 'Открыть поиск', 'pickprism' ); ?>"
				data-pa-search-toggle
			>
				<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M10 2a8 8 0 1 1-5.3 14.1l-3.4 3.4-1.4-1.4 3.4-3.4A8 8 0 0 1 10 2Zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z"/>
				</svg>
			</button>

			<!-- Telegram CTA -->
			<a
				class="pa-header__tg-btn"
				href="https://t.me/"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e( 'Наш Telegram-канал', 'pickprism' ); ?>"
			>
				<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M9.8 15.5 9.6 19c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.7.4 1.3.2 1.5-.7L21 5.6c.3-1.2-.4-1.7-1.2-1.4L3.6 10.5c-1.2.5-1.2 1.1-.2 1.4l4.1 1.3 9.5-6c.5-.3.9-.1.5.3l-7.7 7Z"/>
				</svg>
				<?php esc_html_e( 'Telegram', 'pickprism' ); ?>
			</a>

			<!-- Бургер (мобайл) -->
			<button
				class="pa-header__burger"
				type="button"
				aria-expanded="false"
				aria-controls="pa-mobile-nav"
				aria-label="<?php esc_attr_e( 'Открыть меню', 'pickprism' ); ?>"
				data-pa-burger
			>
				<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h18v2H3v-2Z"/>
				</svg>
			</button>
		</div>

	</div>

	<!-- Поисковый дроп -->
	<div class="pa-header__search-wrap" id="pa-header-search" hidden aria-label="<?php esc_attr_e( 'Поиск', 'pickprism' ); ?>">
		<div class="container">
			<?php
			get_template_part(
				'template-parts/search-form',
				null,
				array( 'size' => 'md' )
			);
			?>
		</div>
	</div>

	<!-- Мобильное меню -->
	<nav class="pa-header__mobile-nav" id="pa-mobile-nav" aria-label="<?php esc_attr_e( 'Мобильное меню', 'pickprism' ); ?>" hidden>
		<div class="container">
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => '',
						'depth'          => 1,
						'fallback_cb'    => '__return_empty_string',
					)
				);
				?>
			<?php elseif ( ! empty( $fallback_cats ) ) : ?>
				<ul>
					<?php foreach ( $fallback_cats as $cat ) :
						$cat_link = get_term_link( $cat );
						if ( is_wp_error( $cat_link ) ) {
							continue;
						}
						?>
						<li>
							<a href="<?php echo esc_url( $cat_link ); ?>">
								<?php echo esc_html( $cat->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</nav>

</header>

<script>
// Инлайн-скрипт для хедера: поиск-дроп + мобильный бургер.
// Выполняется сразу — не ждёт DOMContentLoaded (элементы уже в DOM).
(function () {
	var toggle = document.querySelector('[data-pa-search-toggle]');
	var wrap   = document.getElementById('pa-header-search');
	if (toggle && wrap) {
		toggle.addEventListener('click', function () {
			var isOpen = !wrap.hidden;
			wrap.hidden = isOpen;
			toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
			if (!isOpen) {
				var input = wrap.querySelector('input[type="search"]');
				if (input) { input.focus(); }
			}
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !wrap.hidden) {
				wrap.hidden = true;
				toggle.setAttribute('aria-expanded', 'false');
				toggle.focus();
			}
		});
	}

	var burger  = document.querySelector('[data-pa-burger]');
	var mobileNav = document.getElementById('pa-mobile-nav');
	if (burger && mobileNav) {
		burger.addEventListener('click', function () {
			var isOpen = !mobileNav.hidden;
			mobileNav.hidden = isOpen;
			burger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
		});
	}
}());
</script>
