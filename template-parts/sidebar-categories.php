<?php
/**
 * Sidebar-блок «Категории» — 6 пунктов с hue-иконками + ссылка «все».
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$cats = pickprism_get_top_categories( 6 );
if ( empty( $cats ) ) {
	return;
}

$icons = array(
	'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 4 5-5"/></svg>',
	'<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M13 22v-8h3l.4-4H13V7.2c0-1 .3-1.7 1.7-1.7H17V2h-3c-3 0-4.8 1.7-4.8 4.7V10H7v4h2.2v8z"/></svg>',
	'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><path d="M12 2a14 14 0 0 1 0 20M12 2a14 14 0 0 0 0 20M2 12h20"/></svg>',
	'<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16.6 5.8a4.5 4.5 0 0 1-3.6-4.3h-3.4v14.3a2.8 2.8 0 1 1-2-2.7v-3.5a6.2 6.2 0 1 0 5.4 6.2V9.3a7.9 7.9 0 0 0 4.6 1.5V7.4a4.5 4.5 0 0 1-1-1.6"/></svg>',
	'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
	'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/></svg>',
);
?>
<div class="ha-side__block">
	<div class="ha-side__head">
		<div class="ha-side__title"><?php esc_html_e( 'Категории', 'pickprism' ); ?></div>
		<a class="ha-side__all" href="<?php echo esc_url( home_url( '/categories/' ) ); ?>">
			<?php esc_html_e( 'все →', 'pickprism' ); ?>
		</a>
	</div>
	<ul class="ha-side__cats">
		<?php foreach ( $cats as $i => $cat ) :
			$link = get_term_link( $cat );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$hue  = pickprism_term_hue( $cat );
			$icon = $icons[ $i % count( $icons ) ];
			?>
			<li>
				<a href="<?php echo esc_url( $link ); ?>" style="--hue: <?php echo (int) $hue; ?>;">
					<span class="ha-side__cat-icon" aria-hidden="true">
						<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — whitelisted inline SVG. ?>
					</span>
					<span class="ha-side__cat-label"><?php echo esc_html( $cat->name ); ?></span>
					<span class="ha-side__cat-count"><?php echo esc_html( (string) $cat->count ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
