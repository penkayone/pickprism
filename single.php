<?php
/**
 * Single post.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main container">
	<div class="layout-with-sidebar">
		<div class="single">
			<?php
			while ( have_posts() ) :
				the_post();
				$cats = get_the_category();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'article' ); ?>>
					<header class="article__header">
						<?php if ( ! empty( $cats ) ) : ?>
							<div class="article__cats">
								<?php foreach ( $cats as $cat ) :
									$link = get_term_link( $cat );
									if ( is_wp_error( $link ) ) {
										continue;
									}
									?>
									<a class="chip chip--category chip--sm" href="<?php echo esc_url( $link ); ?>">
										<?php echo esc_html( $cat->name ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<h1 class="article__title"><?php the_title(); ?></h1>

						<div class="article__meta">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
								<?php echo esc_html( get_the_date() ); ?>
							</time>
							<span class="article__meta-sep">·</span>
							<span>
								<?php
								/* translators: %s: автор */
								echo esc_html( sprintf( __( 'Автор: %s', 'pickprism' ), get_the_author() ) );
								?>
							</span>
						</div>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="article__hero">
							<?php
							the_post_thumbnail(
								'pickprism-hero',
								array(
									'loading'  => 'eager',
									'decoding' => 'async',
									'class'    => 'article__hero-img',
									'alt'      => the_title_attribute( array( 'echo' => false ) ),
								)
							);
							?>
						</figure>
					<?php endif; ?>

					<div class="article__content">
						<?php the_content(); ?>
					</div>

					<footer class="article__footer">
						<?php
						$tags = get_the_tags();
						if ( is_array( $tags ) && ! empty( $tags ) ) :
							?>
							<div class="article__tags">
								<?php foreach ( $tags as $t ) :
									$link = get_term_link( $t );
									if ( is_wp_error( $link ) ) {
										continue;
									}
									?>
									<a class="chip chip--tag" href="<?php echo esc_url( $link ); ?>">
										#<?php echo esc_html( $t->name ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</footer>
				</article>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			<?php endwhile; ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</main>
<?php
get_footer();
