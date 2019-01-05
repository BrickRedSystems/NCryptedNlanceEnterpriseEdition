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
            <div class="tools">
                <a href="javascript:;" class="collapse"></a>
            </div>
        </div>
        <div class="portlet-body form">
            %STATISTICS_LIST%
        </div>
     </div>   
	</div>
</div>    	