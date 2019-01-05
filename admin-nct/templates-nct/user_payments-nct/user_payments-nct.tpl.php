<script type="text/javascript">
	$(function() {
		OTable = $('#example123').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			aaSorting : [[0, 'asc']],
			"sAjaxSource": "ajax.<?php echo $this->module; ?>.php",
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
				{ "sName": "fullName", sTitle: "user name"},
				{ "sName": "email", sTitle: "email"},
				{ "sName": "total_deposit_funds", sTitle: "total deposit funds (USD)"},
				{ "sName": "total_requested__redeem_funds", sTitle: "total requested redeem funds (USD)"},
				{ "sName": "total_paid_redeem_founds", sTitle: "total paid redeem funds (USD)"},
				{ "sName": "total_commission_paid", sTitle: "total commission paid (USD)",bSortable:false},
				{ "sName": "current_funds", sTitle: "current funds (USD)"},
				{ "sName": "total_credits_boughts", sTitle: "total credits boughts"},
				{ "sName": "credits_used", sTitle: "credits used"},
				{ "sName": "current_credits", sTitle: "current credits"},
				{ "sName": "total_of_featured_projects_bought", sTitle: "total of featured projects bought"},
				{ "sName": "total_of_milestones_bought", sTitle: "total of milestones bought (USD)"}
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