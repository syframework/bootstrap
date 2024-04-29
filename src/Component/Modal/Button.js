(function () {
	document.body.addEventListener('mousedown', function (event) {
		var modalTrigger = event.target.closest('[data-bs-toggle="modal"][data-dialog]');
		if (!modalTrigger) return;
		var id = modalTrigger.dataset.bsTarget;
		if (document.querySelector(id)) return;
		document.body.appendChild(document.getElementById(modalTrigger.dataset.dialog).content.cloneNode(true));
	});

	document.querySelectorAll('button[data-bs-toggle="modal"][data-dialog]').forEach(function (button) {
		var dialog = button.dataset.dialog;
		var parsedHTML = new DOMParser().parseFromString(dialog, 'text/html');
		if (parsedHTML.querySelector('div.alert-danger')) {
			var id = button.dataset.bsTarget;
			if (!document.querySelector(id)) {
				document.body.appendChild(document.getElementById(dialog).content.cloneNode(true));
			}
			button.click();
		}
	});
})();