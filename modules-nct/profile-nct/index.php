<?php
$reqAuth = false;
$module = 'profile-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.profile-nct.php";


$js_array = array(
	SITE_JS . "modules/$module.js"
);
$obj = new Profile($module, 0, $_REQUEST);

$winTitle = $headTitle = $obj->user['fullName'] . ' - ' . SITE_NM;

$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
	if (isset($_REQUEST['pageNo'])) {
		echo $obj->{$method}();
		exit ;
	}

	$response['status'] = 1;
	$response['msg'] = 'undefined';
	$response['html'] = $obj->{$method}();
	echo json_encode($response);
	exit ;
}
$pageContent = $obj->getPageContent();
require_once DIR_TMPL . "parsing-nct.tpl.php";
?>