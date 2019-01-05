<?php
	$reqAuth=true;
	require_once("../../../includes-nct/config-nct.php");
	require_once("class.numeric_statistics-nct.php");

	$objPost = new stdClass();
	
	$winTitle = 'Numerical Statistics - '.SITE_NM;
	$headTitle = 'Numerical Website Statistics';
	$metaTag = getMetaTags(array("description"=>"Admin Panel",
		"keywords"=>'Admin Panel',
		'author'=>AUTHOR));
		
	$module = 'numeric_statistics-nct';
	$breadcrumb = array($headTitle);
	chkPermission($module);

	$objStatistics = new Statistics();	
	$pageContent = $objStatistics->getPageContent();
	require_once(DIR_ADMIN_TMPL."parsing-nct.tpl.php");
?>