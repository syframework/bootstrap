function flash(message, type, timeout) {
	if (type === undefined) type = 'success';
	if (timeout === undefined) timeout = 3500;

	var title = '';

	if (message instanceof Object) {
		if (message['title'] !== undefined) title = message['title'];
		message = message['message'];
	}

	if (timeout === 0) {
		if (document.querySelector('#flash-message-modal h4') !== null) {
			document.querySelector('#flash-message-modal h4').innerText = title;
		}
		document.querySelector('#flash-message-modal p').innerText = message;
		var modal = new bootstrap.Modal(document.getElementById('flash-message-modal'), {
			keyboard: false,
			backdrop: 'static'
		});
		modal.show();
		return;
	}

	if (document.getElementById('flash-message').classList.contains('in')) return;

	if (document.querySelector('#flash-message span.h4') !== null) {
		document.querySelector('#flash-message span.h4').innerText = title;
	}
	document.querySelector('#flash-message p').innerText = message;
	document.getElementById('flash-message').classList.remove('alert-success', 'alert-info', 'alert-warning', 'alert-danger');
	document.getElementById('flash-message').classList.add('in', 'alert-' + type);

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
		}, timeout);
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