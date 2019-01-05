<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
    <div class="form-body">
     	<div class="form-group">
     		<label for="firstName" class="control-label col-md-3"><font color="#FF0000">*</font>First Name: &nbsp;</label>
     		<div class="col-md-4">
     			<input type="text" class="form-control logintextbox-bg required" name="firstName" id="firstName" value="%FIRSTNAME%">
     		</div>
     	</div>
     	<div class="form-group">
     		<label for="lastName" class="control-label col-md-3"><font color="#FF0000">*</font>Last Name: &nbsp;</label>
     		<div class="col-md-4">
     			<input type="text" class="form-control logintextbox-bg required" name="lastName" id="lastName" value="%LASTNAME%">
     		</div>
     	</div>
        <div class="form-group">
            <label for="profileImg" class="control-label col-md-3"><font color="#FF0000">*</font>Profile Image: &nbsp;</label>
            <div class="col-md-4">
                <input type="file" class="form-control logintextbox-bg" name="profileImg" id="profileImg" accept="image/*">
                <br /><br />
                <img id="img_preview" src="%OUTPUTPROFILEIMG%" style="height: 60px; width: 50%">
            </div>
        </div>
        <div class="form-group">
            <label for="country_id" class="control-label col-md-3"> <font color="#FF0000">*</font>Select location:&nbsp;</label>
            <div class="col-md-4">
                <select name="country_id" id="country_id" class="form-control selectBox-bg required">
          		    <option value="">Please Select Country</option>
          			%COUNTRIES_OPTION%
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="genre_id" class="control-label col-md-3"> <font color="#FF0000">*</font>Genre:&nbsp;</label>
            <div class="col-md-4">
        	    <select name="genre_id" id="genre_id" class="form-control selectBox-bg required">
          		    <option value="">Please Select Genre</option>
          			%GENRES_OPTION%
        	    </select>
            </div>
        </div>
        <div class="form-group">
         	<label for="lang_id" class="control-label col-md-3"> <font color="#FF0000">*</font>Speaking Language:&nbsp;</label>
          	<div class="col-md-4">
            	<select name="lang_id" id="lang_id" class="form-control selectBox-bg required">
              		<option value="">Please Select Language</option>
              			%LANGUAGES_OPTION%
            	</select>
          	</div>
        </div>
     	<div class="form-group">
     	 	<label class="control-label col-md-3">Status: &nbsp;</label>
     	 	<div class="col-md-4">
     	 	 	<div class="radio-list" data-error-container="#form_2_Status: _error">
         	 	    <label class="">
                        <input class="radioBtn-bg required" id="y" name="status" type="radio" value="y" %STATUS_A%> Active
                    </label><span for="status" class="help-block"></span>
         	 	    <label class="">
         	 	        <input class="radioBtn-bg required" id="n" name="status" type="radio" value="n" %STATUS_D%> Deactive
                    </label><span for="status" class="help-block"></span>
     	 	    </div>
     	 	    <div id="form_2_Status: _error"></div>
     	 	</div>
     	</div>
     	<div class="flclear clearfix"></div>
        <input type="hidden" name="profile_image" id="profile_image" value="%OLD_IMAGE%"/>
        <input type="hidden" name="height" id="height" value="250" />
        <input type="hidden" name="width" id="width" value="200" />
        <input type="hidden" name="dest_site_folder" id="dest_folder" value="%SITE_PROFILEIMG%" />
        <input type="hidden" name="dest_dir_folder" id="dest_folder" value="%DIR_PROFILEIMG%" />
        <input type="hidden" name="type" id="type" value="%TYPE%">
        <input type="hidden" name="id" id="id" value="%ID%">
        <div class="padtop20"></div>
    </div>
    <div class="form-actions fluid">
    	<div class="col-md-offset-3 col-md-9">
    		<button type="submit" name="submitAddForm" class="btn green" id="submitAddForm">Submit</button><button type="button" name="cn" class="btn btn-toggler" id="cn">Cancel</button>
    	</div>
    </div>
</form>

<div class="modal fade" id="avatar-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form class="avatar-form" action="<?php echo SITE_ADM_INC.'crop-nct.php'; ?>" enctype="multipart/form-data" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="avatar-modal-label">Change Profile</h4>
                </div>
                <div class="modal-body">
                    <div class="avatar-body">

                        <!-- Upload image and data -->
                        <div class="avatar-upload">
                            <input type="hidden" class="avatar-src" name="avatar_src" id="avatar_src" />
                            <input type="hidden" class="avatar-data" name="avatar_data" id="avatar_data" />
                        </div>

                        <!-- Crop and preview -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="avatar-wrapper"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="btnCrop" name="btnCrop" type="button" class="btn btn-primary">Crop</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>