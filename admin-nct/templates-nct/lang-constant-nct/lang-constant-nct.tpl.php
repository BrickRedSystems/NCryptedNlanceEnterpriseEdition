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
                <?php if(in_array('add',$this->Permission) && ENVIRONMENT == 'd'){ ?>
                <a href="<?php echo SITE_ADM_MOD.$this->module.'/';?>ajax.<?php echo $this->module;?>.php?action=add" class="btn blue btn-add">
                    <i class="fa fa-pencil"></i> Add
                </a>
              
                <?php } ?>
                <div class="btn-group">
                      <a class="btn green" href="#" data-toggle="dropdown">
                      <i class="fa fa-cogs"></i> Language
                      <i class="fa fa-angle-down"></i>
                      </a>
                      <ul class="dropdown-menu pull-right" id="changeLanguage">
                    
                    <?php 
                    
                    foreach($this->langArray as $k => $v)
                    {?>
                        <li><a href="javascript:void(0);"  data-url="<?php echo SITE_ADM_MOD.$this->module.'/';?>ajax.<?php echo $this->module;?>.php" data-id="<?php echo $k;?>"><?php echo $v;?></a></li>
                    <?php }
                    ?>
                      </ul>
                   </div>
                </div>
            </div>
            <div class="portlet-body portlet-toggler">
                <table id="cnstnt" class="table table-striped table-bordered table-hover"></table>
            </div>
            <div class="portlet-toggler pageform" style="display:none;"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
var langId = 1;

$(function() {
	 $("#changeLanguage li").on("click","a",function(e){

		  var url = $(this).data("url");
		  langId = $(this).data("id");
		  OTable.fnDraw();
	 });
	  OTable = $('#cnstnt').dataTable({
			"bProcessing": true,
			"bServerSide": true,
			"bStateSave": true,
	        /*"fnStateSave": function (oSettings, oData) {
	            localStorage.setItem('cnstnt', JSON.stringify(oData));
	        },
	        "fnStateLoad": function (oSettings) {
	            return JSON.parse(localStorage.getItem('cnstnt'));
	        },*/
			"sAjaxSource": "<?php echo SITE_ADM_MOD.$this->module.'/';?>ajax.<?php echo $this->module;?>.php",
			"fnServerData": function (sSource, aoData, fnCallback) {
				$.ajax({
				   "dataType": 'json',
				   "type": "POST",
				   "url": sSource+'?langId='+langId,
				   "data": aoData,
				   "success": fnCallback
				});
			 },
			 "aaSorting": [],
			 "aoColumns": [
				{ "sName": "constantName", 'sTitle' : 'Constant Name'},
				{ "sName": "constantValue", 'sTitle' : 'Constant value'}
				<?php if(in_array('edit',$this->Permission) || in_array('delete',$this->Permission) || in_array('view',$this->Permission) ){ ?>
				,{ "sName": "operation", 'sTitle' : 'Operation' ,bSortable:false,bSearchable:false}
				<?php } ?>
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
		return /^[a-zA-Z0-9][a-zA-Z0-9\-\_]*$/.test(value); 
		},'Page name is not valid. Only alphanumeric and -,_ are allowed'
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
