(function() {
	const textArea = document.getElementById('{TEXT_AREA_ID}');

	const editor = ace.edit('{CODE_AREA_ID}', {
		theme: 'ace/theme/{THEME}',
		mode: 'ace/mode/{MODE}',
		placeholder: '{PLACEHOLDER}',
		fontSize: '{FONT_SIZE}',
		useSoftTabs: false,
		showPrintMargin: false,
		enableAutoIndent: true,
		enableLiveAutocompletion: true,
		enableEmmet: true,
		enableSnippets: true,
		value: textArea.value
	});

	editor.session.on('change', function() {
		textArea.value = editor.getValue();
	});

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
})();