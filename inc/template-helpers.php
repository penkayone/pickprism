<?php
/**
 * Template helpers (редизайн pressaff-style).
 *
 * Экспортирует универсальные функции, используемые в шаблонах:
 * - pickprism_primary_category     — первичная категория поста (Rank Math или fallback).
 * - pickprism_term_hue             — hue 0..359 от term_id (или slug).
 * - pickprism_user_hue             — hue 0..359 от user_id.
 * - pickprism_cover_hue            — hue обложки поста (от primary category).
 * - pickprism_reading_time         — минуты чтения (с кэшем в post_meta).
 * - pickprism_is_new               — опубликован ли пост за последние 7 дней.
 * - pickprism_render_cover         — единая разметка обложки карточки.
 *
 * Хуки:
 * - save_post → пересчёт _pickprism_reading_time.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

// ════════════════════════════════════════════════════════════════════════════
// Primary category + hue helpers
// ════════════════════════════════════════════════════════════════════════════

/**
 * Первичная категория поста. Приоритет — Rank Math SEO (meta-ключ
 * `rank_math_primary_category`), иначе первая из `get_the_category()`.
 *
 * @param int $post_id
 * @return WP_Term|null
 */
function pickprism_primary_category( int $post_id ): ?WP_Term {
	if ( $post_id <= 0 ) {
		return null;
	}

	// Rank Math — если установлен и задан primary.
	$primary_id = (int) get_post_meta( $post_id, 'rank_math_primary_category', true );
	if ( $primary_id > 0 ) {
		$term = get_term( $primary_id, 'category' );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	// Fallback — первая категория поста.
	$cats = get_the_category( $post_id );
	if ( is_array( $cats ) && ! empty( $cats ) ) {
		$first = $cats[0];
		if ( $first instanceof WP_Term ) {
			return $first;
		}
	}

	return null;
}

/**
 * Hue 0..359 от заданной строки (детерминированно).
 *
 * @param string $seed
 * @return int
 */
function pickprism_hue_from_seed( string $seed ): int {
	$seed = trim( $seed );
	if ( $seed === '' ) {
		return 24; // orange-ish default
	}
	return (int) ( abs( crc32( $seed ) ) % 360 );
}

/**
 * Hue для таксономии (term). Использует term_id, иначе slug.
 *
 * @param WP_Term|int|null $term
 * @return int
 */
function pickprism_term_hue( $term ): int {
	if ( $term instanceof WP_Term ) {
		if ( (int) $term->term_id > 0 ) {
			return pickprism_hue_from_seed( (string) $term->term_id );
		}
		if ( $term->slug !== '' ) {
			return pickprism_hue_from_seed( $term->slug );
		}
	}
	if ( is_int( $term ) && $term > 0 ) {
		return pickprism_hue_from_seed( (string) $term );
	}
	return 24;
}

/**
 * Hue пользователя (для аватара fallback).
 *
 * @param int $user_id
 * @return int
 */
function pickprism_user_hue( int $user_id ): int {
	if ( $user_id <= 0 ) {
		return 24;
	}
	return pickprism_hue_from_seed( 'user-' . $user_id );
}

/**
 * Hue обложки поста — от первичной категории.
 *
 * @param int $post_id
 * @return int
 */
function pickprism_cover_hue( int $post_id ): int {
	$term = pickprism_primary_category( $post_id );
	if ( $term instanceof WP_Term ) {
		return pickprism_term_hue( $term );
	}
	return pickprism_hue_from_seed( 'post-' . $post_id );
}

// ════════════════════════════════════════════════════════════════════════════
// Reading time (с кэшем в post_meta)
// ════════════════════════════════════════════════════════════════════════════

/**
 * Минуты чтения поста. Минимум 1. Используется:
 * 1) post_meta '_pickprism_reading_time' — если есть.
 * 2) иначе считается на лету (но НЕ записывается — запись только на save_post,
 *    чтобы не создавать всплеск DB-запросов при первом заходе на 1000 постов).
 *
 * @param int $post_id
 * @return int
 */
function pickprism_reading_time( int $post_id ): int {
	if ( $post_id <= 0 ) {
		return 1;
	}

	$cached = get_post_meta( $post_id, '_pickprism_reading_time', true );
	if ( $cached !== '' && (int) $cached > 0 ) {
		return (int) $cached;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return 1;
	}

	$content = (string) $post->post_content;
	$plain   = wp_strip_all_tags( $content );
	$words   = str_word_count( $plain );

	$minutes = (int) max( 1, (int) ceil( $words / 200 ) );
	return $minutes;
}

/**
 * Пересчёт reading-time на save_post (некешируемое хранение меты ценнее — оно
 * сразу доступно всем запросам без pay-for-first-hit).
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
function pickprism_update_reading_time_meta( int $post_id, WP_Post $post ): void {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( $post->post_type !== 'post' ) {
		return;
	}
	if ( ! in_array( $post->post_status, array( 'publish', 'future', 'draft', 'pending' ), true ) ) {
		return;
	}

	$plain   = wp_strip_all_tags( (string) $post->post_content );
	$words   = str_word_count( $plain );
	$minutes = (int) max( 1, (int) ceil( $words / 200 ) );

	update_post_meta( $post_id, '_pickprism_reading_time', $minutes );
}
add_action( 'save_post', 'pickprism_update_reading_time_meta', 20, 2 );

// ════════════════════════════════════════════════════════════════════════════
// Is-new badge
// ════════════════════════════════════════════════════════════════════════════

/**
 * True, если пост опубликован за последние 7 дней.
 *
 * @param int $post_id
 * @return bool
 */
function pickprism_is_new( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return false;
	}
	$post_ts = (int) get_post_time( 'U', true, $post_id );
	if ( $post_ts <= 0 ) {
		return false;
	}
	$week_ago = strtotime( '-7 days' );
	return $post_ts >= $week_ago;
}

// ════════════════════════════════════════════════════════════════════════════
// Cover renderer (для карточек)
// ════════════════════════════════════════════════════════════════════════════

/**
 * Рендерит единую разметку обложки карточки. Если есть featured image — img,
 * иначе — hue-градиент + первая буква категории.
 *
 * @param int    $post_id
 * @param string $size  md | sm | lg | row (определяет aspect-ratio)
 * @return void
 */
function pickprism_render_cover( int $post_id, string $size = 'md' ): void {
	$size = in_array( $size, array( 'md', 'sm', 'lg', 'row' ), true ) ? $size : 'md';
	$hue  = pickprism_cover_hue( $post_id );
	$term = pickprism_primary_category( $post_id );
	$letter = $term ? mb_strtoupper( mb_substr( $term->name, 0, 1, 'UTF-8' ), 'UTF-8' ) : 'P';
	$category_name = $term ? $term->name : '';

	echo '<div class="ha-cover ha-cover--' . esc_attr( $size ) . '" style="--hue: ' . (int) $hue . ';">';

	if ( has_post_thumbnail( $post_id ) ) {
		$img_size = $size === 'lg' ? 'pickprism-hero' : 'pickprism-card';
		echo get_the_post_thumbnail( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$post_id,
			$img_size,
			array(
				'class'    => 'ha-cover__img',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => esc_attr( get_the_title( $post_id ) ),
			)
		);
	} else {
		echo '<div class="ha-cover__bg" aria-hidden="true"></div>';
		echo '<div class="ha-cover__mesh" aria-hidden="true">';
		echo '<span style="left: 14%; top: 22%;">&#10022;</span>';
		echo '<span style="right: 12%; top: 30%;">&#10022;</span>';
		echo '<span style="left: 28%; bottom: 16%;">&#10022;</span>';
		echo '<span style="right: 22%; bottom: 10%;">&#10022;</span>';
		echo '</div>';
		echo '<div class="ha-cover__letter" aria-hidden="true">' . esc_html( $letter ) . '</div>';
	}

	if ( $category_name !== '' ) {
		echo '<span class="ha-cover__cat">' . esc_html( $category_name ) . '</span>';
	}

	echo '</div>';
}

// ════════════════════════════════════════════════════════════════════════════
// Социальные ссылки
// ════════════════════════════════════════════════════════════════════════════

/**
 * URL соц. сети из theme_mod. Поддерживает: telegram, reddit, twitter, vk и др.
 * Пустая строка означает «ссылка не задана» — UI должен скрыть кнопку.
 *
 * Чтобы заполнить: WP Admin → Внешний вид → Настроить → custom-css или через
 * `set_theme_mod( 'pickprism_telegram_url', 'https://t.me/your_channel' )`.
 *
 * @param string $network Имя сети (lowercase).
 * @return string Валидный URL или пустая строка.
 */
function pickprism_social_url( string $network ): string {
	$key = 'pickprism_' . $network . '_url';
	$url = (string) get_theme_mod( $key, '' );
	return $url !== '' && filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
}
