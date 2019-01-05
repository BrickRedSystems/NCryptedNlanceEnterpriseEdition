<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate"> 
	<div class="form-body">
		<div class="clearfix"></div>
		<div class="form-group" id="123"> 
			<label class="control-label col-md-3">Project Title&nbsp;:</label> <div class="col-md-4"> <p class="form-control-static">%TITLE%</p> </div>
		</div>
		<div class="clearfix"></div>
		<div class="form-group" id="123"> 
			<label class="control-label col-md-3">Sender Name&nbsp;:</label> <div class="col-md-4"> <p class="form-control-static">%USER_NAME%</p> </div>
		</div>
		<div class="clearfix"></div>
		<div class="form-group" id="123"> 
			<label class="control-label col-md-3">Sender's User Type&nbsp;:</label> <div class="col-md-4"> <p class="form-control-static">%UTYPE%</p> </div>
		</div>
		<div class="clearfix"></div>
		<div class="form-group" id="123"> 
			<label class="control-label col-md-3">Reply To&nbsp;:</label> <div class="col-md-4"> <p class="form-control-static">%EMAIL%</p> </div>
		</div>
		<div class="clearfix"></div>
		<div class="form-group" id="123"> 
			<label class="control-label col-md-3">Reported On&nbsp;:</label> <div class="col-md-4"> <p class="form-control-static">%DATE%</p> </div>
		</div>
		<div class="clearfix"></div>

		<div class="form-group"> 
		<label for="reply" class="control-label col-md-3">Your Reply&nbsp;:</label> 
			<div class="col-md-6">
				<textarea class="form-control textarea-bg required" name="reply" id="reply"></textarea> 
			</div>
		</div>
		<div class="flclear clearfix"></div>
		<input type="hidden" name="type" id="type" value="%TYPE%"><div class="flclear clearfix"></div>
		<input type="hidden" name="id" id="id" value="%ID%"><div class="padtop20"></div>
	</div>
	<div class="form-actions fluid">
		<div class="col-md-offset-3 col-md-9">
			<button type="submit" name="submitAddForm" class="btn green" id="submitAddForm">Send</button>
			<button type="button" name="cn" class="btn btn-toggler" id="cn">Cancel</button>
		</div>
	</div>
</form>