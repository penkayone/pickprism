<?php
/**
 * Страница «Все категории» — /categories/.
 *
 * Регистрирует rewrite-rule, query-var и template_include для отдельной
 * страницы со списком всех категорий блога. Шаблон лежит в теме:
 * templates/all-categories.php.
 *
 * Flush rewrite-rules выполняется один раз при первой загрузке после
 * активации/обновления темы (контролируется флагом-transient).
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

/**
 * Имя query-var, по которому шаблонный роутинг определяет нашу страницу.
 */
const PICKPRISM_CATEGORIES_QV = 'pickprism_categories';

/**
 * Имя transient-флага «правила уже сброшены».
 * Если флаг отсутствует — на init вызовем flush_rewrite_rules() ОДИН раз.
 */
const PICKPRISM_CATEGORIES_FLUSH_KEY = 'pickprism_categories_rewrite_flushed';

/**
 * Регистрирует rewrite-rule `^categories/?$` → `index.php?{qv}=1`.
 * Вешается на init с приоритетом 10 — стандартный момент для add_rewrite_rule.
 */
function pickprism_categories_register_rewrite(): void {
	add_rewrite_rule(
		'^categories/?$',
		'index.php?' . PICKPRISM_CATEGORIES_QV . '=1',
		'top'
	);
}
add_action( 'init', 'pickprism_categories_register_rewrite' );

/**
 * Регистрирует public query-var, чтобы WP передавал значение из
 * rewrite-rule в WP_Query / get_query_var.
 *
 * @param array<int, string> $vars
 * @return array<int, string>
 */
function pickprism_categories_register_query_var( array $vars ): array {
	$vars[] = PICKPRISM_CATEGORIES_QV;
	return $vars;
}
add_filter( 'query_vars', 'pickprism_categories_register_query_var' );

/**
 * Подменяет шаблон на templates/all-categories.php, если установлен
 * наш query-var. Используется фильтр template_include, чтобы не плодить
 * лишние файлы в иерархии шаблонов.
 *
 * @param string $template
 * @return string
 */
function pickprism_categories_template_include( string $template ): string {
	if ( (int) get_query_var( PICKPRISM_CATEGORIES_QV ) === 1 ) {
		$candidate = PICKPRISM_DIR . 'templates/all-categories.php';
		if ( is_readable( $candidate ) ) {
			// Сообщаем WP, что 404 не нужен — это валидная страница.
			global $wp_query;
			if ( $wp_query instanceof WP_Query ) {
				$wp_query->is_404 = false;
				status_header( 200 );
			}
			return $candidate;
		}
	}
	return $template;
}
add_filter( 'template_include', 'pickprism_categories_template_include' );

/**
 * Сброс rewrite-rules один раз — на первой загрузке после активации/обновления.
 * Используется transient-флаг, чтобы избежать flush на каждом запросе
 * (он дорогой: пересобирает все правила).
 *
 * Дополнительно вешаемся на after_switch_theme, чтобы при смене темы
 * пользователь не видел 404 по /categories/ до первого фронт-запроса.
 */
function pickprism_categories_maybe_flush_rewrite(): void {
	if ( get_transient( PICKPRISM_CATEGORIES_FLUSH_KEY ) ) {
		return;
	}

	// Регистрируем правило ДО flush — иначе оно не попадёт в свежий набор.
	pickprism_categories_register_rewrite();
	flush_rewrite_rules( false );

	// Кэшируем флаг навсегда (transient без срока, удаляется только нашим
	// инвалидатором). Используем длинный TTL вместо 0, чтобы избежать
	// автоматического autoload в options-таблицу.
	set_transient( PICKPRISM_CATEGORIES_FLUSH_KEY, 1, YEAR_IN_SECONDS );
}
add_action( 'init', 'pickprism_categories_maybe_flush_rewrite', 20 );

/**
 * Flush на смене темы — чтобы правило сразу появилось при активации.
 */
function pickprism_categories_on_theme_switch(): void {
	delete_transient( PICKPRISM_CATEGORIES_FLUSH_KEY );
	pickprism_categories_register_rewrite();
	flush_rewrite_rules( false );
	set_transient( PICKPRISM_CATEGORIES_FLUSH_KEY, 1, YEAR_IN_SECONDS );
}
add_action( 'after_switch_theme', 'pickprism_categories_on_theme_switch' );
