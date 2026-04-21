<?php
/**
 * Single post (редизайн pressaff-style):
 * hero-article (full-bleed) → main-with-sidebar [article-body + author + prev/next + tags + share] [sidebar]
 * → related → comments → mobile-share-bar.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<?php get_template_part( 'template-parts/hero-article' ); ?>

	<main id="primary" class="pa-container pa-main pa-main--with-sidebar">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'pa-article' ); ?>>
			<?php the_content(); ?>

			<?php get_template_part( 'template-parts/author-block' ); ?>
			<?php get_template_part( 'template-parts/prev-next' ); ?>

			<?php
			$tags = get_the_tags();
			if ( is_array( $tags ) && ! empty( $tags ) ) :
				?>
				<div class="pa-article__tags">
					<?php foreach ( $tags as $t ) :
						$link = get_term_link( $t );
						if ( is_wp_error( $link ) ) {
							continue;
						}
						?>
						<a class="pa-tag" href="<?php echo esc_url( $link ); ?>">
							#<?php echo esc_html( $t->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="pa-article__share">
				<?php get_template_part( 'template-parts/share-horizontal' ); ?>
			</div>
		</article>

		<?php get_sidebar(); ?>
	</main>

	<section class="pa-container pa-related-wrap">
		<?php get_template_part( 'template-parts/related' ); ?>
	</section>

	<?php
	if ( comments_open() || get_comments_number() ) {
		?>
		<section class="pa-container">
			<?php comments_template(); ?>
		</section>
		<?php
	}
	?>
	<?php
endwhile;

get_footer();
