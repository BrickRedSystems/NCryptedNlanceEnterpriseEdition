<form action="" method="post" name="frmContNL" id="frmContNL" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
    <div class="form-body">

        <div class="form-group" id="userDisp"> 
            <label for="users" class="control-label col-md-3">%MEND_SIGN%Users : &nbsp;</label>
            <div class="col-md-6"> 
                <div id="users" class="radio-list"> 
                    %USERS%
                </div>
                <div id="users_error_container" class="col-sm-9 col-md-9 nopadding"></div>
            </div>
        </div>

        <div class="flclear clearfix"></div>
        <input type="hidden" name="type" id="type" value="%TYPE%"><div class="flclear clearfix"></div>
        <input type="hidden" name="id" id="id" value="%ID%"><div class="padtop20"></div>
        
    </div>
    
    <div class="form-actions fluid">
        <div class="col-md-offset-3 col-md-9">
            <button type="submit" name="submitAddFormNL" class="btn green" id="submitAddForm">Submit</button>
            <button type="button" name="cn" class="btn btn-toggler" id="cn">Cancel</button>
        </div>
    </div>
</form>
