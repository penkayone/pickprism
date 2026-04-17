<?php
/**
 * 404 — страница не найдена.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main container">
	<section class="error-404">
		<div class="error-404__code">404</div>
		<h1 class="error-404__title"><?php esc_html_e( 'Страница не найдена', 'pickprism' ); ?></h1>
		<p class="error-404__text">
			<?php esc_html_e( 'Похоже, такой страницы не существует. Попробуйте поиск или вернитесь на главную.', 'pickprism' ); ?>
		</p>

		<?php get_template_part( 'template-parts/search-form' ); ?>

		<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'На главную', 'pickprism' ); ?>
		</a>
	</section>
</main>
<?php
get_footer();
