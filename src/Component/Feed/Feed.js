(function() {
	$('body').on('click', '.feed-next-page-button', function() {
		var button = $(this);
		button.removeClass('feed-next-page-button');
		var lastId = button.prev().data('id');
		if (lastId === undefined) {
			lastId = button.next().data('id');
		}
		if (lastId === undefined) {
			lastId = button.data('start');
		}
		var params = button.data('params');
		params['class'] = button.data('class');
		params['last'] = lastId;
		$.get(
			button.data('location'),
			params,
			function(result) {
				button.replaceWith(result);
				$('body').trigger('feed-loaded');
			}
		);
	});

	$('.feed-next-page-button.feed-next-page-auto:visible').click();

	function setFeedScroll() {
		var timer;
		$(window).one('scroll.feed', function() {
			if (timer) {
				window.clearTimeout(timer);
			}
			timer = window.setTimeout(function() {
				$('.feed-next-page-button.feed-next-page-auto:visible').each(function() {
					if ($(this).offset().top < ($(window).scrollTop() + $(window).height() + 300)) {
						$(this).click();
					}
				});
				setFeedScroll();
			}, 500);
		});
	}
	setFeedScroll();
})();