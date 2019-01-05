<script type="text/javascript">
    $(function () {
        
        ajaxSourceUrl = "<?php echo SITE_ADM_MOD.  $this->module; ?>/ajax.<?php echo $this->module; ?>.php";
        queryStringUrl = "";
        
        <?php if(isset($_REQUEST['day'])) { ?>
                if(queryStringUrl == "") {
                    queryStringUrl = "?day=<?php echo $_REQUEST['day']; ?>";
                } else {
                    queryStringUrl += "&day=<?php echo $_REQUEST['day']; ?>";
                }
        <?php } ?>
        
        <?php if(isset($_REQUEST['month'])) { ?>
                if(queryStringUrl == "") {
                    queryStringUrl = "?month=<?php echo $_REQUEST['month']; ?>";
                } else {
                    queryStringUrl += "&month=<?php echo $_REQUEST['month']; ?>";
                }
        <?php } ?>
        
        <?php if(isset($_REQUEST['year'])) { ?>
                if(queryStringUrl == "") {
                    queryStringUrl = "?year=<?php echo $_REQUEST['year']; ?>";
                } else {
                    queryStringUrl += "&year=<?php echo $_REQUEST['year']; ?>";
                }
        <?php } ?>
        
        ajaxSourceUrl += queryStringUrl;
        
        OTable = $('#dt_users').dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": ajaxSourceUrl,
            "fnServerData": function (sSource, aoData, fnCallback) {
            	var user_type = $('#user_type').val(),
					user_type = {"name":"user_type", "value":user_type}
					aoData.push(user_type);
					
                $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": fnCallback
                });
            },
            "aaSorting" : [],
            "aoColumns": [
                { sName: "userId", sTitle : 'User ID'},
                {"sName": "firstName", 'sTitle': 'First Name'},
                {"sName": "lastName", 'sTitle': 'Last Name'},
                {"sName": "email", 'sTitle': 'Email Address'},
                {"sName": "userType", 'sTitle': 'Type'},
                { sName: "createdDate", sTitle : 'Registered On'},
                {"sName": "status", 'sTitle': 'Status', bSortable: false, bSearchable: false},
                {"sName": "operation", 'sTitle': 'Operation', bSortable: false, bSearchable: false}
            ],
            "fnServerParams"
                    : function (aoData) {
                        setTitle(aoData, this)
                    },
            "fnDrawCallback"
                    : function (oSettings) {
                        $('.make-switch').bootstrapSwitch();
                        $('.make-switch').bootstrapSwitch('setOnClass', 'success');
                        $('.make-switch').bootstrapSwitch('setOffClass', 'danger');
                    }

        });
        $('.dataTables_filter').css({float: 'right'});
        $('.dataTables_filter input').addClass("form-control input-inline");

        $.validator.addMethod('pagenm', function (value, element) {
            return /^[a-zA-Z0-9][a-zA-Z0-9\_\-]*$/.test(value);
        }, 'Page name is not valid. Only alphanumeric and _ are allowed');
                
                
        $('select#user_type').change( function() {
			OTable.fnDraw();
		});
		$('.btn-filter').click(function(){ $('#filters').slideToggle(300); });	
		
		
        $(document).on('submit', '#frmCont', function (e) {
            $("#frmCont").on('submit', function () {
                for (var instanceName in CKEDITOR.instances) {
                    CKEDITOR.instances[instanceName].updateElement();
                }
            })
            $("#frmCont").validate({
                ignore: [],
                errorClass: 'help-block',
                errorElement: 'span',
                rules: {
                    page_name: {
                        pagenm: true,
                        remote: {
                            url: "<?php echo SITE_ADM_MOD . $this->module ?>/ajax.<?php echo $this->module; ?>.php",
                            type: "post",
                            async: false,
                            data: {ajaxvalidate: true, page_name: function () {
                                    return $("#page_name").val();
                                }, id: function () {
                                    return $("#id").val();
                                }},
                            complete: function (data) {
                                return data;
                            }
                        }
                    }
                },
                messages: {
                    page_name: {remote: 'Page name already exist'}
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
                    } else {
                        error.insertAfter(element);
                    }
                }
            });
            if ($("#frmCont").valid()) {
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
        <!-- Begin: life time stats -->
        <div class="portlet box blue-dark">
            <div class="portlet-title ">
                <div class="caption"><i class="fa fa-list-alt"></i><?php echo $this->headTitle; ?></div>
                
                <div class="actions portlet-toggler">
            		<a href="javascript:void(0);" class="btn yellow btn-filter"><i class="fa fa-filter"></i> Apply Filter</a>
                	<div class="btn-group"></div>
                </div>
            </div>
            <div class="portlet-body portlet-toggler">
            	<div style="display: none;" id="filters" class="portlet-body">
					<div class="col-md-12" style="padding-left: 0">
						<div class="col-md-4" style="padding-left: 0">
							<div class="form-group" style="padding-left: 0">
				            	<select name="user_type" id="user_type" class="form-control selectBox-bg">
				            		<option value="">Select User Type</option>
				                	<option value="c">Customer</option>
				                	<option value="p">Provider</option>
				                	
								</select>
							</div>
						</div>
	            		<div class="col-md-8"></div>
					</div>

            	</div>
            	<div class="actions portlet-toggler">
                    %VIEW_ALL_RECORDS_BTN%
                    <div class="btn-group"></div>
                </div>
                <table id="dt_users" class="table table-striped table-bordered table-hover"></table>
            </div>
            <div class="portlet-toggler pageform" style="display:none;"></div>
        </div>
    </div>
</div>     