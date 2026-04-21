<?php
/**
 * Pressaff — featured статья + сетка обычных карточек.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

// Получаем 1 sticky пост для featured.
$sticky_posts = pickprism_get_sticky_posts( 1 );
$featured     = ! empty( $sticky_posts ) ? $sticky_posts[0] : null;
$featured_id  = $featured ? $featured->ID : 0;

// 8 обычных постов для сетки, исключая featured.
$exclude = $featured_id ? array( $featured_id ) : array();

$grid_query = new WP_Query(
	array(
		'posts_per_page'      => 8,
		'post_status'         => 'publish',
		'post_type'           => 'post',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'post__not_in'        => $exclude,
	)
);

if ( ! $featured && ! $grid_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="pa-featured" aria-label="<?php esc_attr_e( 'Избранное и статьи', 'pickprism' ); ?>">
	<div class="container">
		<div class="pa-section-head">
			<h2 class="pa-section-head__title"><?php esc_html_e( 'Читайте сейчас', 'pickprism' ); ?></h2>
			<div class="pa-section-head__line" aria-hidden="true"></div>
		</div>

		<div class="pa-featured__layout">

			<?php if ( $featured ) : ?>
				<!-- Большая featured-карточка -->
				<?php
				setup_postdata( $featured );
				$feat_thumb    = get_the_post_thumbnail_url( $featured->ID, 'large' );
				$feat_link     = get_permalink( $featured->ID );
				$feat_title    = get_the_title( $featured->ID );
				$feat_excerpt  = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $featured->ID ) ), 35, '…' );
				$feat_date     = get_the_date( '', $featured->ID );
				$feat_date_iso = get_the_date( 'c', $featured->ID );
				$feat_author   = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $featured->ID ) );

				$feat_cats = get_the_terms( $featured->ID, 'category' );
				$feat_cat  = is_array( $feat_cats ) ? reset( $feat_cats ) : null;
				wp_reset_postdata();
				?>
				<article class="pa-card-featured reveal">
					<?php if ( $feat_thumb ) : ?>
						<a class="pa-card-featured__media" href="<?php echo esc_url( $feat_link ); ?>" tabindex="-1" aria-hidden="true">
							<img
								src="<?php echo esc_url( $feat_thumb ); ?>"
								alt="<?php echo esc_attr( $feat_title ); ?>"
								loading="eager"
								decoding="async"
							>
						</a>
					<?php endif; ?>

					<div class="pa-card-featured__body">
						<span class="pa-card-featured__badge">
							<?php
							if ( $feat_cat && ! is_wp_error( $feat_cat ) ) {
								echo esc_html( $feat_cat->name );
							} else {
								esc_html_e( 'Главное', 'pickprism' );
							}
							?>
						</span>

						<h2 class="pa-card-featured__title">
							<a href="<?php echo esc_url( $feat_link ); ?>" rel="bookmark">
								<?php echo esc_html( $feat_title ); ?>
							</a>
						</h2>

						<?php if ( $feat_excerpt ) : ?>
							<p class="pa-card-featured__excerpt"><?php echo esc_html( $feat_excerpt ); ?></p>
						<?php endif; ?>

						<div class="pa-card-featured__footer">
							<div class="pa-card-featured__meta">
								<?php if ( $feat_author ) : ?>
									<span><?php echo esc_html( $feat_author ); ?></span>
									<span aria-hidden="true">·</span>
								<?php endif; ?>
								<time datetime="<?php echo esc_attr( $feat_date_iso ); ?>">
									<?php echo esc_html( $feat_date ); ?>
								</time>
							</div>

							<a class="pa-card-featured__cta" href="<?php echo esc_url( $feat_link ); ?>">
								<?php esc_html_e( 'Читать', 'pickprism' ); ?>
								<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
									<path fill="currentColor" d="m5 12 14 0M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
								</svg>
							</a>
						</div>
					</div>
				</article>
			<?php endif; ?>

			<!-- Сетка обычных карточек -->
			<?php if ( $grid_query->have_posts() ) : ?>
				<div class="pa-card-grid">
					<?php while ( $grid_query->have_posts() ) : $grid_query->the_post(); ?>
						<?php
						$p_id      = get_the_ID();
						$p_link    = get_permalink();
						$p_title   = get_the_title();
						$p_date    = get_the_date();
						$p_date_iso = get_the_date( 'c' );
						$p_author  = get_the_author();
						$p_thumb   = get_the_post_thumbnail_url( $p_id, 'pickprism-card' );
						$p_thumb_full = get_the_post_thumbnail_url( $p_id, 'medium' );

						$p_cats    = get_the_terms( $p_id, 'category' );
						$p_cat     = is_array( $p_cats ) ? reset( $p_cats ) : null;
						$p_cat_link = ( $p_cat && ! is_wp_error( $p_cat ) ) ? get_term_link( $p_cat ) : null;

						$author_initial = $p_author ? mb_strtoupper( mb_substr( $p_author, 0, 1 ) ) : 'A';
						?>
						<article class="pa-card reveal">
							<?php if ( $p_thumb ) : ?>
								<a class="pa-card__media" href="<?php echo esc_url( $p_link ); ?>" tabindex="-1" aria-hidden="true">
									<img
										src="<?php echo esc_url( $p_thumb ); ?>"
										alt="<?php echo esc_attr( $p_title ); ?>"
										loading="lazy"
										decoding="async"
									>
								</a>
							<?php else : ?>
								<div class="pa-card__media">
									<div class="pa-card__media-placeholder" aria-hidden="true">
										<svg width="32" height="32" viewBox="0 0 24 24">
											<path fill="currentColor" d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2Zm-10-7-2.5 3.25L6 12l-3 4h18l-4.5-6-3 4-2.5-3.25Z"/>
										</svg>
									</div>
								</div>
							<?php endif; ?>

							<div class="pa-card__body">
								<?php if ( $p_cat && ! is_wp_error( $p_cat ) && $p_cat_link ) : ?>
									<a class="pa-card__badge" href="<?php echo esc_url( $p_cat_link ); ?>">
										<?php echo esc_html( $p_cat->name ); ?>
									</a>
								<?php endif; ?>

								<h3 class="pa-card__title">
									<a href="<?php echo esc_url( $p_link ); ?>" rel="bookmark">
										<?php echo esc_html( $p_title ); ?>
									</a>
								</h3>

								<div class="pa-card__meta">
									<span class="pa-card__author-avatar" aria-hidden="true">
										<?php echo esc_html( $author_initial ); ?>
									</span>
									<span><?php echo esc_html( $p_author ); ?></span>
									<span aria-hidden="true">·</span>
									<time datetime="<?php echo esc_attr( $p_date_iso ); ?>">
										<?php echo esc_html( $p_date ); ?>
									</time>
								</div>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
