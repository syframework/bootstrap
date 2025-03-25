(function () {

	const loadCSS = ($url) => {
		if (document.head.querySelector(`link[href="${$url}"]`)) return;
		const link = document.createElement('link');
		link.rel = 'stylesheet';
		link.href = $url;
		document.head.appendChild(link);
	};

	const loadScript = (url, callback) => {
		if (document.head.querySelector(`script[src="${url}"]`)) return callback();
		const script = document.createElement('script');
		script.src = url;
		script.type = 'text/javascript';
		script.onload = callback;
		document.head.appendChild(script);
	};

	const reset = (input) => {
		input.classList.remove('is-invalid');
		input.parentElement.nextElementSibling.textContent = input.dataset.help;
		input.parentElement.nextElementSibling.classList.replace('text-danger', 'text-muted');
		input.setCustomValidity('');
	};

	const initTelInput = (input) => {

		loadCSS('{INTLTELINPUT_CSS}');

		loadScript('{INTLTELINPUT_JS}', () => {

			const iti = window.intlTelInput(input, {
				dropdownContainer: document.body,
				loadUtils: () => import('{INTLTELINPUT_UTILS_JS}'),
				initialCountry: 'auto',
				geoIpLookup: (success, failure) => {
					fetch("http://ip-api.com/json")
						.then((res) => res.json())
						.then((data) => success(data.countryCode))
						.catch(() => failure());
				},
				countryOrder: [{TOP_COUNTRIES}],
				countrySearch: false,
				hiddenInput: () => ({ phone: input.dataset.name }),
				strictMode: true,
			});

			input.addEventListener('blur', (e) => {
				reset(e.target);
				if (e.target.value.trim() && !iti.isValidNumber()) {
					e.target.classList.add('is-invalid');
					e.target.parentElement.nextElementSibling.textContent = e.target.dataset.error;
					e.target.parentElement.nextElementSibling.classList.replace('text-muted', 'text-danger');
					e.target.setCustomValidity(e.target.dataset.error);
				}
			});

			input.addEventListener('keyup', (e) => {
				reset(e.target);
			});

			input.addEventListener('change', (e) => {
				reset(e.target);
			});

		});

	}

	let telInput = document.querySelectorAll('input[type="tel"]');
	telInput.forEach(input => {
		initTelInput(input);
	});

	let observer = new MutationObserver(mutations => {

		for (let mutation of mutations) {
			if (mutation.type !== 'childList') continue;

			for (let node of mutation.addedNodes) {
				if (!(node instanceof HTMLElement)) continue;

				if (node.matches('input[type="tel"]')) {
					initTelInput(node);
				}

				for (let elem of node.querySelectorAll('input[type="tel"]')) {
					initTelInput(elem);
				}
			}
		}

	});

	observer.observe(document.body, { childList: true });

})();