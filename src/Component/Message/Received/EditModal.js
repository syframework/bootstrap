(function() {
	$('#edit-msg-form').submit(function(e) {
		e.preventDefault();
		var id = $(this).find('input[name=message_id]').val();
		var button = $(this).find('button[type=submit]');
		button.attr('disabled', 'disabled');
		$('#edit-msg-div').hide();
		$.post(
			$(this).attr('action'),
			$(this).serialize(),
			function(result) {
				if (result.status === 'ok') {
					// Reset form
					$('.picture-input-hidden').val('');
					$('.picture-div').html('');

					$('#edit-msg-modal').modal('hide');
					$('#message-' + id).find('.photoswipe-gallery').html(result.message);
					$('#message-' + id).find('button[data-bs-target="#edit-msg-modal"]').data('message', result.message_raw);
				} else {
					$('#edit-msg-div').html(result.message);
					$('#edit-msg-div').show();
					if (result.csrf) {
						$('#edit-msg-form input[name="__csrf"]').val(result.csrf);
					}
				}
				button.removeAttr('disabled');
			},
			'json'
		);
	});

	$('#edit-msg-modal').on('show.bs.modal', function (e) {
		if (e.relatedTarget === undefined) return;

		var button = $(e.relatedTarget);

		$(this).find('input[name=message_id]').val(button.data('message-id'));
		$(this).find('textarea[name=message]').val(button.data('message'));
	});

	$('#edit-msg-modal').on('shown.bs.modal', function (e) {
		$(this).find('textarea').change();
	});
})();