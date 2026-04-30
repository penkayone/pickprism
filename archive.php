<?php
/**
 * Общий archive (category / tag / date / author).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="ha-container">
	<header class="page-header">
		<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
		<?php the_archive_description( '<p class="page-header__desc">', '</p>' ); ?>
	</header>

	<div class="ha-withside">
		<section class="ha-feed" id="feed" data-feed-container>
			<?php if ( have_posts() ) : ?>
				<div class="ha-feed__grid" data-feed-list>
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/card-article' );
					endwhile;
					?>
				</div>

				<?php get_template_part( 'template-parts/pagination' ); ?>

				<div class="ha-sentinel" data-feed-sentinel aria-hidden="true"></div>
			<?php else : ?>
				<p class="empty-state__text"><?php esc_html_e( 'В этом архиве пока нет статей.', 'pickprism' ); ?></p>
			<?php endif; ?>
		</section>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
