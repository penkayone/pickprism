<?php
/**
 * Страница результатов поиска.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main container">
	<header class="page-header">
		<h1 class="page-header__title">
			<?php
			/* translators: %s: поисковый запрос */
			echo esc_html( sprintf( __( 'Поиск: %s', 'pickprism' ), get_search_query() ) );
			?>
		</h1>
		<?php get_template_part( 'template-parts/search-form' ); ?>
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
					echo '<p class="empty-state__text">' . esc_html__( 'Ничего не найдено. Попробуйте другой запрос.', 'pickprism' ) . '</p>';
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
