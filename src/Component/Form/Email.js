(function () {
	document.querySelectorAll('input[type="email"]').forEach(input => {
		var help = input.nextElementSibling;
		while (help) {
			if (help.matches('.form-text')) break;
			help = help.nextElementSibling;
		}
		input.addEventListener('input', (e) => {
			Mailcheck.run({
				email: input.value,
				suggested: function(suggestion) {
					help.innerHTML = input.dataset.error.replace('[EMAIL]', '<a href="#" class="email">' + suggestion.full + '</a>');
				},
				empty: function() {
					help.innerText = input.dataset.help;
				}
			});
		});
	});

	document.querySelectorAll('.form-text').forEach(help => {
		help.addEventListener('click', (e) => {
			if (!e.target.matches('a.email')) return;
			e.preventDefault();
			var input = help.previousElementSibling;
			while (input) {
				if (input.matches('input[type="email"]')) break;
				input = input.previousElementSibling;
			}
			input.value = e.target.innerText;
			help.innerText = input.dataset.help;
		});
	});
})();