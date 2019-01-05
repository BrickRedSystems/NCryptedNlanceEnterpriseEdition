<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
	<div class="form-body">
		<div class="form-group">
			<label for="country" class="control-label col-md-3"><font color="#FF0000">*</font>
				Country: &nbsp;
			</label>
			<div class="col-md-4">
				<select class="form-control" name="country" id="country">
					%OPTION_C%
				</select>           
			</div>
		</div>
		<div class="form-group">
			<label for="state" class="control-label col-md-3"><font color="#FF0000">*</font>
				County: &nbsp;
			</label>
			<div class="col-md-4">
				<select class="form-control" name="state" id="state">
					%OPTION_S%
				</select>	
			</div>
		</div>
		<div class="form-group clearfix">
			<label for="cityName" class="control-label col-md-3"><font color="#FF0000">*</font>
				City Name: &nbsp;
			</label>
			<div class="col-md-4">
				<input type="text" class="form-control logintextbox-bg required" name="cityName" id="cityName" value="%CITY_NAME%"></div>
		</div>
		<div class="padtop10 flclear"></div>
		<div class="form-group">
	 	 	<label class="control-label col-md-3">Status: &nbsp;</label>
	 	 	 <div class="col-md-4"> 
	 	 	 	<div class="radio-list" data-error-container="#form_2_Status: _error"> 
	 	 	 <label class=""> <input class="radioBtn-bg required" id="y" name="status" type="radio" value="y" %STATUS_A%> Active</label><span for="status" class="help-block"></span> 
	 	 	 <label class=""> 
	 	 	 <input class="radioBtn-bg required" id="n" name="status" type="radio" value="n" %STATUS_D%> Deactive</label><span for="status" class="help-block"></span> 
	 	 	 </div>
	 	 	 <div id="form_2_Status: _error"></div>
	 	 	  </div>
	 	</div>
		<div class="flclear clearfix"></div>
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

<script type="text/javascript">
	$(function(){
		 $(document).on("change","#country",function(){
             var cId=$(this).val();
            if (cId>0) {
              $.ajax({
                url: '<?php echo SITE_ADM_MOD.$this->module.'/';?>ajax.<?php echo $this->module;?>.php',
                type: 'post',
                dataType: 'json',
                data: {action : 'getStates',cid:cId},
                success:function(response){
                    $("#state").html(response.states)
                  }

                })
              }
           });
	});
</script>