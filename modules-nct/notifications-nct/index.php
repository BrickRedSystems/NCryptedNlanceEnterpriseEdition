<?php
$reqAuth = true;
$module = 'notifications-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.notifications-nct.php";

$winTitle = 'Notifications - ' . SITE_NM;
$headTitle = $winTitle;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));
$js_array = array(
	SITE_JS . "jquery.infinitescroll.js",
	SITE_JS . "modules/$module.js"
);
$obj = new Notifications($module, 0, $_REQUEST);
extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
	if (isset($_REQUEST['pageNo'])) {
		echo $obj -> {$method}();
		exit ;
	}

	$response['status'] = 1;
	$response['msg'] = 'undefined';
	$response['html'] = $obj -> {$method}();
	echo json_encode($response);
	exit ;
}
$pageContent = $obj -> getPageContent();
require_once DIR_TMPL . "parsing-nct.tpl.php";
?>