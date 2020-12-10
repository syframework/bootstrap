<input type="hidden" name="MAX_FILE_SIZE" value="{MAX_UPLOAD_SIZE}" />
<button class="btn btn-primary" onclick="$(this).next().click(); return false;"><span class="fas fa-{ICON}"></span> {LABEL}</button>
<input name="file" type="file" class="d-none" onchange="this.form.submit()" accept="{ACCEPT}" />
<p class="help-block">{"Maximun size"} {MAX_SIZE} {"Mb"}</p>