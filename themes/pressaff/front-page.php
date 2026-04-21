<?php
/**
 * Pressaff — альтернативная главная страница.
 *
 * Не использует get_header() / get_footer() — вместо этого включает
 * кастомные парты напрямую, чтобы иметь полный контроль над разметкой.
 * wp_head(), wp_footer(), body_class(), language_attributes() — обязательны.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$parts_dir = PICKPRISM_DIR . 'themes/pressaff/parts/';

// ── Хедер (открывает <html>, <head>, <body>) ──────────────────────────
include $parts_dir . 'site-header.php';
?>

<div id="primary" class="pa-page">

	<?php
	// ── Hero ─────────────────────────────────────────────────────────────
	include $parts_dir . 'hero.php';

	// ── Latest row ───────────────────────────────────────────────────────
	include $parts_dir . 'latest-row.php';

	// ── Featured + card grid ─────────────────────────────────────────────
	include $parts_dir . 'featured.php';

	// ── Tabs по категориям ───────────────────────────────────────────────
	include $parts_dir . 'tabs.php';

	// ── Compact grid (популярное) ────────────────────────────────────────
	include $parts_dir . 'compact-grid.php';

	// ── Categories showcase ──────────────────────────────────────────────
	include $parts_dir . 'categories-showcase.php';

	// ── CTA Telegram / Reddit ────────────────────────────────────────────
	include $parts_dir . 'cta.php';
	?>

</div><!-- #primary -->

<?php
// ── Футер (закрывает </body>, </html>, вызывает wp_footer()) ─────────
include $parts_dir . 'site-footer.php';
