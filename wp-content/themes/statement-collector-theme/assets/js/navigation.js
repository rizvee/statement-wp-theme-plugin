(() => {
	'use strict';

	const activeDialogs = new Set();

	function updateBodyScrollLock() {
		if (activeDialogs.size > 0) {
			document.body.classList.add('statement-dialog-open');
			document.body.style.overflow = 'hidden';
		} else {
			document.body.classList.remove('statement-dialog-open');
			document.body.style.overflow = '';
		}
	}

	function bindDialog(dialogId, focusSelector = null) {
		const dialog = document.getElementById(dialogId);
		const triggers = Array.from(document.querySelectorAll(`[data-dialog-open="${dialogId}"]`));

		if (!dialog || triggers.length === 0) {
			return;
		}

		let returnTarget = null;

		function setExpanded(value) {
			for (const trigger of triggers) {
				if (trigger.hasAttribute('aria-expanded')) {
					trigger.setAttribute('aria-expanded', value);
				}
			}
		}

		function openDialog(trigger) {
			returnTarget = trigger;

			if (typeof dialog.showModal !== 'function' || dialog.open) {
				return;
			}

			dialog.showModal();
			setExpanded('true');
			activeDialogs.add(dialog);
			updateBodyScrollLock();

			const focusTarget = focusSelector ? dialog.querySelector(focusSelector) : null;
			if (focusTarget) {
				focusTarget.focus();
			}
		}

		function closeDialog() {
			if (dialog.open) {
				dialog.close();
			}
		}

		for (const trigger of triggers) {
			trigger.addEventListener('click', () => openDialog(trigger));
		}

		for (const closeButton of dialog.querySelectorAll('[data-dialog-close]')) {
			closeButton.addEventListener('click', () => closeDialog());
		}

		// Close on backdrop click
		dialog.addEventListener('click', (event) => {
			if (event.target === dialog) {
				closeDialog();
			}
		});

		dialog.addEventListener('close', () => {
			setExpanded('false');
			activeDialogs.delete(dialog);
			updateBodyScrollLock();
			const target = returnTarget;
			returnTarget = null;
			if (target && typeof target.focus === 'function' && target.isConnected) {
				target.focus();
			}
		});
	}

	bindDialog('statement-mobile-navigation');
	bindDialog('statement-search-dialog', '[data-dialog-focus]');

	// Close mobile menu automatically when viewport expands to desktop layout
	window.addEventListener('resize', () => {
		if (window.innerWidth >= 1024) {
			const mobileNav = document.getElementById('statement-mobile-navigation');
			if (mobileNav && mobileNav.open) {
				mobileNav.close();
			}
		}
	});
})();
