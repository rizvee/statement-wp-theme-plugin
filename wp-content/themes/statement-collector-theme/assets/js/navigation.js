(() => {
	'use strict';

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

			const focusTarget = focusSelector ? dialog.querySelector(focusSelector) : null;
			if (focusTarget) {
				focusTarget.focus();
			}
		}

		for (const trigger of triggers) {
			trigger.addEventListener('click', () => openDialog(trigger));
		}

		for (const closeButton of dialog.querySelectorAll('[data-dialog-close]')) {
			closeButton.addEventListener('click', () => {
				if (dialog.open) {
					dialog.close();
				}
			});
		}

		dialog.addEventListener('close', () => {
			setExpanded('false');
			const target = returnTarget;
			returnTarget = null;
			if (target?.isConnected) {
				target.focus();
			}
		});
	}

	bindDialog('statement-mobile-navigation');
	bindDialog('statement-search-dialog', '[data-dialog-focus]');
})();
