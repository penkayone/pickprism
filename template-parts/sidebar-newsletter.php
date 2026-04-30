<?php
/**
 * Sidebar-блок «Дайджест» — форма подписки (тёмный).
 * Форма — заглушка (action="#"), backend-интеграция вне темы.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="ha-side__block ha-side__nl">
	<div class="ha-side__nl-ico" aria-hidden="true">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
	</div>
	<div class="ha-side__nl-title"><?php esc_html_e( 'Еженедельный дайджест', 'pickprism' ); ?></div>
	<div class="ha-side__nl-text">
		<?php esc_html_e( 'Каждый четверг — подборка самых сильных материалов недели. Без спама.', 'pickprism' ); ?>
	</div>
	<form class="ha-side__nl-form" action="#" method="post" onsubmit="return false;">
		<label for="ha-nl-email" class="screen-reader-text"><?php esc_html_e( 'Email', 'pickprism' ); ?></label>
		<input id="ha-nl-email" type="email" name="email" placeholder="<?php esc_attr_e( 'email@example.com', 'pickprism' ); ?>" required />
		<button type="submit"><?php esc_html_e( 'Подписаться', 'pickprism' ); ?></button>
	</form>
	<div class="ha-side__nl-note">
		<?php esc_html_e( 'Отписаться можно в 1 клик', 'pickprism' ); ?>
	</div>
</div>
