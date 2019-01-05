<?php
$reqAuth = true;
require_once ("../../../includes-nct/config-nct.php");
require_once ("class.dispute-messages-nct.php");

$module = "dispute-messages-nct";
$table = "tbl_messages";

chkPermission($module);
$Permission = chkModulePermission($module);

$styles = array(
	array(
		"data-tables/DT_bootstrap.css",
		SITE_ADM_PLUGIN
	),
	array(
		"bootstrap-switch/css/bootstrap-switch.min.css",
		SITE_ADM_PLUGIN
	)
);

$scripts = array(
	"core/datatable.js",
	array(
		"data-tables/jquery.dataTables.js",
		SITE_ADM_PLUGIN
	),
	array(
		"data-tables/DT_bootstrap.js",
		SITE_ADM_PLUGIN
	),
	array(
		"bootstrap-switch/js/bootstrap-switch.min.js",
		SITE_ADM_PLUGIN
	)
);

$metaTag = getMetaTags(array(
	"description" => "Admin Panel",
	"keywords" => 'Admin Panel',
	"author" => SITE_NM
));
$breadcrumb = array("Dispute Messages");

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;
$ctypeTxt = isset($_REQUEST["ctype"]) ? trim($_REQUEST["ctype"]) : "f";
$ctype = $ctypeTxt == 'pages' ? 't' : ($ctypeTxt == 'messages' ? 'm' : 'f');
$headTitle = 'Dispute Messages';
$winTitle = $headTitle . ' - ' . SITE_NM;

$constObj = new DisputeMessages($module, $id, array(), $type = 'langArray');
$pageContent = $constObj -> getPageContent();
require_once (DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
