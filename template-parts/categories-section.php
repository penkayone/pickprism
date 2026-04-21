<?php
/**
 * Секция «Категории» на главной — 6 плиток-иконок с hue-цветом от term_id.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$cats = pickprism_get_top_categories( 6 );
if ( empty( $cats ) ) {
	return;
}

// Набор встроенных иконок-SVG — подбираем по индексу. Дизайн не требует
// маппинга на конкретные категории, только «симпатичные» иконки.
$icons = array(
	'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 4 5-5"/></svg>',
	'<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M13 22v-8h3l.4-4H13V7.2c0-1 .3-1.7 1.7-1.7H17V2h-3c-3 0-4.8 1.7-4.8 4.7V10H7v4h2.2v8z"/></svg>',
	'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><path d="M12 2a14 14 0 0 1 0 20M12 2a14 14 0 0 0 0 20M2 12h20"/></svg>',
	'<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16.6 5.8a4.5 4.5 0 0 1-3.6-4.3h-3.4v14.3a2.8 2.8 0 1 1-2-2.7v-3.5a6.2 6.2 0 1 0 5.4 6.2V9.3a7.9 7.9 0 0 0 4.6 1.5V7.4a4.5 4.5 0 0 1-1-1.6"/></svg>',
	'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
	'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/></svg>',
);
?>
<section class="ha-cats" aria-labelledby="home-cats-title">
	<div class="ha-sec-head">
		<h2 id="home-cats-title" class="ha-sec-head__title"><?php esc_html_e( 'Категории', 'pickprism' ); ?></h2>
		<span class="ha-sec-head__line" aria-hidden="true"></span>
		<a class="ha-sec-head__link" href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Все категории →', 'pickprism' ); ?>
		</a>
	</div>

	<div class="ha-cats__grid">
		<?php foreach ( $cats as $i => $cat ) :
			$link = get_term_link( $cat );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$hue  = pickprism_term_hue( $cat );
			$icon = $icons[ $i % count( $icons ) ];
			?>
			<a class="ha-cat" href="<?php echo esc_url( $link ); ?>" style="--hue: <?php echo (int) $hue; ?>;">
				<span class="ha-cat__icon" aria-hidden="true">
					<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — whitelisted inline SVG. ?>
				</span>
				<span class="ha-cat__label"><?php echo esc_html( $cat->name ); ?></span>
				<span class="ha-cat__count"><?php echo esc_html( (string) $cat->count ); ?></span>
				<svg class="ha-cat__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
		<?php endforeach; ?>
	</div>
</section>
