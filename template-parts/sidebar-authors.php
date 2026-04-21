<?php
/**
 * Sidebar-блок «Авторы недели» — топ-4 автора по post_count, аватары или gradient-fallback.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$authors = pickprism_authors_of_the_week( 4 );
if ( empty( $authors ) ) {
	return;
}
?>
<div class="ha-side__block">
	<div class="ha-side__head">
		<div class="ha-side__title"><?php esc_html_e( 'Авторы недели', 'pickprism' ); ?></div>
	</div>
	<ul class="ha-side__authors">
		<?php foreach ( $authors as $au ) :
			$user_id     = (int) $au->ID;
			$name        = $au->display_name;
			$url         = get_author_posts_url( $user_id );
			$post_count  = (int) count_user_posts( $user_id, 'post', true );
			$avatar_url  = get_avatar_url( $user_id, array( 'size' => 80 ) );
			$hue         = pickprism_user_hue( $user_id );
			$initial     = '';
			if ( $name ) {
				$parts = preg_split( '/\s+/', trim( $name ) );
				foreach ( $parts as $p ) {
					if ( $p === '' ) {
						continue;
					}
					$initial .= mb_strtoupper( mb_substr( $p, 0, 1, 'UTF-8' ), 'UTF-8' );
					if ( mb_strlen( $initial, 'UTF-8' ) >= 2 ) {
						break;
					}
				}
			}
			if ( $initial === '' ) {
				$initial = '?';
			}
			$role_label = (string) get_user_meta( $user_id, 'description', true );
			$role_label = $role_label ? wp_trim_words( $role_label, 4, '…' ) : __( 'Автор', 'pickprism' );
			?>
			<li>
				<a href="<?php echo esc_url( $url ); ?>" style="--hue: <?php echo (int) $hue; ?>;">
					<span class="ha-side__ava" aria-hidden="true">
						<?php if ( $avatar_url ) : ?>
							<img src="<?php echo esc_url( $avatar_url ); ?>" alt="" loading="lazy" decoding="async" width="40" height="40" />
						<?php else : ?>
							<?php echo esc_html( $initial ); ?>
						<?php endif; ?>
					</span>
					<span class="ha-side__au-body">
						<span class="ha-side__au-name"><?php echo esc_html( $name ); ?></span>
						<span class="ha-side__au-role"><?php echo esc_html( $role_label ); ?></span>
					</span>
					<span class="ha-side__au-n"><?php echo esc_html( (string) $post_count ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
