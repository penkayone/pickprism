<?php
/**
 * Главная страница (редизайн pressaff-style):
 * hero-home → categories-section → main-with-sidebar [sticky + tabs + 2-col grid + sidebar] → popular-tags.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();

$sticky_posts = pickprism_get_sticky_posts( 1 );
$sticky_ids   = wp_list_pluck( $sticky_posts, 'ID' );
$per_page     = (int) get_option( 'posts_per_page', 12 );
$paged        = max( 1, (int) get_query_var( 'paged', 1 ) );
?>

<?php get_template_part( 'template-parts/hero-home' ); ?>

<div class="ha-container">
	<?php get_template_part( 'template-parts/categories-section' ); ?>

	<div class="ha-withside">
		<section class="ha-feed" id="feed" data-feed-container>
			<div class="ha-sec-head">
				<h2 class="ha-sec-head__title"><?php esc_html_e( 'Лента материалов', 'pickprism' ); ?></h2>
				<span class="ha-sec-head__line" aria-hidden="true"></span>
				<?php get_template_part( 'template-parts/feed-tabs' ); ?>
			</div>

			<?php if ( ! empty( $sticky_posts ) ) : ?>
				<?php
				foreach ( $sticky_posts as $sp ) :
					get_template_part(
						'template-parts/card-article-sticky',
						null,
						array( 'post' => $sp )
					);
				endforeach;
				?>
			<?php endif; ?>

			<div class="ha-feed__grid" data-feed-list>
				<?php
				$feed_query = new WP_Query(
					array(
						'post_status'         => 'publish',
						'post_type'           => 'post',
						'posts_per_page'      => $per_page,
						'ignore_sticky_posts' => true,
						'post__not_in'        => $sticky_ids,
						'paged'               => $paged,
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
			</div>

			<?php
			// Pagination + sentinel для infinite-scroll.
			get_template_part(
				'template-parts/pagination',
				null,
				array( 'query' => $feed_query )
			);
			?>

			<div class="ha-sentinel" data-feed-sentinel aria-hidden="true"></div>
		</section>

		<?php get_sidebar(); ?>
	</div>

	<?php get_template_part( 'template-parts/popular-tags' ); ?>
</div>

<?php
get_footer();
