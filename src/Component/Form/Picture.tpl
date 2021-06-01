<input class="picture-input-hidden" type="hidden" name="{NAME}" />
<button class="btn btn-{COLOR} btn-{SIZE} {CLASS}" onclick="$(this).siblings('input.picture-input-file').click();return false" title="{TITLE}" data-title="{TITLE}" data-trigger="hover" data-container="body"><span class="fas fa-{ICON}"></span> {LABEL}</button>
<input type="file" class="picture-input-file d-none" onclick="this.value=null" accept="image/*" multiple data-caption-placeholder="{'Add a caption'}" />
<div class="loader text-center" style="display:none"><i class="fas fa-circle-notch fa-spin"></i></div>
<div class="picture-div text-center"></div>
