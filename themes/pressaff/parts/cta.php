<?php
/**
 * Pressaff — CTA-блок Telegram / Reddit.
 * Тёмный фон, оранжевый акцент.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="pa-cta" aria-label="<?php esc_attr_e( 'Присоединяйтесь к нам', 'pickprism' ); ?>">
	<div class="container pa-cta__inner">

		<!-- Иконка -->
		<div class="pa-cta__icon" aria-hidden="true">
			<svg width="32" height="32" viewBox="0 0 24 24" focusable="false">
				<path fill="currentColor" d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/>
			</svg>
		</div>

		<h2 class="pa-cta__title">
			<?php esc_html_e( 'Будьте в курсе новостей', 'pickprism' ); ?>
		</h2>

		<p class="pa-cta__text">
			<?php esc_html_e( 'Подпишитесь на наш Telegram-канал и получайте свежие материалы первыми. Обсуждайте темы в Reddit-сообществе.', 'pickprism' ); ?>
		</p>

		<div class="pa-cta__buttons">
			<a
				class="pa-cta__btn pa-cta__btn--primary"
				href="https://t.me/"
				target="_blank"
				rel="noopener noreferrer"
			>
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M9.8 15.5 9.6 19c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.7.4 1.3.2 1.5-.7L21 5.6c.3-1.2-.4-1.7-1.2-1.4L3.6 10.5c-1.2.5-1.2 1.1-.2 1.4l4.1 1.3 9.5-6c.5-.3.9-.1.5.3l-7.7 7Z"/>
				</svg>
				<?php esc_html_e( 'Telegram-канал', 'pickprism' ); ?>
			</a>

			<a
				class="pa-cta__btn pa-cta__btn--outline"
				href="https://www.reddit.com/"
				target="_blank"
				rel="noopener noreferrer"
			>
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M22 12a2.5 2.5 0 0 0-4.2-1.8c-1.6-1-3.6-1.6-5.7-1.7l1-4.3 3 .7a1.8 1.8 0 1 0 .2-1.2l-3.6-.8a.6.6 0 0 0-.7.5l-1.1 4.8c-2.2.1-4.2.7-5.8 1.7a2.5 2.5 0 1 0-3 3.8c-.1.4-.1.8-.1 1.2 0 3.7 4.3 6.7 9.6 6.7s9.6-3 9.6-6.7c0-.4 0-.8-.1-1.2A2.5 2.5 0 0 0 22 12Zm-14.5 2a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm8.6 3.8c-1.1 1-3 1.1-3.8 1.1-.8 0-2.7-.1-3.8-1.1a.5.5 0 1 1 .7-.7c.7.7 2.2.9 3.1.9.9 0 2.4-.2 3.1-.9a.5.5 0 1 1 .7.7Zm-.1-2.3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Z"/>
				</svg>
				<?php esc_html_e( 'Reddit-сообщество', 'pickprism' ); ?>
			</a>
		</div>

	</div>
</section>
