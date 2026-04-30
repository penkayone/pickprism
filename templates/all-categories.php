<?php
/**
 * Шаблон страницы /categories/ — список всех непустых категорий блога.
 *
 * Подключается через template_include в inc/categories-page.php при
 * наличии query-var pickprism_categories.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$pickprism_terms = get_transient( 'pickprism_all_categories' );
if ( ! is_array( $pickprism_terms ) ) {
	$pickprism_terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $pickprism_terms ) ) {
		$pickprism_terms = array();
	}

	set_transient( 'pickprism_all_categories', $pickprism_terms, PICKPRISM_CACHE_TTL );
}

get_header();
?>
<main id="primary" class="pa-allcats-main">
	<div class="ha-container">
		<header class="pa-allcats__head">
			<h1 class="pa-allcats__title"><?php esc_html_e( 'Все категории', 'pickprism' ); ?></h1>
			<p class="pa-allcats__desc">
				<?php esc_html_e( 'Темы блога — выберите интересную и читайте материалы по ней.', 'pickprism' ); ?>
			</p>
		</header>

		<?php if ( ! empty( $pickprism_terms ) ) : ?>
			<ul class="pa-allcats__grid">
				<?php
				foreach ( $pickprism_terms as $pickprism_term ) :
					if ( ! $pickprism_term instanceof WP_Term ) {
						continue;
					}

					$pickprism_link = get_term_link( $pickprism_term );
					if ( is_wp_error( $pickprism_link ) ) {
						continue;
					}

					$pickprism_hue    = pickprism_term_hue( $pickprism_term );
					$pickprism_letter = mb_strtoupper( mb_substr( $pickprism_term->name, 0, 1, 'UTF-8' ), 'UTF-8' );
					$pickprism_count = (int) $pickprism_term->count;
					$pickprism_mod10 = $pickprism_count % 10;
					$pickprism_mod100 = $pickprism_count % 100;
					if ( $pickprism_mod10 === 1 && $pickprism_mod100 !== 11 ) {
						$pickprism_tpl = __( '%d статья', 'pickprism' );
					} elseif ( $pickprism_mod10 >= 2 && $pickprism_mod10 <= 4 && ( $pickprism_mod100 < 12 || $pickprism_mod100 > 14 ) ) {
						$pickprism_tpl = __( '%d статьи', 'pickprism' );
					} else {
						$pickprism_tpl = __( '%d статей', 'pickprism' );
					}
					/* translators: %d: number of posts in category. */
					$pickprism_label = sprintf( $pickprism_tpl, $pickprism_count );
					?>
					<li class="pa-allcats__item">
						<a
							class="pa-allcats__card"
							href="<?php echo esc_url( $pickprism_link ); ?>"
							style="--hue: <?php echo (int) $pickprism_hue; ?>;"
						>
							<span class="pa-allcats__icon" aria-hidden="true">
								<span class="pa-allcats__letter"><?php echo esc_html( $pickprism_letter ); ?></span>
							</span>
							<span class="pa-allcats__body">
								<span class="pa-allcats__name"><?php echo esc_html( $pickprism_term->name ); ?></span>
								<span class="pa-allcats__count"><?php echo esc_html( $pickprism_label ); ?></span>
								<?php if ( ! empty( $pickprism_term->description ) ) : ?>
									<span class="pa-allcats__excerpt">
										<?php echo esc_html( wp_trim_words( $pickprism_term->description, 14, '…' ) ); ?>
									</span>
								<?php endif; ?>
							</span>
							<span class="pa-allcats__arrow" aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<div class="pa-allcats__empty">
				<p class="empty-state__text">
					<?php esc_html_e( 'Пока нет категорий с публикациями.', 'pickprism' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
