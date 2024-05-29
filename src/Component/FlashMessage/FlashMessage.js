function flash(message, color, autohide) {
	if (!message) return;
	color = color ?? 'success';
	autohide = autohide ?? true;

	let title = '';

	if (message instanceof Object) {
		title = message['title'] ?? '';
		message = message['body'];
	}

	if (!autohide) {
		const clone = document.getElementById('flash-message-modal-template').content.cloneNode(true);
		if (title === '') {
			clone.querySelector('.modal-header').remove();
		} else {
			clone.querySelector('.modal-title').innerHTML = title;
		}
		clone.querySelector('.modal-body').innerHTML = message;
		clone.querySelector('.btn').classList.add('btn-outline-' + color);
		const element = clone.firstElementChild;
		document.body.appendChild(clone);
		const modal = new bootstrap.Modal(element, {
			keyboard: false,
			backdrop: 'static'
		});
		modal.show();
		return;
	}

	const clone = document.getElementById('flash-message-alert-template').content.cloneNode(true);
	if (title === '') {
		clone.querySelector('span.h4').remove();
	} else {
		clone.querySelector('span.h4').innerHTML = title;
	}
	clone.querySelector('p').innerHTML = message;
	clone.querySelector('div.alert').classList.add('alert-' + color);
	const alert = clone.firstElementChild;
	document.getElementById('flash-message-container').appendChild(clone);

	setTimeout(() => {
		let timeout = message.length * 100;
		timeout = timeout < 3500 ? 3500 : timeout;
		timeout = timeout > 35000 ? 35000 : timeout;

		let timer = setTimeout(function() {
			alert.classList.remove('in');
		}, timeout);

		alert.addEventListener('mouseenter', () => clearTimeout(timer));

		alert.addEventListener('mouseleave', () => {
			timer = setTimeout(() => {
				alert.classList.remove('in');
			}, timeout);
		});

		alert.classList.add('in');

		alert.addEventListener('transitionend', () => {
			if (alert.classList.contains('in')) return;
			alert.remove();
		});
	}, 50);
}

window.addEventListener('DOMContentLoaded', () => {
	// Check message on server session
	const url = new URL('{API_URL}', window.location.origin);
	url.searchParams.set('ts', Date.now());
	fetch(url.href, {
		cache: 'no-cache'
	}).then(response => response.json()).then(data => {
		if (!data.message) return;
		fetch(url.href, {
			method: 'DELETE',
			cache: 'no-cache'
		}).then(response => {
			if (!response.ok) return;
			flash(data.message, data.color, data.autohide);
		}).catch(console.error);
	}).catch(console.error);

	// Check message on session storage
	let fm = sessionStorage.getItem('flash-message');
	if (!fm) return;
	fm = JSON.parse(fm);
	flash(fm.message, fm.color, fm.autohide);
	sessionStorage.removeItem('flash-message');
});