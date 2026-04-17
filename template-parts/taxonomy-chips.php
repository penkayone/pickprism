<?php
/**
 * Чипсы категорий и тегов.
 *
 * @package Pickprism
 *
 * @var array{categories?:WP_Term[], tags?:WP_Term[]} $args
 */

defined( 'ABSPATH' ) || exit;

$categories = isset( $args['categories'] ) && is_array( $args['categories'] ) ? $args['categories'] : array();
$tags       = isset( $args['tags'] ) && is_array( $args['tags'] ) ? $args['tags'] : array();

if ( empty( $categories ) && empty( $tags ) ) {
	return;
}
?>
<div class="chips">
	<?php if ( ! empty( $categories ) ) : ?>
		<div class="chips__group">
			<span class="chips__label"><?php esc_html_e( 'Категории:', 'pickprism' ); ?></span>
			<ul class="chips__list">
				<?php foreach ( $categories as $term ) :
					if ( ! $term instanceof WP_Term ) {
						continue;
					}
					$link = get_term_link( $term );
					if ( is_wp_error( $link ) ) {
						continue;
					}
					?>
					<li>
						<a class="chip chip--category" href="<?php echo esc_url( $link ); ?>">
							<?php echo esc_html( $term->name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $tags ) ) : ?>
		<div class="chips__group">
			<span class="chips__label"><?php esc_html_e( 'Теги:', 'pickprism' ); ?></span>
			<ul class="chips__list">
				<?php foreach ( $tags as $term ) :
					if ( ! $term instanceof WP_Term ) {
						continue;
					}
					$link = get_term_link( $term );
					if ( is_wp_error( $link ) ) {
						continue;
					}
					?>
					<li>
						<a class="chip chip--tag" href="<?php echo esc_url( $link ); ?>">
							#<?php echo esc_html( $term->name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
