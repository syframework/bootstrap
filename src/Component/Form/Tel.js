(function () {

	function initTelInput(input) {

		window.intlTelInput(input, {
			dropdownContainer: document.body,
			utilsScript: '{INTLTELINPUT_UTILS_JS}',
			initialCountry: 'auto',
			geoIpLookup: function(callback) {
				fetch('{WEB_ROOT}/api/location').then(function(res) {
					if (!res.ok) return;
					res.text().then(function(data) {
						callback(data);
					});
				});
			},
			preferredCountries: [{TOP_COUNTRIES}]
		});

		var iti = window.intlTelInputGlobals.getInstance(input);

		var reset = function (input) {
			input.classList.remove('is-invalid');
			input.parentElement.nextElementSibling.textContent = input.dataset.help;
			input.parentElement.nextElementSibling.classList.replace('text-danger', 'text-muted');
			input.setCustomValidity('');
		};

		input.addEventListener('blur', (e) => {
			reset(e.target);
			let hidden = input.form.querySelector('input[name="' + input.dataset.name + '"]');
			if (e.target.value.trim()) {
				if (iti.isValidNumber()) {
					let number = iti.getNumber();
					hidden.value = number;
					hidden.setAttribute('value', number);
				} else {
					e.target.classList.add('is-invalid');
					e.target.parentElement.nextElementSibling.textContent = e.target.dataset.error;
					e.target.parentElement.nextElementSibling.classList.replace('text-muted', 'text-danger');
					e.target.setCustomValidity(e.target.dataset.error);
				}
			} else {
				hidden.value = '';
			}
		});

		input.addEventListener('keyup', (e) => {
			reset(e.target);
		});

		input.addEventListener('change', (e) => {
			reset(e.target);
		});

		input.closest('form').querySelector('input[name="' + input.dataset.name + '"]').addEventListener('change', (e) => {
			iti.setNumber(e.target.value);
		});

		iti.setNumber(input.closest('form').querySelector('input[name="' + input.dataset.name + '"]').value);

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