<?php
/**
 * Hero статьи (single) — full-bleed dark с breadcrumbs, cat-chip, title, meta, scroll-hint.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$post_id       = get_the_ID();
$primary_term  = pickprism_primary_category( $post_id );
$read_time     = pickprism_reading_time( $post_id );

$author_id     = (int) get_the_author_meta( 'ID' );
$author_name   = get_the_author();
$author_avatar = get_avatar_url( $author_id, array( 'size' => 80 ) );
$author_desc   = (string) get_user_meta( $author_id, 'description', true );
$author_role   = $author_desc ? wp_trim_words( $author_desc, 4, '…' ) : __( 'Автор', 'pickprism' );
$author_ini    = '';
if ( $author_name ) {
	$parts = preg_split( '/\s+/', trim( $author_name ) );
	foreach ( $parts as $p ) {
		if ( $p === '' ) {
			continue;
		}
		$author_ini .= mb_strtoupper( mb_substr( $p, 0, 1, 'UTF-8' ), 'UTF-8' );
		if ( mb_strlen( $author_ini, 'UTF-8' ) >= 2 ) {
			break;
		}
	}
}
if ( $author_ini === '' ) {
	$author_ini = '?';
}

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
			<div class="pa-hero__author">
				<span class="pa-hero__avatar" aria-hidden="true">
					<?php if ( $author_avatar ) : ?>
						<img src="<?php echo esc_url( $author_avatar ); ?>" alt="" loading="lazy" decoding="async" width="38" height="38" />
					<?php else : ?>
						<?php echo esc_html( $author_ini ); ?>
					<?php endif; ?>
				</span>
				<span>
					<strong><?php echo esc_html( $author_name ); ?></strong>
					<span class="pa-hero__role"><?php echo esc_html( $author_role ); ?></span>
				</span>
			</div>
			<span class="pa-hero__dot" aria-hidden="true">·</span>
			<span>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</span>
			<span class="pa-hero__dot" aria-hidden="true">·</span>
			<span>
				<?php
				/* translators: %d: минуты чтения */
				echo esc_html( sprintf( __( '%d минут', 'pickprism' ), $read_time ) );
				?>
			</span>
		</div>

		<div class="pa-hero__scroll">
			<span><?php esc_html_e( 'Читать', 'pickprism' ); ?></span>
			<svg width="14" height="20" viewBox="0 0 14 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M7 3v14M2 12l5 5 5-5"/></svg>
		</div>
	</div>
</section>
