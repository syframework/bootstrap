(function() {
	var modal = document.getElementById('{ID}');
	if (modal !== null) return;
	modal = document.createElement('div');
	modal.innerHTML = `{DIALOG}`;
	document.body.append(modal);
})();