<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
    <div class="form-body">
        %html%
        
        <div class="form-group">
            <label for="profileImg" class="control-label col-md-3"><font color="#FF0000">*</font>Top skill image: &nbsp;</label>
            <div class="col-md-4">
                <input type="file" class="form-control logintextbox-bg " name="profileImg" id="profileImg" accept="image/*">
                <br /><br />
                <img id="img_preview" src="%OUTPUTPROFILEIMG%" style="height:100px; width:100px;">
            </div>
        </div>
        
        <div class="form-group"> 
            <label for="skillId" class="control-label col-md-3">%MEND_SIGN%Skills: &nbsp;</label> 
            <div class="col-md-4"> 
                <select name="skills[]" id="skills" class="selectBox-bg" multiple="multiple">%SKILL_OPTIONS%</select>
            </div>
        </div>
        
        <div class="form-group"> 
            <label for="skill_description" class="control-label col-md-3">%MEND_SIGN%Skill description : &nbsp;</label> 
            <div class="col-md-4"> 
                <textarea name="skill_description" id="skill_description" class="form-control required">%SKILL_DESCRIPTION%</textarea>
            </div>
        </div>

        <div class="form-group"> 
            <label class="control-label col-md-3">Status: &nbsp;</label> 
            <div class="col-md-4"> 
                <div class="radio-list" data-error-container="#form_2_Status: _error"> 
                    <label class=""> 
                        <input class="radioBtn-bg required" id="y" name="show_on_home" type="radio" value="y" %STATUS_A%> Active
                    </label>
                    <span for="status" class="help-block"></span> 

                    <label class=""> 
                        <input class="radioBtn-bg required" id="n" name="show_on_home" type="radio" value="n" %STATUS_D%> Deactive
                    </label>
                    <span for="status" class="help-block"></span> 
                </div>
                <div id="form_2_Status: _error"></div> 
            </div>
        </div>
        <div class="flclear clearfix"></div>
        
        <input type="hidden" name="profile_image" id="profile_image" value="%OLD_IMAGE%"/>
        <input type="hidden" name="height" id="height" value="500" />
        <input type="hidden" name="width" id="width" value="500" />
        <input type="hidden" name="dest_site_folder" id="dest_folder" value="%SITE_SKILLIMG%" />
        <input type="hidden" name="dest_dir_folder" id="dest_folder" value="%DIR_SKILLIMG%" />
        
        
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



<script type="text/javascript">
    $(document).ready(function(){
        /* Start Multiple Check box */
        sol = $('#skills').searchableOptionList({
            maxHeight: '250px',
            showSelectAll: true
        });
        /* Multiple Check box End */        
    });
</script>