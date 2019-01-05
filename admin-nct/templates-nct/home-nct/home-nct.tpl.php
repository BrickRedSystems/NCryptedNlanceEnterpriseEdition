<?php
$qrySel = $this->db->pdoQuery(" SELECT * FROM tbl_users ")->results();
$count = count($qrySel);

$category = $this->db->pdoQuery(" SELECT * FROM  tbl_categories where parentId=0 ")->results();
$categorycount = count($category);

$subcategory = $this->db->pdoQuery(" SELECT * FROM  tbl_categories where parentId>0 ")->results();
$subcategorycount = count($subcategory);
$projectsCount = getTableValue('tbl_projects','COUNT(id)');
$revenueEarned = getTableValue('tbl_payment_history','SUM(adminCommission)');
$revenueEscrow = getTableValue('tbl_payment_history','SUM(totalAmount)',array('paymentType'=>'escrow'));
$revenueFeatured = getTableValue('tbl_payment_history','SUM(totalAmount)',array('paymentType'=>'featured'));

$revenueEarned = $revenueEarned + $revenueEscrow + $revenueFeatured;

?>


    <!-- BEGIN PAGE HEADER-->
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN PAGE TITLE & BREADCRUMB-->
            <h3 class="page-title">
            Dashboard
            </h3>
           	<?php
				echo $this->breadcrumb;
			?>
            <!-- END PAGE TITLE & BREADCRUMB-->
        </div>
    </div>
    <!-- END PAGE HEADER-->
    <!-- BEGIN DASHBOARD STATS -->
    <div class="row">
    	<?php //echo $this->dashboard_list;?>
    	
    	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
	        <a href="<?php print SITE_ADM_MOD; ?>users-nct">
	            <div class="dashboard-stat blue">
	                <div class="visual">
	                    <i class="fa fa-users"></i>
	                </div>
	                <div class="details">
	                    <div class="number" id="getcustomer">
	                        <?php echo $count; ?>
	                    </div>
	                    <div class="desc">
	                        Users
	                    </div>
	                </div>	
	            </div>	
	        </a>
	    </div>
    	
    	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
	        <a href="<?php print SITE_ADM_MOD; ?>category-nct">
	            <div class="dashboard-stat green">
	                <div class="visual">
	                    <i class="fa fa-sitemap"></i>
	                </div>
	                <div class="details">
	                    <div class="number">
	                        <?php echo $categorycount; ?>
	                    </div>
	                    <div class="desc">
	                        Categories
	                    </div>
	                </div>
	            </div>
	        </a>
	    </div>
	    
	    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
	        <a href="<?php print SITE_ADM_MOD; ?>subcategory-nct">
	            <div class="dashboard-stat purple">
	                <div class="visual">
	                    <i class="fa fa-sitemap"></i>
	                </div>
	                <div class="details">
	                    <div class="number">
	                        <?php echo $subcategorycount; ?>
	                    </div>
	                    <div class="desc">
	                        Sub Categories
	                    </div>
	                </div>	
	            </div>	
	        </a>
	    </div>
	    
	    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
	        <a href="<?php print SITE_ADM_MOD; ?>projects-nct">
	            <div class="dashboard-stat red">
	                <div class="visual">
	                    <i class="fa fa-tasks"></i>
	                </div>
	                <div class="details">
	                    <div class="number">
	                        <?php echo $projectsCount; ?>
	                    </div>
	                    <div class="desc">
	                        Projects
	                    </div>
	                </div>	
	            </div>	
	        </a>
	    </div>
    	
    	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
	        <a href="<?php print SITE_ADM_MOD; ?>received_payments-nct">
	            <div class="dashboard-stat dark">
	                <div class="visual">
	                    <i class="fa fa-credit-card "></i>
	                </div>
	                <div class="details">
	                    <div class="number">
	                        $<?php echo $revenueEarned; ?>
	                    </div>
	                    <div class="desc">
	                        Revenue Earned
	                    </div>
	                </div>	
	            </div>	
	        </a>
	    </div>
    	
    		
    </div>
    <!-- END DASHBOARD STATS -->
    <div class="clearfix">
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <!-- BEGIN PORTLET-->
           		<div id="container" style="min-width: 310px; height: 600px; margin: 0 auto">
           			
           			
           			<ul id="chart_toggle" class="switcher">
				      <li class="active"><a href="#chartdiv" data-chart-id="pie" >Projects Per Category </a></li>
				      <li><a href="#chartdiv" data-chart-id="user_views">View Users</a></li>
				      <li><a href="#chartdiv" data-chart-id="posted_projects">Posted Projects</a></li>
				      <li><a href="#chartdiv" data-chart-id="revenues_earned">Revenue Earned</a></li>
				      <!--<li><a href="#" data-chart-id="purchase_view" >View Purchase</a></li>-->				      
				    </ul>
  <div id="chartdiv" style="width:100%;height:500px;font-size: 11px;"></div>
    

<script src="<?php print SITE_ADM_PLUGIN;?>amcharts.js" type="text/javascript"></script>
<script src="<?php print SITE_ADM_PLUGIN;?>serials.js" type="text/javascript"></script>
<script src="<?php print SITE_ADM_PLUGIN;?>none.js"></script>
<script src="<?php print SITE_ADM_PLUGIN;?>pie.js"></script>

<script type="text/javascript">

$(document).ready(function(){	
    showChartpie(jQuery(this).data("chart-id")=='pie')
});
var chartReady = {
    "user_views": false,
    "posted_projects": false,
    "revenue_earned": false
}
var chart;
var graph;

function showChartpie(pdata)
{
        var chart = AmCharts.makeChart( "chartdiv", <?php echo $this->projectsPerCategory; ?> );        
}

jQuery(document).ready(function() {

    var chartsVisible = (jQuery("#listing_charts").hasClass("show"));

    if (chartsVisible) {
        AmCharts.ready(function() {
            var chartTitle = "Users";
            
            jQuery("#chart_toggle li a").click(function(e) {
                e.preventDefault();
                if(jQuery(this).data("chart-id")=='pie')
                {
                    showChartpie(jQuery(this).data("chart-id"))
                }
                else if(jQuery(this).data("chart-id")=='user_views')
                {
                    showChartuser_views(jQuery(this).data("chart-id"))
                }
                else if(jQuery(this).data("chart-id")=='posted_projects')
                {
                    posted_projects(jQuery(this).data("chart-id"))
                }
                else if(jQuery(this).data("chart-id")=='revenues_earned')
                {
                    revenues_earned(jQuery(this).data("chart-id"))
                }
                else
                {
                    showChartpie(jQuery(this).data("chart-id"))
                }
                jQuery("#chart_toggle li.active").removeClass("active");
                jQuery(this).closest("li").addClass("active");
            });
        });
    }

});
function showChartuser_views(data)
{
    var chartData = <?php echo $this->chart_data; ?>;
    console.log(chartData);
	
	var chart = AmCharts.makeChart("chartdiv", {
	    "type": "serial",
	    "theme": "none",
	    "pathToImages": "http://www.amcharts.com/lib/3/images/",
	    "legend": {
	        "useGraphSettings": true
	    },
	    "dataProvider": chartData,
	    "valueAxes": [{
	        "id":"v1",
	        "axisColor": "#FF6600",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "axisAlpha": 1,
	        "position": "left"
	    }, {
	        "id":"v2",
	        "axisColor": "#FCD202",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "axisAlpha": 1,
	        "position": "right"
	    }, {
	        "id":"v3",
	        "axisColor": "#B0DE09",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "offset": 50,
	        "axisAlpha": 1,
	        "position": "left"
	    }],
	    "graphs": [{
	        "id":"g1",
	        "balloonText": "[[category]]<br/><b><span style='font-size:14px;'>Customer: [[value]]</span></b>",
	        "bullet": "round",
	        "bulletBorderAlpha": 0,
	        "bulletBorderThickness": 1,
	        "hideBulletsCount": 30,
	        "title": "Customer",
	        "valueField": "customer",
	        "useLineColorForBulletBorder":true,
	        "lineColor": "#FF6600"
	    },{
	        "id":"g2",
	        "balloonText": "[[category]]<br/><b><span style='font-size:14px;'>Provider: [[value]]</span></b>",
	        "bullet": "triangleUp",
	        "bulletBorderAlpha": 0,
	        "bulletBorderThickness": 1,
	        "hideBulletsCount": 30,
	        "title": "Provider",
	        "valueField": "provider",
	        "useLineColorForBulletBorder":true,
	        "lineColor": "#FCD202"
	    }],
	    "chartScrollbar": {
	        "autoGridCount": true,
	        "graph": "g1",
	        "scrollbarHeight": 40
	    },
	    "chartCursor": {
	        "cursorPosition": "mouse"
	    },
	    "categoryField": "date",
	    "categoryAxis": {
	        "parseDates": true,
	        "axisColor": "#DADADA",
	        "minorGridEnabled": true
	    },
	    "export": {
	        "enabled": true,
	        "position": "bottom-right"
	     }
	});
	
	chart.addListener("dataUpdated", zoomChart);
	zoomChart();
	
	
	function zoomChart(){
	    chart.zoomToIndexes(chart.dataProvider.length - 20, chart.dataProvider.length - 1);
	}

}

function posted_projects(data){
	var chartData = <?php echo $this->postedProjects; ?>;
	
	var chart = AmCharts.makeChart("chartdiv", {
	    "type": "serial",
	    "theme": "none",
	    "pathToImages": "http://www.amcharts.com/lib/3/images/",
	    "legend": {
	        "useGraphSettings": true
	    },
	    "dataProvider": chartData,
	    "valueAxes": [{
	        "id":"v1",
	        "axisColor": "#FF6600",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "axisAlpha": 1,
	        "position": "left"
	    }, {
	        "id":"v2",
	        "axisColor": "#FCD202",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "axisAlpha": 1,
	        "position": "right"
	    }, {
	        "id":"v3",
	        "axisColor": "#B0DE09",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "offset": 50,
	        "axisAlpha": 1,
	        "position": "left"
	    }],
	    "graphs": [{
	        "id":"g1",
	        "balloonText": "Total [[project]] projects posted on [[date]]",
	        "bullet": "round",
	        "bulletBorderAlpha": 0,
	        "bulletBorderThickness": 1,
	        "hideBulletsCount": 30,
	        "title": "Posted projects",
	        "valueField": "project",
	        "useLineColorForBulletBorder":true,
	        "lineColor": "#FF6600"
	    }],
	    "chartScrollbar": {
	        "autoGridCount": true,
	        "graph": "g1",
	        "scrollbarHeight": 40
	    },
	    "chartCursor": {
	        "cursorPosition": "mouse"
	    },
	    "categoryField": "date",
	    "categoryAxis": {
	        "parseDates": true,
	        "axisColor": "#DADADA",
	        "minorGridEnabled": true
	    },
	    "export": {
	        "enabled": true,
	        "position": "bottom-right"
	     }
	});
	
	chart.addListener("dataUpdated", zoomChart);
	zoomChart();
	
	
	function zoomChart(){
	    chart.zoomToIndexes(chart.dataProvider.length - 20, chart.dataProvider.length - 1);
	}
}

function revenues_earned(data)
{
	 var revenueData = <?php echo $this->revenueData; ?>;
     var chart = AmCharts.makeChart("chartdiv", {
	    "type": "serial",
	     "theme": "light",
	    "categoryField": "year",
	    "rotate": true,
	    "startDuration": 1,
	    "categoryAxis": {
	        "gridPosition": "start",
	        "position": "left"
	    },
	    "trendLines": [],
	    "graphs": [
	        {
	            "balloonText": "Income: $[[value]]",
	            "fillAlphas": 0.8,
	            "id": "AmGraph-1",
	            "lineAlpha": 0.2,
	            "title": "Income",
	            "type": "column",
	            "valueField": "income"
	        },
	        {
	            "balloonText": "Expenses: $[[value]]",
	            "fillAlphas": 0.8,
	            "id": "AmGraph-2",
	            "lineAlpha": 0.2,
	            "title": "Expenses",
	            "type": "column",
	            "valueField": "expenses"
	        }
	    ],
	    "guides": [],
	    "valueAxes": [
	        {
	            "id": "ValueAxis-1",
	            "position": "top",
	            "axisAlpha": 0
	        }
	    ],
	    "allLabels": [],
	    "balloon": {},
	    "titles": [],
	    "dataProvider": revenueData,
	    "export": {
	        "enabled": true
	     }
	
	});
}

// this method is called when chart is first inited as we listen for "dataUpdated" event

function generatePChartData(data) {
    var chartData = [];   
    for (var i = 0; i < data.length; i++) {        
        var exdata=data[i];
        var dataval=exdata[0];
        var datatitle=exdata[1];
        chartData.push({
            Categories: datatitle,
            Projects: dataval
        });
    }
	return chartData;

}

    var chartData1 = '';
    var chartId='pie';
       $.ajax({
                url: '',
                data:{type: chartId},
                dataType: 'json',
                success: function(data) {
                    var chartData = generatePChartData(data);
                    var chart = AmCharts.makeChart("chartdiv", {
                    "type": "pie",  
                    "theme": "none",
                    "legend": {
                        "markerType": "circle",
                        "position": "right",
                        "marginRight": 80,      
                        "autoMargins": false
                    },
                    "dataProvider":chartData,
                    "valueField": "Projects",
                    "titleField": "Categories",
                    "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
                    "exportConfig": {
                        "menuTop":"0px",
                        "menuItems": [{
                            "icon": '/lib/3/images/export.png',
                            "format": 'png'
                        }]
                    }
                });
    
                }
            });

</script>

<div id="listing_charts" data-hosting-id="21" class="show">
    <div id="user_chart" class="active"></div>
    <div id="Projects_chart"></div>   
</div>
           			
           			
       		</div>
        <!-- END PORTLET-->
    </div>    
</div>