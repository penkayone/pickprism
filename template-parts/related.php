<?php
/**
 * «Читайте дальше» — 3 связанных поста по категории.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$post_id      = get_the_ID();
$primary_term = pickprism_primary_category( $post_id );
if ( ! $primary_term ) {
	return;
}

$q = new WP_Query(
	array(
		'posts_per_page'         => 3,
		'post__not_in'           => array( $post_id ),
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'category__in'           => array( (int) $primary_term->term_id ),
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	)
);

if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="pa-related" aria-labelledby="related-title">
	<div class="pa-sec-head">
		<h2 id="related-title" class="pa-sec-head__title"><?php esc_html_e( 'Читайте дальше', 'pickprism' ); ?></h2>
		<span class="pa-sec-head__line" aria-hidden="true"></span>
		<?php $cat_link = get_term_link( $primary_term ); ?>
		<?php if ( ! is_wp_error( $cat_link ) ) : ?>
			<a class="pa-sec-head__link" href="<?php echo esc_url( $cat_link ); ?>">
				<?php esc_html_e( 'Все статьи →', 'pickprism' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="pa-related__grid pa-related__grid--mag">
		<?php while ( $q->have_posts() ) :
			$q->the_post();
			$r_id    = get_the_ID();
			$r_term  = pickprism_primary_category( $r_id );
			$r_read  = pickprism_reading_time( $r_id );
			$r_hue   = pickprism_cover_hue( $r_id );
			$r_letter = $r_term ? mb_strtoupper( mb_substr( $r_term->name, 0, 1, 'UTF-8' ), 'UTF-8' ) : 'P';
			?>
			<a class="pa-rcard pa-rcard--mag" href="<?php the_permalink(); ?>" style="--hue: <?php echo (int) $r_hue; ?>;">
				<div class="pa-rcard__media">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php
						the_post_thumbnail(
							'pickprism-card',
							array(
								'loading'  => 'lazy',
								'decoding' => 'async',
								'alt'      => the_title_attribute( array( 'echo' => false ) ),
							)
						);
						?>
					<?php else : ?>
						<div class="pa-rcard__placeholder" aria-hidden="true"><?php echo esc_html( $r_letter ); ?></div>
					<?php endif; ?>
					<?php if ( $r_term ) : ?>
						<span class="pa-rcard__badge"><?php echo esc_html( $r_term->name ); ?></span>
					<?php endif; ?>
				</div>
				<div class="pa-rcard__body">
					<h3 class="pa-rcard__title"><?php echo esc_html( get_the_title() ); ?></h3>
				</div>
			</a>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
