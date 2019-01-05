<?php
$reqAuth = true;
$module = 'my_providers-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.my_providers-nct.php";

extract($_REQUEST);

$winTitle = 'My providers - ' . SITE_NM;
$headTitle = $winTitle;
$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords" => $headTitle,
    "author" => AUTHOR
));
if ($sessUserType != "c") {
    $msgType = $_SESSION["msgType"] = disMessage(array(
        'type' => 'err',
        'var' => The_page_you_are_trying_to_access_does_not_exist
    ));
    redirectPage(SITE_URL);
}
$js_array = array(SITE_JS . "modules/$module.js");

$obj = new myProviders($module, 0, $_REQUEST);
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