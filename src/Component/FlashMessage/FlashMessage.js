function flash(message, color, autohide) {
	if (!message) return;
	color = color ?? 'success';
	autohide = autohide ?? true;

	let title = '';

	if (message instanceof Object) {
		title = message['title'] ?? '';
		message = message['message'];
	}

	if (!autohide) {
		if (document.querySelector('#flash-message-modal h4')) {
			document.querySelector('#flash-message-modal h4').innerHTML = title;
		}
		document.querySelector('#flash-message-modal p').innerHTML = message;
		var modal = new bootstrap.Modal(document.getElementById('flash-message-modal'), {
			keyboard: false,
			backdrop: 'static'
		});
		modal.show();
		return;
	}

	if (document.getElementById('flash-message').classList.contains('in')) return;

	if (document.querySelector('#flash-message span.h4') !== null) {
		document.querySelector('#flash-message span.h4').innerHTML = title;
	}
	document.querySelector('#flash-message p').innerHTML = message;
	document.getElementById('flash-message').classList.remove('alert-primary', 'alert-secondary', 'alert-success', 'alert-info', 'alert-warning', 'alert-danger', 'alert-light', 'alert-dark');
	document.getElementById('flash-message').classList.add('in', 'alert-' + color);

	let timeout = message.length * 100;
	timeout = timeout < 3500 ? 3500 : timeout;
	timeout = timeout > 35000 ? 35000 : timeout;

	var timer = setTimeout(function() {
		document.getElementById('flash-message').classList.remove('in');
	}, timeout);

	document.getElementById('flash-message').addEventListener('mouseenter', () => clearTimeout(timer));

	document.getElementById('flash-message').addEventListener('mouseleave', () => {
		timer = setTimeout(() => {
			document.getElementById('flash-message').classList.remove('in');
		}, timeout);
	});
}

window.addEventListener('DOMContentLoaded', () => {
	let fm = sessionStorage.getItem('flash-message');
	if (!fm) return;
	fm = JSON.parse(fm);
	flash(fm.message, fm.color, fm.autohide);
	sessionStorage.removeItem('flash-message');
});

<!-- BEGIN SESSION_BLOCK -->
var ready = (callback) => {
	if (document.readyState != "loading") callback();
	else document.addEventListener("DOMContentLoaded", callback);
}

ready(() => {
	<!-- BEGIN TIMEOUT_BLOCK -->
	document.getElementById('flash-message').classList.toggle('in');
	var timer = setTimeout(function() {
		document.getElementById('flash-message').classList.remove('in');
	}, {TIMEOUT});

	document.getElementById('flash-message').addEventListener('mouseenter', () => clearTimeout(timer));

	document.getElementById('flash-message').addEventListener('mouseleave', () => {
		timer = setTimeout(() => {
			document.getElementById('flash-message').classList.remove('in');
		}, {TIMEOUT});
	});
	<!-- ELSE TIMEOUT_BLOCK -->
	var modal = new bootstrap.Modal(document.getElementById('flash-message-modal'), {
		keyboard: false,
		backdrop: 'static'
	});
	modal.show();
	<!-- END TIMEOUT_BLOCK -->

	var formData = new FormData();
	formData.append('flash_message_action', 'clear');
	fetch(window.location, {
		method: 'POST',
		body: formData
	});
});
<!-- END SESSION_BLOCK -->