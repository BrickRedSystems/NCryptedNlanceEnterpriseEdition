<?php
$reqAuth = true;
$module = 'messages-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.messages-nct.php";

$winTitle = $sessFirstName . ' ' . SITE_NM;
$headTitle = $sessFirstName . ' ' . SITE_NM;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

$css_array = array(SITE_CSS . "scroll/jquery.mCustomScrollbar.css", );

$js_array = array(
	SITE_PLUGIN . "scroll/jquery.mCustomScrollbar.concat.min.js",
	SITE_JS . "modules/$module.js"
);
$obj = new Messages($module, 0, $_REQUEST);
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