<?php
/**
 * Sidebar-блок Telegram CTA (синий gradient).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$telegram_url        = apply_filters( 'pickprism_telegram_url', 'https://t.me/' );
$telegram_subs       = (int) apply_filters( 'pickprism_telegram_subs', 0 );
$telegram_post_count = (int) apply_filters( 'pickprism_telegram_posts_per_week', 0 );
?>
<div class="ha-side__block ha-side__tg">
	<div class="ha-side__tg-ico" aria-hidden="true">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M9.8 15.7 9.6 19c.3 0 .5-.1.6-.3l1.7-1.6 3.4 2.5c.6.3 1 .2 1.2-.6l2.2-10.3c.2-1-.3-1.4-1-1.1L4.8 12.4c-1 .4-1 1-.2 1.2l3.3 1 7.7-4.9c.4-.2.7-.1.4.1z"/></svg>
	</div>
	<div class="ha-side__tg-title"><?php esc_html_e( 'Telegram-канал', 'pickprism' ); ?></div>
	<div class="ha-side__tg-text">
		<?php esc_html_e( 'Короткие разборы и свежие материалы — быстрее, чем статьи.', 'pickprism' ); ?>
	</div>

	<?php if ( $telegram_subs > 0 || $telegram_post_count > 0 ) : ?>
		<div class="ha-side__tg-stat">
			<?php if ( $telegram_subs > 0 ) : ?>
				<div>
					<b><?php echo esc_html( number_format_i18n( $telegram_subs ) ); ?></b>
					<span><?php esc_html_e( 'подписчиков', 'pickprism' ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $telegram_post_count > 0 ) : ?>
				<div>
					<b><?php echo esc_html( (string) $telegram_post_count ); ?></b>
					<span><?php esc_html_e( 'постов в неделю', 'pickprism' ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<a class="ha-side__tg-btn" href="<?php echo esc_url( $telegram_url ); ?>" target="_blank" rel="noopener noreferrer">
		<?php esc_html_e( 'Подписаться', 'pickprism' ); ?>
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
	</a>
</div>
