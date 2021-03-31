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

	var clipboard = new ClipboardJS('.copy-url', {
		target: function(trigger) {
			return trigger.parentNode.previousElementSibling;
		}
	});

	clipboard.on('success', function(e) {
		var btn = $(e.trigger);
		var title = btn.data('bs-original-title');
		btn.attr('data-bs-original-title', btn.data('success'));
		var tooltip = bootstrap.Tooltip.getInstance(e.trigger);
		tooltip.show();
		btn.attr('data-bs-original-title', title);
	});
});