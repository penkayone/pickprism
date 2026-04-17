<?php
/**
 * Генератор тестовых данных.
 * Запуск:
 *   wp pickprism fixtures             — с дефолтами (1000 постов, 18 категорий, 50 тегов)
 *   wp pickprism fixtures --posts=500 --categories=10 --tags=30 --sticky=5 --purge
 *
 * Картинки — Lorem Picsum, загружаются в медиабиблиотеку (не hotlink), чтобы работал lazy-loading
 * и srcset. Для ускорения можно выключить через --skip-images.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pickprism fixtures', 'Pickprism_Fixtures_Command' );
	WP_CLI::add_command( 'pickprism purge', 'Pickprism_Purge_Command' );
}

/**
 * WP-CLI команда: генерация фикстур.
 */
class Pickprism_Fixtures_Command {

	/** @var string[] */
	private const CATEGORY_POOL = array(
		'Искусственный интеллект', 'Разработка', 'DevOps', 'Базы данных',
		'Фронтенд', 'Бэкенд', 'Мобильная разработка', 'Кибербезопасность',
		'Облачные технологии', 'Блокчейн', 'Стартапы', 'Карьера',
		'UX/UI', 'Большие данные', 'Open Source', 'Архитектура',
		'Тестирование', 'Продуктивность',
	);

	/** @var string[] */
	private const TAG_POOL = array(
		'php', 'laravel', 'wordpress', 'javascript', 'typescript', 'react',
		'vue', 'node', 'python', 'django', 'fastapi', 'golang', 'rust',
		'docker', 'kubernetes', 'aws', 'gcp', 'azure', 'postgres', 'mysql',
		'redis', 'mongodb', 'elasticsearch', 'graphql', 'rest', 'grpc',
		'microservices', 'monolith', 'ddd', 'tdd', 'ci-cd', 'github-actions',
		'nginx', 'linux', 'bash', 'git', 'agile', 'scrum', 'teamwork',
		'performance', 'security', 'oauth', 'jwt', 'ssr', 'spa', 'pwa',
		'webpack', 'vite', 'css', 'scss', 'tailwind',
	);

	/** @var string[] */
	private const TITLE_STARTERS = array(
		'Как мы', 'Почему', '10 способов', 'Руководство по', 'Разбор:',
		'Сравнение', 'Опыт внедрения', 'Что нужно знать о', 'Эволюция',
		'Будущее', 'Практика', 'Архитектура', 'Оптимизация', 'Миграция на',
		'От нуля до продакшена:', 'За кулисами', 'Рецепт',
	);

	/** @var string[] */
	private const TITLE_SUBJECTS = array(
		'масштабируемых систем', 'CI/CD пайплайнов', 'высоконагруженных API',
		'Kubernetes в продакшене', 'монолитов и микросервисов', 'GraphQL-схем',
		'event-driven архитектуры', 'кэширования данных', 'наблюдаемости сервисов',
		'нулевого даунтайма', 'зелёного деплоя', 'feature flags',
		'оркестрации контейнеров', 'serverless-функций', 'edge-вычислений',
		'реактивных интерфейсов', 'доступности веб-приложений',
	);

	/**
	 * Генерация фикстур.
	 *
	 * ## OPTIONS
	 *
	 * [--posts=<number>]
	 * : Сколько постов создать. default: 1000
	 *
	 * [--categories=<number>]
	 * : Сколько категорий. default: 18
	 *
	 * [--tags=<number>]
	 * : Сколько тегов. default: 50
	 *
	 * [--sticky=<number>]
	 * : Сколько постов сделать закреплёнными. default: 7
	 *
	 * [--skip-images]
	 * : Пропустить загрузку картинок.
	 *
	 * [--purge]
	 * : Сначала удалить все существующие фикстуры (посты, тестовые категории/теги, медиа).
	 *
	 * ## EXAMPLES
	 *
	 *     wp pickprism fixtures
	 *     wp pickprism fixtures --posts=200 --skip-images
	 *     wp pickprism fixtures --purge --posts=1000
	 */
	public function __invoke( array $args, array $assoc ): void {
		$posts_count = isset( $assoc['posts'] ) ? max( 1, (int) $assoc['posts'] ) : 1000;
		$cats_count  = isset( $assoc['categories'] ) ? max( 1, (int) $assoc['categories'] ) : 18;
		$tags_count  = isset( $assoc['tags'] ) ? max( 1, (int) $assoc['tags'] ) : 50;
		$sticky_cnt  = isset( $assoc['sticky'] ) ? max( 0, (int) $assoc['sticky'] ) : 7;
		$with_images = ! isset( $assoc['skip-images'] );
		$do_purge    = isset( $assoc['purge'] );

		if ( $do_purge ) {
			( new Pickprism_Purge_Command() )( array(), array() );
		}

		// ---------- Категории ----------
		$cats_count = min( $cats_count, count( self::CATEGORY_POOL ) );
		$cat_ids    = array();
		WP_CLI::log( "Создаём категории: {$cats_count}" );
		foreach ( array_slice( self::CATEGORY_POOL, 0, $cats_count ) as $name ) {
			$cat_ids[] = pickprism_fixtures_ensure_term( $name, 'category' );
		}

		// ---------- Теги ----------
		$tags_count = min( $tags_count, count( self::TAG_POOL ) );
		$tag_ids    = array();
		WP_CLI::log( "Создаём теги: {$tags_count}" );
		foreach ( array_slice( self::TAG_POOL, 0, $tags_count ) as $slug ) {
			$tag_ids[] = pickprism_fixtures_ensure_term( $slug, 'post_tag' );
		}

		// ---------- Посты ----------
		WP_CLI::log( "Создаём посты: {$posts_count}" );
		$progress = \WP_CLI\Utils\make_progress_bar( 'Посты', $posts_count );

		$created_ids = array();
		$created_dt  = time() - ( 180 * DAY_IN_SECONDS );

		// Чтобы не плодить аттачменты для 1000 постов — переиспользуем пул медиа.
		$media_pool_size = $with_images ? 30 : 0;
		$media_pool      = $with_images ? pickprism_fixtures_ensure_media_pool( $media_pool_size ) : array();

		// Выключаем тяжёлые хуки на время массовой вставки.
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
		remove_action( 'save_post', 'pickprism_flush_widget_cache' );
		$suspended = wp_suspend_cache_invalidation( true );

		for ( $i = 0; $i < $posts_count; $i++ ) {
			$title = self::TITLE_STARTERS[ array_rand( self::TITLE_STARTERS ) ] . ' ' .
				self::TITLE_SUBJECTS[ array_rand( self::TITLE_SUBJECTS ) ] .
				( ( $i % 7 === 0 ) ? ': часть ' . ( 1 + ( $i % 5 ) ) : '' );

			$content = pickprism_fixtures_lorem_ru( wp_rand( 6, 14 ) );
			$excerpt = pickprism_fixtures_lorem_ru( 1, 26 );

			$post_date  = gmdate( 'Y-m-d H:i:s', $created_dt + ( $i * HOUR_IN_SECONDS * 4 ) + wp_rand( 0, HOUR_IN_SECONDS * 3 ) );
			$post_cat   = array( $cat_ids[ array_rand( $cat_ids ) ] );
			// ~30% постов — в двух категориях для реалистичности.
			if ( wp_rand( 1, 100 ) <= 30 ) {
				$post_cat[] = $cat_ids[ array_rand( $cat_ids ) ];
				$post_cat   = array_values( array_unique( $post_cat ) );
			}

			$post_tags = array();
			$tag_n     = wp_rand( 1, 4 );
			for ( $t = 0; $t < $tag_n; $t++ ) {
				$post_tags[] = $tag_ids[ array_rand( $tag_ids ) ];
			}
			$post_tags = array_values( array_unique( $post_tags ) );

			$post_id = wp_insert_post(
				array(
					'post_title'    => $title,
					'post_content'  => $content,
					'post_excerpt'  => $excerpt,
					'post_status'   => 'publish',
					'post_author'   => 1,
					'post_type'     => 'post',
					'post_date'     => $post_date,
					'post_date_gmt' => $post_date,
					'post_category' => $post_cat,
					'tags_input'    => $post_tags,
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				WP_CLI::warning( 'Ошибка вставки поста: ' . $post_id->get_error_message() );
				$progress->tick();
				continue;
			}

			if ( $with_images && ! empty( $media_pool ) ) {
				set_post_thumbnail( $post_id, $media_pool[ $i % count( $media_pool ) ] );
			}

			$created_ids[] = (int) $post_id;
			$progress->tick();
		}

		$progress->finish();

		// Возвращаем всё обратно.
		wp_suspend_cache_invalidation( $suspended );
		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );
		add_action( 'save_post', 'pickprism_flush_widget_cache' );

		// ---------- Sticky ----------
		if ( $sticky_cnt > 0 && ! empty( $created_ids ) ) {
			WP_CLI::log( "Ставим sticky: {$sticky_cnt}" );
			shuffle( $created_ids );
			$sticky_ids = array_slice( $created_ids, 0, $sticky_cnt );
			update_option( 'sticky_posts', array_map( 'intval', $sticky_ids ) );
			foreach ( $sticky_ids as $idx => $sid ) {
				update_post_meta( $sid, PICKPRISM_STICKY_META, $idx );
			}
		}

		pickprism_flush_widget_cache();
		wp_cache_flush();

		WP_CLI::success(
			sprintf(
				'Готово: постов %d, категорий %d, тегов %d, sticky %d',
				count( $created_ids ),
				count( $cat_ids ),
				count( $tag_ids ),
				$sticky_cnt
			)
		);
	}
}

/**
 * Удаление всех фикстур.
 */
class Pickprism_Purge_Command {

	/**
	 * ## EXAMPLES
	 *
	 *     wp pickprism purge
	 */
	public function __invoke( array $args, array $assoc ): void {
		global $wpdb;

		WP_CLI::log( 'Удаляем все посты…' );
		$post_ids = (array) $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post'"
		);
		foreach ( $post_ids as $id ) {
			wp_delete_post( (int) $id, true );
		}
		WP_CLI::log( sprintf( 'Удалено постов: %d', count( $post_ids ) ) );

		WP_CLI::log( 'Удаляем тестовые вложения Pickprism…' );
		$attachments = (array) $wpdb->get_col(
			$wpdb->prepare(
				"SELECT p.ID
					FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
					WHERE p.post_type = %s AND pm.meta_key = %s",
				'attachment',
				'_pickprism_fixture'
			)
		);
		foreach ( $attachments as $id ) {
			wp_delete_attachment( (int) $id, true );
		}
		WP_CLI::log( sprintf( 'Удалено вложений: %d', count( $attachments ) ) );

		WP_CLI::log( 'Сбрасываем sticky_posts…' );
		update_option( 'sticky_posts', array() );

		pickprism_flush_widget_cache();
		wp_cache_flush();

		WP_CLI::success( 'Очистка завершена.' );
	}
}

/**
 * Создаёт или возвращает ID термина.
 */
function pickprism_fixtures_ensure_term( string $name, string $taxonomy ): int {
	$existing = term_exists( $name, $taxonomy );
	if ( is_array( $existing ) && isset( $existing['term_id'] ) ) {
		return (int) $existing['term_id'];
	}
	$created = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $created ) ) {
		return 0;
	}
	return (int) $created['term_id'];
}

/**
 * Загружает в медиабиблиотеку пул картинок с Lorem Picsum.
 *
 * @return int[] IDs attachments
 */
function pickprism_fixtures_ensure_media_pool( int $size ): array {
	global $wpdb;

	$existing = (array) $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s AND pm.meta_key = %s
				ORDER BY p.ID ASC
				LIMIT %d",
			'attachment',
			'_pickprism_fixture',
			$size
		)
	);
	$existing = array_map( 'intval', $existing );

	if ( count( $existing ) >= $size ) {
		return array_slice( $existing, 0, $size );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$need     = $size - count( $existing );
	WP_CLI::log( "Скачиваем картинки: {$need}" );
	$progress = \WP_CLI\Utils\make_progress_bar( 'Медиа', $need );

	for ( $i = 0; $i < $need; $i++ ) {
		$seed = wp_rand( 1, 10000 );
		$url  = "https://picsum.photos/seed/pickprism{$seed}/1200/750.jpg";

		$tmp = download_url( $url, 20 );
		if ( is_wp_error( $tmp ) ) {
			WP_CLI::warning( 'Не удалось скачать ' . $url . ': ' . $tmp->get_error_message() );
			$progress->tick();
			continue;
		}

		$file = array(
			'name'     => 'pickprism-' . $seed . '.jpg',
			'tmp_name' => $tmp,
		);

		$att_id = media_handle_sideload( $file, 0, 'Pickprism fixture' );

		if ( is_wp_error( $att_id ) ) {
			@unlink( $tmp ); // phpcs:ignore
			WP_CLI::warning( 'media_handle_sideload: ' . $att_id->get_error_message() );
			$progress->tick();
			continue;
		}

		update_post_meta( (int) $att_id, '_pickprism_fixture', 1 );
		update_post_meta( (int) $att_id, '_wp_attachment_image_alt', 'Pickprism demo image' );
		$existing[] = (int) $att_id;
		$progress->tick();
	}

	$progress->finish();
	return $existing;
}

/**
 * Возвращает параграфы псевдо-русского Lorem.
 */
function pickprism_fixtures_lorem_ru( int $paragraphs, int $words_per = 60 ): string {
	static $pool = null;
	if ( null === $pool ) {
		$pool = array(
			'команда', 'разработка', 'архитектура', 'сервис', 'деплой', 'микросервис',
			'контейнер', 'оркестрация', 'кэш', 'база', 'запрос', 'индекс', 'миграция',
			'фронтенд', 'бэкенд', 'интерфейс', 'компонент', 'состояние', 'пропс',
			'код', 'рефакторинг', 'тест', 'покрытие', 'производительность',
			'нагрузка', 'балансировщик', 'очередь', 'событие', 'обработчик',
			'конфигурация', 'окружение', 'переменная', 'секрет', 'токен',
			'аутентификация', 'авторизация', 'роль', 'доступ', 'пользователь',
			'продукт', 'метрика', 'мониторинг', 'алерт', 'журнал', 'трейс',
			'инцидент', 'откат', 'релиз', 'ветка', 'коммит', 'ревью', 'пул-реквест',
			'спринт', 'задача', 'бэклог', 'эпик', 'оценка', 'приоритет',
		);
	}

	$out = array();
	for ( $p = 0; $p < $paragraphs; $p++ ) {
		$words = array();
		for ( $w = 0; $w < $words_per; $w++ ) {
			$word = $pool[ array_rand( $pool ) ];
			if ( 0 === $w ) {
				$word = function_exists( 'mb_convert_case' )
					? mb_convert_case( $word, MB_CASE_TITLE, 'UTF-8' )
					: ucfirst( $word );
			}
			$words[] = $word;
		}
		$sentence = implode( ' ', $words );
		// Разбиваем на 2-3 предложения.
		$sentence = preg_replace_callback(
			'/ /',
			static function () {
				static $c = 0;
				$c++;
				if ( $c % 14 === 0 ) {
					return '. ';
				}
				return ' ';
			},
			$sentence
		);
		$out[] = $sentence . '.';
	}
	return implode( "\n\n", $out );
}
