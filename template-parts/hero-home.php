<?php
/**
 * Hero главной страницы — тёмный centered с glow-эффектом и звёздами.
 * Контент управляется через ACF (Options page «Главная страница»).
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

$has_acf = function_exists( 'get_field' );

$hero_kicker = $has_acf ? (string) get_field( 'hero_kicker', 'option' ) : '';
$hero_title  = $has_acf ? (string) get_field( 'hero_title', 'option' ) : '';
$hero_lead   = $has_acf ? (string) get_field( 'hero_lead', 'option' ) : '';

$cta_primary   = $has_acf ? (array) get_field( 'hero_cta_primary', 'option' ) : array();
$cta_secondary = $has_acf ? (array) get_field( 'hero_cta_secondary', 'option' ) : array();

$cta_primary_label   = isset( $cta_primary['label'] ) ? trim( (string) $cta_primary['label'] ) : '';
$cta_primary_url     = isset( $cta_primary['url'] ) ? trim( (string) $cta_primary['url'] ) : '';
$cta_secondary_label = isset( $cta_secondary['label'] ) ? trim( (string) $cta_secondary['label'] ) : '';
$cta_secondary_url   = isset( $cta_secondary['url'] ) ? trim( (string) $cta_secondary['url'] ) : '';

$has_primary_cta   = $cta_primary_label !== '' && $cta_primary_url !== '';
$has_secondary_cta = $cta_secondary_label !== '' && $cta_secondary_url !== '';
$has_any_cta       = $has_primary_cta || $has_secondary_cta;

$title_allowed = array(
	'br'   => array(),
	'span' => array( 'class' => array() ),
);

$has_title = trim( wp_strip_all_tags( $hero_title ) ) !== '';
?>
<section
	class="ha-hero ha-hero--centered"
	<?php echo $has_title ? 'aria-labelledby="hero-title"' : 'aria-label="' . esc_attr__( 'Главная', 'pickprism' ) . '"'; ?>
>
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
		<?php if ( $hero_kicker !== '' ) : ?>
			<div class="ha-hero__kicker">
				<span class="ha-hero__dot" aria-hidden="true"></span>
				<?php echo esc_html( $hero_kicker ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $has_title ) : ?>
			<h1 id="hero-title" class="ha-hero__title">
				<?php echo wp_kses( $hero_title, $title_allowed ); ?>
			</h1>
		<?php endif; ?>

		<?php if ( $hero_lead !== '' ) : ?>
			<p class="ha-hero__lead"><?php echo esc_html( $hero_lead ); ?></p>
		<?php endif; ?>

		<?php if ( $has_any_cta ) : ?>
			<div class="ha-hero__ctas">
				<?php if ( $has_primary_cta ) : ?>
					<a class="ha-btn ha-btn--accent" href="<?php echo esc_url( $cta_primary_url ); ?>">
						<?php echo esc_html( $cta_primary_label ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
					</a>
				<?php endif; ?>

				<?php if ( $has_secondary_cta ) : ?>
					<a
						class="ha-btn ha-btn--ghost"
						href="<?php echo esc_url( $cta_secondary_url ); ?>"
						<?php echo pickprism_is_external_url( $cta_secondary_url ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
					>
						<?php echo esc_html( $cta_secondary_label ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
