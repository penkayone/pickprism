<?php
/**
 * Форма поиска с dropdown-результатами.
 * Поддерживает размер: 'md' (по умолчанию) и 'lg' (hero).
 *
 * @package Pickprism
 *
 * @var array{size?:string} $args
 */

defined( 'ABSPATH' ) || exit;

$size   = isset( $args['size'] ) && 'lg' === $args['size'] ? 'lg' : 'md';
$form_c = 'search-form search-form--' . $size;
$uid    = wp_unique_id( 'search-' );
?>
<form
	role="search"
	method="get"
	class="<?php echo esc_attr( $form_c ); ?>"
	action="<?php echo esc_url( home_url( '/' ) ); ?>"
	data-search
	autocomplete="off"
>
	<label for="<?php echo esc_attr( $uid ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Поиск по сайту', 'pickprism' ); ?>
	</label>

	<div class="search-form__field">
		<svg class="search-form__icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<path fill="currentColor" d="M10 2a8 8 0 1 1-5.3 14.1l-3.4 3.4-1.4-1.4 3.4-3.4A8 8 0 0 1 10 2Zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z"/>
		</svg>

		<input
			id="<?php echo esc_attr( $uid ); ?>"
			type="search"
			name="s"
			class="search-form__input"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php esc_attr_e( 'Поиск статей…', 'pickprism' ); ?>"
			data-search-input
			aria-autocomplete="list"
			aria-controls="<?php echo esc_attr( $uid ); ?>-list"
			aria-expanded="false"
			minlength="2"
			maxlength="100"
		/>

		<button type="submit" class="search-form__submit">
			<?php esc_html_e( 'Искать', 'pickprism' ); ?>
		</button>
	</div>

	<div
		class="search-form__dropdown"
		id="<?php echo esc_attr( $uid ); ?>-list"
		data-search-dropdown
		role="listbox"
		hidden
	></div>
</form>
