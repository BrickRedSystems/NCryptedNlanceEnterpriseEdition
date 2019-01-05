<?php
$reqAuth = true;
$module = 'my_reviews-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.my_reviews-nct.php";

$winTitle = $sessFirstName . ' ' . SITE_NM;
$headTitle = $sessFirstName . ' ' . SITE_NM;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));
$js_array = array(
	SITE_JS . "jquery.infinitescroll.js"
);
$obj = new MyReviews($module, 0, $_REQUEST);
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