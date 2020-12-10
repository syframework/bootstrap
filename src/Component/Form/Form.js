(function() {
	setInterval(function() {
		fetch('{URL}').then(response => response.json()).then(data => {
			document.querySelectorAll('input[name=__csrf').forEach(input => {
				input.value = data.csrf;
			});
		});
	}, 1200000);
})();