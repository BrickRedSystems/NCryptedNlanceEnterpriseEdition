<script type="text/javascript">
    $(document).on('submit', '#frmSS', function (e) {
        $("#frmSS").validate({
            ignore: [],
            errorClass: 'help-block',
            errorElement: 'span',
            highlight: function (element) {
                $(element).closest('.form-group').addClass('has-error');
            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error');
            },
            errorPlacement: function (error, element) {
                if (element.attr("data-error-container")) {
                    error.appendTo(element.attr("data-error-container"));
                } else {
                    error.insertAfter(element);
                }
            }
        });
        if ($("#frmSS").valid()) {
            return true;
        } else {
            return false;
        }
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
                <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="frmSS" id="frmSS" class="form-horizontal" enctype="multipart/form-data">
                    <div class="form-body">
                        <?php echo $this->getForm; ?>
                    </div>	
                </form> 
            </div>
        </div>   
    </div>
</div>    	