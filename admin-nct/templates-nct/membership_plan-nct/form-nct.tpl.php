<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
	<div class="form-body">
		%html%	 	
	 	<div class="form-group"> 
	 		<label for="plan_price" class="control-label col-md-3"><font color="#FF0000">*</font>Plan Price/Month (%CURR_CODE%): &nbsp;</label> 
	 		<div class="col-md-4"> 
	 			<input type="text" class="form-control logintextbox-bg required" name="price" id="price" value="%PRICE%"> 
	 		</div>
	 	</div>
		<div class="form-group"> 
	 		<label for="plan_price" class="control-label col-md-3"><font color="#FF0000">*</font>Plan Credits : &nbsp;</label> 
	 		<div class="col-md-4"> 
	 			<input type="text" class="form-control logintextbox-bg required" name="credits" id="credits" value="%CREDITS%"> 
	 		</div>
	 	</div>		
	 	
	 	<div class="form-group">
		      <label class="control-label col-md-3">Status: &nbsp;</label>
		      <div class="col-md-4">
		        <div class="radio-list" data-error-container="#form_2_Status: _error">
		          <label class="">
		            <input class="radioBtn-bg required" id="y" name="isactive" type="radio" value="y" %STATIC_A%>
		            Active</label>
		          <span for="status" class="help-block"></span>
		          <label class="">
		            <input class="radioBtn-bg required" id="n" name="isactive" type="radio" value="n" %STATIC_D%>
		            Inactive</label>
		          <span for="status" class="help-block"></span> </div>
		        <div id="form_2_Status: _error"></div>
		      </div>
		    </div> 		
		 <div class="flclear clearfix"></div>                  
 		<input type="hidden" name="type" id="type" value="%TYPE%">
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