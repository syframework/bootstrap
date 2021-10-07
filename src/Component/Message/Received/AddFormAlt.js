(function() {
	$('.account-checkbox').change(function(e) {
		$(this.form).find('.account-ok').toggle();
		e.stopImmediatePropagation();
	});
})();