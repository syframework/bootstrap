(function () {
	document.body.addEventListener('show.bs.modal', function (e) {
		var target = e.relatedTarget || document.querySelector('[data-bs-target="#' + e.target.id + '"]');
		var title = e.target.querySelector('.modal-title');
		if (title.textContent !== '') return;
		title.innerHTML = (target.textContent.trim() === '') ? target.innerHTML + target.dataset.bsTitle : target.innerHTML;
	}, true);
})();