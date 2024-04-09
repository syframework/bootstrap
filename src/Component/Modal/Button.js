(function () {
	document.body.addEventListener('mousedown', function (event) {
		if (event.target.matches('[data-bs-toggle="modal"][data-dialog]')) {
			var id = event.target.getAttribute('data-bs-target');
			if (document.querySelector(id)) return;
			document.body.insertAdjacentHTML('beforeend', event.target.getAttribute('data-dialog'));
		}
	});

	document.querySelectorAll('button[data-bs-toggle="modal"][data-dialog]').forEach(function (button) {
		var dialog = button.getAttribute('data-dialog');
		var parsedHTML = new DOMParser().parseFromString(dialog, 'text/html');
		if (parsedHTML.querySelector('div.alert-danger')) {
			var id = button.getAttribute('data-bs-target');
			if (!document.querySelector(id)) {
				document.body.insertAdjacentHTML('beforeend', dialog);
			}
			button.click();
		}
	});
})();