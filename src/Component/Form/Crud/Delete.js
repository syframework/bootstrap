(function() {

	window.addEventListener('submitted.syform', e => {
		const form = e.target.closest('form.syform.syform-delete');
		if (!form) return;

		const data = e.detail;
		if (!data.ok) return;

		if (!data.selector) return;
		document.querySelectorAll(data.selector).forEach(el => el.remove());
	});

})();