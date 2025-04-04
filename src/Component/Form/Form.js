(function() {

	function disableForm(form) {
		Array.prototype.forEach.call(form.elements, el => el.disabled = true);
	}

	function enableForm(form) {
		Array.prototype.forEach.call(form.elements, el => el.disabled = false);
	}

	window.addEventListener('submit', e => {
		if (e.defaultPrevented) return;

		const form = e.target.closest('form.syform');
		if (!form) return;

		const action = form.getAttribute('action');
		const method = form.getAttribute('method').toUpperCase();
		const data = new FormData(form);

		e.preventDefault();

		if (form.dataset.confirm) {
			if (!confirm(form.dataset.confirm)) return;
		}

		// Disable form
		disableForm(form);

		let url;
		try {
			url = new URL(action);
		} catch (error) {
			url = new URL(action, window.location.origin);
		}

		const options = {
			method: method
		};

		switch (method) {
			case 'GET':
				data.forEach((value, key) => url.searchParams.set(key, value));
				break;

			case 'POST':
				options['body'] = data;
				break;
		}

		fetch(url.href, options).then(response => {
			if (response.redirected) {
				window.location.href = response.url;
			}
			return response.json();
		}).then(result => {
			form.dispatchEvent(new CustomEvent('submitted.syform', {bubbles: true, cancelable: true, detail: result}));
		}).catch(error => {
			console.error(error.name, error.message);
			window.dispatchEvent(new Event('csrf'));
			enableForm(form);
			if (error instanceof TypeError) {
				flash(form.dataset.networkError ?? 'Network error', 'danger');
			}
		});
	});

	window.addEventListener('submitted.syform', e => {
		if (e.defaultPrevented) return;

		const form = e.target.closest('form.syform');
		if (!form) return;

		const data = e.detail;

		// Redirection
		if (data.redirection) {
			sessionStorage.setItem('flash-message', JSON.stringify({
				message: data.message,
				color: data.color,
				autohide: data.autohide,
			}));
			window.location.href = data.redirection;
			return;
		}

		// Enable form
		enableForm(form);

		// Error message
		if (!data.ok) {
			flash(data.message, data.color ?? 'danger', data.autohide);
			return;
		}

		// Close modal if form is contained in it
		const modal = form.closest('.modal');
		if (modal) {
			bootstrap.Modal.getOrCreateInstance(modal).hide();
		}

		// Reset form
		const reset = data.reset ?? true;
		if (reset) form.reset();

		// Show success message
		flash(data.message, data.color ?? 'success', data.autohide);
	});

})();