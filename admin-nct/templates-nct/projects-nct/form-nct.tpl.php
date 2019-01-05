<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
	<div class="form-body">
		<div class="clearfix"></div>
		<div class="form-group" >
			<label class="control-label col-md-3"><font color="#FF0000">*</font>
				Project title : </label>
			<div class="col-md-3">
				<input type="text" class="form-control logintextbox-bg required" name="title" id="title" value="%TITLE%">
			</div>
		</div>

		<div class="clearfix"></div>
		<div class="form-group" >
			<label name= "description" class="control-label col-md-3"> 
				Description : </label>
			<div class="col-md-3">
			<textarea name="description" id="description" class="form-control required" rows="5" cols="100">%DESC%</textarea>	
				
			</div>
		</div>

		<div class="form-group">
			<label for="categoryId" class="control-label col-md-3"> <font color="#FF0000">*</font>Select category:&nbsp;</label>
			<div class="col-md-4">
				<select name="categoryId" id="categoryId" class="form-control selectBox-bg required">
					<option value="">Please select category</option>
					%CAT_OPTION%
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="subcategoryId" class="control-label col-md-3"> <font color="#FF0000"></font>Select sub-category:&nbsp;</label>
			<div class="col-md-4">
				<select name="subcategoryId" id="subcategoryId" class="form-control selectBox-bg ">
					<option value="">Please select sub-category</option>
					%SUBCAT_OPTION%
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="duration" class="control-label col-md-3"> <font color="#FF0000">*</font>Select duration:&nbsp;</label>
			<div class="col-md-4">
			<input type="text" class="form-control logintextbox-bg" name="duration" id="duration" value="%DURATION%">				
			</div>
		</div>

		<div class="form-group">
			<label for="jobStatus" class="control-label col-md-3"> <font color="#FF0000">*</font>Select project status:&nbsp;</label>
			<div class="col-md-4">
				<select name="jobStatus" id="jobStatus" class="form-control selectBox-bg required">
					<option value="">Please select project status</option>
					%STATUS_OPTION%
				</select>
			</div>
		</div>

		<div class="padtop10 flclear"></div>

		<div class="padtop10 flclear"></div>
		<div class="form-group">
			<label class="control-label col-md-3">Featured: &nbsp;</label>
			<div class="col-md-4">
				<div class="radio-list" data-error-container="#form_2_Status: _error">
					<label class="">
						<input class="radioBtn-bg required" id="y" name="isFeatured" type="radio" value="y" %FEATURED_Y%>
						Yes</label><span for="isFeatured" class="help-block"></span>
					<label class="">
						<input class="radioBtn-bg required" id="n" name="isFeatured" type="radio" value="n" %FEATURED_N%>
						No</label><span for="isFeatured" class="help-block"></span>
				</div>
				<div id="form_2_Status: _error"></div>
			</div>
		</div>
		<div class="flclear clearfix"></div>
		<input type="hidden" name="type" id="type" value="%TYPE%">
		<div class="flclear clearfix"></div>
		<input type="hidden" name="id" id="id" value = "%ID%" >
		

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
<script type="text/javascript">
$(function(){
	

	$(document).on("change","#categoryId",function(){
         var cId=$(this).val();
         
         if (cId>0) {
          $.ajax({
            url: '<?php echo SITE_ADM_MOD.$this->module.'/';?>ajax.<?php echo $this->module;?>.php',
            type: 'post',
            dataType: 'json',
            data: {action : 'getSubcategory',cid:cId},
            success:function(response){
            	$("#subcategoryId").find("option:not(:first-child)").remove();
                $("#subcategoryId").find("option:first").after(response.subcategory);
            }

        })
      }
    });

});
</script>
