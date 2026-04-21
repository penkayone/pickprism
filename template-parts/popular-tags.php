<?php
/**
 * Секция «Популярные теги» внизу главной.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$tags = pickprism_get_popular_tags( 15 );
if ( empty( $tags ) ) {
	return;
}
?>
<section class="ha-tags" aria-labelledby="home-tags-title">
	<div class="ha-sec-head">
		<h2 id="home-tags-title" class="ha-sec-head__title"><?php esc_html_e( 'Популярные теги', 'pickprism' ); ?></h2>
		<span class="ha-sec-head__line" aria-hidden="true"></span>
	</div>
	<div class="ha-tags__list">
		<?php foreach ( $tags as $tag ) :
			$link = get_term_link( $tag );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			?>
			<a class="ha-tag" href="<?php echo esc_url( $link ); ?>">
				<span class="ha-tag__hash">#</span>
				<?php echo esc_html( $tag->name ); ?>
				<span class="ha-tag__n"><?php echo esc_html( (string) $tag->count ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
