const SyFormPicture = {

	getNextSibling: function (el, selector) {
		let sibling = el.nextElementSibling;
		if (!selector) return sibling;

		while (sibling) {
			if (sibling.matches(selector)) return sibling;
			sibling = sibling.nextElementSibling
		}
		return null;
	},

	getPreviousSibling: function (el, selector) {
		let sibling = el.previousElementSibling;
		if (!selector) return sibling;

		while (sibling) {
			if (sibling.matches(selector)) return sibling;
			sibling = sibling.previousElementSibling
		}
		return null;
	},

	handleFileSelectBtn: function (input) {
		const form = input.closest('form');
		const hiddenField = SyFormPicture.getPreviousSibling(input, '.sy-picture-input-hidden');
		const loader = SyFormPicture.getNextSibling(input, '.sy-picture-loader');
		const pictureDiv = SyFormPicture.getNextSibling(input, '.sy-picture-div');
		const files = Array.from(input.files).reverse();
		const imageCount = files.length + pictureDiv.querySelectorAll('img.sy-picture-img').length;

		if (imageCount > input.dataset.imgMaxCount) {
			alert(input.dataset.alertCount);
			return;
		}

		let submit = false;
		if (form) {
			submit = form.querySelector('[type="submit"]');
			if (submit) submit.disabled = true;
		}
		loader.style.display = 'block';

		let data = JSON.parse(hiddenField.value || '{}');

		let promises = files.map((file) => {
			let id = file.name.replace(/\W/g, '');
			if (!pictureDiv.querySelector(`div[data-id="${id}"]`)) {
				let container = document.createElement('div');
				container.className = 'sy-picture-container';
				container.setAttribute('data-id', id);
				container.style.cssText = 'position:relative;display:inline-block';
				pictureDiv.appendChild(container);
			}

			return new Promise((resolve) => {
				SyFormPicture.createThumbnail(file, input, id, resolve);
			});
		});

		Promise.all(promises).then((values) => {
			values.forEach((value) => {
				if (value.image) {
					data[value.id] = { image: value.image };
				} else {
					let divToRemove = pictureDiv.querySelector(`div[data-id="${value.id}"]`);
					if (divToRemove) {
						divToRemove.remove();
					}
				}
			});

			loader.style.display = 'none';
			hiddenField.value = JSON.stringify(data);
			hiddenField.dispatchEvent(new Event('change', { bubbles: true }));
			hiddenField.dispatchEvent(new Event('input', { bubbles: true }));
			if (submit) submit.disabled = false;
		});
	},

	createThumbnail: function (f, input, id, callback) {
		if (!f.type.match('image.*')) {
			alert(input.dataset.alertImage);
			callback({ id: id, image: false });
			return;
		}

		var reader = new FileReader();

		reader.onload = function () {
			let img = new Image();
			img.onload = function () {
				let minWidth = parseInt(input.dataset.imgMinWidth);
				let minHeight = parseInt(input.dataset.imgMinHeight);
				let maxWidth = parseInt(input.dataset.imgMaxWidth);
				let maxHeight = parseInt(input.dataset.imgMaxHeight);
				let quality = parseFloat(input.dataset.imgQuality);
				let width = img.width;
				let height = img.height;

				if (width < minWidth || height < minHeight) {
					alert(input.dataset.alertDimension);
					callback({ id: id, image: false });
					return;
				}

				const pictureDiv = SyFormPicture.getNextSibling(input, '.sy-picture-div');

				let targetDiv = pictureDiv.querySelector(`div[data-id="${id}"]`);
				if (!targetDiv) {
					targetDiv = document.createElement('div');
					targetDiv.setAttribute('data-id', id);
					targetDiv.className = 'sy-picture-container';
					pictureDiv.appendChild(targetDiv);
				}

				targetDiv.innerHTML = `
					<img class="sy-picture-img img-fluid rounded" src="${img.src}" style="margin:10px;max-width:250px;max-height:250px" />
					<button style="position:absolute;top:10px;right:0;display:flex;justify-content:center;align-items:center" class="btn btn-secondary btn-sm sy-picture-rm" data-id="${id}">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
							<path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
						</svg>
					</button>
					<input type="text" class="form-control sy-picture-caption" data-id="${id}" placeholder="${input.dataset.captionPlaceholder}" />
				`;

				if (width > maxWidth || height > maxHeight) {
					if (width / height > 1) {
						height = Math.round(height * maxHeight / width);
						width = maxWidth;
					} else {
						width = Math.round(width * maxWidth / height);
						height = maxHeight;
					}
				}

				var canvas = document.createElement('canvas');
				canvas.width = width;
				canvas.height = height;
				var ctx = canvas.getContext("2d");
				ctx.drawImage(img, 0, 0, width, height);
				callback({
					id: id,
					image: canvas.toDataURL("image/webp", quality).split(',')[1]
				});
			};
			img.src = reader.result;
		};

		reader.readAsDataURL(f);
	},

	removePicture: function (btn) {
		const pic = btn.closest('div.sy-picture-container');
		const hiddenField = SyFormPicture.getPreviousSibling(btn.closest('div.sy-picture-div'), 'input.sy-picture-input-hidden');
		let data = JSON.parse(hiddenField.value || '{}');
		pic.remove();
		delete data[btn.dataset.id];
		hiddenField.value = JSON.stringify(data);
		hiddenField.dispatchEvent(new Event('change', { bubbles: true }));
		hiddenField.dispatchEvent(new Event('input', { bubbles: true }));
	},

	updateCaption: function (input) {
		const hiddenField = SyFormPicture.getPreviousSibling(input.closest('div.sy-picture-div'), 'input.sy-picture-input-hidden');
		let data = JSON.parse(hiddenField.value || '{}');
		data[input.dataset.id].caption = input.value;
		hiddenField.value = JSON.stringify(data);
		hiddenField.dispatchEvent(new Event('change', { bubbles: true }));
		hiddenField.dispatchEvent(new Event('input', { bubbles: true }));
	},

	drawPictures: function (hidden) {
		let val = hidden.value || '{}';
		let pictures = JSON.parse(val);
		let html = '';
		let fileInput = SyFormPicture.getNextSibling(hidden, 'input[type=file].sy-picture-input-file');
		let placeholder = fileInput ? fileInput.dataset.captionPlaceholder : '';

		for (let id in pictures) {
			if (pictures.hasOwnProperty(id)) {
				let caption = pictures[id].caption || '';
				html += `
				<div class="sy-picture-container" style="position:relative;display:inline-block">
					<img class="sy-picture-img img-fluid rounded" src="data:image/png;base64,${pictures[id].image}" style="margin:10px;max-width:250px;max-height:250px" />
					<button style="position:absolute;top:10px;right:0;display:flex;justify-content:center;align-items:center" class="btn btn-secondary btn-sm sy-picture-rm" data-id="${id}">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
							<path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
						</svg>
					</button>
					<input type="text" class="form-control sy-picture-caption" data-id="${id}" placeholder="${placeholder}" value="${caption}" />
				</div>`;
			}
		}

		let syPictureDiv = SyFormPicture.getNextSibling(hidden, 'div.sy-picture-div');
		if (syPictureDiv) {
			syPictureDiv.innerHTML = html;
		}
	}

};

document.body.addEventListener('change', function (event) {
	if (event.target.matches('.sy-picture-input-file')) {
		SyFormPicture.handleFileSelectBtn(event.target);
	}
});

document.body.addEventListener('click', function (event) {
	let target = event.target.closest('.sy-picture-rm');
	if (!target) return;
	SyFormPicture.removePicture(target);
});

document.body.addEventListener('change', function (event) {
	if (event.target.matches('.sy-picture-caption')) {
		SyFormPicture.updateCaption(event.target);
	}
});

document.querySelectorAll('.sy-picture-input-hidden').forEach(input => {
	SyFormPicture.drawPictures(input);
});

(function () {
	let observer = new MutationObserver(mutations => {

		for (let mutation of mutations) {
			if (mutation.type !== 'attributes') continue;

			if (mutation.attributeName !== 'value') continue;

			if (mutation.target.matches('.sy-picture-input-hidden')) {
				SyFormPicture.drawPictures(mutation.target);
			}
		}

	});

	observer.observe(document.body, { childList: true, subtree: true, attributeFilter: ['value'] });
})();