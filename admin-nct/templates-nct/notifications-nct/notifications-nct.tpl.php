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
                <div class="caption">
                    <i class="fa fa-dot-circle-o"></i><?php echo $this->headTitle; ?>
                </div>
            </div>
            <div class="portlet-body portlet-toggler">
                <!--<div class="scroller" style="height: auto; max-height: 500px; min-height: 30px;" data-always-visible="1" data-rail-visible="0">-->
                <div class="scroller" style="height: 500px; max-height: 500px; min-height: 30px;" data-always-visible="1" data-rail-visible="0">
                    <ul id="all_notifications_container" class="feeds">
                        
                    </ul>
                </div>
            </div>
            <div class="portlet-body portlet-toggler pageform" style="display:none;"></div>
        </div>
    </div>
</div>
<script type="text/javascript">

    var filterData = {};
    var order = 'DESC';
    var URL = "<?php echo SITE_ADM_MOD . $this->module ?>/ajax.<?php echo $this->module; ?>.php";
    var from, to;

    function infScroll(flag) {

        if (typeof flag !== 'undefined') {
            $(window).unbind('scroll');
            $('#all_notifications_container').html('');
        }

        $('#all_notifications_container').html('');
        $('#all_notifications_container').scrollPagination({
            nop: 10,
            action: "getRests",
            offset: 0,
            initMsg: '<?php echo LOADER; ?>',
            error: '<li class="nmrf">No more notifications found.</li>',
            delay: 500,
            scroll: false,
            async: false,
            ajaxFile: URL,
            addDiv: true,
            extraData: filterData,
            afterLoad: function (data) { // after loading content, you can use this function to animate your new elements
                //console.log(data);
            }
        });
    }
    $(document).ready(function () {
        infScroll(true);
    });

</script>