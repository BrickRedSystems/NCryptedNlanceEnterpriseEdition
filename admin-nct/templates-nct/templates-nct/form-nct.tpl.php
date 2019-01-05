<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
	<div class="form-body">
		
		<div class="form-group">
            <label class="control-label col-md-3"> 
                Types: &nbsp;
            </label>
            <div class="col-md-4">
                <textarea class="ckeditor form-control textarea-bg" name="types" id="types">%TYPES%</textarea>
            </div>
        </div>
        <div class="padtop10 flclear"></div>
        <div class="form-group">
            <label class="control-label col-md-3"> 
                Constant: &nbsp;
            </label>
            <div class="col-md-4">
                <textarea class="ckeditor form-control textarea-bg" name="constant" id="constant">%CONSTANT%</textarea>
            </div>
        </div>
        <div class="padtop10 flclear"></div>
		<div class="form-group">
			<label class="control-label col-md-3"> 
				Description: &nbsp;
			</label>
			<div class="col-md-4">
				<textarea class="ckeditor form-control textarea-bg" name="description" id="description">%DESCRIPTION%</textarea>
			</div>
		</div>
		<div class="padtop10 flclear"></div>
		%html%
		<input type="hidden" name="type" id="type" value="%TYPE%">
		<div class="flclear clearfix"></div>
		<input type="hidden" name="id" id="id" value="%ID%">
		<div class="padtop20"></div>
	</div>
	<div class="form-actions fluid">
		<div class="col-md-offset-3 col-md-9">
			<button type="submit" name="submitAddForm" class="btn green" id="submitAddForm">Submit</button>
			<button type="button" name="cn" class="btn btn-toggler" id="cn">Cancel</button>
		</div>
	</div>
</form>