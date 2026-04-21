<?php
/**
 * Pressaff — табы по категориям.
 * JS-логика: assets/src/js/pressaff-tabs.js.
 * Fallback без JS: прямые ссылки на архивы категорий.
 *
 * @package Pickprism
 */

defined( 'ABSPATH' ) || exit;

$tab_cats = pickprism_get_top_categories( 3 );

if ( empty( $tab_cats ) ) {
	return;
}

// Предзагружаем первую категорию для SSR (без JS работает сразу).
$first_cat = reset( $tab_cats );
$first_slug = $first_cat instanceof WP_Term ? $first_cat->slug : '';
?>
<section class="pa-tabs" aria-label="<?php esc_attr_e( 'Статьи по категориям', 'pickprism' ); ?>" data-pa-tabs>
	<div class="container">
		<div class="pa-section-head">
			<h2 class="pa-section-head__title"><?php esc_html_e( 'По категориям', 'pickprism' ); ?></h2>
			<div class="pa-section-head__line" aria-hidden="true"></div>
		</div>

		<!-- Табы-навигация -->
		<div class="pa-tabs__nav" role="tablist" aria-label="<?php esc_attr_e( 'Категории', 'pickprism' ); ?>">
			<?php foreach ( $tab_cats as $idx => $cat ) :
				if ( ! $cat instanceof WP_Term ) {
					continue;
				}
				$cat_link = get_term_link( $cat );
				if ( is_wp_error( $cat_link ) ) {
					continue;
				}
				$is_first = ( 0 === $idx );
				$tab_id   = 'pa-tab-' . sanitize_html_class( $cat->slug );
				?>
				<button
					class="pa-tabs__tab<?php echo $is_first ? ' is-active' : ''; ?>"
					type="button"
					role="tab"
					id="<?php echo esc_attr( $tab_id ); ?>"
					aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( 'panel-' . sanitize_html_class( $cat->slug ) ); ?>"
					data-pa-tab="<?php echo esc_attr( $cat->slug ); ?>"
				>
					<?php echo esc_html( $cat->name ); ?>
					<span aria-label="<?php
					echo esc_attr(
						sprintf(
							/* translators: %d: количество статей */
							_n( '%d статья', '%d статей', $cat->count, 'pickprism' ),
							$cat->count
						)
					);
					?>">
						(<?php echo esc_html( (string) $cat->count ); ?>)
					</span>
				</button>
			<?php endforeach; ?>
		</div>

		<!-- Панели -->
		<div class="pa-tabs__panels">
			<?php foreach ( $tab_cats as $idx => $cat ) :
				if ( ! $cat instanceof WP_Term ) {
					continue;
				}
				$cat_link  = get_term_link( $cat );
				$is_first  = ( 0 === $idx );
				$panel_id  = 'panel-' . sanitize_html_class( $cat->slug );
				$tab_id    = 'pa-tab-' . sanitize_html_class( $cat->slug );
				?>
				<div
					class="pa-tabs__panel<?php echo $is_first ? ' is-active' : ''; ?>"
					id="<?php echo esc_attr( $panel_id ); ?>"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
					data-pa-panel="<?php echo esc_attr( $cat->slug ); ?>"
					data-pa-cat="<?php echo esc_attr( $cat->slug ); ?>"
				>
					<!-- Лоадер (прячется после загрузки) -->
					<div class="pa-tabs__loading" data-tab-loading hidden>
						<span class="pa-spinner" aria-hidden="true"></span>
						<?php esc_html_e( 'Загружаем…', 'pickprism' ); ?>
					</div>

					<!-- Ошибка -->
					<div class="pa-tabs__error" data-tab-error hidden></div>

					<!-- Сетка карточек (JS заполнит) -->
					<div class="pa-tabs__grid" data-tab-grid>
						<?php if ( $is_first ) : ?>
							<?php
							// SSR первой вкладки — нет зависимости от JS.
							$tab_query = new WP_Query(
								array(
									'posts_per_page'      => 6,
									'post_status'         => 'publish',
									'post_type'           => 'post',
									'ignore_sticky_posts' => true,
									'no_found_rows'       => true,
									'cat'                 => (int) $cat->term_id,
								)
							);
							if ( $tab_query->have_posts() ) :
								while ( $tab_query->have_posts() ) :
									$tab_query->the_post();
									$p_id       = get_the_ID();
									$p_link     = get_permalink();
									$p_title    = get_the_title();
									$p_date     = get_the_date();
									$p_date_iso = get_the_date( 'c' );
									$p_author   = get_the_author();
									$p_thumb    = get_the_post_thumbnail_url( $p_id, 'pickprism-card' );

									$p_tags     = get_the_terms( $p_id, 'post_tag' );
									$p_tag      = is_array( $p_tags ) ? reset( $p_tags ) : null;
									$p_tag_link = ( $p_tag && ! is_wp_error( $p_tag ) ) ? get_term_link( $p_tag ) : null;

									$author_initial = $p_author ? mb_strtoupper( mb_substr( $p_author, 0, 1 ) ) : 'A';
									?>
									<article class="pa-card reveal">
										<?php if ( $p_thumb ) : ?>
											<a class="pa-card__media" href="<?php echo esc_url( $p_link ); ?>" tabindex="-1" aria-hidden="true">
												<img
													src="<?php echo esc_url( $p_thumb ); ?>"
													alt="<?php echo esc_attr( $p_title ); ?>"
													loading="lazy"
													decoding="async"
												>
											</a>
										<?php else : ?>
											<div class="pa-card__media">
												<div class="pa-card__media-placeholder" aria-hidden="true">
													<svg width="32" height="32" viewBox="0 0 24 24">
														<path fill="currentColor" d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2Zm-10-7-2.5 3.25L6 12l-3 4h18l-4.5-6-3 4-2.5-3.25Z"/>
													</svg>
												</div>
											</div>
										<?php endif; ?>

										<div class="pa-card__body">
											<?php if ( $p_tag && ! is_wp_error( $p_tag ) && $p_tag_link ) : ?>
												<a class="pa-card__badge" href="<?php echo esc_url( $p_tag_link ); ?>">
													<?php echo esc_html( $p_tag->name ); ?>
												</a>
											<?php endif; ?>

											<h3 class="pa-card__title">
												<a href="<?php echo esc_url( $p_link ); ?>" rel="bookmark">
													<?php echo esc_html( $p_title ); ?>
												</a>
											</h3>

											<div class="pa-card__meta">
												<span class="pa-card__author-avatar" aria-hidden="true">
													<?php echo esc_html( $author_initial ); ?>
												</span>
												<span><?php echo esc_html( $p_author ); ?></span>
												<span aria-hidden="true">·</span>
												<time datetime="<?php echo esc_attr( $p_date_iso ); ?>">
													<?php echo esc_html( $p_date ); ?>
												</time>
											</div>
										</div>
									</article>
								<?php endwhile; ?>
								<?php wp_reset_postdata(); ?>
							<?php else : ?>
								<p style="color:var(--c-text-subtle);font-size:var(--fs-sm);grid-column:1/-1">
									<?php esc_html_e( 'Пока нет статей в этой категории.', 'pickprism' ); ?>
								</p>
							<?php endif; ?>
							<?php
							// Помечаем что уже загружено — JS не будет повторять запрос.
							?>
							<?php if ( $tab_query->have_posts() || ( isset( $tab_query ) && $tab_query->post_count > 0 ) ) : ?>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<!-- Ссылка на архив (fallback без JS и для перехода) -->
					<?php if ( ! is_wp_error( $cat_link ) ) : ?>
						<div class="pa-tabs__fallback">
							<a href="<?php echo esc_url( $cat_link ); ?>">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: имя категории */
										__( 'Все материалы «%s» →', 'pickprism' ),
										$cat->name
									)
								);
								?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
// Помечаем первую панель как already-loaded для JS (чтобы не перезагружал).
if ( ! empty( $first_slug ) ) :
	?>
	<script>
	(function () {
		var panel = document.querySelector('[data-pa-panel="<?php echo esc_js( $first_slug ); ?>"]');
		if (panel) panel.dataset.loaded = 'true';
	}());
	</script>
<?php endif; ?>
