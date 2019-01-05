<script type="text/javascript">

$(function() {
	OTable= $('#example123').dataTable( {
		bProcessing: true,
		bServerSide: true,
		/*"bFilter": false,*/
		aaSorting : [[0, 'desc']],
		sAjaxSource: "ajax.<?php echo $this->module;?>.php",
		fnServerData: function (sSource, aoData, fnCallback) {
			$.ajax({
			   dataType: 'json',
			   type: "POST",
			   url: sSource,
			   data: aoData,
			   success: fnCallback
			});
		 },
		 aoColumns: [
		 	{ "sName": "id", 'sTitle' : 'ID', 'sClass' : 'hidden'},
			{ "sName": "membership", 'sTitle' : 'Plan Name'},
			{ "sName": "price", 'sTitle' : 'Plan Price/Month'},
			{ "sName": "credits", 'sTitle' : 'Plan Credits'}
			<?php if(in_array('status',$this->Permission)){ ?>
			,{ "sName": "isActive", 'sTitle' : 'Status' ,bSortable:false,bSearchable:false}
			<?php } ?>
			<?php if(in_array('edit',$this->Permission) || in_array('view',$this->Permission) ){ ?>
			,{ "sName": "operation", 'sTitle' : 'Operation' ,bSortable:false,bSearchable:false}
			<?php } ?>
		],
		fnServerParams: function(aoData){setTitle(aoData, this)},
		fnDrawCallback: function( oSettings ) {
			$('.make-switch').bootstrapSwitch();
			$('.make-switch').bootstrapSwitch('setOnClass', 'success');
			$('.make-switch').bootstrapSwitch('setOffClass', 'danger');
		}
	});
	
	$('.dataTables_filter').css({float:'right'});
	$('.dataTables_filter input').addClass("form-control input-inline"); 

	$.validator.addMethod('pagenm',function (value, element) { 
		return /^[a-zA-Z0-9][a-zA-Z0-9\-\_]*$/.test(value); 
		},'Page name is not valid. Only alphanumeric and -,_ are allowed'
	);
	$(document).on('submit','#frmCont', function(e){
		$("#frmCont").validate({
			ignore:[],
			errorClass: 'help-block',
			errorElement: 'span',
            highlight: function (element) {
			   $(element).closest('.form-group').addClass('has-error');
			},
			unhighlight: function (element) {
				$(element).closest('.form-group').removeClass('has-error');
			},
			rules: { 
				membership: { required: true },
				price: { required: true, min: 0},
				credits: { required: true, min: 0,number: true,digits: true},
				description: {required: true}
			},
			messages: {
				membership: { 
					required: 'Please enter plan name.'
				},
				price: { 
					required: 'Please enter price.', 
					min: 'Only numeric and positive values are allowed'
				},
				credits: { 
					required: 'Please enter credits.', 
					min: 'Only numeric and positive values are allowed',
					number: "Please etner digits value",
					digits: "Please etner digits value"
				},
				description: {
					required: 'Please add description to show users'
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
		if($("#frmCont").valid()){
			return true;
		}else{
			return false;
		}
	});
	
});	
</script>
<div class="row">
        <div class="col-md-12">
            <!-- BEGIN PAGE TITLE & BREADCRUMB-->
           	<?php
				echo $this->breadcrumb;
			?>
            <!-- END PAGE TITLE & BREADCRUMB-->
        </div>
    </div>
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-dark">
            <div class="portlet-title ">
                <div class="caption"><i class="fa fa-list-alt"></i><?php echo $this->headTitle; ?></div>
                <div class="actions portlet-toggler">
				<?php if(in_array('add',$this->Permission)){?>
                    <a href="ajax.<?php echo $this->module;?>.php?action=add" class="btn blue btn-add"><i class="fa fa-plus"></i> Add</a>
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