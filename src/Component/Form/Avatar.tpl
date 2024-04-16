<div class="text-center mb-1">
	<p>
		<img id="avatar-original" class="rounded-circle border border-secondary" src="{IMG_SRC}" style="width: {SIZE}px; height: {SIZE}px" />
	</p>
	<button class="btn btn-primary" onclick="$('#avatar-input-file').click(); return false">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor" style="width:1em; height:1em; vertical-align: -.125em;"><path d="M0 96C0 60.7 28.7 32 64 32H448c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96zM323.8 202.5c-4.5-6.6-11.9-10.5-19.8-10.5s-15.4 3.9-19.8 10.5l-87 127.6L170.7 297c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6h96 32H424c8.9 0 17.1-4.9 21.2-12.8s3.6-17.4-1.4-24.7l-120-176zM112 192a48 48 0 1 0 0-96 48 48 0 1 0 0 96z"/></svg>
		{"Choose image"}
	</button>
	<input id="avatar-input-file" data-csrf="{CSRF_TOKEN}" data-upload-url="{UPLOAD_URL}" data-size="{SIZE}" data-alert-image="{'Selected file is not an image'}" data-alert-dimension="{'Picture is too small'}" type="file" class="d-none" onclick="this.value=null" accept="image/*" />
</div>

<div id="avatar-modal" class="modal fade" data-bs-backdrop="static">
	<div class="modal-dialog modal-dialog-centered modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{"Crop picture"}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<div>
					<img id="avatar" />
				</div>
			</div>
			<div class="modal-footer">
				<button id="avatar-upload-btn" class="btn btn-primary"><span class="fas fa-upload"></span> {"Upload"}</button>
			</div>
		</div>
	</div>
</div>

<div id="avatar-progress-modal" class="modal" data-bs-backdrop="static">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="progress">
					<div id="avatar-progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
				</div>
				<div class="text-center">{"Please wait"}...</div>
			</div>
		</div>
	</div>
</div>