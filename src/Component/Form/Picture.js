var SyFormPicture = {

	handleFileSelectBtn: function(input) {
		var files = [].slice.call(input.files).reverse();
		var l = files.length + $(input).siblings('.picture-div').find('div[data-id]').length;
		if (l > 20) {
			alert({ALERT_COUNT});
			return;
		}
		$(input).closest('form').find('[type="submit"]').attr('disabled', 'disabled');
		$(input).siblings('.loader').show();

		let hiddenField = $(input).siblings('input.picture-input-hidden');
		hiddenField.data('_pictures', hiddenField.data('_pictures') || {});

		var promises = [];
		for (var i = 0; i < files.length; i++) {
			var id = files[i].name.replace(/\W/g, '');
			if ($(input).siblings('.picture-div').find('div[data-id="' + id + '"]').length === 0) {
				$(input).siblings('.picture-div').append('<div class="one-pic" data-id="' + id + '" style="position:relative;display:inline-block"></div>');
			}
			promises.push(new Promise(function(resolve) {
				SyFormPicture.createThumbnail(files[i], input, id, resolve);
			}));
		}

		Promise.all(promises).then(function(values) {
			for (var i = 0; i < values.length; i++) {
				(hiddenField.data('_pictures'))[values[i].id] = {image: values[i].image};
			}
			$(input).siblings('.loader').hide();
			hiddenField.val(JSON.stringify(hiddenField.data('_pictures')));
			hiddenField.change();
			$(input).closest('form').find('[type="submit"]').removeAttr('disabled');
		});
	},

	createThumbnail: function(f, input, id, callback) {
		if (!f.type.match('image.*')) {
			alert({ALERT_IMAGE});
			return;
		}

		var reader = new FileReader();

		reader.onload = function() {
			var img = new Image();
			img.onload = function() {
				if (img.width < 50 || img.heigth < 50) {
					alert({ALERT_DIMENSION});
				} else {
					$(input).siblings('.loader').hide();
					$(input).siblings('.picture-div').find('div[data-id="' + id + '"]').html(
						'<img class="picture-img img-fluid rounded" src="' + img.src + '" style="margin:10px;max-width:250px;max-height:250px" />' +
						'<button style="position:absolute;top:10px;right:0" class="btn btn-secondary btn-sm picture-rm" data-id="' + id + '"><span class="fas fa-times"></span></button>' +
						'<input type="text" class="form-control picture-caption" data-id="' + id + '" placeholder="' + $(input).data('caption-placeholder') + '" />'
					);

					var width = img.width;
					var height = img.height;

					if (width > 750 || height > 750) {
						if (width/height > 1) {
							height = Math.round(height * 750 / width);
							width = 750;
						} else {
							width = Math.round(width * 750 / height);
							height = 750;
						}
					}

					var canvas = document.createElement('canvas');
					canvas.width = width;
					canvas.height = height;
					var ctx = canvas.getContext("2d");
					ctx.drawImage(img, 0, 0, width, height);
					callback({
						id: id,
						image: canvas.toDataURL("image/jpeg", 0.7).split(',')[1]
					});
				}
			};
			img.src = reader.result;
		};

		reader.readAsDataURL(f);
	},

	removePicture: function(btn) {
		let pic = $(btn).closest('.one-pic');
		let hiddenField = pic.parent().siblings('input.picture-input-hidden'); //supposedly pic.parent() == pic.closest('.picture-div')
		pic.remove();
		delete hiddenField.data('_pictures')[$(btn).data('id')];
		hiddenField.val(JSON.stringify(hiddenField.data('_pictures')));
		hiddenField.change();
	},

	updateCaption: function(input) {
		let hiddenField = $(input).closest('.picture-div').siblings('input.picture-input-hidden');
		hiddenField.data('_pictures')[$(input).data('id')].caption = $(input).val();
		hiddenField.val(JSON.stringify(hiddenField.data('_pictures')));
		hiddenField.change();
	},

	bindHandlers: function(){
		let self = this;
		$('.picture-input-file').off('change');
		$('.picture-input-file').on('change', function() {
			self.handleFileSelectBtn(this);
		});

		$('.picture-div').off('click', '.picture-rm');
		$('.picture-div').on('click', '.picture-rm', function() {
			self.removePicture(this);
		});

		$('.picture-div').off('change', '.picture-caption');
		$('.picture-div').on('change', '.picture-caption', function() {
			self.updateCaption(this);
		});
	}

};

SyFormPicture.bindHandlers();
