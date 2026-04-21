<?php
/**
 * Pressaff — тёмный многоколоночный футер.
 * Подключается напрямую из themes/pressaff/front-page.php.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$footer_cats = pickprism_get_top_categories( 8 );
$site_name   = get_bloginfo( 'name' );
$site_desc   = get_bloginfo( 'description' );
?>
<footer class="pa-footer" role="contentinfo">

	<!-- SVG-волна перехода (белый/серый фон → тёмный футер) -->
	<div class="pa-footer__wave" aria-hidden="true">
		<svg viewBox="0 0 1200 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M0 0Q300 60 600 30T1200 0V60H0Z" fill="#10131a"/>
		</svg>
	</div>

	<!-- Декоративные звёздочки -->
	<div class="pa-footer__decor" aria-hidden="true">
		<svg class="pa-footer__decor-star" viewBox="0 0 24 24"><path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="currentColor"/></svg>
		<svg class="pa-footer__decor-star" viewBox="0 0 24 24"><path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="currentColor"/></svg>
		<svg class="pa-footer__decor-star" viewBox="0 0 24 24"><path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="currentColor"/></svg>
		<svg class="pa-footer__decor-star" viewBox="0 0 24 24"><path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="currentColor"/></svg>
	</div>

	<div class="container">

		<!-- Основной контент -->
		<div class="pa-footer__inner">

			<!-- Бренд-колонка -->
			<div class="pa-footer__brand">
				<a class="pa-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<span class="pa-footer__logo-mark" aria-hidden="true">p</span>
					<span><?php echo esc_html( $site_name ); ?></span>
				</a>

				<?php if ( $site_desc ) : ?>
					<p class="pa-footer__desc"><?php echo esc_html( $site_desc ); ?></p>
				<?php endif; ?>

				<div class="pa-footer__social" aria-label="<?php esc_attr_e( 'Соцсети', 'pickprism' ); ?>">
					<a
						class="pa-footer__social-link"
						href="https://t.me/"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php esc_attr_e( 'Telegram', 'pickprism' ); ?>"
					>
						<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
							<path fill="currentColor" d="M9.8 15.5 9.6 19c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.7.4 1.3.2 1.5-.7L21 5.6c.3-1.2-.4-1.7-1.2-1.4L3.6 10.5c-1.2.5-1.2 1.1-.2 1.4l4.1 1.3 9.5-6c.5-.3.9-.1.5.3l-7.7 7Z"/>
						</svg>
					</a>
					<a
						class="pa-footer__social-link"
						href="https://www.reddit.com/"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php esc_attr_e( 'Reddit', 'pickprism' ); ?>"
					>
						<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
							<path fill="currentColor" d="M22 12a2.5 2.5 0 0 0-4.2-1.8c-1.6-1-3.6-1.6-5.7-1.7l1-4.3 3 .7a1.8 1.8 0 1 0 .2-1.2l-3.6-.8a.6.6 0 0 0-.7.5l-1.1 4.8c-2.2.1-4.2.7-5.8 1.7a2.5 2.5 0 1 0-3 3.8c-.1.4-.1.8-.1 1.2 0 3.7 4.3 6.7 9.6 6.7s9.6-3 9.6-6.7c0-.4 0-.8-.1-1.2A2.5 2.5 0 0 0 22 12Zm-14.5 2a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm8.6 3.8c-1.1 1-3 1.1-3.8 1.1-.8 0-2.7-.1-3.8-1.1a.5.5 0 1 1 .7-.7c.7.7 2.2.9 3.1.9.9 0 2.4-.2 3.1-.9a.5.5 0 1 1 .7.7Zm-.1-2.3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Z"/>
						</svg>
					</a>
				</div>
			</div>

			<!-- Категории -->
			<?php if ( ! empty( $footer_cats ) ) : ?>
				<div class="pa-footer__col">
					<h3 class="pa-footer__col-title"><?php esc_html_e( 'Категории', 'pickprism' ); ?></h3>
					<ul class="pa-footer__list">
						<?php foreach ( array_slice( $footer_cats, 0, 8 ) as $cat ) :
							if ( ! $cat instanceof WP_Term ) {
								continue;
							}
							$cat_link = get_term_link( $cat );
							if ( is_wp_error( $cat_link ) ) {
								continue;
							}
							?>
							<li class="pa-footer__list-item">
								<a href="<?php echo esc_url( $cat_link ); ?>">
									<?php echo esc_html( $cat->name ); ?>
									<span class="pa-footer__list-count"><?php echo esc_html( (string) $cat->count ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<!-- Навигация -->
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<div class="pa-footer__col">
					<h3 class="pa-footer__col-title"><?php esc_html_e( 'Навигация', 'pickprism' ); ?></h3>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'items_wrap'     => '<ul class="pa-footer__list">%3$s</ul>',
							'depth'          => 1,
							'fallback_cb'    => '__return_empty_string',
							'walker'         => null,
							'echo'           => true,
						)
					);
					?>
				</div>
			<?php else : ?>
				<!-- Fallback-навигация -->
				<div class="pa-footer__col">
					<h3 class="pa-footer__col-title"><?php esc_html_e( 'Навигация', 'pickprism' ); ?></h3>
					<ul class="pa-footer__list">
						<li class="pa-footer__list-item">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'pickprism' ); ?></a>
						</li>
						<?php if ( get_option( 'page_for_posts' ) ) : ?>
							<li class="pa-footer__list-item">
								<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Блог', 'pickprism' ); ?></a>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>

			<!-- Контакты -->
			<div class="pa-footer__col">
				<h3 class="pa-footer__col-title"><?php esc_html_e( 'Контакты', 'pickprism' ); ?></h3>
				<ul class="pa-footer__list">
					<li class="pa-footer__list-item">
						<a href="https://t.me/" target="_blank" rel="noopener noreferrer">Telegram</a>
					</li>
					<li class="pa-footer__list-item">
						<a href="https://www.reddit.com/" target="_blank" rel="noopener noreferrer">Reddit</a>
					</li>
					<?php
					// Ссылки на политики — из WP Pages (ищем по slug).
					$privacy_page = get_page_by_path( 'privacy-policy' );
					if ( $privacy_page ) :
						?>
						<li class="pa-footer__list-item">
							<a href="<?php echo esc_url( get_permalink( $privacy_page ) ); ?>">
								<?php esc_html_e( 'Политика конфиденциальности', 'pickprism' ); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>

		</div>

		<!-- Нижний ряд: копирайт + политики -->
		<div class="pa-footer__bottom">
			<span class="pa-footer__copy">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: год, 2: название сайта */
						__( '© %1$s %2$s. Все права защищены.', 'pickprism' ),
						gmdate( 'Y' ),
						$site_name
					)
				);
				?>
			</span>

			<div class="pa-footer__links">
				<?php if ( $privacy_page ?? null ) : ?>
					<a href="<?php echo esc_url( get_permalink( $privacy_page ) ); ?>">
						<?php esc_html_e( 'Конфиденциальность', 'pickprism' ); ?>
					</a>
				<?php endif; ?>
				<?php
				$terms_page = get_page_by_path( 'terms' );
				if ( $terms_page ) :
					?>
					<a href="<?php echo esc_url( get_permalink( $terms_page ) ); ?>">
						<?php esc_html_e( 'Условия', 'pickprism' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
