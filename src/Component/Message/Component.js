(function() {
	$(window).scroll();
	if ($('#new-msg-modal').has('div.alert:not(:empty)').length > 0) {
		$('#tablist-ul a[href="#messages"]').tab('show');
		$('#new-msg-modal').modal('show');
	}
<!-- BEGIN NEW_BLOCK -->
	$('#new-msg-form').submit(function(e) {
		e.preventDefault();
		$('#new-msg-send-btn').attr('disabled', 'disabled');
		$('#new-msg-div').hide();
		$.post(
			$(this).attr('action'),
			$(this).serialize(),
			function(result) {
				if (result.status === 'ok') {
					// Reset form
					$('#new-msg-textarea').val('');
					$('.picture-input-hidden').val('');
					$('.picture-div').html('');
					SyFormPicture.pictures = {};

					$('#new-msg-modal').modal('hide');
					$('#message-feed').load("{FEED_URL}", function () {
						$('body').trigger('feed-loaded');
					});
				} else {
					$('#new-msg-div').html(result.message);
					$('#new-msg-div').show();
					if (result.csrf) {
						$('#new-msg-form input[name="__csrf"]').val(result.csrf);
					}
				}
				$('#new-msg-send-btn').removeAttr('disabled');
			},
			'json'
		);
	});
<!-- END NEW_BLOCK -->
})();