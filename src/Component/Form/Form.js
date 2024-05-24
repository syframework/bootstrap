(function() {

	document.body.addEventListener('submit', e => {
		const form = e.target.closest('form.syform');
		if (!form) return;

		const action = form.getAttribute('action');
		const method = form.getAttribute('method').toUpperCase();
		const data = new FormData(form);

		e.preventDefault();

		// Disable form
		Array.prototype.forEach.call(form.elements, el => el.disabled = true);

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

			// Enable form
			Array.prototype.forEach.call(form.elements, el => el.disabled = false);
		}).catch(console.error);
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