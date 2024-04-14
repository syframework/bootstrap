(function () {

	<!-- BEGIN UPDATE_BLOCK -->
	function draggable(element) {
		var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
		element.onmousedown = dragMouseDown;

		function dragMouseDown(e) {
			e = e || window.event;
			e.preventDefault();
			pos3 = e.clientX;
			pos4 = e.clientY;
			document.onmouseup = closeDragElement;
			document.onmousemove = elementDrag;
		}

		function elementDrag(e) {
			e = e || window.event;
			e.preventDefault();
			pos1 = pos3 - e.clientX;
			pos2 = pos4 - e.clientY;
			pos3 = e.clientX;
			pos4 = e.clientY;
			element.style.top = (element.offsetTop - pos2) + 'px';
			element.style.left = (element.offsetLeft - pos1) + 'px';
		}

		function closeDragElement() {
			document.onmouseup = null;
			document.onmousemove = null;
		}
	}

	var changed = false;
	var csrf = "{CSRF}";

	CKEDITOR.dtd.$removeEmpty['span'] = false;
	CKEDITOR.dtd.$removeEmpty['i'] = false;
	CKEDITOR.plugins.addExternal('sycomponent', '{CKEDITOR_ROOT}/plugins/sycomponent/');
	CKEDITOR.plugins.addExternal('sywidget', '{CKEDITOR_ROOT}/plugins/sywidget/');

	function save(reload) {
		fetch("{URL}", {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				id: "{ID}",
				lang: "{LANG}",
				csrf: csrf,
				content: CKEDITOR.instances.content.getData()
			})
		})
			.then(response => response.json())
			.then(res => {
				if (res.status === 'ko') {
					alert(document.createElement('p').appendChild(document.createTextNode(res.message)).parentNode.textContent);
					if (res.csrf) {
						csrf = res.csrf;
						changed = true;
					} else {
						location.reload(true);
					}
				} else if (reload) {
					location.reload(true);
				}
			})
			.catch(error => console.error('Error:', error));
		changed = false;
	}

	document.getElementById('sy-btn-page-update-start').addEventListener('click', function (e) {
		e.preventDefault();
		fetch('{GET_URL}').then(
			response => response.json()
		).then(res => {
			if (res.status === 'ok') {
				var contentElement = document.getElementById('content');
				contentElement.innerHTML = res.content;
				contentElement.setAttribute('contenteditable', 'true');
				if (!CKEDITOR.instances.content) {
					var editor = CKEDITOR.inline('content', {
						entities: false,
						title: false,
						startupFocus: true,
						linkShowAdvancedTab: false,
						filebrowserImageBrowseUrl: '{IMG_BROWSE}',
						filebrowserImageUploadUrl: '{IMG_UPLOAD_AJAX}',
						filebrowserBrowseUrl: '{FILE_BROWSE}',
						filebrowserUploadUrl: '{FILE_UPLOAD_AJAX}',
						filebrowserWindowWidth: 200,
						filebrowserWindowHeight: 400,
						imageUploadUrl: '{IMG_UPLOAD_AJAX}',
						uploadUrl: '{FILE_UPLOAD_AJAX}',
						extraPlugins: 'sharedspace,sycomponent,sywidget,tableresize,uploadimage,uploadfile',
						allowedContent: true,
						justifyClasses: ['text-left', 'text-center', 'text-right', 'text-justify'],
						disallowedContent: 'script; *[on*]; img{width,height}',
						removePlugins: 'about',
						templates: 'websyte',
						templates_files: ['{CKEDITOR_ROOT}/templates.js'],
						sharedSpaces: {
							top: 'sy-page-topbar',
							bottom: 'sy-page-bottombar'
						},
						on: {
							instanceReady: function (ev) {
								this.dataProcessor.writer.setRules('p', {
									indent: true,
									breakBeforeOpen: true,
									breakAfterOpen: true,
									breakBeforeClose: true,
									breakAfterClose: true
								});
								this.dataProcessor.writer.setRules('div', {
									indent: true,
									breakBeforeOpen: true,
									breakAfterOpen: true,
									breakBeforeClose: true,
									breakAfterClose: true
								});
								this.dataProcessor.htmlFilter.addRules({
									elements: {
										img: function (el) {
											el.addClass('img-fluid');
										}
									}
								});
								draggable(document.getElementById('sy-page-topbar'));
							}
						}
					});

					editor.on('blur', function () {
						if (changed) save();
					});

					editor.on('change', function () {
						changed = true;
					});

					editor.config.toolbar = [
						{ name: 'document', items: ['Templates'] },
						{ name: 'clipboard', items: ['Undo', 'Redo'] },
						{ name: 'editing', items: ['Find', 'Replace', 'Scayt'] },
						{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
						{ name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
						{ name: 'links', items: ['Link', 'Unlink'] },
						{ name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'Iframe'] },
						{ name: 'styles', items: ['Format'] },
						{ name: 'colors', items: ['TextColor', 'BGColor'] }
					];
				}
				document.getElementById('sy-btn-page-update-start').style.display = 'none';
				document.getElementById('sy-btn-page-update-stop').classList.remove('d-none');
			}
		}).catch(
			error => console.error('Error:', error)
		);
	});

	document.getElementById('sy-btn-page-update-stop').addEventListener('click', function(e) {
		e.preventDefault();
		if (changed) {
			save(true);
		} else {
			location.reload(true);
		}
	});

	setInterval(function () {
		if (changed) save();
	}, 60000);

	setInterval(function () {
		fetch('{CSRF_URL}').then(response => response.json()).then(data => {
			csrf = data.csrf;
		});
	}, 1200000);
	<!-- END UPDATE_BLOCK -->

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

		if (htmlLoaded) return;
		fetch('{GET_URL}')
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