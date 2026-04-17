<?php
/**
 * Подвал сайта с топ-категориями и меню футера.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$top_cats = pickprism_get_top_categories( 12 );
?>

<footer class="site-footer" role="contentinfo">
	<div class="container site-footer__inner">
		<div class="site-footer__col site-footer__col--brand">
			<a class="site-footer__title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>
			<p class="site-footer__tagline">
				<?php echo esc_html( get_bloginfo( 'description' ) ); ?>
			</p>
		</div>

		<?php if ( ! empty( $top_cats ) ) : ?>
			<div class="site-footer__col site-footer__col--cats">
				<h4 class="site-footer__heading"><?php esc_html_e( 'Категории', 'pickprism' ); ?></h4>
				<ul class="site-footer__list">
					<?php foreach ( $top_cats as $cat ) :
						$link = get_term_link( $cat );
						if ( is_wp_error( $link ) ) {
							continue;
						}
						?>
						<li>
							<a href="<?php echo esc_url( $link ); ?>">
								<?php echo esc_html( $cat->name ); ?>
								<span class="count"><?php echo esc_html( (string) $cat->count ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<div class="site-footer__col site-footer__col--menu">
				<h4 class="site-footer__heading"><?php esc_html_e( 'Навигация', 'pickprism' ); ?></h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'site-footer__menu',
						'depth'          => 1,
					)
				);
				?>
			</div>
		<?php endif; ?>

		<div class="site-footer__col site-footer__col--social">
			<h4 class="site-footer__heading"><?php esc_html_e( 'Соцсети', 'pickprism' ); ?></h4>
			<div class="site-footer__social">
				<a class="btn btn--ghost" href="https://www.reddit.com/" target="_blank" rel="noopener noreferrer">Reddit</a>
				<a class="btn btn--ghost" href="https://t.me/" target="_blank" rel="noopener noreferrer">Telegram</a>
			</div>
		</div>
	</div>

	<div class="site-footer__bottom container">
		<span>
			<?php
			/* translators: %s: год */
			echo esc_html( sprintf( __( '© %s Pickprism. Все права защищены.', 'pickprism' ), gmdate( 'Y' ) ) );
			?>
		</span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
