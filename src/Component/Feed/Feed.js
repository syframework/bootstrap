(function () {

	document.body.addEventListener('click', function (event) {
		var button = null;
		if (event.target.classList.contains('feed-next-page-button')) {
			button = event.target;
		}
		if (!button) {
			button = event.target.closest('.feed-next-page-button');
		}
		if (!button) return;
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
		var lastId = button.previousElementSibling ? button.previousElementSibling.dataset.id : null;
		if (!lastId) {
			lastId = button.nextElementSibling ? button.nextElementSibling.dataset.id : null;
		}
		if (!lastId) {
			lastId = button.dataset.start;
		}
		var params = JSON.parse(button.dataset.params);
		params['class'] = button.dataset.class;
		params['last'] = lastId;
		var location = new URL(button.dataset.location, window.location.origin);
		Object.entries(params).forEach(([key, value]) => {
			if (value === null) return;
			location.searchParams.set(key, value);
		});
		fetch(location.href)
			.then(response => response.text())
			.then(result => {
				button.outerHTML = result;
				document.body.dispatchEvent(new CustomEvent('feed-loaded'));
			});
	});

	// Trigger click on visible auto-load buttons
	function clickLoad() {
		document.querySelectorAll('.feed-next-page-button.feed-next-page-auto').forEach(function (button) {
			if (!isVisible(button)) return;
			var rect = button.getBoundingClientRect();
			if (rect.top < window.innerHeight + 300) {
				button.click();
			}
		});
	}
	clickLoad();

	// Scroll event for auto-loading feeds
	function setFeedScroll() {
		var timer;
		window.addEventListener('scroll', function () {
			if (timer) {
				clearTimeout(timer);
			}
			timer = setTimeout(function () {
				clickLoad();
				setFeedScroll();
			}, 500);
		}, { once: true });
	}
	setFeedScroll();

	function isVisible(elem) {
		return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
	}

	let observer = new MutationObserver(mutations => {
		clickLoad();
	});

	observer.observe(document.body, { attributes: true, childList: true, subtree: true });

})();