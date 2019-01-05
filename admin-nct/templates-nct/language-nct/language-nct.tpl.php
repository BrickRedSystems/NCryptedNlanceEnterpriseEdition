<div class="row">
    <div class="col-md-12">
    <!-- Begin: life time stats -->
    	<?php
        echo $this->breadcrumb;
        ?>
        <div class="portlet box blue-dark">
            <div class="portlet-title ">
                <div class="caption">
                <i class="fa"></i><?php echo $this->headTitle; ?>
                </div>
                <div class="actions portlet-toggler">
                	 <?php
					 	if(in_array('add',$this->Permission)){
			 		 ?>
	                    <a href="ajax.<?php echo $this->module; ?>.php?action=add" class="btn blue btn-add" ><i class="fa fa-plus"></i> Add</a>
    	               <?php } ?>
                    <div class="btn-group"></div>
                </div>
            </div>
            <div class="portlet-body portlet-toggler">
                <table id="language" class="table table-striped table-bordered table-hover"></table>
            </div>
            <div class="portlet-toggler pageform" style="display:none;"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function() {
	  OTable= $('#language').dataTable( {
	  		"bStateSave" : true,
			"bProcessing": true,
			"bServerSide": true,
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
			 "fnStateSave": function (oSettings, oData) {
	            localStorage.setItem('language', JSON.stringify(oData));
	        },
	        "fnStateLoad": function (oSettings) {
	            return JSON.parse(localStorage.getItem('language'));
	        },
			 "aoColumns": [
				{ "sName": "languageName", 'sTitle' : 'Language Name'}
				<?php if(in_array('status',$this->Permission)){ ?>
				,{ "sName": "status", 'sTitle' : 'Status', bSearchable:false}
				<?php } ?>
				<?php if(in_array('edit',$this->Permission) || in_array('delete',$this->Permission) || in_array('view',$this->Permission) ){ ?>
				,{ "sName": "operation", 'sTitle' : 'Operation' ,bSortable:false,bSearchable:false}
				<?php } ?>
			],
			"aaSorting": [],
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
		return /^[a-zA-Z0-9][a-zA-Z0-9\-\_]*$/.test(value); 
		},'Page name is not valid. Only alphanumeric and -,_ are allowed'
	);
	$(document).on('submit','#frm-forgotpss', function(e){
		$("#frm-forgotpss").on('submit', function() {
			for(var instanceName in CKEDITOR.instances) {
				CKEDITOR.instances[instanceName].updateElement();
			}
		})
		$("#frm-forgotpss").validate({
			ignore:[],
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
		if($("#frm-forgotpss").valid()){
			return true;
		}else{
			return false;
		}
	});


	$(document).on('submit','#frmCont', function(e){
        $("#frmCont").validate({
            errorClass: 'help-block',
            errorElement: 'span',
            rules: {
                languageName: {
                    required: true,
                   
                }
            },
            messages: {
                languageName: {
                    required: "&nbsp; Please enter language.",
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
