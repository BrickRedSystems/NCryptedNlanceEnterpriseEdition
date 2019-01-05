
<div class="form-group">
    <label class="control-label col-md-3"> %MEND_SIGN%
    Newsletter Content (%languageName%): &nbsp;
    </label>
    <div class="col-md-9">
        <textarea class="ckeditor form-control textarea-bg required" name="newsletter_content[%id%]" id="newsletter_content[%id%]" data-error-container="#editor_error" style="display: none;">
            %NLCONTENT%
        </textarea>
        <div id="editor_error"></div>
    </div>
</div>

<script type="text/javascript">$(function(){loadCKE("newsletter_content[%id%]");});</script>
