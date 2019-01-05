<?php
$reqAuth = true;
$module = 'wallet-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.wallet-nct.php";

extract($_REQUEST);

$winTitle = $headTitle = 'Wallet - ' . SITE_NM;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

$js_array = array(SITE_JS . "modules/$module.js");

$obj = new Wallet($module, 0, $_REQUEST);
extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
	$response['status'] = 1;
	$response['msg'] = 'undefined';
	$response['html'] = $obj->{$method}();	
	echo json_encode($response);
	exit ;
}
$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>