(() => {
	'use strict';
	const root = document.querySelector('.mce-preventivo');
	if (!root) return;
	const form = root.querySelector('form');
	const sections = [...form.querySelectorAll('[data-step]')];
	const storageKey = 'mediacon-preventivo-state-v1';
	const config = JSON.parse(root.dataset.config || '{}');
	const expenseLabels = { registered_letter: 'Raccomandata', digital_signature: 'Firma digitale', extra_copy: 'Copia ulteriore del verbale' };
	let step = 1;
	let state = { parties: [] };
	const escape = (value) => String(value).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
	const money = (value) => new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(Number(value) || 0);
	const save = () => localStorage.setItem(storageKey, JSON.stringify(state));
	const findParty = (id) => state.parties.find((item) => item.id === id);
	try { state = { parties: [], ...JSON.parse(localStorage.getItem(storageKey) || '{}') }; } catch (error) { localStorage.removeItem(storageKey); }

	const addParty = (role) => {
		const count = state.parties.filter((item) => item.role === role).length + 1;
		state.parties.push({ id: role + '-' + Date.now() + '-' + count, name: (role === 'claimant' ? 'Parte istante ' : 'Parte invitata ') + count, role, status: 'present', centerCount: 1, expenses: [], paid: 0 });
		save();
		renderDynamic();
	};
	if (!state.parties.some((item) => item.role === 'claimant')) addParty('claimant');

	const renderParties = (role) => {
		const target = root.querySelector('[data-party-list="' + role + '"]');
		target.innerHTML = state.parties.filter((item) => item.role === role).map((item) => {
			const statuses = role === 'invited' ? '<label>Partecipazione <select data-field="status"><option value="present"' + (item.status === 'present' ? ' selected' : '') + '>Presente / aderente</option><option value="absent"' + (item.status === 'absent' ? ' selected' : '') + '>Assente</option><option value="nonadherent"' + (item.status === 'nonadherent' ? ' selected' : '') + '>Non aderente</option></select></label>' : '';
			return '<fieldset data-party="' + escape(item.id) + '"><legend>' + escape(item.name) + '</legend><label>Denominazione <input data-field="name" value="' + escape(item.name) + '" required></label>' + statuses + '<button type="button" data-remove-party="' + escape(item.id) + '">Rimuovi</button></fieldset>';
		}).join('') || '<p>Nessuna parte inserita.</p>';
	};
	const renderCenters = () => {
		root.querySelector('[data-centers]').innerHTML = state.parties.map((item) => '<label>' + escape(item.name) + ' — numero centri<input type="number" min="1" max="20" value="' + (Number(item.centerCount) || 1) + '" data-centers-for="' + escape(item.id) + '"></label>').join('');
	};
	const renderExpenses = () => {
		const defaults = config.expenses || {};
		root.querySelector('[data-expenses]').innerHTML = state.parties.map((item) => {
			const choices = Object.entries(expenseLabels).map(([key, label]) => '<label><input type="checkbox" data-expense="' + escape(key) + '" ' + (item.expenses.some((row) => row.key === key) ? 'checked' : '') + '> ' + escape(label) + ' (' + money(defaults[key]) + ')</label>').join('');
			return '<fieldset data-expenses-for="' + escape(item.id) + '"><legend>' + escape(item.name) + '</legend>' + choices + '<label>Altra spesa documentata — descrizione<input data-custom-label value="' + escape(item.customLabel || '') + '"></label><label>Importo €<input type="number" min="0" step="0.01" data-custom-amount value="' + (Number(item.customAmount) || 0) + '"></label></fieldset>';
		}).join('');
	};
	const renderPaid = () => { root.querySelector('[data-paid]').innerHTML = state.parties.map((item) => '<label>' + escape(item.name) + ' — già versato €<input type="number" min="0" step="0.01" value="' + (Number(item.paid) || 0) + '" data-paid-for="' + escape(item.id) + '"></label>').join(''); };
	const renderReview = () => { root.querySelector('[data-review]').innerHTML = '<ul>' + state.parties.map((item) => '<li><strong>' + escape(item.name) + '</strong>: ' + escape(item.role === 'claimant' ? 'istante' : 'invitata') + ', ' + (Number(item.centerCount) || 1) + ' centri, stato ' + escape(item.status) + '</li>').join('') + '</ul>'; };
	const renderDynamic = () => { renderParties('claimant'); renderParties('invited'); renderCenters(); renderExpenses(); renderPaid(); renderReview(); };

	const sync = () => {
		state.type = form.elements.type.value || state.type || '';
		state.value = Number(form.elements.value.value || state.value || 0);
		state.scenario = form.elements.scenario.value || state.scenario || 'absence';
		state.increase = Number(form.elements.increase.value || state.increase || 0);
		root.querySelectorAll('[data-party]').forEach((fieldset) => {
			const item = findParty(fieldset.dataset.party);
			if (!item) return;
			item.name = fieldset.querySelector('[data-field="name"]').value.trim();
			const status = fieldset.querySelector('[data-field="status"]');
			if (status) item.status = status.value;
		});
		root.querySelectorAll('[data-centers-for]').forEach((input) => { findParty(input.dataset.centersFor).centerCount = Math.max(1, Number(input.value) || 1); });
		root.querySelectorAll('[data-paid-for]').forEach((input) => { findParty(input.dataset.paidFor).paid = Math.max(0, Number(input.value) || 0); });
		root.querySelectorAll('[data-expenses-for]').forEach((fieldset) => {
			const item = findParty(fieldset.dataset.expensesFor);
			item.expenses = [...fieldset.querySelectorAll('[data-expense]:checked')].map((input) => ({ key: input.dataset.expense, label: expenseLabels[input.dataset.expense], amount: Number(config.expenses?.[input.dataset.expense]) || 0 }));
			item.customLabel = fieldset.querySelector('[data-custom-label]').value.trim();
			item.customAmount = Math.max(0, Number(fieldset.querySelector('[data-custom-amount]').value) || 0);
		});
		save();
	};
	const validate = () => {
		sync();
		if (step === 1 && !state.type) return 'Seleziona il tipo di mediazione.';
		if (step === 2 && state.value <= 0) return 'Indica un valore maggiore di zero.';
		if (step === 3 && !state.parties.some((item) => item.role === 'claimant' && item.name)) return 'Inserisci almeno una parte istante.';
		return '';
	};
	const showStep = (next) => {
		step = Math.min(10, Math.max(1, next));
		sections.forEach((section) => { section.hidden = Number(section.dataset.step) !== step; });
		root.querySelector('[data-back]').disabled = step === 1;
		root.querySelector('[data-next]').hidden = step === 10;
		root.querySelector('.mce-preventivo__progress span').style.width = (step * 10) + '%';
		renderDynamic();
		sections[step - 1].querySelector('input, select, button')?.focus();
	};
	root.addEventListener('click', (event) => {
		const add = event.target.closest('[data-add-party]');
		const remove = event.target.closest('[data-remove-party]');
		if (add) addParty(add.dataset.addParty);
		if (remove) { state.parties = state.parties.filter((item) => item.id !== remove.dataset.removeParty); save(); renderDynamic(); }
		if (event.target.closest('[data-next]')) {
			const error = validate();
			const message = root.querySelector('.mce-preventivo__error');
			message.hidden = !error;
			message.textContent = error;
			if (!error) showStep(step + 1);
		}
		if (event.target.closest('[data-back]')) showStep(step - 1);
		if (event.target.closest('[data-reset]')) { localStorage.removeItem(storageKey); window.location.reload(); }
		if (event.target.closest('[data-print]')) window.print();
	});
	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		sync();
		const payload = {
			type: state.type, value: state.value, scenario: state.scenario, increase: state.increase,
			parties: state.parties.map((item) => ({
				id: item.id, name: item.name, role: item.role, status: item.role === 'claimant' ? 'present' : item.status,
				centers: Array.from({ length: item.centerCount }, (_, index) => 'Centro ' + (index + 1)),
				expenses: item.expenses.map(({ label, amount }) => ({ label, amount })).concat(item.customAmount > 0 ? [{ label: item.customLabel || 'Spesa viva documentata', amount: item.customAmount }] : []),
				paid: item.paid,
			})),
		};
		const response = await fetch(root.dataset.endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': root.dataset.nonce }, body: JSON.stringify(payload) });
		const data = await response.json();
		if (!response.ok) {
			const alert = root.querySelector('.mce-preventivo__error');
			alert.textContent = data.message || 'Errore di calcolo.';
			alert.hidden = false;
			return;
		}
		const printConfig = config.print || {};
		const parties = data.parties.map((item) => {
			const lines = item.lines.map((line) => '<tr><td>' + escape(line.label) + '</td><td>' + (line.operation === 'subtract' ? '− ' : '') + money(line.amount) + '</td></tr>').join('');
			return '<article><h3>' + escape(item.name) + '</h3><p>Ruolo: ' + escape(item.role) + ' · Stato: ' + escape(item.status) + ' · Centri: ' + item.centers + '</p><table><thead><tr><th>Voce</th><th>Importo</th></tr></thead><tbody>' + lines + '</tbody><tfoot><tr><th>Totale dovuto</th><th>' + money(item.total) + '</th></tr><tr><th>Già versato</th><th>' + money(item.paid) + '</th></tr><tr><th>Residuo</th><th>' + money(item.residual) + '</th></tr></tfoot></table></article>';
		}).join('');
		const result = root.querySelector('.mce-preventivo__result');
		result.innerHTML = '<div class="mce-preventivo__print"><header><strong>' + escape(printConfig.header || 'Mediacon') + '</strong><span>' + escape(data.simulation) + ' · ' + escape(data.date) + '</span></header><h2>Preventivo per singola parte</h2><p>Scenario: ' + escape(data.scenario) + ' · Regime applicato: ' + escape(data.regime) + '</p>' + parties + '<footer>' + escape(printConfig.footer || '') + '</footer></div><button type="button" data-print>Stampa / salva PDF</button>';
		result.hidden = false;
		result.scrollIntoView({ behavior: 'smooth' });
	});
	if (state.type) {
		const radio = form.querySelector('[name="type"][value="' + CSS.escape(state.type) + '"]');
		if (radio) radio.checked = true;
	}
	form.elements.value.value = state.value || '';
	form.elements.scenario.value = state.scenario || 'absence';
	form.elements.increase.value = state.increase || 0;
	renderDynamic();
	showStep(1);
})();
