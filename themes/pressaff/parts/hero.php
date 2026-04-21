<?php
/**
 * Pressaff — hero секция.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$site_name = get_bloginfo( 'name' );
$tagline   = get_bloginfo( 'description' );
?>
<section class="pa-hero" aria-label="<?php esc_attr_e( 'Герой', 'pickprism' ); ?>">

	<!-- Декоративные SVG-звёздочки -->
	<div class="pa-hero__decor" aria-hidden="true">
		<!-- 4-конечная звёздочка -->
		<svg class="pa-hero__star pa-hero__star--1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="#ff7a1a"/>
		</svg>
		<svg class="pa-hero__star pa-hero__star--2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="#ff7a1a"/>
		</svg>
		<svg class="pa-hero__star pa-hero__star--3" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="#ff9a4d"/>
		</svg>
		<svg class="pa-hero__star pa-hero__star--4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="#ff9a4d"/>
		</svg>
	</div>

	<div class="container pa-hero__inner">

		<!-- Кикер-бейдж -->
		<div class="pa-hero__kicker">
			<svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
				<path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z" fill="currentColor"/>
			</svg>
			<?php
			$post_count = wp_count_posts();
			$total      = isset( $post_count->publish ) ? (int) $post_count->publish : 0;
			echo esc_html(
				sprintf(
					/* translators: %d: количество статей */
					_n( '%d статья', '%d статей', $total, 'pickprism' ),
					$total
				)
			);
			?>
		</div>

		<!-- Заголовок -->
		<h1 class="pa-hero__title">
			<?php
			$name_parts = explode( ' ', $site_name, 2 );
			if ( count( $name_parts ) > 1 ) {
				echo esc_html( $name_parts[0] ) . ' <em>' . esc_html( $name_parts[1] ) . '</em>';
			} else {
				echo '<em>' . esc_html( $site_name ) . '</em>';
			}
			?>
			<br>
			<?php esc_html_e( '— всё, что нужно знать', 'pickprism' ); ?>
		</h1>

		<!-- Подзаголовок -->
		<p class="pa-hero__subtitle">
			<?php
			if ( $tagline ) {
				echo esc_html( $tagline );
			} else {
				esc_html_e( 'Свежие статьи, обзоры и разборы. Ищите по теме или листайте ленту.', 'pickprism' );
			}
			?>
		</p>

		<!-- Форма поиска -->
		<div class="pa-hero__search">
			<?php
			get_template_part(
				'template-parts/search-form',
				null,
				array( 'size' => 'lg' )
			);
			?>
		</div>

		<!-- Статы -->
		<div class="pa-hero__stats">
			<?php
			$categories = pickprism_get_top_categories( 99 );
			$cat_count  = count( $categories );
			$tags       = pickprism_get_popular_tags( 99 );
			$tag_count  = count( $tags );
			?>
			<?php if ( $total > 0 ) : ?>
				<div class="pa-hero__stat">
					<strong><?php echo esc_html( number_format( $total ) ); ?></strong>
					<span><?php esc_html_e( 'статей', 'pickprism' ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $cat_count > 0 ) : ?>
				<div class="pa-hero__stat">
					<strong><?php echo esc_html( (string) $cat_count ); ?></strong>
					<span><?php esc_html_e( 'категорий', 'pickprism' ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $tag_count > 0 ) : ?>
				<div class="pa-hero__stat">
					<strong><?php echo esc_html( (string) $tag_count ); ?></strong>
					<span><?php esc_html_e( 'тегов', 'pickprism' ); ?></span>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>
