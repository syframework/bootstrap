(function() {
	var editor = ace.edit("{CODE_AREA_ID}");
	editor.setTheme("ace/theme/{THEME}");
	editor.session.setMode("ace/mode/{MODE}");
	editor.session.setUseSoftTabs(false);
	editor.setShowPrintMargin(false);

	var textArea = document.getElementById('{TEXT_AREA_ID}');
    editor.session.setValue(textArea.value);

	editor.session.on('change', function() {
		textArea.value = editor.getValue();
	});

	editor.setOption('placeholder', `{PLACEHOLDER}`);

	editor.commands.addCommand({
		name: 'save',
		bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
		exec: function() {
			textArea.form.submit();
		}
	});

	editor.commands.addCommand({
		name: 'format',
		bindKey: {win: 'Ctrl-Shift-F',  mac: 'Command-Shift-F'},
		exec: function() {
			ace.require("ace/ext/beautify").beautify(editor.session);
		}
	});

	editor.setOption('enableLiveAutocompletion', true);
	editor.setOption('enableEmmet', true);

	return editor;
})();