$('body').on('show.bs.modal', '#shareModal', function (event) {
	var button = $(event.relatedTarget);
	var url = button.data('url');
	if (url !== undefined && url.length > 0) {
		$(this).find('.share-url').val(url);
		$(this).find('.popup').data('url', url);
	}
});