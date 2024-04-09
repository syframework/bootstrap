// $(function () {
// 	$('body').on('click', '.popup', function (e) {
// 		e.preventDefault();
// 		var width = $(this).data('width'),
// 			height = $(this).data('height'),
// 			left = ($(window).width() - width) / 2,
// 			top = ($(window).height() - height) / 2,
// 			url = this.href + encodeURIComponent($(this).data('url')),
// 			opts = 'status=1' +
// 				',width=' + width +
// 				',height=' + height +
// 				',top=' + top +
// 				',left=' + left;
// 		window.open(url, 'share', opts);
// 	});

// 	var clipboard = new ClipboardJS('.copy-url', {
// 		target: function (trigger) {
// 			return trigger.previousElementSibling;
// 		}
// 	});

// 	clipboard.on('success', function (e) {
// 		var btn = $(e.trigger);
// 		var title = btn.data('bs-original-title');
// 		btn.attr('data-bs-original-title', btn.data('success'));
// 		var tooltip = bootstrap.Tooltip.getInstance(e.trigger);
// 		tooltip.show();
// 		btn.attr('data-bs-original-title', title);
// 	});
// });

(function () {
	document.body.addEventListener('click', function (e) {
		var popup = e.target.closest('.popup');
		if (!popup) return;
		e.preventDefault();
		var width = popup.dataset.width,
			height = popup.dataset.height,
			left = (window.innerWidth - width) / 2,
			top = (window.innerHeight - height) / 2,
			url = popup.href + encodeURIComponent(popup.dataset.url),
			opts = 'status=1' +
				',width=' + width +
				',height=' + height +
				',top=' + top +
				',left=' + left;
		window.open(url, 'share', opts);
	});

	var clipboard = new ClipboardJS('.copy-url', {
		target: function (trigger) {
			return trigger.previousElementSibling;
		}
	});

	clipboard.on('success', function (e) {
		var btn = e.trigger;
		var title = btn.dataset.bsOriginalTitle;
		btn.dataset.bsOriginalTitle = btn.dataset.success;
		var tooltip = bootstrap.Tooltip.getInstance(btn);
		tooltip.show();
		btn.dataset.bsOriginalTitle = title;
	});
})();