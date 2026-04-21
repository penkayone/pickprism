<?php
/**
 * Карточка закреплённого поста (редизайн): 2-col — tint cover с буквой категории + pin-бейдж
 * слева / body + author + CTA справа.
 *
 * @package Pickprism
 *
 * @var array{post:WP_Post} $args
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $args['post'] ) || ! $args['post'] instanceof WP_Post ) {
	return;
}

$p            = $args['post'];
$post_id      = $p->ID;
$perma        = get_permalink( $p );
$primary_term = pickprism_primary_category( $post_id );
$hue          = pickprism_cover_hue( $post_id );
$category_letter = $primary_term ? mb_strtoupper( mb_substr( $primary_term->name, 0, 1, 'UTF-8' ), 'UTF-8' ) : 'P';
$is_new       = pickprism_is_new( $post_id );
$read_time    = pickprism_reading_time( $post_id );

$author_id    = (int) $p->post_author;
$author_name  = get_the_author_meta( 'display_name', $author_id );
$author_ini   = '';
if ( $author_name ) {
	$parts = preg_split( '/\s+/', trim( $author_name ) );
	foreach ( $parts as $part ) {
		if ( $part === '' ) {
			continue;
		}
		$author_ini .= mb_strtoupper( mb_substr( $part, 0, 1, 'UTF-8' ), 'UTF-8' );
		if ( mb_strlen( $author_ini, 'UTF-8' ) >= 2 ) {
			break;
		}
	}
}
if ( $author_ini === '' ) {
	$author_ini = '?';
}

$author_avatar = get_avatar_url( $author_id, array( 'size' => 80 ) );
?>
<a
	class="ha-sticky reveal"
	href="<?php echo esc_url( $perma ); ?>"
	data-post-id="<?php echo esc_attr( (string) $post_id ); ?>"
	style="--hue: <?php echo (int) $hue; ?>;"
>
	<div class="ha-sticky__cover">
		<?php if ( has_post_thumbnail( $p ) ) : ?>
			<?php
			echo get_the_post_thumbnail( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$p,
				'pickprism-hero',
				array(
					'loading'  => 'eager',
					'decoding' => 'async',
					'alt'      => esc_attr( get_the_title( $p ) ),
				)
			);
			?>
		<?php else : ?>
			<div class="ha-sticky__bg" aria-hidden="true"></div>
			<div class="ha-sticky__mesh" aria-hidden="true">
				<span style="left: 12%; top: 20%;">&#10022;</span>
				<span style="right: 10%; top: 28%;">&#10022;</span>
				<span style="left: 28%; bottom: 24%;">&#10022;</span>
				<span style="right: 22%; bottom: 14%;">&#10022;</span>
				<span style="left: 55%; top: 60%;">&#10022;</span>
			</div>
			<div class="ha-sticky__letter" aria-hidden="true"><?php echo esc_html( $category_letter ); ?></div>
		<?php endif; ?>

		<span class="ha-sticky__pin">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16 9V4h1a1 1 0 0 0 0-2H7a1 1 0 0 0 0 2h1v5a5 5 0 0 0-3 4.6V15h6v7l1 1 1-1v-7h6v-1.4A5 5 0 0 0 16 9"/></svg>
			<?php esc_html_e( 'Закреплено', 'pickprism' ); ?>
		</span>
	</div>

	<div class="ha-sticky__body">
		<div>
			<div class="ha-sticky__meta">
				<?php if ( $primary_term ) : ?>
					<span class="ha-sticky__cat"><?php echo esc_html( $primary_term->name ); ?></span>
				<?php endif; ?>
				<?php if ( $is_new ) : ?>
					<span class="ha-card__new"><?php esc_html_e( 'Новое', 'pickprism' ); ?></span>
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'c', $p ) ); ?>">
					<?php echo esc_html( get_the_date( 'j F', $p ) ); ?>
				</time>
				<span class="ha-card__dot" aria-hidden="true">·</span>
				<span class="ha-card__read">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
					<?php
					/* translators: %d: минуты чтения */
					echo esc_html( sprintf( __( '%d мин', 'pickprism' ), $read_time ) );
					?>
				</span>
			</div>

			<h2 class="ha-sticky__title"><?php echo esc_html( get_the_title( $p ) ); ?></h2>

			<?php
			$excerpt = $p->post_excerpt ? $p->post_excerpt : $p->post_content;
			$excerpt_text = wp_trim_words( wp_strip_all_tags( $excerpt ), 34, '…' );
			if ( $excerpt_text ) :
				?>
				<p class="ha-sticky__excerpt"><?php echo esc_html( $excerpt_text ); ?></p>
			<?php endif; ?>
		</div>

		<div class="ha-sticky__foot">
			<span class="ha-card__avatar" aria-hidden="true">
				<?php if ( $author_avatar ) : ?>
					<img src="<?php echo esc_url( $author_avatar ); ?>" alt="" loading="lazy" decoding="async" width="40" height="40" />
				<?php else : ?>
					<?php echo esc_html( $author_ini ); ?>
				<?php endif; ?>
			</span>
			<div>
				<div class="ha-sticky__author"><?php echo esc_html( $author_name ); ?></div>
				<div class="ha-sticky__author-role"><?php esc_html_e( 'Автор', 'pickprism' ); ?></div>
			</div>
			<span class="ha-sticky__cta">
				<?php esc_html_e( 'Читать', 'pickprism' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
			</span>
		</div>
	</div>
</a>
