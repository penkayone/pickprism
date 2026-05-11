/**
 * Кнопка «Наверх».
 * Появляется при scrollY > THRESHOLD, кликом плавно скроллит к началу страницы.
 * Throttle через requestAnimationFrame, listener — passive.
 */
const THRESHOLD = 400;

export function initScrollTop() {
	const btn = document.querySelector('[data-scroll-top]');
	if (!btn) return;

	btn.removeAttribute('hidden');

	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	let ticking = false;

	const update = () => {
		btn.classList.toggle('is-visible', window.scrollY > THRESHOLD);
		ticking = false;
	};

	update();

	window.addEventListener(
		'scroll',
		() => {
			if (!ticking) {
				window.requestAnimationFrame(update);
				ticking = true;
			}
		},
		{ passive: true }
	);

	btn.addEventListener('click', () => {
		window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
	});
}
