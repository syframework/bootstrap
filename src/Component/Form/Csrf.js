(function() {
	function updateCsrf() {
		const location = new URL('{URL}', window.location.origin);
		location.searchParams.set('ts', Date.now());
		fetch(location.href).then(response => response.json()).then(data => {
			document.querySelectorAll('input[name=__csrf').forEach(input => {
				input.value = data.csrf;
			});
		});
	}

	window.addEventListener('csrf', updateCsrf);

	setInterval(updateCsrf, 1200000);
})();