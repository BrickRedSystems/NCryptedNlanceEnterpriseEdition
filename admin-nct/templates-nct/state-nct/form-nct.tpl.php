<form action="" method="post" name="frmCont" id="frmCont" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate">
  <div class="form-body">
    <div class="form-group">
      <label for="section" class="control-label col-md-3"><font color="#FF0000">*</font>Select Country: &nbsp;</label>
      <div class="col-md-4">
        <select name="section" id="section" class="form-control required">
          <option value="">Please Select</option>
          %SECTION_OPT%
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="page_name" class="control-label col-md-3"><font color="#FF0000">*</font>State Name: &nbsp;</label>
      <div class="col-md-4">
        <input type="text" class="form-control logintextbox-bg required" name="stateName" id="stateName" value="%STATE_NAME%">
      </div>
    </div>

    <div class="form-group">
      <label class="control-label col-md-3">Status: &nbsp;</label>
      <div class="col-md-4">
        <div class="radio-list" data-error-container="#form_2_Status: _error">
          <label class="">
            <input class="radioBtn-bg required" id="y" name="isActive" type="radio" value="y" %STATIC_A%>
            Active</label>
          <span for="status" class="help-block"></span>
          <label class="">
            <input class="radioBtn-bg required" id="n" name="isActive" type="radio" value="n" %STATIC_D%>
            Inactive</label>
          <span for="status" class="help-block"></span> </div>
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
