<?php
/**
 * Главная страница: hero + sticky + лента + sidebar.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();

$sticky_posts = pickprism_get_sticky_posts( 3 );
$sticky_ids   = wp_list_pluck( $sticky_posts, 'ID' );
?>

<?php get_template_part( 'template-parts/hero' ); ?>

<main id="primary" class="site-main container">
	<div class="layout-with-sidebar">
		<div class="feed" data-feed-container>

			<?php if ( ! empty( $sticky_posts ) ) : ?>
				<section class="feed__sticky" aria-label="<?php esc_attr_e( 'Закреплённые статьи', 'pickprism' ); ?>">
					<?php foreach ( $sticky_posts as $sp ) : ?>
						<?php
						get_template_part(
							'template-parts/card-article-sticky',
							null,
							array( 'post' => $sp )
						);
						?>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>

			<section class="feed__list" data-feed-list>
				<?php
				$feed_query = new WP_Query(
					array(
						'post_status'         => 'publish',
						'post_type'           => 'post',
						'posts_per_page'      => (int) get_option( 'posts_per_page', 10 ),
						'ignore_sticky_posts' => true,
						'post__not_in'        => $sticky_ids,
						'paged'               => max( 1, (int) get_query_var( 'paged', 1 ) ),
					)
				);

				if ( $feed_query->have_posts() ) :
					while ( $feed_query->have_posts() ) :
						$feed_query->the_post();
						get_template_part( 'template-parts/card-article' );
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="empty-state__text">' . esc_html__( 'Пока нет публикаций.', 'pickprism' ) . '</p>';
				endif;
				?>
			</section>

			<?php
			get_template_part(
				'template-parts/pagination',
				null,
				array( 'query' => $feed_query )
			);
			?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
