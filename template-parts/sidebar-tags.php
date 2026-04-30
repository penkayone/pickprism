<?php
/**
 * Sidebar-блок «Популярные теги» — pill-теги.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$tags = pickprism_get_popular_tags( 12 );
if ( empty( $tags ) ) {
	return;
}
?>
<div class="ha-side__block">
	<div class="ha-side__head">
		<div class="ha-side__title"><?php esc_html_e( 'Популярные теги', 'pickprism' ); ?></div>
	</div>
	<div class="ha-side__tags">
		<?php foreach ( $tags as $tag ) :
			$link = get_term_link( $tag );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			?>
			<a class="ha-side__tagitem" href="<?php echo esc_url( $link ); ?>">
				<span>#</span><?php echo esc_html( $tag->name ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</div>
