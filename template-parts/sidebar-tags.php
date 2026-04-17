<?php
/**
 * Sidebar: облако популярных тегов.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$tags = pickprism_get_popular_tags( 20 );
if ( empty( $tags ) ) {
	return;
}
?>
<section class="sidebar__block sidebar__tags">
	<h3 class="sidebar__title"><?php esc_html_e( 'Теги', 'pickprism' ); ?></h3>
	<ul class="chips__list chips__list--wrap">
		<?php foreach ( $tags as $term ) :
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			?>
			<li>
				<a class="chip chip--tag chip--sm" href="<?php echo esc_url( $link ); ?>">
					#<?php echo esc_html( $term->name ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
