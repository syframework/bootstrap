(function () {

	document.body.addEventListener('click', function (event) {
		let button = null;
		if (event.target.classList.contains('feed-next-page-button')) {
			button = event.target;
		}
		if (!button) {
			button = event.target.closest('.feed-next-page-button');
		}
		if (!button) return;
		button.classList.remove('feed-next-page-button');
		button.disabled = true;
		let spinner = document.createElement('span');
		spinner.className = 'spinner-border spinner-border-sm';
		spinner.setAttribute('role', 'status');
		spinner.setAttribute('aria-hidden', 'true');
		let biIcon = button.querySelector('.bi');
		if (biIcon) {
			biIcon.parentNode.replaceChild(spinner, biIcon);
		}
		let lastId = button.previousElementSibling ? button.previousElementSibling.dataset.id : null;
		if (!lastId) {
			lastId = button.nextElementSibling ? button.nextElementSibling.dataset.id : null;
		}
		if (!lastId) {
			lastId = button.dataset.start;
		}
		const params = JSON.parse(button.dataset.params);
		params['class'] = button.dataset.class;
		params['last'] = lastId;
		const location = new URL(button.dataset.location, window.location.origin);
		const addParam = (key, value) => {
			if (value === null) return;
			if (typeof value === 'object') {
				Object.entries(value).forEach(([nestedKey, nestedValue]) => {
					addParam(`${key}[${nestedKey}]`, nestedValue);
				});
			} else {
				location.searchParams.set(key, value);
			}
		};
		Object.entries(params).forEach(([key, value]) => {
			addParam(key, value);
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