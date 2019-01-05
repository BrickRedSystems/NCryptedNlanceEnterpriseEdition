<?php
#############################################################
# Project:			Demo-Structure PMS
# Developer ID:		107
# Page: 			PMS
# Started Date: 	26-Jul-2016
##############################################################
$reqAuth = true;
$module = 'pms-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.pms-nct.php";

extract($_REQUEST);

$winTitle = 'Messages - ' . SITE_NM;
$headTitle = 'Messages';
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));
$obj = new Message($module);
$pageContent = $obj->getPageContent();
require_once DIR_TMPL . "parsing-nct.tpl.php";
?>