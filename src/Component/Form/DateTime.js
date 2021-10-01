(function()  {
	function initDateTimeUTC(element) {
		element.addEventListener('blur', function() {
			updateDateTimeUTC(this);
		});

		if (element.getAttribute('value')) {
			let v = new Date(element.getAttribute('value'));
			let d = new Date(Date.UTC(v.getFullYear(), v.getMonth(), v.getDate(), v.getHours(), v.getMinutes()));
			let s = d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2) + 'T' + ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
			element.value = s;
			updateDateTimeUTC(element);
		}
	}

	function updateDateTimeUTC(element) {
		let hidden = element.nextElementSibling;
		let d = new Date(element.value);
		let s = d.getUTCFullYear() + '-' + ('0' + (d.getUTCMonth() + 1)).slice(-2) + '-' + ('0' + d.getUTCDate()).slice(-2) + 'T' + ('0' + d.getUTCHours()).slice(-2) + ':' + ('0' + d.getUTCMinutes()).slice(-2);
		hidden.value = s;
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