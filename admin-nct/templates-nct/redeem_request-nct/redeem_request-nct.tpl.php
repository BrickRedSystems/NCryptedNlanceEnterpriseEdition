<script type="text/javascript">

    $(function() {
  		OTable = $('#example123').dataTable({
			"bProcessing": true,
			"bServerSide": true,
			aaSorting : [[0, 'desc']],
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
			 	{ "sName": "id", 'sClass' : 'hidden'},
				{ "sName": "userName", 'sTitle' : 'User Name'},
				{ "sName": "email", 'sTitle' : 'Email'},
				{ "sName": "paypalEmail", 'sTitle' : 'PayPal ID'},
				{ "sName": "amount", 'sTitle' : 'Redeem amount'},
				{ "sName": "createdDate", 'sTitle' : 'Request Date'} ,
				{ "sName": "transactionId", 'sTitle' : 'Transaction ID'} ,
				{ "sName": "pay", 'sTitle' : 'Pay', bSortable:false, bSearchable:false }
				<?php if(in_array('delete',$this->Permission) || in_array('view',$this->Permission) ){ ?>
				,{ "sName": "operation", 'sTitle' : 'Operation' ,bSortable:false,bSearchable:false }
				<?php } ?>
			],
			"fnServerParams": function(aoData){setTitle(aoData, this)},
			"fnDrawCallback": function( oSettings ) {
				$('.make-switch').bootstrapSwitch();
				$('.make-switch').bootstrapSwitch('setOnClass', 'success');
				$('.make-switch').bootstrapSwitch('setOffClass', 'danger');
			}
   		}
	);

	$('.dataTables_filter').css({float:'right'});
	$('.dataTables_filter input').addClass("form-control input-inline");
	/*OTable.fnSort([[0,'desc']]);*/

	

	$.validator.addMethod('pagenm',function (value, element) {
		return /^[a-zA-Z0-9][a-zA-Z0-9\_\-]*$/.test(value);
		},'Page name is not valid. Only alphanumeric and _ are allowed'
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
				page_name:{
					pagenm:true,
					remote:{
						url:"<?php echo SITE_ADM_MOD.$this->module ?>/ajax.<?php echo $this->module;?>.php",
						type: "post",
						async:false,
						data: {ajaxvalidate:true,page_name: function() {return $("#page_name").val();},id: function() {return $("#id").val();}},
						complete: function(data){
							return data;
						}
					}
				}
			},
			messages:{
				page_name:{remote:'Page name already exist'}
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

	$(document).on('click', '.btn-approveRedeem', function(e) {
		e.preventDefault();
		var href = $(this).attr('href');
		if(confirm('Are you sure to pay ?') && href!='') {
			$.ajax({
				url: href,
				type: 'POST',
				dataType: 'json'
			})
			.done(function(data) {
				if(data && data.code && data.code=='200') {
					window.location.href = data.url;
				} else {
					toastr['error'](data.msg);
				}
			});
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
				<!-- <div class="clearfix"></div> -->
                <div class="actions portlet-toggler">            	
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
