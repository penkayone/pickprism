<?php
/**
 * Шаблон блока комментариев для single.php (редизайн pressaff-style).
 * Подключается через comments_template() в single.php.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

// Если пост защищён паролем и он не введён — вежливо молчим.
if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="pa-comments comments-section" data-post-id="<?php the_ID(); ?>">
	<?php if ( have_comments() ) : ?>
		<div class="pa-sec-head">
			<h2 class="pa-sec-head__title">
				<?php
				$num = (int) get_comments_number();
				printf(
					/* translators: %s: число комментариев. */
					esc_html( _n( 'Комментарии %s', 'Комментарии %s', $num, 'pickprism' ) ),
					'<span class="pa-sec-head__count">' . esc_html( number_format_i18n( $num ) ) . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — constructed from escaped count.
				);
				?>
			</h2>
			<span class="pa-sec-head__line" aria-hidden="true"></span>
		</div>

		<?php
		// Рендерим wp_list_comments в буфер и переименуем .children → .pa-replies.
		ob_start();
		?>
		<ul class="pa-clist">
			<?php
			wp_list_comments(
				array(
					'style'        => 'ul',
					'callback'     => 'pickprism_comment_callback',
					'end-callback' => 'pickprism_comment_end_callback',
					'avatar_size'  => 44,
					'short_ping'   => true,
				)
			);
			?>
		</ul>
		<?php
		$list_html = ob_get_clean();
		echo pickprism_replies_class_swap( $list_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — internal content already escaped in callback.
		?>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => __( '← Предыдущие', 'pickprism' ),
				'next_text' => __( 'Следующие →', 'pickprism' ),
				'class'     => 'comments-pagination',
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() > 0 ) : ?>
		<p class="comments-closed"><?php esc_html_e( 'Комментарии к этой публикации закрыты.', 'pickprism' ); ?></p>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<?php
		comment_form(
			array(
				'must_log_in'  => '',
				'logged_in_as' => '',
			)
		);
		?>
	<?php endif; ?>
</section>
