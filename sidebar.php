<?php
/**
 * Боковая колонка.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'Боковая колонка', 'pickprism' ); ?>">
	<?php get_template_part( 'template-parts/sidebar-popular' ); ?>
	<?php get_template_part( 'template-parts/sidebar-tags' ); ?>

	<?php if ( is_active_sidebar( 'sidebar-primary' ) ) : ?>
		<div class="sidebar__widgets">
			<?php dynamic_sidebar( 'sidebar-primary' ); ?>
		</div>
	<?php endif; ?>
</aside>
