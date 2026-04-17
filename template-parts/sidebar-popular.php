<?php
/**
 * Sidebar: популярные посты.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$popular = pickprism_get_popular_posts( 5 );
if ( empty( $popular ) ) {
	return;
}
?>
<section class="sidebar__block sidebar__popular">
	<h3 class="sidebar__title"><?php esc_html_e( 'Популярное', 'pickprism' ); ?></h3>
	<ol class="sidebar__popular-list">
		<?php foreach ( $popular as $p ) : ?>
			<li class="sidebar__popular-item">
				<?php if ( has_post_thumbnail( $p ) ) : ?>
					<a class="sidebar__popular-thumb" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
						<?php
						echo get_the_post_thumbnail( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							$p,
							'thumbnail',
							array(
								'loading'  => 'lazy',
								'decoding' => 'async',
								'alt'      => esc_attr( get_the_title( $p ) ),
							)
						);
						?>
					</a>
				<?php endif; ?>
				<div class="sidebar__popular-body">
					<a class="sidebar__popular-title" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
						<?php echo esc_html( get_the_title( $p ) ); ?>
					</a>
					<span class="sidebar__popular-meta">
						<?php echo esc_html( get_the_date( '', $p ) ); ?>
					</span>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
