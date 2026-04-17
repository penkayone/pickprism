<?php
/**
 * Карточка статьи: теги → H2 → картинка → excerpt.
 * Используется в ленте и для AJAX-результатов (вызывается из PHP или рендерится JS).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();
$tags    = get_the_terms( $post_id, 'post_tag' );
$tags    = is_array( $tags ) ? array_slice( $tags, 0, 3 ) : array();
?>
<article
	id="post-<?php the_ID(); ?>"
	<?php post_class( 'card card--article reveal' ); ?>
	data-post-id="<?php echo esc_attr( (string) $post_id ); ?>"
>
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

	<h2 class="card__title">
		<a href="<?php the_permalink(); ?>" rel="bookmark">
			<?php the_title(); ?>
		</a>
	</h2>

	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php
			the_post_thumbnail(
				'pickprism-card',
				array(
					'loading'  => 'lazy',
					'decoding' => 'async',
					'class'    => 'card__img',
					'alt'      => the_title_attribute( array( 'echo' => false ) ),
				)
			);
			?>
		</a>
	<?php endif; ?>

	<div class="card__excerpt">
		<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 28, '…' ) ); ?>
	</div>

	<div class="card__meta">
		<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
			<?php echo esc_html( get_the_date() ); ?>
		</time>
	</div>
</article>
