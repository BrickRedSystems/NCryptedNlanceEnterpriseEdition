<script type="text/javascript">
    $(function () {
        $(document).on('submit', '#frmCP', function (e) {
            $("#frmCP").on('submit', function () {
                for (var instanceName in CKEDITOR.instances) {
                    CKEDITOR.instances[instanceName].updateElement();
                }
            })
            $("#frmCP").validate({
                ignore: [],
                errorClass: 'help-block',
                errorElement: 'span',
                rules: {
                    opasswd: {
                        required: true
                    },
                    passwd: {
                        required: true,
                        minlength: 6,
                        maxlength: 12,
                    },
                    cpasswd: {
                        required: true,
                        minlength: 6,
                        maxlength: 12,
                        equalTo: "#passwd"
                    }
                },
                messages: {
                    opasswd: {
                        required: "&nbsp;Please enter current password"
                    },
                    passwd: {
                        required: "&nbsp;Please enter new password", 
                        minlength: "&nbsp;At least 6 characters.",
                        maxlength: "&nbsp;At least 12 characters.",
                    },
                    cpasswd: {
                        required: "&nbsp;Please confirm new password", 
                        minlength: "&nbsp;At least 6 characters.",
                        maxlength: "&nbsp;At least 12 characters.", 
                        equalTo: "&nbsp;Password do not match."
                    }

                },
                errorPlacement: function (error, element) {
                    if (element.attr("data-error-container")) {
                        error.appendTo(element.attr("data-error-container"));
                    } else {
                        error.insertAfter(element);
                    }
                }
            });
            if ($("#frmCP").valid()) {
                return true;
            } else {
                return false;
            }
        });
    });
</script>	
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
        <div class="portlet box blue-dark">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-reorder"></i><?php echo $this->headTitle; ?>
                </div>
            </div>
            <div class="portlet-body form">
                <?php echo $this->getForm; ?>
            </div>
        </div>   
    </div>
</div>
