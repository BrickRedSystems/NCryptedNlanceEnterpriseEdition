<!-- BEGIN PAGE HEADER-->
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN PAGE TITLE & BREADCRUMB-->
        <?php
        echo $this->breadcrumb;
        ?>
        <!-- END PAGE TITLE & BREADCRUMB-->
    </div>
</div>
<!-- END PAGE HEADER-->
<div class="row">
    <div class="col-md-12">
        <!-- Begin: life time stats -->
        <div class="portlet box blue-dark">
            <div class="portlet-title ">
                <div class="caption">
                    <i class="fa fa-dot-circle-o"></i><?php echo $this->headTitle; ?>
                </div>
                <div class="actions portlet-toggler">
                    <?php if (in_array('add', $this->Permission)) { ?>
                        <a href="ajax.<?php echo $this->module; ?>.php?action=add" class="btn blue btn-add"><i class="fa fa-plus"></i> Add</a>
                    <?php } ?>
                    <div class="btn-group"></div>
                </div>
            </div>
            <div class="portlet-body portlet-toggler">
                <table id="example123" class="table table-striped table-bordered table-hover"></table>
            </div>
            <div class="portlet-body portlet-toggler pageform" style="display:none;"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
var sol;
    $(function () {
        OTable = $('#example123').dataTable({
        bProcessing: true,
                bServerSide: true,
                sAjaxSource: "ajax.<?php echo $this->module; ?>.php",
                fnServerData: function (sSource, aoData, fnCallback) {
                    $.ajax({
                        dataType: 'json',
                        type: "POST",
                        url: sSource,
                        data: aoData,
                        success: fnCallback
                    });
                },
                "aaSorting" : [],
                aoColumns: [
                { "sName": "id", 'sTitle': "Top Skill ID"},
                { sName: "skillName", sTitle : "Skill name"}
<?php if (in_array('status', $this->Permission)) { ?>
                    , { "sName": "status", 'sTitle' : 'Show on home?', bSortable:false, bSearchable:false}
<?php } ?>
<?php if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) { ?>
                    , {"sName": "operation", 'sTitle': 'Operation', bSortable: false, bSearchable: false}
<?php } ?>
                ],
                fnServerParams: function(aoData){setTitle(aoData, this)},
                fnDrawCallback: function(oSettings) {
                $('.make-switch').bootstrapSwitch();
                $('.make-switch').bootstrapSwitch('setOnClass', 'success');
                $('.make-switch').bootstrapSwitch('setOffClass', 'danger');

            }
    });
    $('.dataTables_filter').css({float: 'right'});
    $('.dataTables_filter input').addClass("form-control input-inline");
    $('.dataTables_length select').addClass("form-control");

    $.validator.addMethod('pagenm', function (value, element) {
        return /^[a-zA-Z0-9][a-zA-Z0-9\-\_]*$/.test(value);
    }, 'Page name is not valid. Only alphanumeric and -,_ are allowed');
    
    });
    
    $(document).on('click', '#submitAddForm', function (e) {
        e.preventDefault();

        $("#frmCont").validate({
            ignore: [],
            errorClass: 'help-block',
            errorElement: 'span',
            rules: {
                skillName: {
                    required: true,
                    remote: {
                        url: "<?php echo SITE_ADM_MOD . $this->module ?>/ajax.<?php echo $this->module; ?>.php",
                        data: {
                            id: $('#id').val()
                        }
                    }
                },
                skill_description: {
                    required: true,
                },
                "skills[]":{
                    required:true
                }
            },
            messages: {
                skillName: {
                    required: "Please enter skill name.",
                    remote: "Entered skill name already exists."
                },
                skill_description: {
                    required: "Please enter skill description.",
                },
                "skills[]":{
                    required:"Please select a skill for this top skill."
                }
            },
            highlight: function (element) {
                $(element).closest('.form-group').addClass('has-error');
            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error');
            },
            errorPlacement: function (error, element) {
                if (element.attr("data-error-container")) {
                    error.appendTo(element.attr("data-error-container"));
                }else if(element.hasClass("sol-checkbox")){
                    error.insertAfter("#skills");
                } else {
                    error.insertAfter(element);
                }
            }
        });
        
        if ($("#frmCont").valid()) {
            ajaxFormSubmit("#frmCont");
        } else {
            return false;
        }
    });
    



//////cropping/////
$(document).on('change', '#profileImg', function(event) {
        var _this = $(this);
        var value = _this.val();
        var allowedFiles = ["jpg", "jpeg", "png"];
        var extension = value.split('.').pop().toLowerCase();

        if(value && value!='') {
            if ($.inArray(extension, allowedFiles) < 0) {
                toastr['info']("Please select valid image. (e.g. jpg, jpeg, png)");
            } else if (this.files[0].size > 4194304) {                
                toastr['info']("Image size must be less then 4MB");
            } else {
                var url = URL.createObjectURL(event.target.files[0]);
	            var img = $('<img src="' + url + '">');
	            $('.avatar-wrapper').empty().html('<img src="' + url + '">');
	            $('#avatar-modal').modal('show');
            }
        }else {
			event.preventDefault();
        }
    });

	$(document).on('hidden.bs.modal', '#avatar-modal', function() {
        $('.avatar-wrapper img').cropper('destroy');
        $('.avatar-wrapper').empty();
    });

    $(document).on('shown.bs.modal', '#avatar-modal', function() {
        $('.avatar-wrapper img').cropper({
            aspectRatio: 1/1,
            strict: true,
            crop: function(e) {
                var json = [
                    '{"x":' + e.x,
                    '"y":' + e.y,
                    '"height":' + e.height,
                    '"width":' + e.width,
                    '"rotate":' + e.rotate + '}'
                ].join();
                $('.avatar-data').val(json);
            }
        });
    });

    $(document).on('click', '#btnCrop', function() {
        var avatarForm = $('.avatar-form');
        var frmCont = $('#frmCont');
        var url = avatarForm.attr('action');

        var data =  new FormData(frmCont[0]);
        data.append('avatar_src', $('#avatar_src').val());
        data.append('avatar_data', $('#avatar_data').val());

        $.ajax(url, {
            type: 'post',
            data: data,
            dataType: 'json',
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('.loading').fadeIn();
            },
            success: function(data) {
                if(data.state==200) {
                    $('#profile_image').val(data.image);
                    $('#img_preview').attr('src', data.source);
                    $('#avatar-modal').modal('hide');
                } else {}
            },
            complete: function() {
                $('.loading').fadeOut();
            }
        });
    });
    
//////cropping/////    
</script>
