<?php
/**
 * Табы над лентой на главной: «Все» + топ-4 категории.
 * Переключение выполняется через JS (см. category-tabs.js) через REST-эндпоинт
 * /pickprism/v1/feed (type=category, value=slug).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$cats = pickprism_get_top_categories( 4 );
?>
<div class="ha-tabs" role="tablist" data-feed-tabs>
	<button
		type="button"
		class="ha-tabs__btn is-active"
		role="tab"
		aria-selected="true"
		data-feed-tab
		data-feed-type="home"
		data-feed-value=""
	>
		<?php esc_html_e( 'Все', 'pickprism' ); ?>
	</button>

	<?php foreach ( $cats as $cat ) : ?>
		<button
			type="button"
			class="ha-tabs__btn"
			role="tab"
			aria-selected="false"
			data-feed-tab
			data-feed-type="category"
			data-feed-value="<?php echo esc_attr( $cat->slug ); ?>"
		>
			<?php echo esc_html( $cat->name ); ?>
		</button>
	<?php endforeach; ?>
</div>
