(function () {
	let crop;
	const avatarInputFile = document.getElementById('avatar-input-file');
	const size = parseInt(avatarInputFile.dataset.size);
	const avatarModal = bootstrap.Modal.getOrCreateInstance('#avatar-modal');
	const avatarProgressModal = bootstrap.Modal.getOrCreateInstance('#avatar-progress-modal');

	function createThumbnail(file) {
		if (!file.type.match('image.*')) {
			alert(avatarInputFile.dataset.alertImage);
			return;
		}

		const reader = new FileReader();

		reader.onload = function (e) {
			const img = new Image();
			img.onload = function () {
				if (img.width < size || img.height < size) {
					alert(avatarInputFile.dataset.alertDimension);
				} else {
					const avatar = document.getElementById('avatar');
					avatar.src = img.src;
					avatar.classList.add('img-fluid');
					avatarModal.show();
				}
			};
			img.src = e.target.result;
		};

		reader.readAsDataURL(file);
	}

	function handleFileSelectBtn(event) {
		const files = event.target.files;
		createThumbnail(files[0]);
	}

	avatarInputFile.addEventListener('change', handleFileSelectBtn);

	document.getElementById('avatar-modal').addEventListener('shown.bs.modal', function () {
		crop = new Cropper(document.getElementById('avatar'), {
			aspectRatio: 1,
			autoCropArea: 1
		});
	});

	document.getElementById('avatar-modal').addEventListener('hidden.bs.modal', function () {
		crop.destroy();
	});

	document.getElementById('avatar-upload-btn').addEventListener('click', function () {
		avatarModal.hide();

		const avatarProgress = document.getElementById('avatar-progress');
		avatarProgress.style.width = '0%';
		avatarProgressModal.show();

		const xhr = new XMLHttpRequest();
		xhr.open('POST', avatarInputFile.dataset.uploadUrl);

		xhr.upload.onprogress = function (e) {
			const percentage = e.loaded * 100 / e.total;
			avatarProgress.style.width = percentage + '%';
		};

		xhr.onload = function () {
			document.getElementById('avatar-original').src = canvas.toDataURL();
		};

		xhr.onloadend = function () {
			avatarProgressModal.hide();
		};

		const canvas = crop.getCroppedCanvas({
			width: size,
			height: size,
			imageSmoothingEnabled: true,
			imageSmoothingQuality: 'high'
		});

		canvas.toBlob(function (blob) {
			const form = new FormData();
			form.append('file', blob);
			form.append('__csrf', avatarInputFile.dataset.csrf);

			xhr.send(form);
		}, 'image/webp');
	});
})();