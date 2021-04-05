(function () {
	var telInput = document.querySelectorAll('input[type="tel"]');

	telInput.forEach(input => {
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
			preferredCountries: ['fr', 'es', 'it', 'us', 'gb']
		});
	});

	var reset = function (input) {
		input.classList.remove('is-invalid');
		input.parentElement.nextElementSibling.textContent = input.dataset.help;
	};

	telInput.forEach(input => {
		input.addEventListener('blur', (e) => {
			reset(e.target);
			if (e.target.value.trim()) {
				var iti = window.intlTelInputGlobals.getInstance(e.target);
				if (!iti.isValidNumber()) {
					e.target.classList.add('is-invalid');
					e.target.parentElement.nextElementSibling.textContent = e.target.dataset.error;
				}
			}
		});
	});

	telInput.forEach(input => {
		input.addEventListener('keyup', (e) => {
			reset(e.target);
		})
		input.addEventListener('change', (e) => {
			reset(e.target);
		})
	});

	telInput.forEach(input => {
		input.closest('form').addEventListener('submit', (e) => {
			if (input.value === '') return;
			var iti = window.intlTelInputGlobals.getInstance(input);
			if (!iti.isValidNumber()) {
				input.classList.add('is-invalid');
				input.parentElement.nextElementSibling.textContent = input.dataset.error;
				e.preventDefault();
				return;
			}
			e.target.querySelector('input[name="' + input.dataset.name + '"]').value = iti.getNumber();
		});
	});

	document.querySelectorAll('.tel-hidden-input').forEach(input => {
		input.addEventListener('change', (e) => {
			var iti = window.intlTelInputGlobals.getInstance(input.closest('form').querySelector('input[name="' + input.getAttribute('name') + '-raw"]'));
			iti.setNumber(input.value);
		});
	});

	document.querySelectorAll('.tel-hidden-input').forEach(input => {
		if (input.value === '') return;
		var iti = window.intlTelInputGlobals.getInstance(input.closest('form').querySelector('input[name="' + input.getAttribute('name') + '-raw"]'));
		iti.setNumber(input.value);
	});
})();