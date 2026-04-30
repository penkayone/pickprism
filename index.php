<?php
/**
 * Fallback-шаблон (используется, если нет более специфичного).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<div class="ha-container">
	<div class="ha-withside">
		<section class="ha-feed" id="feed" data-feed-container>
			<div class="ha-sec-head">
				<h1 class="ha-sec-head__title"><?php esc_html_e( 'Все материалы', 'pickprism' ); ?></h1>
				<span class="ha-sec-head__line" aria-hidden="true"></span>
			</div>

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
				<section class="empty-state">
					<h1 class="empty-state__title"><?php esc_html_e( 'Ничего не найдено', 'pickprism' ); ?></h1>
					<p class="empty-state__text"><?php esc_html_e( 'Попробуйте изменить запрос или вернуться на главную.', 'pickprism' ); ?></p>
				</section>
			<?php endif; ?>
		</section>

		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
