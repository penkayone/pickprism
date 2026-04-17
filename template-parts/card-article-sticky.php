<?php
/**
 * Карточка закреплённого поста — крупнее, с бейджем.
 *
 * @package Pickprism
 *
 * @var array{post:WP_Post} $args
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $args['post'] ) || ! $args['post'] instanceof WP_Post ) {
	return;
}

$p     = $args['post'];
$tags  = get_the_terms( $p, 'post_tag' );
$tags  = is_array( $tags ) ? array_slice( $tags, 0, 3 ) : array();
$perma = get_permalink( $p );
?>
<article class="card card--sticky reveal" data-post-id="<?php echo esc_attr( (string) $p->ID ); ?>">
	<span class="card__badge">
		<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<path fill="currentColor" d="M16 3v6l5 3v2h-8v7l-1 2-1-2v-7H3v-2l5-3V3h8Z"/>
		</svg>
		<?php esc_html_e( 'Закреплено', 'pickprism' ); ?>
	</span>

	<?php if ( has_post_thumbnail( $p ) ) : ?>
		<a class="card__media" href="<?php echo esc_url( $perma ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			echo get_the_post_thumbnail( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$p,
				'pickprism-hero',
				array(
					'loading'  => 'eager',
					'decoding' => 'async',
					'class'    => 'card__img',
					'alt'      => esc_attr( get_the_title( $p ) ),
				)
			);
			?>
		</a>
	<?php endif; ?>

	<div class="card__body">
		<?php if ( ! empty( $tags ) ) : ?>
			<div class="card__tags">
				<?php foreach ( $tags as $tag ) :
					$link = get_term_link( $tag );
					if ( is_wp_error( $link ) ) {
						continue;
					}
					?>
					<a class="chip chip--tag chip--sm" href="<?php echo esc_url( $link ); ?>">
						#<?php echo esc_html( $tag->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<h2 class="card__title card__title--lg">
			<a href="<?php echo esc_url( $perma ); ?>" rel="bookmark">
				<?php echo esc_html( get_the_title( $p ) ); ?>
			</a>
		</h2>

		<p class="card__excerpt">
			<?php
			$excerpt = $p->post_excerpt ? $p->post_excerpt : $p->post_content;
			echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 34, '…' ) );
			?>
		</p>

		<div class="card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $p ) ); ?>">
				<?php echo esc_html( get_the_date( '', $p ) ); ?>
			</time>
		</div>
	</div>
</article>
