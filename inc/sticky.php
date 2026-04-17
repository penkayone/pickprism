<?php
/**
 * Metabox с полем sticky_order + влияние на сортировку sticky-постов.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

const PICKPRISM_STICKY_META = '_pickprism_sticky_order';

add_action(
	'add_meta_boxes',
	static function (): void {
		add_meta_box(
			'pickprism_sticky_order',
			__( 'Pickprism: приоритет закрепа', 'pickprism' ),
			'pickprism_render_sticky_metabox',
			'post',
			'side',
			'default'
		);
	}
);

/**
 * Рендерит metabox.
 *
 * @param WP_Post $post
 */
function pickprism_render_sticky_metabox( WP_Post $post ): void {
	wp_nonce_field( 'pickprism_sticky_save', 'pickprism_sticky_nonce' );
	$value = (int) get_post_meta( $post->ID, PICKPRISM_STICKY_META, true );
	?>
	<p>
		<label for="pickprism_sticky_order">
			<?php esc_html_e( 'Порядок среди закреплённых (меньше — выше). Работает только если пост отмечен как Sticky.', 'pickprism' ); ?>
		</label>
	</p>
	<p>
		<input
			type="number"
			id="pickprism_sticky_order"
			name="pickprism_sticky_order"
			value="<?php echo esc_attr( (string) $value ); ?>"
			min="0"
			max="999"
			step="1"
			style="width:100%"
		/>
	</p>
	<?php
}

/**
 * Сохранение значения.
 */
add_action(
	'save_post_post',
	static function ( int $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce = isset( $_POST['pickprism_sticky_nonce'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['pickprism_sticky_nonce'] ) ) : '';
		if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'pickprism_sticky_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['pickprism_sticky_order'] ) ) {
			return;
		}

		$order = (int) $_POST['pickprism_sticky_order']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( $order < 0 ) {
			$order = 0;
		}
		if ( $order > 999 ) {
			$order = 999;
		}

		update_post_meta( $post_id, PICKPRISM_STICKY_META, $order );
	}
);

/**
 * Возвращает отсортированные sticky-посты (по sticky_order ASC, потом по дате DESC).
 *
 * @param int $limit
 * @return WP_Post[]
 */
function pickprism_get_sticky_posts( int $limit = 10 ): array {
	$sticky_ids = get_option( 'sticky_posts' );
	if ( ! is_array( $sticky_ids ) || empty( $sticky_ids ) ) {
		return array();
	}

	$sticky_ids = array_map( 'intval', $sticky_ids );

	$q = new WP_Query(
		array(
			'post__in'            => $sticky_ids,
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'no_found_rows'       => true,
			'orderby'             => array(
				'meta_value_num' => 'ASC',
				'date'           => 'DESC',
			),
			'meta_key'            => PICKPRISM_STICKY_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	return $q->posts ?: array();
}
