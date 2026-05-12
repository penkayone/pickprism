<?php
/**
 * Тёмный footer: brand + 3 колонки-меню + bottom (copyright + правовые ссылки).
 * Контент колонок управляется через ACF (Настройки сайта → вкладка «Футер»)
 * + назначение меню в Внешний вид → Меню → Расположение: «Футер · Колонка N».
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$has_acf = function_exists( 'get_field' );

// Описание под логотипом: ACF → fallback на tagline сайта.
$footer_description = $has_acf ? trim( (string) get_field( 'footer_description', 'option' ) ) : '';
if ( $footer_description === '' ) {
	$footer_description = (string) get_bloginfo( 'description' );
}

// Заголовки колонок: ACF → fallback на исторические названия.
$footer_col_titles = array(
	1 => $has_acf ? trim( (string) get_field( 'footer_col1_title', 'option' ) ) : '',
	2 => $has_acf ? trim( (string) get_field( 'footer_col2_title', 'option' ) ) : '',
	3 => $has_acf ? trim( (string) get_field( 'footer_col3_title', 'option' ) ) : '',
);
$footer_col_defaults = array(
	1 => __( 'Категории', 'pickprism' ),
	2 => __( 'Проект', 'pickprism' ),
	3 => __( 'Сервисы', 'pickprism' ),
);
foreach ( $footer_col_titles as $i => $t ) {
	if ( $t === '' ) {
		$footer_col_titles[ $i ] = $footer_col_defaults[ $i ];
	}
}

// Copyright: ACF → fallback. Плейсхолдеры {year} и {site}.
$footer_copyright_raw = $has_acf ? trim( (string) get_field( 'footer_copyright', 'option' ) ) : '';
if ( $footer_copyright_raw === '' ) {
	/* translators: copyright fallback. {year} → текущий год, {site} → название сайта. */
	$footer_copyright_raw = __( '© {year} {site}. Все права защищены.', 'pickprism' );
}
$footer_copyright = strtr(
	$footer_copyright_raw,
	array(
		'{year}' => gmdate( 'Y' ),
		'{site}' => get_bloginfo( 'name' ),
	)
);
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
			<?php if ( $footer_description !== '' ) : ?>
				<p><?php echo esc_html( $footer_description ); ?></p>
			<?php endif; ?>
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
			<?php for ( $i = 1; $i <= 3; $i++ ) :
				$location = 'footer-' . $i;
				if ( ! has_nav_menu( $location ) ) {
					continue;
				}
				?>
				<div>
					<div class="pa-footer__title"><?php echo esc_html( $footer_col_titles[ $i ] ); ?></div>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $location,
							'container'      => false,
							'items_wrap'     => '<ul>%3$s</ul>',
							'depth'          => 1,
							'fallback_cb'    => '__return_empty_string',
						)
					);
					?>
				</div>
			<?php endfor; ?>
		</div>
	</div>

	<div class="pa-footer__bottom">
		<span><?php echo esc_html( $footer_copyright ); ?></span>
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

<?php get_template_part( 'template-parts/scroll-top' ); ?>
<?php get_template_part( 'template-parts/mobile-drawer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
