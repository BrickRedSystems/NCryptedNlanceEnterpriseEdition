<div class="form-group" id="descwrap_%languageName%">
    <label for="pageDesc[%id%]"  class="control-label col-md-3"><font color="#FF0000">*</font>Page Description (%languageName%): &nbsp;</label>
    <div class="col-md-9">
    <textarea class="ckeditor form-control textarea-bg pageDesc" name="pageDesc[%id%]" id="pageDesc[%id%]" data-error-container="#editor_error_%id%" style="display: none;">%PAGE_DESCRIPTION%</textarea>
        <div id="editor_error_%id%"></div>
    </div>
</div>

<script type="text/javascript">$(function () {
    loadCKE("pageDesc[%id%]");
});
</script>

<div class="padtop10 flclear"></div>