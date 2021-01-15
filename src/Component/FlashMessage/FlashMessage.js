function flash(message, type, timeout) {
	if (type === undefined) type = 'success';
	if (timeout === undefined) timeout = 3500;

	var title = '';

	if (message instanceof Object) {
		if (message['title'] !== undefined) title = message['title'];
		message = message['message'];
	}

	if (timeout === 0) {
		$('#flash-message-modal h4').text(title);
		$('#flash-message-modal p').text(message);
		$('#flash-message-modal').modal({
			keyboard: false,
			backdrop: 'static'
		});
		return;
	}

	if ($('#flash-message').hasClass('in')) return;

	$('#flash-message span.h4').text($('<p/>').html(title).text());
	$('#flash-message p').text($('<p/>').html(message).text());
	$('#flash-message').removeClass('alert-success alert-info alert-warning alert-danger');
	$('#flash-message').addClass('in alert-' + type);

	var timer = setTimeout(function() {
		$('#flash-message').removeClass('in');
	}, timeout);

	$('#flash-message').mouseenter(function() {
		clearTimeout(timer);
	});

	$('#flash-message').mouseleave(function() {
		timer = setTimeout(function() {
			$('#flash-message').removeClass('in');
		}, timeout);
	});
}
<!-- BEGIN SESSION_BLOCK -->
$(function() {
	<!-- BEGIN TIMEOUT_BLOCK -->
	$('#flash-message').toggleClass('in');
	var timer = setTimeout(function() {
		$('#flash-message').removeClass('in');
	}, {TIMEOUT});

	$('#flash-message').mouseenter(function() {
		clearTimeout(timer);
	});

	$('#flash-message').mouseleave(function() {
		timer = setTimeout(function() {
			$('#flash-message').removeClass('in');
		}, {TIMEOUT});
	});
	<!-- ELSE TIMEOUT_BLOCK -->
	var modal = new bootstrap.Modal(document.getElementById('flash-message-modal'), {
		keyboard: false,
		backdrop: 'static'
	});
	modal.show();
	<!-- END TIMEOUT_BLOCK -->

	$.post(window.location, {flash_message_action: "clear"});
});
<!-- END SESSION_BLOCK -->