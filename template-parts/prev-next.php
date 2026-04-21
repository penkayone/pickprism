<?php
/**
 * Prev/Next между статьями.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$prev_post = get_previous_post();
$next_post = get_next_post();

if ( ! $prev_post && ! $next_post ) {
	return;
}
?>
<nav class="pa-prevnext" aria-label="<?php esc_attr_e( 'Между статьями', 'pickprism' ); ?>">
	<?php if ( $prev_post ) : ?>
		<a class="pa-prevnext__item pa-prevnext__item--prev" href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>" rel="prev">
			<span class="pa-prevnext__dir"><?php esc_html_e( '← Предыдущая', 'pickprism' ); ?></span>
			<span class="pa-prevnext__title"><?php echo esc_html( get_the_title( $prev_post ) ); ?></span>
		</a>
	<?php else : ?>
		<span></span>
	<?php endif; ?>

	<?php if ( $next_post ) : ?>
		<a class="pa-prevnext__item pa-prevnext__item--next" href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" rel="next">
			<span class="pa-prevnext__dir"><?php esc_html_e( 'Следующая →', 'pickprism' ); ?></span>
			<span class="pa-prevnext__title"><?php echo esc_html( get_the_title( $next_post ) ); ?></span>
		</a>
	<?php else : ?>
		<span></span>
	<?php endif; ?>
</nav>
