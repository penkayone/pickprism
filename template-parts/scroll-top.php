<?php
/**
 * Кнопка «Наверх» — появляется при прокрутке вниз, плавно скроллит к началу.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;
?>
<button
	type="button"
	class="pa-scroll-top"
	aria-label="<?php esc_attr_e( 'Наверх', 'pickprism' ); ?>"
	data-scroll-top
	hidden
>
	<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
		<path d="M12 19V5"/>
		<path d="m5 12 7-7 7 7"/>
	</svg>
</button>
