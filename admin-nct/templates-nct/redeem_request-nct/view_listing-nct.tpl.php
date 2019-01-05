<script type="text/javascript">
	$(function() {
  		OTable = $('#example123_listing').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			"bFilter": false,
			"sAjaxSource": "ajax.<?php echo $this->module;?>.php?action=viewlistingrid&id=<?php echo $this->id; ?>",
			"fnServerData": function (sSource, aoData, fnCallback) {
				$.ajax({
				   "dataType": 'json',
				   "type": "POST",
				   "url": sSource,
				   "data": aoData,
				   "success": fnCallback
				});
			 },
			 "aoColumns": [
				{ "sName": "Title", 'sTitle' : 'Listing Title'}
				<?php if(in_array('status',$this->Permission)){ ?>
				,{ "sName": "status", 'sTitle' : 'Status' ,bSearchable:false}
				<?php } ?>

				<?php if(in_array('edit',$this->Permission) || in_array('delete',$this->Permission) || in_array('view',$this->Permission) ){ ?>
				,{ "sName": "operation", 'sTitle' : 'Operation' ,bSortable:false,bSearchable:false}
				<?php } ?>
			],
			"fnServerParams": function(aoData){setTitle(aoData, this)},
			"fnDrawCallback": function( oSettings ) {
				$('.another_switch').bootstrapSwitch();
				$('.another_switch').bootstrapSwitch('setOnClass', 'success');
				$('.another_switch').bootstrapSwitch('setOffClass', 'danger');

			}	
   		});
		$('.dataTables_filter').css({float:'right'});
		$('.dataTables_filter input').addClass("form-control input-inline");
	});
</script>

<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-dark">
			<div class="portlet-title ">
				<div class="caption"><i class="fa fa-list-alt"></i>View Listings</div>
				<!-- <div class="actions portlet-toggler">
					<?php if(in_array('delete',$this->Permission)){ ?>
						<a href="ajax.<?php echo $this->module; ?>.php?action=delete_activity&id=<?php echo $this->id; ?>" class="btn default btn-xs red btn-delete" ><i class="fa fa-trash-o"></i> Delete All Activities</a>
					<?php } ?>
					<div class="btn-group"></div>
				</div> -->
			</div>
			<div class="portlet-body portlet-toggler">
				<table id="example123_listing" class="table table-striped table-bordered table-hover">
				</table>
			</div>
			<div class="portlet-toggler pageform" style="display:none;"> </div>
		</div>
	</div>
</div>