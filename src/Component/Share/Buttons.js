$(function() {
	$('.popup').click(function(e) {
		e.preventDefault();
		var width  = $(this).data('width'),
			height = $(this).data('height'),
			left   = ($(window).width()  - width)  / 2,
			top    = ($(window).height() - height) / 2,
			url    = this.href + encodeURIComponent($(this).data('url')),
			opts   = 'status=1' +
					 ',width='  + width +
					 ',height=' + height +
					 ',top='    + top +
					 ',left='   + left;
		window.open(url, 'share', opts);
	});

	var clipboard = new Clipboard('.copy-url', {
		target: function(trigger) {
			return trigger.parentNode.previousElementSibling;
		}
	});

	clipboard.on('success', function(e) {
		var btn = $(e.trigger);
		var title = btn.data('original-title');
		btn.attr('data-original-title', btn.data('success')).tooltip('show');
		btn.attr('data-original-title', title);
	});
});