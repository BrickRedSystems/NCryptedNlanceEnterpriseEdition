<?php
$reqAuth = false;
$module = 'home-nct';
require_once "includes-nct/config-nct.php";
require_once "modules-nct/home-nct/class.home-nct.php";


extract($_REQUEST);
$winTitle = 'Welcome to ' . SITE_NM;
$headTitle = 'Home' . SITE_NM;
$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords" => $headTitle,
    "author" => AUTHOR
));

$js_array = array(SITE_JS . "jquery.waypoints.min.js", SITE_JS . "jquery.counterup.min.js", SITE_JS . "modules/$module.js");

$obj = new Home($module, $_REQUEST);
extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
    $response['status'] = 1;
    $response['msg'] = 'undefined';
    $response['html'] = $obj -> {$method}();
    echo json_encode($response);
    exit ;
}
$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>