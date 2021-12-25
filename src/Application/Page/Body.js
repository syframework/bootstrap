(function ($) {
	$.fn.drags = function (opt) {

		opt = $.extend({ handle: "", cursor: "move" }, opt);

		if (opt.handle === "") {
			var $el = this;
		} else {
			var $el = this.find(opt.handle);
		}

		return $el.css('cursor', opt.cursor).on("mousedown", function (e) {
			if (opt.handle === "") {
				var $drag = $(this).addClass('draggable');
			} else {
				var $drag = $(this).addClass('active-handle').parent().addClass('draggable');
			}
			var z_idx = $drag.css('z-index'),
				drg_h = $drag.outerHeight(),
				drg_w = $drag.outerWidth(),
				pos_y = $drag.offset().top + drg_h - e.pageY,
				pos_x = $drag.offset().left + drg_w - e.pageX;
			$drag.css('z-index', 1060).parents().on("mousemove", function (e) {
				$('.draggable').offset({
					top: e.pageY + pos_y - drg_h,
					left: e.pageX + pos_x - drg_w
				}).on("mouseup", function () {
					$(this).removeClass('draggable').css('z-index', z_idx);
				});
			});
			e.preventDefault(); // disable selection
		}).on("mouseup", function () {
			if (opt.handle === "") {
				$(this).removeClass('draggable');
			} else {
				$(this).removeClass('active-handle').parent().removeClass('draggable');
			}
		});

	}
})(jQuery);

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

	$('#sy-btn-page-update-start').click(function(e) {
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
								$('#sy-page-topbar').drags();
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
				$('#sy-btn-page-update-start').hide();
				$('#sy-btn-page-update-stop').removeClass("d-none");
			}
		});
	});

	$('#sy-btn-page-update-stop').click(function(e) {
		e.preventDefault();
		if (changed) {
			save(true);
		} else {
			location.reload(true);
		}
	});

	setInterval(function() {
		if (changed) save();
	}, 60000);
<!-- END UPDATE_BLOCK -->

<!-- BEGIN DELETE_BLOCK -->
	$('#sy-btn-page-delete').click(function(e) {
		e.preventDefault();
		if (confirm($('<div />').html("{CONFIRM_DELETE}").text())) {
			$('#{DELETE_FORM_ID}').submit();
		}
	});
<!-- END DELETE_BLOCK -->

<!-- BEGIN CODE_BLOCK -->
	let htmlLoaded = false;

	$('#sy-code-modal').on('shown.bs.modal', function (e) {
		CodeArea('{CM_HTML_ID}').resize();

		if (htmlLoaded) return;
		$.getJSON('{GET_URL}', function(res) {
			if (res.status === 'ok') {
				CodeArea('{CM_HTML_ID}').session.setValue(res.content);
				htmlLoaded = true;
			}
		});
	});

	$('#sy-css-tab').on('shown.bs.tab', function (e) {
		CodeArea('{CM_CSS_ID}').resize();
	});

	$('#sy-js-tab').on('shown.bs.tab', function (e) {
		CodeArea('{CM_JS_ID}').resize();
	});

	$('#sy-code-modal form').submit(function(e) {
		let code = CodeArea['{CM_JS_ID}'].getValue();
		try {
			eval(code);
		} catch(err) {
			alert('JS code error');
			e.preventDefault();
		}

		this.js.value = code;
		this.css.value = CodeArea['{CM_CSS_ID}'].getValue();
	});
<!-- END CODE_BLOCK -->
});
