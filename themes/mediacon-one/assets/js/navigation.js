(() => {
	'use strict';

	const body = document.body;
	const menuButton = document.querySelector('[data-menu-toggle]');
	const navigation = document.querySelector('[data-primary-navigation]');
	const searchButton = document.querySelector('[data-search-toggle]');
	const searchPanel = document.querySelector('[data-search-panel]');
	const searchClose = document.querySelector('[data-search-close]');

	const setExpanded = (button, expanded) => {
		if (button) {
			button.setAttribute('aria-expanded', String(expanded));
		}
	};

	const closeMenu = () => {
		if (!menuButton || !navigation) return;
		navigation.classList.remove('is-open');
		body.classList.remove('menu-open');
		setExpanded(menuButton, false);
	};

	const closeSearch = () => {
		if (!searchButton || !searchPanel) return;
		searchPanel.hidden = true;
		setExpanded(searchButton, false);
	};

	menuButton?.addEventListener('click', () => {
		const open = !navigation?.classList.contains('is-open');
		navigation?.classList.toggle('is-open', open);
		body.classList.toggle('menu-open', open);
		setExpanded(menuButton, open);
	});

	searchButton?.addEventListener('click', () => {
		if (!searchPanel) return;
		const open = searchPanel.hidden;
		searchPanel.hidden = !open;
		setExpanded(searchButton, open);
		if (open) searchPanel.querySelector('input')?.focus();
	});

	searchClose?.addEventListener('click', () => {
		closeSearch();
		searchButton?.focus();
	});

	navigation?.querySelectorAll('.menu-item-has-children').forEach((item, index) => {
		const submenu = item.querySelector(':scope > .sub-menu');
		const link = item.querySelector(':scope > a');
		if (!submenu || !link) return;
		const id = submenu.id || `submenu-${index + 1}`;
		submenu.id = id;
		const toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'submenu-toggle';
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-controls', id);
		toggle.setAttribute('aria-label', `${link.textContent.trim()}: sottomenu`);
		toggle.innerHTML = '<span aria-hidden="true">⌄</span>';
		link.after(toggle);
		toggle.addEventListener('click', () => {
			const open = !submenu.classList.contains('is-open');
			submenu.classList.toggle('is-open', open);
			setExpanded(toggle, open);
		});
	});

	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') return;
		closeMenu();
		closeSearch();
	});

	window.addEventListener('resize', () => {
		if (window.matchMedia('(min-width: 72rem)').matches) closeMenu();
	}, { passive: true });
})();
