/**
 * Statement Collector Hero Slider
 *
 * Lightweight, zero-dependency vanilla JavaScript slider for the Statement theme.
 * Features:
 * - Touch swipe gestures (pointer/touch)
 * - Keyboard navigation (Left/Right arrows)
 * - Autoplay with pause on hover/focus and Page Visibility API
 * - Respects prefers-reduced-motion media query
 * - Accessible ARIA attributes and live region
 */

(function () {
	'use strict';

	function initHeroSlider() {
		const slider = document.querySelector('.statement-hero-slider');
		if (!slider) {
			return;
		}

		const slides = Array.from(slider.querySelectorAll('.statement-hero-slide'));
		if (slides.length <= 1) {
			return;
		}

		const prevBtn = slider.querySelector('.statement-hero-slider__control--prev');
		const nextBtn = slider.querySelector('.statement-hero-slider__control--next');
		const counterCurrent = slider.querySelector('.statement-hero-slider__counter-current');
		const counterTotal = slider.querySelector('.statement-hero-slider__counter-total');
		const indicators = Array.from(slider.querySelectorAll('.statement-hero-slider__dot'));
		const liveRegion = slider.querySelector('.statement-hero-slider__live');

		let currentIndex = 0;
		let autoplayTimer = null;
		const autoplayDelay = 6500;
		const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		if (counterTotal) {
			counterTotal.textContent = String(slides.length).padStart(2, '0');
		}

		function goToSlide(index) {
			if (index < 0) {
				index = slides.length - 1;
			} else if (index >= slides.length) {
				index = 0;
			}

			if (index === currentIndex && slides[currentIndex].classList.contains('is-active')) {
				return;
			}

			slides.forEach(function (slide, i) {
				const isActive = i === index;
				slide.classList.toggle('is-active', isActive);
				slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
				slide.setAttribute('tabindex', isActive ? '0' : '-1');
			});

			indicators.forEach(function (dot, i) {
				const isSelected = i === index;
				dot.classList.toggle('is-active', isSelected);
				dot.setAttribute('aria-selected', isSelected ? 'true' : 'false');
			});

			currentIndex = index;

			if (counterCurrent) {
				counterCurrent.textContent = String(currentIndex + 1).padStart(2, '0');
			}

			if (liveRegion) {
				const heading = slides[currentIndex].querySelector('.statement-hero-slide__heading');
				const title = heading ? heading.textContent.trim() : `Slide ${currentIndex + 1}`;
				liveRegion.textContent = `Slide ${currentIndex + 1} of ${slides.length}: ${title}`;
			}
		}

		function nextSlide() {
			goToSlide(currentIndex + 1);
		}

		function prevSlide() {
			goToSlide(currentIndex - 1);
		}

		function startAutoplay() {
			if (prefersReducedMotion || autoplayTimer) {
				return;
			}
			autoplayTimer = setInterval(nextSlide, autoplayDelay);
		}

		function stopAutoplay() {
			if (autoplayTimer) {
				clearInterval(autoplayTimer);
				autoplayTimer = null;
			}
		}

		function resetAutoplay() {
			stopAutoplay();
			startAutoplay();
		}

		// Controls event listeners
		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				nextSlide();
				resetAutoplay();
			});
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				prevSlide();
				resetAutoplay();
			});
		}

		indicators.forEach(function (dot, idx) {
			dot.addEventListener('click', function () {
				goToSlide(idx);
				resetAutoplay();
			});
		});

		// Hover and focus listeners to pause autoplay
		slider.addEventListener('mouseenter', stopAutoplay);
		slider.addEventListener('mouseleave', startAutoplay);
		slider.addEventListener('focusin', stopAutoplay);
		slider.addEventListener('focusout', startAutoplay);

		// Page Visibility API
		document.addEventListener('visibilitychange', function () {
			if (document.visibilityState === 'hidden') {
				stopAutoplay();
			} else {
				startAutoplay();
			}
		});

		// Keyboard navigation
		slider.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight' || e.key === 'Right') {
				e.preventDefault();
				nextSlide();
				resetAutoplay();
			} else if (e.key === 'ArrowLeft' || e.key === 'Left') {
				e.preventDefault();
				prevSlide();
				resetAutoplay();
			}
		});

		// Touch swipe gestures
		let touchStartX = 0;
		let touchStartY = 0;
		let touchEndX = 0;
		let touchEndY = 0;
		const minSwipeDistance = 45;

		slider.addEventListener(
			'touchstart',
			function (e) {
				if (e.touches.length === 1) {
					touchStartX = e.touches[0].screenX;
					touchStartY = e.touches[0].screenY;
				}
			},
			{ passive: true }
		);

		slider.addEventListener(
			'touchend',
			function (e) {
				if (e.changedTouches.length === 1) {
					touchEndX = e.changedTouches[0].screenX;
					touchEndY = e.changedTouches[0].screenY;
					handleSwipe();
				}
			},
			{ passive: true }
		);

		function handleSwipe() {
			const deltaX = touchEndX - touchStartX;
			const deltaY = touchEndY - touchStartY;

			// Ensure horizontal swipe is dominant
			if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) >= minSwipeDistance) {
				if (deltaX < 0) {
					// Swiped left -> next
					nextSlide();
				} else {
					// Swiped right -> prev
					prevSlide();
				}
				resetAutoplay();
			}
		}

		// Initialize state and autoplay
		goToSlide(0);
		startAutoplay();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initHeroSlider);
	} else {
		initHeroSlider();
	}
})();
