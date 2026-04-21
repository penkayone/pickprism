<?php
/**
 * Карточка статьи в ленте (редизайн): cover → meta (date + read-time + «Новое») → title → excerpt.
 * Cover либо featured image, либо hue-градиент + первая буква категории.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$post_id      = get_the_ID();
$primary_term = pickprism_primary_category( $post_id );
$hue          = pickprism_cover_hue( $post_id );
$category_label = $primary_term ? $primary_term->name : '';
$is_new       = pickprism_is_new( $post_id );
$read_time    = pickprism_reading_time( $post_id );
?>
<a
	id="post-<?php the_ID(); ?>"
	class="ha-card reveal"
	href="<?php the_permalink(); ?>"
	data-post-id="<?php echo esc_attr( (string) $post_id ); ?>"
>
	<?php pickprism_render_cover( $post_id, 'md' ); ?>

	<div class="ha-card__body">
		<div class="ha-card__meta">
			<?php if ( $is_new ) : ?>
				<span class="ha-card__new"><?php esc_html_e( 'Новое', 'pickprism' ); ?></span>
			<?php endif; ?>
			<span class="ha-card__date">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<?php echo esc_html( get_the_date( 'j F', $post_id ) ); ?>
				</time>
			</span>
			<span class="ha-card__dot" aria-hidden="true">·</span>
			<span class="ha-card__read">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
				<?php
				/* translators: %d: минуты чтения */
				echo esc_html( sprintf( __( '%d мин', 'pickprism' ), $read_time ) );
				?>
			</span>
		</div>

		<h3 class="ha-card__title"><?php echo esc_html( get_the_title() ); ?></h3>

		<?php
		$excerpt = get_the_excerpt();
		if ( $excerpt ) :
			?>
			<p class="ha-card__excerpt">
				<?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 24, '…' ) ); ?>
			</p>
		<?php endif; ?>
	</div>
</a>
