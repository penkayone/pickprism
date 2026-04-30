<?php
/**
 * Sidebar-блок «Читают сейчас» — 5 популярных постов с номерами.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$posts = pickprism_get_popular_posts( 5 );
if ( empty( $posts ) ) {
	return;
}
?>
<div class="ha-side__block">
	<div class="ha-side__head">
		<span class="ha-side__pulse" aria-hidden="true"></span>
		<div class="ha-side__title"><?php esc_html_e( 'Читают сейчас', 'pickprism' ); ?></div>
	</div>
	<ul class="ha-side__trending">
		<?php foreach ( $posts as $i => $p ) :
			$primary = pickprism_primary_category( $p->ID );
			$read    = pickprism_reading_time( $p->ID );
			?>
			<li>
				<a href="<?php echo esc_url( get_permalink( $p ) ); ?>">
					<span class="ha-side__num"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
					<span class="ha-side__body">
						<?php if ( $primary ) : ?>
							<span class="ha-side__tag"><?php echo esc_html( $primary->name ); ?></span>
						<?php endif; ?>
						<span class="ha-side__tt"><?php echo esc_html( get_the_title( $p ) ); ?></span>
						<span class="ha-side__meta">
							<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
							<?php
							/* translators: 1: минуты, 2: дата */
							echo esc_html(
								sprintf(
									__( '%1$d мин · %2$s', 'pickprism' ),
									$read,
									get_the_date( 'j M', $p )
								)
							);
							?>
						</span>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
