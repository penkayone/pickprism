<?php
/**
 * Страница блога (лента постов без hero).
 * Используется если в настройках задана отдельная "страница блога".
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
			$page_for_posts = (int) get_option( 'page_for_posts' );
			if ( $page_for_posts ) {
				echo esc_html( get_the_title( $page_for_posts ) );
			} else {
				esc_html_e( 'Все статьи', 'pickprism' );
			}
			?>
		</h1>
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
					echo '<p class="empty-state__text">' . esc_html__( 'Пока нет публикаций.', 'pickprism' ) . '</p>';
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
