(function () {
	document.body.addEventListener('click', function (event) {
		var button = event.target.closest('.feed-next-page-button');
		if (button) {
			button.classList.remove('feed-next-page-button');
			button.disabled = true;
			var spinner = document.createElement('span');
			spinner.className = 'spinner-border spinner-border-sm';
			spinner.setAttribute('role', 'status');
			spinner.setAttribute('aria-hidden', 'true');
			var biIcon = button.querySelector('.bi');
			if (biIcon) {
				biIcon.parentNode.replaceChild(spinner, biIcon);
			}
			var lastId = button.getAttribute('data-id-start') || button.previousElementSibling.getAttribute('data-id') || button.nextElementSibling.getAttribute('data-id');
			var params = JSON.parse(button.getAttribute('data-params'));
			params['class'] = button.getAttribute('data-class');
			params['last'] = lastId;
			fetch(button.getAttribute('data-location') + '?' + new URLSearchParams(params))
				.then(response => response.text())
				.then(result => {
					button.outerHTML = result;
					document.body.dispatchEvent(new CustomEvent('feed-loaded'));
				});
		}
	});

	// Trigger click on visible auto-load buttons
	document.querySelectorAll('.feed-next-page-button.feed-next-page-auto').forEach(function (button) {
		if (button.offsetParent !== null) {
			button.click();
		}
	});

	// Scroll event for auto-loading feeds
	function setFeedScroll() {
		var timer;
		window.addEventListener('scroll', function () {
			if (timer) {
				clearTimeout(timer);
			}
			timer = setTimeout(function () {
				document.querySelectorAll('.feed-next-page-button.feed-next-page-auto').forEach(function (button) {
					var rect = button.getBoundingClientRect();
					if (rect.top < window.innerHeight + 300) {
						button.click();
					}
				});
				setFeedScroll();
			}, 500);
		}, { once: true });
	}
	setFeedScroll();
})();