<?php
/**
 * Общий archive.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main container">
	<header class="page-header">
		<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
		<?php the_archive_description( '<p class="page-header__desc">', '</p>' ); ?>
	</header>

	<div class="layout-with-sidebar">
		<div class="feed" data-feed-container>
			<section class="feed__list" data-feed-list>
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/card-article' );
					endwhile;
				else :
					echo '<p class="empty-state__text">' . esc_html__( 'В этом архиве пока нет статей.', 'pickprism' ) . '</p>';
				endif;
				?>
			</section>

			<?php get_template_part( 'template-parts/pagination' ); ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</main>
<?php
get_footer();
