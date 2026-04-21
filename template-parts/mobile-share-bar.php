<?php
/**
 * Mobile share bar (sticky bottom ≤960) для single.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_singular( 'post' ) ) {
	return;
}

$url          = get_permalink();
$title        = get_the_title();
$telegram_url = 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title );
$x_url        = 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title );
?>
<div class="pa-mbar" role="toolbar" aria-label="<?php esc_attr_e( 'Поделиться', 'pickprism' ); ?>">
	<div class="pa-mbar__share">
		<a href="<?php echo esc_url( $telegram_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M9.8 15.7 9.6 19c.3 0 .5-.1.6-.3l1.7-1.6 3.4 2.5c.6.3 1 .2 1.2-.6l2.2-10.3c.2-1-.3-1.4-1-1.1L4.8 12.4c-1 .4-1 1-.2 1.2l3.3 1 7.7-4.9c.4-.2.7-.1.4.1z"/></svg>
		</a>
		<a href="<?php echo esc_url( $x_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="X">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="m4 4 7.6 10.2L4.3 20h2l6.3-6.5 4.7 6.5H22l-8-10.8L20.9 4h-2l-5.7 6L8.9 4z"/></svg>
		</a>
		<button type="button" data-copy-link="<?php echo esc_attr( $url ); ?>" aria-label="<?php esc_attr_e( 'Скопировать ссылку', 'pickprism' ); ?>">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M9 3h9a2 2 0 0 1 2 2v11h-2V5H9zM5 7h9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2m0 2v10h9V9z"/></svg>
		</button>
	</div>
	<a href="https://t.me/" target="_blank" rel="noopener noreferrer" class="pa-mbar__cta">
		<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M9.8 15.7 9.6 19c.3 0 .5-.1.6-.3l1.7-1.6 3.4 2.5c.6.3 1 .2 1.2-.6l2.2-10.3c.2-1-.3-1.4-1-1.1L4.8 12.4c-1 .4-1 1-.2 1.2l3.3 1 7.7-4.9c.4-.2.7-.1.4.1z"/></svg>
		<span><?php esc_html_e( 'В канал', 'pickprism' ); ?></span>
	</a>
</div>
