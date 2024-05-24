(function() {
	setInterval(function() {
		const location = new URL('{URL}', window.location.origin);
		location.searchParams.set('ts', new Date().getTime());
		fetch(location.href).then(response => response.json()).then(data => {
			document.querySelectorAll('input[name=__csrf').forEach(input => {
				input.value = data.csrf;
			});
		});
	}, 1200000);
})();