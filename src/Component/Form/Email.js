(function () {

	const initMailInput = input => {
		const help = document.querySelector(`[data-input-id="${input.id}"]`);
		if (!help) return;
		input.addEventListener('input', (e) => {
			Mailcheck.run({
				email: input.value,
				suggested: function(suggestion) {
					help.innerHTML = input.dataset.error.replace('[EMAIL]', '<a href="#" class="email">' + suggestion.full + '</a>');
				},
				empty: function() {
					help.innerText = input.dataset.help ?? '';
				}
			});
		});
	};

	document.querySelectorAll('input[type="email"]').forEach(input => {
		initMailInput(input);
	});

	document.body.addEventListener('click', e => {
		if (!e.target.matches('.form-text a.email')) return;
		e.preventDefault();
		const help = e.target.closest('.form-text');
		let input = document.querySelector(`#${help.dataset.inputId}`);
		input.value = e.target.innerText;
		help.innerText = input.dataset.help ?? '';
	});

	let observer = new MutationObserver(mutations => {

		for (let mutation of mutations) {
			if (mutation.type !== 'childList') continue;

			for (let node of mutation.addedNodes) {
				if (!(node instanceof HTMLElement)) continue;

				if (node.matches('input[type="email"]')) {
					initMailInput(node);
				}

				for (let elem of node.querySelectorAll('input[type="email"]')) {
					initMailInput(elem);
				}
			}
		}

	});

	observer.observe(document.body, { childList: true });

})();