<?php
/**
 * Hero-секция главной: заголовок, чипсы категорий/тегов, большой поиск.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$hero_cats = pickprism_get_top_categories( 8 );
$hero_tags = pickprism_get_popular_tags( 12 );
?>
<section class="hero" aria-labelledby="hero-title">
	<div class="container hero__inner">
		<h1 id="hero-title" class="hero__title">
			<?php esc_html_e( 'Статьи про технологии, продукт и разработку', 'pickprism' ); ?>
		</h1>
		<p class="hero__subtitle">
			<?php esc_html_e( 'Разборы, практика и мнения без воды. Читайте, ищите, подписывайтесь.', 'pickprism' ); ?>
		</p>

		<?php get_template_part( 'template-parts/search-form', null, array( 'size' => 'lg' ) ); ?>

		<?php if ( ! empty( $hero_cats ) || ! empty( $hero_tags ) ) : ?>
			<div class="hero__chips">
				<?php
				get_template_part(
					'template-parts/taxonomy-chips',
					null,
					array(
						'categories' => $hero_cats,
						'tags'       => $hero_tags,
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
