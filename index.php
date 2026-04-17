<?php
/**
 * Главный fallback-шаблон.
 * Используется когда нет более специфичного шаблона в иерархии WP.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<main id="primary" class="site-main container">
	<div class="layout-with-sidebar">
		<div class="feed">
			<?php if ( have_posts() ) : ?>

				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card-article' );
				endwhile;
				?>

				<?php get_template_part( 'template-parts/pagination' ); ?>

			<?php else : ?>

				<section class="empty-state">
					<h1 class="empty-state__title"><?php esc_html_e( 'Ничего не найдено', 'pickprism' ); ?></h1>
					<p class="empty-state__text"><?php esc_html_e( 'Попробуйте изменить запрос или вернуться на главную.', 'pickprism' ); ?></p>
				</section>

			<?php endif; ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
