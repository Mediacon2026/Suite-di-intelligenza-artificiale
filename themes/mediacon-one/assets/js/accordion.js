(() => {
	'use strict';

	document.querySelectorAll('[data-accordion]').forEach((accordion) => {
		accordion.querySelectorAll('button[aria-controls]').forEach((button) => {
			button.addEventListener('click', () => {
				const panel = document.getElementById(button.getAttribute('aria-controls'));
				if (!panel) return;
				const open = button.getAttribute('aria-expanded') === 'true';
				button.setAttribute('aria-expanded', String(!open));
				panel.hidden = open;
			});
		});
	});
})();
