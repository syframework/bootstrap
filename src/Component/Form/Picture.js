var SyFormPicture = {

	handleFileSelectBtn: function(input) {
		var files = [].slice.call(input.files).reverse();
		var l = files.length + $(input).nextAll('.sy-picture-div').first().find('img.sy-picture-img').length;
		if (l > $(input).data('img-max-count')) {
			alert($(input).data('alert-count'));
			return;
		}
		$(input).closest('form').find('[type="submit"]').attr('disabled', 'disabled');
		$(input).nextAll('.sy-picture-loader').first().show();

		let hiddenField = $(input).prevAll('input.sy-picture-input-hidden').first();
		let data = JSON.parse(hiddenField.val() || '{}');
		//hiddenField.data('_pictures', hiddenField.data('_pictures') || {});

		var promises = [];
		for (var i = 0; i < files.length; i++) {
			var id = files[i].name.replace(/\W/g, '');
			if ($(input).nextAll('.sy-picture-div').first().find('div[data-id="' + id + '"]').length === 0) {
				$(input).nextAll('.sy-picture-div').first().append('<div class="sy-picture-container" data-id="' + id + '" style="position:relative;display:inline-block"></div>');
			}
			promises.push(new Promise(function(resolve) {
				SyFormPicture.createThumbnail(files[i], input, id, resolve);
			}));
		}

		Promise.all(promises).then(function(values) {
			for (var i = 0; i < values.length; i++) {
				if (values[i].image) {
					data[values[i].id] = {image: values[i].image};
				} else {
					$(input).nextAll('.sy-picture-div').first().find('div[data-id="' + id + '"]').remove();
				}
			}
			$(input).nextAll('.sy-picture-loader').first().hide();
			hiddenField.val(JSON.stringify(data));
			hiddenField.change();
			$(input).closest('form').find('[type="submit"]').removeAttr('disabled');
		});
	},

	createThumbnail: function(f, input, id, callback) {
		if (!f.type.match('image.*')) {
			alert($(input).data('alert-image'));
			callback({id: id, image: false});
			return;
		}

		var reader = new FileReader();

		reader.onload = function() {
			var img = new Image();
			img.onload = function() {
				if (img.width < $(input).data('img-min-width') || img.heigth < $(input).data('img-min-height')) {
					alert($(input).data('alert-dimension'));
					callback({id: id, image: false});
					return;
				}

				$(input).nextAll('.sy-picture-div').first().find('div[data-id="' + id + '"]').html(
					'<img class="sy-picture-img img-fluid rounded" src="' + img.src + '" style="margin:10px;max-width:250px;max-height:250px" />' +
					'<button style="position:absolute;top:10px;right:0" class="btn btn-secondary btn-sm sy-picture-rm" data-id="' + id + '"><span class="fas fa-times"></span></button>' +
					'<input type="text" class="form-control sy-picture-caption" data-id="' + id + '" placeholder="' + $(input).data('caption-placeholder') + '" />'
				);

				var width = img.width;
				var height = img.height;

				if (width > $(input).data('img-max-width') || height > $(input).data('img-max-height')) {
					if (width/height > 1) {
						height = Math.round(height * $(input).data('img-max-height') / width);
						width = $(input).data('img-max-width');
					} else {
						width = Math.round(width * $(input).data('img-max-width') / height);
						height = $(input).data('img-max-height');
					}
				}

				var canvas = document.createElement('canvas');
				canvas.width = width;
				canvas.height = height;
				var ctx = canvas.getContext("2d");
				ctx.drawImage(img, 0, 0, width, height);
				callback({
					id: id,
					image: canvas.toDataURL("image/jpeg", $(input).data('img-quality')).split(',')[1]
				});
			};
			img.src = reader.result;
		};

		reader.readAsDataURL(f);
	},

	removePicture: function(btn) {
		let pic = $(btn).closest('.sy-picture-container');
		let hiddenField = pic.parent().prevAll('input.sy-picture-input-hidden').first(); //supposedly pic.parent() == pic.closest('.sy-picture-div')
		let data = JSON.parse(hiddenField.val() || '{}');
		pic.remove();
		delete data[$(btn).data('id')];
		hiddenField.val(JSON.stringify(data));
		hiddenField.change();
	},

	updateCaption: function(input) {
		let hiddenField = $(input).closest('.sy-picture-div').prevAll('input.sy-picture-input-hidden').first();
		hiddenField.data('_pictures')[$(input).data('id')].caption = $(input).val();
		hiddenField.val(JSON.stringify(hiddenField.data('_pictures')));
		hiddenField.change();
	},

	drawPictures: function(hidden) {
		let val = $(hidden).val();
		if (val === '') return;
		let pictures = JSON.parse(val);
		let html = '';
		let placeholder = $(hidden).nextAll('input[type=file].sy-picture-input-file').first().data('caption-placeholder');
		//$(hidden).data('_pictures', $(hidden).data('_pictures') || {});

		for (var id in pictures) {
			var caption = pictures[id].caption === undefined ? '' : pictures[id].caption;
			html += `
			<div class="sy-picture-container" style="position:relative;display:inline-block">
				<img class="sy-picture-img img-fluid rounded" src="data:image/png;base64,${pictures[id].image}" style="margin:10px;max-width:250px;max-height:250px" />
				<button style="position:absolute;top:10px;right:0" class="btn btn-secondary btn-sm sy-picture-rm" data-id="${id}"><span class="fas fa-times"></span></button>
				<input type="text" class="form-control sy-picture-caption" data-id="${id}" placeholder="${placeholder}" value="${caption}" />
			</div>`;
			//$(hidden).data('_pictures')[id] = {image: pictures[id].image, caption: caption};
		}
		$(hidden).nextAll('.sy-picture-div').first().html(html);
	}

};

$('body').on('change.sy-picture', '.sy-picture-input-file', function() {
	SyFormPicture.handleFileSelectBtn(this);
});

$('body').on('change.sy-picture', '.sy-picture-input-hidden', function() {
	SyFormPicture.drawPictures(this);
});

$('body').on('click.sy-picture', '.sy-picture-rm', function() {
	SyFormPicture.removePicture(this);
});

$('body').on('change.sy-picture', '.sy-picture-caption', function() {
	SyFormPicture.updateCaption(this);
});

$(document).ready(function() {
	$('.sy-picture-input-hidden').change();
});