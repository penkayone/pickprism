<?php
/**
 * Pressaff — compact grid (популярное / свежее).
 * 10 постов в квадратных карточках. Используем pickprism_get_popular_posts().
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$popular_posts = pickprism_get_popular_posts( 10 );

if ( empty( $popular_posts ) ) {
	return;
}
?>
<section class="pa-compact" aria-label="<?php esc_attr_e( 'Популярное', 'pickprism' ); ?>">
	<div class="container">
		<div class="pa-section-head">
			<h2 class="pa-section-head__title"><?php esc_html_e( 'Популярное', 'pickprism' ); ?></h2>
			<div class="pa-section-head__line" aria-hidden="true"></div>
		</div>

		<div class="pa-compact__grid">
			<?php foreach ( $popular_posts as $num => $p ) :
				if ( ! $p instanceof WP_Post ) {
					continue;
				}
				$p_link  = get_permalink( $p->ID );
				$p_title = get_the_title( $p->ID );
				$p_thumb = get_the_post_thumbnail_url( $p->ID, 'thumbnail' );
				$rank    = $num + 1;
				?>
				<a
					class="pa-card-sq"
					href="<?php echo esc_url( $p_link ); ?>"
					title="<?php echo esc_attr( $p_title ); ?>"
				>
					<div class="pa-card-sq__media">
						<?php if ( $p_thumb ) : ?>
							<img
								src="<?php echo esc_url( $p_thumb ); ?>"
								alt="<?php echo esc_attr( $p_title ); ?>"
								loading="lazy"
								decoding="async"
								width="150"
								height="150"
							>
						<?php else : ?>
							<div class="pa-card-sq__placeholder" aria-hidden="true">
								<svg width="28" height="28" viewBox="0 0 24 24">
									<path fill="currentColor" d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2Zm-10-7-2.5 3.25L6 12l-3 4h18l-4.5-6-3 4-2.5-3.25Z"/>
								</svg>
							</div>
						<?php endif; ?>
						<span class="pa-card-sq__num" aria-label="<?php echo esc_attr( (string) $rank ); ?>">
							<?php echo esc_html( (string) $rank ); ?>
						</span>
					</div>
					<span class="pa-card-sq__title"><?php echo esc_html( $p_title ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
