<?php
/**
 * Блок «Автор статьи» под статьёй.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$author_id     = (int) get_the_author_meta( 'ID' );
$author_name   = get_the_author();
$author_avatar = get_avatar_url( $author_id, array( 'size' => 128 ) );
$author_desc   = (string) get_user_meta( $author_id, 'description', true );
$author_url    = get_author_posts_url( $author_id );
$author_ini    = '';
if ( $author_name ) {
	$parts = preg_split( '/\s+/', trim( $author_name ) );
	foreach ( $parts as $p ) {
		if ( $p === '' ) {
			continue;
		}
		$author_ini .= mb_strtoupper( mb_substr( $p, 0, 1, 'UTF-8' ), 'UTF-8' );
		if ( mb_strlen( $author_ini, 'UTF-8' ) >= 2 ) {
			break;
		}
	}
}
if ( $author_ini === '' ) {
	$author_ini = '?';
}
?>
<aside class="pa-author">
	<div class="pa-author__avatar" aria-hidden="true">
		<?php if ( $author_avatar ) : ?>
			<img src="<?php echo esc_url( $author_avatar ); ?>" alt="" loading="lazy" decoding="async" width="64" height="64" />
		<?php else : ?>
			<?php echo esc_html( $author_ini ); ?>
		<?php endif; ?>
	</div>
	<div class="pa-author__body">
		<div class="pa-author__name">
			<?php echo esc_html( $author_name ); ?>
		</div>
		<?php if ( $author_desc ) : ?>
			<p class="pa-author__bio"><?php echo esc_html( $author_desc ); ?></p>
		<?php endif; ?>
		<div class="pa-author__links">
			<a href="<?php echo esc_url( $author_url ); ?>">
				<?php esc_html_e( 'Все статьи автора →', 'pickprism' ); ?>
			</a>
		</div>
	</div>
</aside>
