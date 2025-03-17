(function() {

	window.addEventListener('submitted.syform', e => {
		const form = e.target.closest('form.syform.syform-delete');
		if (!form) return;

		const data = e.detail;
		if (!data.ok) return;

		const selector = form.dataset.selector;
		if (!selector) return;
		document.querySelectorAll(selector).forEach(el => el.remove());
	});

})();