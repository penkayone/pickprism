<?php
/**
 * Pressaff — горизонтальная лента последних статей (latest row).
 * 5 последних постов (не sticky), компактные карточки.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$latest_query = new WP_Query(
	array(
		'posts_per_page'      => 5,
		'post_status'         => 'publish',
		'post_type'           => 'post',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	)
);

if ( ! $latest_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="pa-latest" aria-label="<?php esc_attr_e( 'Свежие статьи', 'pickprism' ); ?>">
	<div class="container">
		<div class="pa-section-head">
			<h2 class="pa-section-head__title"><?php esc_html_e( 'Свежее', 'pickprism' ); ?></h2>
			<div class="pa-section-head__line" aria-hidden="true"></div>
			<a class="pa-section-head__link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Все статьи →', 'pickprism' ); ?>
			</a>
		</div>

		<div class="pa-latest__scroll" role="list">
			<?php while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>
				<?php
				$thumb_url  = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
				$permalink  = get_permalink();
				$post_title = get_the_title();
				?>
				<a
					class="pa-card-mini"
					href="<?php echo esc_url( $permalink ); ?>"
					role="listitem"
				>
					<div class="pa-card-mini__thumb" aria-hidden="true">
						<?php if ( $thumb_url ) : ?>
							<img
								src="<?php echo esc_url( $thumb_url ); ?>"
								alt=""
								loading="lazy"
								decoding="async"
								width="72"
								height="72"
							>
						<?php else : ?>
							<div class="pa-card-mini__thumb-placeholder">
								<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
									<path fill="currentColor" d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2Zm-10-7-2.5 3.25L6 12l-3 4h18l-4.5-6-3 4-2.5-3.25Z"/>
								</svg>
							</div>
						<?php endif; ?>
					</div>

					<div class="pa-card-mini__body">
						<span class="pa-card-mini__title"><?php echo esc_html( $post_title ); ?></span>
						<time class="pa-card-mini__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( get_the_date() ); ?>
						</time>
					</div>
				</a>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</div>
</section>
