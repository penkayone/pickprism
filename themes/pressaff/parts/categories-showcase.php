<?php
/**
 * Pressaff — categories showcase.
 * Ряд из топ-категорий в виде крупных пилюль-ссылок.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$showcase_cats = pickprism_get_top_categories( 12 );

if ( empty( $showcase_cats ) ) {
	return;
}
?>
<section class="pa-cats" aria-label="<?php esc_attr_e( 'Разделы сайта', 'pickprism' ); ?>">
	<div class="container">
		<div class="pa-section-head">
			<h2 class="pa-section-head__title"><?php esc_html_e( 'Разделы', 'pickprism' ); ?></h2>
			<div class="pa-section-head__line" aria-hidden="true"></div>
		</div>

		<div class="pa-cats__grid" role="list">
			<?php foreach ( $showcase_cats as $cat ) :
				if ( ! $cat instanceof WP_Term ) {
					continue;
				}
				$cat_link = get_term_link( $cat );
				if ( is_wp_error( $cat_link ) ) {
					continue;
				}
				?>
				<a
					class="pa-cat-pill"
					href="<?php echo esc_url( $cat_link ); ?>"
					role="listitem"
				>
					<?php echo esc_html( $cat->name ); ?>
					<span class="pa-cat-pill__count"><?php echo esc_html( (string) $cat->count ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
