(function() {
	$('body').on('submit', '.message-reply-form', function(e) {
		e.preventDefault();
		$('.btn-reply').attr('disabled', 'disabled');
		var message_id = $(this).data('messageId');
		$('#reply-alert-div-' + message_id).hide();
		var data = $(this).serialize();
		$.post(
			$(this).attr('action'),
			data,
			function(result) {
				if (result.status === 'ok') {
					$('#reply-form-textarea-' + message_id).val('');
					$('.picture-input-hidden').val('');
					$('.picture-div').html('');
					SyFormPicture.pictures = {};
					$('#message-' + message_id).removeClass('new');
					$('#reply-feed-div-' + message_id + ' .feed-next-page-button').click();
					$('#reply-nb-' + message_id).html(result.nb_reply);
				} else {
					$('#reply-alert-div-' + message_id).html(result.message);
					$('#reply-alert-div-' + message_id).show();
					if (result.csrf) {
						$(e.target).find('input[name="__csrf"]').val(result.csrf);
					}
				}
				$('.btn-reply').removeAttr('disabled');
			},
			'json'
		);
	});

	$('body').on('submit', '.message-delete-form', function(e) {
		e.preventDefault();
		var message_id = $(this).data('messageId');
		var message_del = $(this).data('messageDel');
		if (!confirm(message_del)) return;
		$.post(
			$(this).attr('action'),
			$(this).serialize(),
			function(result) {
				if (result.status === 'ok') {
					$('#message-' + message_id).hide();
					flash(result.message, 'success');
				} else {
					flash(result.message, 'danger');
					if (result.csrf) {
						$('.message-delete-form input[name="__csrf"]').val(result.csrf);
					}
				}
			},
			'json'
		);
	});

	$('body').on('submit', '.reply-delete-form', function(e) {
		e.preventDefault();
		var message_id = $(this).data('messageId');
		var message_del = $(this).data('messageDel');
		if (!confirm(message_del)) return;
		$.post(
			$(this).attr('action'),
			$(this).serialize(),
			function(result) {
				if (result.status === 'ok') {
					$('#reply-' + message_id).hide();
					flash(result.message, 'success');
				} else {
					flash(result.message, 'danger');
					if (result.csrf) {
						$('.reply-delete-form input[name="__csrf"]').val(result.csrf);
					}
				}
			},
			'json'
		);
	});

	$('body').on('click', '.message-toggle', function(e) {
		var offset = $(this).parents('[data-message-offset]').data('message-offset');
		if (offset === undefined) offset = 60;
		e.preventDefault();
		$(this).removeClass('btn-success');
		var id = $(this).data('id');
		if ($('#message-' + id).hasClass('opened')) {
			window.scroll({top: $('#message-' + id).offset().top - offset, left: 0, behavior: 'smooth'});
			$('#reply-div-' + id).slideUp();
		} else {
			$('#reply-div-' + id).slideDown({
				complete: function() {
					var h = $(window).height() - $('#message-' + id).innerHeight();
					if (h > 0) h = offset;
					window.scroll({top: $('#message-' + id).offset().top - h, left: 0, behavior: 'smooth'});
					$(window).scroll();
				}
			});
		}
		$('#message-' + id).toggleClass('opened');
		localStorage.setItem('reply-nb-' + id, $('#reply-nb-' + id).text());
	});

	$('body').on('feed-loaded', function() {
		$('.comment-form').each(function() {
			var id = $(this).parent().data('id');
			if ($('#reply-feed-div-' + id).find('.sms-container').length === 0) {
				if (localStorage.getItem('comment-' + id) === null) return;
				$('#reply-feed-div-' + id).prepend(localStorage.getItem('comment-' + id));
				if ($('#reply-feed-div-' + id).find('.sms-container').length > localStorage.getItem('reply-nb-' + id)) {
					$('#reply-feed-div-' + id + ' .sms-container').remove();
				}
			} else {
				var e = $('#reply-feed-div-' + id).clone();
				e.find('.sms-container').removeAttr('style');
				e.find('.feed-next-page-button').remove();
				e.find('.reply-delete-form').remove();
				try {
					localStorage.setItem('comment-' + id, e.html());
				} catch(e) {
					localStorage.clear();
				}
			}
		});

		$('.btn.message-toggle').each(function() {
			var id = $(this).data('id');
			if (localStorage.getItem('reply-nb-' + id) === null) return;
			if ($('#message-' + id).hasClass('opened')) {
				localStorage.setItem('reply-nb-' + id, $('#reply-nb-' + id).text());
			} else if ($('#reply-nb-' + id).text() > localStorage.getItem('reply-nb-' + id)) {
				$(this).addClass('btn-success');
			}
		});

		$('.date[data-date]').each(function() {
			$(this).text(moment.unix($(this).data('date')).fromNow());
		});
	});
})();