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
			console.error(error);
			flash(form.dataset.networkError ?? 'Network error', 'danger');
			enableForm(form);
		});
	});

	window.addEventListener('submitted.syform', e => {
		if (e.defaultPrevented) return;

		const form = e.target.closest('form.syform');
		if (!form) return;

		const data = e.detail;
		const timeout = data.timeout ?? 3500;
		const color = data.color ?? null;

		// Redirection
		if (data.redirection) {
			sessionStorage.setItem('flash-message', JSON.stringify({
				message: data.message,
				color: data.color,
				timeout: data.timeout,
			}));
			window.location.href = data.redirection;
			return;
		}

		// Enable form
		enableForm(form);

		// Error message
		if (!data.ok) {
			flash(data.message, color ?? 'danger', timeout);
			return;
		}

		// Ok
		flash(data.message, color ?? 'success', timeout);

		// Close modal if form is contained in it
		const modal = form.closest('.modal');
		if (!modal) return;
		bootstrap.Modal.getOrCreateInstance(modal).hide();
	});

})();