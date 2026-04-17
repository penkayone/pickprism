<?php
/**
 * Пагинация + маркер конца для infinite scroll.
 *
 * Поддерживает явный $args['query'] (кастомный WP_Query), иначе берёт основной.
 *
 * @package Pickprism
 *
 * @var array{query?:WP_Query} $args
 */

defined( 'ABSPATH' ) || exit;

$query = isset( $args['query'] ) && $args['query'] instanceof WP_Query
	? $args['query']
	: ( $GLOBALS['wp_query'] ?? null );

if ( ! $query instanceof WP_Query ) {
	return;
}

$max_pages = (int) $query->max_num_pages;
$current   = max( 1, (int) ( $query->get( 'paged' ) ?: get_query_var( 'paged', 1 ) ) );

if ( $max_pages <= 1 ) {
	return;
}
?>
<nav class="pagination" aria-label="<?php esc_attr_e( 'Пагинация', 'pickprism' ); ?>">
	<div class="pagination__sentinel" data-feed-sentinel hidden></div>

	<div class="pagination__status" data-feed-status aria-live="polite"></div>

	<div class="pagination__links">
		<?php
		echo wp_kses_post( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			paginate_links(
				array(
					'prev_text' => __( '← Назад', 'pickprism' ),
					'next_text' => __( 'Вперёд →', 'pickprism' ),
					'type'      => 'plain',
					'total'     => $max_pages,
					'current'   => $current,
				)
			)
		);
		?>
	</div>

	<button
		type="button"
		class="btn btn--ghost pagination__load-more"
		data-feed-load-more
		hidden
	>
		<?php esc_html_e( 'Показать ещё', 'pickprism' ); ?>
	</button>
</nav>
