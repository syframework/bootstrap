(function() {
	$('body').on('mousedown', '[data-bs-toggle="modal"][data-dialog]', function () {
		var id = $(this).data('bs-target');
		if ($(id).length > 0) return;
		$('body').append($(this).data('dialog'));
	});

	$('button[data-bs-toggle="modal"][data-dialog]').each(function () {
		if ($($(this).data('dialog')).find('div.alert-danger').length > 0) {
			var id = $(this).data('bs-target');
			if ($(id).length == 0) {
				$('body').append($(this).data('dialog'));
			}
			$(this).click();
		}
	});
})();