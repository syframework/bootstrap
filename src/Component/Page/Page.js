$(function() {
<!-- BEGIN UPDATE_BLOCK -->
	var changed = false;
	var csrf = "{CSRF}";

	CKEDITOR.dtd.$removeEmpty['span'] = false;
	CKEDITOR.dtd.$removeEmpty['i'] = false;
	CKEDITOR.plugins.addExternal('sycomponent', '{CKEDITOR_ROOT}/plugins/sycomponent/');
	CKEDITOR.plugins.addExternal('sywidget', '{CKEDITOR_ROOT}/plugins/sywidget/');

	function save(reload) {
		$.post("{URL}", {
			id: "{ID}",
			lang: "{LANG}",
			csrf: csrf,
			content: CKEDITOR.instances.content.getData()
		}, function(res) {
			if (res.status === 'ko') {
				alert($('<p/>').html(res.message).text());
				if (res.csrf) {
					csrf = res.csrf;
					changed = true;
				} else {
					location.reload(true);
				}
			} else if (reload) {
				location.reload(true);
			}
		}, 'json');
		changed = false;
	}

	$('#btn-page-update-start').click(function(e) {
		e.preventDefault();
		$.getJSON('{GET_URL}', function(res) {
			if (res.status === 'ok') {
				$('#content').html(res.content);
				$('#content').attr('contenteditable', true);
				if (!CKEDITOR.instances.content) {
					var editor = CKEDITOR.inline('content', {
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
						extraPlugins: 'sharedspace,sycomponent,sywidget,tableresize,embedbase,embed,autoembed,uploadimage,uploadfile',
						allowedContent: true,
						justifyClasses: [ 'text-left', 'text-center', 'text-right', 'text-justify' ],
						disallowedContent: 'script; *[on*]; img{width,height}',
						removePlugins: 'about',
						templates: 'websyte',
						templates_files: ['{CKEDITOR_ROOT}/templates.js'],
						<!-- BEGIN IFRAMELY_BLOCK -->
						embed_provider: '{IFRAMELY}?url={' + 'url' + '}&callback={' + 'callback' + '}&api_key={IFRAMELY_KEY}',
						<!-- END IFRAMELY_BLOCK -->
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
										img: function(el) {
											el.addClass('img-fluid');
										}
									}
								});
							}
						}
					});

					editor.on('blur', function() {
						if (changed) save();
					});

					editor.on('change', function() {
						changed = true;
					});

					editor.config.toolbar = [
						{ name: 'document', items: [ 'Templates' ] },
						{ name: 'clipboard', items: [ 'Undo', 'Redo' ] },
						{ name: 'editing', items: [ 'Find', 'Replace', 'Scayt' ] },
						{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike' ] },
						{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight' ] },
						{ name: 'links', items: [ 'Link', 'Unlink' ] },
						{ name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'Iframe' ] },
						{ name: 'styles', items: [ 'Format' ] },
						{ name: 'colors', items: [ 'TextColor', 'BGColor' ] }
					];
				}
				$('#btn-page-update-start').hide();
				$('#btn-page-update-stop').removeClass("d-none");
			}
		});
	});

	$('#btn-page-update-stop').click(function(e) {
		e.preventDefault();
		if (changed) {
			save(true);
		} else {
			location.reload();
		}
	});

	setInterval(function() {
		if (changed) save();
	}, 60000);
<!-- END UPDATE_BLOCK -->
<!-- BEGIN DELETE_BLOCK -->
	$('#btn-page-delete').click(function(e) {
		e.preventDefault();
		if (confirm($('<div />').html("{CONFIRM_DELETE}").text())) {
			$('#{DELETE_FORM_ID}').submit();
		}
	});
<!-- END DELETE_BLOCK -->
<!-- BEGIN HTML_BLOCK -->
	$('#html-modal').on('shown.bs.modal', function (e) {
		CodeMirror['{CM_HTML_ID}'].setSize(null, window.innerHeight - $(this).find('.modal-header').outerHeight() - $(this).find('.modal-footer').outerHeight() - 20);
		CodeMirror['{CM_HTML_ID}'].refresh();
		$.getJSON('{GET_URL}', function(res) {
			if (res.status === 'ok') {
				CodeMirror['{CM_HTML_ID}'].setValue(res.content);
			}
		});
	});
<!-- END HTML_BLOCK -->
<!-- BEGIN CSS_BLOCK -->
	$('#css-modal').on('shown.bs.modal', function (e) {
		CodeMirror['{CM_CSS_ID}'].setSize(null, window.innerHeight - $(this).find('.modal-header').outerHeight() - $(this).find('.modal-footer').outerHeight() - 20);
		CodeMirror['{CM_CSS_ID}'].refresh();
	});
<!-- END CSS_BLOCK -->
<!-- BEGIN JS_BLOCK -->
	$('#js-modal').on('shown.bs.modal', function (e) {
		CodeMirror['{CM_JS_ID}'].setSize(null, window.innerHeight - $(this).find('.modal-header').outerHeight() - $(this).find('.modal-footer').outerHeight() - 20);
		CodeMirror['{CM_JS_ID}'].refresh();
	});
	$('#js-modal form').submit(function(e) {
		let code = CodeMirror['{CM_JS_ID}'].getValue();
		try { 
			eval(code);
		} catch(err) {
			alert('JS code error');
			e.preventDefault();
		}
	});
<!-- END JS_BLOCK -->
});
