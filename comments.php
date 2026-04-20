<?php
/**
 * Шаблон блока комментариев для single.php.
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
<section id="comments" class="comments-section" data-post-id="<?php the_ID(); ?>">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$num = (int) get_comments_number();
			printf(
				/* translators: %s: число комментариев. */
				esc_html( _n( '%s комментарий', '%s комментариев', $num, 'pickprism' ) ),
				esc_html( number_format_i18n( $num ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'        => 'ol',
					'callback'     => 'pickprism_comment_callback',
					'end-callback' => 'pickprism_comment_end_callback',
					'avatar_size'  => 44,
					'short_ping'   => true,
				)
			);
			?>
		</ol>

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
				// Форма всегда анонимная — никаких «вошли как…».
				'must_log_in'  => '',
				'logged_in_as' => '',
			)
		);
		?>
	<?php endif; ?>
</section>
