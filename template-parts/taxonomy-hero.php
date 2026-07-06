<?php
/**
 * Hero для архивов категорий и тегов.
 * Использует hue от term — поддерживает визуальное соответствие с обложками.
 * На category-странице — ссылка «← Все категории».
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$term = get_queried_object();
if ( ! $term instanceof WP_Term ) {
	return;
}

$is_category = is_category();
$is_tag      = is_tag();

if ( ! $is_category && ! $is_tag ) {
	return;
}

$kicker      = $is_category ? __( 'Категория', 'pickprism' ) : __( 'Тег', 'pickprism' );
$hue         = pickprism_term_hue( $term );
$letter      = mb_strtoupper( mb_substr( $term->name, 0, 1, 'UTF-8' ), 'UTF-8' );
$count       = (int) $term->count;
$description = trim( (string) $term->description );
?>
<header
	class="pa-taxhero<?php echo $is_tag ? ' pa-taxhero--tag' : ''; ?>"
	style="--hue: <?php echo (int) $hue; ?>;"
	aria-labelledby="taxhero-title"
>
	<div class="pa-taxhero__inner">
		<div class="pa-taxhero__body">
			<span class="pa-taxhero__kicker"><?php echo esc_html( $kicker ); ?></span>
			<h1 id="taxhero-title" class="pa-taxhero__title"><?php echo esc_html( $term->name ); ?></h1>

			<?php if ( $description !== '' ) : ?>
				<p class="pa-taxhero__desc"><?php echo wp_kses_post( $description ); ?></p>
			<?php endif; ?>

			<div class="pa-taxhero__meta">
				<span class="pa-taxhero__count">
					<?php
					/* translators: %s — количество статей */
					echo esc_html(
						sprintf(
							_n( '%s статья', '%s статей', $count, 'pickprism' ),
							number_format_i18n( $count )
						)
					);
					?>
				</span>

				<?php if ( $is_category ) : ?>
					<a class="pa-taxhero__back" href="<?php echo esc_url( home_url( '/categories/' ) ); ?>">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
						<?php esc_html_e( 'Все категории', 'pickprism' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>
