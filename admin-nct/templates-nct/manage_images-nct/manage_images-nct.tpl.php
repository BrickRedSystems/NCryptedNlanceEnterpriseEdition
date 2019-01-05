<script type="text/javascript">
        $(function() {
	  OTable = $('#example123').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			"bPaginate": false,
			"bInfo": null,
			"sAjaxSource": "ajax.<?php echo $this->module;?>.php",
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
				{ "sName": "name", 'sTitle' : 'Name'},
				{ "sName": "filetype", 'sTitle' : 'File Type'},
				{ "sName": "image_name", 'sTitle' : 'Image/Video', bSortable:false},
				{ "sName": "operation", 'sTitle' : 'operation' ,bSortable:false,bSearchable:false}
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
		return /^[a-zA-Z0-9][a-zA-Z0-9\_\-\ ]*$/.test(value); 
		},'Category name is not valid. Only alphanumeric and _ are allowed'
	);
	$(document).on('submit','#frmCont', function(e){
		$("#frmCont").on('submit', function() {
			for(var instanceName in CKEDITOR.instances) {
				CKEDITOR.instances[instanceName].updateElement();
			}
		})
		$("#frmCont").validate({
			ignore:[],
			errorClass: 'help-block',
			errorElement: 'span',
			rules:{
				image:{
					extension: "jpg|bmp|png|gif|jpeg"
				},
				video:{
					extension: "mp4|webm|ogg"
				},
				name:{required:true}
			},
			messages:{
				image:{remote:'Invalid file type.'},
				name:{required:'Name is required.'}
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
                	<?php if(in_array('add',$this->Permission)){?>
                    <!-- <a href="ajax.<?php //echo $this->module;?>.php?action=add" class="btn blue btn-add"><i class="fa fa-plus"></i> Add</a> -->
                    <?php } ?>
                    <div class="btn-group"></div>
                </div>
            </div>
            <div class="portlet-body portlet-toggler">
                <table id="example123" class="table table-striped table-bordered table-hover"></table>
            </div>
            <div class="portlet-toggler pageform" style="display:none;"></div>
        </div>
    </div>
</div>     