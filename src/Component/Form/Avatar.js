(function () {
	var crop;
	var size = $('#avatar-input-file').data('size');

	function createThumbnail(f) {
		if (!f.type.match('image.*')) {
			alert($('#avatar-input-file').data('alert-image'));
			return;
		}

		var reader = new FileReader();

		reader.onload = function() {
			var img = new Image();
			img.onload = function() {
				if (img.width < size || img.heigth < size) {
					alert($('#avatar-input-file').data('alert-dimension'));
				} else {
					$('#avatar').replaceWith('<img id="avatar" class="img-fluid" src="' + img.src + '"/>');
					$('#avatar-modal').modal('show');
				}
			};
			img.src = reader.result;
		};

		reader.readAsDataURL(f);
	}

	function handleFileSelectBtn(evt) {
		var files = evt.target.files;
		createThumbnail(files[0]);
	}

	$('#avatar-input-file').change(handleFileSelectBtn);

	$('#avatar-modal').on('shown.bs.modal', function() {
		crop = new Cropper(document.getElementById('avatar'), {
			aspectRatio: 1,
			autoCropArea: 1
		});
	});

	$('#avatar-modal').on('hidden.bs.modal', function() {
		crop.destroy();
	});

	$('#avatar-upload-btn').click(function() {
		$('#avatar-modal').modal('hide');

		$('#avatar-progress').css('width', '0%');
		$('#avatar-progress-modal').modal('show');
		$('#avatar-progress-modal').on('shown.bs.modal', function () {
			if (xhr.status > 0) $('#avatar-progress-modal').modal('hide');
		})

		var xhr = new XMLHttpRequest();

		xhr.open('POST', $('#avatar-input-file').data('upload-url'));

		xhr.upload.onprogress = function(e) {
			var percentage = e.loaded * 100 / e.total;
			$('#avatar-progress').css('width', percentage + '%');
		};

		xhr.onload = function() {
			$('#avatar-original').attr('src', canvas.toDataURL());
		};

		xhr.onloadend = function() {
			$('#avatar-progress-modal').modal('hide');
		}

		var canvas = crop.getCroppedCanvas({
			width: 500,
			height: 500,
			imageSmoothingEnabled: true,
			imageSmoothingQuality: 'high'
		});

		canvas.toBlob(function (file) {
			var form = new FormData();
			form.append('file', file);
			form.append('__csrf', $('#avatar-input-file').data('csrf'));

			xhr.send(form);
		}, 'image/png');
	});
})();