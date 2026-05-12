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
	<?php
	$page_for_posts = (int) get_option( 'page_for_posts' );
	$pickprism_blog_title = $page_for_posts ? get_the_title( $page_for_posts ) : __( 'Все статьи', 'pickprism' );
	?>
	<header class="pa-taxhero" style="--hue: 24;" aria-labelledby="blog-title">
		<div class="pa-taxhero__inner">
			<div class="pa-taxhero__body">
				<span class="pa-taxhero__kicker"><?php esc_html_e( 'Блог', 'pickprism' ); ?></span>
				<h1 id="blog-title" class="pa-taxhero__title"><?php echo esc_html( $pickprism_blog_title ); ?></h1>
			</div>
		</div>
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
