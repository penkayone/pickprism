<?php
/**
 * Hero главной страницы — тёмный centered с glow-эффектом и звёздами.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

// Позиции звёзд (фиксированные — для стабильной анимации).
$stars = array(
	array( 10, 20 ), array( 18, 70 ), array( 28, 35 ), array( 42, 15 ),
	array( 58, 62 ), array( 72, 28 ), array( 85, 75 ), array( 90, 40 ),
	array( 4, 55 ), array( 35, 82 ),
);
?>
<section class="ha-hero ha-hero--centered" aria-labelledby="hero-title">
	<div class="ha-hero__bg" aria-hidden="true">
		<div class="ha-hero__glow ha-hero__glow--1"></div>
		<div class="ha-hero__glow ha-hero__glow--2"></div>
		<div class="ha-hero__stars">
			<?php foreach ( $stars as $coord ) : ?>
				<span style="left: <?php echo (int) $coord[0]; ?>%; top: <?php echo (int) $coord[1]; ?>%;">&#10022;</span>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="ha-hero__inner">
		<div class="ha-hero__kicker">
			<span class="ha-hero__dot" aria-hidden="true"></span>
			<?php esc_html_e( 'Медиа про технологии и разработку · обновляется ежедневно', 'pickprism' ); ?>
		</div>

		<h1 id="hero-title" class="ha-hero__title">
			<?php echo wp_kses_post( __( 'Статьи, разборы<br> и практика<br> <span class="ha-hero__title--accent">для тех, кто делает</span>', 'pickprism' ) ); ?>
		</h1>

		<p class="ha-hero__lead">
			<?php esc_html_e( 'Архив практики без воды: разборы кейсов, технологические гайды и мнения специалистов. Читайте, ищите, подписывайтесь.', 'pickprism' ); ?>
		</p>

		<div class="ha-hero__ctas">
			<a class="ha-btn ha-btn--accent" href="#feed">
				<?php esc_html_e( 'Читать ленту', 'pickprism' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</a>
			<?php $pickprism_hero_tg = pickprism_social_url( 'telegram' ); ?>
			<?php if ( $pickprism_hero_tg !== '' ) : ?>
				<a class="ha-btn ha-btn--ghost" href="<?php echo esc_url( $pickprism_hero_tg ); ?>" target="_blank" rel="noopener noreferrer">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M9.8 15.7 9.6 19c.3 0 .5-.1.6-.3l1.7-1.6 3.4 2.5c.6.3 1 .2 1.2-.6l2.2-10.3c.2-1-.3-1.4-1-1.1L4.8 12.4c-1 .4-1 1-.2 1.2l3.3 1 7.7-4.9c.4-.2.7-.1.4.1z"/></svg>
					<?php esc_html_e( 'Telegram', 'pickprism' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
