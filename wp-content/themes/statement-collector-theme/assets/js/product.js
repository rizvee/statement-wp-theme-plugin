/**
 * Statement Product Page Interactions:
 * - Luxury Size Selector Button synchronization with WooCommerce variations.
 * - Accessible Size Guide modal handling.
 * - Mobile Sticky Add-to-Bag bar.
 */
document.addEventListener('DOMContentLoaded', function() {
	// 1. Synchronize Luxury Size Buttons with WooCommerce Variation Selects
	var variationForms = document.querySelectorAll('form.variations_form');
	variationForms.forEach(function(form) {
		var selects = form.querySelectorAll('table.variations select');
		selects.forEach(function(select) {
			var wrapper = document.createElement('div');
			wrapper.className = 'statement-variation-buttons-container';

			var group = document.createElement('div');
			group.className = 'statement-size-button-group';
			group.setAttribute('role', 'radiogroup');
			group.setAttribute('aria-label', select.getAttribute('aria-label') || 'Select Size');

			var options = select.querySelectorAll('option');
			var hasButtons = false;

			options.forEach(function(opt) {
				if (!opt.value) return;
				hasButtons = true;

				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'statement-size-btn';
				btn.setAttribute('role', 'radio');
				btn.setAttribute('aria-checked', opt.selected ? 'true' : 'false');
				btn.setAttribute('data-value', opt.value);
				btn.textContent = opt.textContent.trim();

				if (opt.selected) {
					btn.classList.add('is-selected');
				}

				btn.addEventListener('click', function(e) {
					e.preventDefault();
					select.value = opt.value;

					// Dispatch native events
					var changeEvent = new Event('change', { bubbles: true });
					select.dispatchEvent(changeEvent);

					if (window.jQuery) {
						window.jQuery(select).trigger('change');
					}

					// Update button states
					group.querySelectorAll('.statement-size-btn').forEach(function(b) {
						b.classList.remove('is-selected');
						b.setAttribute('aria-checked', 'false');
					});
					btn.classList.add('is-selected');
					btn.setAttribute('aria-checked', 'true');
				});

				group.appendChild(btn);
			});

			if (hasButtons) {
				select.classList.add('statement-native-select--hidden');
				wrapper.appendChild(group);
				select.parentNode.insertBefore(wrapper, select.nextSibling);

				// Listen to external reset/changes on native select
				select.addEventListener('change', function() {
					group.querySelectorAll('.statement-size-btn').forEach(function(b) {
						var isMatch = b.getAttribute('data-value') === select.value;
						b.classList.toggle('is-selected', isMatch);
						b.setAttribute('aria-checked', isMatch ? 'true' : 'false');
					});
				});
			}
		});
	});

	// 2. Size Guide Accessible Modal Management
	var sizeGuideOpenBtn  = document.getElementById('statement-size-guide-open');
	var sizeGuideDialog   = document.getElementById('statement-size-guide-dialog');
	var sizeGuideCloseBtn = document.getElementById('statement-size-guide-close');

	if (sizeGuideOpenBtn && sizeGuideDialog) {
		function openSizeGuide() {
			if (typeof sizeGuideDialog.showModal === 'function') {
				sizeGuideDialog.showModal();
			} else {
				sizeGuideDialog.setAttribute('open', 'true');
			}
			document.body.classList.add('statement-dialog-open');
			if (sizeGuideCloseBtn) {
				sizeGuideCloseBtn.focus();
			}
		}

		function closeSizeGuide() {
			if (typeof sizeGuideDialog.close === 'function') {
				sizeGuideDialog.close();
			} else {
				sizeGuideDialog.removeAttribute('open');
			}
			document.body.classList.remove('statement-dialog-open');
			sizeGuideOpenBtn.focus();
		}

		sizeGuideOpenBtn.addEventListener('click', openSizeGuide);

		if (sizeGuideCloseBtn) {
			sizeGuideCloseBtn.addEventListener('click', closeSizeGuide);
		}

		sizeGuideDialog.addEventListener('click', function(e) {
			if (e.target === sizeGuideDialog) {
				closeSizeGuide();
			}
		});

		sizeGuideDialog.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' || e.key === 'Esc') {
				closeSizeGuide();
			}
		});
	}

	// 3. Mobile Sticky Add-to-Bag Bar
	var mainAddBtn = document.querySelector('.single_add_to_cart_button');
	if (mainAddBtn) {
		var stickyBar = document.querySelector('.statement-mobile-sticky-bar');
		if (!stickyBar) {
			stickyBar = document.createElement('div');
			stickyBar.className = 'statement-mobile-sticky-bar';

			var priceEl = document.querySelector('.statement-product__price .woocommerce-Price-amount') || document.querySelector('.statement-product__price');
			var priceHtml = priceEl ? priceEl.innerHTML : '';

			stickyBar.innerHTML = '<div class="statement-mobile-sticky-bar__inner">' +
				'<div class="statement-mobile-sticky-bar__price">' + priceHtml + '</div>' +
				'<button type="button" class="statement-mobile-sticky-bar__btn">' +
					mainAddBtn.textContent.trim() +
				'</button>' +
			'</div>';

			document.body.appendChild(stickyBar);

			var stickyBtn = stickyBar.querySelector('.statement-mobile-sticky-bar__btn');
			if (stickyBtn) {
				stickyBtn.addEventListener('click', function() {
					mainAddBtn.click();
				});
			}
		}

		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (entry.isIntersecting) {
						stickyBar.classList.remove('is-visible');
					} else {
						// Only show if we scrolled past the button downwards
						if (entry.boundingClientRect.top < 0) {
							stickyBar.classList.add('is-visible');
						} else {
							stickyBar.classList.remove('is-visible');
						}
					}
				});
			}, { threshold: 0.1 });

			observer.observe(mainAddBtn);
		} else {
			window.addEventListener('scroll', function() {
				var rect = mainAddBtn.getBoundingClientRect();
				if (rect.bottom < 0) {
					stickyBar.classList.add('is-visible');
				} else {
					stickyBar.classList.remove('is-visible');
				}
			}, { passive: true });
		}
	}
});
