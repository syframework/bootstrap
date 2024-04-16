<input type="hidden" name="MAX_FILE_SIZE" value="{MAX_UPLOAD_SIZE}" />
<button class="btn btn-primary" onclick="this.nextElementSibling && this.nextElementSibling.click(); return false;">{ICON} {LABEL}</button>
<input name="file" type="file" class="d-none" onchange="this.form.submit()" accept="{ACCEPT}" />
<p class="help-block">{"Maximun size"} {MAX_SIZE} {"Mb"}</p>