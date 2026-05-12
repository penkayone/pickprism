<?php
/**
 * Универсальный логотип сайта.
 * Если в админке (Внешний вид → Настроить → Свойства сайта → Логотип)
 * задан custom-logo — отрисовывается <img>. Иначе fallback на
 * квадратную марку с первой буквой названия + текст названия.
 *
 * Args:
 *   - variant: 'default' | 'light' — для тёмного фона (footer).
 *   - tabindex: '-1' для скрытых случаев (drawer).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$pickprism_logo_args = is_array( $args ?? null ) ? $args : array();
$pickprism_logo_variant  = isset( $pickprism_logo_args['variant'] ) ? (string) $pickprism_logo_args['variant'] : 'default';
$pickprism_logo_tabindex = isset( $pickprism_logo_args['tabindex'] ) ? (string) $pickprism_logo_args['tabindex'] : '';

$pickprism_logo_id = (int) get_theme_mod( 'custom_logo' );
$pickprism_logo_class = 'pa-logo';
if ( $pickprism_logo_id ) {
	$pickprism_logo_class .= ' pa-logo--img';
}
if ( $pickprism_logo_variant === 'light' ) {
	$pickprism_logo_class .= ' pa-logo--light';
}
?>
<a
	class="<?php echo esc_attr( $pickprism_logo_class ); ?>"
	href="<?php echo esc_url( home_url( '/' ) ); ?>"
	rel="home"
	<?php echo $pickprism_logo_tabindex !== '' ? 'tabindex="' . esc_attr( $pickprism_logo_tabindex ) . '"' : ''; ?>
>
	<?php if ( $pickprism_logo_id ) : ?>
		<?php
		echo wp_get_attachment_image(
			$pickprism_logo_id,
			'full',
			false,
			array(
				'class' => 'pa-logo__img',
				'alt'   => esc_attr( get_bloginfo( 'name' ) ),
			)
		);
		?>
	<?php else : ?>
		<?php
		$pickprism_logo_letter = strtoupper( mb_substr( get_bloginfo( 'name' ), 0, 1, 'UTF-8' ) );
		if ( $pickprism_logo_letter === '' ) {
			$pickprism_logo_letter = 'P';
		}
		?>
		<span class="pa-logo__mark" aria-hidden="true"><?php echo esc_html( $pickprism_logo_letter ); ?></span>
		<span class="pa-logo__text"><?php bloginfo( 'name' ); ?></span>
	<?php endif; ?>
</a>
