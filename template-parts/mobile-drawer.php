<?php
/**
 * Мобильный drawer (бургер-меню).
 * Виден только на ≤bp-lg, открывается кнопкой .pa-burger в шапке.
 * Содержит: поиск, primary меню (с тем же fallback на категории, что и .pa-nav)
 * и CTA-ссылку на Telegram.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$pickprism_drawer_tg = pickprism_social_url( 'telegram' );
?>
<div
	class="pa-drawer"
	id="pa-drawer"
	data-drawer
	role="dialog"
	aria-modal="true"
	aria-hidden="true"
	aria-label="<?php esc_attr_e( 'Меню', 'pickprism' ); ?>"
	hidden
>
	<div class="pa-drawer__backdrop" data-drawer-close aria-hidden="true"></div>
	<div class="pa-drawer__panel" role="document">
		<div class="pa-drawer__head">
			<?php get_template_part( 'template-parts/site-logo', null, array( 'tabindex' => '-1' ) ); ?>
			<button
				type="button"
				class="pa-drawer__close"
				data-drawer-close
				aria-label="<?php esc_attr_e( 'Закрыть меню', 'pickprism' ); ?>"
			>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false">
					<path d="M6 6l12 12M18 6 6 18"/>
				</svg>
			</button>
		</div>

		<form
			class="pa-drawer__search"
			role="search"
			action="<?php echo esc_url( home_url( '/' ) ); ?>"
			method="get"
		>
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
				<circle cx="11" cy="11" r="7"/>
				<path d="m21 21-4.3-4.3"/>
			</svg>
			<input
				type="search"
				name="s"
				placeholder="<?php esc_attr_e( 'Поиск по статьям…', 'pickprism' ); ?>"
				aria-label="<?php esc_attr_e( 'Поиск', 'pickprism' ); ?>"
				autocomplete="off"
			/>
		</form>

		<nav class="pa-drawer__nav" aria-label="<?php esc_attr_e( 'Основные ссылки', 'pickprism' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'pa-drawer__list',
						'depth'          => 1,
						'fallback_cb'    => '__return_empty_string',
					)
				);
			} else {
				$fallback_cats = pickprism_get_top_categories( 6 );
				if ( ! empty( $fallback_cats ) ) {
					echo '<ul class="pa-drawer__list">';
					foreach ( $fallback_cats as $fc ) {
						$link = get_term_link( $fc );
						if ( ! is_wp_error( $link ) ) {
							printf(
								'<li><a href="%s">%s</a></li>',
								esc_url( $link ),
								esc_html( $fc->name )
							);
						}
					}
					echo '</ul>';
				}
			}
			?>
		</nav>

		<?php if ( $pickprism_drawer_tg !== '' ) : ?>
			<a
				class="pa-btn pa-btn--accent pa-drawer__cta"
				href="<?php echo esc_url( $pickprism_drawer_tg ); ?>"
				target="_blank"
				rel="noopener noreferrer"
			>
				<?php esc_html_e( 'Telegram', 'pickprism' ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
