(() => {
	'use strict';
	document.querySelectorAll('.mce-search-form').forEach((form) => {
		const input = form.querySelector('input[type="search"]');
		const list = form.querySelector('[role="listbox"]');
		const status = form.querySelector('[aria-live]');
		const minimum = Number(form.dataset.min) || 2;
		let timer;
		let active = -1;
		let items = [];
		const escape = (value) => String(value).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));

		const close = () => {
			list.hidden = true;
			list.innerHTML = '';
			input.setAttribute('aria-expanded', 'false');
			input.removeAttribute('aria-activedescendant');
			active = -1;
			items = [];
		};
		const activate = (index) => {
			if (!items.length) return;
			active = (index + items.length) % items.length;
			items.forEach((item, position) => item.setAttribute('aria-selected', position === active ? 'true' : 'false'));
			input.setAttribute('aria-activedescendant', items[active].id);
			items[active].scrollIntoView({ block: 'nearest' });
		};
		const load = async () => {
			const query = input.value.trim();
			if (query.length < minimum) {
				status.textContent = query ? 'Continua a digitare.' : '';
				close();
				return;
			}
			status.textContent = 'Caricamento suggerimenti…';
			try {
				const response = await fetch(form.dataset.endpoint, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': form.dataset.nonce },
					body: JSON.stringify({ q: query }),
				});
				const data = await response.json();
				if (!response.ok) throw new Error(data.message || 'Errore');
				list.innerHTML = data.items.map((item, index) => '<li id="' + input.id + '-option-' + index + '" role="option" aria-selected="false"><a href="' + escape(item.url) + '"><span>' + escape(item.title) + '</span><small>' + escape(item.type) + '</small></a></li>').join('');
				items = [...list.querySelectorAll('[role="option"]')];
				list.hidden = items.length === 0;
				input.setAttribute('aria-expanded', items.length ? 'true' : 'false');
				status.textContent = items.length ? items.length + ' suggerimenti disponibili.' : 'Nessun suggerimento.';
			} catch (error) {
				status.textContent = 'Suggerimenti non disponibili.';
				close();
			}
		};
		input.addEventListener('input', () => {
			window.clearTimeout(timer);
			timer = window.setTimeout(load, 250);
		});
		input.addEventListener('keydown', (event) => {
			if (event.key === 'ArrowDown') { event.preventDefault(); activate(active + 1); }
			if (event.key === 'ArrowUp') { event.preventDefault(); activate(active - 1); }
			if (event.key === 'Escape') { event.preventDefault(); close(); input.focus(); }
			if (event.key === 'Enter' && active >= 0) { event.preventDefault(); items[active].querySelector('a').click(); }
		});
		document.addEventListener('click', (event) => { if (!form.contains(event.target)) close(); });
	});
})();
