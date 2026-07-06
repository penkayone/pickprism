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
	<?php
	$pickprism_q = get_search_query();
	$pickprism_found = (int) ( $GLOBALS['wp_query']->found_posts ?? 0 );
	?>
	<header class="pa-taxhero" style="--hue: 24;" aria-labelledby="search-title">
		<div class="pa-taxhero__inner">
			<div class="pa-taxhero__body">
				<span class="pa-taxhero__kicker"><?php esc_html_e( 'Поиск', 'pickprism' ); ?></span>
				<h1 id="search-title" class="pa-taxhero__title">
					<?php echo $pickprism_q !== '' ? esc_html( $pickprism_q ) : esc_html__( 'Пустой запрос', 'pickprism' ); ?>
				</h1>
				<div class="pa-taxhero__meta">
					<span class="pa-taxhero__count">
						<?php
						/* translators: %s — количество результатов */
						echo esc_html(
							sprintf(
								_n( '%s результат', '%s результатов', $pickprism_found, 'pickprism' ),
								number_format_i18n( $pickprism_found )
							)
						);
						?>
					</span>
				</div>
			</div>
		</div>
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
