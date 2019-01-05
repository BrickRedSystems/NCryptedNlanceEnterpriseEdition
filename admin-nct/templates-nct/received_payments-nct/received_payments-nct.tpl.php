<script type="text/javascript">
    $(function() {
	  	OTable = $('#example123').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			aaSorting : [[0, 'desc']],
			"sAjaxSource": "ajax.<?php echo $this->module;?>.php",
			"fnServerData": function (sSource, aoData, fnCallback) {
				var userType = $('#userType').val(),
					userType = {"name":"userType", "value":userType}
					aoData.push(userType);
				$.ajax({
				   "dataType": 'json',
				   "type": "POST",
				   "url": sSource,
				   "data": aoData,
				   "success": fnCallback
				});
			 },
			 "aoColumns": [
				{ "sName": "id", 'sClass' : 'hidden'},
				{ "sName": "userName", 'sTitle' : 'User Name'},
				{ "sName": "userType", 'sTitle' : 'User Type'},
				{ "sName": "totalAmount", 'sTitle' : 'Total Amount Received'},
				{ "sName": "adminCommission", 'sTitle' : 'Admin Commission'},
				{ "sName": "paypal_fees", 'sTitle' : 'Paypal Fees On Received Amount'},
				{ "sName": "paymentType", 'sTitle' : 'Payment Type'},					
				{ "sName": "transactionId", 'sTitle' : 'Transaction Id'},
				{ "sName": "createdDate", 'sTitle' : 'Payment Date'}
			],
			"fnServerParams": function(aoData){setTitle(aoData, this)},
			"fnDrawCallback": function( oSettings ) {
				$('.make-switch').bootstrapSwitch();
				$('.make-switch').bootstrapSwitch('setOnClass', 'success');
				$('.make-switch').bootstrapSwitch('setOffClass', 'danger');
			}

   });
	$('.dataTables_filter').css({float:'right'});
	$('.dataTables_filter input').addClass("form-control input-inline");

	$.validator.addMethod('pagenm',function (value, element) {
		return /^[A-Za-z0-9-_]*$/.test(value);
		},'Page name is not valid. Only alphanumeric, - and _ are allowed'
	);

	$('select#userType').change( function() {
		OTable.fnDraw();
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
					<div class="col-md-4">
	            		<div class="form-group">
			            	<select name="userType" id="userType" class="form-control selectBox-bg">
			            		<option value="">Select User Type</option>
			                	<option value="p">Provider</option>
			                	<option value="c">Customer</option>
							</select>
						</div>
					</div>
            	</div>
            	<div class="flclear clearfix"></div>
                <table id="example123" class="table table-striped table-bordered table-hover"></table>
            </div>
            <div class="portlet-toggler pageform" style="display:none;"></div>
        </div>
    </div>
</div>


<script type="text/javascript">
$(document).ready(function(){
	$('.btn-filter').click(function(){ $('#filters').slideToggle(300); });
});
</script>