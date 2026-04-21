<?php
/**
 * Страница результатов поиска.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="ha-container">
	<header class="page-header">
		<h1 class="page-header__title">
			<?php
			/* translators: %s: поисковый запрос */
			echo esc_html( sprintf( __( 'Поиск: %s', 'pickprism' ), get_search_query() ) );
			?>
		</h1>
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
			<?php else : ?>
				<p class="empty-state__text"><?php esc_html_e( 'Ничего не найдено. Попробуйте другой запрос.', 'pickprism' ); ?></p>
			<?php endif; ?>
		</section>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
