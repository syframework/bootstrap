(function() {
	$('.modal').appendTo('body');

	$('.modal').on('show.bs.modal', function (e) {
		var button = (e.relatedTarget === undefined) ? $('button[data-bs-target="#' + $(this).attr('id') + '"]') : $(e.relatedTarget);
		var title = $(this).find('.modal-title');
		if (title.text() !== '') return;
		title.html(button.html());
	});

	$('.modal').each(function () {
		if ($(this).has('div.alert-danger:not(:empty)').length > 0) {
			var modal = bootstrap.Modal.getInstance(this)
			modal.show();
			return false;
		}
	});
})();
