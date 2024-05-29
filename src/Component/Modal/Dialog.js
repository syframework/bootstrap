(function () {
	document.body.addEventListener('show.bs.modal', function (e) {
		const target = e.relatedTarget || document.querySelector('[data-bs-target="#' + e.target.id + '"]');
		if (!target) return;
		const title = e.target.querySelector('.modal-title');
		if (!title) return;
		if (title.textContent !== '') return;
		title.innerHTML = (target.textContent.trim() === '') ? target.innerHTML + (target.dataset.bsTitle ? target.dataset.bsTitle : '') : target.innerHTML;
	}, true);
})();