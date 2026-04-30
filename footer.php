<?php
/**
 * Тёмный footer: brand + 3 колонки (Рубрики / Проект / Сервисы) + bottom.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$top_cats = pickprism_get_top_categories( 5 );

// Fallback-секции, если меню не настроены.
$footer_menu_project = array(
	array(
		'label' => __( 'О нас', 'pickprism' ),
		'url'   => '#',
	),
	array(
		'label' => __( 'Редакция', 'pickprism' ),
		'url'   => '#',
	),
	array(
		'label' => __( 'Контакты', 'pickprism' ),
		'url'   => '#',
	),
);

$footer_menu_services = array();
$pickprism_tg_url = pickprism_social_url( 'telegram' );
if ( $pickprism_tg_url !== '' ) {
	$footer_menu_services[] = array(
		'label' => __( 'Telegram', 'pickprism' ),
		'url'   => $pickprism_tg_url,
	);
}
?>

<?php if ( is_singular( 'post' ) ) : ?>
	<?php get_template_part( 'template-parts/mobile-share-bar' ); ?>
<?php endif; ?>

<footer class="pa-footer" role="contentinfo">
	<div class="pa-footer__top">
		<div class="pa-footer__brand">
			<a class="pa-logo pa-logo--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php
				$first_letter = strtoupper( mb_substr( get_bloginfo( 'name' ), 0, 1, 'UTF-8' ) );
				if ( $first_letter === '' ) {
					$first_letter = 'P';
				}
				?>
				<span class="pa-logo__mark" aria-hidden="true"><?php echo esc_html( $first_letter ); ?></span>
				<span class="pa-logo__text"><?php bloginfo( 'name' ); ?></span>
			</a>
			<p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			<?php
			$pickprism_socials = array_filter(
				array(
					'telegram' => pickprism_social_url( 'telegram' ),
					'reddit'   => pickprism_social_url( 'reddit' ),
				)
			);
			if ( ! empty( $pickprism_socials ) ) :
				?>
				<div class="pa-footer__socials">
					<?php if ( ! empty( $pickprism_socials['telegram'] ) ) : ?>
						<a href="<?php echo esc_url( $pickprism_socials['telegram'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M9.8 15.7 9.6 19c.3 0 .5-.1.6-.3l1.7-1.6 3.4 2.5c.6.3 1 .2 1.2-.6l2.2-10.3c.2-1-.3-1.4-1-1.1L4.8 12.4c-1 .4-1 1-.2 1.2l3.3 1 7.7-4.9c.4-.2.7-.1.4.1z"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $pickprism_socials['reddit'] ) ) : ?>
						<a href="<?php echo esc_url( $pickprism_socials['reddit'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Reddit">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M22 12a2.5 2.5 0 0 0-4.2-1.8c-1.6-1-3.6-1.6-5.7-1.7l1-4.3 3 .7a1.8 1.8 0 1 0 .2-1.2l-3.6-.8a.6.6 0 0 0-.7.5l-1.1 4.8c-2.2.1-4.2.7-5.8 1.7a2.5 2.5 0 1 0-3 3.8c-.1.4-.1.8-.1 1.2 0 3.7 4.3 6.7 9.6 6.7s9.6-3 9.6-6.7c0-.4 0-.8-.1-1.2A2.5 2.5 0 0 0 22 12Z"/></svg>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="pa-footer__cols">
			<?php if ( ! empty( $top_cats ) ) : ?>
				<div>
					<div class="pa-footer__title"><?php esc_html_e( 'Рубрики', 'pickprism' ); ?></div>
					<ul>
						<?php foreach ( $top_cats as $cat ) :
							$link = get_term_link( $cat );
							if ( is_wp_error( $link ) ) {
								continue;
							}
							?>
							<li>
								<a href="<?php echo esc_url( $link ); ?>">
									<?php echo esc_html( $cat->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<div>
					<div class="pa-footer__title"><?php esc_html_e( 'Проект', 'pickprism' ); ?></div>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'items_wrap'     => '<ul>%3$s</ul>',
							'depth'          => 1,
						)
					);
					?>
				</div>
			<?php else : ?>
				<div>
					<div class="pa-footer__title"><?php esc_html_e( 'Проект', 'pickprism' ); ?></div>
					<ul>
						<?php foreach ( $footer_menu_project as $it ) : ?>
							<li><a href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $it['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div>
				<div class="pa-footer__title"><?php esc_html_e( 'Сервисы', 'pickprism' ); ?></div>
				<ul>
					<?php foreach ( $footer_menu_services as $it ) : ?>
						<li><a href="<?php echo esc_url( $it['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $it['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>

	<div class="pa-footer__bottom">
		<span>
			<?php
			/* translators: %1$s: year %2$s: site name */
			echo esc_html( sprintf( __( '© %1$s %2$s. Все права защищены.', 'pickprism' ), gmdate( 'Y' ), get_bloginfo( 'name' ) ) );
			?>
		</span>
		<div class="pa-footer__legal">
			<?php if ( get_privacy_policy_url() ) : ?>
				<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Политика конфиденциальности', 'pickprism' ); ?></a>
			<?php endif; ?>
			<?php
			$pickprism_terms_url = (string) get_theme_mod( 'pickprism_terms_url', '' );
			if ( $pickprism_terms_url !== '' ) :
				?>
				<a href="<?php echo esc_url( $pickprism_terms_url ); ?>"><?php esc_html_e( 'Пользовательское соглашение', 'pickprism' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
