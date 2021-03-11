CodeMirror['{CODE_AREA_ID}'] = (function() {
	CodeMirror.commands.save = function(cm) {
		$(cm.getTextArea().form).submit();
	};
	return CodeMirror.fromTextArea(document.getElementById("{CODE_AREA_ID}"), {
<!-- BEGIN MODE_BLOCK -->
		mode: "{MODE}",
<!-- END MODE_BLOCK -->
		lineNumbers: true,
		indentUnit: 4,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		theme: "{THEME}",
		matchBrackets: true,
		autoCloseBrackets: true,
		autoCloseTags: true,
		foldGutter: true,
		matchTags: {bothTags: true},
		gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
		extraKeys: {
			"Shift-Ctrl-C": "toggleComment",
			"Ctrl-Space": "autocomplete"
		}
	});
})();

