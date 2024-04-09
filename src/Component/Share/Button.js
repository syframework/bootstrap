document.body.addEventListener('show.bs.modal', function (event) {
	if (!event.target.matches('#shareModal')) return;
	var button = event.relatedTarget;
	var url = button.dataset.url;
	if (url) {
		document.querySelector('#shareModal .share-url').value = url;
		document.querySelectorAll('#shareModal .popup').forEach(e => e.dataset.url = url);
	}
}, false);