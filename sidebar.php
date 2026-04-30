<?php
/**
 * Единый sidebar: 4 блока (categories, trending, newsletter, tags).
 * Переиспользуется на home и single.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="ha-side" aria-label="<?php esc_attr_e( 'Боковая колонка', 'pickprism' ); ?>">
	<?php get_template_part( 'template-parts/sidebar-categories' ); ?>
	<?php get_template_part( 'template-parts/sidebar-trending' ); ?>
	<?php get_template_part( 'template-parts/sidebar-newsletter' ); ?>
	<?php get_template_part( 'template-parts/sidebar-tags' ); ?>

	<?php if ( is_active_sidebar( 'sidebar-primary' ) ) : ?>
		<div class="ha-side__block">
			<?php dynamic_sidebar( 'sidebar-primary' ); ?>
		</div>
	<?php endif; ?>
</aside>
