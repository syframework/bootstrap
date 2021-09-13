$('body').on('mousedown', '[data-bs-toggle="modal"]', function () {
	var id = $(this).data('bs-target');
	if ($(id).length > 0) return;
	$('body').append(`{DIALOG}`);
});