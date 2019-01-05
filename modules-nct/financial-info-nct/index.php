<?php
$reqAuth = true;
$module = 'financial-info-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.financial-info-nct.php";

extract($_REQUEST);

$winTitle = $headTitle = Financial_Information.' - ' . SITE_NM;


$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

$js_array = array(SITE_JS . "modules/$module.js");

$obj = new FinancialInfo($module, 0, $_REQUEST);
extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
	$response['status'] = 1;
	$response['msg'] = 'undefined';
	$response['html'] = $obj->{$method}();	
	echo json_encode($response);
	exit;
}
$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>