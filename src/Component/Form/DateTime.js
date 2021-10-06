(function()  {
	function initDateTimeUTC(element) {
		element.addEventListener('blur', function() {
			let hidden = element.nextElementSibling;
			let d = new Date(element.value);
			hidden.value = d.toISOString();
		});

		let hidden = element.nextElementSibling;
		if (hidden.getAttribute('value')) {
			let d = new Date(hidden.getAttribute('value'));
			let s = d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2) + 'T' + ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
			element.value = s;
		}
	}

	document.querySelectorAll('.datetime-utc').forEach(el => initDateTimeUTC(el));

	let observer = new MutationObserver(mutations => {

		for (let mutation of mutations) {
			if (mutation.type !== 'childList') continue;

			for (let node of mutation.addedNodes) {
				if (!(node instanceof HTMLElement)) continue;

				if (node.matches('input.datetime-utc[type="datetime-local"]')) {
					initDateTimeUTC(node);
				}

				for (let elem of node.querySelectorAll('input.datetime-utc[type="datetime-local"]')) {
					initDateTimeUTC(elem);
				}
			}
		}

	});

	observer.observe(document.body, { childList: true });
})();