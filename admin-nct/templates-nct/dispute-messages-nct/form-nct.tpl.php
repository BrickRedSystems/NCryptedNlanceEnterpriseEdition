<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
	<div class="form-body">
		<div class="clearfix"></div>
		<div class="form-group" >
			<label class="control-label col-md-3">
				Project title : </label>
			<div class="col-md-3">
				%TITLE%
			</div>
		</div>
		<div class="form-group" >
			<label class="control-label col-md-3">
				Dispute raised by : </label>
			<div class="col-md-3">
				%USER_NAME%
			</div>
		</div>	
		<div class="form-group" >
			<label name= "description" class="control-label col-md-3"> <font color="#FF0000">*</font>
				Subject : </label>
			<div class="col-md-3">
				<input type="text" class="form-control logintextbox-bg" name="subject" id="subject" value="%SUBJECT%">
			</div>
		</div>	
		<div class="form-group" >
			<label name= "description" class="control-label col-md-3"> <font color="#FF0000">*</font>
				Description : </label>
			<div class="col-md-3">
				<textarea name="description" id="description" class="form-control required" rows="5" cols="100">%DESC%</textarea>				
			</div>
		</div>
		
		

		<div class="form-group">
			<label for="categoryId" class="control-label col-md-3"> <font color="#FF0000">*</font>Your action for this dispute :&nbsp;</label>
			<div class="col-md-4">
				<select name="admin_judgement" id="admin_judgement" class="form-control selectBox-bg required">
					<option value="">Please select action</option>
					<option value="pending" >Pending</option>
					<option value="valid" >Valid</option>
					<option value="invalid" >Invalid</option>
				</select>
			</div>
		</div>

		<div class="flclear clearfix"></div>
		<input type="hidden" name="type" id="type" value="%TYPE%">		
		<input type="hidden" name="id" id="id" value = "%ID%" >
		<input type="hidden" name="projectId" id="projectId" value = "%PROJECTID%" >

		<div class="padtop20"></div>
	</div>
	<div class="form-actions fluid">
		<div class="col-md-offset-3 col-md-9">
			<button type="submit" name="submitAddForm" class="btn green" id="submitAddForm">
				Submit
			</button>
			<button type="button" name="cn" class="btn btn-toggler" id="cn">
				Cancel
			</button>
		</div>
	</div>
</form>
