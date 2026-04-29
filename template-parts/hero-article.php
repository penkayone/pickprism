<?php
/**
 * Hero статьи (single) — full-bleed dark с breadcrumbs, cat-chip, title, meta, scroll-hint.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$post_id      = get_the_ID();
$primary_term = pickprism_primary_category( $post_id );
$read_time    = pickprism_reading_time( $post_id );

// Фон hero: если есть featured image — накладываем с градиентом, иначе — чистый тёмный.
$thumb_url = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'pickprism-hero' ) : '';
?>
<section class="pa-hero pa-hero--full">
	<div class="pa-hero__full-bg">
		<?php if ( $thumb_url ) : ?>
			<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="eager" decoding="async" />
		<?php endif; ?>
		<div class="pa-hero__cover-grid" aria-hidden="true"></div>
		<div class="pa-hero__decor" aria-hidden="true">
			<span class="pa-star pa-star--1">&#10022;</span>
			<span class="pa-star pa-star--2">&#10022;</span>
			<span class="pa-star pa-star--3">&#10022;</span>
			<span class="pa-star pa-star--4">&#10022;</span>
			<span class="pa-star pa-star--5">&#10022;</span>
		</div>
	</div>

	<div class="pa-container pa-hero__full-inner">
		<nav class="pa-crumbs" aria-label="breadcrumbs">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'pickprism' ); ?></a>
			<?php if ( $primary_term ) :
				$cat_link = get_term_link( $primary_term );
				if ( ! is_wp_error( $cat_link ) ) :
					?>
					<span class="pa-crumbs__sep">/</span>
					<a href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $primary_term->name ); ?></a>
					<?php
				endif;
			endif; ?>
			<span class="pa-crumbs__sep">/</span>
			<span class="pa-crumbs__cur"><?php echo esc_html( get_the_title() ); ?></span>
		</nav>

		<?php if ( $primary_term ) :
			$cat_link = get_term_link( $primary_term );
			if ( ! is_wp_error( $cat_link ) ) :
				?>
				<a class="pa-hero__cat" href="<?php echo esc_url( $cat_link ); ?>">
					<?php echo esc_html( $primary_term->name ); ?>
				</a>
				<?php
			endif;
		endif; ?>

		<h1 class="pa-hero__title pa-hero__title--xl">
			<?php echo esc_html( get_the_title() ); ?>
		</h1>

		<?php if ( has_excerpt() ) : ?>
			<p class="pa-hero__dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<div class="pa-hero__meta">
			<span class="pa-hero__meta-item">
				<svg class="pa-hero__meta-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</span>
			<span class="pa-hero__dot" aria-hidden="true">·</span>
			<span class="pa-hero__meta-item">
				<svg class="pa-hero__meta-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
				<span>
					<?php
					/* translators: %d: минуты чтения */
					echo esc_html( sprintf( __( '%d минут', 'pickprism' ), $read_time ) );
					?>
				</span>
			</span>
		</div>

		<a class="pa-hero__scroll" href="#article-body">
			<span><?php esc_html_e( 'Читать', 'pickprism' ); ?></span>
			<svg width="14" height="20" viewBox="0 0 14 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M7 3v14M2 12l5 5 5-5"/></svg>
		</a>
	</div>
</section>
