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
	<?php if ( is_category() || is_tag() ) : ?>
		<?php get_template_part( 'template-parts/taxonomy-hero' ); ?>
	<?php else : ?>
		<?php
		// Архивы date / author / прочее — компактный header в стиле pa-taxhero.
		if ( is_author() ) {
			$pickprism_arch_kicker = __( 'Автор', 'pickprism' );
			$pickprism_arch_title  = (string) get_the_author();
		} elseif ( is_day() ) {
			$pickprism_arch_kicker = __( 'Архив', 'pickprism' );
			$pickprism_arch_title  = get_the_date();
		} elseif ( is_month() ) {
			$pickprism_arch_kicker = __( 'Архив', 'pickprism' );
			$pickprism_arch_title  = single_month_title( ' ', false );
		} elseif ( is_year() ) {
			$pickprism_arch_kicker = __( 'Архив', 'pickprism' );
			$pickprism_arch_title  = get_the_date( 'Y' );
		} else {
			$pickprism_arch_kicker = __( 'Архив', 'pickprism' );
			// fallback — берём готовый title и убираем дефолтный префикс «Архивы:».
			$pickprism_arch_title = wp_strip_all_tags( (string) get_the_archive_title() );
			$pickprism_arch_title = (string) preg_replace( '/^[^:]+:\s*/u', '', $pickprism_arch_title );
		}
		$pickprism_arch_desc = wp_strip_all_tags( (string) get_the_archive_description() );
		?>
		<header class="pa-taxhero" style="--hue: 24;" aria-labelledby="archive-title">
			<div class="pa-taxhero__inner">
				<div class="pa-taxhero__body">
					<span class="pa-taxhero__kicker"><?php echo esc_html( $pickprism_arch_kicker ); ?></span>
					<h1 id="archive-title" class="pa-taxhero__title"><?php echo esc_html( $pickprism_arch_title ); ?></h1>
					<?php if ( $pickprism_arch_desc !== '' ) : ?>
						<p class="pa-taxhero__desc"><?php echo esc_html( $pickprism_arch_desc ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</header>
	<?php endif; ?>

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
