(function() {
	var editor = ace.edit("{CODE_AREA_ID}");
	editor.setTheme("ace/theme/{THEME}");
	editor.session.setMode("ace/mode/{MODE}");
	editor.session.setUseSoftTabs(false);
	editor.setShowPrintMargin(false);
	editor.session.setValue($('#{TEXT_AREA_ID}').val());
	editor.session.on('change', function() {
		$('#{TEXT_AREA_ID}').val(editor.getValue());
	});
	editor.setOption('placeholder', `{PLACEHOLDER}`);
	editor.commands.addCommand({
		name: 'save',
		bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
		exec: function() {
			$('#{TEXT_AREA_ID}').closest('form').submit();
		}
	});
	return editor;
})();