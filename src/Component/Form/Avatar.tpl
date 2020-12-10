<div class="text-center mb-1">
	<p>
		<img id="avatar-original" class="rounded-circle border border-secondary" src="{IMG_SRC}" style="width: {SIZE}px; height: {SIZE}px" />
	</p>
	<button class="btn btn-primary" onclick="$('#avatar-input-file').click(); return false">
		<i class="fas fa-image"></i> {"Choose image"}
	</button>
	<input id="avatar-input-file" data-csrf="{CSRF_TOKEN}" data-upload-url="{UPLOAD_URL}" data-size="{SIZE}" data-alert-image="{'Selected file is not an image'}" data-alert-dimension="{'Picture is too small'}" type="file" class="d-none" onclick="this.value=null" accept="image/*" />
</div>

<div id="avatar-modal" class="modal fade" data-backdrop="static">
	<div class="modal-dialog modal-dialog-centered modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">{"Crop picture"}</h4>
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
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

<div id="avatar-progress-modal" class="modal fade" data-backdrop="static">
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