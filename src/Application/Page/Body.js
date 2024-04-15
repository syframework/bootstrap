(function () {

	<!-- BEGIN DELETE_BLOCK -->
	document.getElementById('sy-btn-page-delete').addEventListener('click', function(e) {
		e.preventDefault();
		if (confirm(document.createElement('div').appendChild(document.createTextNode("{CONFIRM_DELETE}")).parentNode.textContent)) {
			document.getElementById('{DELETE_FORM_ID}').submit();
		}
	});
	<!-- END DELETE_BLOCK -->

	<!-- BEGIN CODE_BLOCK -->
	let htmlLoaded = false;

	const codeEditorHtml = ace.edit('codearea_codearea_html_{ID}');
	const codeEditorCss = ace.edit('codearea_codearea_css_{ID}');
	const codeEditorJs = ace.edit('codearea_codearea_js_{ID}');

	function resizeCodeArea() {
		let codeEditorHeight = document.querySelector('#sy-code-modal .modal-body').offsetHeight;
		let codeEditorWidth = document.querySelector('#sy-code-modal .modal-body').offsetWidth;

		let htmlEditor = document.querySelector('#codearea_codearea_html_{ID}');
		htmlEditor.style.height = codeEditorHeight + 'px';
		htmlEditor.style.width = codeEditorWidth + 'px';
		codeEditorHtml.resize();

		let cssEditor = document.querySelector('#codearea_codearea_css_{ID}');
		cssEditor.style.height = codeEditorHeight + 'px';
		cssEditor.style.width = codeEditorWidth + 'px';
		codeEditorCss.resize();

		let jsEditor = document.querySelector('#codearea_codearea_js_{ID}');
		jsEditor.style.height = codeEditorHeight + 'px';
		jsEditor.style.width = codeEditorWidth + 'px';
		codeEditorJs.resize();
	}

	window.addEventListener('resize', resizeCodeArea);

	// Event listener for modal when it's shown
	document.getElementById('sy-code-modal').addEventListener('shown.bs.modal', function(e) {
		resizeCodeArea();
		codeEditorHtml.focus();

		if (htmlLoaded) return;
		var timestamp = new Date().getTime();
		fetch('{GET_URL}&ts=' + timestamp)
			.then(response => response.json())
			.then(res => {
				if (res.status === 'ok') {
					codeEditorHtml.session.setValue(res.content);
					htmlLoaded = true;
				}
			});
	});

	// Form submission event listener
	document.querySelector('#sy-code-modal form').addEventListener('submit', function(e) {
		e.preventDefault();
		this.js.value = codeEditorJs.getValue();
		this.css.value = codeEditorCss.getValue();
		this.submit();
	});

	// Show modals if they contain an alert
	['sy-new-page-modal', 'sy-update-page-modal', 'sy-code-modal'].forEach(function(modalId) {
		let modal = document.getElementById(modalId);
		if (modal.querySelector('div.alert')) {
			new bootstrap.Modal(modal).show();
		}
	});

	// Display error message
	let errorMsgElement = document.querySelector('#sy-code-modal div.alert');
	if (errorMsgElement) {
		let errorMsg = errorMsgElement.textContent;
		if (errorMsg.startsWith('SCSS')) {
			new bootstrap.Tab(document.querySelector('#sy-css-tab')).show();
		}
		flash(errorMsg, 'danger');
	}
	<!-- END CODE_BLOCK -->

})();