(function() {
	$('body').on('show.bs.modal', '.modal', function (e) {
		var button = (e.relatedTarget === undefined) ? $('[data-bs-target="#' + $(this).attr('id') + '"]') : $(e.relatedTarget);
		var title = $(this).find('.modal-title');
		if (title.text() !== '') return;
		title.html((button.text().trim() === '') ? button.html() + button.data('bs-title') : button.html());
	});
})();