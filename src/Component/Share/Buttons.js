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
		var title = btn.dataset.bsTitle;
		var tooltip = bootstrap.Tooltip.getInstance(btn);
		tooltip.setContent({ '.tooltip-inner': btn.dataset.success });
		setTimeout(function () {
			tooltip.setContent({ '.tooltip-inner': title });
		}, 1000);
	});
})();